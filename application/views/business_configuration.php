<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$config = isset($config) ? $config : array();
$location_name = isset($config['location_name']) ? $config['location_name'] : '';
$phone = isset($config['phone']) ? $config['phone'] : '';
$address_urdu = isset($config['address_urdu']) ? $config['address_urdu'] : '';
$naap_number_prefix = isset($config['naap_number_prefix']) ? $config['naap_number_prefix'] : '';
$daily_booking_limit = isset($config['daily_booking_limit']) && $config['daily_booking_limit'] !== NULL && $config['daily_booking_limit'] !== ''
	? (int) $config['daily_booking_limit']
	: '';
$active_tab = isset($active_tab) ? $active_tab : 'business';
$order_predefined_items = isset($order_predefined_items) ? $order_predefined_items : array();
$booking_date_limits = isset($booking_date_limits) && is_array($booking_date_limits) ? $booking_date_limits : array();
$booking_limits_table_ready = !empty($booking_limits_table_ready);
$predefined_table_ready = !empty($predefined_table_ready);
$config_table_ready = !empty($config_table_ready);
$backup_limits = isset($backup_limits) ? $backup_limits : array();
$backup_max_label = isset($backup_max_label) ? $backup_max_label : '';
$cli_mysqldump = !empty($backup_limits['cli_mysqldump']);
$cli_mysql = !empty($backup_limits['cli_mysql']);
?>
<style>
	.config-urdu-field {
		direction: rtl;
		text-align: right;
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', serif;
		font-size: 20px;
		line-height: 2;
		min-height: 120px;
	}
	.config-help {
		color: #64748b;
		font-size: 13px;
		margin-top: 6px;
	}
	.config-predefined-list {
		direction: rtl;
		text-align: right;
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', serif;
		font-size: 18px;
	}
	.config-predefined-list .list-group-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 1rem;
	}
	.config-predefined-label {
		flex: 1;
	}
	.config-predefined-actions {
		display: flex;
		flex-shrink: 0;
		gap: 0.35rem;
	}
	.config-backup-panel {
		max-width: 720px;
	}
	.config-backup-panel + .config-backup-panel {
		margin-top: 2rem;
		padding-top: 1.5rem;
		border-top: 1px solid #e2e8f0;
	}
	.config-backup-danger {
		border-left: 3px solid #dc2626;
		padding-left: 1rem;
	}
	.config-backup-progress {
		display: none;
		margin-top: 1rem;
		max-width: 520px;
	}
	.config-backup-progress.is-visible {
		display: block;
	}
	.config-backup-progress-track {
		height: 12px;
		border-radius: 999px;
		background: #e2e8f0;
		overflow: hidden;
	}
	.config-backup-progress-bar {
		height: 100%;
		width: 0%;
		border-radius: 999px;
		background: #0284c7;
		transition: width 0.25s ease;
	}
	.config-backup-progress-meta {
		display: flex;
		justify-content: space-between;
		gap: 1rem;
		margin-top: 0.45rem;
		font-size: 13px;
		color: #334155;
	}
	.config-backup-progress-msg {
		margin-top: 0.35rem;
		font-size: 13px;
		color: #64748b;
	}
	.config-backup-progress.is-error .config-backup-progress-bar {
		background: #dc2626;
	}
	.config-backup-progress.is-done .config-backup-progress-bar {
		background: #16a34a;
	}
</style>
<script>
	function confirmConfigurationSave() {
		return confirm('Save business configuration?');
	}

	function confirmPredefinedDelete() {
		return confirm('Delete this order predefined value?');
	}

	function confirmDatabaseImport() {
		var typed = document.getElementById('confirm_restore');
		if (!typed || String(typed.value || '').toUpperCase() !== 'RESTORE') {
			alert('Type RESTORE in the confirmation box before importing.');
			return false;
		}
		return confirm('This will REPLACE all current database data with the selected backup. Continue?');
	}

	function confirmDatabaseClear() {
		var typed = document.getElementById('confirm_clear');
		var otp = document.getElementById('clear_otp');
		if (!typed || String(typed.value || '').toUpperCase() !== 'CLEAR') {
			alert('Type CLEAR in the confirmation box before clearing.');
			return false;
		}
		if (!otp || String(otp.value || '').replace(/\D+/g, '').length !== 6) {
			alert('Enter the 6-digit WhatsApp OTP first.');
			return false;
		}
		return confirm('This will DELETE all live ERP data (customers, orders, cash, etc.). Continue?');
	}

	(function () {
		'use strict';

		function csrfFields() {
			var nameMeta = document.querySelector('meta[name="csrf-token-name"]');
			var valMeta = document.querySelector('meta[name="csrf-token-value"]');
			var name = nameMeta ? nameMeta.getAttribute('content') : '';
			var value = valMeta ? valMeta.getAttribute('content') : '';
			if (window.AppCsrf && typeof window.AppCsrf.token === 'function') {
				value = window.AppCsrf.token() || value;
			}
			return { name: name, value: value };
		}

		function initImportProgress() {
			var form = document.getElementById('config-import-form');
			var btn = document.getElementById('config-import-btn');
			var panel = document.getElementById('config-import-progress');
			if (!form || !btn || !panel || form.getAttribute('data-import-bound') === '1') {
				return;
			}
			form.setAttribute('data-import-bound', '1');

			var bar = document.getElementById('config-import-progress-bar');
			var pctEl = document.getElementById('config-import-progress-pct');
			var msgEl = document.getElementById('config-import-progress-msg');
			var resultEl = document.getElementById('config-import-result');
			var startUrl = form.getAttribute('data-start-url') || '';
			var statusUrl = form.getAttribute('data-status-url') || '';
			var pollTimer = null;

			function setProgress(percent, message, state) {
				var pct = Math.max(0, Math.min(100, parseInt(percent, 10) || 0));
				panel.classList.add('is-visible');
				panel.classList.toggle('is-error', state === 'error');
				panel.classList.toggle('is-done', state === 'done');
				if (bar) {
					bar.style.width = pct + '%';
					bar.setAttribute('aria-valuenow', String(pct));
				}
				if (pctEl) {
					pctEl.textContent = pct + '%';
				}
				if (msgEl) {
					msgEl.textContent = message || '';
				}
			}

			function stopPoll() {
				if (pollTimer) {
					clearInterval(pollTimer);
					pollTimer = null;
				}
			}

			function showResult(message, isError) {
				if (!resultEl) {
					return;
				}
				resultEl.style.display = 'block';
				resultEl.className = isError ? 'alert alert-danger' : 'alert alert-success';
				resultEl.textContent = message || '';
			}

			function pollStatus(jobId) {
				fetch(statusUrl + encodeURIComponent(jobId), {
					credentials: 'same-origin',
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
				}).then(function (res) {
					return res.json();
				}).then(function (data) {
					if (!data || !data.ok) {
						stopPoll();
						setProgress(0, (data && data.error) || <?php echo json_encode(t('config_backup_import_failed')); ?>, 'error');
						showResult((data && data.error) || <?php echo json_encode(t('config_backup_import_failed')); ?>, true);
						btn.disabled = false;
						return;
					}

					var message = data.message || '';
					if (data.status === 'done' && data.table_count) {
						message = message || (<?php echo json_encode(t('config_backup_import_done')); ?> + ' (' + data.table_count + ' tables)');
					}
					setProgress(
						data.percent || 0,
						message,
						data.status === 'error' ? 'error' : (data.status === 'done' ? 'done' : 'running')
					);

					if (data.status === 'done') {
						stopPoll();
						btn.disabled = false;
						showResult(message || <?php echo json_encode(t('config_backup_import_done')); ?>, false);
						form.reset();
						return;
					}
					if (data.status === 'error') {
						stopPoll();
						btn.disabled = false;
						showResult(data.error || data.message || <?php echo json_encode(t('config_backup_import_failed')); ?>, true);
					}
				}).catch(function () {
					stopPoll();
					setProgress(0, <?php echo json_encode(t('config_backup_import_failed')); ?>, 'error');
					showResult(<?php echo json_encode(t('config_backup_import_failed')); ?>, true);
					btn.disabled = false;
				});
			}

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				if (btn.disabled) {
					return;
				}
				if (!confirmDatabaseImport()) {
					return;
				}

				var fileInput = document.getElementById('backup_file');
				if (!fileInput || !fileInput.files || !fileInput.files.length) {
					alert('Please choose a backup file.');
					return;
				}

				btn.disabled = true;
				stopPoll();
				if (resultEl) {
					resultEl.style.display = 'none';
					resultEl.textContent = '';
				}
				setProgress(1, <?php echo json_encode(t('config_backup_import_uploading')); ?>, 'running');

				var body = new FormData(form);
				var csrf = csrfFields();
				if (csrf.name && csrf.value) {
					body.set(csrf.name, csrf.value);
				}

				var stallTimer = null;
				var lastLoaded = 0;
				var sawProgress = false;
				function clearStallTimer() {
					if (stallTimer) {
						clearTimeout(stallTimer);
						stallTimer = null;
					}
				}
				function armStallTimer() {
					clearStallTimer();
					stallTimer = setTimeout(function () {
						if (sawProgress && lastLoaded > 0) {
							return;
						}
						try { xhr.abort(); } catch (err) {}
						setProgress(0, <?php echo json_encode(t('config_backup_import_upload_stalled')); ?>, 'error');
						showResult(<?php echo json_encode(t('config_backup_import_upload_stalled')); ?>, true);
						btn.disabled = false;
					}, 45000);
				}

				var xhr = new XMLHttpRequest();
				xhr.open('POST', startUrl, true);
				xhr.withCredentials = true;
				xhr.setRequestHeader('Accept', 'application/json');
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

				xhr.upload.onprogress = function (e) {
					if (!e.lengthComputable) {
						return;
					}
					sawProgress = true;
					lastLoaded = e.loaded;
					clearStallTimer();
					if (e.loaded < e.total) {
						armStallTimer();
					}
					var pct = Math.max(1, Math.min(20, Math.round((e.loaded / e.total) * 20)));
					setProgress(pct, <?php echo json_encode(t('config_backup_import_uploading')); ?> + ' ' + Math.round((e.loaded / e.total) * 100) + '%', 'running');
				};

				xhr.onload = function () {
					clearStallTimer();
					var data = null;
					try {
						data = JSON.parse(xhr.responseText || '{}');
					} catch (err) {
						data = null;
					}
					if (xhr.status < 200 || xhr.status >= 300 || !data || !data.ok || !data.job_id) {
						setProgress(0, (data && data.error) || <?php echo json_encode(t('config_backup_import_failed')); ?>, 'error');
						showResult((data && data.error) || <?php echo json_encode(t('config_backup_import_failed')); ?>, true);
						btn.disabled = false;
						return;
					}
					setProgress(22, <?php echo json_encode(t('config_backup_import_running')); ?>, 'running');
					pollStatus(data.job_id);
					pollTimer = setInterval(function () {
						pollStatus(data.job_id);
					}, 1200);
				};

				xhr.onerror = function () {
					clearStallTimer();
					setProgress(0, <?php echo json_encode(t('config_backup_import_failed')); ?>, 'error');
					showResult(<?php echo json_encode(t('config_backup_import_failed')); ?>, true);
					btn.disabled = false;
				};

				xhr.onabort = function () {
					clearStallTimer();
				};

				armStallTimer();
				xhr.send(body);
			});
		}

		function initClearOtp() {
			var btn = document.getElementById('config-clear-otp-btn');
			var statusEl = document.getElementById('config-clear-otp-status');
			if (!btn || btn.getAttribute('data-clear-otp-bound') === '1') {
				return;
			}
			btn.setAttribute('data-clear-otp-bound', '1');
			var sendUrl = btn.getAttribute('data-send-url') || '';

			btn.addEventListener('click', function (event) {
				event.preventDefault();
				if (btn.disabled) {
					return;
				}
				btn.disabled = true;
				if (statusEl) {
					statusEl.textContent = <?php echo json_encode(t('config_backup_clear_send_otp')); ?> + '…';
					statusEl.className = 'config-help';
				}

				var csrf = csrfFields();
				var body = new FormData();
				if (csrf.name && csrf.value) {
					body.append(csrf.name, csrf.value);
				}

				fetch(sendUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
					body: body
				}).then(function (res) {
					return res.json().then(function (data) {
						return { status: res.status, data: data };
					});
				}).then(function (result) {
					var data = result.data || {};
					if (!data.ok) {
						if (statusEl) {
							statusEl.textContent = data.error || 'OTP send failed.';
							statusEl.className = 'config-help text-danger';
						}
						btn.disabled = false;
						return;
					}
					if (statusEl) {
						statusEl.textContent = data.message || 'OTP sent.';
						statusEl.className = 'config-help text-success';
					}
					var otpInput = document.getElementById('clear_otp');
					if (otpInput) {
						otpInput.focus();
					}
					setTimeout(function () {
						btn.disabled = false;
					}, 60000);
				}).catch(function () {
					if (statusEl) {
						statusEl.textContent = 'OTP send failed.';
						statusEl.className = 'config-help text-danger';
					}
					btn.disabled = false;
				});
			});
		}

		function initExportProgress() {
			var btn = document.getElementById('config-export-btn');
			var panel = document.getElementById('config-export-progress');
			if (!btn || !panel || btn.getAttribute('data-export-bound') === '1') {
				return;
			}
			btn.setAttribute('data-export-bound', '1');

			var bar = document.getElementById('config-export-progress-bar');
			var pctEl = document.getElementById('config-export-progress-pct');
			var msgEl = document.getElementById('config-export-progress-msg');
			var startUrl = btn.getAttribute('data-start-url') || '';
			var statusUrl = btn.getAttribute('data-status-url') || '';
			var downloadUrl = btn.getAttribute('data-download-url') || '';
			var pollTimer = null;

			function setProgress(percent, message, state) {
				var pct = Math.max(0, Math.min(100, parseInt(percent, 10) || 0));
				panel.classList.add('is-visible');
				panel.classList.toggle('is-error', state === 'error');
				panel.classList.toggle('is-done', state === 'done');
				if (bar) {
					bar.style.width = pct + '%';
					bar.setAttribute('aria-valuenow', String(pct));
				}
				if (pctEl) {
					pctEl.textContent = pct + '%';
				}
				if (msgEl) {
					msgEl.textContent = message || '';
				}
			}

			function stopPoll() {
				if (pollTimer) {
					clearInterval(pollTimer);
					pollTimer = null;
				}
			}

			function triggerDownload(jobId, token) {
				var url = downloadUrl
					.replace('__JOB__', encodeURIComponent(jobId))
					.replace('__TOKEN__', encodeURIComponent(token));
				setProgress(100, <?php echo json_encode(t('config_backup_export_downloading')); ?>, 'done');
				btn.disabled = false;
				window.location.href = url;
			}

			function pollStatus(jobId) {
				fetch(statusUrl + encodeURIComponent(jobId), {
					credentials: 'same-origin',
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
				}).then(function (res) {
					return res.json();
				}).then(function (data) {
					if (!data || !data.ok) {
						stopPoll();
						setProgress(0, (data && data.error) || <?php echo json_encode(t('config_backup_export_failed')); ?>, 'error');
						btn.disabled = false;
						return;
					}

					var message = data.message || '';
					if (data.bytes_label && data.status === 'ready') {
						message = message + ' (' + data.bytes_label + ')';
					}
					setProgress(data.percent || 0, message, data.status === 'error' ? 'error' : (data.status === 'ready' ? 'done' : 'running'));

					if (data.status === 'ready' && data.token) {
						stopPoll();
						triggerDownload(jobId, data.token);
						return;
					}
					if (data.status === 'error') {
						stopPoll();
						btn.disabled = false;
					}
				}).catch(function () {
					stopPoll();
					setProgress(0, <?php echo json_encode(t('config_backup_export_failed')); ?>, 'error');
					btn.disabled = false;
				});
			}

			btn.addEventListener('click', function (event) {
				event.preventDefault();
				if (btn.disabled) {
					return;
				}

				btn.disabled = true;
				stopPoll();
				setProgress(1, <?php echo json_encode(t('config_backup_export_starting')); ?>, 'running');

				var csrf = csrfFields();
				var body = new FormData();
				if (csrf.name && csrf.value) {
					body.append(csrf.name, csrf.value);
				}

				fetch(startUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
					body: body
				}).then(function (res) {
					return res.json();
				}).then(function (data) {
					if (!data || !data.ok || !data.job_id) {
						setProgress(0, (data && data.error) || <?php echo json_encode(t('config_backup_export_failed')); ?>, 'error');
						btn.disabled = false;
						return;
					}
					setProgress(3, <?php echo json_encode(t('config_backup_export_running')); ?>, 'running');
					pollStatus(data.job_id);
					pollTimer = setInterval(function () {
						pollStatus(data.job_id);
					}, 1000);
				}).catch(function () {
					setProgress(0, <?php echo json_encode(t('config_backup_export_failed')); ?>, 'error');
					btn.disabled = false;
				});
			});
		}

		function bootBackupUi() {
			initExportProgress();
			initImportProgress();
			initClearOtp();
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', bootBackupUi);
		} else {
			bootBackupUi();
		}
		document.addEventListener('app:ajax-loaded', bootBackupUi);
	})();
</script>
<body class="animsition erp-app erp-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card measurement-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('config_short'); ?></h2>
							</div>
							<div class="card-body erp-card-body data-entry-body measurement-tab-body">
								<nav>
									<div class="nav nav-tabs" id="config-nav-tab" role="tablist">
										<a class="nav-item nav-link<?php echo $active_tab === 'business' ? ' active' : ''; ?>" data-no-ajax="true" href="<?php echo site_url('Configuration'); ?>"><?php echo t('config_tab_business'); ?></a>
										<a class="nav-item nav-link<?php echo $active_tab === 'devices' ? ' active' : ''; ?>" data-no-ajax="true" href="<?php echo site_url('Configuration?tab=devices'); ?>" style="font-weight:700;"><?php
											$__dev_tab = t('config_tab_devices');
											echo esc_html(($__dev_tab && $__dev_tab !== 'config_tab_devices') ? $__dev_tab : 'Device Security');
										?></a>
										<a class="nav-item nav-link<?php echo $active_tab === 'order_predefined' ? ' active' : ''; ?>" data-no-ajax="true" href="<?php echo site_url('Configuration?tab=order_predefined'); ?>"><?php echo t('config_tab_predefined'); ?></a>
										<a class="nav-item nav-link<?php echo $active_tab === 'database' ? ' active' : ''; ?>" data-no-ajax="true" href="<?php echo site_url('Configuration?tab=database'); ?>"><?php echo t('config_tab_database'); ?></a>
									</div>
								</nav>

								<div class="tab-content pl-3 pt-3" id="config-nav-tabContent">
									<?php if ($active_tab === 'business') { ?>
									<div class="tab-pane fade show active" id="config-business" role="tabpanel">
										<form name="Form" action="<?php echo site_url('Configuration/save'); ?>" method="post" onsubmit="return confirmConfigurationSave();">
											<?php $this->load->view('partials/csrf_field'); ?>
											<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
											<?php if(!$config_table_ready || $this->session->flashdata('error') == 'missing_table') { ?>
												<div class="alert alert-warning">Run <strong>application/sql/business_configuration.sql</strong> before saving configuration.</div>
											<?php } ?>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="location_name" class="control-label mb-1"><?php echo t('config_location_name'); ?></label>
														<input id="location_name" name="location_name" type="text" class="form-control" value="<?php echo esc_html($location_name); ?>" placeholder="Main Location">
														<div class="config-help">Use this when the same app is deployed at different locations.</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="phone" class="control-label mb-1"><?php echo t('config_phone_number'); ?></label>
														<input id="phone" name="phone" type="text" class="form-control" value="<?php echo esc_html($phone); ?>" placeholder="0300-0000000">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<label for="address_urdu" class="control-label mb-1"><?php echo t('config_address'); ?></label>
														<textarea id="address_urdu" name="address_urdu" class="form-control config-urdu-field" lang="ur" dir="rtl" placeholder="اپنا مکمل پتہ اردو میں لکھیں"><?php echo esc_html($address_urdu); ?></textarea>
														<div class="config-help">This field is prepared for Urdu address printing/display when you decide where to map it.</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="naap_number_prefix" class="control-label mb-1"><?php echo t('config_naap_prefix'); ?></label>
														<input id="naap_number_prefix" name="naap_number_prefix" type="text" class="form-control" maxlength="20" value="<?php echo esc_html($naap_number_prefix); ?>" placeholder="A">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="daily_booking_limit" class="control-label mb-1"><?php echo t('config_daily_booking_limit'); ?></label>
														<input id="daily_booking_limit" name="daily_booking_limit" type="number" min="0" step="1" class="form-control" value="<?php echo esc_html($daily_booking_limit === '' ? '' : (string) $daily_booking_limit); ?>" placeholder="e.g. 40">
														<div class="config-help"><?php echo t('config_daily_booking_limit_help'); ?></div>
													</div>
												</div>
											</div>
											<div class="row data-entry-actions">
												<div class="col-md-4"></div>
												<div class="col-md-4">
													<button id="submit-button" type="submit" class="btn btn-lg btn-primary btn-block"><?php echo t('config_save_btn'); ?></button>
												</div>
												<div class="col-md-4"></div>
											</div>
										</form>

										<hr class="my-4">
										<h4 class="mb-2"><?php echo t('config_booking_date_limits_title'); ?></h4>
										<p class="config-help mb-3"><?php echo t('config_booking_date_limits_help'); ?></p>
										<?php if ( ! $booking_limits_table_ready) { ?>
											<div class="alert alert-warning"><?php echo t('config_booking_limits_missing_table'); ?></div>
										<?php } else { ?>
											<?php
											$bl_suss = $this->session->flashdata('booking_limit_suss');
											$bl_err = $this->session->flashdata('booking_limit_error');
											?>
											<?php if ($bl_suss) { ?>
												<div class="alert alert-success"><?php echo $bl_suss === 'deleted' ? t('config_booking_limit_deleted') : t('config_booking_limit_saved'); ?></div>
											<?php } ?>
											<?php if ($bl_err) { ?>
												<div class="alert alert-danger"><?php echo t('config_booking_limit_failed'); ?></div>
											<?php } ?>
											<form action="<?php echo site_url('Configuration/booking_limit_save'); ?>" method="post" class="mb-3">
												<?php $this->load->view('partials/csrf_field'); ?>
												<div class="row align-items-end">
													<div class="col-md-3 mb-2">
														<label for="booking_return_date" class="control-label mb-1"><?php echo t('config_booking_return_date'); ?></label>
														<input id="booking_return_date" name="return_date" type="date" class="form-control" required>
													</div>
													<div class="col-md-2 mb-2">
														<label for="booking_limit_qty" class="control-label mb-1"><?php echo t('config_booking_limit_qty'); ?></label>
														<input id="booking_limit_qty" name="limit_qty" type="number" min="0" step="1" class="form-control" placeholder="e.g. 80" required>
													</div>
													<div class="col-md-4 mb-2">
														<label for="booking_limit_note" class="control-label mb-1"><?php echo t('config_booking_limit_note'); ?></label>
														<input id="booking_limit_note" name="note" type="text" class="form-control" maxlength="255" placeholder="<?php echo esc_html(t('config_booking_limit_note_ph')); ?>">
													</div>
													<div class="col-md-3 mb-2">
														<button type="submit" class="btn btn-primary btn-block"><?php echo t('config_booking_limit_save_btn'); ?></button>
													</div>
												</div>
											</form>
											<div class="table-responsive">
												<table class="table table-sm table-bordered">
													<thead>
														<tr>
															<th><?php echo t('config_booking_return_date'); ?></th>
															<th><?php echo t('config_booking_limit_qty'); ?></th>
															<th><?php echo t('config_booking_limit_note'); ?></th>
															<th></th>
														</tr>
													</thead>
													<tbody>
													<?php if (empty($booking_date_limits)) { ?>
														<tr><td colspan="4" class="text-muted"><?php echo t('config_booking_limits_empty'); ?></td></tr>
													<?php } else { foreach ($booking_date_limits as $bl_row) { ?>
														<tr>
															<td><?php echo esc_html($bl_row['return_date']); ?></td>
															<td><?php echo (int) $bl_row['limit_qty']; ?></td>
															<td><?php echo esc_html(isset($bl_row['note']) ? (string) $bl_row['note'] : ''); ?></td>
															<td>
																<a class="btn btn-sm btn-danger" data-no-ajax="true" href="<?php echo site_url('Configuration/booking_limit_delete/'.(int) $bl_row['id']); ?>" onclick="return confirm(<?php echo json_encode(t('config_booking_limit_delete_confirm')); ?>);"><?php echo t('config_predefined_delete'); ?></a>
															</td>
														</tr>
													<?php } } ?>
													</tbody>
												</table>
											</div>
										<?php } ?>
									</div>
									<?php } elseif ($active_tab === 'devices') { ?>
									<?php $this->load->view('partials/config_devices_tab', array(
										'device_tables_ready' => isset($device_tables_ready) ? $device_tables_ready : FALSE,
										'device_lock_enabled' => isset($device_lock_enabled) ? $device_lock_enabled : FALSE,
										'authorized_devices' => isset($authorized_devices) ? $authorized_devices : array(),
										'this_device' => isset($this_device) ? $this_device : NULL,
										'device_client_ip' => isset($device_client_ip) ? $device_client_ip : '',
										'device_enrollment_code' => isset($device_enrollment_code) ? $device_enrollment_code : '',
										'device_enrollment_expires' => isset($device_enrollment_expires) ? $device_enrollment_expires : '',
									)); ?>
									<?php } elseif ($active_tab === 'database') { ?>
									<div class="tab-pane fade show active" id="config-database" role="tabpanel">
										<?php
										$backup_suss = $this->session->flashdata('backup_suss');
										$backup_safety = $this->session->flashdata('backup_safety');
										$backup_summary = $this->session->flashdata('backup_summary');
										$backup_error = $this->session->flashdata('backup_error');
										?>
										<?php if ($backup_suss == 'imported' || $backup_suss == 'cleared') { ?>
											<div class="alert alert-success config-alert-auto">
												<?php echo $backup_suss == 'cleared' ? t('config_backup_cleared') : t('config_backup_imported'); ?>
												<?php if ($backup_summary) { ?>
													<span class="d-block config-help mt-1"><?php echo esc_html($backup_summary); ?></span>
												<?php } ?>
												<?php if ($backup_safety) { ?>
													<span class="d-block config-help mt-1"><?php echo t('config_backup_safety_kept'); ?>: <code><?php echo esc_html($backup_safety); ?></code></span>
												<?php } ?>
											</div>
										<?php } ?>
										<div id="config-import-result" class="alert alert-success" style="display:none;" role="status"></div>
										<?php if ($backup_error) { ?>
											<div class="alert alert-danger"><?php echo esc_html($backup_error); ?></div>
										<?php } ?>

										<div class="config-backup-panel">
											<h4 class="mb-2"><?php echo t('config_backup_export_title'); ?></h4>
											<p class="config-help mb-3"><?php echo t('config_backup_export_help'); ?></p>
											<button
												type="button"
												id="config-export-btn"
												class="btn btn-primary dz-btn--filled"
												data-start-url="<?php echo esc_attr(site_url('Configuration/export_start')); ?>"
												data-status-url="<?php echo esc_attr(site_url('Configuration/export_status/')); ?>"
												data-download-url="<?php echo esc_attr(site_url('Configuration/export_download/__JOB__')); ?>?token=__TOKEN__"
											><?php echo t('config_backup_export_btn'); ?></button>
											<div id="config-export-progress" class="config-backup-progress" aria-live="polite">
												<div class="config-backup-progress-track">
													<div id="config-export-progress-bar" class="config-backup-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
												</div>
												<div class="config-backup-progress-meta">
													<strong id="config-export-progress-pct">0%</strong>
													<span><?php echo t('config_backup_export_progress_label'); ?></span>
												</div>
												<div id="config-export-progress-msg" class="config-backup-progress-msg"></div>
											</div>
											<p class="config-help mt-2">
												<?php echo $cli_mysqldump ? t('config_backup_method_cli') : t('config_backup_method_php'); ?>
											</p>
										</div>

										<div class="config-backup-panel config-backup-danger">
											<h4 class="mb-2"><?php echo t('config_backup_import_title'); ?></h4>
											<p class="config-help mb-3"><?php echo t('config_backup_import_help'); ?></p>
											<p class="config-help mb-3" style="color:#b45309;"><?php echo t('config_backup_import_cf_hint'); ?></p>
											<form
												id="config-import-form"
												action="<?php echo site_url('Configuration/import_database'); ?>"
												method="post"
												enctype="multipart/form-data"
												data-no-ajax="true"
												data-start-url="<?php echo esc_attr(site_url('Configuration/import_start')); ?>"
												data-status-url="<?php echo esc_attr(site_url('Configuration/import_status/')); ?>"
											>
												<?php $this->load->view('partials/csrf_field'); ?>
												<div class="form-group">
													<label for="backup_file" class="control-label mb-1"><?php echo t('config_backup_file'); ?></label>
													<input id="backup_file" name="backup_file" type="file" class="form-control-file" accept=".sql,.sql.gz,.gz" required>
													<div class="config-help"><?php echo t('config_backup_max_size'); ?>: <?php echo esc_html($backup_max_label); ?></div>
												</div>
												<div class="form-group">
													<label for="confirm_restore" class="control-label mb-1"><?php echo t('config_backup_confirm_label'); ?></label>
													<input id="confirm_restore" name="confirm_restore" type="text" class="form-control" autocomplete="off" placeholder="RESTORE" required>
													<div class="config-help"><?php echo t('config_backup_confirm_help'); ?></div>
												</div>
												<button type="submit" id="config-import-btn" class="btn btn-danger dz-btn--filled"><?php echo t('config_backup_import_btn'); ?></button>
											</form>
											<div id="config-import-progress" class="config-backup-progress" aria-live="polite">
												<div class="config-backup-progress-track">
													<div id="config-import-progress-bar" class="config-backup-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
												</div>
												<div class="config-backup-progress-meta">
													<strong id="config-import-progress-pct">0%</strong>
													<span><?php echo t('config_backup_import_progress_label'); ?></span>
												</div>
												<div id="config-import-progress-msg" class="config-backup-progress-msg"></div>
											</div>
											<p class="config-help mt-3">
												<?php echo $cli_mysql ? t('config_backup_import_cli') : t('config_backup_import_php'); ?>
											</p>
										</div>

										<div class="config-backup-panel config-backup-danger">
											<h4 class="mb-2"><?php echo t('config_backup_clear_title'); ?></h4>
											<p class="config-help mb-3"><?php echo t('config_backup_clear_help'); ?></p>
											<form action="<?php echo site_url('Configuration/clear_database'); ?>" method="post" data-no-ajax="true" onsubmit="return confirmDatabaseClear();">
												<?php $this->load->view('partials/csrf_field'); ?>
												<div class="form-group">
													<label class="control-label mb-1"><?php echo t('config_backup_clear_otp_label'); ?></label>
													<div class="d-flex flex-wrap align-items-center" style="gap:0.5rem;">
														<input id="clear_otp" name="clear_otp" type="text" class="form-control" style="max-width:10rem;" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000000" required>
														<button
															type="button"
															id="config-clear-otp-btn"
															class="btn btn-outline-primary"
															data-send-url="<?php echo esc_attr(site_url('Configuration/clear_send_otp')); ?>"
														><?php echo t('config_backup_clear_send_otp'); ?></button>
													</div>
													<div id="config-clear-otp-status" class="config-help"><?php echo t('config_backup_clear_otp_help'); ?></div>
												</div>
												<div class="form-group">
													<label for="confirm_clear" class="control-label mb-1"><?php echo t('config_backup_clear_confirm_label'); ?></label>
													<input id="confirm_clear" name="confirm_clear" type="text" class="form-control" autocomplete="off" placeholder="CLEAR" required>
													<div class="config-help text-danger"><?php echo t('config_backup_clear_warning'); ?></div>
												</div>
												<button type="submit" class="btn btn-danger dz-btn--filled"><?php echo t('config_backup_clear_btn'); ?></button>
											</form>
										</div>
									</div>
									<?php } else { ?>
									<div class="tab-pane fade show active" id="config-order-predefined" role="tabpanel">
										<?php if($this->session->flashdata('predefined_suss') == 'yes') { ?>
											<div class="alert alert-success config-alert-auto"><?php echo t('config_predefined_added'); ?></div>
										<?php } elseif($this->session->flashdata('predefined_suss') == 'updated') { ?>
											<div class="alert alert-success config-alert-auto"><?php echo t('config_predefined_updated'); ?></div>
										<?php } elseif($this->session->flashdata('predefined_suss') == 'deleted') { ?>
											<div class="alert alert-success config-alert-auto"><?php echo t('config_predefined_deleted'); ?></div>
										<?php } ?>
										<?php if($this->session->flashdata('predefined_error') == 'missing_table') { ?>
											<div class="alert alert-warning">Run <strong>application/sql/order_predefined.sql</strong> before managing order predefined values.</div>
										<?php } elseif($this->session->flashdata('predefined_error') == 'invalid') { ?>
											<div class="alert alert-danger config-alert-auto">Please enter a label for the predefined value.</div>
										<?php } elseif($this->session->flashdata('predefined_suss') == 'reseeded') { ?>
											<div class="alert alert-success config-alert-auto"><?php echo t('config_predefined_reseeded'); ?></div>
										<?php } elseif($this->session->flashdata('predefined_error') == 'failed') { ?>
											<div class="alert alert-danger config-alert-auto"><?php echo t('config_predefined_failed'); ?></div>
										<?php } ?>

										<p class="config-help">These values appear as clickable tags on the order form (نوٹ لائن). Row 1 and Row 2 match the two lines on the order screen.</p>
										<?php if ($predefined_table_ready) { ?>
										<p class="config-help mb-3">
											If Urdu text looks broken in the database, use
											<a href="<?php echo site_url('Configuration/predefined_reseed'); ?>" data-no-ajax="true" onclick="return confirm('Restore all default Urdu predefined values? This replaces every row in the list.');">Restore default Urdu values</a>.
										</p>
										<?php } ?>

										<form action="<?php echo site_url('Configuration/predefined_add'); ?>" method="post" class="mb-4" data-no-ajax="true">
											<?php $this->load->view('partials/csrf_field'); ?>
											<div class="row">
												<div class="col-md-4">
													<div class="form-group">
														<label for="predefined_label_text" class="control-label mb-1"><?php echo t('config_predefined_label'); ?></label>
														<input id="predefined_label_text" name="label_text" type="text" class="form-control config-urdu-field" lang="ur" dir="rtl" required>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label for="predefined_insert_text" class="control-label mb-1"><?php echo t('config_predefined_insert'); ?></label>
														<input id="predefined_insert_text" name="insert_text" type="text" class="form-control config-urdu-field" lang="ur" dir="rtl" placeholder="Defaults to label + ' -'">
													</div>
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label for="predefined_row_group" class="control-label mb-1"><?php echo t('config_predefined_row'); ?></label>
														<select id="predefined_row_group" name="row_group" class="form-control">
															<option value="1">Row 1 (style)</option>
															<option value="2">Row 2 (colour)</option>
														</select>
													</div>
												</div>
												<div class="col-md-2">
													<label class="control-label mb-1 d-block">&nbsp;</label>
													<button type="submit" class="btn btn-primary dz-btn--filled btn-block"<?php echo $predefined_table_ready ? '' : ' disabled'; ?>><?php echo t('config_predefined_add'); ?></button>
												</div>
											</div>
										</form>

										<div class="config-predefined-list">
											<?php if (empty($order_predefined_items)) { ?>
												<p class="text-muted">No predefined values yet.</p>
											<?php } else { ?>
												<ul class="list-group">
													<?php foreach ($order_predefined_items as $item) {
														$id = isset($item['id']) ? (int) $item['id'] : 0;
														$row_label = ((int) (isset($item['row_group']) ? $item['row_group'] : 1) === 2) ? 'Row 2' : 'Row 1';
													?>
													<li class="list-group-item">
														<span class="config-predefined-label">
															<strong><?php echo esc_html(isset($item['label_text']) ? $item['label_text'] : ''); ?></strong>
															<small class="text-muted d-block"><?php echo esc_html($row_label); ?> · inserts: <?php echo esc_html(isset($item['insert_text']) ? $item['insert_text'] : ''); ?></small>
														</span>
														<?php if ($id > 0 && $predefined_table_ready) { ?>
														<span class="config-predefined-actions">
															<button type="button" class="btn btn-sm btn-primary dz-btn--filled dz-btn--sm predefined-edit-btn"
																data-id="<?php echo $id; ?>"
																data-label="<?php echo esc_html(isset($item['label_text']) ? $item['label_text'] : ''); ?>"
																data-insert="<?php echo esc_html(isset($item['insert_text']) ? $item['insert_text'] : ''); ?>"
																data-row="<?php echo (int) (isset($item['row_group']) ? $item['row_group'] : 1); ?>"><?php echo t('config_predefined_edit'); ?></button>
															<a href="<?php echo site_url('Configuration/predefined_delete/'.$id); ?>" class="btn btn-sm btn-danger dz-btn--filled dz-btn--sm" data-no-ajax="true" onclick="return confirmPredefinedDelete();"><?php echo t('config_predefined_delete'); ?></a>
														</span>
														<?php } ?>
													</li>
													<?php } ?>
												</ul>
											<?php } ?>
										</div>

										<div class="modal fade" id="predefinedEditModal" tabindex="-1" role="dialog" aria-hidden="true">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
													<form action="<?php echo site_url('Configuration/predefined_update'); ?>" method="post" data-no-ajax="true">
														<?php $this->load->view('partials/csrf_field'); ?>
														<div class="modal-header">
															<h5 class="modal-title"><?php echo t('config_predefined_edit'); ?></h5>
															<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
														</div>
														<div class="modal-body">
															<input type="hidden" name="id" id="edit_predefined_id" value="">
															<div class="form-group">
																<label for="edit_predefined_label_text"><?php echo t('config_predefined_label'); ?></label>
																<input id="edit_predefined_label_text" name="label_text" type="text" class="form-control config-urdu-field" lang="ur" dir="rtl" required>
															</div>
															<div class="form-group">
																<label for="edit_predefined_insert_text"><?php echo t('config_predefined_insert'); ?></label>
																<input id="edit_predefined_insert_text" name="insert_text" type="text" class="form-control config-urdu-field" lang="ur" dir="rtl">
															</div>
															<div class="form-group mb-0">
																<label for="edit_predefined_row_group"><?php echo t('config_predefined_row'); ?></label>
																<select id="edit_predefined_row_group" name="row_group" class="form-control">
																	<option value="1">Row 1 (style)</option>
																	<option value="2">Row 2 (colour)</option>
																</select>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary dz-btn--outlined" data-dismiss="modal"><?php echo t('app_cancel'); ?></button>
															<button type="submit" class="btn btn-primary dz-btn--filled"><?php echo t('app_save'); ?></button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
