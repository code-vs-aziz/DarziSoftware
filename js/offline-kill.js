/**
 * Purge Service Worker + caches so Full ERP always hits the live server.
 * Stops PWA/Offline Desk from wrapping POSTs (which caused ERR_FAILED / connection drop).
 */
(function () {
	'use strict';
	var RELOAD_KEY = 'darzi_sw_kill_reloaded_v2';

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
			return Promise.resolve(false);
		}
		var hadController = !!navigator.serviceWorker.controller;
		return navigator.serviceWorker.getRegistrations().then(function (regs) {
			var hadRegs = regs && regs.length > 0;
			return Promise.all(
				(regs || []).map(function (reg) {
					return reg.unregister();
				})
			).then(function () {
				return hadController || hadRegs;
			});
		}).catch(function () {
			return false;
		});
	}

	unregisterWorkers()
		.then(function (hadSw) {
			return wipeCaches().then(function () {
				return hadSw;
			});
		})
		.then(function (hadSw) {
			if (!hadSw) {
				return;
			}
			try {
				if (sessionStorage.getItem(RELOAD_KEY) === '1') {
					return;
				}
				sessionStorage.setItem(RELOAD_KEY, '1');
			} catch (err) {
				/* ignore storage errors */
			}
			// One hard reload so the page is no longer controlled by a dead SW.
			window.location.reload();
		});
})();
