<?php
ob_Start();
$this->load->view('Head');
$this->load->view('Header');
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('auth_access_denied'); ?></h2>
							</div>
							<div class="card-body erp-card-body">
								<div class="alert alert-danger mb-0">
									<?php echo t('auth_access_denied_msg'); ?>
								</div>
								<div class="mt-3">
									<a href="<?php echo site_url('Welcome'); ?>" class="btn btn-primary dz-btn--filled"><?php echo t('auth_go_dashboard'); ?></a>
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
