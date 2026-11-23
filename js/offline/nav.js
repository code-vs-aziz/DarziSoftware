/**
 * Offline navigation for measurement / order-add.
 * Avoids full page reload (SW Welcome fallback) when a customer is clicked.
 */
(function (window, document) {
	'use strict';

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function isOnline() {
		return window.DarziOffline &&
			window.DarziOffline.connectivity &&
			window.DarziOffline.connectivity.isOnline();
	}

	function db() {
		return window.DarziOffline && window.DarziOffline.db;
	}

	function toast(msg) {
		if (window.DarziOffline && window.DarziOffline.ui && window.DarziOffline.ui.toast) {
			window.DarziOffline.ui.toast(msg, 'pending');
			return;
		}
		window.alert(msg);
	}

	function applyHtml(html, url) {
		if (window.AppAjaxShell && typeof window.AppAjaxShell.applyHtml === 'function') {
			window.AppAjaxShell.applyHtml(html, url, true);
			return true;
		}
		// Fallback: write document (keeps offline usable without shell).
		try {
			document.open();
			document.write(html);
			document.close();
			if (url) {
				window.history.pushState({ appAjax: true, url: url }, '', url);
			}
			return true;
		} catch (e) {
			return false;
		}
	}

	function matchExact(urlObj) {
		if (!('caches' in window)) {
			return Promise.resolve(null);
		}
		var candidates = [urlObj.href, urlObj.origin + urlObj.pathname];
		if (urlObj.search) {
			candidates.push(urlObj.origin + urlObj.pathname + urlObj.search);
		}
		function next(i) {
			if (i >= candidates.length) {
				return Promise.resolve(null);
			}
			return caches.match(candidates[i]).then(function (res) {
				if (!res) {
					return next(i + 1);
				}
				return res.text().then(function (html) {
					return html && html.length > 80 ? html : next(i + 1);
				});
			});
		}
		return next(0);
	}

	function findTemplateInCache(kind) {
		// kind: 'measurement' | 'order'
		if (!('caches' in window)) {
			return Promise.resolve(null);
		}
		var re =
			kind === 'order'
				? /\/Order\/order_add1\/\d+/i
				: /\/Measurement\/measurement_(edit|add)\/\d+/i;
		return caches.keys().then(function (keys) {
			var pageKeys = keys.filter(function (k) {
				return /darzi-erp-.*-pages/.test(k) || /pages/.test(k);
			});
			if (!pageKeys.length) {
				pageKeys = keys.slice();
			}
			function scanCache(ci) {
				if (ci >= pageKeys.length) {
					return Promise.resolve(null);
				}
				return caches.open(pageKeys[ci]).then(function (cache) {
					return cache.keys().then(function (reqs) {
						for (var i = 0; i < reqs.length; i++) {
							if (re.test(reqs[i].url)) {
								return cache.match(reqs[i]).then(function (res) {
									return res ? res.text() : null;
								});
							}
						}
						return null;
					});
				}).then(function (html) {
					if (html && html.length > 80) {
						return html;
					}
					return scanCache(ci + 1);
				});
			}
			return scanCache(0);
		});
	}

	function loadCustomer(code) {
		code = String(code || '');
		if (!db() || !code) {
			return Promise.resolve(null);
		}
		return db().getAll('customers').then(function (rows) {
			rows = rows || [];
			for (var i = 0; i < rows.length; i++) {
				if (String(rows[i].server_id || '') === code) {
					return rows[i];
				}
			}
			return null;
		});
	}

	function loadMeasurementForCustomer(code) {
		code = String(code || '');
		if (!db() || !code) {
			return Promise.resolve(null);
		}
		return db().getAll('measurements').then(function (rows) {
			rows = rows || [];
			var best = null;
			var bestCode = 0;
			rows.forEach(function (m) {
				if (String(m.customer_code || (m.raw && m.raw.customer_code) || '') !== code) {
					return;
				}
				var sid = parseInt(m.server_id || (m.raw && (m.raw.MCODE || m.raw.code)) || '0', 10) || 0;
				if (sid >= bestCode) {
					bestCode = sid;
					best = m;
				}
			});
			return best;
		});
	}

	function esc(s) {
		return String(s || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function hydrateMeasurementHtml(html, customer, measurement, targetUrl) {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var code = String((customer && customer.server_id) || '');
		var form = doc.querySelector('form[name="Form"]');
		var raw = (measurement && measurement.raw) || measurement || {};
		var mcode =
			(measurement && measurement.server_id) ||
			raw.MCODE ||
			raw.code ||
			'';

		var cn = doc.querySelector('[name="customerno1"]');
		if (cn && code) {
			cn.setAttribute('value', code);
			cn.value = code;
		}
		var nap = doc.querySelector('[name="man_napno"]');
		if (nap && customer) {
			var nv = customer.man_napno || customer.provisional_no || '';
			nap.setAttribute('value', nv);
			nap.value = nv;
		}

		if (form) {
			var base = (cfg().apiBase || '').replace(/\/api\/erp_sync\/?$/, '') || window.location.origin + '/index.php';
			base = String(base).replace(/\/$/, '');
			if (mcode) {
				form.setAttribute('action', base + '/Measurement/process_edit/' + mcode);
			} else {
				form.setAttribute('action', base + '/Measurement/process');
			}
			// Ensure return_to=order for order flow.
			var ret = form.querySelector('[name="return_to"]');
			if (!ret) {
				ret = doc.createElement('input');
				ret.type = 'hidden';
				ret.name = 'return_to';
				form.insertBefore(ret, form.firstChild);
			}
			ret.value = 'order';
		}

		// Fill measurement fields from offline cache.
		if (raw && typeof raw === 'object') {
			Object.keys(raw).forEach(function (key) {
				if (!key || key === 'code' || key === 'MCODE' || key === 'customer_code') {
					return;
				}
				var el = doc.querySelector('[name="' + key + '"]');
				if (!el) {
					return;
				}
				var val = raw[key] == null ? '' : String(raw[key]);
				if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
					el.value = val;
					el.setAttribute('value', val);
				}
			});
		}

		// Update visible customer info panel labels if present.
		var items = doc.querySelectorAll('.measurement-info-panel__item');
		items.forEach(function (item) {
			var label = (item.querySelector('span') && item.querySelector('span').textContent) || '';
			var strong = item.querySelector('strong');
			if (!strong || !customer) {
				return;
			}
			if (/name|نام/i.test(label)) {
				strong.textContent = customer.name || '';
			} else if (/phone|فون|موبائل/i.test(label)) {
				strong.textContent = customer.Phone || customer.phone || '';
			} else if (/naap|ناپ|manual/i.test(label)) {
				strong.textContent = customer.man_napno || customer.provisional_no || '';
			}
		});

		// Banner so user knows it's offline-hydrated.
		var body = doc.querySelector('.main-content') || doc.body;
		if (body && !doc.getElementById('dz-offline-meas-banner')) {
			var ban = doc.createElement('div');
			ban.id = 'dz-offline-meas-banner';
			ban.className = 'alert alert-info';
			ban.style.cssText = 'margin:0.5rem 1rem;';
			ban.textContent =
				'Offline measurement — save queues for sync. Customer #' +
				code +
				(customer && customer.name ? ' · ' + customer.name : '');
			body.insertBefore(ban, body.firstChild);
		}

		if (targetUrl) {
			var baseTag = doc.querySelector('base');
			if (!baseTag) {
				baseTag = doc.createElement('base');
				if (doc.head) {
					doc.head.insertBefore(baseTag, doc.head.firstChild);
				}
			}
		}

		return '<!DOCTYPE html>\n' + doc.documentElement.outerHTML;
	}

	function hydrateOrderHtml(html, customer) {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var code = String((customer && customer.server_id) || '');
		var cn = doc.querySelector('[name="customerno"], [name="customer_code"], [name="customerno1"]');
		if (cn && code) {
			cn.value = code;
			cn.setAttribute('value', code);
		}
		var body = doc.querySelector('.main-content') || doc.body;
		if (body && !doc.getElementById('dz-offline-order-banner')) {
			var ban = doc.createElement('div');
			ban.id = 'dz-offline-order-banner';
			ban.className = 'alert alert-info';
			ban.style.cssText = 'margin:0.5rem 1rem;';
			ban.textContent =
				'Offline order — submit queues for sync. ' +
				(customer && customer.name ? customer.name : 'Customer #' + code);
			body.insertBefore(ban, body.firstChild);
		}
		return '<!DOCTYPE html>\n' + doc.documentElement.outerHTML;
	}

	function customerCodeFromUrl(urlObj) {
		var m = String(urlObj.pathname || '').match(
			/\/(?:Measurement\/measurement_(?:edit|add)|Order\/order_add1)\/(\d+)/i
		);
		return m ? m[1] : '';
	}

	function isMeasurementUrl(urlObj) {
		return /\/Measurement\/measurement_(edit|add)\//i.test(urlObj.pathname || '');
	}

	function isOrderAddUrl(urlObj) {
		return /\/Order\/order_add1\//i.test(urlObj.pathname || '');
	}

	function open(rawUrl) {
		var url = new URL(rawUrl, window.location.href);

		if (isOnline()) {
			if (window.AppAjaxShell && typeof window.AppAjaxShell.load === 'function') {
				// Measurement/order are full-page preferred; still allow shell.
				window.location.href = url.href;
			} else {
				window.location.href = url.href;
			}
			return Promise.resolve({ ok: true, mode: 'online' });
		}

		if (!isMeasurementUrl(url) && !isOrderAddUrl(url)) {
			// Generic offline page open via shell cache if possible.
			if (window.AppAjaxShell && typeof window.AppAjaxShell.load === 'function') {
				window.AppAjaxShell.load(url.href);
				return Promise.resolve({ ok: true, mode: 'shell' });
			}
			window.location.href = url.href;
			return Promise.resolve({ ok: true, mode: 'location' });
		}

		var code = customerCodeFromUrl(url);
		return matchExact(url)
			.then(function (html) {
				if (html) {
					if (!applyHtml(html, url.href)) {
						throw new Error('apply_failed');
					}
					return { ok: true, mode: 'cache' };
				}
				var kind = isOrderAddUrl(url) ? 'order' : 'measurement';
				return findTemplateInCache(kind).then(function (tpl) {
					if (!tpl) {
						toast(
							'Offline page not ready for this customer yet. Go online → Sync Now, then retry.'
						);
						return { ok: false, reason: 'no_template' };
					}
					return Promise.all([loadCustomer(code), loadMeasurementForCustomer(code)]).then(
						function (parts) {
							var customer = parts[0] || { server_id: code };
							var measurement = parts[1];
							var out =
								kind === 'order'
									? hydrateOrderHtml(tpl, customer)
									: hydrateMeasurementHtml(tpl, customer, measurement, url.href);
							if (!applyHtml(out, url.href)) {
								throw new Error('apply_failed');
							}
							return { ok: true, mode: 'hydrate', customer: code };
						}
					);
				});
			})
			.catch(function (err) {
				toast(
					'Could not open offline: ' + ((err && err.message) || 'error')
				);
				return { ok: false, error: (err && err.message) || 'open_failed' };
			});
	}

	/** Warm measurement/order URLs while online after search hits. */
	function warmUrls(urls) {
		if (!isOnline() || !urls || !urls.length) {
			return;
		}
		urls.slice(0, 12).forEach(function (u, i) {
			window.setTimeout(function () {
				fetch(u, {
					method: 'GET',
					credentials: 'same-origin',
					cache: 'no-store',
					headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
				}).catch(function () {
					/* ignore */
				});
			}, 80 * i);
		});
	}

	/**
	 * Capture suggest clicks even if Welcome HTML is an old cached copy
	 * whose inline go() still does location.href (broken offline).
	 */
	function bindSuggestCapture() {
		if (document.documentElement.getAttribute('data-dz-nav-suggest') === '1') {
			return;
		}
		document.documentElement.setAttribute('data-dz-nav-suggest', '1');
		document.addEventListener(
			'click',
			function (e) {
				if (isOnline()) {
					return;
				}
				var btn = e.target && e.target.closest
					? e.target.closest('.dz-home__suggest-item')
					: null;
				if (!btn) {
					return;
				}
				var url = btn.getAttribute('data-meas-url') || '';
				if (!url) {
					var code = btn.getAttribute('data-customer-code') || '';
					if (code) {
						url =
							'/index.php/Measurement/measurement_edit/' +
							code +
							'?return=order';
					}
				}
				if (!url) {
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				if (typeof e.stopImmediatePropagation === 'function') {
					e.stopImmediatePropagation();
				}
				open(url);
			},
			true
		);
	}

	function bind() {
		if (!cfg().enabled) {
			return;
		}
		bindSuggestCapture();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.nav = {
		open: open,
		warmUrls: warmUrls,
		bind: bind,
		ensureFormTemplates: null, // set below after helper
		isMeasurementUrl: function (u) {
			try {
				return isMeasurementUrl(new URL(u, window.location.href));
			} catch (e) {
				return false;
			}
		}
	};

	/** While online: cache real measurement + order form pages (no manual open). */
	function ensureFormTemplates() {
		if (!isOnline() || !db()) {
			return Promise.resolve({ skipped: true });
		}
		return Promise.all([db().getAll('customers'), db().getAll('measurements')]).then(
			function (parts) {
				var customers = parts[0] || [];
				var measurements = parts[1] || [];
				var hasMeas = {};
				measurements.forEach(function (m) {
					var cc = String(m.customer_code || (m.raw && m.raw.customer_code) || '');
					if (cc) {
						hasMeas[cc] = true;
					}
				});
				var urls = [
					'/index.php/Welcome',
					'/index.php/transfer/order_Status'
				];
				var withM = [];
				var without = [];
				customers.forEach(function (c) {
					var code = parseInt(c.server_id || '0', 10) || 0;
					if (code < 1) {
						return;
					}
					if (hasMeas[String(code)]) {
						withM.push(code);
					} else {
						without.push(code);
					}
				});
				// Warm measurement forms only — do NOT prefetch Order/order_add1
				// (one-time submit tokens go stale and break online Save).
				withM.slice(0, 40).forEach(function (code) {
					urls.push('/index.php/Measurement/measurement_edit/' + code + '?return=order');
				});
				without.slice(0, 20).forEach(function (code) {
					urls.push('/index.php/Measurement/measurement_add/' + code + '?return=order');
				});
				warmUrls(urls);
				return { ok: true, queued: urls.length };
			}
		);
	}

	window.DarziOffline.nav.ensureFormTemplates = ensureFormTemplates;
})(window, document);
