<?php
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');

$code = isset($customer_code) ? (int) $customer_code : 0;
$c = isset($customer) ? $customer : NULL;
$ctx = isset($order_context) && is_array($order_context) ? $order_context : array();
$meas_code = isset($measurement_code) ? (int) $measurement_code : 0;
$has_meas = ! empty($has_measurement);
$orders = isset($order_history) && is_array($order_history) ? $order_history : array();
$suits = isset($open_suits) && is_array($open_suits) ? $open_suits : array();
$ledger_from = isset($ledger_from) ? (string) $ledger_from : date('Y-m-d', strtotime('-1 year'));

$name = $c && isset($c->name) ? (string) $c->name : '';
$phone = $c && isset($c->Phone) ? (string) $c->Phone : '';
$naap = $c && isset($c->man_napno) ? (string) $c->man_napno : '';
$cnic = $c && isset($c->cnic) ? (string) $c->cnic : '';
$address = $c && isset($c->Address) ? (string) $c->Address : '';
$pending = isset($ctx['pending_balance']) ? (float) $ctx['pending_balance'] : 0;
$order_count = isset($ctx['order_count']) ? (int) $ctx['order_count'] : 0;
$pending_count = isset($ctx['pending_count']) ? (int) $ctx['pending_count'] : 0;

$stage_labels = array(
	'BOOK' => t('customer_stage_booking'),
	'CUT' => t('customer_stage_cutting'),
	'HO2' => t('customer_stage_ho2'),
	'STIT' => t('customer_stage_stitching'),
	'DEL' => t('customer_stage_delivery'),
	'READY' => t('customer_stage_ready'),
);

$meas_url = $has_meas
	? site_url('Measurement/measurement_edit/'.$code)
	: site_url('Measurement/measurement_add/'.$code.'?return=order');
$order_url = $has_meas
	? site_url('Order/order_add1/'.$code.'/'.$meas_code)
	: $meas_url;
$edit_url = site_url('Customer/customer_edit/'.$code);
$ledger_url = site_url('Cash/customer_ledger/'.$ledger_from.'/'.$code);
?>
<body class="animsition erp-app erp-form-page customer-record-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card customer-record">
							<div class="erp-card-head">
								<div>
									<h2 class="erp-module-title mb-0"><?php echo esc_html($name !== '' ? $name : t('customer_title')); ?></h2>
									<p class="customer-record__sub mb-0">
										<?php echo esc_html(t('customer_number')); ?> <?php echo (int) $code; ?>
										<?php if ($naap !== '') { ?> · <?php echo esc_html(t('customer_naap')); ?> <?php echo esc_html($naap); ?><?php } ?>
										<?php if ($phone !== '') { ?> · <?php echo esc_html($phone); ?><?php } ?>
									</p>
								</div>
								<div class="erp-card-actions">
									<a class="btn btn-outline-secondary dz-btn--outlined btn-sm" href="<?php echo site_url('Welcome'); ?>"><?php echo esc_html(t('menu_dashboard_home')); ?></a>
								</div>
							</div>

							<div class="card-body erp-card-body">
								<div class="customer-record__stats">
									<div class="customer-record__stat">
										<span><?php echo esc_html(t('customer_orders_count')); ?></span>
										<strong><?php echo (int) $order_count; ?></strong>
									</div>
									<div class="customer-record__stat<?php echo $pending > 0 ? ' is-warn' : ''; ?>">
										<span><?php echo esc_html(t('customer_outstanding')); ?></span>
										<strong><?php echo esc_html(number_format($pending, 2)); ?></strong>
										<?php if ($pending_count > 0) { ?>
										<em><?php echo (int) $pending_count; ?> <?php echo esc_html(t('customer_pending_entries')); ?></em>
										<?php } ?>
									</div>
									<div class="customer-record__stat">
										<span><?php echo esc_html(t('customer_measurement')); ?></span>
										<strong><?php echo $has_meas ? esc_html(t('app_yes')) : esc_html(t('app_no')); ?></strong>
									</div>
									<?php if ( ! empty($ctx['has_last_order'])) { ?>
									<div class="customer-record__stat">
										<span><?php echo esc_html(t('customer_last_order')); ?></span>
										<strong>#<?php echo (int) $ctx['last_order_no']; ?></strong>
										<em><?php echo esc_html((string) $ctx['last_order_date']); ?> · <?php echo esc_html(number_format((float) $ctx['last_order_total'], 2)); ?></em>
									</div>
									<?php } ?>
								</div>

								<div class="customer-record__actions">
									<a class="btn btn-primary dz-btn--filled" href="<?php echo $meas_url; ?>"><?php echo esc_html(t('customer_measurement')); ?></a>
									<a class="btn btn-primary dz-btn--filled" href="<?php echo $order_url; ?>"><?php echo esc_html(t('customer_add_order')); ?></a>
									<a class="btn btn-outline-primary dz-btn--outlined" href="<?php echo $edit_url; ?>"><?php echo esc_html(t('customer_edit')); ?></a>
									<a class="btn btn-outline-primary dz-btn--outlined" href="<?php echo $ledger_url; ?>"><?php echo esc_html(t('customer_ledger_link')); ?></a>
								</div>

								<?php if ($address !== '' || $cnic !== '') { ?>
								<div class="customer-record__meta">
									<?php if ($cnic !== '') { ?>
									<div><span><?php echo esc_html(t('customer_cnic')); ?></span><strong><?php echo esc_html($cnic); ?></strong></div>
									<?php } ?>
									<?php if ($address !== '') { ?>
									<div><span><?php echo esc_html(t('customer_address')); ?></span><strong><?php echo esc_html($address); ?></strong></div>
									<?php } ?>
								</div>
								<?php } ?>

								<section class="customer-record__section" aria-labelledby="customer-open-suits-title">
									<h3 id="customer-open-suits-title" class="customer-record__section-title"><?php echo esc_html(t('customer_open_suits')); ?></h3>
									<?php if (empty($suits)) { ?>
									<p class="text-muted mb-0"><?php echo esc_html(t('customer_open_suits_none')); ?></p>
									<?php } else { ?>
									<div class="table-responsive">
										<table class="table table-sm table-bordered table-striped mb-0">
											<thead>
												<tr>
													<th><?php echo esc_html(t('order_history_order_no')); ?></th>
													<th><?php echo esc_html(t('customer_line_type')); ?></th>
													<th><?php echo esc_html(t('customer_suit')); ?></th>
													<th><?php echo esc_html(t('customer_suit_stage')); ?></th>
													<th><?php echo esc_html(t('order_history_return_date')); ?></th>
													<th><?php echo esc_html(t('app_view')); ?></th>
												</tr>
											</thead>
											<tbody>
											<?php foreach ($suits as $line) {
												$dept = isset($line->stage_dept) ? strtoupper(trim((string) $line->stage_dept)) : '';
												$stage = isset($stage_labels[$dept]) ? $stage_labels[$dept] : ($dept !== '' ? $dept : t('customer_stage_unknown'));
												$status = isset($line->stage_status) ? (string) $line->stage_status : '';
												if ($status !== '' && strcasecmp($status, 'Active') === 0)
												{
													$stage .= ' · '.$status;
												}
												?>
												<tr>
													<td>#<?php echo (int) $line->order_no; ?></td>
													<td><?php echo esc_html((string) $line->line_type); ?></td>
													<td><?php echo esc_html((string) $line->suit_name); ?></td>
													<td><?php echo esc_html($stage); ?></td>
													<td><?php echo ! empty($line->return_date) ? esc_html(date('d-m-Y', strtotime($line->return_date))) : '—'; ?></td>
													<td>
														<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/Order_Print_det/'.(int) $line->order_no); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(t('app_view')); ?></a>
													</td>
												</tr>
											<?php } ?>
											</tbody>
										</table>
									</div>
									<?php } ?>
								</section>

								<section class="customer-record__section" aria-labelledby="customer-order-history-title">
									<h3 id="customer-order-history-title" class="customer-record__section-title"><?php echo esc_html(t('order_history_title')); ?></h3>
									<?php
									$orderdata = $orders;
									$this->load->view('partials/customer_order_history_table', array('orderdata' => $orderdata));
									?>
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
<?php ob_end_flush(); ?>
