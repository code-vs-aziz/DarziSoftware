/**
 * Global WhatsApp bridge status indicator (header chip + break banner).
 */
(function () {
	'use strict';

	var POLL_OK_MS = 60000;
	var POLL_BAD_MS = 30000;
	var lastMode = '';
	var timer = null;
	var bannerEl = null;
	var bannerShownForBreak = false;

	function t(key, fallback) {
		var lang = window.APP_LANG || {};
		return lang[key] || fallback || key;
	}

	function chips() {
		return document.querySelectorAll('[data-bridge-chip="1"]');
	}

	function ensureBanner() {
		if (bannerEl) {
			return bannerEl;
		}
		bannerEl = document.createElement('div');
		bannerEl.id = 'dz-bridge-break-banner';
		bannerEl.className = 'dz-bridge-break-banner';
		bannerEl.setAttribute('role', 'status');
		bannerEl.hidden = true;
		bannerEl.innerHTML =
			'<a class="dz-bridge-break-banner__link" href="#">' +
			'<span class="dz-bridge-break-banner__icon" aria-hidden="true">!</span>' +
			'<span class="dz-bridge-break-banner__text"></span>' +
			'</a>' +
			'<button type="button" class="dz-bridge-break-banner__close" aria-label="Close">&times;</button>';
		document.body.appendChild(bannerEl);

		bannerEl.querySelector('.dz-bridge-break-banner__close').addEventListener('click', function () {
			bannerEl.hidden = true;
			bannerShownForBreak = true;
		});
		return bannerEl;
	}

	function showBreakBanner(fixUrl, mode) {
		var el = ensureBanner();
		var link = el.querySelector('.dz-bridge-break-banner__link');
		var text = el.querySelector('.dz-bridge-break-banner__text');
		var first = chips()[0];
		var canManage = first && first.getAttribute('data-can-manage') === '1';
		link.href = fixUrl || '#';
		if (mode === 'wait') {
			text.textContent = canManage
				? t('bridge_banner_scan', 'WhatsApp QR needed — open Customer Messages to scan.')
				: t('bridge_banner_scan_staff', 'WhatsApp QR needed — ask admin to scan once (shared for all users).');
		} else {
			text.textContent = canManage
				? t('bridge_banner_down', 'WhatsApp bridge down — open Customer Messages to restart & scan QR.')
				: t('bridge_banner_down_staff', 'WhatsApp bridge down — ask admin to restart / scan QR (shared for all users).');
		}
		if (!bannerShownForBreak) {
			el.hidden = false;
		}
	}

	function hideBreakBanner() {
		if (bannerEl) {
			bannerEl.hidden = true;
		}
		bannerShownForBreak = false;
	}

	function applyMode(mode, err) {
		var labelKey = {
			ok: 'bridge_chip_ok',
			wait: 'bridge_chip_wait',
			off: 'bridge_chip_off',
			checking: 'bridge_chip_checking'
		};
		var fallback = {
			ok: 'WhatsApp OK',
			wait: 'Scan QR',
			off: 'Bridge down',
			checking: 'Checking…'
		};
		var label = t(labelKey[mode] || 'bridge_chip_checking', fallback[mode] || fallback.checking);
		var title = label;
		if (err && mode !== 'ok') {
			title = label + ' — ' + err;
		}

		chips().forEach(function (chip) {
			chip.classList.remove('is-ok', 'is-wait', 'is-off', 'is-checking');
			chip.classList.add('is-' + mode);
			chip.title = title;
			var lab = chip.querySelector('[data-bridge-label]');
			if (lab) {
				lab.textContent = label;
			}
		});

		var fixUrl = (chips()[0] && chips()[0].getAttribute('data-fix-url')) || '';
		if (mode === 'ok') {
			hideBreakBanner();
		} else if (mode === 'wait' || mode === 'off') {
			if (lastMode === 'ok' || lastMode === 'checking' || lastMode === '') {
				bannerShownForBreak = false;
			}
			showBreakBanner(fixUrl, mode);
		}

		lastMode = mode;
		schedule(mode === 'ok' ? POLL_OK_MS : POLL_BAD_MS);
	}

	function schedule(ms) {
		if (timer) {
			clearTimeout(timer);
		}
		timer = setTimeout(poll, ms);
	}

	function poll() {
		var first = chips()[0];
		if (!first) {
			return;
		}
		var url = first.getAttribute('data-status-url');
		if (!url) {
			return;
		}

		fetch(url + (url.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now(), {
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (!data || data.message === 'session_expired') {
					applyMode('off', '');
					return;
				}
				var st = data.status || {};
				if (st.ready) {
					applyMode('ok', '');
					return;
				}
				// QR ready counts as wait — never show "Bridge down" while has_qr.
				if (st.ok || st.has_qr || st.state === 'qr' || st.state === 'starting' || st.state === 'restarting') {
					applyMode('wait', st.error || '');
					return;
				}
				applyMode('off', st.error || '');
			})
			.catch(function () {
				applyMode('off', '');
			});
	}

	function boot() {
		if (!chips().length) {
			return;
		}
		poll();
		document.addEventListener('visibilitychange', function () {
			if (!document.hidden) {
				poll();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
