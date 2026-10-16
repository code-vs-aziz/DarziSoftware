<?php
ob_Start();
$this->load->helper('ui');

$this->load->view('Head');
$this->load->view('Header');
?>
<body class="animsition erp-app erp-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('master_button_form'); ?></h2>
							</div>
							<div class="card-body erp-card-body data-entry-body">
								<form name="Form" action="<?php echo site_url('Button/process'); ?>" method="post" data-no-ajax="true">
									<div class="erp-form-section">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="buttonName" class="control-label mb-1"><?php echo t('master_button_name'); ?></label>
													<input id="buttonName" name="Name" type="text" class="form-control erp-urdu-field" required>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="buttonRemarks" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
													<input id="buttonRemarks" name="Remarks" type="text" class="form-control erp-urdu-field">
												</div>
											</div>
										</div>
									</div>
									<div class="row data-entry-actions data-entry-actions--split">
										<div class="col-md-4">
											<a href="<?php echo site_url('Button'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
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
