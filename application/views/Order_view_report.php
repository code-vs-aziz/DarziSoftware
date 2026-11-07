<?php
	ob_Start();
$this->load->helper('ui');

	$this->load->view('Head');
	$this->load->view('Header');

	$ovr_filters = isset($detail_filters) && is_array($detail_filters) ? $detail_filters : array();
	$ovr_customer = isset($ovr_filters['customer_name']) ? (string) $ovr_filters['customer_name'] : '';
	$ovr_order_no = isset($ovr_filters['order_no']) ? (string) $ovr_filters['order_no'] : '';
	$ovr_naap_no = isset($ovr_filters['naap_no']) ? (string) $ovr_filters['naap_no'] : '';
	$ovr_mobile_no = isset($ovr_filters['mobile_no']) ? (string) $ovr_filters['mobile_no'] : '';
	$ovr_return_from = isset($ovr_filters['return_from']) ? (string) $ovr_filters['return_from'] : '';
	$ovr_return_to = isset($ovr_filters['return_to']) ? (string) $ovr_filters['return_to'] : '';
	$ovr_row_limit = isset($ovr_filters['row_limit']) ? (int) $ovr_filters['row_limit'] : 500;
	if ($ovr_row_limit < 1) {
		$ovr_row_limit = 500;
	}
	if ($ovr_row_limit > 10000) {
		$ovr_row_limit = 10000;
	}
	$ovr_order_status = isset($ovr_filters['order_status']) ? strtolower((string) $ovr_filters['order_status']) : 'active';
	if (!in_array($ovr_order_status, array('active', 'deactivated', 'all'), TRUE)) {
		$ovr_order_status = 'active';
	}
	$orderdata = isset($orderdata) && is_array($orderdata) ? $orderdata : array();
?>
<body class="erp-app dt-example">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
					<div class="col-md-12">
						<div class="dz-card erp-module-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('order_detail'); ?></h2>
							</div>
							<div class="card-body erp-card-body">
								<form method="post" class="mb-3" action="<?php echo site_url('Order/order_view_det'); ?>">
									<div class="row align-items-end">
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Customer</strong></label>
											<input type="text" name="customer_name" class="form-control form-control-sm order-filter-urdu-field customer-urdu-field" dir="rtl" lang="ur" placeholder="<?php echo esc_html(t('customer_name')); ?>" value="<?php echo esc_html($ovr_customer); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Order #</strong></label>
											<input type="text" name="order_no" class="form-control form-control-sm" placeholder="Order number" value="<?php echo esc_html($ovr_order_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Naap #</strong></label>
											<input type="text" name="naap_no" class="form-control form-control-sm" placeholder="Naap number" value="<?php echo esc_html($ovr_naap_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Mobile #</strong></label>
											<input type="text" name="mobile_no" class="form-control form-control-sm" placeholder="Mobile number" value="<?php echo esc_html($ovr_mobile_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Return From</strong></label>
											<input type="date" name="return_from" class="form-control form-control-sm" value="<?php echo esc_html($ovr_return_from); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Return To</strong></label>
											<input type="date" name="return_to" class="form-control form-control-sm" value="<?php echo esc_html($ovr_return_to); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Show entries</strong></label>
											<input type="number" name="row_limit" class="form-control form-control-sm" value="<?php echo (int) $ovr_row_limit; ?>" min="1" max="10000" step="1" title="Max orders to load from database">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong><?php echo esc_html(t('order_status_filter')); ?></strong></label>
											<select name="order_status" class="form-control form-control-sm">
												<option value="active"<?php echo $ovr_order_status === 'active' ? ' selected' : ''; ?>><?php echo esc_html(t('order_status_active')); ?></option>
												<option value="deactivated"<?php echo $ovr_order_status === 'deactivated' ? ' selected' : ''; ?>><?php echo esc_html(t('order_status_deactivated')); ?></option>
												<option value="all"<?php echo $ovr_order_status === 'all' ? ' selected' : ''; ?>><?php echo esc_html(t('order_status_all')); ?></option>
											</select>
										</div>
										<div class="col-md-10 mb-2">
											<label class="mb-1 d-none d-md-block">&nbsp;</label>
											<button type="submit" class="btn btn-primary dz-btn--filled btn-lg btn-block"><?php echo t('app_submit'); ?></button>
										</div>
									</div>
								</form>
								<p class="text-muted small mb-2">Set Return From / Return To yourself when needed, or search by customer, order #, naap, or mobile. Empty dates do not apply a date filter.</p>
								<section>
									<table id="example" class="display order-detail-table" style="width:100%" data-page-length="10">
										<thead>
											<tr>
												<th>Order #</th>
												<th>Entry Date</th>
												<th>Retrun Date</th>
												<th>Customer</th>
												<th>Naap No</th>
												<th>Mobile</th>
												<th>Edit</th>
												<th>Delete</th>
												<th>View</th>
												<th><?php echo t('lifecycle_history'); ?></th>
											</tr>
										</thead>
										<tbody>
										<?php foreach ($orderdata as $order_row) {
											$is_deactivated = (isset($order_row->ostatus) && (string) $order_row->ostatus === '1');
										?>
											<tr<?php echo $is_deactivated ? ' class="table-warning"' : ''; ?>>
												<td><?php echo $order_row->ORDERNO; ?></td>
												<td><?php
													$newDate1 = date('d-m-Y', strtotime($order_row->ORDERDATE));
													echo $newDate1;
												?></td>
												<td><?php
													$newDate1 = date('d-m-Y', strtotime($order_row->RDATE));
													echo $newDate1;
												?></td>
												<td><?php echo $order_row->name; ?></td>
												<td><?php echo $order_row->man_napno; ?></td>
												<td><?php echo $order_row->Phone; ?></td>
												<td>
													<?php if ( ! $is_deactivated) { ?>
													<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_edit/'.$order_row->ORDERNO.''); ?>" data-no-ajax="true">Edit</a>
													<?php } else { ?>
													<span class="text-muted small"><?php echo esc_html(t('app_deactive')); ?></span>
													<?php } ?>
												</td>
												<td>
													<?php if ($is_deactivated) { ?>
													<a class="btn btn-success dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_active/'.$order_row->ORDERNO); ?>" onclick="return confirm(<?php echo htmlspecialchars(json_encode(t('app_order_reactivate_confirm')), ENT_QUOTES, 'UTF-8'); ?>);"><?php echo esc_html(t('app_reactivate')); ?></a>
													<?php } else { ?>
													<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_deactive/'.$order_row->ORDERNO); ?>" onclick="return confirm(<?php echo htmlspecialchars(json_encode(t('app_order_deactivate_confirm')), ENT_QUOTES, 'UTF-8'); ?>);"><?php echo esc_html(t('app_deactive')); ?></a>
													<?php } ?>
												</td>
												<td><a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_Print_det/'.$order_row->ORDERNO.''); ?>" target="_blank">View</a></td>
												<td>
													<a href="#" class="btn btn-primary dz-btn--filled btn-sm btn-order-lifecycle" role="button" data-order-id="<?php echo (int) $order_row->ORDERNO; ?>" data-no-ajax="true"><?php echo t('lifecycle_history'); ?></a>
												</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</section>
								<div class="modal fade" id="orderLifecycleModal" tabindex="-1" role="dialog" aria-labelledby="orderLifecycleModalLabel" aria-hidden="true">
									<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="orderLifecycleModalLabel"><?php echo t('lifecycle_title'); ?></h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body" id="orderLifecycleModalBody">
												<p class="text-center text-muted py-3 mb-0"><?php echo t('app_loading'); ?></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</body>
</html>
