<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
 $this->load->view('Header');
	
 
  
?>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <div class="transfer-table-page">
            <!-- MAIN CONTENT-->
            <div class="main-content transfer-table-page">
                <div  >
                    <div class="container-fluid" style="padding-left:0px !important">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
											
										</div>
						<?php  } ?>
						<?php if ($this->session->flashdata('transfer_error')) { ?>
						<div class="alert alert-danger" role="alert">
							<?php echo esc_html($this->session->flashdata('transfer_error')); ?>
						</div>
						<?php } ?>
						<?php if ($this->session->flashdata('bulk_transfer_count') !== FALSE && $this->session->flashdata('bulk_transfer_count') !== NULL) { ?>
						<div class="alert alert-success" role="alert">
							<?php echo sprintf(t('transfer_bulk_transfer_done'), (int) $this->session->flashdata('bulk_transfer_count')); ?>
						</div>
						<?php } ?>
                     	<div class="col-md-14">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                    <div class="card-title">
                                            <h5 class="text-center"><?php echo t('transfer_order_heading'); ?> <?php  
											if($this->uri->segment(3) == 'HO' or $this->uri->segment(4) == 'HO')
											{
													echo 'Booking';
											}
											elseif($this->uri->segment(3) == 'CUT' or $this->uri->segment(4) == 'CUT')
											{
													echo 'Cutting';
											}
											elseif($this->uri->segment(3) == 'HO2' or $this->uri->segment(4) == 'HO2')
											{
													echo 'HO2';
											}
											elseif($this->uri->segment(3) == 'STIT' or $this->uri->segment(4) == 'STIT')
											{
													echo 'Stitching';
											}
											   ?></h5>
                                        </div>
                                        <hr>
									<section>
			<?php $show_bulk_transfer = ($DEPT === 'CUT' || $DEPT === 'STIT'); ?>
			<?php if ($show_bulk_transfer) { ?>
			<form id="bulk-transfer-form" method="post" action="<?php echo site_url('Transfer/transfer_process_bulk'); ?>" data-no-ajax="true" style="margin-bottom:10px;" class="form-inline"
				data-bulk-enabled="1"
				data-confirm-msg="<?php echo esc_html($DEPT === 'STIT' ? t('transfer_bulk_transfer_confirm_stit') : t('transfer_bulk_transfer_confirm')); ?>"
				data-none-msg="<?php echo esc_html(t('transfer_bulk_transfer_none')); ?>">
				<input type="hidden" name="dept" value="<?php echo esc_html($DEPT); ?>">
				<input type="hidden" name="list_from" value="<?php echo esc_html($this->uri->segment(3)); ?>">
				<input type="hidden" name="transfer_items" id="transfer_items_json" value="">
				<button type="button" id="bulk-transfer-btn" class="btn btn-success dz-btn--filled btn-sm mb-2"><?php echo t('transfer_bulk_transfer'); ?></button>
			</form>
			<?php } ?>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-transfer-table="1">
			<thead>
					<tr>
						<?php if ($show_bulk_transfer) { ?><th><input type="checkbox" id="select-all-transfer" title="<?php echo esc_html(t('transfer_bulk_transfer_select_page')); ?>"></th><?php } ?>
						<th>Order #</th>
						<th>Employee</th>
						<th>Entry Date</th>
						<th>Return Date</th>
						<th>Phone</th>
						<th>Customer</th>
						<th>Naap No</th>
						<th>Remarks</th>
						
						<th> View</th>
						<th> Transfer</th>
						<th> Return</th>
						 
						 
				 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
				$order = 0 ;
				$sr = 1 ;
			 foreach($orderdata as $orderdata){
			$line_sr = isset($orderdata->ORDERSR) ? (int) $orderdata->ORDERSR : (isset($orderdata->SUITNO) ? (int) $orderdata->SUITNO : 0);
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
						<?php if ($show_bulk_transfer) {
						?>
						<td>
							<input type="checkbox" class="bulk-transfer-cb"
								data-trnno="<?php echo (int) $orderdata->TRANSFERID; ?>"
								data-order="<?php echo (int) $orderdata->ORDERNO; ?>"
								data-sr="<?php echo $line_sr; ?>">
						</td>
						<?php } ?>
						<td><?php echo $orderdata->ORDERNO.'-'.$sr; ?></td>
						
						<td>
						<?php 
						foreach($employee as $employees){
						
						
						if($employees->empno == $orderdata->empno )
						{
							echo $employees->name; 
						}
						}
					//	echo $orderdata->employee;
						?>
						
						
						</td>
						<td><?php
						$originalDate = $orderdata->ORDERDATE;
						if ($originalDate === '' || $originalDate === NULL)
						{
							$originalDate = $orderdata->TRANSFEREDATE;
						}
						echo ($originalDate !== '' && $originalDate !== NULL) ? date('d-m-Y', strtotime($originalDate)) : '';
						?></td>
						<td><?php
						$originalDate1 = $orderdata->RORDERDATE;
						if ($originalDate1 === '' || $originalDate1 === NULL)
						{
							$originalDate1 = $orderdata->RETURNDATE;
						}
						echo ($originalDate1 !== '' && $originalDate1 !== NULL) ? date('d-m-Y', strtotime($originalDate1)) : '';
						?></td>
						<td><?php echo $orderdata->Phone; ?></td>
						<td><?php echo $orderdata->name; ?></td>
						<td><?php echo $orderdata->man_napno; ?></td>
						<td><?php echo $orderdata->order_remarks; ?></td>
						
						<td>
						<?php if($DEPT == 'CUT' or $DEPT == 'STIT' )  {?>
						<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Transfer/transfer_Print/'.$orderdata->TRANSFERID.'/'.$DEPT.'/'.$line_sr); ?>" target="_blank"  >View</a>
						<?php  }  ?>
						</td>
						
						<td><a class="btn btn-primary dz-btn--filled btn-sm"  href="<?php echo site_url('Transfer/transfer_order/'.$orderdata->TRANSFERID.'/'.$DEPT.'/'.$line_sr); ?>"   >Transfer</a></td>
						<td>
						<?php
							if($DEPT != 'HO' )
							{
						?>
						
						<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Transfer/transfer_return/'.$orderdata->TRANSFERID.'/'.$DEPT.'/'.$orderdata->ORDERNO.'/'.$line_sr.'/'.$this->uri->segment(3)); ?>"   >Return</a>
						
							<?php  }  ?>
						</td>
				 
				 
					 
					</tr>
				 <?php  } ?>		
									</tbody>
				 
			</table>
			 
	</section>
                      </div>
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
