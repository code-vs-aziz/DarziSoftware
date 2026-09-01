<?php
/**
 * One-off view string migration for i18n. Skips archive/copy views.
 */
$dir = dirname(__DIR__) . '/application/views';
$skip = array('Copy', '02092019', '23072019', '0902019', '25072019', '27052019', '04072019', 'orderold', 'Order1.php');

$map = array(
	'<h2 class="erp-module-title mb-0">Customer Form</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'customer_form\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Measurement Form</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'measurement_form\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Cash Payment/Receipt</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'cash_title\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Configuration</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'config_short\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Transfer Order Form</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'transfer_order_form\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Order Detail</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'order_detail\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Delivered Order Status</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'order_delivered_status\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">Ready For Delivered</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'order_ready_deliver\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">RBAC Users</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'rbac_users\'); ?></h2>',
	'<h2 class="erp-module-title mb-0">RBAC Roles</h2>' => '<h2 class="erp-module-title mb-0"><?php echo t(\'rbac_roles\'); ?></h2>',
	'<h2 class="erp-module-title">Customers</h2>' => '<h2 class="erp-module-title"><?php echo t(\'customer_list\'); ?></h2>',
	'<h5 class="text-center">Order Edit Form</h5>' => '<h5 class="text-center"><?php echo t(\'order_edit_form\'); ?></h5>',
	'<h5 class="text-center">Order Status</h5>' => '<h5 class="text-center"><?php echo t(\'order_status\'); ?></h5>',
	'<h5 class="text-center">Order Detail</h5>' => '<h5 class="text-center"><?php echo t(\'order_detail\'); ?></h5>',
	'<h5 class="text-center">Customer Form</h5>' => '<h5 class="text-center"><?php echo t(\'customer_form\'); ?></h5>',
	'<h5 class="text-center">Employee Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_employee_form\'); ?></h5>',
	'<h5 class="text-center">Style Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_style_form\'); ?></h5>',
	'<h5 class="text-center">Embroidery Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_embroidery_form\'); ?></h5>',
	'<h5 class="text-center">Suit Type Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_suit_form\'); ?></h5>',
	'<h5 class="text-center">Button Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_button_form\'); ?></h5>',
	'<h5 class="text-center">Style Assign to Employee Form</h5>' => '<h5 class="text-center"><?php echo t(\'master_design_assign_form\'); ?></h5>',
	'<h5 class="text-center">Transfer Order ' => '<h5 class="text-center"><?php echo t(\'transfer_order_heading\'); ?> ',
	'Employee Successfully Created' => '<?php echo t(\'master_employee_created\'); ?>',
	'Customer Successfully created' => '<?php echo t(\'customer_created\'); ?>',
	'Style Successfully created' => '<?php echo t(\'master_style_created\'); ?>',
	'Embroidery Successfully created' => '<?php echo t(\'master_embroidery_created\'); ?>',
	'Button Successfully created' => '<?php echo t(\'master_button_created\'); ?>',
	'Order Successfully Delivered' => '<?php echo t(\'order_delivered_success\'); ?>',
	'Order Delivered Successfully Created' => '<?php echo t(\'order_issue_success\'); ?>',
	'Saved successfully.' => '<?php echo t(\'app_saved_success\'); ?>',
	'Configuration saved successfully.' => '<?php echo t(\'config_saved\'); ?>',
	'Order predefined value added.' => '<?php echo t(\'config_predefined_added\'); ?>',
	'Order predefined value deleted.' => '<?php echo t(\'config_predefined_deleted\'); ?>',
	'Default Urdu values restored successfully.' => '<?php echo t(\'config_predefined_reseeded\'); ?>',
	'Your record successfully  enterd.' => '<?php echo t(\'app_record_entered_short\'); ?>',
	'<span id="submit-button-amount">Submit</span>' => '<span id="submit-button-amount"><?php echo t(\'app_submit\'); ?></span>',
	'<span id="submit-button-sending" style="display:none;">Saving…</span>' => '<span id="submit-button-sending" style="display:none;"><?php echo t(\'app_saving\'); ?></span>',
	'>Delete</span>' => '><?php echo t(\'app_delete\'); ?></span>',
);

$changed = 0;
foreach (glob($dir . '/*.php') as $file) {
	$base = basename($file);
	foreach ($skip as $s) {
		if (stripos($file, $s) !== false || stripos($base, $s) !== false) {
			continue 2;
		}
	}
	$content = file_get_contents($file);
	$new = $content;
	foreach ($map as $from => $to) {
		$new = str_replace($from, $to, $new);
	}
	if ($new !== $content) {
		file_put_contents($file, $new);
		$changed++;
		echo "Updated: $base\n";
	}
}

echo "Done. Files changed: $changed\n";
