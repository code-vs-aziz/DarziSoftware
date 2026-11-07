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
// Shared shop bridge — any logged-in user can see status; only admin manages QR.
$can_manage_bridge = (isset($this->rbac) && is_object($this->rbac) && $this->rbac->can('configuration.manage'));
$bridge_fix_url = $can_manage_bridge ? site_url('Notifications') : site_url('Welcome');
$bridge_variant = isset($bridge_indicator_variant) ? $bridge_indicator_variant : 'desktop';
$bridge_title = $can_manage_bridge
	? t('notify_bridge_chip_title')
	: t('notify_bridge_chip_title_shared', 'WhatsApp bridge (shared) — admin scans QR once for the whole shop');
?>
<a href="<?php echo esc_attr($bridge_fix_url); ?>"
	class="dz-bridge-chip dz-bridge-chip--<?php echo esc_attr($bridge_variant); ?> is-checking"
	id="dz-bridge-chip-<?php echo esc_attr($bridge_variant); ?>"
	data-bridge-chip="1"
	data-status-url="<?php echo esc_attr($bridge_status_url); ?>"
	data-fix-url="<?php echo esc_attr($bridge_fix_url); ?>"
	data-can-manage="<?php echo $can_manage_bridge ? '1' : '0'; ?>"
	title="<?php echo esc_attr($bridge_title); ?>"
	aria-live="polite">
	<span class="dz-bridge-chip__dot" aria-hidden="true"></span>
	<span class="dz-bridge-chip__label" data-bridge-label><?php echo esc_html(t('notify_bridge_chip_checking')); ?></span>
</a>
