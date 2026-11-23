<?php
/**
 * Offline / sync status chip — same ERP header UI, no separate desk link.
 */
if (!$this->session->userdata('user')) {
	return;
}
$offline_desk_enabled = function_exists('dz_env_bool') ? dz_env_bool('OFFLINE_DESK_ENABLED', TRUE) : TRUE;
if ( ! $offline_desk_enabled) {
	return;
}
$offline_variant = isset($offline_indicator_variant) ? $offline_indicator_variant : 'desktop';
?>
<button type="button"
	class="dz-offline-chip dz-offline-chip--<?php echo esc_attr($offline_variant); ?> is-checking"
	id="dz-offline-chip-<?php echo esc_attr($offline_variant); ?>"
	data-offline-chip="1"
	data-variant="<?php echo esc_attr($offline_variant); ?>"
	title="<?php echo esc_attr(t('offline_chip_title')); ?>"
	aria-live="polite">
	<span class="dz-offline-chip__dot" aria-hidden="true"></span>
	<span class="dz-offline-chip__label" data-offline-label><?php echo esc_html(t('offline_chip_checking')); ?></span>
	<span class="dz-offline-chip__count" data-offline-count hidden></span>
</button>
<button type="button"
	class="dz-offline-chip dz-offline-chip--sync dz-offline-chip--<?php echo esc_attr($offline_variant); ?>"
	data-offline-sync="1"
	title="<?php echo esc_attr(t('offline_sync_now')); ?>">
	<span class="dz-offline-chip__label"><?php echo esc_html(t('offline_sync_now')); ?></span>
</button>
