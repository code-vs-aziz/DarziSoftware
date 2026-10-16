<?php defined('BASEPATH') OR exit('No direct script access allowed');
$line_index = isset($line_index) ? (int) $line_index : 0;
$selected = isset($selected) ? (int) $selected : 0;
?>
<div class="order-kolar-picker" data-line="<?php echo $line_index; ?>">
	<label class="control-label mb-2 order-kolar-picker__label"><?php echo t('order_kolar_style'); ?></label>
	<div class="order-kolar-picker__grid">
		<?php for ($k = 1; $k <= 7; $k++) { ?>
		<label class="order-kolar-option">
			<input type="radio"
				name="kolorstyle<?php echo $line_index; ?>"
				value="<?php echo $k; ?>"
				class="order-kolar-option__input"
				<?php echo ($selected === $k) ? 'checked="checked"' : ''; ?>>
			<span class="order-kolar-option__card">
				<img src="<?php echo base_url('images/'.$k.'.jpg'); ?>" alt="<?php echo esc_attr(t('order_kolar_style')); ?> <?php echo $k; ?>">
			</span>
		</label>
		<?php } ?>
	</div>
</div>
