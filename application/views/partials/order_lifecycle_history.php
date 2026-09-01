<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$history = isset($history) && is_array($history) ? $history : array();
$order_header = isset($history['order']) ? $history['order'] : NULL;
$lines = isset($history['lines']) && is_array($history['lines']) ? $history['lines'] : array();

if ( ! function_exists('order_lifecycle_event_label'))
{
	function order_lifecycle_event_label($event_key)
	{
		$keys = array(
			'booked' => 'lifecycle_booked',
			'cutting' => 'lifecycle_cutting',
			'cutting_done' => 'lifecycle_cutting_done',
			'ho2_return' => 'lifecycle_ho2',
			'stitching' => 'lifecycle_stitching',
			'stitching_done' => 'lifecycle_stitching_done',
			'ready' => 'lifecycle_ready',
			'delivered' => 'lifecycle_delivered',
			'other' => 'lifecycle_other',
		);
		$lang_key = isset($keys[$event_key]) ? $keys[$event_key] : 'lifecycle_other';
		return t($lang_key);
	}
}

if ( ! function_exists('order_lifecycle_format_date'))
{
	function order_lifecycle_format_date($date_str)
	{
		$date_str = trim((string) $date_str);
		if ($date_str === '' || $date_str === '0000-00-00')
		{
			return '—';
		}
		$ts = strtotime($date_str);
		return $ts ? date('d-m-Y', $ts) : $date_str;
	}
}
?>
<?php if ( ! $order_header) { ?>
	<p class="text-center text-muted py-3 mb-0"><?php echo t('lifecycle_order_not_found'); ?></p>
<?php } elseif (empty($lines)) { ?>
	<p class="text-muted mb-2">
		<strong><?php echo t('order_history_order_no'); ?>:</strong> <?php echo (int) $order_header->trnno; ?>
		&nbsp;|&nbsp;
		<strong><?php echo t('order_customer_name'); ?>:</strong> <?php echo esc_html($order_header->customer_name); ?>
	</p>
	<p class="text-center text-muted py-3 mb-0"><?php echo t('lifecycle_no_lines'); ?></p>
<?php } else { ?>
	<div class="mb-3">
		<strong><?php echo t('order_history_order_no'); ?>:</strong> <?php echo (int) $order_header->trnno; ?>
		&nbsp;|&nbsp;
		<strong><?php echo t('order_customer_name'); ?>:</strong> <?php echo esc_html($order_header->customer_name); ?>
		&nbsp;|&nbsp;
		<strong><?php echo t('order_history_entry_date'); ?>:</strong> <?php echo order_lifecycle_format_date($order_header->order_date); ?>
	</div>
	<?php foreach ($lines as $line) { ?>
	<div class="card mb-3 order-lifecycle-line-card">
		<div class="card-header py-2">
			<strong><?php echo sprintf(t('lifecycle_line_title'), (int) $line['line_no']); ?></strong>
			<?php if ( ! empty($line['type'])) { ?>
				<span class="text-muted"> — <?php echo esc_html($line['type']); ?></span>
			<?php } ?>
			<?php if ( ! empty($line['suit_name'])) { ?>
				<span class="badge badge-secondary ml-1"><?php echo esc_html($line['suit_name']); ?></span>
			<?php } ?>
		</div>
		<div class="card-body p-0">
			<?php if (empty($line['events'])) { ?>
			<p class="text-muted mb-0 p-3"><?php echo t('lifecycle_no_events'); ?></p>
			<?php } else { ?>
			<div class="table-responsive">
				<table class="table table-sm table-striped mb-0">
					<thead>
						<tr>
							<th><?php echo t('lifecycle_col_date'); ?></th>
							<th><?php echo t('lifecycle_col_event'); ?></th>
							<th><?php echo t('lifecycle_col_employee'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($line['events'] as $event) { ?>
						<tr>
							<td><?php echo order_lifecycle_format_date($event['date']); ?></td>
							<td><?php echo esc_html(order_lifecycle_event_label($event['event_key'])); ?></td>
							<td><?php echo $event['employee'] !== '' ? esc_html($event['employee']) : '—'; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<?php } ?>
			<?php if ( ! empty($line['remarks'])) { ?>
			<div class="px-3 py-2 border-top">
				<small class="text-muted"><?php echo t('order_note_line'); ?>:</small>
				<span dir="rtl" lang="ur"><?php echo esc_html($line['remarks']); ?></span>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
<?php } ?>
