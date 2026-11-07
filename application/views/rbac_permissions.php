<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$role = isset($role) ? $role : NULL;
$permissions = isset($permissions) ? $permissions : array();
$role_permissions = isset($role_permissions) ? $role_permissions : array();
$current_category = '';
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head"><h2 class="erp-module-title mb-0"><?php echo t('rbac_permissions'); ?><?php echo $role ? ': '.esc_html($role->role_name) : ''; ?></h2></div>
							<div class="card-body erp-card-body">
								<?php $this->load->view('rbac_nav'); ?>
								<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
								<?php if (!$role) { ?>
									<div class="alert alert-warning">Role not found.</div>
								<?php } else { ?>
								<form action="<?php echo site_url('Rbac/save_permissions'); ?>" method="post">
									<input type="hidden" name="role_id" value="<?php echo (int) $role->id; ?>">
									<div class="row">
										<?php foreach ($permissions as $permission) { ?>
										<?php if ($current_category !== $permission->category) { $current_category = $permission->category; ?>
										<div class="col-md-12"><h5 class="mt-3"><?php echo esc_html($current_category); ?></h5><hr></div>
										<?php } ?>
										<div class="col-md-4">
											<label class="d-block">
												<input type="checkbox" name="permissions[]" value="<?php echo esc_html($permission->permission_key); ?>" <?php echo in_array($permission->permission_key, $role_permissions, TRUE) ? 'checked' : ''; ?>>
												<?php echo esc_html($permission->permission_label); ?>
											</label>
										</div>
										<?php } ?>
									</div>
									<div class="mt-3"><button type="submit" class="btn btn-primary dz-btn--filled"><?php echo t('rbac_save_permissions'); ?></button></div>
								</form>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
