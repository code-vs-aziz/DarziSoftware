/* Darzi Baba ERP — Full ERP offline cache (auto background after login)
 * 1) NEVER intercept POST.
 * 2) Online: network-first HTML (live shop reliable). Offline: same-URL cache.
 * 3) Mutating GETs: network-only — never Welcome / stale page substitute.
 */
/* eslint-disable no-restricted-globals */
var CACHE_VERSION = 'darzi-erp-safe-v37';
var SHELL_CACHE = CACHE_VERSION + '-shell';
var PAGE_CACHE = CACHE_VERSION + '-pages';
var OFFLINE_APP = '/offline-app.html';

var SHELL_URLS = [
	'/offline-app.html',
	'/offline.html',
	'/manifest.webmanifest',
	'/vendor/bootstrap-4.1/bootstrap.min.css',
	'/vendor/font-awesome-5/css/fontawesome-all.min.css?v=5154',
	'/css/dz-design-system.css?v=88',
	'/css/app-unified-theme.css?v=11',
	'/css/offline-erp.css?v=8',
	'/css/offline-desk.css?v=5',
	'/css/measurement-form-enhancements.css?v=13',
	'/css/theme.css',
	'/css/dz-mobile-app.css?v=6',
	'/css/erp-form-enhancements.css?v=5',
	'/js/jquery-3.3.1.js',
	'/js/transfer-bulk-table.js?v=10',
	'/js/order-detail-datatable.js?v=18',
	'/js/app-datatables-init.js?v=3',
	'/js/offline/db.js?v=7',
	'/js/offline/connectivity.js?v=6',
	'/js/offline/sync-engine.js?v=11',
	'/js/offline/search.js?v=3',
	'/js/offline/lists.js?v=8',
	'/js/offline/ui.js?v=18',
	'/js/app-ajax-shell.js?v=35',
	'/js/offline/nav.js?v=3',
	'/js/offline/forms.js?v=11',
	'/js/offline/bootstrap.js?v=20',
	'/js/offline/prefetch.js?v=5',
	'/js/offline/desk-ops.js?v=8',
	'/js/offline/desk.js?v=8',
	'/images/darzi-baba-logo.png?v=5',
	'/images/favicon-32.png',
	'/images/apple-touch-icon.png'
];

var PREFETCH_PAGES = [
	'/index.php/Welcome',
	'/index.php/Welcome/dashboard',
	'/index.php/Welcome/Report',
	'/index.php/Login',
	'/index.php/Customer',
	'/index.php/Customer/Customer_add',
	'/index.php/Measurement',
	'/index.php/Order/order_view_det',
	// order_add1 intentionally NOT prefetched — one-time submit tokens go stale in cache.
	'/index.php/Order/order_issue',
	'/index.php/Order/print_blank',
	'/index.php/Cash',
	'/index.php/Employee',
	'/index.php/Suit',
	'/index.php/Style',
	'/index.php/embroidery',
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
	'/index.php/Configuration',
	'/offline-app.html'
];

self.addEventListener('install', function (event) {
	event.waitUntil(
		caches.open(SHELL_CACHE).then(function (cache) {
			return Promise.all(
				SHELL_URLS.map(function (u) {
					return cache.add(new Request(u, { cache: 'reload' })).catch(function () {
						return null;
					});
				})
			);
		}).then(function () {
			return self.skipWaiting();
		})
	);
});

self.addEventListener('activate', function (event) {
	event.waitUntil(
		caches.keys().then(function (keys) {
			return Promise.all(
				keys
					.filter(function (k) {
						return k.indexOf('darzi-erp-') === 0 && k !== SHELL_CACHE && k !== PAGE_CACHE;
					})
					.map(function (k) {
						return caches.delete(k);
					})
			);
		}).then(function () {
			return self.clients.claim();
		}).then(function () {
			return prefetchUrls(PREFETCH_PAGES);
		})
	);
});

function isNavigate(request) {
	return (
		request.mode === 'navigate' ||
		request.destination === 'document' ||
		(request.method === 'GET' &&
			request.headers.get('accept') &&
			request.headers.get('accept').indexOf('text/html') !== -1)
	);
}

function isStaticAsset(url) {
	return /\.(?:js|css|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|webmanifest)(?:\?|$)/i.test(url.pathname) ||
		/\/offline(?:-app)?\.html$/i.test(url.pathname);
}

function isErpHtmlPath(url) {
	return /\/index\.php\//i.test(url.pathname) || /\/offline(?:-app)?\.html$/i.test(url.pathname);
}

function cachePage(request, response) {
	if (!response || !response.ok) {
		return response;
	}
	var ctype = (response.headers.get('content-type') || '').toLowerCase();
	if (ctype && ctype.indexOf('text/html') === -1 && ctype.indexOf('application/xhtml') === -1) {
		return response;
	}
	var copy = response.clone();
	// Never cache one-shot flash banners (otherwise red/green alerts stick forever offline).
	copy
		.text()
		.then(function (html) {
			if (/data-flash-once|data-app-flash-alert/i.test(html || '')) {
				return null;
			}
			return caches.open(PAGE_CACHE).then(function (cache) {
				var clean = new Response(html, {
					status: response.status,
					statusText: response.statusText,
					headers: response.headers
				});
				cache.put(request, clean.clone());
				try {
					var u = new URL(typeof request === 'string' ? request : request.url);
					cache.put(u.origin + u.pathname, clean);
				} catch (e) {
					/* ignore */
				}
				return null;
			});
		})
		.catch(function () {
			/* ignore cache write errors */
		});
	return response;
}

function matchPage(request) {
	return caches.open(PAGE_CACHE).then(function (cache) {
		return cache.match(request).then(function (hit) {
			if (hit) {
				return hit;
			}
			try {
				var u = new URL(typeof request === 'string' ? request : request.url);
				var candidates = [u.origin + u.pathname];
				if (/\/transfer\//.test(u.pathname)) {
					candidates.push(u.origin + u.pathname.replace('/transfer/', '/Transfer/'));
				}
				if (/\/Transfer\//.test(u.pathname)) {
					candidates.push(u.origin + u.pathname.replace('/Transfer/', '/transfer/'));
				}
				function tryNext(i) {
					if (i >= candidates.length) {
						return null;
					}
					return cache.match(candidates[i]).then(function (h) {
						return h || tryNext(i + 1);
					});
				}
				return tryNext(0);
			} catch (e) {
				return null;
			}
		});
	});
}

function prefetchUrls(urls) {
	return caches.open(PAGE_CACHE).then(function (cache) {
		var i = 0;
		function next() {
			if (i >= urls.length) {
				return Promise.resolve();
			}
			var u = urls[i++];
			return fetch(u, { credentials: 'include', cache: 'no-store' })
				.then(function (res) {
					if (res && res.ok) {
						return cache.put(u, res.clone()).then(function () {
							try {
								var parsed = new URL(u, self.location.origin);
								return cache.put(parsed.origin + parsed.pathname, res.clone());
							} catch (e) {
								return null;
							}
						});
					}
					return null;
				})
				.catch(function () {
					return null;
				})
				.then(next);
		}
		return next();
	});
}

/**
 * Offline only: same-URL cache, then offline-app.
 * NEVER substitute Welcome for an unrelated URL — that looked like a "redirect to Home"
 * bug (action still ran server-side, browser showed cached Welcome).
 */
function offlineFallback(request) {
	return matchPage(request).then(function (page) {
		if (page) {
			return page;
		}
		return caches.match(OFFLINE_APP).then(function (desk) {
			return desk || htmlOfflineResponse();
		});
	});
}

self.addEventListener('message', function (event) {
	var data = event.data || {};
	if (data.type === 'SKIP_WAITING') {
		self.skipWaiting();
		return;
	}
	if (data.type === 'darzi-prefetch' && data.urls && data.urls.length) {
		event.waitUntil(prefetchUrls(data.urls));
	}
	// Drop specific ERP list pages (e.g. Ready list after a delivery).
	if (data.type === 'INVALIDATE_PAGES' && data.paths && data.paths.length) {
		event.waitUntil(
			caches.open(PAGE_CACHE).then(function (cache) {
				return cache.keys().then(function (keys) {
					return Promise.all(
						keys.map(function (req) {
							var u = String(req.url || '');
							var hit = data.paths.some(function (p) {
								return p && u.indexOf(p) !== -1;
							});
							return hit ? cache.delete(req) : null;
						})
					);
				});
			})
		);
		return;
	}
	// Sign-out: drop cached ERP HTML so next user/session never sees previous pages.
	if (data.type === 'darzi-logout-clear') {
		event.waitUntil(
			caches.delete(PAGE_CACHE).then(function () {
				return caches.open(PAGE_CACHE);
			})
		);
	}
});

/**
 * Don't wait for TCP timeout (can be 30–90s). Race network vs short timer,
 * then serve cache so Order Status / nav never stick at 90%.
 */
function fetchWithTimeout(request, ms) {
	return new Promise(function (resolve) {
		var done = false;
		var timer = setTimeout(function () {
			if (!done) {
				done = true;
				resolve(null);
			}
		}, ms);
		fetch(request)
			.then(function (res) {
				if (!done) {
					done = true;
					clearTimeout(timer);
					resolve(res);
				}
			})
			.catch(function () {
				if (!done) {
					done = true;
					clearTimeout(timer);
					resolve(null);
				}
			});
	});
}

function htmlOfflineResponse() {
	return new Response(
		'<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem"><h1>Offline</h1><p>This page is not cached yet. Open it once while online.</p><p><a href="/index.php/Welcome">Home</a></p></body></html>',
		{ status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
	);
}

/**
 * Mutating GETs must NEVER fall back to page cache / Welcome.
 * Audit coverage: Order_deactive/active/delete, line_delete, ready_return_*,
 * toggle_active, process_now/pending, predefined_reseed/delete, booking_limit_*,
 * category_/product_ toggle|delete, transfer_return/process, Configuration clear/import.
 */
function isActionPath(url) {
	var p = String((url && url.pathname) || '');
	if (/\/(delete|save|process|remove|revoke|logout|return|reseed)(\/|$)/i.test(p)) {
		return true;
	}
	// Suffix after underscore: Order_deactive, order_line_delete, toggle_active, …
	if (
		/_(delete|save|add|update|process|toggle|activate|deactivate|deactive|active|return|reseed)(\/|$|_)/i.test(
			p
		)
	) {
		return true;
	}
	// process_now / process_pending (no underscore before process)
	if (/\/(process_now|process_pending|process_del|process_edit|process_status|process_rate)\b/i.test(p)) {
		return true;
	}
	// Broadcast / notification side-effects (GET)
	if (/\/(pause|resume|cancel|run_payment_reminders|restore_templates)\b/i.test(p)) {
		return true;
	}
	if (/\/order\/(order_deactive|order_active|order_delete|order_line_delete|ready_return)\b/i.test(p)) {
		return true;
	}
	if (/ready_return_/i.test(p)) {
		return true;
	}
	if (/booking_limit_|predefined_|device_|import_|clear_|category_toggle|category_delete|product_toggle|product_delete/i.test(p)) {
		return true;
	}
	if (/transfer_(return|order|process|print)/i.test(p)) {
		return true;
	}
	return false;
}

/**
 * Order create forms embed a one-time submit token. Serving a cached copy
 * while "online" makes the first Save fail with "submitted twice or expired".
 */
function isOneTimeTokenFormPath(url) {
	var p = String((url && url.pathname) || '').toLowerCase();
	return (
		p.indexOf('/order/order_add') !== -1 ||
		p.indexOf('/order/order_add1') !== -1 ||
		p.indexOf('/order/order_add2') !== -1 ||
		/\/order\/fresh_submit_token/i.test(p)
	);
}

function actionFailResponse() {
	return new Response(
		'<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem">' +
			'<h1>Connection problem</h1>' +
			'<p>Action could not finish. Go back and try again — do not stay on Home.</p>' +
			'<p><a href="javascript:history.back()">Go back</a></p>' +
			'</body></html>',
		{ status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
	);
}

self.addEventListener('fetch', function (event) {
	var request = event.request;

	if (request.method !== 'GET') {
		return;
	}

	var url;
	try {
		url = new URL(request.url);
	} catch (err) {
		return;
	}
	if (url.origin !== self.location.origin) {
		return;
	}

	if (/\/api\//.test(url.pathname) || /erp_sync/.test(url.pathname)) {
		return;
	}

	if (isNavigate(request) || isErpHtmlPath(url)) {
		// Delete/save/process: network only — never Welcome fallback.
		if (isActionPath(url)) {
			event.respondWith(
				fetch(request).catch(function () {
					return actionFailResponse();
				})
			);
			return;
		}

		// Order add forms: network-first, long wait, never re-cache dead tokens.
		if (isOneTimeTokenFormPath(url)) {
			event.respondWith(
				fetchWithTimeout(request, 20000)
					.then(function (res) {
						// Do not put tokenized forms in PAGE_CACHE.
						return res;
					})
					.catch(function () {
						return matchPage(request).then(function (cached) {
							// Offline only: last resort cached shell (JS will refresh token when online).
							return cached || offlineFallback(request).catch(function () {
								return htmlOfflineResponse();
							});
						});
					})
			);
			return;
		}

		// Production-safe: ALWAYS network-first for ERP HTML (live shop must not see stale pages).
		// Cache is offline backup only — never race 2.8s and swap in wrong/stale HTML while online.
		event.respondWith(
			fetchWithTimeout(request, 18000).then(function (res) {
				if (res && res.ok) {
					return cachePage(request, res);
				}
				return matchPage(request).then(function (cached) {
					if (cached) {
						return cached;
					}
					return offlineFallback(request).catch(function () {
						return htmlOfflineResponse();
					});
				});
			})
		);
		return;
	}

	if (!isStaticAsset(url)) {
		return;
	}

	event.respondWith(
		caches.match(request).then(function (cached) {
			var network = fetch(request)
				.then(function (res) {
					if (res && res.ok) {
						var copy = res.clone();
						caches.open(SHELL_CACHE).then(function (cache) {
							cache.put(request, copy);
						});
					}
					return res;
				})
				.catch(function () {
					return cached || Response.error();
				});
			return cached || network;
		})
	);
});
