<?php
ob_start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');

$customer_row = null;
if (!empty($customer_data) && is_array($customer_data)) {
	foreach ($customer_data as $customer_row) {
		break;
	}
}
if (!$customer_row) {
	$customer_row = (object) array(
		'code' => (int) $this->uri->segment(3),
		'name' => '',
		'man_napno' => '',
		'Phone' => '',
		'cnic' => '',
		'cus_type' => 'Local',
		'city' => '',
		'country' => '',
		'Address' => '',
		'remarks' => '',
	);
}
$customer_type = isset($customer_row->cus_type) && $customer_row->cus_type !== '' ? $customer_row->cus_type : 'Local';
$edit_code = (int) $customer_row->code;
$return_to = trim((string) $this->input->get_post('return_to', TRUE));
if ($return_to !== '' && (strpos($return_to, '://') !== FALSE || strpos($return_to, '//') === 0)) {
	$return_to = '';
}
$return_back_url = ($return_to !== '') ? site_url($return_to) : site_url('Customer');
?>
<link href="<?php echo base_url('css/customer-form-enhancements.css?v=5'); ?>" rel="stylesheet" media="all">
<script>
	function initCustomerUrduFields() {
		if (!window.jQuery || !jQuery.fn.setUrduInput) {
			return;
		}
		jQuery('#Name, #Address').each(function () {
			var $field = jQuery(this);
			if (!$field.data('urduBound')) {
				$field.setUrduInput();
				$field.data('urduBound', 1);
			}
		});
	}
	jQuery(function () {
		initCustomerUrduFields();
	});
	jQuery(document).on('app:ajax-loaded', initCustomerUrduFields);
</script>
<body class="animsition erp-app erp-form-page customer-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>

					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('customer_edit'); ?></h2>
								<div class="erp-card-actions">
									<span class="erp-entry-number"><?php echo t('customer_number'); ?> : <?php echo (int) $edit_code; ?></span>
								</div>
							</div>

							<div class="card-body erp-card-body data-entry-body">
								<div class="customer-form-layout">
									<form name="Form" action="<?php echo site_url('Customer/process_edit/' . $edit_code); ?>" method="post" data-no-ajax="true">
										<?php if ($return_to !== '') { ?>
										<input type="hidden" name="return_to" value="<?php echo esc_attr($return_to); ?>">
										<?php } ?>
										<input id="City" name="City" type="hidden" value="<?php echo esc_html((string) $customer_row->city); ?>">
										<input id="Country" name="Country" type="hidden" value="<?php echo esc_html((string) $customer_row->country); ?>">
										<input id="Remarks" name="Remarks" type="hidden" value="<?php echo esc_html((string) $customer_row->remarks); ?>">

										<div class="customer-info-panel">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_form'); ?></h3>
											</div>
											<div class="customer-info-panel__grid">
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_number'); ?></span>
													<strong><?php echo (int) $edit_code; ?></strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_manual_naap'); ?></span>
													<strong><?php echo esc_html((string) $customer_row->man_napno); ?></strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_type'); ?></span>
													<strong><?php echo $customer_type === 'Foreign' ? t('customer_type_foreign') : t('customer_type_local'); ?></strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_phone'); ?></span>
													<strong><?php echo esc_html((string) ($customer_row->Phone !== '' ? $customer_row->Phone : '—')); ?></strong>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_basic'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Name" class="control-label mb-1"><?php echo t('customer_name'); ?></label>
														<input id="Name" name="Name" type="text" class="form-control customer-urdu-field" required value="<?php echo esc_html((string) $customer_row->name); ?>">
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="man_napno" class="control-label mb-1"><?php echo t('customer_manual_naap'); ?></label>
														<input id="man_napno" name="man_napno" type="text" class="form-control" value="<?php echo esc_html((string) $customer_row->man_napno); ?>">
													</div>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_contact'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Phone" class="control-label mb-1"><?php echo t('customer_phone'); ?></label>
														<input id="Phone" name="Phone" type="text" class="form-control" inputmode="tel" value="<?php echo esc_html((string) $customer_row->Phone); ?>">
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="CNIC" class="control-label mb-1"><?php echo t('customer_cnic'); ?></label>
														<input id="CNIC" name="CNIC" type="text" class="form-control" value="<?php echo esc_html((string) $customer_row->cnic); ?>">
													</div>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_details'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="control-label mb-1"><?php echo t('customer_type'); ?></label>
														<div class="customer-type-chips">
															<label class="customer-type-chip">
																<input type="radio" name="type" value="Local" <?php echo $customer_type === 'Local' ? 'checked' : ''; ?>>
																<span class="customer-type-chip__card"><?php echo t('customer_type_local'); ?></span>
															</label>
															<label class="customer-type-chip">
																<input type="radio" name="type" value="Foreign" <?php echo $customer_type === 'Foreign' ? 'checked' : ''; ?>>
																<span class="customer-type-chip__card"><?php echo t('customer_type_foreign'); ?></span>
															</label>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="Address" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
														<input id="Address" name="Address" type="text" class="form-control customer-urdu-field" value="<?php echo esc_html((string) $customer_row->Address); ?>">
													</div>
												</div>
											</div>
										</div>

										<div class="customer-submit-sticky">
											<div class="customer-submit-sticky__actions customer-submit-sticky__actions--split">
												<a href="<?php echo esc_html($return_back_url); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
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
	</div>
</body>
</html>
