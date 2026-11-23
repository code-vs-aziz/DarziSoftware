/**
 * Offline status chip + bottom prepare bar (no popup toasts).
 */
(function (window, document) {
	'use strict';

	var panelEl = null;
	var progressState = {
		dataPercent: 0,
		pagePercent: 0,
		customers: 0,
		measurements: 0,
		pagesOk: 0,
		pagesTotal: 0,
		phase: 'idle',
		error: ''
	};

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
			return d.toLocaleString([], {
				month: 'short',
				day: 'numeric',
				hour: '2-digit',
				minute: '2-digit'
			});
		} catch (e) {
			return iso;
		}
	}

	function formatAge(iso) {
		if (!iso) {
			return t('offline_never_synced', 'Never');
		}
		var tMs = Date.parse(iso);
		if (!tMs) {
			return formatTime(iso);
		}
		var mins = Math.max(0, Math.round((Date.now() - tMs) / 60000));
		if (mins < 1) {
			return t('offline_age_just_now', 'Just now');
		}
		if (mins < 60) {
			return mins + ' ' + t('offline_age_min', 'min ago');
		}
		var hrs = Math.round(mins / 60);
		if (hrs < 48) {
			return hrs + ' ' + t('offline_age_hr', 'hr ago');
		}
		return formatTime(iso);
	}

	function getReadiness() {
		return Promise.all([
			window.DarziOffline.sync.countCached
				? window.DarziOffline.sync.countCached()
				: Promise.resolve({ customers: 0, measurements: 0, orders: 0 }),
			window.DarziOffline.db.getMeta('last_pull_at', null),
			window.DarziOffline.db.getMeta('last_sync_at', null),
			window.DarziOffline.db.getMeta('last_prefetch_at', null),
			window.DarziOffline.db.getMeta('last_prefetch_summary', null),
			window.DarziOffline.db.countPending()
		]).then(function (parts) {
			var counts = parts[0] || {};
			var lastPull = parts[1] || parts[2];
			var lastPrefetch = parts[3];
			var prefSummary = parts[4] || {};
			var pending = parts[5] || 0;
			var ageMs = lastPull ? Date.now() - Date.parse(lastPull) : Infinity;
			var stale = !lastPull || ageMs > 60 * 60 * 1000; // older than 1 hour
			var ready = (counts.customers || 0) > 0 && !!lastPull;
			return {
				ready: ready,
				stale: stale,
				customers: counts.customers || 0,
				measurements: counts.measurements || 0,
				orders: counts.orders || 0,
				pending: pending,
				lastPull: lastPull,
				lastPrefetch: lastPrefetch,
				pagesOk: prefSummary.ok || 0,
				pagesTotal: prefSummary.total || 0
			};
		});
	}

	function applyChipTitle(readyInfo) {
		var title;
		if (!readyInfo.ready) {
			title = t('offline_ready_no', 'Offline data not ready — connect online once');
		} else if (readyInfo.stale) {
			title =
				t('offline_ready_stale', 'Offline data may be outdated') +
				' · ' +
				t('offline_last_synced', 'Last synced') +
				' ' +
				formatAge(readyInfo.lastPull) +
				' · ' +
				readyInfo.customers +
				' ' +
				t('offline_sync_customers', 'customers');
		} else {
			title =
				t('offline_ready_yes', 'Offline up to date') +
				' · ' +
				readyInfo.customers +
				' ' +
				t('offline_sync_customers', 'customers') +
				' · ' +
				t('offline_last_synced', 'Last synced') +
				' ' +
				formatAge(readyInfo.lastPull);
		}
		chips().forEach(function (chip) {
			chip.setAttribute('title', title);
		});
		return title;
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
			// Pending = local changes waiting to upload (NOT "pages missing").
			if (pending > 0 && mode === 'online') {
				text = labelMap.pending + ' (' + pending + ')';
				chip.classList.remove('is-online', 'is-offline');
				chip.classList.add('is-pending');
			} else if (pending > 0 && mode === 'offline') {
				// Stay "Offline" — show count only; don't scare with Pending Sync.
				text = labelMap.offline + ' · ' + pending;
				chip.classList.add('is-offline');
			} else if (pending > 0 && mode === 'pending') {
				text = labelMap.pending + ' (' + pending + ')';
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
		// Silent by design: never popup. Reflect status on chip only.
		var mode = kind || 'pending';
		if (mode === 'sync_failed' || mode === 'offline' || mode === 'pending' || mode === 'synced' || mode === 'syncing') {
			window.DarziOffline.db.countPending().then(function (pending) {
				setChipMode(mode === 'synced' ? 'synced' : mode, pending || 0, null);
			}).catch(function () {
				setChipMode(mode === 'synced' ? 'synced' : mode, 0, null);
			});
		}
		chips().forEach(function (chip) {
			if (message) {
				chip.setAttribute('title', message);
			}
		});
	}

	function ensureProgressBar() {
		var el = document.getElementById('dz-offline-progress');
		if (el) {
			return el;
		}
		// Prefer under "powered by Aziz" footer; fallback create there.
		var footerBar = document.querySelector('.erp-global-footer__bar');
		el = document.createElement('div');
		el.id = 'dz-offline-progress';
		el.className = 'dz-offline-progress';
		el.setAttribute('role', 'status');
		el.hidden = true;
		el.innerHTML =
			'<div class="dz-offline-progress__track"><div class="dz-offline-progress__fill" data-offline-fill></div></div>' +
			'<div class="dz-offline-progress__row">' +
			'<span class="dz-offline-progress__text" data-offline-progress-text></span>' +
			'<span class="dz-offline-progress__meta" data-offline-progress-meta></span>' +
			'</div>';
		if (footerBar) {
			footerBar.appendChild(el);
		} else {
			document.body.appendChild(el);
			el.classList.add('dz-offline-progress--floating');
		}
		return el;
	}

	function combinedPercent() {
		var d = Math.max(0, Math.min(100, progressState.dataPercent || 0));
		var p = Math.max(0, Math.min(100, progressState.pagePercent || 0));
		// Data is more important for offline search than pages.
		return Math.round(d * 0.65 + p * 0.35);
	}

	function renderProgressBar(forceShow) {
		var el = ensureProgressBar();
		var fill = el.querySelector('[data-offline-fill]');
		var text = el.querySelector('[data-offline-progress-text]');
		var meta = el.querySelector('[data-offline-progress-meta]');
		var pct = combinedPercent();
		var online =
			window.DarziOffline.connectivity && window.DarziOffline.connectivity.isOnline();

		if (progressState.phase === 'error') {
			el.hidden = false;
			el.classList.add('is-error');
			el.classList.remove('is-ready', 'is-working');
			fill.style.width = '100%';
			text.textContent = t('offline_prepare_failed', 'Offline data failed — Sync Now');
			meta.textContent = progressState.error || '';
			return;
		}

		if (progressState.phase === 'ready' && progressState.customers > 0) {
			fill.style.width = '100%';
			text.textContent = t('offline_prepare_ready', 'Offline ready');
			meta.textContent =
				progressState.customers +
				' ' +
				t('offline_sync_customers', 'customers') +
				(progressState.pagesTotal
					? ' · ' + progressState.pagesOk + '/' + progressState.pagesTotal + ' pages'
					: '');
			el.classList.remove('is-error', 'is-working');
			el.classList.add('is-ready');
			el.hidden = false;
			window.clearTimeout(renderProgressBar._hide);
			renderProgressBar._hide = window.setTimeout(function () {
				if (progressState.phase === 'ready') {
					el.hidden = true;
				}
			}, 3500);
			return;
		}

		if (!online && progressState.customers < 1) {
			el.hidden = false;
			el.classList.add('is-error');
			el.classList.remove('is-ready', 'is-working');
			fill.style.width = '100%';
			text.textContent = t(
				'offline_prepare_missing',
				'Offline data not downloaded — connect once'
			);
			meta.textContent = '';
			return;
		}

		if (forceShow || progressState.phase === 'start' || progressState.phase === 'download' || progressState.phase === 'pages') {
			el.hidden = false;
			el.classList.add('is-working');
			el.classList.remove('is-error', 'is-ready');
			fill.style.width = pct + '%';
			text.textContent =
				t('offline_prepare_working', 'Preparing offline') + ' ' + pct + '%';
			meta.textContent =
				(progressState.customers || 0) +
				' customers' +
				(progressState.pagesTotal
					? ' · pages ' + (progressState.pagesOk || 0) + '/' + progressState.pagesTotal
					: '');
			return;
		}

		if (!forceShow && progressState.phase === 'idle') {
			el.hidden = true;
		}
	}

	function showReadyState(opts) {
		opts = opts || {};
		if (!window.DarziOffline.sync || !window.DarziOffline.sync.countCached) {
			return Promise.resolve();
		}
		return window.DarziOffline.sync.countCached().then(function (counts) {
			progressState.customers = counts.customers || 0;
			progressState.measurements = counts.measurements || 0;
			if (counts.customers > 0) {
				progressState.phase = 'ready';
				progressState.dataPercent = 100;
				if (opts.quiet) {
					// Already prepared — keep footer clean on refresh/navigation.
					var el = ensureProgressBar();
					el.hidden = true;
					el.classList.remove('is-working', 'is-error');
					el.classList.add('is-ready');
					return counts;
				}
			} else if (
				window.DarziOffline.connectivity &&
				!window.DarziOffline.connectivity.isOnline()
			) {
				progressState.phase = 'error';
				progressState.error = 'no_cache';
			}
			renderProgressBar(!opts.quiet);
			return counts;
		});
	}

	function showSummary(summary) {
		if (!summary) {
			return;
		}
		if (!summary.ok) {
			setChipMode('sync_failed', 0, t('offline_chip_failed', 'Sync Failed'));
			chips().forEach(function (chip) {
				chip.setAttribute(
					'title',
					t('offline_sync_failed', 'Sync Failed') + ': ' + (summary.error || '')
				);
			});
			return;
		}
		setChipMode('synced', 0);
		chips().forEach(function (chip) {
			chip.setAttribute(
				'title',
				t('offline_sync_complete', 'Sync Complete') +
					' · ' +
					t('offline_last_synced', 'Last synced') +
					': ' +
					formatTime(summary.at)
			);
		});
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
				'<button type="button" class="btn btn-outline-secondary btn-sm" data-panel-refresh-offline></button>' +
				'<button type="button" class="btn btn-primary btn-sm" data-panel-sync></button>' +
				'</div></div>';
			document.body.appendChild(panelEl);
			panelEl.querySelector('.dz-offline-panel__close').addEventListener('click', function () {
				panelEl.hidden = true;
			});
			panelEl.querySelector('[data-panel-sync]').addEventListener('click', function () {
				panelEl.hidden = true;
				triggerSync();
			});
			panelEl.querySelector('[data-panel-refresh-offline]').addEventListener('click', function () {
				panelEl.hidden = true;
				refreshOfflineData();
			});
			panelEl.addEventListener('click', function (e) {
				if (e.target === panelEl) {
					panelEl.hidden = true;
					return;
				}
				var btn = e.target && e.target.closest ? e.target.closest('[data-resolve-conflict]') : null;
				if (!btn) {
					return;
				}
				e.preventDefault();
				var conflictId = btn.getAttribute('data-conflict-id');
				var resolution = btn.getAttribute('data-resolve-conflict');
				if (!conflictId || !resolution || !window.DarziOffline.sync.resolveConflict) {
					return;
				}
				btn.disabled = true;
				window.DarziOffline.sync
					.resolveConflict(conflictId, resolution)
					.then(function () {
						toast(
							resolution === 'keep_local'
								? t('offline_conflict_kept_local', 'Kept local changes — syncing')
								: t('offline_conflict_kept_server', 'Kept server data'),
							resolution === 'keep_local' ? 'pending' : 'synced'
						);
						openPanel();
						refreshUi();
					})
					.catch(function (err) {
						btn.disabled = false;
						toast(
							t('offline_conflict_resolve_failed', 'Could not resolve conflict: ') +
								((err && err.message) || 'error'),
							'sync_failed'
						);
					});
			});
		}
		panelEl.querySelector('[data-panel-title]').textContent = t(
			'offline_sync_status',
			'Offline status'
		);
		panelEl.querySelector('[data-panel-sync]').textContent = t('offline_sync_now', 'Sync Now');
		panelEl.querySelector('[data-panel-refresh-offline]').textContent = t(
			'offline_refresh_data',
			'Refresh offline data'
		);
		var body = panelEl.querySelector('[data-panel-body]');
		Promise.all([
			getReadiness(),
			window.DarziOffline.db.getAll('conflicts'),
			window.DarziOffline.db.getAll('sync_queue'),
			window.DarziOffline.db.getMeta('schema_status', null)
		]).then(function (parts) {
			var readyInfo = parts[0];
			var conflicts = (parts[1] || []).filter(function (c) {
				return c.status === 'open';
			});
			var failedPermanent = (parts[2] || []).filter(function (op) {
				return op && op.status === 'failed_permanent';
			}).length;
			var schema = parts[3] || null;
			var s = summary || window.DarziOffline.sync.getLastSummary() || {};
			var statusLine = !readyInfo.ready
				? t('offline_ready_no', 'Offline data not ready — connect online once')
				: readyInfo.stale
					? t('offline_ready_stale', 'Offline data may be outdated')
					: t('offline_ready_yes', 'Offline up to date');
			var conflictHtml = '';
			if (conflicts.length) {
				conflictHtml =
					'<div class="dz-offline-conflicts">' +
					'<h4 class="dz-offline-conflicts__title">' +
					t('offline_conflict_resolve_title', 'Resolve conflicts') +
					'</h4>' +
					conflicts
						.map(function (c) {
							var fields = Array.isArray(c.conflict_fields)
								? c.conflict_fields.join(', ')
								: String(c.conflict_fields || '');
							var allowLocal =
								c.entity_type !== 'delivery' && c.entity_type !== 'cash';
							return (
								'<div class="dz-offline-conflict" data-conflict-row="' +
								c.id +
								'">' +
								'<div class="dz-offline-conflict__meta">' +
								'<strong>' +
								(c.entity_type || 'record') +
								'</strong> #' +
								(c.server_entity_id || '—') +
								(fields
									? ' <span class="dz-offline-panel__muted">(' + fields + ')</span>'
									: '') +
								'</div>' +
								'<div class="dz-offline-conflict__actions">' +
								(allowLocal
									? '<button type="button" class="btn btn-sm btn-outline-primary" data-resolve-conflict="keep_local" data-conflict-id="' +
									  c.id +
									  '">' +
									  t('offline_keep_local', 'Keep Local') +
									  '</button>'
									: '') +
								'<button type="button" class="btn btn-sm btn-primary" data-resolve-conflict="keep_server" data-conflict-id="' +
								c.id +
								'">' +
								t('offline_keep_server', 'Keep Server') +
								'</button>' +
								'</div></div>'
							);
						})
						.join('') +
					'</div>';
			}
			var schemaLine = '';
			if (schema && schema.ready === false) {
				schemaLine =
					'<p class="dz-offline-panel__status is-warn">' +
					t('offline_schema_incomplete', 'Sync schema incomplete') +
					': ' +
					((schema.missing && schema.missing.join(', ')) || '') +
					'</p>';
			} else if (schema && schema.ready) {
				schemaLine =
					'<p class="dz-offline-panel__status is-ok">' +
					t('offline_schema_ready', 'Sync schema ready') +
					'</p>';
			}
			body.innerHTML =
				'<p class="dz-offline-panel__status ' +
				(!readyInfo.ready || readyInfo.stale ? 'is-warn' : 'is-ok') +
				'">' +
				statusLine +
				'</p>' +
				schemaLine +
				'<ul class="dz-offline-panel__list">' +
				'<li>' +
				t('offline_cached_customers', 'Cached customers') +
				': <strong>' +
				readyInfo.customers +
				'</strong></li>' +
				'<li>' +
				t('offline_cached_measurements', 'Cached measurements') +
				': <strong>' +
				readyInfo.measurements +
				'</strong></li>' +
				'<li>' +
				t('offline_cached_orders', 'Cached open orders') +
				': <strong>' +
				readyInfo.orders +
				'</strong></li>' +
				'<li>' +
				t('offline_last_synced', 'Last synced') +
				': <strong>' +
				formatAge(readyInfo.lastPull || s.at) +
				'</strong> <span class="dz-offline-panel__muted">(' +
				formatTime(readyInfo.lastPull || s.at) +
				')</span></li>' +
				'<li>' +
				t('offline_pages_cached', 'Pages cached') +
				': <strong>' +
				(readyInfo.pagesTotal
					? readyInfo.pagesOk + '/' + readyInfo.pagesTotal
					: '—') +
				'</strong></li>' +
				'<li>' +
				t('offline_pending_count', 'Pending changes') +
				': <strong>' +
				readyInfo.pending +
				'</strong></li>' +
				'<li>' +
				t('offline_sync_conflicts', 'Conflicts') +
				': <strong>' +
				conflicts.length +
				'</strong></li>' +
				'<li>' +
				t('offline_failed_permanent', 'Failed (needs review)') +
				': <strong>' +
				failedPermanent +
				'</strong></li>' +
				'</ul>' +
				conflictHtml;
			applyChipTitle(readyInfo);
			panelEl.hidden = false;
		});
	}

	function refreshUi() {
		var conn = window.DarziOffline.connectivity.getState();
		return getReadiness().then(function (readyInfo) {
			var pending = readyInfo.pending || 0;
			var mode = conn.status;
			if (window.DarziOffline.sync.isSyncing()) {
				mode = 'syncing';
			} else if (pending > 0 && mode === 'online') {
				mode = 'pending';
			} else if (mode === 'online' && readyInfo.ready && !readyInfo.stale) {
				mode = 'synced';
			} else if (mode === 'online' && readyInfo.ready && readyInfo.stale) {
				// Stale cache ≠ pending uploads
				mode = 'online';
			} else if (mode === 'offline' && !readyInfo.ready) {
				mode = 'sync_failed';
			}
			var extra = null;
			if (mode === 'synced') {
				extra = t('offline_chip_ready', 'Offline OK');
			} else if (mode === 'online' && readyInfo.stale && pending < 1) {
				extra = t('offline_chip_stale', 'Update offline');
			} else if (mode === 'sync_failed' && !readyInfo.ready) {
				extra = t('offline_chip_no_data', 'No offline data');
			}
			setChipMode(mode, pending, extra);
			applyChipTitle(readyInfo);
			return pending;
		});
	}

	var syncClickBusy = false;
	var FRESH_MS = 55 * 60 * 1000; // ~1 hour — already synced = skip heavy work

	function ageMs(iso) {
		if (!iso) {
			return Infinity;
		}
		var t = Date.parse(iso);
		return t ? Date.now() - t : Infinity;
	}

	/** Check local cache before re-downloading everything. */
	function assessOfflineReady() {
		var sync = window.DarziOffline.sync;
		var db = window.DarziOffline.db;
		return Promise.all([
			sync.countCached(),
			db.getMeta('last_pull_at', null),
			db.getMeta('last_prefetch_at', null),
			db.getMeta('offline_lists', null),
			db.countPending()
		]).then(function (parts) {
			var counts = parts[0] || {};
			var lastPull = parts[1];
			var lastPrefetch = parts[2];
			var lists = parts[3] || {};
			var pending = parts[4] || 0;
			var listCount =
				((lists.order_status && lists.order_status.current && lists.order_status.current.length) || 0) +
				((lists.order_status && lists.order_status.delivered && lists.order_status.delivered.length) || 0);
			var dataFresh = (counts.customers || 0) > 0 && ageMs(lastPull) < FRESH_MS;
			var listsFresh = listCount > 0 && ageMs(lists.at) < FRESH_MS;
			var pagesFresh = !!lastPrefetch && ageMs(lastPrefetch) < 6 * 60 * 60 * 1000;
			return {
				counts: counts,
				pending: pending,
				listCount: listCount,
				dataFresh: dataFresh,
				listsFresh: listsFresh,
				pagesFresh: pagesFresh,
				fullyReady: dataFresh && listsFresh && pagesFresh && pending < 1
			};
		});
	}

	function markAlreadyReady(info) {
		progressState.phase = 'ready';
		progressState.dataPercent = 100;
		progressState.pagesPercent = 100;
		progressState.customers = (info.counts && info.counts.customers) || progressState.customers || 0;
		renderProgressBar(true);
		setChipMode(
			'online',
			info.pending || 0,
			t('offline_chip_ready', 'Offline OK')
		);
		chips().forEach(function (chip) {
			chip.setAttribute(
				'title',
				t('offline_already_synced', 'Already up to date — no full download needed')
			);
		});
		if (typeof window.DarziOffline.ui.toast === 'function') {
			/* quiet — progress bar is enough */
		}
		return showReadyState({ quiet: true });
	}

	function refreshOfflineData() {
		// Same smart path as Sync Now (no forced full rebuild).
		triggerSync({ forceFull: false });
	}

	/**
	 * Sync Now:
	 * - If cache already fresh → verify only (no 520-page re-download)
	 * - If missing/stale → delta + fill gaps
	 * - forceFull:true only when explicitly requested
	 */
	function triggerSync(opts) {
		opts = opts || {};
		var forceFull = !!opts.forceFull;

		if (!window.DarziOffline.connectivity.isOnline()) {
			showReadyState();
			return;
		}
		if (syncClickBusy || window.DarziOffline.sync.isSyncing()) {
			return;
		}
		syncClickBusy = true;

		assessOfflineReady()
			.then(function (info) {
				// Already complete — do NOT restart 100% download.
				if (!forceFull && info.fullyReady) {
					return window.DarziOffline.sync
						.deltaSyncIfDue({ maxAgeMs: FRESH_MS })
						.then(function () {
							if (window.DarziOffline.prefetch && window.DarziOffline.prefetch.ensureCore) {
								return window.DarziOffline.prefetch.ensureCore();
							}
							return null;
						})
						.then(function () {
							return markAlreadyReady(info);
						});
				}

				setChipMode('syncing', info.pending || 0, t('offline_chip_caching', 'Caching…'));
				progressState.phase = 'download';
				progressState.dataPercent = info.dataFresh ? 80 : 10;
				progressState.customers = (info.counts && info.counts.customers) || 0;
				renderProgressBar(true);

				// Smart: push pending + delta pull if data exists; full bootstrap only if empty.
				var dataStep = forceFull
					? window.DarziOffline.sync.ensureOfflineData({ force: true })
					: info.pending > 0
						? window.DarziOffline.sync.syncNow({ quiet: false })
						: window.DarziOffline.sync.ensureOfflineData({ force: false });

				return Promise.resolve(dataStep)
					.then(function (summary) {
						if (summary && summary.push) {
							showSummary(summary);
						}
						var listsWarm =
							window.DarziOffline.lists && window.DarziOffline.lists.warm
								? window.DarziOffline.lists.warm(!!forceFull || !info.listsFresh)
								: Promise.resolve();
						return listsWarm;
					})
					.then(function () {
						// Auto-cache measurement/order forms — no manual page open.
						if (
							window.DarziOffline.nav &&
							typeof window.DarziOffline.nav.ensureFormTemplates === 'function'
						) {
							return window.DarziOffline.nav.ensureFormTemplates();
						}
						return null;
					})
					.then(function () {
						if (!window.DarziOffline.prefetch) {
							return null;
						}
						// forceFull → full crawl; else gap-fill + skip if recent.
						return window.DarziOffline.prefetch.run(!!forceFull);
					})
					.then(function () {
						return refreshUi();
					})
					.then(function () {
						return showReadyState();
					});
			})
			.catch(function (err) {
				if (err && err.message === 'sync_in_progress') {
					return refreshUi();
				}
				setChipMode('sync_failed', 0, t('offline_chip_failed', 'Sync Failed'));
				chips().forEach(function (chip) {
					chip.setAttribute(
						'title',
						t('offline_sync_failed', 'Sync Failed') + ': ' + ((err && err.message) || '')
					);
				});
				return refreshUi();
			})
			.then(function () {
				syncClickBusy = false;
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
		ensureProgressBar();
		window.DarziOffline.connectivity.onChange(function () {
			refreshUi();
			showReadyState();
		});
		window.addEventListener('darzi-offline-queued', refreshUi);
		window.addEventListener('darzi-offline-sync', function (ev) {
			var detail = (ev && ev.detail) || {};
			var phase = detail.phase;
			var quiet = !!detail.quiet || (detail.summary && detail.summary.quiet);
			if (quiet) {
				if (phase === 'done' || phase === 'error') {
					refreshUi();
				}
				return;
			}
			if (phase === 'start' || phase === 'push' || phase === 'pull') {
				setChipMode('syncing', 0);
				progressState.phase = 'download';
				progressState.dataPercent = phase === 'start' ? 15 : phase === 'push' ? 35 : 55;
				renderProgressBar(true);
			}
			if (phase === 'done') {
				progressState.dataPercent = 100;
				refreshUi();
				showReadyState();
			}
			if (phase === 'error') {
				setChipMode('sync_failed', 0);
				progressState.phase = 'error';
				progressState.error = detail.error || '';
				renderProgressBar(true);
			}
		});
		window.addEventListener('darzi-offline-data', function (ev) {
			var d = (ev && ev.detail) || {};
			progressState.phase = d.phase || progressState.phase;
			if (typeof d.percent === 'number') {
				progressState.dataPercent = d.percent;
			}
			if (typeof d.customers === 'number') {
				progressState.customers = d.customers;
			}
			if (typeof d.measurements === 'number') {
				progressState.measurements = d.measurements;
			}
			if (d.phase === 'error') {
				progressState.error = d.error || '';
			}
			if (d.phase === 'ready') {
				progressState.dataPercent = 100;
			}
			// Already cached on this device — no progress flash on every page.
			if (d.skipped && d.phase === 'ready') {
				showReadyState({ quiet: true });
				refreshUi();
				return;
			}
			renderProgressBar(true);
			setChipMode(
				d.phase === 'error' ? 'sync_failed' : d.phase === 'ready' ? 'synced' : 'syncing',
				0,
				d.phase === 'ready' ? t('offline_chip_synced', 'Synced') : t('offline_chip_caching', 'Caching…')
			);
			if (d.phase === 'ready' || d.phase === 'error') {
				refreshUi();
			}
		});
		window.addEventListener('darzi-offline-prefetch', function (ev) {
			var d = (ev && ev.detail) || {};
			if (d.phase === 'skipped') {
				showReadyState({ quiet: true });
				refreshUi();
				return;
			}
			if (d.phase === 'start') {
				progressState.phase = 'pages';
				progressState.pagePercent = 5;
				progressState.pagesOk = 0;
				progressState.pagesTotal = 0;
				setChipMode('syncing', 0, t('offline_chip_caching', 'Caching…'));
				renderProgressBar(true);
			}
			if (typeof d.done === 'number' && typeof d.total === 'number' && d.total > 0) {
				progressState.phase = 'pages';
				progressState.pagesOk = d.ok || d.done;
				progressState.pagesTotal = d.total;
				progressState.pagePercent = Math.round((d.done / d.total) * 100);
				renderProgressBar(true);
			}
			if (d.phase === 'done') {
				var s = d.summary || {};
				progressState.pagesOk = s.ok || 0;
				progressState.pagesTotal = s.total || 0;
				progressState.pagePercent = 100;
				if (progressState.customers > 0) {
					progressState.phase = 'ready';
				}
				renderProgressBar(true);
				refreshUi();
			}
			if (d.phase === 'error') {
				refreshUi();
			}
		});
		refreshUi();
		showReadyState();
	}

	window.DarziOffline = window.DarziOffline || {};
	window.DarziOffline.ui = {
		start: start,
		refresh: refreshUi,
		toast: toast,
		triggerSync: triggerSync,
		refreshOfflineData: refreshOfflineData,
		openPanel: openPanel,
		showReadyState: showReadyState
	};
})(window, document);
