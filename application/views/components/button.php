<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('ui');
$label = isset($label) ? (string) $label : '';
$href = isset($href) ? (string) $href : '';
$type = isset($type) ? (string) $type : 'button';
$variant = isset($variant) ? (string) $variant : 'filled';
$icon = isset($icon) ? (string) $icon : '';
$size = isset($size) ? (string) $size : '';
$attrs = isset($attrs) ? (string) $attrs : '';
$tag = ($href !== '') ? 'a' : 'button';
$classes = array('dz-btn', 'dz-btn--'.$variant);
if ($size !== '') {
	$classes[] = 'dz-btn--'.$size;
}
$class_str = implode(' ', $classes);
?>
<?php if ($tag === 'a') { ?>
<a href="<?php echo esc_html($href); ?>" class="<?php echo esc_html($class_str); ?>" <?php echo $attrs; ?>>
<?php } else { ?>
<button type="<?php echo esc_html($type); ?>" class="<?php echo esc_html($class_str); ?>" <?php echo $attrs; ?>>
<?php } ?>
	<?php if ($icon !== '') { echo ui_icon($icon); } ?>
	<span><?php echo esc_html($label); ?></span>
<?php if ($tag === 'a') { ?>
</a>
<?php } else { ?>
</button>
<?php } ?>
