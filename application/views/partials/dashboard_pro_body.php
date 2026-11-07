<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('ui');
$dashboard_summary = isset($dashboard_summary) && is_array($dashboard_summary) ? $dashboard_summary : array();
$dash_cards = array(
	array('key' => 'ready_to_deliver', 'label' => t('dash_ready_deliver'), 'icon' => 'fa-box-open', 'link' => site_url('Order/order_issue')),
	array('key' => 'today_bookings', 'label' => t('dash_today_bookings'), 'icon' => 'fa-calendar-plus', 'link' => site_url('Order/order_view_det')),
	array('key' => 'today_deliveries', 'label' => t('dash_today_deliveries'), 'icon' => 'fa-truck', 'link' => site_url('Order/order_issue')),
	array('key' => 'today_return', 'label' => t('dash_today_return'), 'icon' => 'fa-undo', 'link' => site_url('Order/order_view_det')),
	array('key' => 'pending_booking', 'label' => t('menu_pending_booking'), 'icon' => 'fa-clipboard-list', 'link' => site_url('transfer/transfer_table/HO/HO')),
	array('key' => 'pending_cutting', 'label' => t('menu_pending_cutting'), 'icon' => 'fa-cut', 'link' => site_url('transfer/transfer_table/CUT/CUT')),
	array('key' => 'pending_ho2', 'label' => t('menu_pending_ho2'), 'icon' => 'fa-layer-group', 'link' => site_url('transfer/transfer_table/HO2/HO2')),
	array('key' => 'pending_stitching', 'label' => t('menu_pending_stitching'), 'icon' => 'fa-tshirt', 'link' => site_url('transfer/transfer_table/STIT/STIT')),
);
$user_name = (string) $this->session->userdata('user');
?>
<div class="dz-dashboard">
	<header class="dz-dashboard__header">
		<h1 class="dz-dashboard__title"><?php echo esc_html(t('dash_title')); ?></h1>
		<p class="dz-dashboard__subtitle"><?php echo esc_html(t('dash_welcome')); ?></p>
		<?php if ($user_name !== '') { ?>
		<p class="dz-dashboard__meta">
			<?php echo ui_icon('calendar-alt', 'far'); ?>
			<?php echo esc_html(date('l, d M Y')); ?>
			&middot; <?php echo esc_html($user_name); ?>
		</p>
		<?php } ?>
	</header>

	<section class="dz-search-panel" aria-label="<?php echo esc_html(t('dash_search_placeholder')); ?>">
		<form id="dashboard-home-search-form" class="dz-search-panel__row dz-search-panel__row--home" action="#" method="get" data-no-ajax="true" onsubmit="return dashboardHomeSearchSubmit(event);">
			<input
				class="dz-input form-control dz-search-panel__input"
				type="search"
				name="search"
				id="dash-home-search"
				placeholder="<?php echo esc_html(t('dash_search_placeholder')); ?>"
				autocomplete="off"
				enterkeyhint="search"
				autofocus>
			<?php echo ui_btn(array('label' => t('app_search'), 'type' => 'submit', 'variant' => 'filled', 'icon' => 'fa-search')); ?>
			<?php echo ui_btn(array('label' => t('dash_create_customer'), 'href' => site_url('Customer/Customer_add'), 'variant' => 'tonal', 'icon' => 'fa-user-plus')); ?>
		</form>
	</section>

	<h2 class="dz-section-label"><?php echo esc_html(t('dash_overview_title')); ?></h2>
	<div class="dz-stat-grid dz-mb-6">
		<?php foreach ($dash_cards as $dash_card) {
			echo ui_stat(array(
				'label' => $dash_card['label'],
				'value' => isset($dashboard_summary[$dash_card['key']]) ? (int) $dashboard_summary[$dash_card['key']] : 0,
				'icon' => $dash_card['icon'],
				'href' => $dash_card['link'],
			));
		} ?>
	</div>

	<section class="dz-card">
		<div class="dz-card__header">
			<h2 class="dz-card__title"><?php echo esc_html(t('dash_quick_actions')); ?></h2>
		</div>
		<div class="dz-card__body">
			<div class="dz-actions-row">
				<?php
				echo ui_btn(array('label' => t('dash_add_customer'), 'href' => site_url('Customer/Customer_add'), 'variant' => 'filled', 'icon' => 'fa-user-plus'));
				echo ui_btn(array('label' => t('dash_open_ready_deliver'), 'href' => site_url('Order/order_issue'), 'variant' => 'tonal', 'icon' => 'fa-box-open'));
				echo ui_btn(array('label' => t('dash_search_order_status'), 'href' => site_url('Order/order_view_det'), 'variant' => 'outlined', 'icon' => 'fa-search'));
				echo ui_btn(array('label' => t('dash_order_detail_report'), 'href' => site_url('Cash/reprtfinancial_dashboard'), 'variant' => 'outlined', 'icon' => 'fa-chart-line', 'attrs' => 'target="_blank" rel="noopener noreferrer"'));
				?>
			</div>
		</div>
	</section>
</div>
