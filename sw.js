/* Darzi Baba ERP — Service Worker TEMPORARILY DISABLED
 * This build only unregisters itself and clears offline caches so shops
 * always get Full ERP (not Offline Desk). Re-enable offline desk later
 * by restoring the real SW + OFFLINE_DESK_ENABLED=true.
 */
/* eslint-disable no-restricted-globals */
var CACHE_VERSION = 'darzi-erp-offline-disabled-v7';

self.addEventListener('install', function (event) {
	event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', function (event) {
	event.waitUntil(
		caches.keys()
			.then(function (keys) {
				return Promise.all(
					keys.map(function (k) {
						return caches.delete(k);
					})
				);
			})
			.then(function () {
				return self.registration.unregister();
			})
			.then(function () {
				return self.clients.claim();
			})
			.then(function () {
				return self.clients.matchAll({ type: 'window' });
			})
			.then(function (clients) {
				clients.forEach(function (client) {
					if (client.url && 'navigate' in client) {
						client.navigate(client.url);
					}
				});
			})
			.catch(function () {
				return null;
			})
	);
});

/* No fetch handler — browser talks to network directly for Full ERP. */
