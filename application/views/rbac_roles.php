<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$roles = isset($roles) ? $roles : array();
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head"><h2 class="erp-module-title mb-0"><?php echo t('rbac_roles'); ?></h2></div>
							<div class="card-body erp-card-body">
								<?php $this->load->view('rbac_nav'); ?>
								<?php if($this->session->flashdata('suss') == 'yes') { ?><div class="alert alert-success"><?php echo t('app_saved_success'); ?></div><?php } ?>
								<form action="<?php echo site_url('Rbac/save_role'); ?>" method="post" class="mb-4">
									<div class="row">
										<div class="col-md-3"><label>Role Name</label><input type="text" name="role_name" class="form-control" required></div>
										<div class="col-md-5"><label>Description</label><input type="text" name="description" class="form-control"></div>
										<div class="col-md-2"><label>Status</label><select name="is_active" class="form-control"><option value="1">Active</option><option value="0">Inactive</option></select></div>
										<div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary dz-btn--filled btn-block">Add Role</button></div>
									</div>
								</form>
								<div class="table-responsive">
									<table class="table table-striped table-hover">
										<thead><tr><th>Role</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
										<tbody>
											<?php foreach ($roles as $role) { ?>
											<tr>
												<td><?php echo esc_html($role->role_name); ?></td>
												<td><?php echo esc_html($role->description); ?></td>
												<td><?php echo ((int) $role->is_active) === 1 ? 'Active' : 'Inactive'; ?></td>
												<td>
													<a class="btn btn-sm btn-primary dz-btn--filled dz-btn--sm" href="<?php echo site_url('Rbac/permissions/'.$role->id); ?>">Permissions</a>
													<form action="<?php echo site_url('Rbac/save_role'); ?>" method="post" class="d-inline">
														<input type="hidden" name="role_id" value="<?php echo (int) $role->id; ?>">
														<input type="hidden" name="role_name" value="<?php echo esc_html($role->role_name); ?>">
														<input type="hidden" name="description" value="<?php echo esc_html($role->description); ?>">
														<input type="hidden" name="is_active" value="<?php echo ((int) $role->is_active) === 1 ? '0' : '1'; ?>">
														<button type="submit" class="btn btn-sm btn-secondary dz-btn--outlined dz-btn--sm"><?php echo ((int) $role->is_active) === 1 ? 'Disable' : 'Enable'; ?></button>
													</form>
												</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
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
