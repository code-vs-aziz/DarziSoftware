<?php
$this->load->helper('url');
$flash_error = $this->session->flashdata('error');
?>
<!DOCTYPE html>
<html lang="<?php echo esc_html(app_html_lang()); ?>" dir="<?php echo app_is_rtl() ? 'rtl' : 'ltr'; ?>" class="<?php echo esc_html(app_lang_css_class()); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
	<title><?php echo esc_html(t('auth_sign_in_title')); ?></title>
	<link rel="icon" href="<?php echo base_url('images/favicon-32.png'); ?>?v=1" type="image/png" sizes="32x32">
	<link rel="icon" href="<?php echo base_url('images/favicon-16.png'); ?>?v=1" type="image/png" sizes="16x16">
	<link rel="shortcut icon" href="<?php echo base_url('favicon.ico'); ?>?v=1">
	<link rel="apple-touch-icon" href="<?php echo base_url('images/apple-touch-icon.png'); ?>?v=2" sizes="180x180">
	<meta name="theme-color" content="#0ea5e9">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="apple-mobile-web-app-title" content="DarziERP">
	<?php
	$offline_desk_enabled = function_exists('dz_env_bool') ? dz_env_bool('OFFLINE_DESK_ENABLED', FALSE) : FALSE;
	if ($offline_desk_enabled) {
	?>
	<link rel="manifest" href="<?php echo base_url('manifest.webmanifest'); ?>">
	<?php } ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@400;500;600;700&family=Noto+Naskh+Urdu:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link href="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
	<link href="<?php echo base_url(); ?>vendor/font-awesome-5/css/fontawesome-all.min.css?v=5154" rel="stylesheet" media="all">
	<link href="<?php echo base_url(); ?>css/login.css?v=5" rel="stylesheet" media="all">
	<?php if ( ! $offline_desk_enabled) { ?>
	<script src="<?php echo base_url(); ?>js/offline-kill.js?v=1"></script>
	<?php } ?>
	<?php $this->load->view('partials/csrf_bootstrap'); ?>
</head>
<body class="login-advanced">
	<div class="login-advanced__bg" aria-hidden="true"></div>
	<main class="login-advanced__main">
		<div class="login-card">
			<header class="login-card__head">
				<div class="login-card__logo">
					<img src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=5" width="320" height="201" alt="Darzi Baba">
				</div>
				<h1><?php echo t('app_brand'); ?></h1>
				<p class="login-subtitle"><?php echo t('auth_sign_in_subtitle'); ?></p>
			</header>
			<div class="login-card__body">
				<?php if ( ! empty($flash_error)) { ?>
				<div class="login-alert" role="alert">
					<i class="fas fa-exclamation-circle" aria-hidden="true"></i>
					<span><?php echo esc_html((string) $flash_error); ?></span>
				</div>
				<?php } ?>
				<form action="<?php echo site_url('Login/process'); ?>" method="post" autocomplete="on">
					<?php $this->load->view('partials/csrf_field'); ?>
					<div class="login-field">
						<label for="login-email"><?php echo t('auth_username'); ?></label>
						<div class="login-input-group">
							<span class="login-input-group__icon" aria-hidden="true"><i class="fas fa-user"></i></span>
							<input id="login-email" class="au-input au-input--full" type="text" name="email" placeholder="<?php echo esc_html(t('auth_username_placeholder')); ?>" autocomplete="username" required>
						</div>
					</div>
					<div class="login-field">
						<label for="login-password"><?php echo t('auth_password'); ?></label>
						<div class="login-input-group login-input-group--password">
							<span class="login-input-group__icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
							<input id="login-password" class="au-input au-input--full" type="password" name="password" placeholder="<?php echo esc_html(t('auth_password_placeholder')); ?>" autocomplete="current-password" required>
							<button type="button" class="login-input-group__toggle" id="login-password-toggle" aria-label="<?php echo esc_attr(t('auth_show_password')); ?>" aria-pressed="false" data-show-label="<?php echo esc_attr(t('auth_show_password')); ?>" data-hide-label="<?php echo esc_attr(t('auth_hide_password')); ?>">
								<i class="fas fa-eye" aria-hidden="true"></i>
							</button>
						</div>
					</div>
					<div class="login-row">
						<label class="login-remember mb-0">
							<input type="checkbox" name="remember" value="1">
							<span><?php echo t('auth_remember'); ?></span>
						</label>
						<?php $this->load->view('partials/language_switcher'); ?>
					</div>
					<button class="btn-login" name="logBtn" type="submit"><?php echo t('auth_sign_in'); ?></button>
				</form>
			</div>
		</div>
	</main>
	<footer class="login-footer">
		<div class="login-footer__bar">
			<p class="login-footer__credit">
				<?php echo esc_html(t('app_software_powered_by')); ?>
				<a href="https://wa.me/923060407042" target="_blank" rel="noopener noreferrer" class="login-footer__name"><?php echo esc_html(t('app_powered_by_name')); ?></a>
			</p>
		</div>
	</footer>
	<script>window.APP_LANG = <?php echo json_encode(app_lang_js_strings(), JSON_UNESCAPED_UNICODE); ?>;</script>
	<script src="<?php echo base_url(); ?>vendor/jquery-3.2.1.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/popper.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
	<script>
	(function () {
		var input = document.getElementById('login-password');
		var btn = document.getElementById('login-password-toggle');
		if (!input || !btn) return;
		btn.addEventListener('click', function () {
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			btn.setAttribute('aria-pressed', show ? 'true' : 'false');
			btn.setAttribute('aria-label', show ? (btn.getAttribute('data-hide-label') || 'Hide password') : (btn.getAttribute('data-show-label') || 'Show password'));
			var icon = btn.querySelector('i');
			if (icon) {
				icon.classList.toggle('fa-eye', !show);
				icon.classList.toggle('fa-eye-slash', show);
			}
		});
	})();
	</script>
</body>
</html>
