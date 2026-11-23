/**
 * Offline-first bootstrap for Darzi Baba ERP.
 * After online login: download customer/measurement data + cache Full ERP pages.
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
		navigator.serviceWorker.register(cfg().swUrl, { scope: cfg().swScope || './' }).then(function (reg) {
			if (reg && reg.update) {
				reg.update().catch(function () { /* ignore */ });
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
			homeUrl: c.homeUrl || '/index.php/Welcome',
			loginUrl: c.loginUrl || '/index.php/Login',
			customerUrl: c.customerUrl || '',
			measurementUrl: c.measurementUrl || '',
			orderAddUrl: c.orderAddUrl || '',
			cashUrl: c.cashUrl || '',
			swUrl: c.swUrl || '/sw.js?v=28',
			swScope: c.swScope || '/',
			saved_at: new Date().toISOString()
		});
	}

	/**
	 * One-click sign-out: revoke offline token, clear cached ERP HTML, then hard-navigate.
	 * (Old flow raced ajax-shell + inline onclick — often needed a second click, and
	 * left page cache / sync token behind for the next login.)
	 */
	function bindLogout() {
		document.addEventListener(
			'click',
			function (e) {
				var a = e.target && e.target.closest ? e.target.closest('[data-app-logout="true"]') : null;
				var href;
				var done = false;
				var timer;
				var revoke;

				if (!a) {
					return;
				}
				href = a.href;
				if (!href) {
					return;
				}
				if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || (e.which && e.which !== 1)) {
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				function go() {
					if (done) {
						return;
					}
					done = true;
					if (timer) {
						window.clearTimeout(timer);
					}
					try {
						if (navigator.serviceWorker && navigator.serviceWorker.controller) {
							navigator.serviceWorker.controller.postMessage({ type: 'darzi-logout-clear' });
						}
					} catch (err) {
						/* ignore */
					}
					window.location.assign(href);
				}

				function continueLogout() {
					// Never block sign-out more than ~700ms waiting on revoke.
					timer = window.setTimeout(go, 700);
					revoke = Promise.resolve();
					try {
						if (window.DarziOffline && window.DarziOffline.sync && window.DarziOffline.sync.revokeOnLogout) {
							revoke = window.DarziOffline.sync.revokeOnLogout() || Promise.resolve();
						}
					} catch (err) {
						revoke = Promise.resolve();
					}
					Promise.resolve(revoke).then(go, go);
				}

				// Keep unsynced queue intact across logout (no silent wipe). Warn if pending.
				var pendingCheck =
					window.DarziOffline && window.DarziOffline.db && window.DarziOffline.db.countPending
						? window.DarziOffline.db.countPending()
						: Promise.resolve(0);
				Promise.resolve(pendingCheck)
					.then(function (n) {
						if (n > 0) {
							var msg =
								(window.APP_LANG && window.APP_LANG.offline_logout_pending_warn) ||
								'You have ' +
									n +
									' unsynced offline change(s). They stay on this browser until synced. Continue logout?';
							if (!window.confirm(msg)) {
								return;
							}
						}
						continueLogout();
					})
					.catch(function () {
						continueLogout();
					});
			},
			true
		);
	}

	function runBackgroundPrefetch(force) {
		if (!window.DarziOffline || !window.DarziOffline.prefetch) {
			return Promise.resolve();
		}
		return new Promise(function (resolve) {
			window.setTimeout(function () {
				window.DarziOffline.prefetch.run(!!force).then(resolve).catch(resolve);
			}, 800);
		});
	}

	var HOUR_MS = 60 * 60 * 1000;
	var hourTimer = null;

	function runHourlyDeltaSync() {
		if (!window.DarziOffline.connectivity.isOnline()) {
			return;
		}
		if (!window.DarziOffline.sync || !window.DarziOffline.sync.deltaSyncIfDue) {
			return;
		}
		window.DarziOffline.sync
			.deltaSyncIfDue({ maxAgeMs: HOUR_MS })
			.then(function () {
				if (window.DarziOffline.lists && window.DarziOffline.lists.warm) {
					return window.DarziOffline.lists.warm(false);
				}
				return null;
			})
			.then(function () {
				if (window.DarziOffline.ui && window.DarziOffline.ui.refresh) {
					window.DarziOffline.ui.refresh();
				}
			})
			.catch(function () {
				/* quiet background */
			});
	}

	function startHourlyDeltaSync() {
		if (hourTimer) {
			window.clearInterval(hourTimer);
		}
		// First check shortly after load (delta only if >1h old).
		window.setTimeout(runHourlyDeltaSync, 20 * 1000);
		hourTimer = window.setInterval(runHourlyDeltaSync, HOUR_MS);
	}

	function warmAfterOnline() {
		if (!window.DarziOffline.connectivity.isOnline()) {
			if (window.DarziOffline.ui && window.DarziOffline.ui.showReadyState) {
				window.DarziOffline.ui.showReadyState();
			}
			return;
		}
		// Auto while online: customers/measurements + Order Status/Transfer lists + all ERP pages.
		// User does not need to open every menu item manually.
		var dataWarm = window.DarziOffline.sync.ensureOfflineData({ force: false }).catch(function () {
			return null;
		});
		var listsWarm =
			window.DarziOffline.lists && window.DarziOffline.lists.warm
				? window.DarziOffline.lists.warm(true)
				: Promise.resolve();
		Promise.all([dataWarm, listsWarm])
			.then(function () {
				if (window.DarziOffline.ui && window.DarziOffline.ui.showReadyState) {
					window.DarziOffline.ui.showReadyState({ quiet: true });
				}
				// Cache measurement/order form pages automatically (no manual open).
				var formsWarm =
					window.DarziOffline.nav && window.DarziOffline.nav.ensureFormTemplates
						? window.DarziOffline.nav.ensureFormTemplates()
						: Promise.resolve();
				return formsWarm.then(function () {
					if (window.DarziOffline.prefetch && window.DarziOffline.prefetch.ensureCore) {
						return window.DarziOffline.prefetch.ensureCore().then(function () {
							return runBackgroundPrefetch(false);
						});
					}
					return runBackgroundPrefetch(false);
				});
			})
			.then(function () {
				if (window.DarziOffline.ui && window.DarziOffline.ui.showReadyState) {
					window.DarziOffline.ui.showReadyState({ quiet: true });
				}
			});
	}

	function autoSyncWhenOnline(prev, next) {
		if (prev === 'offline' && next === 'online') {
			window.DarziOffline.db.countPending().then(function (n) {
				var chain =
					n > 0
						? window.DarziOffline.ui.triggerSync()
						: window.DarziOffline.sync.ensureOfflineData({ force: false });
				Promise.resolve(chain)
					.catch(function () {
						return null;
					})
					.then(function () {
						var listsWarm =
							window.DarziOffline.lists && window.DarziOffline.lists.warm
								? window.DarziOffline.lists.warm(true)
								: Promise.resolve();
						return listsWarm.then(function () {
							return runBackgroundPrefetch(false);
						});
					})
					.then(function () {
						if (window.DarziOffline.ui && window.DarziOffline.ui.showReadyState) {
							window.DarziOffline.ui.showReadyState({ quiet: true });
						}
					});
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
		if (window.DarziOffline.search && typeof window.DarziOffline.search.bind === 'function') {
			window.DarziOffline.search.bind();
		}
		if (window.DarziOffline.lists && typeof window.DarziOffline.lists.bind === 'function') {
			window.DarziOffline.lists.bind();
		}
		document.addEventListener('app:ajax-loaded', function () {
			if (window.DarziOffline.lists && typeof window.DarziOffline.lists.bind === 'function') {
				window.DarziOffline.lists.bind();
			}
		});

		var lastStatus = 'checking';
		window.DarziOffline.connectivity.onChange(function (st) {
			autoSyncWhenOnline(lastStatus, st.status);
			lastStatus = st.status;
			if (st.status === 'offline' && window.DarziOffline.ui && window.DarziOffline.ui.showReadyState) {
				window.DarziOffline.ui.showReadyState();
			}
		});

		var sessionUser = (cfg().deviceLabel || '').toString().trim();
		var restore =
			window.DarziOffline.db.restoreActiveUser
				? window.DarziOffline.db.restoreActiveUser()
				: Promise.resolve('');
		restore
			.then(function (stored) {
				if (sessionUser && window.DarziOffline.db.activateUser) {
					return window.DarziOffline.db.activateUser(sessionUser);
				}
				if (stored && window.DarziOffline.db.activateUser) {
					return window.DarziOffline.db.activateUser(stored);
				}
				return null;
			})
			.catch(function () {
				return null;
			})
			.then(function () {
				return window.DarziOffline.connectivity.start();
			})
			.then(function () {
				window.DarziOffline.ui.start();
				warmAfterOnline();
				startHourlyDeltaSync();
				if (
					window.DarziOffline.connectivity.isOnline() &&
					window.DarziOffline.sync &&
					window.DarziOffline.sync.fetchSchemaStatus
				) {
					window.DarziOffline.sync.fetchSchemaStatus().catch(function () {
						return null;
					});
				}
			});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})(window, document);
