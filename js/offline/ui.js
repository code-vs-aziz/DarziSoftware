/**
 * Offline status chip, banner, Sync Now, toast.
 */
(function (window, document) {
	'use strict';

	var panelEl = null;

	function t(key, fallback) {
		var lang = window.APP_LANG || {};
		return lang[key] || fallback || key;
	}

	function chips() {
		return document.querySelectorAll('[data-offline-chip="1"]');
	}

	function formatTime(iso) {
		if (!iso) {
			return '—';
		}
		try {
			var d = new Date(iso);
			return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		} catch (e) {
			return iso;
		}
	}

	function setChipMode(mode, pending, extraLabel) {
		var labelMap = {
			online: t('offline_chip_online', 'Online'),
			offline: t('offline_chip_offline', 'Offline'),
			pending: t('offline_chip_pending', 'Pending Sync'),
			syncing: t('offline_chip_syncing', 'Syncing'),
			synced: t('offline_chip_synced', 'Synced'),
			sync_failed: t('offline_chip_failed', 'Sync Failed'),
			checking: t('offline_chip_checking', 'Checking…')
		};
		chips().forEach(function (chip) {
			chip.className = chip.className
				.replace(/\bis-\w+/g, '')
				.trim();
			chip.classList.add('dz-offline-chip');
			if (chip.getAttribute('data-variant') === 'mobile') {
				chip.classList.add('dz-offline-chip--mobile');
			} else {
				chip.classList.add('dz-offline-chip--desktop');
			}
			chip.classList.add('is-' + mode);
			var label = chip.querySelector('[data-offline-label]');
			var text = labelMap[mode] || mode;
			if (pending > 0 && (mode === 'online' || mode === 'offline' || mode === 'pending')) {
				text = labelMap.pending + ' (' + pending + ')';
				chip.classList.remove('is-online', 'is-offline');
				chip.classList.add('is-pending');
			}
			if (extraLabel) {
				text = extraLabel;
			}
			if (label) {
				label.textContent = text;
			}
			var countEl = chip.querySelector('[data-offline-count]');
			if (countEl) {
				countEl.textContent = pending > 0 ? String(pending) : '';
				countEl.hidden = pending < 1;
			}
		});
		updateBanner(mode, pending);
	}

	function ensureBanner() {
		var el = document.getElementById('dz-offline-banner');
		if (el) {
			return el;
		}
		el = document.createElement('div');
		el.id = 'dz-offline-banner';
		el.className = 'dz-offline-banner';
		el.setAttribute('role', 'status');
		el.hidden = true;
		el.innerHTML =
			'<span class="dz-offline-banner__dot" aria-hidden="true"></span>' +
			'<span class="dz-offline-banner__text"></span>' +
			'<button type="button" class="dz-offline-banner__sync" data-offline-sync="1"></button>';
		document.body.appendChild(el);
		el.querySelector('[data-offline-sync]').addEventListener('click', function () {
			triggerSync();
		});
		return el;
	}

	function updateBanner(mode, pending) {
		var el = ensureBanner();
		var text = el.querySelector('.dz-offline-banner__text');
		var btn = el.querySelector('[data-offline-sync]');
		btn.textContent = t('offline_sync_now', 'Sync Now');
		if (mode === 'offline') {
			text.textContent = t(
				'offline_banner_offline',
				'Offline — Changes will sync when connection is restored'
			);
			el.hidden = false;
			el.classList.add('is-offline');
			el.classList.remove('is-pending');
			btn.hidden = pending < 1;
		} else if (pending > 0) {
			text.textContent =
				t('offline_banner_pending', 'Pending changes ready to sync') + ' (' + pending + ')';
			el.hidden = false;
			el.classList.remove('is-offline');
			el.classList.add('is-pending');
			btn.hidden = false;
		} else if (mode === 'online' || mode === 'synced') {
			text.textContent = t('offline_banner_online', 'Online — All changes synchronized');
			el.hidden = true;
			btn.hidden = true;
		} else {
			el.hidden = mode !== 'sync_failed';
			if (mode === 'sync_failed') {
				text.textContent = t('offline_banner_failed', 'Sync failed — tap Sync Now to retry');
				btn.hidden = false;
			}
		}
	}

	function toast(message, kind) {
		var el = document.getElementById('dz-offline-toast');
		if (!el) {
			el = document.createElement('div');
			el.id = 'dz-offline-toast';
			el.className = 'dz-offline-toast';
			el.setAttribute('role', 'status');
			document.body.appendChild(el);
		}
		el.className = 'dz-offline-toast is-' + (kind || 'info');
		el.textContent = message;
		el.hidden = false;
		window.clearTimeout(toast._t);
		toast._t = window.setTimeout(function () {
			el.hidden = true;
		}, 4500);
	}

	function showSummary(summary) {
		if (!summary) {
			return;
		}
		if (!summary.ok) {
			toast(t('offline_sync_failed', 'Sync Failed') + ': ' + (summary.error || ''), 'sync_failed');
			return;
		}
		var p = summary.push || {};
		var parts = [
			t('offline_sync_complete', 'Sync Complete'),
			(p.orders || 0) + ' ' + t('offline_sync_orders', 'orders'),
			(p.customers || 0) + ' ' + t('offline_sync_customers', 'customers'),
			(p.conflicts || 0) + ' ' + t('offline_sync_conflicts', 'conflicts'),
			t('offline_last_synced', 'Last synced') + ': ' + formatTime(summary.at)
		];
		toast(parts.join(' · '), 'synced');
		openPanel(summary);
	}

	function openPanel(summary) {
		if (!panelEl) {
			panelEl = document.createElement('div');
			panelEl.className = 'dz-offline-panel';
			panelEl.hidden = true;
			panelEl.innerHTML =
				'<div class="dz-offline-panel__card" role="dialog" aria-label="Sync status">' +
				'<div class="dz-offline-panel__head">' +
				'<strong data-panel-title></strong>' +
				'<button type="button" class="dz-offline-panel__close" aria-label="Close">&times;</button>' +
				'</div>' +
				'<div class="dz-offline-panel__body" data-panel-body></div>' +
				'<div class="dz-offline-panel__actions">' +
				'<button type="button" class="btn btn-primary btn-sm" data-panel-sync></button>' +
				'</div></div>';
			document.body.appendChild(panelEl);
			panelEl.querySelector('.dz-offline-panel__close').addEventListener('click', function () {
				panelEl.hidden = true;
			});
			panelEl.querySelector('[data-panel-sync]').addEventListener('click', function () {
				triggerSync();
			});
			panelEl.addEventListener('click', function (e) {
				if (e.target === panelEl) {
					panelEl.hidden = true;
				}
			});
		}
		panelEl.querySelector('[data-panel-title]').textContent = t('offline_sync_status', 'Sync status');
		panelEl.querySelector('[data-panel-sync]').textContent = t('offline_sync_now', 'Sync Now');
		var body = panelEl.querySelector('[data-panel-body]');
		Promise.all([
			window.DarziOffline.db.countPending(),
			window.DarziOffline.db.getMeta('last_sync_at', null),
			window.DarziOffline.db.getAll('conflicts')
		]).then(function (parts) {
			var pending = parts[0];
			var last = parts[1];
			var conflicts = (parts[2] || []).filter(function (c) {
				return c.status === 'open';
			});
			var s = summary || window.DarziOffline.sync.getLastSummary() || {};
			var p = s.push || {};
			body.innerHTML =
				'<ul class="dz-offline-panel__list">' +
				'<li>' +
				t('offline_pending_count', 'Pending changes') +
				': <strong>' +
				pending +
				'</strong></li>' +
				'<li>' +
				t('offline_last_synced', 'Last synced') +
				': <strong>' +
				formatTime(s.at || last) +
				'</strong></li>' +
				'<li>' +
				t('offline_sync_orders', 'Orders') +
				': ' +
				(p.orders || 0) +
				'</li>' +
				'<li>' +
				t('offline_sync_customers', 'Customers') +
				': ' +
				(p.customers || 0) +
				'</li>' +
				'<li>' +
				t('offline_sync_conflicts', 'Conflicts') +
				': ' +
				(conflicts.length || p.conflicts || 0) +
				'</li>' +
				'</ul>';
			panelEl.hidden = false;
		});
	}

	function refreshUi() {
		var conn = window.DarziOffline.connectivity.getState();
		return window.DarziOffline.db.countPending().then(function (pending) {
			var mode = conn.status;
			if (window.DarziOffline.sync.isSyncing()) {
				mode = 'syncing';
			} else if (pending > 0 && mode === 'online') {
				mode = 'pending';
			}
			setChipMode(mode, pending);
			return pending;
		});
	}

	function triggerSync() {
		setChipMode('syncing', 0);
		window.DarziOffline.sync
			.syncNow()
			.then(function (summary) {
				showSummary(summary);
				return refreshUi();
			})
			.catch(function (err) {
				setChipMode('sync_failed', 0);
				toast(t('offline_sync_failed', 'Sync Failed') + ': ' + ((err && err.message) || ''), 'sync_failed');
				return refreshUi();
			});
	}

	function bindChips() {
		chips().forEach(function (chip) {
			chip.addEventListener('click', function (e) {
				e.preventDefault();
				if (e.target && e.target.getAttribute('data-offline-sync') === '1') {
					triggerSync();
					return;
				}
				openPanel();
			});
		});
		document.querySelectorAll('[data-offline-sync="1"]').forEach(function (btn) {
			if (btn.closest('.dz-offline-banner')) {
				return;
			}
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				triggerSync();
			});
		});
	}

	function start() {
		bindChips();
		window.DarziOffline.connectivity.onChange(function () {
			refreshUi();
		});
		window.addEventListener('darzi-offline-queued', refreshUi);
		window.addEventListener('darzi-offline-sync', function (ev) {
			var phase = ev.detail && ev.detail.phase;
			if (phase === 'start' || phase === 'push' || phase === 'pull') {
				setChipMode('syncing', 0);
			}
			if (phase === 'done') {
				refreshUi();
			}
			if (phase === 'error') {
				setChipMode('sync_failed', 0);
			}
		});
		refreshUi();
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.ui = {
		start: start,
		refresh: refreshUi,
		toast: toast,
		triggerSync: triggerSync,
		openPanel: openPanel
	};
})(window, document);
