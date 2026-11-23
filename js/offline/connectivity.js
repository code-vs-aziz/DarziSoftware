/**
 * Connectivity monitor: navigator.onLine + backend health check.
 */
(function (window) {
	'use strict';

	var HEALTH_OK_MS = 30000;
	var HEALTH_BAD_MS = 12000;
	var listeners = [];
	var state = {
		browserOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
		serverReachable: null,
		status: 'checking',
		lastCheckedAt: null,
		lastError: ''
	};
	var timer = null;
	var checking = false;

	function cfg() {
		return window.DARZI_OFFLINE_CFG || {};
	}

	function healthUrl() {
		return cfg().healthUrl || '/index.php/api/erp_sync/health';
	}

	function emit() {
		listeners.forEach(function (fn) {
			try {
				fn(Object.assign({}, state));
			} catch (e) {
				/* ignore listener errors */
			}
		});
		window.dispatchEvent(
			new CustomEvent('darzi-offline-status', { detail: Object.assign({}, state) })
		);
	}

	function deriveStatus() {
		if (!state.browserOnline || state.serverReachable === false) {
			state.status = 'offline';
			return;
		}
		if (state.serverReachable === null) {
			state.status = 'checking';
			return;
		}
		state.status = 'online';
	}

	function schedule() {
		if (timer) {
			clearTimeout(timer);
		}
		var ms = state.status === 'online' ? HEALTH_OK_MS : HEALTH_BAD_MS;
		timer = setTimeout(checkHealth, ms);
	}

	function checkHealth() {
		if (checking) {
			return Promise.resolve(state);
		}
		checking = true;
		state.browserOnline = navigator.onLine;
		if (!state.browserOnline) {
			state.serverReachable = false;
			state.lastCheckedAt = new Date().toISOString();
			deriveStatus();
			checking = false;
			emit();
			schedule();
			return Promise.resolve(state);
		}

		var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
		var t = setTimeout(function () {
			if (ctrl) {
				ctrl.abort();
			}
		}, 3500);

		var opts = {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json' }
		};
		if (ctrl) {
			opts.signal = ctrl.signal;
		}

		return fetch(healthUrl(), opts)
			.then(function (res) {
				clearTimeout(t);
				return res.ok ? res.json() : Promise.reject(new Error('health_http_' + res.status));
			})
			.then(function (json) {
				state.serverReachable = !!(json && json.ok);
				state.lastError = state.serverReachable ? '' : 'health_not_ok';
				state.lastCheckedAt = new Date().toISOString();
				deriveStatus();
				checking = false;
				emit();
				schedule();
				return state;
			})
			.catch(function (err) {
				clearTimeout(t);
				state.serverReachable = false;
				state.lastError = (err && err.message) || 'health_failed';
				state.lastCheckedAt = new Date().toISOString();
				deriveStatus();
				checking = false;
				emit();
				schedule();
				return state;
			});
	}

	var onlineDebounce = null;

	function start() {
		window.addEventListener('online', function () {
			state.browserOnline = true;
			// Debounce rapid flaps so reconnect does not spam health + sync triggers.
			if (onlineDebounce) {
				clearTimeout(onlineDebounce);
			}
			onlineDebounce = setTimeout(function () {
				onlineDebounce = null;
				checkHealth();
			}, 400);
		});
		window.addEventListener('offline', function () {
			state.browserOnline = false;
			state.serverReachable = false;
			deriveStatus();
			emit();
			schedule();
		});
		return checkHealth();
	}

	function isOnline() {
		return state.status === 'online';
	}

	function onChange(fn) {
		listeners.push(fn);
		return function () {
			listeners = listeners.filter(function (f) {
				return f !== fn;
			});
		};
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.connectivity = {
		start: start,
		check: checkHealth,
		isOnline: isOnline,
		getState: function () {
			return Object.assign({}, state);
		},
		onChange: onChange
	};
})(window);
