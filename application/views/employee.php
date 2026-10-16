<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');
?>
<script>
	$(function () {
		$('#Name, #Address, #Remarks').each(function () {
			if ($.fn.setUrduInput) {
				$(this).setUrduInput();
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
						<div class="alert alert-danger erp-form-alert" role="alert"><?php echo t('app_error'); ?></div>
					<?php } ?>
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('master_employee_form'); ?></h2>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Employee/process'); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="erp-form-section__head">
											<h3 class="erp-form-section__title"><?php echo t('master_employee_form'); ?></h3>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Name" class="control-label mb-1"><?php echo t('master_employee_name'); ?></label>
													<input id="Name" name="Name" type="text" class="form-control erp-urdu-field" required>
												</div>
											</div>
											<?php
											$this->load->view('partials/employee_type_status_fields', array(
												'emp_type' => '',
												'emp_status' => 'Active',
											));
											?>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Phone" class="control-label mb-1"><?php echo t('customer_phone'); ?></label>
													<input id="Phone" name="Phone" type="text" class="form-control">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Loan" class="control-label mb-1">Loan</label>
													<input id="Loan" name="Loan" type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="CNIC" class="control-label mb-1"><?php echo t('master_cnic'); ?></label>
													<input id="CNIC" name="CNIC" type="text" class="form-control">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Contact" class="control-label mb-1"><?php echo t('master_emergency_contact'); ?></label>
													<input id="Contact" name="Contact" type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="Address" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
													<input id="Address" name="Address" type="text" class="form-control erp-urdu-field">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="Remarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="Remarks" name="Remarks" type="text" class="form-control erp-urdu-field">
												</div>
											</div>
										</div>
									</div>
									<div class="row data-entry-actions data-entry-actions--split">
										<div class="col-md-4">
											<a href="<?php echo site_url('Employee'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
										</div>
										<div class="col-md-4">
											<button id="btn-employee-save" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
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
