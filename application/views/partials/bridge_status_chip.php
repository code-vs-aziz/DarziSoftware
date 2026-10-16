<?php
/**
 * Global WhatsApp bridge health chip (header).
 * Shown when WHATSAPP_BRIDGE_ENABLED=true and user is logged in.
 */
$bridge_ui_enabled = function_exists('dz_env_bool') ? dz_env_bool('WHATSAPP_BRIDGE_ENABLED', FALSE) : FALSE;
if (!$bridge_ui_enabled || !$this->session->userdata('user')) {
	return;
}
$bridge_status_url = site_url('Notifications/bridge_status_json');
$bridge_fix_url = site_url('Notifications');
$bridge_variant = isset($bridge_indicator_variant) ? $bridge_indicator_variant : 'desktop';
?>
<a href="<?php echo esc_attr($bridge_fix_url); ?>"
	class="dz-bridge-chip dz-bridge-chip--<?php echo esc_attr($bridge_variant); ?> is-checking"
	id="dz-bridge-chip-<?php echo esc_attr($bridge_variant); ?>"
	data-bridge-chip="1"
	data-status-url="<?php echo esc_attr($bridge_status_url); ?>"
	data-fix-url="<?php echo esc_attr($bridge_fix_url); ?>"
	title="<?php echo esc_attr(t('notify_bridge_chip_title')); ?>"
	aria-live="polite">
	<span class="dz-bridge-chip__dot" aria-hidden="true"></span>
	<span class="dz-bridge-chip__label" data-bridge-label><?php echo esc_html(t('notify_bridge_chip_checking')); ?></span>
</a>
