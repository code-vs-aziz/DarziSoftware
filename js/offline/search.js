/**
 * Offline customer search for the same ERP home suggest UI.
 * When offline (or suggest XHR fails), answers from IndexedDB.
 */
(function (window) {
	'use strict';

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function isOnline() {
		return window.DarziOffline &&
			window.DarziOffline.connectivity &&
			window.DarziOffline.connectivity.isOnline();
	}

	function norm(s) {
		return String(s || '')
			.toLowerCase()
			.replace(/\s+/g, ' ')
			.trim();
	}

	function digits(s) {
		return String(s || '').replace(/\D+/g, '');
	}

	function matchCustomer(c, q) {
		var nq = norm(q);
		var dq = digits(q);
		if (!nq) {
			return false;
		}
		var hay = norm(
			[c.name, c.man_napno, c.provisional_no, c.Phone, c.phone, c.cnic, c.Address, c.city].join(' ')
		);
		if (hay.indexOf(nq) !== -1) {
			return true;
		}
		if (dq.length >= 3) {
			var phoneDigits = digits(c.Phone || c.phone || '');
			var naapDigits = digits(c.man_napno || c.provisional_no || '');
			var cnicDigits = digits(c.cnic || '');
			if (
				phoneDigits.indexOf(dq) !== -1 ||
				naapDigits.indexOf(dq) !== -1 ||
				cnicDigits.indexOf(dq) !== -1
			) {
				return true;
			}
		}
		return false;
	}

	function searchCustomers(q, limit) {
		limit = limit || 50;
		if (!window.DarziOffline || !window.DarziOffline.db) {
			return Promise.resolve([]);
		}
		return Promise.all([
			window.DarziOffline.db.getAll('customers'),
			window.DarziOffline.db.getAll('measurements')
		]).then(function (parts) {
			var customers = parts[0] || [];
			var measurements = parts[1] || [];
			var measByCustomer = {};
			measurements.forEach(function (m) {
				var cc = String(m.customer_code || (m.raw && m.raw.customer_code) || '');
				if (!cc) {
					return;
				}
				var code = parseInt(m.server_id || (m.raw && m.raw.code) || '0', 10) || 0;
				if (!measByCustomer[cc] || code > measByCustomer[cc]) {
					measByCustomer[cc] = code;
				}
			});

			var hits = customers.filter(function (c) {
				return matchCustomer(c, q);
			});

			hits.sort(function (a, b) {
				return String(b.updated_at || '').localeCompare(String(a.updated_at || ''));
			});

			return hits.slice(0, limit).map(function (c) {
				var code = parseInt(c.server_id || '0', 10) || 0;
				var measCode = code ? measByCustomer[String(code)] || 0 : 0;
				return {
					code: code,
					name: c.name || '',
					phone: c.Phone || c.phone || '',
					naap: c.man_napno || c.provisional_no || '',
					has_measurement: measCode > 0,
					measurement_code: measCode,
					client_uuid: c.client_uuid || '',
					offline: true
				};
			}).filter(function (r) {
				// Suggest needs a server customer code to open measurement URLs.
				return r.code > 0;
			});
		});
	}

	function isSuggestUrl(url) {
		try {
			var u = String(url || '');
			return /\/customer\/search_suggest/i.test(u);
		} catch (e) {
			return false;
		}
	}

	function patchXhr() {
		if (patchXhr._done || !window.XMLHttpRequest) {
			return;
		}
		patchXhr._done = true;
		var Proto = window.XMLHttpRequest.prototype;
		var rawOpen = Proto.open;
		var rawSend = Proto.send;

		Proto.open = function (method, url) {
			this.__darziUrl = url;
			this.__darziMethod = method;
			return rawOpen.apply(this, arguments);
		};

		Proto.send = function () {
			var xhr = this;
			var url = xhr.__darziUrl || '';
			if (!isSuggestUrl(url)) {
				return rawSend.apply(this, arguments);
			}

			var q = '';
			try {
				q = new URL(url, window.location.href).searchParams.get('q') || '';
			} catch (e) {
				var m = String(url).match(/[?&]q=([^&]*)/);
				q = m ? decodeURIComponent(m[1]) : '';
			}

			function serveLocal() {
				searchCustomers(q, 50).then(function (results) {
					if ((!results || !results.length) && window.DarziOffline && window.DarziOffline.sync) {
						window.DarziOffline.sync.countCached().then(function (counts) {
							if (!counts.customers) {
								window.dispatchEvent(
									new CustomEvent('darzi-offline-data', {
										detail: {
											phase: 'error',
											percent: 0,
											customers: 0,
											error: 'no_cache'
										}
									})
								);
							}
						});
					}
					var body = JSON.stringify({
						ok: true,
						results: results,
						offline: true,
						empty_reason: results.length ? '' : 'no_offline_match_or_cache'
					});
					Object.defineProperty(xhr, 'readyState', { configurable: true, get: function () { return 4; } });
					Object.defineProperty(xhr, 'status', { configurable: true, get: function () { return 200; } });
					Object.defineProperty(xhr, 'responseText', { configurable: true, get: function () { return body; } });
					Object.defineProperty(xhr, 'response', { configurable: true, get: function () { return body; } });
					if (typeof xhr.onreadystatechange === 'function') {
						xhr.onreadystatechange();
					}
					if (typeof xhr.onload === 'function') {
						xhr.onload();
					}
				});
			}

			if (!isOnline()) {
				serveLocal();
				return;
			}

			var rawOnReady = xhr.onreadystatechange;
			var rawOnError = xhr.onerror;
			var rawOnLoad = xhr.onload;
			var settled = false;

			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status !== 200 && !settled) {
					settled = true;
					serveLocal();
					return;
				}
				if (typeof rawOnReady === 'function') {
					rawOnReady.apply(xhr, arguments);
				}
			};
			xhr.onerror = function () {
				if (!settled) {
					settled = true;
					serveLocal();
				}
				if (typeof rawOnError === 'function') {
					rawOnError.apply(xhr, arguments);
				}
			};
			xhr.onload = function () {
				if (typeof rawOnLoad === 'function') {
					rawOnLoad.apply(xhr, arguments);
				}
			};

			try {
				return rawSend.apply(this, arguments);
			} catch (err) {
				serveLocal();
			}
		};
	}

	function bind() {
		if (!cfg().enabled) {
			return;
		}
		patchXhr();
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.search = {
		bind: bind,
		searchCustomers: searchCustomers
	};
})(window);
