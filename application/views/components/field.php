<?php defined('BASEPATH') OR exit('No direct script access allowed');
$label = isset($label) ? (string) $label : '';
$name = isset($name) ? (string) $name : '';
$id = isset($id) ? (string) $id : $name;
$type = isset($type) ? (string) $type : 'text';
$value = isset($value) ? (string) $value : '';
$placeholder = isset($placeholder) ? (string) $placeholder : '';
$required = !empty($required);
$class = isset($class) ? (string) $class : '';
$attrs = isset($attrs) ? (string) $attrs : '';
?>
<div class="dz-field form-group">
	<?php if ($label !== '') { ?>
	<label class="dz-label" for="<?php echo esc_html($id); ?>"><?php echo esc_html($label); ?></label>
	<?php } ?>
	<input
		type="<?php echo esc_html($type); ?>"
		class="dz-input form-control <?php echo esc_html($class); ?>"
		name="<?php echo esc_html($name); ?>"
		id="<?php echo esc_html($id); ?>"
		value="<?php echo esc_html($value); ?>"
		placeholder="<?php echo esc_html($placeholder); ?>"
		<?php echo $required ? 'required' : ''; ?>
		<?php echo $attrs; ?>>
</div>
