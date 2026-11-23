/**
 * After online login / Sync Now: auto-cache Full ERP pages in background.
 * User does NOT need to open every page manually.
 */
(function (window) {
	'use strict';

	var running = false;
	var MIN_FULL_GAP_MS = 6 * 60 * 60 * 1000; // full re-crawl at most every 6h
	var LOCK_KEY = 'darzi_prefetch_lock';

	// Highest priority first — Order Status + pending tables must be ready offline.
	var CORE_PAGES = [
		'/index.php/Welcome',
		'/index.php/Welcome/dashboard',
		'/index.php/transfer/order_Status',
		'/index.php/Transfer/order_Status',
		'/index.php/transfer/transfer_table/HO/HO',
		'/index.php/transfer/transfer_table/CUT/CUT',
		'/index.php/transfer/transfer_table/HO2/HO2',
		'/index.php/transfer/transfer_table/STIT/STIT',
		'/index.php/Transfer/transfer_table/HO/HO',
		'/index.php/Transfer/transfer_table/CUT/CUT',
		'/index.php/Transfer/transfer_table/HO2/HO2',
		'/index.php/Transfer/transfer_table/STIT/STIT',
		'/index.php/Order/order_issue',
		'/index.php/Order/order_view_det',
		// order_add1 not prefetched — burns one-time submit tokens in session.
		'/index.php/Order/print_blank',
		'/index.php/Customer',
		'/index.php/Customer/Customer_add',
		'/index.php/Measurement',
		'/index.php/Cash',
		'/index.php/Employee',
		'/index.php/Suit',
		'/index.php/Style',
		'/index.php/embroidery',
		'/index.php/Welcome/Report',
		'/index.php/Configuration',
		'/index.php/Login',
		'/offline-app.html'
	];

	var MUST_HAVE = [
		'/index.php/Welcome',
		'/index.php/transfer/order_Status',
		'/index.php/Transfer/order_Status',
		'/index.php/transfer/transfer_table/HO/HO',
		'/index.php/transfer/transfer_table/CUT/CUT',
		'/index.php/transfer/transfer_table/HO2/HO2',
		'/index.php/transfer/transfer_table/STIT/STIT',
		'/index.php/Customer',
		'/index.php/Measurement',
		'/index.php/Cash',
		'/index.php/Order/order_issue'
	];

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function isOnline() {
		return window.DarziOffline &&
			window.DarziOffline.connectivity &&
			window.DarziOffline.connectivity.isOnline();
	}

	function sleep(ms) {
		return new Promise(function (resolve) {
			window.setTimeout(resolve, ms);
		});
	}

	function fetchOne(url) {
		return fetch(url, {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
		}).then(function (res) {
			return { url: url, ok: !!(res && res.ok), status: res ? res.status : 0 };
		}).catch(function () {
			return { url: url, ok: false, status: 0 };
		});
	}

	function tellSw(urls) {
		if (!navigator.serviceWorker || !navigator.serviceWorker.controller) {
			return Promise.resolve();
		}
		try {
			navigator.serviceWorker.controller.postMessage({
				type: 'darzi-prefetch',
				urls: urls
			});
		} catch (e) {
			/* ignore */
		}
		return Promise.resolve();
	}

	function cacheHas(url) {
		if (!('caches' in window)) {
			return Promise.resolve(false);
		}
		var origin = window.location.origin;
		var abs = url.indexOf('http') === 0 ? url : origin + url;
		var bare = abs.split('?')[0];
		return caches.match(abs).then(function (a) {
			if (a) {
				return true;
			}
			return caches.match(bare).then(function (b) {
				return !!b;
			});
		}).catch(function () {
			return false;
		});
	}

	function findMissing(urls) {
		var missing = [];
		var chain = Promise.resolve();
		urls.forEach(function (u) {
			chain = chain.then(function () {
				return cacheHas(u).then(function (ok) {
					if (!ok) {
						missing.push(u);
					}
				});
			});
		});
		return chain.then(function () {
			return missing;
		});
	}

	function customerMeasurementUrls() {
		if (!window.DarziOffline || !window.DarziOffline.db) {
			return Promise.resolve([]);
		}
		return window.DarziOffline.db.getAll('customers').then(function (rows) {
			rows = (rows || []).slice().sort(function (a, b) {
				return String(b.updated_at || b.server_id || '').localeCompare(
					String(a.updated_at || a.server_id || '')
				);
			});
			var urls = [];
			var max = 250;
			for (var i = 0; i < rows.length && urls.length < max * 2; i++) {
				var code = parseInt(rows[i].server_id || '0', 10) || 0;
				if (code < 1) {
					continue;
				}
				urls.push('/index.php/Measurement/measurement_edit/' + code + '?return=order');
				urls.push('/index.php/Measurement/measurement_add/' + code + '?return=order');
				// Skip Order/order_add1 prefetch — stale/one-time tokens break online Save.
			}
			return urls;
		}).catch(function () {
			return [];
		});
	}

	function runQueue(urls, concurrency) {
		concurrency = concurrency || 4;
		var i = 0;
		var ok = 0;
		var fail = 0;

		function worker() {
			if (i >= urls.length) {
				return Promise.resolve();
			}
			var url = urls[i++];
			return fetchOne(url).then(function (r) {
				if (r.ok) {
					ok++;
				} else {
					fail++;
				}
				window.dispatchEvent(
					new CustomEvent('darzi-offline-prefetch', {
						detail: { done: ok + fail, total: urls.length, ok: ok, fail: fail, url: url }
					})
				);
				return sleep(30).then(worker);
			});
		}

		var starters = [];
		for (var c = 0; c < concurrency; c++) {
			starters.push(worker());
		}
		return Promise.all(starters).then(function () {
			return { ok: ok, fail: fail, total: urls.length };
		});
	}

	function readLock() {
		try {
			var raw = window.sessionStorage.getItem(LOCK_KEY);
			if (!raw) {
				return null;
			}
			return JSON.parse(raw);
		} catch (e) {
			return null;
		}
	}

	function writeLock(active) {
		try {
			if (!active) {
				window.sessionStorage.removeItem(LOCK_KEY);
				return;
			}
			window.sessionStorage.setItem(
				LOCK_KEY,
				JSON.stringify({ at: Date.now(), active: true })
			);
		} catch (e) {
			/* ignore */
		}
	}

	function wasRecent(iso, gapMs) {
		if (!iso) {
			return false;
		}
		var t = Date.parse(iso);
		if (!t) {
			return false;
		}
		return Date.now() - t < gapMs;
	}

	function uniq(urls) {
		var seen = {};
		return (urls || []).filter(function (u) {
			if (!u || seen[u]) {
				return false;
			}
			seen[u] = true;
			return true;
		});
	}

	/**
	 * Always fill missing core pages while online.
	 * This is what makes "no need to open every page" true.
	 */
	function ensureCoreCached() {
		if (!cfg().enabled || !isOnline()) {
			return Promise.resolve({ skipped: true, reason: 'offline' });
		}
		return findMissing(MUST_HAVE.concat(CORE_PAGES)).then(function (missing) {
			missing = uniq(missing);
			if (!missing.length) {
				return { skipped: true, reason: 'complete', missing: 0 };
			}
			window.dispatchEvent(
				new CustomEvent('darzi-offline-prefetch', {
					detail: { phase: 'start', mode: 'gapfill', total: missing.length }
				})
			);
			tellSw(missing);
			return runQueue(missing, 4).then(function (summary) {
				window.dispatchEvent(
					new CustomEvent('darzi-offline-prefetch', {
						detail: { phase: 'done', mode: 'gapfill', summary: summary }
					})
				);
				return summary;
			});
		});
	}

	function prefetchFullErp(force) {
		if (!cfg().enabled) {
			return Promise.resolve({ skipped: true });
		}
		if (!isOnline()) {
			return Promise.resolve({ skipped: true, reason: 'offline' });
		}

		// Always patch missing must-have pages first (even if "recent").
		return ensureCoreCached().then(function (gap) {
			if (running && !force) {
				return { skipped: true, reason: 'running', gap: gap };
			}
			var lock = readLock();
			if (!force && lock && lock.active && Date.now() - (lock.at || 0) < 20 * 60 * 1000) {
				return { skipped: true, reason: 'locked', gap: gap };
			}

			var metaP =
				window.DarziOffline && window.DarziOffline.db
					? Promise.all([
							window.DarziOffline.db.getMeta('last_prefetch_at', null),
							window.DarziOffline.db.getMeta('last_prefetch_summary', null)
					  ])
					: Promise.resolve([null, null]);

			return metaP.then(function (parts) {
				var at = parts[0];
				var summary = parts[1] || {};
				var okRatio = summary.total > 0 ? (summary.ok || 0) / summary.total : 0;
				if (!force && wasRecent(at, MIN_FULL_GAP_MS) && okRatio >= 0.7) {
					return { skipped: true, reason: 'recent', at: at, summary: summary, gap: gap };
				}

				running = true;
				writeLock(true);
				window.dispatchEvent(
					new CustomEvent('darzi-offline-prefetch', { detail: { phase: 'start', mode: 'full' } })
				);

				return customerMeasurementUrls()
					.then(function (extra) {
						var urls = uniq(CORE_PAGES.concat(extra || []));
						tellSw(urls);
						return runQueue(urls, 4);
					})
					.then(function (fullSummary) {
						running = false;
						writeLock(false);
						if (window.DarziOffline && window.DarziOffline.db) {
							window.DarziOffline.db.setMeta('last_prefetch_at', new Date().toISOString());
							window.DarziOffline.db.setMeta('last_prefetch_summary', fullSummary);
							window.DarziOffline.db.setMeta('offline_pages_ready', true);
						}
						window.dispatchEvent(
							new CustomEvent('darzi-offline-prefetch', {
								detail: { phase: 'done', mode: 'full', summary: fullSummary }
							})
						);
						return fullSummary;
					})
					.catch(function (err) {
						running = false;
						writeLock(false);
						window.dispatchEvent(
							new CustomEvent('darzi-offline-prefetch', {
								detail: {
									phase: 'error',
									error: (err && err.message) || 'prefetch_failed'
								}
							})
						);
						return { ok: false, error: (err && err.message) || 'prefetch_failed' };
					});
			});
		});
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.prefetch = {
		run: prefetchFullErp,
		ensureCore: ensureCoreCached,
		corePages: CORE_PAGES,
		mustHave: MUST_HAVE
	};
})(window);
