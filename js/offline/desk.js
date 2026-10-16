/**
 * Standalone Offline Desk — works without PHP server (cold start).
 */
(function (window, document) {
	'use strict';

	var DEFAULT_CFG = {
		enabled: true,
		apiBase: './index.php/api/erp_sync',
		healthUrl: './index.php/api/erp_sync/health',
		swUrl: './sw.js',
		swScope: './',
		homeUrl: './index.php/Welcome',
		loginUrl: './index.php/Login',
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

	function setStatus(text, mode) {
		var el = $('#desk-status');
		if (!el) {
			return;
		}
		el.textContent = text;
		el.className = 'desk-status is-' + (mode || 'offline');
	}

	function showView(name) {
		$all('[data-desk-view]').forEach(function (v) {
			v.hidden = v.getAttribute('data-desk-view') !== name;
		});
		$all('[data-desk-nav]').forEach(function (b) {
			b.classList.toggle('is-active', b.getAttribute('data-desk-nav') === name);
		});
		if (name === 'customers') {
			refreshCustomers();
		}
		if (name === 'orders') {
			refreshOrders();
			fillCustomerSelects();
			fillMeasurementSelect();
		}
		if (name === 'cash' || name === 'delivery' || name === 'transfer') {
			fillCustomerSelects();
		}
		if (name === 'queue') {
			refreshQueue();
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
				list.innerHTML = '<p class="desk-empty">Abhi koi customer cache/pending nahi. Naya add karo.</p>';
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
				list.innerHTML = '<p class="desk-empty">Koi order nahi. Naya order banao.</p>';
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
				list.innerHTML = '<p class="desk-empty">Queue khali — sab sync ho chuka ya abhi kuch save nahi.</p>';
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
					toast('Server abhi offline hai — pehle local save raho, baad mein Sync.', 'offline');
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
							toast('Sync ke liye pehle full ERP login chahiye. Local data safe hai.', 'sync_failed');
							setStatus('Online · Login needed for sync', 'pending');
						} else {
							toast('Sync failed: ' + msg, 'sync_failed');
							setStatus('Sync failed', 'offline');
						}
						updatePendingBadge();
					});
			});
		}

		var openErp = $('#btn-open-erp');
		if (openErp) {
			openErp.addEventListener('click', function () {
				window.location.href = cfg.homeUrl || DEFAULT_CFG.homeUrl;
			});
		}

		var openLogin = $('#btn-open-login');
		if (openLogin) {
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
				bindForms();
				showView('home');
				updatePendingBadge();

				window.DarziOffline.connectivity.onChange(function (st) {
					if (st.status === 'online') {
						setStatus('Online — Sync Now se pending bhejo', 'online');
						tryOpenFullErp(cfg);
					} else if (st.status === 'checking') {
						setStatus('Checking server…', 'checking');
					} else {
						setStatus('Offline mode — server ke baghair kaam chal raha hai', 'offline');
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
