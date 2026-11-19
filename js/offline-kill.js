/**
 * Temporary: purge Service Worker + caches so Full ERP always loads.
 * Remove / stop including this when Offline Desk is re-enabled.
 */
(function () {
	'use strict';
	function wipeCaches() {
		if (!window.caches || !caches.keys) {
			return Promise.resolve();
		}
		return caches.keys().then(function (keys) {
			return Promise.all(
				keys.map(function (k) {
					return caches.delete(k);
				})
			);
		}).catch(function () {
			return null;
		});
	}

	function unregisterWorkers() {
		if (!('serviceWorker' in navigator) || !navigator.serviceWorker.getRegistrations) {
			return Promise.resolve();
		}
		return navigator.serviceWorker.getRegistrations().then(function (regs) {
			return Promise.all(
				regs.map(function (reg) {
					return reg.unregister();
				})
			);
		}).catch(function () {
			return null;
		});
	}

	unregisterWorkers().then(wipeCaches);
})();
