<?php
$orders = isset($orderdata) && is_array($orderdata) ? $orderdata : array();
?>
<div class="table-responsive">
	<table class="table table-sm table-striped table-bordered mb-0 customer-order-history-table">
		<thead>
			<tr>
				<th><?php echo t('order_history_order_no'); ?></th>
				<th><?php echo t('order_history_entry_date'); ?></th>
				<th><?php echo t('order_history_return_date'); ?></th>
				<th><?php echo t('order_history_naap'); ?></th>
				<th><?php echo t('app_total'); ?></th>
				<th><?php echo t('app_view'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($orders)) { ?>
			<tr>
				<td colspan="6" class="text-center text-muted py-3"><?php echo t('order_history_none'); ?></td>
			</tr>
		<?php } else {
			foreach ($orders as $order_row) { ?>
			<tr>
				<td><?php echo (int) $order_row->ORDERNO; ?></td>
				<td><?php echo date('d-m-Y', strtotime($order_row->ORDERDATE)); ?></td>
				<td><?php echo date('d-m-Y', strtotime($order_row->RDATE)); ?></td>
				<td><?php echo esc_html($order_row->man_napno); ?></td>
				<td><?php echo esc_html($order_row->total); ?></td>
				<td>
					<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_Print_det/'.$order_row->ORDERNO); ?>" target="_blank" rel="noopener noreferrer"><?php echo t('app_view'); ?></a>
				</td>
			</tr>
		<?php }
		} ?>
		</tbody>
	</table>
</div>
