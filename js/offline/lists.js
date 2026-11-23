/**
 * Offline list cache for Transfer tables + Order Status.
 * Auto-downloads while online so WiFi drop still has searchable lists.
 */
(function (window, document) {
	'use strict';

	var warming = false;

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function db() {
		return window.DarziOffline && window.DarziOffline.db;
	}

	function isOnline() {
		return window.DarziOffline &&
			window.DarziOffline.connectivity &&
			window.DarziOffline.connectivity.isOnline();
	}

	function apiUrl(path) {
		var base = (cfg().apiBase || '').replace(/\/$/, '');
		return base + path;
	}

	function authHeaders() {
		var chain =
			window.DarziOffline.sync && window.DarziOffline.sync.ensureAuth
				? window.DarziOffline.sync.ensureAuth(false).catch(function () {
						return null;
				  })
				: Promise.resolve(null);
		return chain.then(function () {
			return Promise.all([
				db().getMeta('sync_token', null),
				db().ensureDeviceId()
			]).then(function (parts) {
				var h = {
					Accept: 'application/json',
					'X-Device-Id': parts[1] || ''
				};
				if (parts[0]) {
					h.Authorization = 'Bearer ' + parts[0];
				}
				return h;
			});
		});
	}

	function setLists(payload) {
		var os = (payload && payload.order_status) || {};
		var transfer = (payload && payload.transfer) || {};
		return db().setMeta('offline_lists', {
			at: new Date().toISOString(),
			transfer: transfer,
			order_status: os,
			counts: {
				current: (os.current && os.current.length) || 0,
				delivered: (os.delivered && os.delivered.length) || 0,
				transfer:
					((transfer.HO && transfer.HO.length) || 0) +
					((transfer.CUT && transfer.CUT.length) || 0) +
					((transfer.HO2 && transfer.HO2.length) || 0) +
					((transfer.STIT && transfer.STIT.length) || 0)
			}
		});
	}

	function getLists() {
		return db().getMeta('offline_lists', null).then(function (v) {
			return v || { transfer: {}, order_status: {}, counts: {} };
		});
	}

	function listCount(lists) {
		var os = (lists && lists.order_status) || {};
		return ((os.current && os.current.length) || 0) + ((os.delivered && os.delivered.length) || 0);
	}

	function warmLists(force) {
		if (!cfg().enabled || !db()) {
			return Promise.resolve({ skipped: true });
		}
		if (!isOnline()) {
			return Promise.resolve({ skipped: true, reason: 'offline' });
		}
		if (warming) {
			return Promise.resolve({ skipped: true, reason: 'running' });
		}
		warming = true;
		window.dispatchEvent(new CustomEvent('darzi-offline-lists', { detail: { phase: 'start' } }));

		return getLists()
			.then(function (cached) {
				var age = cached.at ? Date.now() - Date.parse(cached.at) : Infinity;
				var count = listCount(cached);
				// Always download if empty; otherwise refresh hourly.
				if (!force && count > 0 && cached.at && age < 55 * 60 * 1000) {
					warming = false;
					window.dispatchEvent(
						new CustomEvent('darzi-offline-lists', {
							detail: { phase: 'skipped', counts: cached.counts || {} }
						})
					);
					return { skipped: true, reason: 'fresh', lists: cached };
				}
				return authHeaders().then(function (headers) {
					return fetch(apiUrl('/lists'), {
						method: 'GET',
						credentials: 'same-origin',
						headers: headers,
						cache: 'no-store'
					}).then(function (res) {
						return res.json().catch(function () {
							return {};
						}).then(function (json) {
							if (!res.ok || !json.ok) {
								throw new Error((json && json.error) || 'lists_failed');
							}
							return setLists(json).then(function () {
								warming = false;
								window.dispatchEvent(
									new CustomEvent('darzi-offline-lists', {
										detail: {
											phase: 'done',
											at: json.server_time || new Date().toISOString(),
											counts: json.counts || {}
										}
									})
								);
								return json;
							});
						});
					});
				});
			})
			.catch(function (err) {
				warming = false;
				window.dispatchEvent(
					new CustomEvent('darzi-offline-lists', {
						detail: { phase: 'error', error: (err && err.message) || 'lists_failed' }
					})
				);
				return { ok: false, error: (err && err.message) || 'lists_failed' };
			});
	}

	function transferRows(dept) {
		dept = String(dept || '').toUpperCase();
		return getLists().then(function (lists) {
			var rows = (lists.transfer && lists.transfer[dept]) || [];
			return Array.isArray(rows) ? rows : [];
		});
	}

	function filterTransferRows(rows, search) {
		var q = String(search || '')
			.toLowerCase()
			.replace(/\s+/g, ' ')
			.trim();
		if (!q) {
			return rows.slice();
		}
		return rows.filter(function (cells) {
			var hay = (cells || [])
				.map(function (c) {
					return String(c || '').replace(/<[^>]+>/g, ' ');
				})
				.join(' ')
				.toLowerCase();
			return hay.indexOf(q) !== -1;
		});
	}

	function orderStatusRows(scope) {
		scope = scope === 'delivered' ? 'delivered' : 'current';
		return getLists().then(function (lists) {
			var rows = (lists.order_status && lists.order_status[scope]) || [];
			return Array.isArray(rows) ? rows : [];
		});
	}

	function matchOrderStatusRow(row, filters) {
		filters = filters || {};
		function has(hay, needle) {
			if (!needle) {
				return true;
			}
			return String(hay || '')
				.toLowerCase()
				.indexOf(String(needle).toLowerCase()) !== -1;
		}
		function digits(s) {
			return String(s || '').replace(/\D+/g, '');
		}
		if (filters.dt_q) {
			var dq = String(filters.dt_q).toLowerCase();
			var dqd = digits(filters.dt_q);
			var blob = [
				row.order_no,
				row.naap_no,
				row.phone,
				row.customer,
				row.emp_name,
				row.status_label
			]
				.join(' ')
				.toLowerCase();
			if (blob.indexOf(dq) === -1 && !(dqd && digits(blob).indexOf(dqd) !== -1)) {
				return false;
			}
		}
		if (!has(row.customer, filters.customer_name)) {
			return false;
		}
		if (!has(String(row.order_no || ''), filters.order_no)) {
			return false;
		}
		if (!has(String(row.naap_no || ''), filters.naap_no)) {
			return false;
		}
		if (filters.mobile_no) {
			var md = digits(filters.mobile_no);
			if (md && digits(row.phone).indexOf(md) === -1) {
				return false;
			}
		}
		// Live ERP clears stage when looking up by phone/order/naap — same offline.
		var hasLookup =
			!!filters.dt_q ||
			!!filters.mobile_no ||
			!!filters.order_no ||
			!!filters.naap_no ||
			!!filters.customer_name;
		if (
			!hasLookup &&
			filters.stage &&
			String(row.stage || '').toUpperCase() !== String(filters.stage).toUpperCase()
		) {
			return false;
		}
		return true;
	}

	function liveStatusLabel(row) {
		var s = String((row && row.status_label) || '').trim();
		var stage = String((row && row.stage) || '')
			.toUpperCase()
			.trim();
		var xs = String((row && row.xfer_status) || '').trim();
		var code = s.toUpperCase().replace(/\s+/g, ' ');
		// Human labels (any case) — keep before dept-code mapping.
		if (code === 'READY 4 DELIVER' || code === 'READY FOR DELIVER' || code === 'READY FOR DELIVERY') {
			return 'Ready 4 Deliver';
		}
		if (code === 'BOOKING') {
			return 'Booking';
		}
		if (code === 'CUTTING') {
			return 'Cutting';
		}
		if (code === 'AFTER CUTTING') {
			return 'After Cutting';
		}
		if (code === 'STITCHING') {
			return 'Stitching';
		}
		if (code === 'DELIVERED') {
			return 'Delivered';
		}
		// Old cached lists stored dept codes (HO/CUT/…) as status_label.
		if (code === 'HO' || stage === 'HO') {
			return 'Booking';
		}
		if (code === 'CUT' || stage === 'CUT') {
			return 'Cutting';
		}
		if (code === 'HO2' || stage === 'HO2') {
			return 'After Cutting';
		}
		if (code === 'STIT' || stage === 'STIT') {
			if (xs === 'Deactive' || xs.toLowerCase() === 'deactive') {
				return 'Ready 4 Deliver';
			}
			return 'Stitching';
		}
		if (code === 'DELIVER' || stage === 'DELIVER') {
			return 'Delivered';
		}
		return s || stage;
	}

	/**
	 * While online: merge currently visible Order Status rows into local cache
	 * so the exact search the user just did survives WiFi drop.
	 */
	function mergeVisibleOrderStatusIntoCache() {
		if (!isOnline() || !db()) {
			return Promise.resolve();
		}
		var table = document.querySelector('table#example[data-order-list-table]');
		if (!table) {
			return Promise.resolve();
		}
		var scopeEl = document.getElementById('os-scope-input');
		var scope = scopeEl && String(scopeEl.value).toLowerCase() === 'delivered' ? 'delivered' : 'current';
		var rows = [];
		var trs = table.querySelectorAll('tbody tr');
		for (var i = 0; i < trs.length; i++) {
			var cells = trs[i].querySelectorAll('td');
			if (cells.length < 10) {
				continue;
			}
			var text = String(cells[0].textContent || '').trim();
			if (!text || /no offline|no matching|no data/i.test(text)) {
				continue;
			}
			var scrapedStatus = String(cells[9].textContent || '').trim();
			rows.push({
				order_no: parseInt(String(text).split('-')[0], 10) || 0,
				order_display: text,
				emp_name: String(cells[1].textContent || '').trim(),
				entry_date: String(cells[2].textContent || '').trim(),
				order_date: String(cells[3].textContent || '').trim(),
				return_date: String(cells[4].textContent || '').trim(),
				phone: String(cells[5].textContent || '').trim(),
				customer: String(cells[6].textContent || '').trim(),
				naap_no: String(cells[7].textContent || '').trim(),
				remarks: String(cells[8].textContent || '').trim(),
				status_label: liveStatusLabel({ status_label: scrapedStatus, stage: scrapedStatus }),
				stage: scrapedStatus
			});
		}
		if (!rows.length) {
			return Promise.resolve();
		}
		return getLists().then(function (lists) {
			lists.order_status = lists.order_status || {};
			var existing = Array.isArray(lists.order_status[scope]) ? lists.order_status[scope].slice() : [];
			var byKey = {};
			existing.forEach(function (r) {
				byKey[String(r.order_display || r.order_no)] = r;
			});
			rows.forEach(function (r) {
				byKey[String(r.order_display || r.order_no)] = r;
			});
			lists.order_status[scope] = Object.keys(byKey).map(function (k) {
				return byKey[k];
			});
			lists.at = new Date().toISOString();
			lists.counts = lists.counts || {};
			lists.counts[scope] = lists.order_status[scope].length;
			return db().setMeta('offline_lists', lists);
		});
	}

	function readFormFilters(form) {
		var fd = new FormData(form);
		return {
			customer_name: String(fd.get('customer_name') || '').trim(),
			order_no: String(fd.get('order_no') || '').trim(),
			naap_no: String(fd.get('naap_no') || '').trim(),
			mobile_no: String(fd.get('mobile_no') || '').trim(),
			stage: String(fd.get('stage') || '').trim(),
			dt_q: String(fd.get('dt_q') || '').trim()
		};
	}

	function runOrderStatusOfflineSearch(e) {
		if (isOnline()) {
			return false;
		}
		var form = document.getElementById('os-status-filter-form');
		if (!form) {
			return false;
		}
		if (e) {
			e.preventDefault();
			e.stopPropagation();
			if (typeof e.stopImmediatePropagation === 'function') {
				e.stopImmediatePropagation();
			}
		}
		var filters = readFormFilters(form);
		var scope = String(
			(document.getElementById('os-scope-input') &&
				document.getElementById('os-scope-input').value) ||
				'current'
		).toLowerCase();
		var keep =
			filters.mobile_no ||
			filters.order_no ||
			filters.naap_no ||
			filters.customer_name ||
			filters.dt_q ||
			'';
		renderOrderStatusOffline(scope, filters, { keepSearch: keep });
		return true;
	}

	function bindOrderStatusOffline() {
		// Document-level capture so AJAX-replaced Order Status forms still work.
		if (document.documentElement.getAttribute('data-os-offline-bound') === '1') {
			return;
		}
		document.documentElement.setAttribute('data-os-offline-bound', '1');

		document.addEventListener(
			'submit',
			function (e) {
				var form = e.target;
				if (!form || form.id !== 'os-status-filter-form') {
					return;
				}
				runOrderStatusOfflineSearch(e);
			},
			true
		);

		document.addEventListener(
			'click',
			function (e) {
				var t = e.target;
				if (!t || !t.closest) {
					return;
				}
				var btn = t.closest('#os-status-filter-form button[type="submit"]');
				if (!btn) {
					return;
				}
				runOrderStatusOfflineSearch(e);
			},
			true
		);
	}

	function statusCellHtml(label) {
		var mapped = liveStatusLabel({ status_label: label, stage: label });
		if (mapped === 'Ready 4 Deliver') {
			return '<span class="badge badge-success os-badge--ready">' + esc(mapped) + '</span>';
		}
		if (mapped === 'Delivered') {
			return '<span class="badge badge-secondary os-badge--delivered">' + esc(mapped) + '</span>';
		}
		return esc(mapped || label || '');
	}

	function restoreFormFilters(filters, scope) {
		var form = document.getElementById('os-status-filter-form');
		if (!form || !filters) {
			return;
		}
		var scopeEl = document.getElementById('os-scope-input');
		if (scopeEl && scope) {
			scopeEl.value = scope;
		}
		['customer_name', 'order_no', 'naap_no', 'mobile_no', 'stage'].forEach(function (name) {
			var el = form.querySelector('[name="' + name + '"]');
			if (el && filters[name] != null) {
				el.value = filters[name];
			}
		});
	}

	function renderOrderStatusOffline(scope, filters, opts) {
		opts = opts || {};
		var table = document.querySelector('table#example[data-order-list-table]');
		if (!table || !window.jQuery) {
			return;
		}
		var $ = window.jQuery;
		var keepSearch = opts.keepSearch != null ? String(opts.keepSearch) : '';
		orderStatusRows(scope).then(function (rows) {
			var filtered = rows.filter(function (r) {
				return matchOrderStatusRow(r, filters || {});
			});
			if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
				try {
					$(table).DataTable().clear().destroy();
				} catch (e) {
					try {
						$(table).DataTable().destroy();
					} catch (e2) {
						/* ignore */
					}
				}
			}
			var $tb = $(table).find('tbody');
			$tb.empty();
			if (!filtered.length) {
				var msg =
					rows.length < 1
						? 'Offline lists not downloaded yet. Go online once — lists auto-download.'
						: 'No offline matches for this search.';
				$tb.append(
					'<tr><td colspan="11" class="text-center text-muted">' + msg + '</td></tr>'
				);
			} else {
				filtered.forEach(function (r) {
					$tb.append(
						'<tr>' +
							'<td>' +
							esc(r.order_display || r.order_no || '') +
							'</td>' +
							'<td>' +
							esc(r.emp_name || '') +
							'</td>' +
							'<td>' +
							esc(r.entry_date || '') +
							'</td>' +
							'<td>' +
							esc(r.order_date || '') +
							'</td>' +
							'<td>' +
							esc(r.return_date || '') +
							'</td>' +
							'<td>' +
							esc(r.phone || '') +
							'</td>' +
							'<td>' +
							esc(r.customer || '') +
							'</td>' +
							'<td>' +
							esc(r.naap_no || '') +
							'</td>' +
							'<td>' +
							esc(r.remarks || '') +
							'</td>' +
							'<td>' +
							statusCellHtml(liveStatusLabel(r)) +
							'</td>' +
							'<td></td>' +
							'</tr>'
					);
				});
			}
			restoreFormFilters(filters, scope);
			// Keep phone/order in upper fields before DT rebuild.
			if (keepSearch) {
				var digKeep = String(keepSearch).replace(/\D+/g, '');
				if (digKeep.length >= 10) {
					var mobEl = document.querySelector('#os-status-filter-form [name="mobile_no"]');
					if (mobEl && !String(mobEl.value || '').trim()) {
						mobEl.value = keepSearch;
					}
				}
			}
			if (typeof window.initOrderListDataTable === 'function') {
				window.initOrderListDataTable();
			}
			if (keepSearch) {
				window.setTimeout(function () {
					var $filter = $(table)
						.closest('.dataTables_wrapper')
						.find('.dataTables_filter input');
					if ($filter.length) {
						$filter.val(keepSearch);
						if ($.fn.DataTable.isDataTable(table)) {
							// Rows already filtered offline — show all of them, keep box text.
							$(table).DataTable().search('').draw(false);
						}
						$filter.val(keepSearch);
					}
					if (filters && filters.dt_q && !filters.mobile_no) {
						var mob = document.querySelector('#os-status-filter-form [name="mobile_no"]');
						var dig = String(filters.dt_q).replace(/\D+/g, '');
						if (mob && dig.length >= 10) {
							mob.value = String(filters.dt_q);
						}
					}
				}, 30);
			}
		});
	}

	function esc(s) {
		return String(s || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function bind() {
		if (!cfg().enabled) {
			return;
		}
		bindOrderStatusOffline();
		if (document.querySelector('table#example[data-order-list-table]')) {
			// Always restore Ready green badge (search / offline can strip it).
			ensureOrderStatusBadges();
			window.setTimeout(ensureOrderStatusBadges, 200);
			if (isOnline()) {
				// Capture whatever the server just rendered (including search hits).
				window.setTimeout(function () {
					mergeVisibleOrderStatusIntoCache();
				}, 800);
				// Keep lists fresh in background while user works online.
				warmLists(false);
			} else {
				var tbody = document.querySelector('table#example[data-order-list-table] tbody');
				var trCount = tbody ? tbody.querySelectorAll('tr').length : 0;
				var emptyish =
					trCount < 1 ||
					(trCount === 1 && /no offline|no matching|no data/i.test(tbody.textContent || ''));
				if (emptyish) {
					var scopeEl = document.getElementById('os-scope-input');
					var scope =
						scopeEl && String(scopeEl.value).toLowerCase() === 'delivered' ? 'delivered' : 'current';
					renderOrderStatusOffline(scope, {});
				} else {
					relabelVisibleOrderStatusCodes();
				}
			}
		} else if (isOnline()) {
			warmLists(false);
		}
	}

	/**
	 * Normalize status cells: restore green Ready badge if a prior offline
	 * pass uppercased/stripped HTML, and rewrite leftover HO/CUT codes.
	 */
	function ensureOrderStatusBadges() {
		var table = document.querySelector('table#example[data-order-list-table]');
		if (!table) {
			return;
		}
		var cells = table.querySelectorAll('tbody tr td:nth-child(10)');
		for (var i = 0; i < cells.length; i++) {
			var cell = cells[i];
			var text = String(cell.textContent || '').trim();
			if (!text) {
				continue;
			}
			var mapped = liveStatusLabel({ status_label: text, stage: text });
			var hasReady = !!cell.querySelector('.badge-success, .os-badge--ready');
			var hasDelivered = !!cell.querySelector('.badge-secondary, .os-badge--delivered');
			if (mapped === 'Ready 4 Deliver') {
				if (!hasReady) {
					cell.innerHTML = statusCellHtml(mapped);
				}
				continue;
			}
			if (mapped === 'Delivered') {
				if (!hasDelivered) {
					cell.innerHTML = statusCellHtml(mapped);
				}
				continue;
			}
			// Only rewrite plain dept codes (HO/CUT/…) — never strip a good badge.
			if (/^(HO|CUT|HO2|STIT|DELIVER)$/i.test(text) && mapped && mapped !== text) {
				cell.innerHTML = statusCellHtml(mapped);
			}
		}
	}

	function relabelVisibleOrderStatusCodes() {
		ensureOrderStatusBadges();
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.lists = {
		warm: warmLists,
		get: getLists,
		transferRows: transferRows,
		filterTransferRows: filterTransferRows,
		orderStatusRows: orderStatusRows,
		applyOrderStatusFilters: renderOrderStatusOffline,
		mergeVisible: mergeVisibleOrderStatusIntoCache,
		bind: bind
	};
})(window, document);
