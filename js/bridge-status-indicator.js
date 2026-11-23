/**
 * Global WhatsApp bridge status indicator (header chip + break banner).
 * Debounces transient /status failures so the banner does not flap.
 */
(function () {
	'use strict';

	var POLL_OK_MS = 90000;
	var POLL_BAD_MS = 45000;
	var POLL_SOFT_MS = 15000;
	/** Consecutive bad polls required before showing Bridge down. */
	var BAD_STREAK_NEEDED = 3;
	var lastMode = '';
	var timer = null;
	var bannerEl = null;
	var bannerShownForBreak = false;
	var bannerDismissedUntil = 0;
	var badStreak = 0;
	var softFailStreak = 0;
	var lastGoodAt = 0;
	var pollGen = 0;

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
			// Stay dismissed for 10 minutes unless user opens Notifications.
			bannerDismissedUntil = Date.now() + 10 * 60 * 1000;
		});
		return bannerEl;
	}

	function showBreakBanner(fixUrl, mode) {
		if (Date.now() < bannerDismissedUntil) {
			return;
		}
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
		bannerDismissedUntil = 0;
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
			// Only re-open banner on a real transition to wait/off after being healthy,
			// not on every soft poll while already bad.
			if (lastMode === 'ok' || lastMode === '') {
				bannerShownForBreak = false;
			}
			showBreakBanner(fixUrl, mode);
		}

		lastMode = mode;
		var next =
			mode === 'ok' ? POLL_OK_MS : mode === 'checking' ? POLL_SOFT_MS : POLL_BAD_MS;
		schedule(next);
	}

	function schedule(ms) {
		if (timer) {
			clearTimeout(timer);
		}
		timer = setTimeout(poll, ms);
	}

	function mapStatus(st) {
		st = st || {};
		var err = String(st.error || '');
		if (st.ready) {
			return { mode: 'ok', err: '' };
		}
		// Cool-down / heal — not a hard outage for the banner.
		if (
			err === 'circuit_open' ||
			err === 'bridge_session_recovering' ||
			st.state === 'starting' ||
			st.state === 'restarting' ||
			st.state === 'recovering'
		) {
			return { mode: 'checking', err: err };
		}
		if (st.ok || st.has_qr || st.state === 'qr') {
			return { mode: 'wait', err: err };
		}
		return { mode: 'off', err: err };
	}

	function acceptMapped(mapped) {
		if (mapped.mode === 'ok') {
			badStreak = 0;
			softFailStreak = 0;
			lastGoodAt = Date.now();
			applyMode('ok', '');
			return;
		}
		if (mapped.mode === 'wait') {
			badStreak = 0;
			softFailStreak = 0;
			applyMode('wait', mapped.err);
			return;
		}
		if (mapped.mode === 'checking') {
			softFailStreak += 1;
			// Keep last good chip briefly while bridge is healing.
			if (lastMode === 'ok' && Date.now() - lastGoodAt < 120000) {
				applyMode('checking', mapped.err);
				return;
			}
			if (lastMode === 'ok' || lastMode === 'checking' || lastMode === '') {
				applyMode('checking', mapped.err);
				return;
			}
			applyMode(lastMode === 'wait' ? 'wait' : 'checking', mapped.err);
			return;
		}
		// Hard off — require a streak so one slow /status does not flash the banner.
		badStreak += 1;
		softFailStreak = 0;
		if (badStreak < BAD_STREAK_NEEDED && (lastMode === 'ok' || lastMode === 'checking' || lastMode === '')) {
			applyMode(lastMode === 'ok' ? 'checking' : lastMode || 'checking', mapped.err);
			return;
		}
		applyMode('off', mapped.err);
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

		var myGen = ++pollGen;
		fetch(url + (url.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now(), {
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (myGen !== pollGen) {
					return;
				}
				if (!data || data.message === 'session_expired') {
					acceptMapped({ mode: 'off', err: '' });
					return;
				}
				acceptMapped(mapStatus(data.status || {}));
			})
			.catch(function () {
				if (myGen !== pollGen) {
					return;
				}
				// Network blip / aborted nav — soft fail, do not scream Bridge down.
				softFailStreak += 1;
				if (lastMode === 'ok' && softFailStreak < BAD_STREAK_NEEDED) {
					applyMode('checking', '');
					return;
				}
				if (softFailStreak < BAD_STREAK_NEEDED) {
					applyMode(lastMode === 'off' ? 'off' : 'checking', '');
					return;
				}
				acceptMapped({ mode: 'off', err: '' });
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
