<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
 $this->load->view('Header');
	
 
  
?>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                   <div class="card-title">
                                            <h5 class="text-center"><?php echo t('order_status'); ?></h5>
                                        </div>
                                        <hr>
									<?php
									$os_filters = isset($status_filters) && is_array($status_filters) ? $status_filters : array();
									$os_customer = isset($os_filters['customer_name']) ? (string) $os_filters['customer_name'] : '';
									$os_order_no = isset($os_filters['order_no']) ? (string) $os_filters['order_no'] : '';
									$os_naap_no = isset($os_filters['naap_no']) ? (string) $os_filters['naap_no'] : '';
									$os_mobile_no = isset($os_filters['mobile_no']) ? (string) $os_filters['mobile_no'] : '';
									$os_return_from = isset($os_filters['return_from']) ? (string) $os_filters['return_from'] : '';
									$os_return_to = isset($os_filters['return_to']) ? (string) $os_filters['return_to'] : '';
									$os_order_from = isset($os_filters['order_from']) ? (string) $os_filters['order_from'] : '';
									$os_order_to = isset($os_filters['order_to']) ? (string) $os_filters['order_to'] : '';
									$os_stage = isset($os_filters['stage']) ? (string) $os_filters['stage'] : '';
									$os_scope = (isset($os_scope) && (string) $os_scope === 'delivered') ? 'delivered' : 'current';
									$os_is_delivered_scope = ($os_scope === 'delivered');
									$os_via_dt = (isset($_GET['via']) && (string) $_GET['via'] === 'dt');
									$os_dt_q = isset($os_filters['dt_q']) ? (string) $os_filters['dt_q'] : '';
									$os_dt_seed = '';
									if ($os_via_dt) {
										if ($os_dt_q !== '') {
											$os_dt_seed = $os_dt_q;
										} elseif ($os_order_no !== '') {
											$os_dt_seed = $os_order_no;
										} elseif ($os_naap_no !== '') {
											$os_dt_seed = $os_naap_no;
										} else {
											$os_dt_seed = $os_mobile_no;
										}
										// Keep typed value only in the table Search box, not the top filters.
										$os_order_no = '';
										$os_naap_no = '';
										$os_mobile_no = '';
									}
									$employee_by_no = array();
									if (isset($employee) && is_array($employee)) {
										foreach ($employee as $employees) {
											$employee_by_no[$employees->empno] = $employees->name;
										}
									}
									?>
									<style>
										.os-scope-toggle { display: inline-flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
										.os-scope-toggle .btn { min-width: 110px; font-weight: 700; }
										.os-scope-toggle .btn.os-scope-active { box-shadow: 0 0 0 2px rgba(15, 118, 110, .35); }
										.os-scope-toggle .btn:not(.os-scope-active) { opacity: .72; background: #5f9ea8; border-color: #5f9ea8; }
									</style>
									<form method="get" id="os-status-filter-form" class="mb-3" action="<?php echo site_url('transfer/order_Status'); ?>">
										<input type="hidden" name="scope" id="os-scope-input" value="<?php echo esc_attr($os_scope); ?>">
										<input type="hidden" name="dt_q" id="os-dt-q-input" value="<?php echo esc_attr($os_via_dt ? $os_dt_seed : ''); ?>">
										<div class="os-scope-toggle" role="group" aria-label="Order status scope">
											<button type="submit" class="btn btn-primary dz-btn--filled btn-sm<?php echo $os_is_delivered_scope ? '' : ' os-scope-active'; ?>" data-os-scope="current" onclick="document.getElementById('os-scope-input').value='current';"><?php echo esc_html(t('order_status_current')); ?></button>
											<button type="submit" class="btn btn-primary dz-btn--filled btn-sm<?php echo $os_is_delivered_scope ? ' os-scope-active' : ''; ?>" data-os-scope="delivered" onclick="document.getElementById('os-scope-input').value='delivered';"><?php echo esc_html(t('order_status_delivered_tab')); ?></button>
										</div>
										<div class="row align-items-end">
											<div class="col-md-3 mb-2">
												<label class="mb-1"><strong><?php echo $os_is_delivered_scope ? 'Delivered From' : 'Return From'; ?></strong></label>
												<input type="date" name="return_from" class="form-control form-control-sm" value="<?php echo esc_html($os_return_from); ?>">
											</div>
											<div class="col-md-3 mb-2">
												<label class="mb-1"><strong><?php echo $os_is_delivered_scope ? 'Delivered To' : 'Return To'; ?></strong></label>
												<input type="date" name="return_to" class="form-control form-control-sm" value="<?php echo esc_html($os_return_to); ?>">
											</div>
											<?php if ( ! $os_is_delivered_scope) { ?>
											<div class="col-md-3 mb-2">
												<label class="mb-1"><strong>Stage</strong></label>
												<select name="stage" id="os-filter-stage" class="form-control form-control-sm" onchange="this.form.submit()">
													<option value="" <?php echo $os_stage === '' ? 'selected' : ''; ?>>All</option>
													<option value="HO" <?php echo $os_stage === 'HO' ? 'selected' : ''; ?>>Booking</option>
													<option value="CUT" <?php echo $os_stage === 'CUT' ? 'selected' : ''; ?>>Cutting</option>
													<option value="HO2" <?php echo $os_stage === 'HO2' ? 'selected' : ''; ?>>After Cutting</option>
													<option value="STIT" <?php echo $os_stage === 'STIT' ? 'selected' : ''; ?>>Stitching</option>
													<option value="READY" <?php echo $os_stage === 'READY' ? 'selected' : ''; ?>>Ready 4 Deliver</option>
												</select>
											</div>
											<?php } ?>
											<div class="col-md-12 mb-2">
												<button type="submit" class="btn btn-primary dz-btn--filled btn-lg btn-block"><?php echo t('app_search'); ?></button>
											</div>
										</div>
									</form>
									<p class="text-muted small text-center mb-2"><?php echo $os_is_delivered_scope ? esc_html(t('order_status_delivered_help')) : esc_html(t('order_status_unified_help')); ?> Use the table Search box for order #, naap, phone, customer, or employee.</p>
									<section>
 
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-order-list-table="1" data-os-via-dt="<?php echo $os_via_dt ? '1' : '0'; ?>" data-os-dt-seed="<?php echo esc_attr($os_dt_seed); ?>">
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
						<th><?php echo t('order_status_actions'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php 
				$order = 0 ;
				$sr = 1 ;
			 foreach($orderdata as $orderdata){
			 $xfer_status = isset($orderdata->status) ? trim((string) $orderdata->status) : '';
			 // Current tab: never show Delivered (use Delivered tab for those).
			 if ( ! $os_is_delivered_scope && strtoupper($xfer_status) === 'DELIVER')
			 {
				 continue;
			 }
				 if($orderdata->ostatus == 0)
				 {
		if($orderdata->ORDERNO != $order  )
			{
						$order  = $orderdata->ORDERNO;
						$sr = 1;
						
			}
			 else
			 {
				 $sr = $sr + 1;
			 }
			 $is_deactivated = (isset($orderdata->ostatus) && (int) $orderdata->ostatus === 1)
			 	|| $xfer_status === 'CANCEL' || $xfer_status === 'CANCEL_READY';
			 $is_delivered = (strtoupper($xfer_status) === 'DELIVER');
			 // Ready 4 Deliver = stitching complete (STIT + Deactive). Deliver only on those rows.
			 $is_ready = ( ! $is_delivered && ! $is_deactivated && isset($orderdata->dept) && $orderdata->dept === 'STIT' && $xfer_status === 'Deactive');
			 $show_deliver = $is_ready;
						                    ?>
					<tr>
						<td data-search="<?php echo esc_attr($orderdata->ORDERNO.' '.$orderdata->ORDERNO.'-'.$sr); ?>"><?php echo $orderdata->ORDERNO.'-'.$sr; ?></td>
						 <td>
						<?php echo isset($employee_by_no[$orderdata->empno]) ? $employee_by_no[$orderdata->empno] : ''; ?>
						
						
						</td>
						<td><?php
					 
								if($orderdata->dept == 'CUT' or $orderdata->dept == 'STIT')
							 {
								$originalDate =  $orderdata->TRANSFEREDATE;
								
							}
							else{ 
								$originalDate =  $orderdata->ORDERDATE;
							}
						
						 
						$newDate = date("d-m-Y", strtotime($originalDate));
					
					
					
							 

						echo $newDate; ?></td>
						<td><?php
						echo !empty($orderdata->ORDERDATE) ? date('d-m-Y', strtotime($orderdata->ORDERDATE)) : '';
						?></td>
						<td><?php
						echo !empty($orderdata->ORDERRDATE) ? date('d-m-Y', strtotime($orderdata->ORDERRDATE)) : '';
						?></td>
						<td data-search="<?php echo esc_attr(preg_replace('/\D+/', '', (string) $orderdata->Phone).' '.$orderdata->Phone); ?>"><?php echo $orderdata->Phone; ?></td>
						<td><?php echo $orderdata->name; ?></td>
						<td><?php echo $orderdata->man_napno; ?></td>
						<td><?php echo $orderdata->order_remarks; ?></td>
						
						<td><?php
						  if ($is_deactivated)
						  {
							echo '<span class="badge badge-danger">'.esc_html(t('order_status_deactivated')).'</span>';
						  }
						  elseif ($is_delivered)
						  {
							echo '<span class="badge badge-secondary os-badge--delivered">'.esc_html(t('order_status_delivered_tab')).'</span>';
						  }
						  elseif($orderdata->dept == 'HO')
						  {
							echo "Booking";
						  }			
						elseif($orderdata->dept == 'CUT')
						{
							echo "Cutting";
						}
						elseif($orderdata->dept == 'HO2')
						{
							echo "After Cutting";
						}
						elseif($orderdata->dept == 'STIT' and $orderdata->status != 'Deactive')
						{
							echo "Stitching";
						}
							elseif($orderdata->dept == 'STIT' and $orderdata->status == 'Deactive')
						{
							echo '<span class="badge badge-success os-badge--ready">Ready 4 Deliver</span>';
						}
						   ?></td>
						<td class="os-actions-cell">
							<button type="button"
								class="btn btn-sm btn-outline-primary dz-btn--outlined btn-os-payment"
								data-order-id="<?php echo (int) $orderdata->ORDERNO; ?>"
								data-customer="<?php echo esc_attr($orderdata->name); ?>">
								<?php echo t('order_status_payment'); ?>
							</button>
							<?php if ($show_deliver) { ?>
							<button type="button"
								class="btn btn-sm btn-success dz-btn--filled btn-os-deliver ml-1"
								data-order-id="<?php echo (int) $orderdata->ORDERNO; ?>"
								data-customer="<?php echo esc_attr($orderdata->name); ?>">
								<?php echo t('order_status_deliver'); ?>
							</button>
							<?php } ?>
						</td>
					 
					</tr>
			 <?php  }  } ?>		
									</tbody>
				 
			</table>
			 
	</section>

<div class="modal fade" id="osDeliverModal" tabindex="-1" role="dialog" aria-labelledby="osDeliverModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<form id="os-deliver-form" method="post" action="#">
				<?php $this->load->view('partials/csrf_field'); ?>
				<input type="hidden" name="redirect_to" value="transfer/order_Status">
				<input type="hidden" name="customer_code" id="os-customer-code" value="">
				<input type="hidden" name="total_Amount" id="os-total-amount" value="">
				<input type="hidden" name="Advance" id="os-advance" value="">
				<input type="hidden" name="Discount" id="os-discount" value="">
				<input type="hidden" name="retrun_Date" id="os-return-date" value="">
				<div id="os-line-fields"></div>

				<div class="modal-header">
					<div>
						<h5 class="modal-title mb-0" id="osDeliverModalTitle"><?php echo esc_html(t('order_status_deliver_title')); ?></h5>
						<small class="text-muted" id="os-modal-subtitle"></small>
					</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div id="os-modal-loading" class="text-center py-4 text-muted"><?php echo esc_html(t('app_loading')); ?>…</div>
					<div id="os-modal-error" class="alert alert-danger" hidden></div>
					<div id="os-modal-body" hidden>
						<div class="os-summary-grid">
							<div class="os-summary-card">
								<span><?php echo esc_html(t('order_total_amount')); ?></span>
								<strong id="os-m-total">0</strong>
							</div>
							<div class="os-summary-card">
								<span><?php echo esc_html(t('order_status_booking_advance')); ?></span>
								<strong id="os-m-booking-adv">0</strong>
							</div>
							<div class="os-summary-card">
								<span><?php echo esc_html(t('order_status_advance_total')); ?></span>
								<strong id="os-m-advance-total">0</strong>
							</div>
							<div class="os-summary-card">
								<span><?php echo esc_html(t('app_discount')); ?></span>
								<strong id="os-m-discount">0</strong>
							</div>
							<div class="os-summary-card">
								<span id="os-lbl-order-remaining"><?php echo esc_html(t('order_status_order_remaining')); ?></span>
								<strong id="os-m-order-remaining">0</strong>
							</div>
							<div class="os-summary-card os-summary-card--warn">
								<span id="os-lbl-prev"><?php echo esc_html(t('order_status_previous_balance')); ?></span>
								<strong id="os-m-prev">0</strong>
							</div>
							<div class="os-summary-card os-summary-card--due">
								<span id="os-lbl-due"><?php echo esc_html(t('order_status_amount_due')); ?></span>
								<strong id="os-m-due">0</strong>
								<small id="os-due-hint" class="os-due-hint text-muted"></small>
							</div>
						</div>

						<h6 class="mt-3 mb-2"><?php echo esc_html(t('order_status_suits')); ?></h6>
						<div class="table-responsive">
							<table class="table table-sm table-bordered mb-3" id="os-suits-table">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo esc_html(t('order_suit_type')); ?></th>
										<th><?php echo esc_html(t('order_design_type')); ?></th>
										<th><?php echo esc_html(t('order_status_suit_price')); ?></th>
										<th><?php echo esc_html(t('order_status')); ?></th>
										<th class="os-deliver-col"><?php echo esc_html(t('order_delivered')); ?></th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>

						<div class="row os-collect-row" id="os-collect-row" hidden>
							<div class="col-md-4 mb-2">
								<label for="os-collect-now"><strong><?php echo esc_html(t('order_status_collect_now')); ?></strong></label>
								<input type="number" step="0.01" min="0" class="form-control" name="Balance" id="os-collect-now" value="0">
							</div>
							<div class="col-md-4 mb-2">
								<label for="os-paid-date"><strong><?php echo esc_html(t('order_paid_date')); ?></strong></label>
								<input type="date" class="form-control" name="paid_Date" id="os-paid-date" value="<?php echo esc_attr(date('Y-m-d')); ?>">
							</div>
							<div class="col-md-4 mb-2">
								<label for="os-udaar"><strong><?php echo esc_html(t('order_udaar')); ?></strong></label>
								<select class="form-control" name="udaar" id="os-udaar">
									<option value="NO">NO</option>
									<option value="YES">YES</option>
								</select>
							</div>
						</div>

						<h6 class="mt-3 mb-2"><?php echo esc_html(t('order_status_history')); ?></h6>
						<div class="table-responsive">
							<table class="table table-sm table-striped mb-0" id="os-history-table">
								<thead>
									<tr>
										<th><?php echo esc_html(t('app_date')); ?></th>
										<th><?php echo esc_html(t('order_status_history_detail')); ?></th>
										<th><?php echo esc_html(t('app_advance')); ?></th>
										<th><?php echo esc_html(t('app_balance')); ?></th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-outline-secondary" id="os-open-full" data-no-ajax="true" target="_blank" rel="noopener"><?php echo esc_html(t('order_status_open_full')); ?></a>
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo esc_html(t('app_cancel')); ?></button>
					<button type="submit" class="btn btn-success" id="os-submit-deliver" hidden><?php echo esc_html(t('order_status_confirm_deliver')); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
.os-actions-cell { white-space: nowrap; min-width: 150px; }
.os-summary-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
	gap: 0.65rem;
}
.os-summary-card {
	background: #f8fafc;
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	padding: 0.65rem 0.75rem;
}
.os-summary-card span {
	display: block;
	font-size: 0.75rem;
	color: #64748b;
	margin-bottom: 0.2rem;
}
.os-summary-card strong {
	font-size: 1.05rem;
	color: #0f172a;
}
.os-summary-card--warn { background: #fff7ed; border-color: #fed7aa; }
.os-summary-card--due { background: #ecfdf5; border-color: #a7f3d0; }
.os-summary-card--due strong { color: #047857; }
.os-due-hint { display: block; margin-top: 4px; font-size: 11px; line-height: 1.3; }
#osDeliverModal .modal-body { max-height: 70vh; overflow-y: auto; }
.os-deliver-col { width: 90px; }
</style>

<script>
window.ORDER_STATUS_DELIVER = <?php echo json_encode(array(
	'summaryUrl' => site_url('Order/delivery_summary_json'),
	'deliverUrlBase' => rtrim(site_url('Order/process_del'), '/').'/',
	'labels' => array(
		'loading' => t('app_loading'),
		'load_fail' => t('order_status_load_fail'),
		'deliver_title' => t('order_status_deliver_title'),
		'payment_title' => t('order_status_payment_title'),
		'confirm' => t('order_status_deliver_confirm'),
		'no_ready' => t('order_status_no_ready'),
		'order_remaining' => t('order_status_order_remaining'),
		'previous_balance' => t('order_status_previous_balance'),
		'amount_due' => t('order_status_amount_due'),
		'due_breakdown' => t('order_status_due_breakdown'),
		'yes' => 'YES',
		'no' => 'NO',
	),
), JSON_UNESCAPED_UNICODE); ?>;
</script>
<script>if (window.jQuery) { jQuery(document).trigger('order-status:ready'); }</script>

                    </div><!-- card-body -->
                </div><!-- card -->
            </div><!-- col-md-12 -->
                    </div><!-- container-fluid -->
                </div>
            <!-- END MAIN CONTENT-->
            </div><!-- main-content -->
        </div><!-- page-wrapper -->

</body>

</html>
<!-- end document-->
