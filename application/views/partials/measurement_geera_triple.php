<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('measurement_triple');

$field_name = isset($field_name) ? (string) $field_name : 'geera';
$field_id = isset($field_id) ? (string) $field_id : $field_name;
$label = isset($label) ? (string) $label : 'گیرا';
$raw_value = isset($value) ? $value : '';
$parts = measurement_split_triple($raw_value);
$top = $parts[0];
$mid = $parts[1];
$bot = $parts[2];
?>
<div class="form-group measurement-geera-group mb-0">
	<label class="control-label mb-1 urd" for="<?php echo esc_attr($field_id); ?>_neeche"><?php echo esc_html($label); ?></label>
	<?php /* LTR DOM: نیچے → درمیان → اوپر so Tab from تیرا hits نیچے first; visual stays نیچے left / اوپر right. */ ?>
	<div class="measurement-geera-triple" dir="ltr" lang="ur">
		<div class="measurement-geera-triple__item">
			<label class="measurement-geera-triple__label urd" for="<?php echo esc_attr($field_id); ?>_neeche">نیچے</label>
			<input id="<?php echo esc_attr($field_id); ?>_neeche"
				name="<?php echo esc_attr($field_name); ?>_neeche"
				type="text"
				class="form-control txtbox measurement-geera-triple__input"
				value="<?php echo esc_html($bot); ?>"
				autocomplete="off">
		</div>
		<div class="measurement-geera-triple__item">
			<label class="measurement-geera-triple__label urd" for="<?php echo esc_attr($field_id); ?>_darmiyan">درمیان</label>
			<input id="<?php echo esc_attr($field_id); ?>_darmiyan"
				name="<?php echo esc_attr($field_name); ?>_darmiyan"
				type="text"
				class="form-control txtbox measurement-geera-triple__input"
				value="<?php echo esc_html($mid); ?>"
				autocomplete="off">
		</div>
		<div class="measurement-geera-triple__item">
			<label class="measurement-geera-triple__label urd" for="<?php echo esc_attr($field_id); ?>_upar">اوپر</label>
			<input id="<?php echo esc_attr($field_id); ?>_upar"
				name="<?php echo esc_attr($field_name); ?>_upar"
				type="text"
				class="form-control txtbox measurement-geera-triple__input"
				value="<?php echo esc_html($top); ?>"
				autocomplete="off">
		</div>
	</div>
</div>
