/**
 * ERP navigation: browser tabs vs Electron desktop shell.
 *
 * Root causes this guards against:
 * 1) window.open(url, '_blank', 'width=…') → chrome-less popup that looks like a Desktop App
 * 2) Installed PWA link-capturing (manifest launch_handler / capture_links) stealing target=_blank
 *
 * Electron keeps its own setWindowOpenHandler in desktop/main.js (same-window for local URLs).
 */
(function (window, document) {
	'use strict';

	var DZ = window.DZ || (window.DZ = {});
	var nativeOpen = window.open;

	function ua() {
		return String((window.navigator && window.navigator.userAgent) || '');
	}

	DZ.isElectronShell = function () {
		return /Electron/i.test(ua());
	};

	DZ.isStandaloneDisplay = function () {
		try {
			if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
				return true;
			}
			if (window.matchMedia && window.matchMedia('(display-mode: minimal-ui)').matches) {
				return true;
			}
		} catch (e) { /* ignore */ }
		return window.navigator.standalone === true;
	};

	DZ.isBrowserTab = function () {
		return !DZ.isElectronShell() && !DZ.isStandaloneDisplay();
	};

	/**
	 * Open url as a new browser tab (browser) or via Electron handler (desktop).
	 * Never pass windowFeatures — those force a chrome-less "app" window.
	 */
	DZ.openInNewContext = function (url) {
		var a;
		var w;
		if (!url) {
			return null;
		}

		if (DZ.isElectronShell()) {
			w = nativeOpen.call(window, url, '_blank');
			if (w) {
				try { w.opener = null; } catch (e1) { /* ignore */ }
			}
			return w;
		}

		a = document.createElement('a');
		a.href = url;
		a.target = '_blank';
		a.rel = 'noopener noreferrer';
		a.setAttribute('data-no-ajax', 'true');
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		return null;
	};

	/*
	 * In a normal browser tab, strip windowFeatures from window.open so callers
	 * cannot create chrome-less popups. Electron / installed PWA are left alone.
	 */
	if (DZ.isBrowserTab() && typeof nativeOpen === 'function') {
		window.open = function (url, target, features) {
			var name = target || '_blank';
			var w;
			/* Ignore features entirely in browser ERP — always prefer a real tab. */
			w = nativeOpen.call(window, url, name);
			if (w) {
				try { w.opener = null; } catch (e2) { /* ignore */ }
			}
			return w;
		};
	}

	/* Expose for debugging / other scripts */
	DZ._nativeOpen = nativeOpen;
})(window, document);
