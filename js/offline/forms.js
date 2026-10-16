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
		var record = {
			local_id: serverId ? 'srv:' + serverId : clientUuid,
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
			base_row_version: parseInt(payload.row_version || '0', 10) || 0,
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
					idempotency_key: db().uuid() + ':customer.update'
				});
			})
			.then(function () {
				return record;
			});
	}

	function saveOrderCreate(payload) {
		var clientUuid = db().uuid();
		return provisionalNumber('O').then(function (displayNo) {
			var record = {
				local_id: clientUuid,
				client_uuid: clientUuid,
				server_id: '',
				provisional_no: displayNo,
				naap_no: payload.Naapno || payload.naap_no || '',
				payload: payload,
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
						payload: payload,
						idempotency_key: clientUuid + ':order.create'
					});
				})
				.then(function () {
					return record;
				});
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
		return enqueueLocal('delivery', 'create', payload, 'DEL');
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

	function redirectAfter(kind) {
		var url = cfg().homeUrl || window.location.href;
		if (kind === 'customer.create') {
			url = cfg().measurementUrl || url;
		} else if (kind === 'customer.update') {
			url = cfg().customerUrl || url;
		} else if (kind === 'cash.create' || kind === 'cash.pending_pay') {
			url = cfg().cashUrl || window.location.pathname.replace(/\/process.*/, '') || url;
		} else if (kind === 'delivery.create') {
			url = cfg().deliveryUrl || cfg().homeUrl || url;
		} else if (kind.indexOf('transfer') === 0) {
			url = cfg().transferUrl || cfg().homeUrl || url;
		}
		window.setTimeout(function () {
			window.location.href = url;
		}, 600);
	}

	function onSubmit(event) {
		var form = event.target;
		if (!form || form.tagName !== 'FORM') {
			return;
		}
		if (form.getAttribute('data-offline-bypass') === '1') {
			return;
		}

		var action = form.getAttribute('action') || window.location.href;

		if (isOnline()) {
			return;
		}

		if (isOnlineOnlyAction(action)) {
			event.preventDefault();
			event.stopPropagation();
			blockOnlineOnly();
			return;
		}

		if (isReportAction(action)) {
			event.preventDefault();
			event.stopPropagation();
			blockReportOffline();
			return;
		}

		var kind = classifyForm(form);
		if (!kind) {
			event.preventDefault();
			event.stopPropagation();
			flashOfflineSaved(
				t(
					'offline_form_unsupported',
					'This form is not available offline.'
				)
			);
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		var payload = formToObject(form);

		var saver;
		if (kind === 'customer.create') {
			saver = saveCustomerCreate(payload);
		} else if (kind === 'customer.update') {
			saver = saveCustomerUpdate(form, payload);
		} else if (kind === 'order.create') {
			saver = saveOrderCreate(payload);
		} else if (kind === 'cash.create') {
			saver = saveCashCreate(payload);
		} else if (kind === 'cash.pending_pay') {
			saver = saveCashPendingPay(payload);
		} else if (kind === 'delivery.create') {
			saver = saveDeliveryCreate(form, payload);
		} else if (kind === 'transfer.process') {
			saver = saveTransferProcess(form, payload);
		} else if (kind === 'transfer.process_bulk') {
			saver = saveTransferBulk(payload);
		} else {
			saver = Promise.reject(new Error('unsupported'));
		}

		saver
			.then(function (record) {
				flashOfflineSaved(
					t('offline_saved_pending', 'Saved Offline — Pending Sync') +
						(record.provisional_no ? ' (' + record.provisional_no + ')' : '')
				);
				window.dispatchEvent(new CustomEvent('darzi-offline-queued'));
				redirectAfter(kind);
			})
			.catch(function (err) {
				flashOfflineSaved(
					t('offline_save_failed', 'Could not save offline: ') + ((err && err.message) || 'error')
				);
			});
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
