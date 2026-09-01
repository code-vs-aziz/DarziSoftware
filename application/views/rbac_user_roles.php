<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$roles = isset($roles) ? $roles : array();
$user_roles = isset($user_roles) ? $user_roles : array();
$assigned = array();
foreach ($user_roles as $role) { $assigned[] = (int) $role->id; }
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head"><h2 class="erp-module-title mb-0"><?php echo t('rbac_assign_roles'); ?>: <?php echo esc_html($username); ?></h2></div>
							<div class="card-body erp-card-body">
								<?php $this->load->view('rbac_nav'); ?>
								<form action="<?php echo site_url('Rbac/save_user_roles'); ?>" method="post">
									<input type="hidden" name="username" value="<?php echo esc_html($username); ?>">
									<div class="row">
										<?php foreach ($roles as $role) { ?>
										<div class="col-md-4">
											<label class="d-block">
												<input type="checkbox" name="roles[]" value="<?php echo (int) $role->id; ?>" <?php echo in_array((int) $role->id, $assigned, TRUE) ? 'checked' : ''; ?>>
												<?php echo esc_html($role->role_name); ?>
											</label>
										</div>
										<?php } ?>
									</div>
									<div class="mt-3">
										<button type="submit" class="btn btn-primary dz-btn--filled"><?php echo t('rbac_save_roles'); ?></button>
										<a href="<?php echo site_url('Rbac/users'); ?>" class="btn btn-secondary dz-btn--outlined"><?php echo t('app_back'); ?></a>
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
