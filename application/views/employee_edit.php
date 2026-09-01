<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');

$employee_row = null;
if (!empty($employee_data)) {
	foreach ($employee_data as $employee_row) {
		break;
	}
}
if (!$employee_row) {
	$employee_row = (object) array(
		'empno' => '',
		'name' => '',
		'type' => '',
		'status' => 'Active',
		'phone' => '',
		'advanceamount' => '',
		'nic' => '',
		'emergencyphone' => '',
		'addres' => '',
		'remarks' => '',
	);
}
$emp_status = isset($employee_row->status) ? $employee_row->status : 'Active';
?>
<style>
	.employee-urdu-field {
		direction: rtl;
		text-align: right;
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', serif;
		font-size: 18px;
		line-height: 1.8;
	}
</style>
<script>
	$(function () {
		$('#Name, #Address, #Remarks').each(function () {
			if ($.fn.setUrduInput) {
				$(this).setUrduInput();
			}
		});
	});
</script>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<?php if ($this->session->flashdata('suss') == 'yes') { ?>
						<div class="alert alert-success" role="alert"><?php echo t('app_record_entered_short'); ?></div>
					<?php } ?>
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('master_employee_form'); ?></h2>
								<span class="erp-entry-number">Employee # : <?php echo (int) $employee_row->empno; ?></span>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Employee/process_edit/' . (int) $employee_row->empno); ?>" method="post" data-no-ajax="true">
									<div class="row">
										<div class="col">
											<div class="form-group">
												<label for="Name" class="control-label mb-1"><?php echo t('master_employee_name'); ?></label>
												<input id="Name" name="Name" type="text" class="form-control employee-urdu-field" required value="<?php echo esc_html($employee_row->name); ?>">
											</div>
										</div>
										<?php
										$this->load->view('partials/employee_type_status_fields', array(
											'emp_type' => isset($employee_row->type) ? $employee_row->type : '',
											'emp_status' => $emp_status,
										));
										?>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="Phone" class="control-label mb-1">Phone</label>
												<input id="Phone" name="Phone" type="text" class="form-control" value="<?php echo esc_html($employee_row->phone); ?>">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="Loan" class="control-label mb-1">Loan</label>
												<input id="Loan" name="Loan" type="text" class="form-control" value="<?php echo esc_html($employee_row->advanceamount); ?>">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="CNIC" class="control-label mb-1"><?php echo t('master_cnic'); ?></label>
												<input id="CNIC" name="CNIC" type="text" class="form-control" value="<?php echo esc_html($employee_row->nic); ?>">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="Contact" class="control-label mb-1"><?php echo t('master_emergency_contact'); ?></label>
												<input id="Contact" name="Contact" type="text" class="form-control" value="<?php echo esc_html($employee_row->emergencyphone); ?>">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="Address" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
												<input id="Address" name="Address" type="text" class="form-control employee-urdu-field" value="<?php echo esc_html($employee_row->addres); ?>">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="Remarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
												<input id="Remarks" name="Remarks" type="text" class="form-control employee-urdu-field" value="<?php echo esc_html($employee_row->remarks); ?>">
											</div>
										</div>
									</div>
									<div class="row data-entry-actions">
										<div class="col-md-4">
											<a href="<?php echo site_url('Employee'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
										</div>
										<div class="col-md-4">
											<button id="btn-employee-save" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
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
