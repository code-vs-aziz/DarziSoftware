<?php
$emp_type = isset($emp_type) ? (string) $emp_type : '';
$emp_status = isset($emp_status) ? (string) $emp_status : 'Active';
if ($emp_status !== 'Inactive')
{
	$emp_status = 'Active';
}
?>
<div class="col">
	<div class="form-group">
		<label class="control-label mb-1"><?php echo t('master_employee_type'); ?></label>
		<select name="type" class="form-control" required>
			<option value=""><?php echo t('master_select_employee_type'); ?></option>
			<option value="Cutting"<?php echo $emp_type === 'Cutting' ? ' selected' : ''; ?>><?php echo t('master_employee_type_cutting'); ?></option>
			<option value="Stitching"<?php echo $emp_type === 'Stitching' ? ' selected' : ''; ?>><?php echo t('master_employee_type_stitching'); ?></option>
			<option value="Staff"<?php echo $emp_type === 'Staff' ? ' selected' : ''; ?>><?php echo t('master_employee_type_staff'); ?></option>
		</select>
	</div>
</div>
<div class="col">
	<div class="form-group">
		<label class="control-label mb-1"><?php echo t('master_employee_status'); ?></label>
		<select name="status" class="form-control" required>
			<option value="Active"<?php echo $emp_status === 'Active' ? ' selected' : ''; ?>><?php echo t('master_employee_status_active'); ?></option>
			<option value="Inactive"<?php echo $emp_status === 'Inactive' ? ' selected' : ''; ?>><?php echo t('master_employee_status_inactive'); ?></option>
		</select>
	</div>
</div>
