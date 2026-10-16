<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$config = isset($config) ? $config : array();
$location_name = isset($config['location_name']) ? $config['location_name'] : '';
$phone = isset($config['phone']) ? $config['phone'] : '';
$address_urdu = isset($config['address_urdu']) ? $config['address_urdu'] : '';
$active_tab = isset($active_tab) ? $active_tab : 'business';
$order_predefined_items = isset($order_predefined_items) ? $order_predefined_items : array();
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

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initExportProgress);
		} else {
			initExportProgress();
		}
		document.addEventListener('app:ajax-loaded', initExportProgress);
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
										<a class="nav-item nav-link<?php echo $active_tab === 'business' ? ' active' : ''; ?>" href="<?php echo site_url('Configuration'); ?>"><?php echo t('config_tab_business'); ?></a>
										<a class="nav-item nav-link<?php echo $active_tab === 'order_predefined' ? ' active' : ''; ?>" href="<?php echo site_url('Configuration?tab=order_predefined'); ?>"><?php echo t('config_tab_predefined'); ?></a>
										<a class="nav-item nav-link<?php echo $active_tab === 'database' ? ' active' : ''; ?>" href="<?php echo site_url('Configuration?tab=database'); ?>"><?php echo t('config_tab_database'); ?></a>
									</div>
								</nav>

								<div class="tab-content pl-3 pt-3" id="config-nav-tabContent">
									<?php if ($active_tab === 'business') { ?>
									<div class="tab-pane fade show active" id="config-business" role="tabpanel">
										<form name="Form" action="<?php echo site_url('Configuration/save'); ?>" method="post" onsubmit="return confirmConfigurationSave();">
											<?php $this->load->view('partials/csrf_field'); ?>
											<?php if($this->session->flashdata('suss') == 'yes') { ?>
												<div class="alert alert-success config-alert-auto"><?php echo t('config_saved'); ?></div>
											<?php } ?>
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
											<div class="row data-entry-actions">
												<div class="col-md-4"></div>
												<div class="col-md-4">
													<button id="submit-button" type="submit" class="btn btn-lg btn-primary btn-block"><?php echo t('config_save_btn'); ?></button>
												</div>
												<div class="col-md-4"></div>
											</div>
										</form>
									</div>
									<?php } elseif ($active_tab === 'database') { ?>
									<div class="tab-pane fade show active" id="config-database" role="tabpanel">
										<?php
										$backup_suss = $this->session->flashdata('backup_suss');
										$backup_safety = $this->session->flashdata('backup_safety');
										$backup_error = $this->session->flashdata('backup_error');
										?>
										<?php if ($backup_suss == 'imported') { ?>
											<div class="alert alert-success config-alert-auto">
												<?php echo t('config_backup_imported'); ?>
												<?php if ($backup_safety) { ?>
													<span class="d-block config-help mt-1"><?php echo t('config_backup_safety_kept'); ?>: <code><?php echo esc_html($backup_safety); ?></code></span>
												<?php } ?>
											</div>
										<?php } ?>
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
											<form action="<?php echo site_url('Configuration/import_database'); ?>" method="post" enctype="multipart/form-data" data-no-ajax="true" onsubmit="return confirmDatabaseImport();">
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
												<button type="submit" class="btn btn-danger dz-btn--filled"><?php echo t('config_backup_import_btn'); ?></button>
											</form>
											<p class="config-help mt-3">
												<?php echo $cli_mysql ? t('config_backup_import_cli') : t('config_backup_import_php'); ?>
											</p>
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
											<a href="<?php echo site_url('Configuration/predefined_reseed'); ?>" onclick="return confirm('Restore all default Urdu predefined values? This replaces every row in the list.');">Restore default Urdu values</a>.
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
															<a href="<?php echo site_url('Configuration/predefined_delete/'.$id); ?>" class="btn btn-sm btn-danger dz-btn--filled dz-btn--sm" onclick="return confirmPredefinedDelete();"><?php echo t('config_predefined_delete'); ?></a>
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
