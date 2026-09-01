<?php defined('BASEPATH') OR exit('No direct script access allowed');
$title = isset($title) ? (string) $title : '';
$actions = isset($actions) ? $actions : '';
$class = isset($class) ? (string) $class : '';
?>
<div class="dz-card erp-module-card <?php echo esc_html($class); ?>">
	<?php if ($title !== '' || $actions !== '') { ?>
	<div class="dz-card__header erp-card-head">
		<?php if ($title !== '') { ?>
		<h2 class="dz-card__title erp-module-title mb-0"><?php echo esc_html($title); ?></h2>
		<?php } ?>
		<?php if ($actions !== '') { ?>
		<div class="dz-card__actions erp-card-actions"><?php echo $actions; ?></div>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="dz-card__body erp-card-body">
