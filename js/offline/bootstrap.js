/**
 * Offline-first bootstrap for Darzi Baba ERP.
 */
(function (window, document) {
	'use strict';

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function registerSw() {
		if (!('serviceWorker' in navigator)) {
			return;
		}
		if (!cfg().swUrl) {
			return;
		}
		navigator.serviceWorker.register(cfg().swUrl, { scope: cfg().swScope || '/' }).then(function (reg) {
			// Ensure Offline Desk + assets are cached while online.
			if (reg && typeof caches !== 'undefined') {
				var urls = [
					'./offline-app.html',
					'./css/offline-desk.css',
					'./js/offline/desk.js',
					'./js/offline/desk-ops.js',
					'./manifest.webmanifest'
				];
				caches.open('darzi-erp-offline-v2-shell').then(function (cache) {
					urls.forEach(function (u) {
						cache.add(u).catch(function () { /* ignore */ });
					});
				});
			}
		}).catch(function () {
			/* SW optional on HTTP / unsupported hosts */
		});
	}

	function snapshotCfg() {
		var c = cfg();
		if (!c || !window.DarziOffline || !window.DarziOffline.db) {
			return;
		}
		window.DarziOffline.db.setMeta('cfg_snapshot', {
			apiBase: c.apiBase || '',
			healthUrl: c.healthUrl || '',
			homeUrl: c.homeUrl || '',
			loginUrl: c.loginUrl || (c.homeUrl ? String(c.homeUrl).replace(/Welcome.*/i, 'Login') : ''),
			customerUrl: c.customerUrl || '',
			measurementUrl: c.measurementUrl || '',
			cashUrl: c.cashUrl || '',
			swUrl: c.swUrl || './sw.js',
			swScope: c.swScope || './',
			saved_at: new Date().toISOString()
		});
	}

	function bindLogout() {
		document.addEventListener('click', function (e) {
			var a = e.target && e.target.closest ? e.target.closest('[data-app-logout="true"]') : null;
			if (!a) {
				return;
			}
			try {
				window.DarziOffline.sync.revokeOnLogout();
			} catch (err) {
				/* ignore */
			}
		});
	}

	function autoSyncWhenOnline(prev, next) {
		if (prev === 'offline' && next === 'online') {
			window.DarziOffline.db.countPending().then(function (n) {
				if (n > 0) {
					window.DarziOffline.ui.triggerSync();
				} else {
					window.DarziOffline.sync.bootstrapIfNeeded();
				}
			});
		}
	}

	function start() {
		if (!cfg().enabled) {
			return;
		}
		if (!window.DarziOffline || !window.DarziOffline.db) {
			return;
		}

		registerSw();
		snapshotCfg();
		bindLogout();
		window.DarziOffline.forms.bind();

		var lastStatus = 'checking';
		window.DarziOffline.connectivity.onChange(function (st) {
			autoSyncWhenOnline(lastStatus, st.status);
			lastStatus = st.status;
		});

		window.DarziOffline.connectivity.start().then(function () {
			window.DarziOffline.ui.start();
			if (window.DarziOffline.connectivity.isOnline()) {
				window.DarziOffline.sync.ensureAuth(false).catch(function () {
					return null;
				});
				window.DarziOffline.sync.bootstrapIfNeeded();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})(window, document);
