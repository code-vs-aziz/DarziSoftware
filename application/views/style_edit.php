<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');

$style_edit = null;
if (!empty($style_data)) {
	foreach ($style_data as $style_edit) {
		break;
	}
}
if (!$style_edit) {
	$style_edit = (object) array('DNO' => 0, 'vol_name' => '', 'vol_no' => '', 'design' => '', 'rate' => '', 'remarks' => '', 'is_active' => 1);
}
$style_active = ! isset($style_edit->is_active) || (int) $style_edit->is_active === 1;
?>
<script>
	jQuery(function () {
		jQuery('#styleName, #styleRemarks').each(function () {
			if (jQuery.fn.setUrduInput && !jQuery(this).data('urduBound')) {
				jQuery(this).setUrduInput();
				jQuery(this).data('urduBound', 1);
			}
		});
	});
</script>
<body class="animsition erp-app erp-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('master_style_form'); ?></h2>
								<div class="erp-card-actions">
									<span class="erp-entry-number"># <?php echo (int) $style_edit->DNO; ?></span>
								</div>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Style/process_edit/' . (int) $style_edit->DNO); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="styleName" class="control-label mb-1"><?php echo t('master_volume_name'); ?></label>
													<input id="styleName" name="Name" type="text" class="form-control erp-urdu-field" required value="<?php echo esc_html((string) $style_edit->vol_name); ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="styleVolumeNo" class="control-label mb-1"><?php echo t('master_volume_no'); ?></label>
													<input id="styleVolumeNo" name="VolumeNo" type="text" class="form-control" required value="<?php echo esc_html((string) $style_edit->vol_no); ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="styleDesignNo" class="control-label mb-1"><?php echo t('master_design_no_label'); ?></label>
													<input id="styleDesignNo" name="DesignNo" type="text" class="form-control" required value="<?php echo esc_html((string) $style_edit->design); ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="styleRate" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
													<input id="styleRate" name="Rate" type="text" class="form-control" required value="<?php echo esc_html((string) $style_edit->rate); ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label for="styleRemarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="styleRemarks" name="Remarks" type="text" class="form-control erp-urdu-field" value="<?php echo esc_html((string) $style_edit->remarks); ?>">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label mb-1 d-block"><?php echo t('master_employee_status'); ?></label>
													<label class="mt-2 mb-0">
														<input type="checkbox" name="is_active" value="1" <?php echo $style_active ? 'checked' : ''; ?>>
														<?php echo t('master_employee_status_active'); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
									<div class="row data-entry-actions data-entry-actions--split">
										<div class="col-md-4">
											<a href="<?php echo site_url('Style'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
										</div>
										<div class="col-md-4">
											<button type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
												<span><?php echo t('app_submit'); ?></span>
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
