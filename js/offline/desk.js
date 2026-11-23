/**
 * Standalone Offline Desk — works without PHP server (cold start).
 */
(function (window, document) {
	'use strict';

	var DEFAULT_CFG = {
		enabled: true,
		// Absolute paths — relative ./index.php/... becomes /index.php/index.php/... under CI URLs.
		apiBase: '/index.php/api/erp_sync',
		healthUrl: '/index.php/api/erp_sync/health',
		swUrl: '/sw.js?v=15',
		swScope: '/',
		homeUrl: '/index.php/Welcome',
		loginUrl: '/index.php/Login',
		offlineApp: true
	};

	function $(sel, root) {
		return (root || document).querySelector(sel);
	}

	function $all(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function toast(msg, mode) {
		if (window.DarziOffline && window.DarziOffline.ui && window.DarziOffline.ui.toast) {
			window.DarziOffline.ui.toast(msg, mode || 'pending');
			return;
		}
		window.alert(msg);
	}

	function loadCfg() {
		return window.DarziOffline.db.getMeta('cfg_snapshot', null).then(function (snap) {
			var cfg = Object.assign({}, DEFAULT_CFG, snap || {}, window.DARZI_OFFLINE_CFG || {});
			window.DARZI_OFFLINE_CFG = cfg;
			return cfg;
		});
	}

	function registerSw(cfg) {
		if (!('serviceWorker' in navigator) || !cfg.swUrl) {
			return Promise.resolve();
		}
		return navigator.serviceWorker.register(cfg.swUrl, { scope: cfg.swScope || './' }).catch(function () {
			return null;
		});
	}

	var VIEW_TITLES = {
		home: 'Home',
		customers: 'Customer',
		measurement: 'Measurement',
		orders: 'Order',
		cash: 'Cash',
		delivery: 'Delivery',
		transfer: 'Transfer',
		queue: 'Sync Queue'
	};

	var currentCustomer = null;
	var currentMeasurement = null;

	function setStatus(text, mode) {
		var el = $('#desk-status');
		if (!el) {
			return;
		}
		var label = el.querySelector('.dz-offline-chip__label');
		if (label) {
			label.textContent = text;
		} else {
			el.textContent = text;
		}
		el.className =
			'dz-offline-chip dz-offline-chip--desktop desk-status is-' + (mode || 'offline');
	}

	function showView(name) {
		$all('[data-desk-view]').forEach(function (v) {
			v.hidden = v.getAttribute('data-desk-view') !== name;
		});
		$all('[data-desk-nav]').forEach(function (b) {
			var active = b.getAttribute('data-desk-nav') === name;
			b.classList.toggle('is-active', active);
			var li = b.closest ? b.closest('.dz-nav__item') : null;
			if (li) {
				li.classList.toggle('is-active', active);
			}
		});
		var title = VIEW_TITLES[name] || name;
		var heading = $('#desk-heading');
		var crumb = $('#desk-page-title');
		if (heading) {
			heading.textContent = title;
		}
		if (crumb) {
			crumb.textContent = title;
		}
		document.body.classList.toggle('erp-home', name === 'home');
		document.body.classList.toggle('erp-form-page', name !== 'home');
		document.body.classList.toggle('measurement-form-page', name === 'measurement');

		if (name === 'customers') {
			refreshCustomers();
		}
		if (name === 'orders') {
			refreshOrders();
			fillCustomerSelects();
			fillMeasurementSelect();
			if (currentCustomer && currentCustomer.code) {
				var oc = $('#order-customer');
				if (oc) {
					oc.value = String(currentCustomer.code);
				}
			}
			if (currentMeasurement && (currentMeasurement.server_id || currentMeasurement.code)) {
				var on = $('#order-naap');
				if (on) {
					on.value = String(currentMeasurement.server_id || currentMeasurement.code);
				}
			}
		}
		if (name === 'cash' || name === 'delivery' || name === 'transfer') {
			fillCustomerSelects();
		}
		if (name === 'queue') {
			refreshQueue();
		}
	}

	function setField(id, value) {
		var el = $('#' + id);
		if (el) {
			el.value = value == null ? '' : String(value);
		}
	}

	function pickMeas(raw, key) {
		if (!raw) {
			return '';
		}
		if (raw[key] != null && raw[key] !== '') {
			return raw[key];
		}
		return '';
	}

	function openMeasurementForCustomer(row) {
		currentCustomer = row || null;
		var code = row && row.code ? String(row.code) : '';
		// Prefer real Full ERP measurement page when it was cached online.
		if (code && row && row.has_measurement) {
			window.location.href = '/index.php/Measurement/measurement_edit/' + code + '?return=order';
			return;
		}
		if (code) {
			window.location.href = '/index.php/Measurement/measurement_add/' + code + '?return=order';
			return;
		}
		setField('meas-customer-code', code);
		var nameEl = $('#meas-cust-name');
		var phoneEl = $('#meas-cust-phone');
		var naapEl = $('#meas-cust-naap');
		if (nameEl) nameEl.textContent = (row && row.name) || '—';
		if (phoneEl) phoneEl.textContent = (row && row.phone) || '—';
		if (naapEl) naapEl.textContent = (row && row.naap) || '—';
		var naapLabel = $('#meas-naap-label');
		if (naapLabel) {
			naapLabel.textContent = row && row.naap ? 'Naap: ' + row.naap : '';
		}

		window.DarziOffline.db.getAll('measurements').then(function (rows) {
			var match = null;
			(rows || []).forEach(function (m) {
				if (String(m.customer_code || '') === code) {
					if (!match || String(m.server_id || '') > String(match.server_id || '')) {
						match = m;
					}
				}
			});
			currentMeasurement = match;
			var raw = (match && (match.raw || match.payload)) || {};
			setField('meas-server-id', match && match.server_id ? match.server_id : '');
			setField('meas-KL', pickMeas(raw, 'KL'));
			setField('meas-Teera', pickMeas(raw, 'Teera'));
			setField('meas-BL', pickMeas(raw, 'BL'));
			setField('meas-Chaati', pickMeas(raw, 'Chaati'));
			setField('meas-Gala', pickMeas(raw, 'Gala'));
			setField('meas-geera', pickMeas(raw, 'geera'));
			setField('meas-Shalwar_Lenght', pickMeas(raw, 'Shalwar_Lenght'));
			setField('meas-Poncha', pickMeas(raw, 'Poncha'));
			setField(
				'meas-RemarksKurta',
				pickMeas(raw, 'RemarksKurta') || pickMeas(raw, 'remarks_kurta') || pickMeas(raw, 'remarks')
			);
			showView('measurement');
		});
	}

	function renderHomeSuggest(results, q) {
		var box = $('#erp-home-suggest');
		var home = document.querySelector('.dz-home');
		var input = $('#erp-home-search');
		if (!box) {
			return;
		}
		if (!results.length) {
			box.innerHTML =
				'<div class="dz-home__suggest-empty">' +
				'<div class="dz-home__suggest-empty-title">No customer found</div>' +
				'<div class="dz-home__suggest-empty-hint">Create a new customer, then add measurement.</div>' +
				'<button type="button" class="dz-home__suggest-add" data-desk-nav="customers">+ Create New Customer</button>' +
				'</div>';
			box.hidden = false;
			if (home) home.classList.add('is-suggest-open');
			if (input) input.setAttribute('aria-expanded', 'true');
			return;
		}
		box.innerHTML = results
			.map(function (r, idx) {
				return (
					'<button type="button" class="dz-home__suggest-item" role="option" data-suggest-idx="' +
					idx +
					'">' +
					'<span class="dz-home__suggest-name">' +
					escapeHtml(r.name || '—') +
					'</span>' +
					'<span class="dz-home__suggest-meta">' +
					escapeHtml((r.phone || '') + (r.naap ? ' · Naap ' + r.naap : '')) +
					(r.has_measurement ? ' · Measurement' : ' · New measurement') +
					'</span>' +
					'</button>'
				);
			})
			.join('');
		box.hidden = false;
		if (home) home.classList.add('is-suggest-open');
		if (input) input.setAttribute('aria-expanded', 'true');
		box._results = results;
	}

	function bindHomeSearch() {
		var form = $('#erp-home-search-form');
		var input = $('#erp-home-search');
		var box = $('#erp-home-suggest');
		var timer = null;
		if (!form || !input) {
			return;
		}

		function runSearch(term) {
			var q = String(term || '').trim();
			if (q.length < 2) {
				if (box) {
					box.hidden = true;
					box.innerHTML = '';
				}
				var home = document.querySelector('.dz-home');
				if (home) home.classList.remove('is-suggest-open');
				return;
			}
			var searchFn =
				window.DarziOffline.search && window.DarziOffline.search.searchCustomers
					? window.DarziOffline.search.searchCustomers
					: null;
			if (!searchFn) {
				toast('Offline search is not ready. Open Full ERP online once.', 'sync_failed');
				return;
			}
			searchFn(q, 50).then(function (rows) {
				renderHomeSuggest(rows || [], q);
			});
		}

		input.addEventListener('input', function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				runSearch(input.value);
			}, 180);
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			runSearch(input.value);
			var results = (box && box._results) || [];
			if (results.length === 1) {
				openMeasurementForCustomer(results[0]);
			}
		});

		if (box) {
			box.addEventListener('click', function (e) {
				var btn = e.target && e.target.closest ? e.target.closest('[data-suggest-idx]') : null;
				if (!btn) {
					return;
				}
				var idx = parseInt(btn.getAttribute('data-suggest-idx'), 10);
				var results = box._results || [];
				if (results[idx]) {
					openMeasurementForCustomer(results[idx]);
				}
			});
		}

		var addBtn = $('#erp-home-add-customer');
		if (addBtn) {
			addBtn.addEventListener('click', function () {
				showView('customers');
			});
		}
	}

	function refreshCustomers() {
		var list = $('#customer-list');
		if (!list) {
			return;
		}
		window.DarziOffline.db.getAll('customers').then(function (rows) {
			rows = (rows || []).slice().sort(function (a, b) {
				return String(b.updated_at || '').localeCompare(String(a.updated_at || ''));
			});
			if (!rows.length) {
				list.innerHTML = '<p class="desk-empty">No customers cached yet. Add one above.</p>';
				return;
			}
			list.innerHTML = rows
				.slice(0, 80)
				.map(function (c) {
					var badge = c.sync_status === 'pending' ? '<span class="desk-badge">pending</span>' : '';
					return (
						'<div class="desk-row">' +
						'<div><strong>' +
						escapeHtml(c.name || '—') +
						'</strong> ' +
						badge +
						'<div class="desk-muted">' +
						escapeHtml(c.man_napno || c.provisional_no || '') +
						' · ' +
						escapeHtml(c.Phone || '') +
						'</div></div></div>'
					);
				})
				.join('');
		});
	}

	function refreshOrders() {
		var list = $('#order-list');
		if (!list) {
			return;
		}
		window.DarziOffline.db.getAll('orders').then(function (rows) {
			rows = (rows || []).slice().sort(function (a, b) {
				return String(b.updated_at || '').localeCompare(String(a.updated_at || ''));
			});
			if (!rows.length) {
				list.innerHTML = '<p class="desk-empty">No orders yet. Create one above.</p>';
				return;
			}
			list.innerHTML = rows
				.slice(0, 60)
				.map(function (o) {
					var badge = o.sync_status === 'pending' ? '<span class="desk-badge">pending</span>' : '';
					return (
						'<div class="desk-row"><div><strong>' +
						escapeHtml(o.provisional_no || o.server_id || '—') +
						'</strong> ' +
						badge +
						'<div class="desk-muted">' +
						escapeHtml(o.customer_name || '') +
						' · naap ' +
						escapeHtml(String(o.naap_no || '')) +
						'</div></div></div>'
					);
				})
				.join('');
		});
	}

	function refreshQueue() {
		var list = $('#queue-list');
		var countEl = $('#pending-count');
		window.DarziOffline.db.pendingOps().then(function (ops) {
			if (countEl) {
				countEl.textContent = String(ops.length);
			}
			if (!ops.length) {
				list.innerHTML = '<p class="desk-empty">Queue is empty — nothing pending to sync.</p>';
				return;
			}
			list.innerHTML = ops
				.map(function (op) {
					return (
						'<div class="desk-row"><div><strong>' +
						escapeHtml(op.entity_type + '.' + op.op_type) +
						'</strong>' +
						'<div class="desk-muted">' +
						escapeHtml(op.created_at || '') +
						(op.last_error ? ' · ' + escapeHtml(op.last_error) : '') +
						'</div></div></div>'
					);
				})
				.join('');
		});
	}

	function fillCustomerSelects() {
		window.DarziOffline.db.getAll('customers').then(function (rows) {
			var opts =
				'<option value="">Customer select…</option>' +
				(rows || [])
					.map(function (c) {
						var id = c.server_id || c.client_uuid || '';
						var label = (c.name || '—') + ' (' + (c.man_napno || c.provisional_no || id) + ')';
						return '<option value="' + escapeAttr(id) + '" data-uuid="' + escapeAttr(c.client_uuid || '') + '">' + escapeHtml(label) + '</option>';
					})
					.join('');
			$all('select[data-customer-select]').forEach(function (sel) {
				var prev = sel.value;
				sel.innerHTML = opts;
				if (prev) {
					sel.value = prev;
				}
			});
		});
	}

	function fillMeasurementSelect() {
		var sel = $('#order-naap');
		if (!sel) {
			return;
		}
		window.DarziOffline.db.getAll('measurements').then(function (rows) {
			sel.innerHTML =
				'<option value="">Measurement / Naap…</option>' +
				(rows || [])
					.map(function (m) {
						var id = m.server_id || '';
						var label = 'Naap #' + id + (m.customer_code ? ' · cust ' + m.customer_code : '');
						return '<option value="' + escapeAttr(id) + '">' + escapeHtml(label) + '</option>';
					})
					.join('');
		});
	}

	function escapeHtml(s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function escapeAttr(s) {
		return escapeHtml(s).replace(/'/g, '&#39;');
	}

	function bindForms() {
		var measForm = $('#form-measurement');
		if (measForm) {
			measForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(measForm);
				var payload = {};
				fd.forEach(function (v, k) {
					payload[k] = v;
				});
				window.DarziOffline.deskOps
					.saveMeasurement(payload)
					.then(function (rec) {
						currentMeasurement = rec;
						toast('Measurement saved offline', 'pending');
						refreshQueue();
						updatePendingBadge();
					})
					.catch(function (err) {
						toast('Measurement save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}
		var toOrder = $('#meas-to-order-btn');
		if (toOrder) {
			toOrder.addEventListener('click', function () {
				showView('orders');
			});
		}

		var custForm = $('#form-customer');
		if (custForm) {
			custForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(custForm);
				window.DarziOffline.deskOps
					.saveCustomer({
						name: fd.get('name'),
						Phone: fd.get('phone'),
						city: fd.get('city'),
						Address: fd.get('address'),
						man_napno: fd.get('man_napno'),
						cnic: fd.get('cnic'),
						remarks: fd.get('remarks')
					})
					.then(function (rec) {
						toast('Customer saved offline: ' + (rec.provisional_no || ''), 'pending');
						custForm.reset();
						refreshCustomers();
						refreshQueue();
						updatePendingBadge();
						if (rec && rec.server_id) {
							openMeasurementForCustomer({
								code: parseInt(rec.server_id, 10) || 0,
								name: rec.name || '',
								phone: rec.Phone || '',
								naap: rec.man_napno || '',
								has_measurement: false
							});
						} else {
							showView('home');
							toast('Customer queued. After Sync, search again for measurement.', 'pending');
						}
					})
					.catch(function (err) {
						toast('Save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}

		var orderForm = $('#form-order');
		if (orderForm) {
			orderForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(orderForm);
				var suit = fd.get('suit') || '1';
				var qty = fd.get('qty') || '1';
				var rate = fd.get('rate') || '0';
				var total = parseFloat(rate) * parseFloat(qty || '1');
				var custSel = $('#order-customer');
				var custName = custSel && custSel.selectedOptions[0] ? custSel.selectedOptions[0].textContent : '';
				var payload = {
					Naapno: fd.get('naap_no') || '',
					naap_no: fd.get('naap_no') || '',
					customer_name: custName,
					customer_code: fd.get('customer_id') || '',
					Advance: fd.get('advance') || '0',
					Discount: fd.get('discount') || '0',
					retrun_Date: fd.get('return_date') || '',
					Quantity: qty,
					total_amount: total,
					total: total,
					desigtype: [fd.get('suit_type') || 'Kurta'],
					suit: [suit],
					rate: [rate],
					itotal: [String(total)],
					Remarks: [fd.get('remarks') || '']
				};
				window.DarziOffline.deskOps
					.saveOrder(payload)
					.then(function (rec) {
						toast('Order saved offline: ' + (rec.provisional_no || ''), 'pending');
						orderForm.reset();
						refreshOrders();
						refreshQueue();
						updatePendingBadge();
					})
					.catch(function (err) {
						toast('Order save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}

		var cashForm = $('#form-cash');
		if (cashForm) {
			cashForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(cashForm);
				window.DarziOffline.deskOps
					.saveCash({
						type: fd.get('type') || '2',
						Customer: fd.get('customer_id') || '',
						Employee: fd.get('employee') || '',
						paid: fd.get('amount') || '0',
						amount: fd.get('amount') || '0',
						Remarks: fd.get('remarks') || '',
						order_number: fd.get('order_no') || ''
					})
					.then(function (rec) {
						toast('Cash saved offline: ' + (rec.provisional_no || ''), 'pending');
						cashForm.reset();
						refreshQueue();
						updatePendingBadge();
					})
					.catch(function (err) {
						toast('Cash save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}

		var delForm = $('#form-delivery');
		if (delForm) {
			delForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(delForm);
				var orderId = fd.get('order_id') || '';
				var sr = fd.get('order_sr') || '1';
				window.DarziOffline.deskOps
					.saveDelivery({
						order_id: orderId,
						customer_code: fd.get('customer_id') || '',
						Balance: fd.get('amount') || '0',
						total_Amount: fd.get('total') || fd.get('amount') || '0',
						Advance: fd.get('advance') || '0',
						Discount: fd.get('discount') || '0',
						paid_Date: fd.get('paid_date') || new Date().toISOString().slice(0, 10),
						udaar: fd.get('udaar') || '',
						order_sr: [sr],
						returndel: ['yes']
					})
					.then(function (rec) {
						toast('Delivery queued: ' + (rec.provisional_no || ''), 'pending');
						delForm.reset();
						refreshQueue();
						updatePendingBadge();
					})
					.catch(function (err) {
						toast('Delivery save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}

		var trForm = $('#form-transfer');
		if (trForm) {
			trForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(trForm);
				window.DarziOffline.deskOps
					.saveTransfer({
						transfer_trnno: fd.get('trnno') || '',
						dept: fd.get('dept') || 'HO',
						sr: fd.get('sr') || '',
						order_id: fd.get('order_id') || '',
						EMPLOYEE: fd.get('employee') || '',
						Rate: fd.get('rate') || '',
						RDATE: fd.get('rdate') || new Date().toISOString().slice(0, 10)
					})
					.then(function (rec) {
						toast('Transfer queued: ' + (rec.provisional_no || ''), 'pending');
						trForm.reset();
						refreshQueue();
						updatePendingBadge();
					})
					.catch(function (err) {
						toast('Transfer save failed: ' + ((err && err.message) || 'error'), 'sync_failed');
					});
			});
		}
	}

	function updatePendingBadge() {
		window.DarziOffline.db.countPending().then(function (n) {
			var el = $('#pending-count');
			if (el) {
				el.textContent = String(n);
			}
			var pill = $('#nav-pending-pill');
			if (pill) {
				pill.textContent = n > 0 ? String(n) : '';
				pill.hidden = n < 1;
			}
		});
	}

	function tryOpenFullErp(cfg) {
		var stay = /[?&]stay=1/.test(window.location.search);
		if (stay) {
			return;
		}
		if (!window.DarziOffline.connectivity.isOnline()) {
			return;
		}
		// Online: offer full ERP, don't force redirect every time if user opened desk intentionally
		var force = /[?&]go=erp/.test(window.location.search);
		if (force) {
			window.location.href = cfg.homeUrl || DEFAULT_CFG.homeUrl;
		}
	}

	function bindChrome(cfg) {
		$all('[data-desk-nav]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				showView(btn.getAttribute('data-desk-nav'));
			});
		});

		var syncBtn = $('#btn-sync');
		if (syncBtn) {
			syncBtn.addEventListener('click', function () {
				if (!window.DarziOffline.connectivity.isOnline()) {
					toast('Server is offline. Keep saving locally, then Sync later.', 'offline');
					return;
				}
				setStatus('Syncing…', 'syncing');
				window.DarziOffline.sync
					.syncNow({ forceToken: false })
					.then(function () {
						toast('Sync complete', 'synced');
						setStatus('Online · Synced', 'online');
						refreshQueue();
						refreshCustomers();
						refreshOrders();
						updatePendingBadge();
					})
					.catch(function (err) {
						var msg = (err && err.message) || 'sync_failed';
						if (msg === 'login_required' || msg.indexOf('unauthorized') !== -1) {
							toast('Sign in to Full ERP once online, then Sync. Local data is safe.', 'sync_failed');
							setStatus('Online · Login needed', 'pending');
						} else {
							toast('Sync failed: ' + msg, 'sync_failed');
							setStatus('Sync failed', 'offline');
						}
						updatePendingBadge();
					});
			});
		}

		// Full ERP / Login are real links — SW serves cached Full ERP pages when offline.
		var openErp = $('#btn-open-erp');
		if (openErp && openErp.tagName !== 'A') {
			openErp.addEventListener('click', function () {
				window.location.href = cfg.homeUrl || DEFAULT_CFG.homeUrl;
			});
		}

		var openLogin = $('#btn-open-login');
		if (openLogin && openLogin.tagName !== 'A') {
			openLogin.addEventListener('click', function () {
				window.location.href = cfg.loginUrl || DEFAULT_CFG.loginUrl;
			});
		}
	}

	function start() {
		if (!window.DarziOffline || !window.DarziOffline.db) {
			return;
		}

		loadCfg()
			.then(function (cfg) {
				return registerSw(cfg).then(function () {
					return cfg;
				});
			})
			.then(function (cfg) {
				bindChrome(cfg);
				bindHomeSearch();
				bindForms();
				if (window.DarziOffline.search && window.DarziOffline.search.bind) {
					window.DarziOffline.search.bind();
				}
				showView('home');
				updatePendingBadge();

				window.DarziOffline.connectivity.onChange(function (st) {
					if (st.status === 'online') {
						setStatus('Online · Ready to sync', 'online');
						tryOpenFullErp(cfg);
					} else if (st.status === 'checking') {
						setStatus('Checking…', 'checking');
					} else {
						setStatus('Offline · Working locally', 'offline');
					}
					updatePendingBadge();
				});

				return window.DarziOffline.connectivity.start().then(function () {
					if (window.DarziOffline.ui && window.DarziOffline.ui.start) {
						window.DarziOffline.ui.start();
					}
					refreshCustomers();
					refreshOrders();
					refreshQueue();
					fillCustomerSelects();
					fillMeasurementSelect();
				});
			})
			.catch(function (err) {
				setStatus('Offline desk error: ' + ((err && err.message) || 'failed'), 'offline');
			});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})(window, document);
