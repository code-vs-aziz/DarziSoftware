<?php
$line_index = isset($line_index) ? (int) $line_index : 0;
$qty = isset($qty) ? (int) $qty : 0;
if ($qty < 2) {
	return;
}
?>
<div class="order-copy-bar" dir="rtl">
	<span class="order-copy-label"><?php echo t('order_copy_from_line'); ?></span>
	<select class="order-copy-from form-control form-control-sm">
		<?php for ($n = 1; $n <= $qty; $n++) {
			if ($n === ($line_index + 1)) {
				continue;
			} ?>
		<option value="<?php echo $n; ?>"><?php echo $n; ?></option>
		<?php } ?>
	</select>
	<button type="button" class="btn btn-sm btn-outline-secondary order-copy-apply"><?php echo t('app_apply'); ?></button>
</div>
