<?php
$line_index = isset($line_index) ? (int) $line_index : 0;
$qty = isset($qty) ? (int) $qty : 0;
if ($qty < 2) {
	return;
}
$suit_label = t('order_suit_line');
?>
<div class="order-copy-bar" dir="rtl">
	<span class="order-copy-label"><?php echo t('order_same_as'); ?></span>
	<select class="order-copy-from form-control form-control-sm" aria-label="<?php echo esc_attr(t('order_same_as')); ?>">
		<option value=""><?php echo t('order_same_as_placeholder'); ?></option>
		<?php for ($n = 1; $n <= $qty; $n++) {
			if ($n === ($line_index + 1)) {
				continue;
			} ?>
		<option value="<?php echo $n; ?>"><?php echo esc_html($suit_label . ' #' . $n); ?></option>
		<?php } ?>
	</select>
	<button type="button" class="btn btn-sm btn-outline-secondary order-copy-apply"><?php echo t('app_apply'); ?></button>
	<?php if ($line_index < ($qty - 1)) { ?>
	<button type="button" class="btn btn-sm btn-outline-primary order-suit-apply-remaining" title="<?php echo esc_attr(t('order_same_remaining_hint')); ?>">
		<?php echo t('order_same_remaining'); ?>
	</button>
	<?php } ?>
</div>
