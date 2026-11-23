/**
 * Offline form capture for permitted ERP writes + online-only guards.
 * WhatsApp / notifications / RBAC / website stay online-only.
 * Cash, delivery, transfer queue offline and sync later.
 * Reports: new queries need online; Print/PDF of a loaded page works offline.
 */
(function (window, document) {
	'use strict';

	var ONLINE_ONLY_PATHS = [
		'/notifications',
		'/order/order_whatsapp',
		'/rbac',
		'/configuration',
		'/website/',
		'/broadcast'
	];

	var REPORT_PATHS = [
		'/reports',
		'/report',
		'/cash/customer_ledger_pro',
		'/cash/employee_ledger_pro',
		'/cash/cash_ledger_pro',
		'/cash/customer_pending_ledger_pr',
		'/cash/cash_flow',
		'/cash/reprt',
		'/cash/sale'
	];

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function db() {
		return window.DarziOffline.db;
	}

	function t(key, fallback) {
		var lang = window.APP_LANG || {};
		return lang[key] || fallback || key;
	}

	function isOnline() {
		return window.DarziOffline.connectivity && window.DarziOffline.connectivity.isOnline();
	}

	function pathOf(action) {
		try {
			var u = new URL(action, window.location.href);
			return (u.pathname || '').toLowerCase();
		} catch (e) {
			return String(action || '').toLowerCase();
		}
	}

	function pathMatches(p, list) {
		for (var i = 0; i < list.length; i++) {
			if (p.indexOf(list[i]) !== -1) {
				return true;
			}
		}
		return false;
	}

	function isOnlineOnlyAction(action) {
		return pathMatches(pathOf(action), ONLINE_ONLY_PATHS);
	}

	function isReportAction(action) {
		return pathMatches(pathOf(action), REPORT_PATHS);
	}

	function formToObject(form) {
		var fd = new FormData(form);
		var out = {};
		fd.forEach(function (value, key) {
			if (Object.prototype.hasOwnProperty.call(out, key)) {
				if (!Array.isArray(out[key])) {
					out[key] = [out[key]];
				}
				out[key].push(value);
			} else {
				out[key] = value;
			}
		});
		return out;
	}

	function classifyForm(form) {
		var action = form.getAttribute('action') || window.location.href;
		var p = pathOf(action);
		if (p.indexOf('/customer/process_edit') !== -1) {
			return 'customer.update';
		}
		if (p.indexOf('/customer/process') !== -1) {
			return 'customer.create';
		}
		if (p.indexOf('/measurement/process_edit') !== -1) {
			return 'measurement.update';
		}
		if (p.indexOf('/measurement/process') !== -1) {
			return 'measurement.create';
		}
		if (p.indexOf('/order/process_del') !== -1) {
			return 'delivery.create';
		}
		if (p.indexOf('/order/process') !== -1 && p.indexOf('process_edit') === -1) {
			return 'order.create';
		}
		if (p.indexOf('/cash/process_status') !== -1) {
			return 'cash.pending_pay';
		}
		if (p.indexOf('/cash/process') !== -1) {
			return 'cash.create';
		}
		if (p.indexOf('/transfer/transfer_process_bulk') !== -1) {
			return 'transfer.process_bulk';
		}
		if (p.indexOf('/transfer/process/') !== -1 || /\/transfer\/process$/.test(p.replace(/\/+$/, ''))) {
			return 'transfer.process';
		}
		if (form.getAttribute('data-offline-entity')) {
			return form.getAttribute('data-offline-entity') + '.' + (form.getAttribute('data-offline-op') || 'create');
		}
		return null;
	}

	function provisionalNumber(prefix) {
		return db().getMeta('seq_' + prefix, 0).then(function (n) {
			n = (parseInt(n, 10) || 0) + 1;
			return db().setMeta('seq_' + prefix, n).then(function () {
				var pad = String(n);
				while (pad.length < 4) {
					pad = '0' + pad;
				}
				return 'OFF-' + prefix + '-' + pad;
			});
		});
	}

	function enqueueLocal(entityType, opType, payload, provisionalPrefix) {
		var clientUuid = db().uuid();
		var chain = provisionalPrefix
			? provisionalNumber(provisionalPrefix)
			: Promise.resolve('');
		return chain.then(function (displayNo) {
			return db()
				.enqueue({
					entity_type: entityType,
					entity_client_id: clientUuid,
					op_type: opType,
					payload: payload,
					idempotency_key: clientUuid + ':' + entityType + '.' + opType
				})
				.then(function () {
					return {
						client_uuid: clientUuid,
						provisional_no: displayNo || ''
					};
				});
		});
	}

	function saveCustomerCreate(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('C').then(function (displayNo) {
			var man = (payload.man_napno || '').toString().trim() || displayNo;
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				man_napno: man,
				name: payload.Name || payload.name || '',
				city: payload.City || payload.city || '',
				country: payload.Country || payload.country || '',
				Phone: payload.Phone || payload.phone || '',
				Address: payload.Address || payload.address || '',
				cnic: payload.CNIC || payload.cnic || '',
				cus_type: payload.type || payload.cus_type || '',
				remarks: payload.Remarks || payload.remarks || '',
				row_version: 0,
				sync_status: 'pending',
				provisional_no: displayNo,
				updated_at: new Date().toISOString()
			};
			return db()
				.put('customers', record)
				.then(function () {
					return db().enqueue({
						entity_type: 'customer',
						entity_client_id: clientUuid,
						op_type: 'create',
						payload: {
							name: record.name,
							city: record.city,
							country: record.country,
							Phone: record.Phone,
							Address: record.Address,
							cnic: record.cnic,
							cus_type: record.cus_type,
							man_napno: record.man_napno,
							remarks: record.remarks
						},
						idempotency_key: clientUuid + ':customer.create'
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function saveCustomerUpdate(form, payload) {
		var action = form.getAttribute('action') || '';
		var m = action.match(/process_edit\/(\d+)/i) || window.location.pathname.match(/customer_edit\/(\d+)/i);
		var serverId = m ? m[1] : payload.code || '';
		var clientUuid = db().uuid();
		var baseVersion = parseInt(payload.row_version || '0', 10) || 0;
		var record = {
			local_id: db().scopedLocalId
				? db().scopedLocalId(serverId, serverId ? null : clientUuid)
				: serverId
					? 'srv:' + serverId
					: clientUuid,
			client_uuid: clientUuid,
			server_id: String(serverId || ''),
			man_napno: payload.man_napno || '',
			name: payload.Name || '',
			city: payload.City || '',
			country: payload.Country || '',
			Phone: payload.Phone || '',
			Address: payload.Address || '',
			cnic: payload.CNIC || '',
			cus_type: payload.type || '',
			remarks: payload.Remarks || '',
			base_row_version: baseVersion,
			sync_status: 'pending',
			updated_at: new Date().toISOString()
		};
		return db()
			.put('customers', record)
			.then(function () {
				return db().enqueue({
					entity_type: 'customer',
					entity_client_id: clientUuid,
					op_type: 'update',
					payload: {
						server_id: serverId,
						base_row_version: record.base_row_version,
						name: record.name,
						city: record.city,
						country: record.country,
						Phone: record.Phone,
						Address: record.Address,
						cnic: record.cnic,
						cus_type: record.cus_type,
						man_napno: record.man_napno,
						remarks: record.remarks
					},
					idempotency_key:
						String(serverId || clientUuid) + ':customer.update:v' + baseVersion + ':' + record.updated_at
				});
			})
			.then(function () {
				return record;
			});
	}

	function looksLikeUuid(value) {
		return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(
			String(value || '').trim()
		);
	}

	function saveOrderCreate(payload) {
		var clientUuid = db().uuid();
		var enriched = Object.assign({}, payload);
		var naapRaw = String(enriched.Naapno || enriched.naap_no || '').trim();
		if (looksLikeUuid(naapRaw)) {
			enriched.measurement_client_id = naapRaw;
			enriched.Naapno = '';
			enriched.naap_no = '';
		} else if (enriched.measurement_client_id && !enriched.naap_no && !enriched.Naapno) {
			/* keep client id for sync rewrite */
		}
		var custRaw = String(enriched.customer_code || enriched.customerno1 || '').trim();
		if (looksLikeUuid(custRaw)) {
			enriched.customer_client_id = custRaw;
		}
		return provisionalNumber('O').then(function (displayNo) {
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				provisional_no: displayNo,
				naap_no: enriched.Naapno || enriched.naap_no || '',
				payload: enriched,
				sync_status: 'pending',
				updated_at: new Date().toISOString()
			};
			return db()
				.put('orders', record)
				.then(function () {
					return db().enqueue({
						entity_type: 'order',
						entity_client_id: clientUuid,
						op_type: 'create',
						payload: enriched,
						idempotency_key: clientUuid + ':order.create'
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function measurementServerCode(form, payload) {
		var action = form.getAttribute('action') || '';
		var m = action.match(/process_edit\/(\d+)/i);
		if (m) {
			return m[1];
		}
		return payload.code || payload.MCODE || '';
	}

	function saveMeasurementCreate(payload) {
		var clientUuid = db().uuid();
		var customerCode = String(payload.customerno1 || payload.customer_code || '');
		var customerClientId = '';
		if (looksLikeUuid(customerCode) || /^OFF-C-/i.test(customerCode)) {
			customerClientId = looksLikeUuid(customerCode) ? customerCode : String(payload.customer_client_id || '');
			if (looksLikeUuid(customerCode)) {
				customerCode = '';
			}
		}
		if (!customerClientId && payload.customer_client_id) {
			customerClientId = String(payload.customer_client_id);
		}
		return provisionalNumber('M').then(function (displayNo) {
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				customer_code: customerCode,
				provisional_no: displayNo,
				payload: payload,
				sync_status: 'pending',
				updated_at: new Date().toISOString()
			};
			return db()
				.put('measurements', record)
				.then(function () {
					var measPayload = Object.assign({}, payload, {
						customer_code: customerCode,
						client_uuid: clientUuid
					});
					if (customerClientId) {
						measPayload.customer_client_id = customerClientId;
					}
					return db().enqueue({
						entity_type: 'measurement',
						entity_client_id: clientUuid,
						op_type: 'create',
						payload: measPayload,
						idempotency_key: clientUuid + ':measurement.create'
					});
				})
				.then(function () {
					return record;
				});
		});
	}

	function saveMeasurementUpdate(form, payload) {
		var serverId = String(measurementServerCode(form, payload) || '');
		var clientUuid = db().uuid();
		var customerCode = String(payload.customerno1 || payload.customer_code || '');
		var baseVersion = parseInt(payload.row_version || '0', 10) || 0;
		var record = {
			local_id: db().scopedLocalId
				? db().scopedLocalId(serverId, serverId ? null : clientUuid)
				: serverId
					? 'srv:' + serverId
					: clientUuid,
			client_uuid: clientUuid,
			server_id: serverId,
			customer_code: customerCode,
			payload: payload,
			sync_status: 'pending',
			updated_at: new Date().toISOString()
		};
		return db()
			.put('measurements', record)
			.then(function () {
				return db().enqueue({
					entity_type: 'measurement',
					entity_client_id: clientUuid,
					op_type: 'update',
					payload: Object.assign({}, payload, {
						server_id: serverId,
						customer_code: customerCode,
						code: serverId,
						base_row_version: baseVersion
					}),
					idempotency_key:
						String(serverId || clientUuid) + ':measurement.update:v' + baseVersion + ':' + record.updated_at
				});
			})
			.then(function () {
				return record;
			});
	}

	function saveCashCreate(payload) {
		return enqueueLocal('cash', 'create', payload, 'CASH');
	}

	function saveCashPendingPay(payload) {
		return enqueueLocal('cash', 'pending_pay', payload, 'PAY');
	}

	function saveDeliveryCreate(form, payload) {
		var action = form.getAttribute('action') || '';
		var m = action.match(/process_del\/(\d+)/i) || window.location.pathname.match(/\/(\d+)\/?$/);
		var orderId = m ? parseInt(m[1], 10) : parseInt(payload.order_id || '0', 10);
		payload.order_id = orderId;
		return enqueueLocal('delivery', 'create', payload, 'DEL').then(function (rec) {
			rec = rec || {};
			rec.order_id = orderId;
			return rec;
		});
	}

	function rememberDeliveredOrder(orderId) {
		orderId = parseInt(orderId, 10) || 0;
		if (!orderId) {
			return;
		}
		var key = 'darzi_just_delivered_orders';
		var list = [];
		try {
			list = JSON.parse(sessionStorage.getItem(key) || '[]');
		} catch (e) {
			list = [];
		}
		if (!Array.isArray(list)) {
			list = [];
		}
		if (list.indexOf(orderId) === -1) {
			list.push(orderId);
		}
		try {
			sessionStorage.setItem(key, JSON.stringify(list));
		} catch (e2) {
			/* ignore quota */
		}
	}

	function invalidateReadyListCache() {
		if (!('serviceWorker' in navigator)) {
			return;
		}
		var msg = {
			type: 'INVALIDATE_PAGES',
			paths: ['/Order/order_issue', '/order/order_issue']
		};
		try {
			if (navigator.serviceWorker.controller) {
				navigator.serviceWorker.controller.postMessage(msg);
			}
			navigator.serviceWorker.ready.then(function (reg) {
				if (reg && reg.active) {
					reg.active.postMessage(msg);
				}
			}).catch(function () { /* ignore */ });
		} catch (e) {
			/* ignore */
		}
		if ('caches' in window) {
			caches.keys().then(function (names) {
				return Promise.all(
					(names || []).map(function (name) {
						if (String(name).indexOf('darzi-erp-') !== 0 || String(name).indexOf('-pages') === -1) {
							return null;
						}
						return caches.open(name).then(function (cache) {
							return cache.keys().then(function (keys) {
								return Promise.all(
									keys.map(function (req) {
										var u = String(req.url || '');
										if (/\/Order\/order_issue/i.test(u) || /\/order\/order_issue/i.test(u)) {
											return cache.delete(req);
										}
										return null;
									})
								);
							});
						});
					})
				);
			}).catch(function () { /* ignore */ });
		}
	}

	function saveTransferProcess(form, payload) {
		var action = form.getAttribute('action') || window.location.href;
		var parts = pathOf(action).split('/').filter(Boolean);
		// .../Transfer/process/{trnno}/{dept}/{sr}
		var idx = parts.indexOf('process');
		if (idx >= 0) {
			payload.transfer_trnno = payload.transfer_trnno || parts[idx + 1] || '';
			payload.dept = payload.dept || parts[idx + 2] || '';
			payload.sr = payload.sr || parts[idx + 3] || '';
		}
		return enqueueLocal('transfer', 'process', payload, 'TR');
	}

	function saveTransferBulk(payload) {
		var items = payload.transfer_items;
		if (typeof items === 'string') {
			try {
				payload.items = JSON.parse(items);
			} catch (e) {
				payload.items = [];
			}
		}
		return enqueueLocal('transfer', 'process_bulk', payload, 'TRB');
	}

	function flashOfflineSaved(message) {
		if (window.DarziOffline && window.DarziOffline.ui && window.DarziOffline.ui.toast) {
			window.DarziOffline.ui.toast(message, 'pending');
			return;
		}
		window.alert(message);
	}

	function blockOnlineOnly() {
		flashOfflineSaved(
			t(
				'offline_online_only',
				'WhatsApp and admin settings need an online connection.'
			)
		);
	}

	function blockReportOffline() {
		flashOfflineSaved(
			t(
				'offline_report_cached_only',
				'New report needs online. Agar page pehle load ho chuka hai to Print/PDF offline use karo.'
			)
		);
	}

	function redirectAfter(kind, record) {
		var url = cfg().homeUrl || window.location.href;
		if (kind === 'customer.create') {
			url = cfg().measurementUrl || url;
		} else if (kind === 'customer.update') {
			url = cfg().customerUrl || url;
		} else if (kind === 'measurement.create' || kind === 'measurement.update') {
			var cust = record && record.customer_code ? String(record.customer_code) : '';
			if (cfg().orderAddUrl && cust) {
				url = cfg().orderAddUrl.replace(/\/$/, '') + '/' + cust;
			} else {
				url = cfg().homeUrl || url;
			}
		} else if (kind === 'order.create') {
			url = cfg().homeUrl || url;
		} else if (kind === 'cash.create' || kind === 'cash.pending_pay') {
			url = cfg().cashUrl || window.location.pathname.replace(/\/process.*/, '') || url;
		} else if (kind === 'delivery.create') {
			url = cfg().deliveryUrl || cfg().homeUrl || url;
			var deliveredId =
				(record && record.order_id) ||
				(record && record.payload && record.payload.order_id) ||
				0;
			rememberDeliveredOrder(deliveredId);
			invalidateReadyListCache();
			// Bust SW/page cache so Ready list does not show the just-delivered order.
			try {
				var u = new URL(url, window.location.href);
				u.searchParams.set('_', String(Date.now()));
				url = u.href;
			} catch (eUrl) {
				url += (url.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();
			}
		} else if (kind.indexOf('transfer') === 0) {
			url = cfg().transferUrl || cfg().homeUrl || url;
		}
		window.setTimeout(function () {
			var offlineNow =
				window.DarziOffline &&
				window.DarziOffline.connectivity &&
				!window.DarziOffline.connectivity.isOnline();
			if (offlineNow && window.DarziOffline.nav && typeof window.DarziOffline.nav.open === 'function') {
				window.DarziOffline.nav.open(url);
				return;
			}
			// Full navigation with cache-bust — avoid stale AJAX/SW Ready list.
			window.location.replace(url);
		}, 600);
	}

	function queueByKind(form, kind, payload) {
		if (kind === 'customer.create') {
			return saveCustomerCreate(payload);
		}
		if (kind === 'customer.update') {
			return saveCustomerUpdate(form, payload);
		}
		if (kind === 'measurement.create') {
			return saveMeasurementCreate(payload);
		}
		if (kind === 'measurement.update') {
			return saveMeasurementUpdate(form, payload);
		}
		if (kind === 'order.create') {
			return saveOrderCreate(payload);
		}
		if (kind === 'cash.create') {
			return saveCashCreate(payload);
		}
		if (kind === 'cash.pending_pay') {
			return saveCashPendingPay(payload);
		}
		if (kind === 'delivery.create') {
			return saveDeliveryCreate(form, payload);
		}
		if (kind === 'transfer.process') {
			return saveTransferProcess(form, payload);
		}
		if (kind === 'transfer.process_bulk') {
			return saveTransferBulk(payload);
		}
		return Promise.reject(new Error('unsupported'));
	}

	function finishQueued(kind, record, opts) {
		opts = opts || {};
		var msg;
		if (opts.synced) {
			msg = t('offline_saved_synced', 'Saved and synced to server');
		} else if (opts.syncing) {
			msg = t('offline_saved_syncing', 'Saved — syncing to server…');
		} else {
			msg = t('offline_saved_pending', 'Saved Offline — Pending Sync');
		}
		flashOfflineSaved(
			msg + (record && record.provisional_no ? ' (' + record.provisional_no + ')' : '')
		);
		window.dispatchEvent(new CustomEvent('darzi-offline-queued'));
		redirectAfter(kind, record);
	}

	/**
	 * Queue-first write path.
	 * Persist locally immediately, redirect/toast, then sync in the background.
	 * Waiting on syncNow blocked the shop UI for minutes (order photos, flaky net).
	 */
	function submitQueueable(form, kind) {
		form.__darziOfflineBusy = true;
		var payload = formToObject(form);
		var online = isOnline();

		return queueByKind(form, kind, payload)
			.then(function (record) {
				finishQueued(kind, record, { synced: false, syncing: online });
				if (online && window.DarziOffline.sync && typeof window.DarziOffline.sync.syncNow === 'function') {
					window.DarziOffline.sync.syncNow({ quiet: true }).catch(function () {
						/* queue kept for retry */
					});
				}
				return record;
			})
			.catch(function (err) {
				flashOfflineSaved(
					t('offline_save_failed', 'Could not save offline: ') + ((err && err.message) || 'error')
				);
			})
			.then(function () {
				form.__darziOfflineBusy = false;
			});
	}

	function onSubmit(event) {
		var form = event.target;
		if (!form || form.tagName !== 'FORM') {
			return;
		}
		if (form.getAttribute('data-offline-bypass') === '1') {
			return;
		}
		if (form.__darziOfflineBusy) {
			event.preventDefault();
			event.stopPropagation();
			return;
		}

		var action = form.getAttribute('action') || window.location.href;
		var kind = classifyForm(form);
		var online = isOnline();

		if (isOnlineOnlyAction(action)) {
			if (!online) {
				event.preventDefault();
				event.stopPropagation();
				blockOnlineOnly();
			}
			return;
		}

		if (isReportAction(action)) {
			if (!online) {
				event.preventDefault();
				event.stopPropagation();
				blockReportOffline();
			}
			return;
		}

		// Non-queueable forms: native browser POST when online; block when offline.
		if (!kind) {
			if (!online) {
				event.preventDefault();
				event.stopPropagation();
				flashOfflineSaved(
					t(
						'offline_form_unsupported',
						'This form is not available offline.'
					)
				);
			}
			return;
		}

		// Online new orders must use native POST → real order # + print page.
		// Queue-first here caused long "Saving…" then dumped users on Welcome
		// while sync pushed a huge photo payload in the background.
		if (kind === 'order.create' && online) {
			return;
		}

		// Queueable ERP writes: durable local-first (offline always; online for
		// non-order kinds). Never block the UI on syncNow — sync in background.
		event.preventDefault();
		event.stopPropagation();
		submitQueueable(form, kind);
	}

	function guardWhatsAppClicks(event) {
		if (isOnline()) {
			return;
		}
		var el = event.target;
		if (!el || !el.closest) {
			return;
		}
		var btn = el.closest('.btn-order-whatsapp, #dz-wa-send, [data-endpoint*="whatsapp"], [href*="whatsapp"]');
		if (!btn) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		flashOfflineSaved(
			t(
				'offline_whatsapp_online_only',
				'WhatsApp online connection ke baad chalega. Ab Print/PDF use karo.'
			)
		);
	}

	function bind() {
		document.addEventListener('submit', onSubmit, true);
		document.addEventListener('click', guardWhatsAppClicks, true);
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.forms = {
		bind: bind,
		isOnlineOnlyAction: isOnlineOnlyAction
	};
})(window, document);
