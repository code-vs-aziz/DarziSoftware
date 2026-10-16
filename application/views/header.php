<?php
$this->load->helper('ui');
$erp_has_rbac = isset($this->rbac) && is_object($this->rbac);
$erp_permission_keys = array(
	'customer.manage',
	'order.status',
	'order.detail',
	'order.ready_delivered',
	'cash.receipt',
	'transfer.booking',
	'transfer.cutting',
	'transfer.ho2',
	'transfer.stitching',
	'reports.menu',
	'employee.manage',
	'suit.manage',
	'style.manage',
	'embroidery.manage',
	'button.manage',
	'order.blank_print',
	'configuration.manage',
	'broadcast.manage',
	'website.manage',
	'rbac.admin'
);
$erp_can = array();
foreach ($erp_permission_keys as $erp_permission_key) {
	$erp_can[$erp_permission_key] = $erp_has_rbac ? $this->rbac->can($erp_permission_key) : FALSE;
}
$erp_router_class = strtolower((string) $this->router->class);
$erp_router_method = strtolower((string) $this->router->method);
$erp_uri = strtolower((string) $this->uri->uri_string());
$erp_can['dashboard.view'] = TRUE;

if ( ! function_exists('erp_nav_transfer_stage_active')) {
	function erp_nav_transfer_stage_active($stage, $uri) {
		$uri = strtolower((string) $uri);
		$stage = strtolower((string) $stage);
		if ($stage === 'ho') {
			return strpos($uri, 'transfer_table/ho2') === FALSE
				&& preg_match('#transfer_table/ho(/|$)#', $uri);
		}
		return strpos($uri, 'transfer_table/'.$stage) !== FALSE;
	}
}

if ( ! function_exists('erp_nav_is_active')) {
	function erp_nav_is_active($match, $class, $method, $uri) {
		$match = strtolower((string) $match);
		$class = strtolower((string) $class);
		$method = strtolower((string) $method);
		$uri = strtolower((string) $uri);
		if ($match === 'welcome' || $match === 'welcome/index') {
			return $class === 'welcome' && in_array($method, array('index', 'overview', ''), TRUE);
		}
		if (strpos($match, '/') !== FALSE) {
			return strpos($uri, $match) !== FALSE || ($class.'/'.$method) === $match;
		}
		return $class === $match;
	}
}

if ( ! function_exists('erp_nav_item')) {
	function erp_nav_item($label, $url, $icon, $active) {
		$cls = $active ? 'dz-nav__item is-active' : 'dz-nav__item';
		return '<li class="'.$cls.'">'
			.'<a href="'.esc_html($url).'">'
			.'<span class="dz-nav__icon" aria-hidden="true">'.ui_icon($icon).'</span>'
			.'<span class="dz-nav__label" lang="'.esc_html(app_html_lang()).'">'.esc_html($label).'</span>'
			.'</a></li>';
	}
}

if ( ! function_exists('erp_nav_section')) {
	function erp_nav_section($title) {
		return '<li class="dz-nav__section" aria-hidden="true"><span>'.esc_html($title).'</span></li>';
	}
}

$erp_nav_html = '';

if (!empty($erp_can['dashboard.view'])) {
	$erp_nav_html .= erp_nav_item(t('menu_dashboard'), site_url('Welcome'), 'fa-home', erp_nav_is_active('welcome', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['customer.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_customer'), site_url('Customer'), 'fa-users', erp_nav_is_active('customer', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['order.status'])) {
	$erp_nav_html .= erp_nav_item(t('menu_order_status'), site_url('transfer/order_Status'), 'fa-clipboard-check', erp_nav_is_active('transfer/order_status', $erp_router_class, $erp_router_method, $erp_uri) || ($erp_router_class === 'transfer' && strpos($erp_router_method, 'order_status') !== FALSE));
}
if (!empty($erp_can['order.detail'])) {
	$erp_nav_html .= erp_nav_item(t('menu_order_detail'), site_url('Order/order_view_det'), 'fa-file-alt', erp_nav_is_active('order', $erp_router_class, $erp_router_method, $erp_uri) && !in_array($erp_router_method, array('order_issue', 'print_blank'), TRUE));
}
if (!empty($erp_can['order.ready_delivered'])) {
	$erp_nav_html .= erp_nav_item(t('menu_ready_delivered'), site_url('Order/order_issue'), 'fa-box-open', $erp_router_class === 'order' && $erp_router_method === 'order_issue');
}
if (!empty($erp_can['cash.receipt'])) {
	$erp_nav_html .= erp_nav_item(t('menu_cash'), site_url('Cash'), 'fa-money-bill-wave', erp_nav_is_active('cash', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['transfer.booking'])) {
	$erp_nav_html .= erp_nav_item(t('menu_pending_booking'), site_url('transfer/transfer_table/HO/HO'), 'fa-book', erp_nav_transfer_stage_active('ho', $erp_uri));
}
if (!empty($erp_can['transfer.cutting'])) {
	$erp_nav_html .= erp_nav_item(t('menu_pending_cutting'), site_url('transfer/transfer_table/CUT/CUT'), 'fa-cut', erp_nav_transfer_stage_active('cut', $erp_uri));
}
if (!empty($erp_can['transfer.ho2'])) {
	$erp_nav_html .= erp_nav_item(t('menu_pending_ho2'), site_url('transfer/transfer_table/HO2/HO2'), 'fa-layer-group', erp_nav_transfer_stage_active('ho2', $erp_uri));
}
if (!empty($erp_can['transfer.stitching'])) {
	$erp_nav_html .= erp_nav_item(t('menu_pending_stitching'), site_url('transfer/transfer_table/STIT/STIT'), 'fa-tshirt', erp_nav_transfer_stage_active('stit', $erp_uri));
}
if (!empty($erp_can['reports.menu'])) {
	$erp_nav_html .= erp_nav_item(t('menu_reports'), site_url('Welcome/Report'), 'fa-chart-bar', $erp_router_class === 'welcome' && $erp_router_method === 'report');
}
if (!empty($erp_can['employee.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_employee'), site_url('Employee'), 'fa-user-tie', erp_nav_is_active('employee', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['suit.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_suit_type'), site_url('Suit'), 'fa-male', erp_nav_is_active('suit', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['style.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_style'), site_url('Style'), 'fa-palette', erp_nav_is_active('style', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['embroidery.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_embroidery'), site_url('embroidery'), 'fa-pen-fancy', erp_nav_is_active('embroidery', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['order.blank_print'])) {
	$erp_nav_html .= erp_nav_item(t('menu_blank_print'), site_url('Order/print_blank'), 'fa-print', $erp_router_class === 'order' && $erp_router_method === 'print_blank');
}
if (!empty($erp_can['configuration.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_configuration'), site_url('Configuration'), 'fa-cog', erp_nav_is_active('configuration', $erp_router_class, $erp_router_method, $erp_uri));
	$erp_nav_html .= erp_nav_item(t('menu_customer_messages'), site_url('Notifications'), 'fa-comment-dots', $erp_router_class === 'notifications');
}
if (!empty($erp_can['broadcast.manage']) || !empty($erp_can['configuration.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_broadcast'), site_url('Broadcast'), 'fa-bullhorn', $erp_router_class === 'broadcast');
}
if (!empty($erp_can['website.manage'])) {
	$erp_nav_html .= erp_nav_item(t('menu_website'), site_url('Website'), 'fa-globe', erp_nav_is_active('website', $erp_router_class, $erp_router_method, $erp_uri));
}
if (!empty($erp_can['rbac.admin'])) {
	$erp_nav_html .= erp_nav_item(t('menu_rbac'), site_url('Rbac/users'), 'fa-users-cog', erp_nav_is_active('rbac', $erp_router_class, $erp_router_method, $erp_uri));
}

$erp_bc_labels = array(
	'welcome' => t('app_dashboard'),
	'customer' => t('menu_bc_customers'),
	'order' => t('menu_bc_orders'),
	'transfer' => t('menu_bc_transfers'),
	'cash' => t('menu_bc_cash'),
	'employee' => t('menu_bc_employees'),
	'suit' => t('menu_suit_type'),
	'style' => t('menu_style'),
	'embroidery' => t('menu_embroidery'),
	'button' => t('menu_button'),
	'measurement' => t('menu_bc_measurements'),
	'configuration' => t('menu_configuration'),
	'notifications' => t('menu_customer_messages'),
	'broadcast' => t('menu_broadcast'),
	'website' => t('menu_website'),
	'rbac' => t('menu_rbac'),
);
$erp_is_dashboard = ($erp_router_class === 'welcome' && in_array($erp_router_method, array('index', 'overview', ''), TRUE));
if ($erp_router_class === 'welcome' && $erp_router_method === 'report') {
	$erp_page_title = t('menu_reports');
} elseif ($erp_router_class === 'transfer' && ($erp_router_method === 'transfer_order' || $erp_router_method === 'transfer_table')) {
	$erp_transfer_dept = '';
	if ($erp_router_method === 'transfer_order') {
		$erp_transfer_dept = strtoupper(trim((string) $this->uri->segment(4)));
	} else {
		$erp_transfer_dept = strtoupper(trim((string) $this->uri->segment(3)));
		if ($erp_transfer_dept === '') {
			$erp_transfer_dept = strtoupper(trim((string) $this->uri->segment(4)));
		}
	}
	if (in_array($erp_transfer_dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE)) {
		$erp_page_title = ($erp_router_method === 'transfer_order')
			? transfer_flow_title($erp_transfer_dept)
			: transfer_pending_title($erp_transfer_dept);
	} else {
		$erp_page_title = t('menu_bc_transfers');
	}
} elseif ($erp_is_dashboard) {
	$erp_page_title = t('dash_title');
} else {
	$erp_page_title = isset($erp_bc_labels[$erp_router_class])
		? $erp_bc_labels[$erp_router_class]
		: ucwords(str_replace('_', ' ', $erp_router_class));
}
$erp_user = $this->session->userdata('user');
?>

<?php
$erp_bottom_items = array();
if (!empty($erp_can['dashboard.view'])) {
	$erp_bottom_items[] = array(
		'label' => t('menu_nav_home'),
		'url' => site_url('Welcome'),
		'icon' => 'fa-home',
		'active' => erp_nav_is_active('welcome', $erp_router_class, $erp_router_method, $erp_uri),
	);
}
if (!empty($erp_can['customer.manage'])) {
	$erp_bottom_items[] = array(
		'label' => t('menu_nav_customers'),
		'url' => site_url('Customer'),
		'icon' => 'fa-users',
		'active' => erp_nav_is_active('customer', $erp_router_class, $erp_router_method, $erp_uri),
	);
}
if (!empty($erp_can['order.detail'])) {
	$erp_bottom_items[] = array(
		'label' => t('menu_nav_orders'),
		'url' => site_url('Order/order_view_det'),
		'icon' => 'fa-file-alt',
		'active' => erp_nav_is_active('order', $erp_router_class, $erp_router_method, $erp_uri) && !in_array($erp_router_method, array('order_issue', 'print_blank'), TRUE),
	);
}
if (!empty($erp_can['cash.receipt'])) {
	$erp_bottom_items[] = array(
		'label' => t('menu_nav_cash'),
		'url' => site_url('Cash'),
		'icon' => 'fa-money-bill-wave',
		'active' => erp_nav_is_active('cash', $erp_router_class, $erp_router_method, $erp_uri),
	);
}
$erp_bottom_items = array_slice($erp_bottom_items, 0, 4);
?>
<header class="header-mobile d-block d-lg-none dz-topbar-mobile">
	<div class="header-mobile__bar dz-topbar-mobile__bar">
		<div class="container-fluid">
			<div class="header-mobile-inner dz-topbar-mobile__inner">
				<button class="hamburger hamburger--slider dz-topbar-mobile__menu" type="button" aria-label="<?php echo esc_html(t('app_menu')); ?>" aria-controls="dz-mobile-drawer" aria-expanded="false" data-dz-nav-toggle="true">
					<span class="hamburger-box"><span class="hamburger-inner"></span></span>
				</button>
				<h1 class="dz-topbar-mobile__title"><?php echo esc_html($erp_page_title); ?></h1>
				<div class="dz-topbar-mobile__actions">
					<?php $this->load->view('partials/offline_status_chip', array('offline_indicator_variant' => 'mobile')); ?>
					<?php $this->load->view('partials/bridge_status_chip', array('bridge_indicator_variant' => 'mobile')); ?>
					<?php $this->load->view('partials/language_switcher', array('switcher_wrap_class' => 'dz-topbar-mobile__lang')); ?>
					<a href="<?php echo site_url('Login/logout'); ?>" class="dz-topbar-mobile__logout" title="<?php echo esc_html(t('app_sign_out')); ?>" data-no-ajax="true" data-app-logout="true" onclick="window.location.href=this.href; return false;"><?php echo ui_icon('sign-out-alt'); ?></a>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="dz-nav-backdrop d-lg-none" data-dz-nav-close="true" aria-hidden="true"></div>
<nav class="navbar-mobile dz-drawer d-lg-none" id="dz-mobile-drawer" aria-label="Main">
	<div class="dz-drawer__brand">
		<a href="<?php echo site_url('Welcome'); ?>">
			<img src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=2" alt="Darzi Baba" width="140" height="88">
		</a>
		<button type="button" class="dz-drawer__close" data-dz-nav-close="true" aria-label="<?php echo esc_html(t('app_close')); ?>"><?php echo ui_icon('times'); ?></button>
	</div>
	<div class="dz-drawer__scroll container-fluid">
		<div class="dz-drawer__lang">
			<?php $this->load->view('partials/language_switcher', array('switcher_wrap_class' => 'dz-drawer__lang-switch')); ?>
		</div>
		<ul class="list-unstyled dz-nav dz-nav--mobile">
			<?php echo $erp_nav_html; ?>
		</ul>
	</div>
</nav>

<?php if ( ! empty($erp_bottom_items)) { ?>
<nav class="dz-bottom-nav d-lg-none" aria-label="Primary">
	<?php foreach ($erp_bottom_items as $erp_bn) { ?>
	<a href="<?php echo esc_html($erp_bn['url']); ?>" class="dz-bottom-nav__item<?php echo !empty($erp_bn['active']) ? ' is-active' : ''; ?>">
		<span class="dz-bottom-nav__icon" aria-hidden="true"><?php echo ui_icon($erp_bn['icon']); ?></span>
		<span class="dz-bottom-nav__label"><?php echo esc_html($erp_bn['label']); ?></span>
	</a>
	<?php } ?>
	<button type="button" class="dz-bottom-nav__item" data-dz-nav-toggle="true" aria-controls="dz-mobile-drawer" aria-expanded="false">
		<span class="dz-bottom-nav__icon" aria-hidden="true"><?php echo ui_icon('bars'); ?></span>
		<span class="dz-bottom-nav__label"><?php echo esc_html(t('app_menu_more')); ?></span>
	</button>
</nav>
<?php } ?>

<aside class="menu-sidebar d-none d-lg-block dz-sidebar">
	<div class="dz-sidebar__brand">
		<a href="<?php echo site_url('Welcome'); ?>">
			<img src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=2" alt="Darzi Baba" class="app-sidebar-logo" width="220" height="138">
		</a>
	</div>
	<div class="menu-sidebar__content js-scrollbar1 dz-sidebar__scroll">
		<nav class="navbar-sidebar" aria-label="Main">
			<ul class="list-unstyled navbar__list dz-nav">
				<?php echo $erp_nav_html; ?>
			</ul>
		</nav>
	</div>
</aside>

<div class="page-container">
	<header class="header-desktop dz-topbar">
		<div class="dz-topbar__inner">
			<div class="dz-topbar__lead">
				<?php if ( ! $erp_is_dashboard) { ?>
				<nav class="dz-topbar__crumb" aria-label="Breadcrumb">
					<a href="<?php echo site_url('Welcome'); ?>"><?php echo esc_html(t('menu_dashboard')); ?></a>
					<span class="dz-topbar__crumb-sep" aria-hidden="true">/</span>
					<span class="dz-topbar__crumb-current" aria-current="page"><?php echo esc_html($erp_page_title); ?></span>
				</nav>
				<?php } ?>
				<h1 class="dz-topbar__title"><?php echo esc_html($erp_page_title); ?></h1>
			</div>
			<div class="dz-topbar__tools">
				<?php $this->load->view('partials/offline_status_chip', array('offline_indicator_variant' => 'desktop')); ?>
				<?php $this->load->view('partials/bridge_status_chip', array('bridge_indicator_variant' => 'desktop')); ?>
				<?php $this->load->view('partials/language_switcher', array('switcher_wrap_class' => 'dz-topbar__lang')); ?>
				<?php if ($erp_user) { ?>
				<div class="dz-topbar__user" title="<?php echo esc_html((string) $erp_user); ?>">
					<span class="dz-topbar__user-avatar" aria-hidden="true"><?php echo ui_icon('user-circle'); ?></span>
					<span class="dz-topbar__user-name"><?php echo esc_html((string) $erp_user); ?></span>
				</div>
				<?php } ?>
				<a href="<?php echo site_url('Login/logout'); ?>" class="dz-topbar__logout" title="<?php echo esc_html(t('app_sign_out')); ?>" data-no-ajax="true" data-app-logout="true" onclick="window.location.href=this.href; return false;">
					<?php echo ui_icon('sign-out-alt'); ?>
					<span class="dz-topbar__logout-label"><?php echo esc_html(t('app_sign_out')); ?></span>
				</a>
			</div>
		</div>
	</header>
	<footer class="erp-global-footer" role="contentinfo">
		<div class="erp-global-footer__bar">
			<p class="erp-global-footer__credit">
				<?php echo esc_html(t('app_software_powered_by')); ?>
				<a href="https://wa.me/923060407042" target="_blank" rel="noopener noreferrer" class="erp-global-footer__name"><?php echo esc_html(t('app_powered_by_name')); ?></a>
			</p>
		</div>
	</footer>
