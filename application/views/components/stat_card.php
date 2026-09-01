<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('ui');
$label = isset($label) ? (string) $label : '';
$value = isset($value) ? (int) $value : 0;
$icon = isset($icon) ? (string) $icon : 'fa-chart-bar';
$href = isset($href) ? (string) $href : '#';
?>
<a href="<?php echo esc_html($href); ?>" class="dz-stat">
	<div class="dz-stat__icon"><?php echo ui_icon($icon); ?></div>
	<div class="dz-stat__label"><?php echo esc_html($label); ?></div>
	<div class="dz-stat__value"><?php echo number_format($value); ?></div>
</a>
