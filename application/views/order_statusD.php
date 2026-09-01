<?php
	ob_Start();
$this->load->helper('ui');

	$this->load->view('Head');
	$this->load->view('Header');

	$osd_filters = isset($status_filters) && is_array($status_filters) ? $status_filters : array();
	$osd_customer = isset($osd_filters['customer_name']) ? (string) $osd_filters['customer_name'] : '';
	$osd_order_no = isset($osd_filters['order_no']) ? (string) $osd_filters['order_no'] : '';
	$osd_naap_no = isset($osd_filters['naap_no']) ? (string) $osd_filters['naap_no'] : '';
	$osd_mobile_no = isset($osd_filters['mobile_no']) ? (string) $osd_filters['mobile_no'] : '';
	$osd_return_from = isset($osd_filters['return_from']) ? (string) $osd_filters['return_from'] : '';
	$osd_return_to = isset($osd_filters['return_to']) ? (string) $osd_filters['return_to'] : '';
	$osd_order_from = isset($osd_filters['order_from']) ? (string) $osd_filters['order_from'] : '';
	$osd_order_to = isset($osd_filters['order_to']) ? (string) $osd_filters['order_to'] : '';
	$employee_by_no = array();
	if (isset($employee) && is_array($employee)) {
		foreach ($employee as $employees) {
			$employee_by_no[$employees->empno] = $employees->name;
		}
	}
	$orderdata = isset($orderdata) && is_array($orderdata) ? $orderdata : array();
?>
<body class="erp-app dt-example">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card">
							<div class="erp-card-head d-flex flex-wrap align-items-center justify-content-between gap-2">
								<h2 class="erp-module-title mb-0"><?php echo t('order_delivered_status'); ?></h2>
								<div>
									<a href="<?php echo site_url('transfer/order_Status/C'); ?>" class="btn btn-primary dz-btn--filled btn-sm"><?php echo t('order_status_current'); ?></a>
									<a href="<?php echo site_url('transfer/order_StatusD'); ?>" class="btn btn-primary dz-btn--filled btn-sm"><?php echo t('order_status_delivered_tab'); ?></a>
								</div>
							</div>
							<div class="card-body erp-card-body">
								<form method="post" class="mb-3" action="<?php echo site_url('transfer/order_StatusD'); ?>">
									<div class="row align-items-end">
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Customer</strong></label>
											<input type="text" id="os-filter-customer-name" name="customer_name" class="form-control form-control-sm order-filter-urdu-field customer-urdu-field" dir="rtl" lang="ur" placeholder="<?php echo esc_html(t('customer_name')); ?>" value="<?php echo esc_html($osd_customer); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Order #</strong></label>
											<input type="text" name="order_no" class="form-control form-control-sm" placeholder="Order number" value="<?php echo esc_html($osd_order_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Naap #</strong></label>
											<input type="text" name="naap_no" class="form-control form-control-sm" placeholder="Naap number" value="<?php echo esc_html($osd_naap_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Mobile #</strong></label>
											<input type="text" name="mobile_no" class="form-control form-control-sm" placeholder="Mobile number" value="<?php echo esc_html($osd_mobile_no); ?>" autocomplete="off">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Return From</strong></label>
											<input type="date" name="return_from" class="form-control form-control-sm" value="<?php echo esc_html($osd_return_from); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Return To</strong></label>
											<input type="date" name="return_to" class="form-control form-control-sm" value="<?php echo esc_html($osd_return_to); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Order From</strong></label>
											<input type="date" name="order_from" class="form-control form-control-sm" value="<?php echo esc_html($osd_order_from); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<label class="mb-1"><strong>Order To</strong></label>
											<input type="date" name="order_to" class="form-control form-control-sm" value="<?php echo esc_html($osd_order_to); ?>">
										</div>
										<div class="col-md-2 mb-2">
											<button type="submit" class="btn btn-primary dz-btn--filled btn-sm btn-block"><?php echo t('app_submit'); ?></button>
										</div>
									</div>
								</form>
								<p class="text-muted small mb-2">Delivered orders for the selected filters. Last 30 days load by default.</p>
								<section>
									<table id="example" class="display" style="width:100%" data-order-list-table="1">
										<thead>
											<tr>
												<th>Order #</th>
												<th>Employee</th>
												<th>Entry Date</th>
												<th><?php echo t('order_order_date'); ?></th>
												<th><?php echo t('order_return_date'); ?></th>
												<th>Phone</th>
												<th>Customer</th>
												<th>Naap No</th>
												<th>Remarks</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
										<?php
										$order = 0;
										$sr = 1;
										foreach ($orderdata as $row) {
											if ($row->ostatus != 0) {
												continue;
											}
											if ($row->ORDERNO != $order) {
												$order = $row->ORDERNO;
												$sr = 1;
											} else {
												$sr = $sr + 1;
											}
										?>
											<tr>
												<td><?php echo $row->ORDERNO.'-'.$sr; ?></td>
												<td><?php echo isset($employee_by_no[$row->empno]) ? $employee_by_no[$row->empno] : ''; ?></td>
												<td><?php
													$originalDate = ($row->dept == 'CUT' || $row->dept == 'STIT') ? $row->TRANSFEREDATE : $row->ORDERDATE;
													echo date('d-m-Y', strtotime($originalDate));
												?></td>
												<td><?php
													echo !empty($row->ORDERDATE) ? date('d-m-Y', strtotime($row->ORDERDATE)) : '';
												?></td>
												<td><?php
													echo !empty($row->ORDERRDATE) ? date('d-m-Y', strtotime($row->ORDERRDATE)) : '';
												?></td>
												<td><?php echo $row->Phone; ?></td>
												<td><?php echo $row->name; ?></td>
												<td><?php echo $row->man_napno; ?></td>
												<td><?php echo $row->order_remarks; ?></td>
												<td>Delivered</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</section>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
