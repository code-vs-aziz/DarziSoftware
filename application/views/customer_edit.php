<?php
ob_Start();
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
?>
<style>
	.customer-urdu-field {
		direction: rtl;
		text-align: right;
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', serif;
		font-size: 18px;
		line-height: 1.8;
	}
</style>
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
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<?php if ($this->session->flashdata('suss') == 'yes') { ?>
						<div class="alert alert-success" role="alert"><?php echo t('app_record_entered_short'); ?></div>
					<?php } elseif ($this->uri->segment(4) === 'err') { ?>
						<div class="alert alert-danger" role="alert">Naap No already exists for another customer.</div>
					<?php } ?>
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('customer_form'); ?></h2>
								<span class="erp-entry-number">Customer # : <?php echo $edit_code; ?></span>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Customer/process_edit/' . $edit_code); ?>" method="post" data-no-ajax="true">
									<input id="City" name="City" type="hidden" value="<?php echo esc_html((string) $customer_row->city); ?>">
									<input id="Country" name="Country" type="hidden" value="<?php echo esc_html((string) $customer_row->country); ?>">
									<input id="Remarks" name="Remarks" type="hidden" value="<?php echo esc_html((string) $customer_row->remarks); ?>">
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
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="Phone" class="control-label mb-1"><?php echo t('customer_phone'); ?></label>
												<input id="Phone" name="Phone" type="text" class="form-control" value="<?php echo esc_html((string) $customer_row->Phone); ?>">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="CNIC" class="control-label mb-1"><?php echo t('customer_cnic'); ?></label>
												<input id="CNIC" name="CNIC" type="text" class="form-control" value="<?php echo esc_html((string) $customer_row->cnic); ?>">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="emp-type" class="control-label mb-1"><?php echo t('customer_type'); ?></label>
												<select id="emp-type" name="type" class="form-control">
													<option value="Local" <?php echo $customer_type === 'Local' ? 'selected' : ''; ?>>Local</option>
													<option value="Foreign" <?php echo $customer_type === 'Foreign' ? 'selected' : ''; ?>>Foreign</option>
												</select>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="Address" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
												<input id="Address" name="Address" type="text" class="form-control customer-urdu-field" value="<?php echo esc_html((string) $customer_row->Address); ?>">
											</div>
										</div>
									</div>
									<div class="row data-entry-actions">
										<div class="col-md-4">
											<a href="<?php echo site_url('Customer'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
										</div>
										<div class="col-md-4">
											<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
												<span><?php echo t('app_submit'); ?></span>
											</button>
										</div>
										<div class="col-md-4"></div>
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
