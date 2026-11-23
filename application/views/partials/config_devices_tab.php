<?php
$device_tables_ready = ! empty($device_tables_ready);
$device_lock_enabled = ! empty($device_lock_enabled);
$authorized_devices = isset($authorized_devices) && is_array($authorized_devices) ? $authorized_devices : array();
$this_device = isset($this_device) ? $this_device : NULL;
$device_client_ip = isset($device_client_ip) ? (string) $device_client_ip : '';
$device_enrollment_code = isset($device_enrollment_code) ? (string) $device_enrollment_code : '';
$device_enrollment_expires = isset($device_enrollment_expires) ? (string) $device_enrollment_expires : '';
$device_suss = $this->session->flashdata('device_suss');
$device_error = $this->session->flashdata('device_error');
?>
<div class="tab-pane fade show active" id="config-devices" role="tabpanel">
	<?php if ($device_suss) { ?>
		<div class="alert alert-success">
			<?php
			if ($device_suss === 'lock_on') { echo esc_html(t('device_msg_lock_on')); }
			elseif ($device_suss === 'lock_off') { echo esc_html(t('device_msg_lock_off')); }
			elseif ($device_suss === 'registered') { echo esc_html(t('device_msg_registered')); }
			elseif ($device_suss === 'revoked') { echo esc_html(t('device_msg_revoked')); }
			elseif ($device_suss === 'code_created') { echo esc_html(t('device_msg_code_created')); }
			else { echo esc_html(t('app_saved_success')); }
			?>
		</div>
	<?php } ?>
	<?php if ($device_error) { ?>
		<div class="alert alert-danger">
			<?php
			if ($device_error === 'missing_table') { echo esc_html(t('device_msg_missing_table')); }
			elseif ($device_error === 'register_failed') { echo esc_html(t('device_msg_register_failed')); }
			elseif ($device_error === 'not_this_device') { echo esc_html(t('device_msg_not_this_device')); }
			else { echo esc_html(t('device_msg_failed')); }
			?>
		</div>
	<?php } ?>

	<?php if ( ! $device_tables_ready) { ?>
		<div class="alert alert-warning"><?php echo esc_html(t('device_msg_missing_table')); ?> <code>application/sql/device_auth_schema.sql</code></div>
	<?php } else { ?>

	<div class="alert alert-info">
		<?php echo esc_html(t('device_mac_note')); ?>
	</div>

	<div class="row mb-4">
		<div class="col-md-7">
			<h4 class="mb-2"><?php echo esc_html(t('device_lock_title')); ?></h4>
			<p class="config-help mb-3"><?php echo esc_html(t('device_lock_help')); ?></p>
			<p class="mb-2"><strong><?php echo esc_html(t('device_your_ip')); ?>:</strong> <?php echo esc_html($device_client_ip !== '' ? $device_client_ip : '—'); ?></p>
			<p class="mb-3">
				<strong><?php echo esc_html(t('device_this_browser')); ?>:</strong>
				<?php if ($this_device) { ?>
					<span class="badge badge-success"><?php echo esc_html(t('device_status_approved')); ?> — <?php echo esc_html($this_device->device_name); ?></span>
				<?php } else { ?>
					<span class="badge badge-secondary"><?php echo esc_html(t('device_status_not_registered')); ?></span>
				<?php } ?>
			</p>
			<form method="post" action="<?php echo site_url('Configuration/device_lock_toggle'); ?>" onsubmit="return confirm('<?php echo esc_attr(t('device_lock_confirm')); ?>');">
				<?php $this->load->view('partials/csrf_field'); ?>
				<?php if ( ! $this_device && ! $device_lock_enabled) { ?>
					<div class="form-group">
						<label for="lock_device_name"><?php echo esc_html(t('device_name_label')); ?></label>
						<input id="lock_device_name" name="device_name" type="text" class="form-control" placeholder="<?php echo esc_attr(t('device_name_placeholder')); ?>">
						<div class="config-help"><?php echo esc_html(t('device_lock_auto_register_help')); ?></div>
					</div>
				<?php } ?>
				<input type="hidden" name="lock_enabled" value="<?php echo $device_lock_enabled ? '0' : '1'; ?>">
				<button type="submit" class="btn <?php echo $device_lock_enabled ? 'btn-warning' : 'btn-danger'; ?>">
					<?php echo esc_html($device_lock_enabled ? t('device_lock_disable') : t('device_lock_enable')); ?>
				</button>
			</form>
		</div>
		<div class="col-md-5">
			<div class="border rounded p-3">
				<h5><?php echo esc_html(t('device_register_title')); ?></h5>
				<p class="config-help"><?php echo esc_html(t('device_register_help')); ?></p>
				<form method="post" action="<?php echo site_url('Configuration/device_register'); ?>">
					<?php $this->load->view('partials/csrf_field'); ?>
					<div class="form-group">
						<label for="reg_device_name"><?php echo esc_html(t('device_name_label')); ?></label>
						<input id="reg_device_name" name="device_name" type="text" class="form-control" placeholder="<?php echo esc_attr(t('device_name_placeholder')); ?>" required>
					</div>
					<button type="submit" class="btn btn-primary btn-block"><?php echo esc_html(t('device_register_btn')); ?></button>
				</form>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col-md-6">
			<div class="border rounded p-3">
				<h5><?php echo esc_html(t('device_code_title')); ?></h5>
				<p class="config-help"><?php echo esc_html(t('device_code_help')); ?></p>
				<?php if ($device_enrollment_code !== '') { ?>
					<div class="alert alert-success mb-3">
						<div><strong><?php echo esc_html(t('device_code_show')); ?>:</strong></div>
						<div style="font-size:2rem;letter-spacing:.2em;font-weight:800;"><?php echo esc_html($device_enrollment_code); ?></div>
						<?php if ($device_enrollment_expires !== '') { ?>
							<small><?php echo esc_html(t('device_code_expires')); ?>: <?php echo esc_html($device_enrollment_expires); ?></small>
						<?php } ?>
					</div>
				<?php } ?>
				<form method="post" action="<?php echo site_url('Configuration/device_enrollment_code'); ?>">
					<?php $this->load->view('partials/csrf_field'); ?>
					<div class="form-group">
						<label for="code_label"><?php echo esc_html(t('device_code_label')); ?></label>
						<input id="code_label" name="label" type="text" class="form-control" placeholder="<?php echo esc_attr(t('device_code_label_ph')); ?>">
					</div>
					<button type="submit" class="btn btn-outline-primary"><?php echo esc_html(t('device_code_generate')); ?></button>
				</form>
			</div>
		</div>
	</div>

	<h4 class="mb-3"><?php echo esc_html(t('device_list_title')); ?></h4>
	<div class="table-responsive">
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th><?php echo esc_html(t('device_col_name')); ?></th>
					<th><?php echo esc_html(t('device_col_ip')); ?></th>
					<th><?php echo esc_html(t('device_col_last_seen')); ?></th>
					<th><?php echo esc_html(t('device_col_status')); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($authorized_devices)) { ?>
					<tr><td colspan="5" class="text-center text-muted"><?php echo esc_html(t('device_list_empty')); ?></td></tr>
				<?php } else { foreach ($authorized_devices as $d) {
					$is_this = $this_device && (int) $this_device->id === (int) $d->id;
				?>
					<tr>
						<td>
							<?php echo esc_html($d->device_name); ?>
							<?php if ($is_this) { ?> <span class="badge badge-info"><?php echo esc_html(t('device_this_one')); ?></span><?php } ?>
							<div class="small text-muted" style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html((string) $d->user_agent); ?></div>
						</td>
						<td>
							<div><?php echo esc_html((string) $d->last_ip); ?></div>
							<small class="text-muted"><?php echo esc_html(t('device_registered_ip')); ?>: <?php echo esc_html((string) $d->register_ip); ?></small>
						</td>
						<td><?php echo esc_html($d->last_seen_at ? (string) $d->last_seen_at : '—'); ?></td>
						<td><?php echo (int) $d->is_active === 1 ? esc_html(t('device_status_active')) : esc_html(t('device_status_revoked')); ?></td>
						<td>
							<?php if ((int) $d->is_active === 1) { ?>
								<form method="post" action="<?php echo site_url('Configuration/device_revoke/'.(int) $d->id); ?>" onsubmit="return confirm('<?php echo esc_attr(t('device_revoke_confirm')); ?>');">
									<?php $this->load->view('partials/csrf_field'); ?>
									<button type="submit" class="btn btn-sm btn-outline-danger"><?php echo esc_html(t('device_revoke_btn')); ?></button>
								</form>
							<?php } ?>
						</td>
					</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
</div>
