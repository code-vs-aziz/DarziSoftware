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
</style>
<script>
	function confirmConfigurationSave() {
		return confirm('Save business configuration?');
	}

	function confirmPredefinedDelete() {
		return confirm('Delete this order predefined value?');
	}
</script>
<body class="animsition erp-app">
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
										<a class="nav-item nav-link<?php echo $active_tab === 'business' ? ' active' : ''; ?>" href="<?php echo site_url('Configuration'); ?>">Business Details</a>
										<a class="nav-item nav-link<?php echo $active_tab === 'order_predefined' ? ' active' : ''; ?>" href="<?php echo site_url('Configuration?tab=order_predefined'); ?>">Order Predefined</a>
									</div>
								</nav>

								<div class="tab-content pl-3 pt-3" id="config-nav-tabContent">
									<?php if ($active_tab === 'business') { ?>
									<div class="tab-pane fade show active" id="config-business" role="tabpanel">
										<form name="Form" action="<?php echo site_url('Configuration/save'); ?>" method="post" onsubmit="return confirmConfigurationSave();">
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
