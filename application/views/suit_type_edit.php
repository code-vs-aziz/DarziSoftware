<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');

$suit_row = null;
if (!empty($suit)) {
	foreach ($suit as $suit_row) {
		break;
	}
}
if (!$suit_row) {
	$suit_row = (object) array('SNO' => 0, 'name' => '', 'rate' => '', 'remarks' => '', 'is_active' => 1);
}
$suit_active = ! isset($suit_row->is_active) || (int) $suit_row->is_active === 1;
?>
<script>
	jQuery(function () {
		jQuery('#suitName, #Remarks').each(function () {
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
					<?php if ($this->session->flashdata('suss') == 'yes') { ?>
						<div class="alert alert-success erp-form-alert" role="alert"><?php echo t('app_record_entered_short'); ?></div>
					<?php } ?>
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('master_suit_form'); ?></h2>
								<div class="erp-card-actions">
									<span class="erp-entry-number"># <?php echo (int) $suit_row->SNO; ?></span>
								</div>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Suit/process_edit/' . (int) $suit_row->SNO); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="suitName" class="control-label mb-1"><?php echo t('master_name'); ?></label>
													<input id="suitName" name="Name" type="text" class="form-control erp-urdu-field" required value="<?php echo esc_html((string) $suit_row->name); ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Rate" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
													<input id="Rate" name="Rate" type="text" class="form-control" required value="<?php echo esc_html((string) $suit_row->rate); ?>">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label for="Remarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="Remarks" name="Remarks" type="text" class="form-control erp-urdu-field" value="<?php echo esc_html((string) $suit_row->remarks); ?>">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="control-label mb-1 d-block"><?php echo t('master_employee_status'); ?></label>
													<label class="mt-2 mb-0">
														<input type="checkbox" name="is_active" value="1" <?php echo $suit_active ? 'checked' : ''; ?>>
														<?php echo t('master_employee_status_active'); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
									<div class="row data-entry-actions data-entry-actions--split">
										<div class="col-md-4">
											<a href="<?php echo site_url('Suit'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
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
