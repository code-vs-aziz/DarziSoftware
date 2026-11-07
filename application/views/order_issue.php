<?php
	ob_Start();
$this->load->helper('ui');

	$this->load->view('Head');
	$this->load->view('Header');
?>
<body class="erp-app dt-example">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="container-fluid">
				<?php
				$notify_delivery_status = $this->session->flashdata('notify_delivery_status');
				if ($notify_delivery_status === 'sent') { ?>
				<div class="alert alert-success" role="alert"><?php echo t('notify_delivery_sent_ok'); ?></div>
				<?php } elseif ($notify_delivery_status === 'failed' || $notify_delivery_status === 'skipped') {
					$detail = (string) $this->session->flashdata('notify_delivery_detail');
				?>
				<div class="alert alert-warning" role="alert">
					<?php echo t('notify_delivery_sent_fail'); ?>
					<?php if ($detail === 'missing_phone') { echo ' — '.t('notify_delivery_no_phone'); } ?>
					<?php if ($detail === 'bridge_disabled' || $detail === 'bridge_unreachable' || $detail === 'not_ready') { echo ' — '.t('notify_bridge_not_ready'); } ?>
				</div>
				<?php } ?>
				<?php
				$ready_return_msg = $this->session->flashdata('ready_return_msg');
				if ($ready_return_msg) { ?>
				<div class="alert alert-success" role="alert">
					<?php echo esc_html($ready_return_msg); ?>
				</div>
				<?php } ?>
				<?php $this->load->view('partials/success_alert', array('success_default' => t('order_issue_success'))); ?>
				<div class="col-md-12">
					<div class="dz-card erp-module-card">
						<div class="erp-card-head">
							<h2 class="erp-module-title mb-0"><?php echo t('order_ready_deliver'); ?></h2>
						</div>
						<div class="card-body erp-card-body">
							<?php
							$rd_tab = ($this->uri->segment(3) === 'D') ? 'D' : 'C';
							$rd_filters = isset($ready_filters) && is_array($ready_filters) ? $ready_filters : array();
							$rd_customer = isset($rd_filters['customer_name']) ? (string) $rd_filters['customer_name'] : '';
							$rd_order_no = isset($rd_filters['order_no']) ? (string) $rd_filters['order_no'] : '';
							$rd_naap_no = isset($rd_filters['naap_no']) ? (string) $rd_filters['naap_no'] : '';
							$rd_mobile_no = isset($rd_filters['mobile_no']) ? (string) $rd_filters['mobile_no'] : '';
							$rd_return_from = isset($rd_filters['return_from']) ? (string) $rd_filters['return_from'] : '';
							$rd_return_to = isset($rd_filters['return_to']) ? (string) $rd_filters['return_to'] : '';
							$rd_has_filter = ($rd_customer !== '' || $rd_order_no !== '' || $rd_naap_no !== '' || $rd_mobile_no !== ''
								|| $rd_return_from !== '' || $rd_return_to !== '');
							$rd_return_query = http_build_query(array_filter(array(
								'customer_name' => $rd_customer,
								'order_no' => $rd_order_no,
								'naap_no' => $rd_naap_no,
								'mobile_no' => $rd_mobile_no,
								'return_from' => $rd_return_from,
								'return_to' => $rd_return_to,
							), function ($v) {
								return trim((string) $v) !== '';
							}));
							?>
							<form method="get" class="mb-3" action="<?php echo site_url('Order/order_issue/'.$rd_tab); ?>">
								<div class="row align-items-end">
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Customer</strong></label>
										<input type="text" name="customer_name" class="form-control form-control-sm order-filter-urdu-field customer-urdu-field" dir="rtl" lang="ur" placeholder="<?php echo esc_html(t('customer_name')); ?>" value="<?php echo esc_html($rd_customer); ?>" autocomplete="off">
									</div>
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Order #</strong></label>
										<input type="text" name="order_no" class="form-control form-control-sm" placeholder="Order number" value="<?php echo esc_html($rd_order_no); ?>" autocomplete="off">
									</div>
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Naap #</strong></label>
										<input type="text" name="naap_no" class="form-control form-control-sm" placeholder="Naap number" value="<?php echo esc_html($rd_naap_no); ?>" autocomplete="off">
									</div>
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Mobile #</strong></label>
										<input type="text" name="mobile_no" class="form-control form-control-sm" placeholder="Mobile number" value="<?php echo esc_html($rd_mobile_no); ?>" autocomplete="off">
									</div>
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Return From</strong></label>
										<input type="date" name="return_from" class="form-control form-control-sm" value="<?php echo esc_html($rd_return_from); ?>">
									</div>
									<div class="col-md-2 mb-2">
										<label class="mb-1"><strong>Return To</strong></label>
										<input type="date" name="return_to" class="form-control form-control-sm" value="<?php echo esc_html($rd_return_to); ?>">
									</div>
									<div class="col-md-12 mb-2">
										<label class="mb-1 d-none d-md-block">&nbsp;</label>
										<button type="submit" class="btn btn-primary dz-btn--filled btn-lg btn-block"><?php echo t('app_search'); ?></button>
									</div>
								</div>
							</form>
							<p class="text-muted small mb-2"><?php echo esc_html(t('order_issue_filter_hint')); ?></p>
							<?php if ($rd_has_filter && empty($order_det)) { ?>
							<p class="text-muted small mb-2">No ready orders match these filters. Adjust the filters and search again.</p>
							<?php } elseif ( ! $rd_has_filter && empty($order_det)) { ?>
							<p class="text-muted small mb-2">No orders are ready for delivery right now. Complete stitching (Pending Stitching) to add them here.</p>
							<?php } ?>
							<section>
								<table id="example" class="display order-ready-table" style="width:100%" data-page-length="10">
									<thead>
										<tr>
											<th>Order #</th>
											<th>Items</th>
											<th>Type</th>
											<th>Return Date</th>
											<th>Customer</th>
											<th>Naap No</th>
											<th>Mobile No</th>
											<th><?php echo t('app_actions'); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php
									if (isset($order_det) && is_array($order_det)) {
										foreach ($order_det as $order_row) {
											$order_no = (int) (isset($order_row->ORDERNO) ? $order_row->ORDERNO : $order_row->trnno);
											$line_count = isset($order_row->line_count) ? (int) $order_row->line_count : 1;
									?>
										<tr>
											<td><?php echo $order_no; ?></td>
											<td><?php echo $line_count; ?></td>
											<td><?php echo esc_html((string) $order_row->line_type); ?></td>
											<td><?php
											$return_ts = strtotime($order_row->RETURNDATE);
											echo $return_ts ? date('d-m-Y', $return_ts) : '';
											?></td>
											<td><?php echo esc_html($order_row->name); ?></td>
											<td><?php echo esc_html($order_row->man_napno); ?></td>
											<td><?php echo esc_html($order_row->Phone); ?></td>
											<td>
												<a href="<?php echo site_url('Order/order_deliverd/'.(int) (isset($order_row->ORDERNO) ? $order_row->ORDERNO : $order_row->trnno)); ?>" class="btn btn-primary dz-btn--filled btn-sm" data-no-ajax="true"><?php echo t('order_delivered'); ?></a>
												<?php
												$return_stitch_url = site_url('Order/ready_return_order_stitching/'.$order_no);
												if ($rd_return_query !== '') {
													$return_stitch_url .= '?'.$rd_return_query;
												}
												?>
												<a href="<?php echo $return_stitch_url; ?>" class="btn btn-secondary dz-btn--outlined btn-sm" data-no-ajax="true" onclick="return confirm('<?php echo esc_html(t('order_return_stitching_confirm')); ?>');"><?php echo t('order_return_stitching'); ?></a>
											</td>
										</tr>
									<?php
										}
									}
									?>
									</tbody>
								</table>
							</section>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
<script>
(function () {
	if (window.orderReadyDataTableBound) {
		return;
	}
	window.orderReadyDataTableBound = true;
	jQuery(function ($) {
		var $table = $('table#example.order-ready-table');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var pageLen = parseInt($table.data('page-length'), 10) || 10;
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			pageLength: pageLen,
			lengthChange: false,
			dom: 'frtip'
		});
		var $wrapper = $table.closest('.dataTables_wrapper');
		$wrapper.find('.order-ready-length').remove();
		var $ctrl = $('<div class="dataTables_length order-ready-length"><label>Show <input type="number" min="1" max="10000" step="1" class="form-control form-control-sm" style="width:5rem;display:inline-block;"> entries</label></div>');
		$ctrl.find('input').val(pageLen);
		$wrapper.find('.dataTables_filter').before($ctrl);
		$ctrl.find('input').on('change', function () {
			var n = parseInt($(this).val(), 10) || pageLen;
			n = Math.min(10000, Math.max(1, n));
			$(this).val(n);
			dt.page.len(n).draw();
		});
	});
})();
</script>
</html>
