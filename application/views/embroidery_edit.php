<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');

$embroidery_row = null;
if (!empty($embroidery)) {
	foreach ($embroidery as $embroidery_row) {
		break;
	}
}
if (!$embroidery_row) {
	$embroidery_row = (object) array('ENO' => 0, 'embroidery_name' => '', 'vol_no' => '', 'design' => '', 'rate' => '', 'remarks' => '', 'is_active' => 1);
}
$embroidery_active = ! isset($embroidery_row->is_active) || (int) $embroidery_row->is_active === 1;
?>
<script>
	jQuery(function () {
		jQuery('#embroideryName, #embroideryRemarks').each(function () {
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
								<h2 class="erp-module-title mb-0"><?php echo t('master_embroidery_form'); ?></h2>
								<div class="erp-card-actions">
									<span class="erp-entry-number"># <?php echo (int) $embroidery_row->ENO; ?></span>
								</div>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Embroidery/process_edit/' . (int) $embroidery_row->ENO); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryName" class="control-label mb-1"><?php echo t('master_volume_name'); ?></label>
													<input id="embroideryName" name="Name" type="text" class="form-control erp-urdu-field" required value="<?php echo esc_html((string) $embroidery_row->embroidery_name); ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryVolumeNo" class="control-label mb-1"><?php echo t('master_volume_no'); ?></label>
													<input id="embroideryVolumeNo" name="vol_no" type="text" class="form-control" required value="<?php echo esc_html((string) $embroidery_row->vol_no); ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryDesignNo" class="control-label mb-1"><?php echo t('master_design_no_label'); ?></label>
													<input id="embroideryDesignNo" name="design" type="text" class="form-control" required value="<?php echo esc_html((string) $embroidery_row->design); ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryRate" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
													<input id="embroideryRate" name="Rate" type="text" class="form-control" required value="<?php echo esc_html((string) $embroidery_row->rate); ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label for="embroideryRemarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="embroideryRemarks" name="Remarks" type="text" class="form-control erp-urdu-field" value="<?php echo esc_html((string) $embroidery_row->remarks); ?>">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label mb-1 d-block"><?php echo t('master_employee_status'); ?></label>
													<label class="mt-2 mb-0">
														<input type="checkbox" name="is_active" value="1" <?php echo $embroidery_active ? 'checked' : ''; ?>>
														<?php echo t('master_employee_status_active'); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
									<div class="row data-entry-actions data-entry-actions--split">
										<div class="col-md-4">
											<a href="<?php echo site_url('Embroidery'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
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
