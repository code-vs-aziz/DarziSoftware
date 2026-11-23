<?php
$this->load->helper('ui');
$error = isset($error) ? (string) $error : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html(t('device_gate_title')); ?> — DarziBaba</title>
	<link rel="stylesheet" href="<?php echo base_url('css/dz-design-system.css'); ?>">
	<style>
		body { background: #f1f5f9; font-family: system-ui, sans-serif; margin: 0; }
		.dg-wrap { max-width: 480px; margin: 8vh auto; padding: 1.5rem; background: #fff; border-radius: 12px; box-shadow: 0 8px 30px rgba(15,23,42,.08); }
		.dg-wrap h1 { font-size: 1.35rem; margin: 0 0 .5rem; color: #0f172a; }
		.dg-wrap p { color: #64748b; line-height: 1.5; }
		.dg-alert { background: #fef2f2; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
		.dg-label { display: block; font-weight: 600; margin: .75rem 0 .35rem; }
		.dg-input { width: 100%; padding: .65rem .75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
		.dg-btn { margin-top: 1rem; width: 100%; padding: .75rem; border: 0; border-radius: 8px; background: #0284c7; color: #fff; font-weight: 700; cursor: pointer; }
		.dg-logout { display: inline-block; margin-top: 1rem; color: #64748b; }
	</style>
</head>
<body>
	<div class="dg-wrap">
		<h1><?php echo esc_html(t('device_gate_title')); ?></h1>
		<p><?php echo esc_html(t('device_gate_help')); ?></p>
		<?php if ($error !== '') { ?>
			<div class="dg-alert"><?php echo esc_html($error); ?></div>
		<?php } ?>
		<form method="post" action="<?php echo site_url('Device_gate/enroll'); ?>">
			<?php $this->load->view('partials/csrf_field'); ?>
			<label class="dg-label" for="enrollment_code"><?php echo esc_html(t('device_enroll_code')); ?></label>
			<input class="dg-input" id="enrollment_code" name="enrollment_code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="12345678" required>
			<label class="dg-label" for="device_name"><?php echo esc_html(t('device_name_label')); ?></label>
			<input class="dg-input" id="device_name" name="device_name" type="text" placeholder="<?php echo esc_attr(t('device_name_placeholder')); ?>">
			<button class="dg-btn" type="submit"><?php echo esc_html(t('device_enroll_submit')); ?></button>
		</form>
		<a class="dg-logout" href="<?php echo site_url('Login/logout'); ?>" data-no-ajax="true" data-app-logout="true"><?php echo esc_html(t('device_logout')); ?></a>
	</div>
</body>
</html>
