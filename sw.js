/* Darzi Baba ERP — Service Worker (offline desk + shell) */
/* eslint-disable no-restricted-globals */
var CACHE_VERSION = 'darzi-erp-offline-v2';
var SHELL_CACHE = CACHE_VERSION + '-shell';
var SHELL_URLS = [
	'./offline-app.html',
	'./offline.html',
	'./manifest.webmanifest',
	'./css/offline-erp.css',
	'./css/offline-desk.css',
	'./css/theme.css',
	'./css/dz-design-system.css',
	'./css/app-unified-theme.css',
	'./css/dz-mobile-app.css',
	'./js/jquery-3.3.1.js',
	'./js/offline/db.js',
	'./js/offline/connectivity.js',
	'./js/offline/sync-engine.js',
	'./js/offline/forms.js',
	'./js/offline/ui.js',
	'./js/offline/bootstrap.js',
	'./js/offline/desk-ops.js',
	'./js/offline/desk.js',
	'./images/darzi-baba-logo.png',
	'./images/favicon-32.png',
	'./images/apple-touch-icon.png'
];

self.addEventListener('install', function (event) {
	event.waitUntil(
		caches.open(SHELL_CACHE).then(function (cache) {
			return cache.addAll(
				SHELL_URLS.map(function (u) {
					return new Request(u, { cache: 'reload' });
				})
			).catch(function () {
				/* partial cache OK — still activate */
				return null;
			});
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
						return k.indexOf('darzi-erp-offline-') === 0 && k !== SHELL_CACHE;
					})
					.map(function (k) {
						return caches.delete(k);
					})
			);
		}).then(function () {
			return self.clients.claim();
		})
	);
});

function isApi(url) {
	return /\/api\//.test(url.pathname) || /erp_sync/.test(url.pathname);
}

function isNavigate(request) {
	return (
		request.mode === 'navigate' ||
		(request.method === 'GET' &&
			request.headers.get('accept') &&
			request.headers.get('accept').indexOf('text/html') !== -1)
	);
}

function offlineDesk() {
	return caches.match('./offline-app.html').then(function (cached) {
		return (
			cached ||
			caches.match('./offline.html').then(function (legacy) {
				return (
					legacy ||
					new Response(
						'<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem"><h1>Offline Desk</h1><p>Cache missing. Open ERP once while online, then retry.</p></body></html>',
						{ status: 200, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
					)
				);
			})
		);
	});
}

self.addEventListener('fetch', function (event) {
	var request = event.request;
	if (request.method !== 'GET') {
		return;
	}
	var url = new URL(request.url);
	if (url.origin !== self.location.origin) {
		return;
	}
	if (isApi(url)) {
		// Network only for APIs (sync engine handles offline).
		return;
	}

	if (isNavigate(request)) {
		event.respondWith(
			fetch(request)
				.then(function (res) {
					// Opportunistically keep a copy of successful HTML for later (best-effort).
					if (res && res.ok) {
						var copy = res.clone();
						caches.open(SHELL_CACHE).then(function (cache) {
							cache.put(request, copy);
						});
					}
					return res;
				})
				.catch(function () {
					// Cold start / server down → Offline Desk
					var path = url.pathname || '';
					if (/offline-app\.html$/i.test(path) || /offline\.html$/i.test(path)) {
						return offlineDesk();
					}
					return offlineDesk();
				})
		);
		return;
	}

	// Static assets: cache-first
	if (/\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|ico|webmanifest)(\?|$)/i.test(url.pathname)) {
		event.respondWith(
			caches.match(request).then(function (cached) {
				if (cached) {
					return cached;
				}
				return fetch(request).then(function (res) {
					var copy = res.clone();
					caches.open(SHELL_CACHE).then(function (cache) {
						cache.put(request, copy);
					});
					return res;
				}).catch(function () {
					return caches.match(request.url.replace(/\?.*$/, '')).then(function (c2) {
						return c2 || Response.error();
					});
				});
			})
		);
	}
});

self.addEventListener('message', function (event) {
	if (event.data && event.data.type === 'SKIP_WAITING') {
		self.skipWaiting();
	}
});
