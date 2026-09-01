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
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
											
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
						<a href="<?php echo site_url('transfer/order_Status/C') ?> " class="btn btn-primary dz-btn--filled btn-sm" style="width:100px;"><?php echo t('order_status_current'); ?></a> 
						<a href="<?php echo site_url('transfer/order_StatusD/') ?> " class="btn btn-primary dz-btn--filled btn-sm" style="width:100px;"><?php echo t('order_status_delivered_tab'); ?></a>
                                   <div class="card-title">
                                            <h5 class="text-center"><?php echo t('order_status'); ?></h5>
                                        </div>
                                        <hr>
									<?php
									$os_tab = ($this->uri->segment(3) === 'D') ? 'D' : 'C';
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
									$employee_by_no = array();
									if (isset($employee) && is_array($employee)) {
										foreach ($employee as $employees) {
											$employee_by_no[$employees->empno] = $employees->name;
										}
									}
									?>
									<form method="get" class="mb-3" action="<?php echo site_url('transfer/order_Status/'.$os_tab); ?>">
										<div class="row align-items-end">
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Customer</strong></label>
												<input type="text" id="os-filter-customer-name" name="customer_name" class="form-control form-control-sm order-filter-urdu-field customer-urdu-field" dir="rtl" lang="ur" placeholder="<?php echo esc_html(t('customer_name')); ?>" value="<?php echo esc_html($os_customer); ?>" autocomplete="off">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Order #</strong></label>
												<input type="text" name="order_no" class="form-control form-control-sm" placeholder="Order number" value="<?php echo esc_html($os_order_no); ?>" autocomplete="off">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Naap #</strong></label>
												<input type="text" name="naap_no" class="form-control form-control-sm" placeholder="Naap number" value="<?php echo esc_html($os_naap_no); ?>" autocomplete="off">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Mobile #</strong></label>
												<input type="text" name="mobile_no" class="form-control form-control-sm" placeholder="Mobile number" value="<?php echo esc_html($os_mobile_no); ?>" autocomplete="off">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Return From</strong></label>
												<input type="date" name="return_from" class="form-control form-control-sm" value="<?php echo esc_html($os_return_from); ?>">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Return To</strong></label>
												<input type="date" name="return_to" class="form-control form-control-sm" value="<?php echo esc_html($os_return_to); ?>">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Order From</strong></label>
												<input type="date" name="order_from" class="form-control form-control-sm" value="<?php echo esc_html($os_order_from); ?>">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Order To</strong></label>
												<input type="date" name="order_to" class="form-control form-control-sm" value="<?php echo esc_html($os_order_to); ?>">
											</div>
											<div class="col-md-2 mb-2">
												<label class="mb-1"><strong>Stage</strong></label>
												<select name="stage" class="form-control form-control-sm">
													<option value="" <?php echo $os_stage === '' ? 'selected' : ''; ?>>All</option>
													<option value="HO" <?php echo $os_stage === 'HO' ? 'selected' : ''; ?>>Booking</option>
													<option value="CUT" <?php echo $os_stage === 'CUT' ? 'selected' : ''; ?>>Cutting</option>
													<option value="HO2" <?php echo $os_stage === 'HO2' ? 'selected' : ''; ?>>After Cutting</option>
													<option value="STIT" <?php echo $os_stage === 'STIT' ? 'selected' : ''; ?>>Stitching</option>
													<option value="READY" <?php echo $os_stage === 'READY' ? 'selected' : ''; ?>>Ready 4 Deliver</option>
												</select>
											</div>
											<div class="col-md-2 mb-2">
												<button type="submit" class="btn btn-primary dz-btn--filled btn-sm btn-block"><?php echo t('app_search'); ?></button>
												<?php if ($os_customer !== '' || $os_order_no !== '' || $os_naap_no !== '' || $os_mobile_no !== '' || $os_return_from !== '' || $os_return_to !== '' || $os_order_from !== '' || $os_order_to !== '' || $os_stage !== '') { ?>
												<a href="<?php echo site_url('transfer/order_Status/'.$os_tab); ?>" class="btn btn-secondary dz-btn--outlined btn-sm btn-block mt-1"><?php echo t('app_clear'); ?></a>
												<?php } ?>
											</div>
										</div>
									</form>
									<p class="text-muted small text-center mb-2">Use specific fields for faster results: order number, naap, mobile, customer, return date range, or order date range.</p>
									<section>
 
			<div class="demo-html"></div>
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
						<th> Status</th>
						 
						
						 
						 
				 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
				$order = 0 ;
				$sr = 1 ;
			 foreach($orderdata as $orderdata){
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
						                    ?>
					<tr>
						<td><?php echo $orderdata->ORDERNO.'-'.$sr; ?></td>
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
						<td><?php echo $orderdata->Phone; ?></td>
						<td><?php echo $orderdata->name; ?></td>
						<td><?php echo $orderdata->man_napno; ?></td>
						<td><?php echo $orderdata->order_remarks; ?></td>
						
						<td><?php 
						
						 if($this->uri->segment(3) == 'C' or $this->uri->segment(3) == '')
						 {
						  if($orderdata->dept == 'HO')
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
							echo "Ready 4 Deliver";
						}
						 }
						   ?></td>
						 
					 
					 
					</tr>
			 <?php  }  } ?>		
									</tbody>
				 
			</table>
			 
	</section>
          <?php
  /*include('footer.php'); */
 ?>
                     
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

 
</body>

</html>
<!-- end document-->
