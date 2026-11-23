<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');

$settings = isset($settings) ? $settings : array();
$queue = isset($queue) ? $queue : array();
$stats = isset($stats) ? $stats : array();
$table_ready = !empty($table_ready);

$event_labels = array(
	'order_booked' => t('notify_event_booked'),
	'order_ready' => t('notify_event_ready'),
	'payment_reminder' => t('notify_event_payment'),
	'delivery_confirm' => t('notify_event_delivery'),
);
?>
<style>
	.notify-template-field { min-height: 72px; font-size: 14px; }
	.notify-stats { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
	.notify-stat {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 0.65rem 1rem;
		min-width: 110px;
	}
	.notify-stat strong { display: block; font-size: 1.25rem; color: #0f172a; }
	.notify-stat span { color: #64748b; font-size: 12px; }
	.notify-msg-preview { max-width: 320px; white-space: normal; font-size: 13px; }
	.notify-setup-warn {
		background: #fff7ed;
		border: 1px solid #fdba74;
		border-radius: 8px;
		padding: 1rem;
		margin-bottom: 1rem;
	}
	.notify-bridge-panel {
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		padding: 1rem 1.15rem;
		margin-bottom: 1.25rem;
		background: linear-gradient(180deg, #f8fafc 0%, #fff 70%);
	}
	.notify-bridge-panel h5 { margin-bottom: 0.35rem; }
	.notify-bridge-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 0.75rem 0; }
	.notify-bridge-qr-wrap {
		display: none;
		text-align: center;
		padding: 0.75rem;
		background: #fff;
		border: 1px dashed #cbd5e1;
		border-radius: 8px;
		margin-top: 0.75rem;
	}
	.notify-bridge-qr-wrap.is-visible { display: block; }
	.notify-bridge-qr-wrap img {
		max-width: 280px;
		width: 100%;
		height: auto;
		border-radius: 6px;
	}
	.notify-bridge-state { font-size: 14px; margin: 0; }
	.notify-bridge-state .ok { color: #059669; font-weight: 600; }
	.notify-bridge-state .wait { color: #d97706; font-weight: 600; }
	.notify-bridge-state .off { color: #dc2626; font-weight: 600; }
	.notify-bridge-hint { font-size: 12px; color: #64748b; margin-top: 0.35rem; }
</style>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('notify_title'); ?></h2>
								<p class="text-muted mb-0 mt-1" style="font-size:13px;"><?php echo t('notify_subtitle'); ?></p>
							</div>
							<div class="card-body erp-card-body">
								<?php if ($this->session->flashdata('notify_saved') === 'templates_restored') { ?>
									<div class="alert alert-success"><?php echo t('notify_templates_restored'); ?></div>
								<?php } elseif ($this->session->flashdata('notify_saved')) { ?>
									<div class="alert alert-success"><?php echo t('notify_saved_ok'); ?></div>
								<?php } ?>
								<?php if ($this->session->flashdata('notify_error')) { ?>
									<div class="alert alert-danger"><?php echo t('notify_saved_fail'); ?></div>
								<?php } ?>
								<?php if ($this->session->flashdata('notify_processed')) { ?>
									<div class="alert alert-info"><?php echo sprintf(t('notify_processed_ok'), (int) $this->session->flashdata('notify_processed')); ?></div>
								<?php } ?>
								<?php if ($this->session->flashdata('notify_reminders_queued')) { ?>
									<div class="alert alert-info"><?php echo sprintf(t('notify_reminders_ok'), (int) $this->session->flashdata('notify_reminders_queued')); ?></div>
								<?php } ?>

								<?php if (!$table_ready) { ?>
									<div class="notify-setup-warn">
										<strong><?php echo t('notify_table_missing'); ?></strong>
										<p class="mb-2 mt-2"><?php echo t('notify_table_missing_help'); ?></p>
										<code>application/sql/notification_automation.sql</code>
									</div>
								<?php } else { ?>
									<div class="notify-stats">
										<div class="notify-stat"><strong><?php echo (int) $stats['total']; ?></strong><span><?php echo t('notify_stat_total'); ?></span></div>
										<div class="notify-stat"><strong><?php echo (int) $stats['sent']; ?></strong><span><?php echo t('notify_stat_sent'); ?></span></div>
										<div class="notify-stat"><strong><?php echo (int) $stats['pending']; ?></strong><span><?php echo t('notify_stat_pending'); ?></span></div>
										<div class="notify-stat"><strong><?php echo (int) $stats['failed']; ?></strong><span><?php echo t('notify_stat_failed'); ?></span></div>
										<div class="notify-stat"><strong><?php echo (int) $stats['skipped']; ?></strong><span><?php echo t('notify_stat_skipped'); ?></span></div>
									</div>

									<div class="mb-3">
										<a href="<?php echo site_url('Notifications/process_pending'); ?>" class="btn btn-sm btn-outline-primary dz-btn--outlined" data-no-ajax="true"><?php echo t('notify_process_pending'); ?></a>
										<a href="<?php echo site_url('Notifications/run_payment_reminders'); ?>" class="btn btn-sm btn-outline-secondary dz-btn--outlined" data-no-ajax="true"><?php echo t('notify_scan_reminders'); ?></a>
										<a href="<?php echo site_url('Notifications/restore_templates'); ?>" class="btn btn-sm btn-outline-info dz-btn--outlined" data-no-ajax="true"><?php echo t('notify_restore_templates'); ?></a>
									</div>

									<div class="notify-bridge-panel" id="notify-bridge-panel"
										data-status-url="<?php echo esc_attr(site_url('Notifications/bridge_status_json')); ?>"
										data-start-url="<?php echo esc_attr(site_url('Notifications/bridge_start')); ?>"
										data-restart-url="<?php echo esc_attr(site_url('Notifications/bridge_restart')); ?>"
										data-reset-url="<?php echo esc_attr(site_url('Notifications/bridge_reset_qr')); ?>">
										<h5><?php echo t('notify_bridge_panel_title'); ?></h5>
										<p class="notify-bridge-state mt-2" id="notify-bridge-state-text">
											<strong><?php echo t('notify_bridge_status'); ?>:</strong>
											<span id="notify-bridge-state-label">…</span>
										</p>
										<p class="notify-bridge-hint" id="notify-bridge-error" style="display:none;"></p>
										<div class="notify-bridge-actions">
											<button type="button" class="btn btn-sm btn-primary dz-btn--filled" id="notify-bridge-start"><?php echo t('notify_bridge_btn_start'); ?></button>
											<button type="button" class="btn btn-sm btn-warning" id="notify-bridge-restart"><?php echo t('notify_bridge_btn_restart'); ?></button>
											<button type="button" class="btn btn-sm btn-danger" id="notify-bridge-reset-qr"><?php echo t('notify_bridge_btn_reset_qr'); ?></button>
											<button type="button" class="btn btn-sm btn-outline-secondary dz-btn--outlined" id="notify-bridge-refresh"><?php echo t('notify_bridge_btn_refresh'); ?></button>
										</div>
										<div class="notify-bridge-qr-wrap" id="notify-bridge-qr-wrap">
											<p class="mb-2"><strong><?php echo t('notify_bridge_scan_qr'); ?></strong></p>
											<img id="notify-bridge-qr-img" alt="WhatsApp QR" width="280" height="280">
											<p class="notify-bridge-hint mb-0"><?php echo t('notify_bridge_scan_help'); ?></p>
										</div>
									</div>

									<form action="<?php echo site_url('Notifications/save_settings'); ?>" method="post" class="mb-4" data-no-ajax="true">
										<?php $this->load->view('partials/csrf_field'); ?>
										<h5 class="mb-3"><?php echo t('notify_settings_heading'); ?></h5>
										<div class="row">
											<div class="col-md-6">
												<div class="form-check mb-2">
													<input class="form-check-input" type="checkbox" name="enabled_order_booked" id="en_booked" value="1" <?php echo !empty($settings['enabled_order_booked']) ? 'checked' : ''; ?>>
													<label class="form-check-label" for="en_booked"><?php echo t('notify_enable_booked'); ?></label>
												</div>
												<div class="form-check mb-2">
													<input class="form-check-input" type="checkbox" name="enabled_order_ready" id="en_ready" value="1" <?php echo !empty($settings['enabled_order_ready']) ? 'checked' : ''; ?>>
													<label class="form-check-label" for="en_ready"><?php echo t('notify_enable_ready'); ?></label>
												</div>
												<div class="form-check mb-2">
													<input class="form-check-input" type="checkbox" name="enabled_payment_reminder" id="en_payment" value="1" <?php echo !empty($settings['enabled_payment_reminder']) ? 'checked' : ''; ?>>
													<label class="form-check-label" for="en_payment"><?php echo t('notify_enable_payment'); ?></label>
												</div>
												<div class="form-check mb-2">
													<input class="form-check-input" type="checkbox" name="enabled_delivery_confirm" id="en_delivery" value="1" <?php echo !empty($settings['enabled_delivery_confirm']) ? 'checked' : ''; ?>>
													<label class="form-check-label" for="en_delivery"><?php echo t('notify_enable_delivery'); ?></label>
												</div>
												<div class="form-check mb-3">
													<input class="form-check-input" type="checkbox" name="auto_open_whatsapp" id="en_auto_wa" value="1" <?php echo !empty($settings['auto_open_whatsapp']) ? 'checked' : ''; ?>>
													<label class="form-check-label" for="en_auto_wa"><?php echo t('notify_auto_whatsapp'); ?></label>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="dispatch_mode"><?php echo t('notify_dispatch_mode'); ?></label>
													<select name="dispatch_mode" id="dispatch_mode" class="form-control">
														<option value="whatsapp_bridge" <?php echo ($settings['dispatch_mode'] === 'whatsapp_bridge') ? 'selected' : ''; ?>><?php echo t('notify_mode_bridge'); ?></option>
														<option value="whatsapp_link" <?php echo ($settings['dispatch_mode'] === 'whatsapp_link') ? 'selected' : ''; ?>><?php echo t('notify_mode_whatsapp'); ?></option>
														<option value="log" <?php echo ($settings['dispatch_mode'] === 'log') ? 'selected' : ''; ?>><?php echo t('notify_mode_log'); ?></option>
													</select>
													<p class="config-help"><?php echo t('notify_dispatch_help'); ?></p>
													<p class="config-help mb-0"><?php echo t('notify_bridge_use_panel'); ?></p>
												</div>
												<div class="form-group">
													<label for="payment_reminder_days_before"><?php echo t('notify_days_before'); ?></label>
													<input type="number" min="0" max="30" class="form-control" id="payment_reminder_days_before" name="payment_reminder_days_before" value="<?php echo (int) $settings['payment_reminder_days_before']; ?>">
												</div>
											</div>
										</div>

										<p class="config-help mb-2"><?php echo t('notify_tokens_help'); ?></p>
										<div class="form-group">
											<label for="template_order_booked"><?php echo t('notify_tpl_booked'); ?></label>
											<textarea class="form-control notify-template-field" name="template_order_booked" id="template_order_booked"><?php echo esc_html($settings['template_order_booked']); ?></textarea>
										</div>
										<div class="form-group">
											<label for="template_order_ready"><?php echo t('notify_tpl_ready'); ?></label>
											<textarea class="form-control notify-template-field" name="template_order_ready" id="template_order_ready"><?php echo esc_html($settings['template_order_ready']); ?></textarea>
										</div>
										<div class="form-group">
											<label for="template_payment_reminder"><?php echo t('notify_tpl_payment'); ?></label>
											<textarea class="form-control notify-template-field" name="template_payment_reminder" id="template_payment_reminder"><?php echo esc_html($settings['template_payment_reminder']); ?></textarea>
										</div>
										<div class="form-group">
											<label for="template_delivery_confirm"><?php echo t('notify_tpl_delivery'); ?></label>
											<textarea class="form-control notify-template-field" name="template_delivery_confirm" id="template_delivery_confirm"><?php echo esc_html($settings['template_delivery_confirm']); ?></textarea>
										</div>
										<button type="submit" class="btn btn-primary dz-btn--filled"><?php echo t('app_save'); ?></button>
									</form>

									<h5 class="mb-3"><?php echo t('notify_log_heading'); ?></h5>
									<div class="table-responsive">
										<table class="table table-sm table-striped" id="notify-queue-table">
											<thead>
												<tr>
													<th>#</th>
													<th><?php echo t('notify_col_event'); ?></th>
													<th><?php echo t('notify_col_order'); ?></th>
													<th><?php echo t('notify_col_phone'); ?></th>
													<th><?php echo t('notify_col_message'); ?></th>
													<th><?php echo t('notify_col_status'); ?></th>
													<th><?php echo t('notify_col_time'); ?></th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												<?php if (empty($queue)) { ?>
													<tr><td colspan="8" class="text-muted"><?php echo t('notify_log_empty'); ?></td></tr>
												<?php } else { foreach ($queue as $row) {
													$ev = isset($event_labels[$row->event_type]) ? $event_labels[$row->event_type] : $row->event_type;
												?>
													<tr>
														<td><?php echo (int) $row->id; ?></td>
														<td><?php echo esc_html($ev); ?></td>
														<td><?php echo (int) $row->order_id; ?></td>
														<td><?php echo esc_html($row->phone); ?></td>
														<td class="notify-msg-preview"><?php echo esc_html($row->message); ?></td>
														<td><?php echo esc_html($row->status); ?></td>
														<td><?php echo esc_html($row->created_at); ?></td>
														<td>
															<?php if (trim((string) $row->whatsapp_url) !== '') { ?>
																<a class="btn btn-xs btn-outline-success" href="<?php echo site_url('Notifications/open_whatsapp/'.$row->id); ?>" target="_blank" rel="noopener"><?php echo t('notify_open_whatsapp'); ?></a>
															<?php } ?>
														</td>
													</tr>
												<?php } } ?>
											</tbody>
										</table>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
	(function () {
		var panel = document.getElementById('notify-bridge-panel');
		if (!panel) return;

		var labels = {
			connected: <?php echo json_encode(t('notify_bridge_connected')); ?>,
			waiting: <?php echo json_encode(t('notify_bridge_waiting')); ?>,
			off: <?php echo json_encode(t('notify_bridge_off')); ?>,
			restarting: <?php echo json_encode(t('notify_bridge_restarting')); ?>,
			starting: <?php echo json_encode(t('notify_bridge_starting')); ?>,
			resetting: <?php echo json_encode(t('notify_bridge_resetting')); ?>
		};

		var stateLabel = document.getElementById('notify-bridge-state-label');
		var errEl = document.getElementById('notify-bridge-error');
		var qrWrap = document.getElementById('notify-bridge-qr-wrap');
		var qrImg = document.getElementById('notify-bridge-qr-img');
		var pollTimer = null;
		var busy = false;
		var recoveringUntil = 0;
		var failStreak = 0;

		function setState(kind, text) {
			stateLabel.className = kind;
			stateLabel.textContent = text;
		}

		function showError(msg) {
			if (!msg) {
				errEl.style.display = 'none';
				errEl.textContent = '';
				return;
			}
			errEl.style.display = 'block';
			errEl.textContent = msg;
		}

		function showQr(dataUrl) {
			if (dataUrl) {
				qrImg.src = dataUrl;
				qrWrap.classList.add('is-visible');
			} else {
				qrWrap.classList.remove('is-visible');
				qrImg.removeAttribute('src');
			}
		}

		function inRecovery() {
			return Date.now() < recoveringUntil;
		}

		function beginRecovery(ms) {
			recoveringUntil = Date.now() + (ms || 60000);
			// Never poll faster than 5s — PHP built-in is single-threaded.
			startFastPoll(5000);
		}

		function applyPayload(data) {
			var st = (data && data.status) ? data.status : {};
			var ready = !!(st.ready);
			var ok = !!(st.ok);
			var qr = (data && data.qr) ? String(data.qr) : '';
			var hasQr = !!(st.has_qr) || !!qr;
			var bridgeState = (st.state || '').toString();
			var err = (st.error || '').toString();
			failStreak = 0;

			if (ready) {
				setState('ok', labels.connected);
				showQr('');
				showError('');
				recoveringUntil = 0;
				stopFastPoll();
				return;
			}

			// QR wins over any transitional label (restarting/starting/resetting).
			if (hasQr && qr) {
				setState('wait', labels.waiting);
				showQr(qr);
				showError('');
				recoveringUntil = 0;
				startFastPoll(8000);
				return;
			}
			if (hasQr) {
				setState('wait', labels.waiting);
				showError('');
				startFastPoll(5000);
				return;
			}

			if (ok) {
				if (bridgeState === 'restarting') {
					setState('wait', inRecovery() ? labels.resetting : labels.restarting);
				} else if (bridgeState === 'starting' || bridgeState === 'authenticated') {
					setState('wait', labels.starting);
				} else {
					setState('wait', labels.waiting);
				}
				showQr('');
				showError(err && err !== 'null' ? err : '');
				startFastPoll(5000);
				return;
			}

			// Brief unreachable during reset — keep polling, don't freeze the label forever.
			if (inRecovery() || err === 'bridge_unreachable' || err === 'circuit_open') {
				setState('wait', inRecovery() ? labels.resetting : labels.starting);
				showQr('');
				showError('');
				startFastPoll(8000);
				return;
			}

			setState('off', labels.off);
			showQr('');
			showError(err && err !== 'bridge_unreachable' ? err : '');
			stopFastPoll();
		}

		function refresh(force) {
			var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
			var timer = ctrl ? setTimeout(function () { try { ctrl.abort(); } catch (e) {} }, 4000) : null;
			var url = panel.getAttribute('data-status-url');
			url += (url.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();
			if (force) {
				url += '&force=1';
			}
			return fetch(url, {
				credentials: 'same-origin',
				cache: 'no-store',
				signal: ctrl ? ctrl.signal : undefined,
				headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(function (r) { return r.json(); })
				.then(function (data) {
					if (!data || !data.success) {
						if (data && data.message === 'session_expired') {
							showError('Session expired — refresh page.');
						}
						return;
					}
					applyPayload(data);
				})
				.catch(function () {
					failStreak += 1;
					if (failStreak >= 3 && !inRecovery()) {
						setState('off', labels.off);
					}
				})
				.finally(function () {
					if (timer) clearTimeout(timer);
				});
		}

		function startFastPoll(intervalMs) {
			var ms = Math.max(5000, intervalMs || 8000);
			if (pollTimer) {
				clearInterval(pollTimer);
			}
			pollTimer = setInterval(function () { refresh(false); }, ms);
		}

		function stopFastPoll() {
			if (!pollTimer) return;
			clearInterval(pollTimer);
			pollTimer = null;
		}

		function postAction(url, body) {
			if (busy) {
				return Promise.resolve(null);
			}
			busy = true;
			var fd = new FormData();
			if (body) {
				Object.keys(body).forEach(function (k) { fd.append(k, body[k]); });
			}
			var csrfName = document.querySelector('meta[name="csrf-token-name"]');
			var csrfVal = document.querySelector('meta[name="csrf-token-value"]');
			if (csrfName && csrfVal && csrfName.getAttribute('content') && csrfVal.getAttribute('content')) {
				fd.append(csrfName.getAttribute('content'), csrfVal.getAttribute('content'));
			}
			var ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
			var timer = ctrl ? setTimeout(function () { try { ctrl.abort(); } catch (e) {} }, 20000) : null;
			return fetch(url, {
				method: 'POST',
				credentials: 'same-origin',
				cache: 'no-store',
				signal: ctrl ? ctrl.signal : undefined,
				headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
				body: fd
			})
				.then(function (r) { return r.json(); })
				.catch(function () { return null; })
				.finally(function () {
					if (timer) clearTimeout(timer);
					busy = false;
				});
		}

		document.getElementById('notify-bridge-refresh').addEventListener('click', function () {
			refresh(true);
		});

		document.getElementById('notify-bridge-start').addEventListener('click', function () {
			setState('wait', labels.starting);
			beginRecovery(45000);
			postAction(panel.getAttribute('data-start-url')).then(function (data) {
				if (data) applyPayload(data);
				refresh(true);
			});
		});

		document.getElementById('notify-bridge-restart').addEventListener('click', function () {
			if (!window.confirm(<?php echo json_encode(t('notify_bridge_restart_confirm')); ?>)) return;
			setState('wait', labels.restarting);
			showQr('');
			beginRecovery(60000);
			postAction(panel.getAttribute('data-restart-url'), { clear_session: '1' }).then(function (data) {
				if (data) applyPayload(data);
				refresh(true);
			});
		});

		document.getElementById('notify-bridge-reset-qr').addEventListener('click', function () {
			if (!window.confirm(<?php echo json_encode(t('notify_bridge_reset_confirm')); ?>)) return;
			setState('wait', labels.resetting);
			showQr('');
			showError('');
			beginRecovery(75000);
			postAction(panel.getAttribute('data-reset-url')).then(function (data) {
				if (data) applyPayload(data);
				refresh(true);
				setTimeout(function () { refresh(true); }, 5000);
				setTimeout(function () { refresh(true); }, 10000);
			});
		});

		refresh(false);
		setInterval(function () { refresh(false); }, 15000);
	})();
	</script>
</body>
</html>
