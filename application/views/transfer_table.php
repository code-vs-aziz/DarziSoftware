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
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
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
                                            <h5 class="text-center"><?php
											$pending_dept = strtoupper(trim((string) (isset($DEPT) ? $DEPT : $this->uri->segment(3))));
											if ($pending_dept === '') {
												$pending_dept = strtoupper(trim((string) $this->uri->segment(4)));
											}
											echo esc_html(in_array($pending_dept, array('HO', 'CUT', 'HO2', 'STIT'), TRUE)
												? transfer_pending_title($pending_dept)
												: t('transfer_order_heading'));
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
			<table id="example" class="display" style="width:100%" data-transfer-table="1"
				data-ssp="1"
				data-ssp-url="<?php echo esc_attr(isset($transfer_ssp_url) ? $transfer_ssp_url : ''); ?>"
				data-dept="<?php echo esc_attr($DEPT); ?>"
				data-bulk="<?php echo $show_bulk_transfer ? '1' : '0'; ?>"
				data-list-from="<?php echo esc_attr(isset($transfer_list_from) ? $transfer_list_from : (string) $this->uri->segment(3)); ?>">
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
