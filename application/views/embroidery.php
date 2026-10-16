<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');
?>
<script>
	function initEmbroideryUrduFields() {
		if (!window.jQuery || !jQuery.fn.setUrduInput) {
			return;
		}
		jQuery('#embroideryName, #embroideryRemarks').each(function () {
			var $field = jQuery(this);
			if (!$field.data('urduBound')) {
				$field.setUrduInput();
				$field.data('urduBound', 1);
			}
		});
	}
	jQuery(function () {
		initEmbroideryUrduFields();
	});
	jQuery(document).on('app:ajax-loaded', initEmbroideryUrduFields);
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
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Embroidery/process'); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="erp-form-section__head">
											<h3 class="erp-form-section__title"><?php echo t('master_embroidery_form'); ?></h3>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryName" class="control-label mb-1"><?php echo t('master_volume_name'); ?></label>
													<input id="embroideryName" name="Name" type="text" class="form-control erp-urdu-field" required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryVolumeNo" class="control-label mb-1"><?php echo t('master_volume_no'); ?></label>
													<input id="embroideryVolumeNo" name="vol_no" type="text" class="form-control" required>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryDesignNo" class="control-label mb-1"><?php echo t('master_design_no_label'); ?></label>
													<input id="embroideryDesignNo" name="design" type="text" class="form-control" required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="embroideryRate" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
													<input id="embroideryRate" name="Rate" type="text" class="form-control" required>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label for="embroideryRemarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="embroideryRemarks" name="Remarks" type="text" class="form-control erp-urdu-field">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label mb-1 d-block"><?php echo t('master_employee_status'); ?></label>
													<label class="mt-2 mb-0">
														<input type="checkbox" name="is_active" value="1" checked>
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
											<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
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
