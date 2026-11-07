<?php
ob_Start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$users = isset($users) ? $users : array();
$roles = isset($roles) ? $roles : array();
$current_user = isset($current_user) ? (string) $current_user : (string) $this->session->userdata('user');
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head"><h2 class="erp-module-title mb-0"><?php echo t('rbac_user_management'); ?></h2></div>
							<div class="card-body erp-card-body">
								<?php $this->load->view('rbac_nav'); ?>
								<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
								<?php if ($this->session->flashdata('error')) { ?>
									<div class="alert alert-danger"><?php echo esc_html($this->session->flashdata('error')); ?></div>
								<?php } ?>
								<form action="<?php echo site_url('Rbac/save_user'); ?>" method="post" class="mb-4" data-no-ajax="true">
									<h5 class="mb-3"><?php echo t('rbac_add_user'); ?></h5>
									<div class="row">
										<div class="col-md-3">
											<label><?php echo t('rbac_username'); ?></label>
											<input type="text" name="username" class="form-control" autocomplete="off" required maxlength="50" pattern="[A-Za-z0-9._-]{2,50}" title="<?php echo esc_html(t('rbac_username_hint')); ?>">
										</div>
										<div class="col-md-3">
											<label><?php echo t('auth_password'); ?></label>
											<input type="password" name="password" class="form-control" autocomplete="new-password" required>
										</div>
										<div class="col-md-4">
											<label><?php echo t('rbac_roles_col'); ?></label>
											<select name="roles[]" class="form-control" multiple size="3">
												<?php foreach ($roles as $role) { ?>
												<option value="<?php echo (int) $role->id; ?>"><?php echo esc_html($role->role_name); ?></option>
												<?php } ?>
											</select>
											<small class="text-muted"><?php echo t('rbac_roles_hint'); ?></small>
										</div>
										<div class="col-md-2 d-flex align-items-end">
											<button type="submit" class="btn btn-primary dz-btn--filled btn-block"><?php echo t('rbac_add_user_btn'); ?></button>
										</div>
									</div>
								</form>
								<div class="table-responsive">
									<table class="table table-striped table-hover" id="rbac-users-table">
										<thead>
											<tr>
												<th><?php echo t('rbac_username'); ?></th>
												<th><?php echo t('rbac_roles_col'); ?></th>
												<th><?php echo t('app_actions'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($users as $user) {
												$can_delete = TRUE;
												if ($user->UNAME === $current_user || strtolower($user->UNAME) === 'alibaba') {
													$can_delete = FALSE;
												}
												$role_text = isset($user->role_labels) ? $user->role_labels : '';
											?>
											<tr>
												<td><?php echo esc_html($user->UNAME); ?></td>
												<td><?php echo $role_text !== '' ? esc_html($role_text) : '—'; ?></td>
												<td class="text-nowrap">
													<a class="btn btn-sm btn-primary dz-btn--filled dz-btn--sm" href="<?php echo site_url('Rbac/user_roles/'.rawurlencode($user->UNAME)); ?>"><?php echo t('rbac_assign_roles'); ?></a>
													<button type="button" class="btn btn-sm btn-warning rbac-reset-pwd-btn" data-username="<?php echo esc_html($user->UNAME); ?>"><?php echo t('rbac_reset_password'); ?></button>
													<?php if ($can_delete) { ?>
													<form action="<?php echo site_url('Rbac/delete_user'); ?>" method="post" class="d-inline" data-no-ajax="true" onsubmit="return confirm('<?php echo esc_html(t('rbac_delete_confirm')); ?>');">
														<input type="hidden" name="username" value="<?php echo esc_html($user->UNAME); ?>">
														<button type="submit" class="btn btn-sm btn-danger dz-btn--filled dz-btn--sm"><?php echo t('rbac_delete_user'); ?></button>
													</form>
													<?php } ?>
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
	<div class="modal fade" id="rbacResetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="rbacResetPasswordModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form action="<?php echo site_url('Rbac/reset_password'); ?>" method="post" data-no-ajax="true">
					<div class="modal-header">
						<h5 class="modal-title" id="rbacResetPasswordModalLabel"><?php echo t('rbac_reset_password'); ?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="username" id="rbac-reset-username" value="">
						<p class="mb-3"><?php echo t('rbac_reset_password_for'); ?> <strong id="rbac-reset-username-label"></strong></p>
						<div class="form-group">
							<label for="rbac-reset-password"><?php echo t('rbac_new_password'); ?></label>
							<input type="password" class="form-control" id="rbac-reset-password" name="password" autocomplete="new-password" required>
						</div>
						<div class="form-group mb-0">
							<label for="rbac-reset-password-confirm"><?php echo t('rbac_confirm_password'); ?></label>
							<input type="password" class="form-control" id="rbac-reset-password-confirm" name="password_confirm" autocomplete="new-password" required>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary dz-btn--outlined" data-dismiss="modal"><?php echo t('app_cancel'); ?></button>
						<button type="submit" class="btn btn-primary dz-btn--filled"><?php echo t('rbac_reset_password_save'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
	$(function () {
		if ($.fn.DataTable && $('#rbac-users-table').length) {
			$('#rbac-users-table').DataTable({ pagingType: 'full_numbers', pageLength: 25 });
		}
		$(document).on('click', '.rbac-reset-pwd-btn', function () {
			var username = $(this).data('username') || '';
			$('#rbac-reset-username').val(username);
			$('#rbac-reset-username-label').text(username);
			$('#rbac-reset-password, #rbac-reset-password-confirm').val('');
			$('#rbacResetPasswordModal').modal('show');
		});
	});
	</script>
</body>
</html>
