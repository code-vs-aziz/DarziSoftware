<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
 
  
?>

 
 <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <style>
	.urd{
			font-weight:bold;
			    font-family: 'Noto Nastaliq Urdu Draft', serif;
				float: right;
	}
	.txtbox{
		direction: rtl;
	}
 </style>
<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/select2.full.js"></script>
<script>
 
 
 



 
</script>
 
 <style>
 
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', sans serif;;
	}
	#Name,#City,#Remarks,#Address,#Country {
		direction: rtl;
		border: 1px solid #ccc;
		vertical-align: middle;
		direction: rtl;
		padding: 2px;
		font-size: 16px;
		font-weight:bold;
	}
	#kb {
		width:500px;
	}
	a {
		color:#4ea0e8;
		text-decoration:none;
	}
	</style>
<script>
	function valid()
	{
		if (!window.confirm('Are you sure you want to submit this delivery?')) {
			return false;
		}
		var submitButton = document.getElementById('delivery-submit-button');
		if (submitButton && submitButton.disabled) {
			return false;
		}
		if (submitButton) {
			submitButton.disabled = true;
			document.getElementById('delivery-submit-text').innerHTML = 'Saving...';
		}
		return true;
	}
</script>

<body class="animsition erp-app erp-form-page">
        <form name="Form" action="<?php echo site_url('Order/process_del/'.$this->uri->segment(3).''); ?>" method="post" onsubmit="return valid()">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('order_delivered_success'))); ?>				
                     	<div class="col-md-12">
                                        <div class="dz-card erp-module-card data-entry-card delivery-entry-card">
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('order_ready_deliver'); ?></h2>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
									<section>
           
			 							  	<?php 
			 foreach($order_single_data as $order_single_data1){
									 
			
						                    ?>
											<?php }  ?>	
 				
					<?php
					$deliver_order_no = '';
					if (isset($order_single_data1->order_id) && (string) $order_single_data1->order_id !== '') {
						$deliver_order_no = (string) $order_single_data1->order_id;
					} elseif ($this->uri->segment(3)) {
						$deliver_order_no = (string) $this->uri->segment(3);
					}
					?>
					<div class="row">
						<div class="col-md-3 col-6">
							<div class="form-group">
								<label for="deliver-naap-no" class="control-label mb-1"><?php echo t('order_naap_no'); ?></label>
								<input id="deliver-naap-no" name="Naapno" readonly="readonly" type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo esc_attr(isset($order_single_data1->man_napno) ? $order_single_data1->man_napno : ''); ?>">
							</div>
						</div>
						<div class="col-md-3 col-6">
							<div class="form-group">
								<label for="deliver-order-no" class="control-label mb-1"><?php echo t('order_order_no'); ?></label>
								<input id="deliver-order-no" name="OrderNo" readonly="readonly" type="text" class="form-control" value="<?php echo esc_attr($deliver_order_no); ?>">
							</div>
						</div>
						<div class="col-md-6 col-12">
							<div class="form-group">
								<label for="deliver-customer-name" class="control-label mb-1"><?php echo t('order_customer_name'); ?></label>
								<input id="deliver-customer-name" name="customer_name" disabled type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo esc_attr(isset($order_single_data1->name) ? $order_single_data1->name : ''); ?>">
							</div>
							<input type="hidden" value="<?php echo esc_attr(isset($order_single_data1->CUSTOMERCODE) ? $order_single_data1->CUSTOMERCODE : ''); ?>" name="customer_code">
						</div>
					</div>
			 		
				<div class="row">
			
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_return_date'); ?></label>
  <input id="retrun_Date" name="retrun_Date" readonly type="text" class="form-control dz-date" aria-required="true" aria-invalid="false" value="<?php echo esc_attr(date('d-M-Y', strtotime($order_single_data1->RDATE))); ?>" >
  
                                            </div>  
						</div>
														<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_quantity'); ?></label>
  <input id="retrun_Date" name="retrun_Date" readonly  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_single_data1->qty; ?>" >
  
                                            </div>  
						</div>
											<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_total_amount'); ?></label>
  <input id="retrun_Date" name="total_Amount" readonly  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_single_data1->total; ?>" >
  
                                            </div>  
						</div>		
							<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_advance'); ?></label>
  <input id="retrun_Date" name="Advance" readonly  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_single_data1->advance + $TOTRECED + $TOTCASHRECEVIED; ?>" >
  
                                            </div>  
						</div>	
					<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_discount'); ?></label>
  <input id="retrun_Date" name="Discount" readonly  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_single_data1->discount; ?>" >
  
                                            </div>  
						</div>	
						
				<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_balance'); ?></label>
  <input id="Balance" name="Balance"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_single_data1->total - $order_single_data1->advance - $order_single_data1->discount - $TOTRECED - $TOTCASHRECEVIED ; ?>" >
  
                                            </div>  
						</div>							
						</div>
						 			<div class="row">
			
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_paid_date'); ?></label>
  <input id="paid_Date" name="paid_Date"   type="date" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   date('Y-m-d');//$order_single_data1->RDATE; ?>" >
  
                                            </div>  
						</div>
					 <div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_udaar'); ?></label>
  	<select name="udaar"  class="form-control">
													<option value="NO">NO</option>
													<option value="YES">YES</option>
												
												</select>
  
                                            </div>  
						</div>
														<div class="col">		
                                             <div class="form-group">
                                         
  
                                            </div>  
						</div>
						</div>
				 
   
				 				  	<?php 
								$SR = 1;
			 foreach($order_single_data as $order_single_datas){
									 
			 
						                    ?>
										
		 

			 		
				<div class="row">
			
				 <input type="hidden" name="order_sr[]" value="<?php echo $order_single_datas->ORDERSR; ?>">
								<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('transfer_kolar_style'); ?></label>
												<image class="form-control" style="    width: 128px;
    height: 128px;" src="<?php echo base_url(); ?>/images/<?php echo $order_single_datas->kolor_style; ?>.jpg" >
                                            </div>  
						</div> 
		<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="form-group" class="control-label mb-1"><?php echo t('order_delivered'); ?></label>
												<select name="returndel[]"  class="form-control">
													<option value="yes">YES</option>
													<option value="no">NO</option>
												
												</select>
                                            </div>  
						</div> 
								<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1" style="    float: right;
    border: 1px solid black;
    border-radius: 29px;
    width: 29px;
    text-align: center;" ><?php
	echo $SR;
	$SR = $SR + 1;
	?></label>
												 
                                            </div>  
						</div> 
						</div>
						 
					 	
   
				 
								<div class="row">
																					<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_design_type'); ?></label>
											
											   <label for="emp-EMPNAME" class="form-control"><?php    echo $order_single_datas->type;  ?></label>
									 

		</div>  
						</div>
								<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_suit_type'); ?></label>
											<?php  
											 
											 foreach($suit as $suits){
												 
											 if($suits->SNO == $order_single_datas->SNO)
											 {
											?>
											   <label for="emp-EMPNAME" class="form-control"><?php   echo $suits->name;  ?></label>
										<?php 	
											 }
										}
											
											?>

		</div>  
						</div>
							<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_design_no'); ?></label>
											<?php  
											 
											 foreach($style as $styles){
												 
											 if($styles->DNO == $order_single_datas->design)
											 {
											?>
											   <label for="emp-EMPNAME" class="form-control"><?php   echo $styles->vol_name.'  '. $styles->vol_no.' '.$styles->design;  ?></label>
										<?php 	
											 }
										}
											
											?>

		</div>  
						</div>
												<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_embroidery'); ?></label>
											<?php  
											 
											 foreach($embroidery as $embroiderya){
												 
											 if($embroiderya->ENO == $order_single_datas->ENO)
											 {
											?>
											   <label for="emp-EMPNAME" class="form-control"><?php    echo $embroiderya->embroidery_name;  ?></label>
										<?php 	
											 }
										}
											
											?>

		</div>  
					 
						</div>
						</div>
						
					 
					 
				
						
												<div class="row">
					 	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_front_pocket'); ?></label>
                                               <label for="emp-EMPNAME" class="form-control"><?php    echo $order_single_datas->fpocket;  ?></label>

		</div>  
						</div>	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_side_pocket'); ?></label>
												 <label for="emp-EMPNAME" class="form-control"><?php    echo $order_single_datas->spocket;  ?></label>

		</div>  
						</div>	
	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_shalwar_pocket'); ?></label>
                                               <label for="emp-EMPNAME" class="form-control"><?php    echo $order_single_datas->Shawlarpocket;  ?></label>

		</div>  
						</div>							
						</div>				
			
						
						
									<div class="row">
					 	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">نوٹ لائن</label>
												
                                                <input id="Remarks" name="Remarks[]" disabled   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php    echo $order_single_datas->order_remarks;  ?>"  >
                                            </div>  
						</div>
						</div>
					 
						 
					 
							<?php }  ?>
					 				<div class="row data-entry-actions">
												<div class="col">
											 <div>
                                                <a id="delivery-back-button" type="button" href="<?php echo site_url('Order/order_issue'); ?>"   class="btn btn-lg btn-secondary btn-block">
	
                                                    <span><?php echo t('app_back'); ?></span>
                                                </a>
                                            </div>
												
											
											</div>
												<div class="col">
                                               <!-- <button id="submit-button" type="submit" name="savBtn" onclick="return valid()"  class="btn btn-lg btn-info btn-block">-->
                                                <button id="delivery-submit-button" type="submit" name="savBtn"    class="btn btn-lg btn-primary btn-block">
	
                                                    <span id="delivery-submit-text">Submit</span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>
                                            </div>
										 
										
</section>
                      </form>
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

<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("emp-EMPTITLE");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
</script>
    <!-- Jquery JS-->
    <script src="<?php echo base_url(); ?>vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="<?php echo base_url(); ?>vendor/slick/slick.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/wow/wow.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/animsition/animsition.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/circle-progress/circle-progress.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?php echo base_url(); ?>vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="<?php echo base_url(); ?>js/main.js"></script>
 <script type="text/javascript">
            $(function () {
                $('#datetimepicker1').datetimepicker();
            });
        </script>
</body>

</html>
<!-- end document-->
