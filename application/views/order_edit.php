<?php
	ob_Start();
$this->load->helper('ui');
	
	$this->load->view('Head');
	$this->load->view('Header');
 
  
?>
<body class="animsition erp-app">
<div class="page-wrapper">
 <style>
	.urd {
		font-weight: bold;
		font-family: 'Noto Nastaliq Urdu Draft', 'Jameel Noori Nastaleeq', serif;
	}
	.txtbox {
		direction: rtl;
		text-align: right;
	}
	.order-predefined-tags {
		direction: rtl;
		text-align: right;
		font-weight: bold;
		line-height: 1.8;
		margin: 8px 0 10px;
		width: 100%;
		clear: both;
	}
	.order-predefined-tags .order-predefined-row { margin-bottom: 4px; }
	.order-predefined-tags .order-predefined-tag {
		display: inline;
		background: none;
		border: none;
		padding: 0;
		margin: 0;
		font: inherit;
		font-weight: bold;
		color: inherit;
		cursor: pointer;
	}
	.order-predefined-tags .order-predefined-tag:hover { text-decoration: underline; }
	.order-remark-group { direction: rtl; text-align: right; width: 100%; }
	.order-remark-group label { display: block; text-align: right; width: 100%; }
	.order-copy-bar {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: 6px;
		margin: 4px 0 8px;
	}
	.order-copy-bar .order-copy-label {
		font-weight: bold;
		white-space: nowrap;
	}
	.order-copy-bar .order-copy-from {
		width: auto;
		min-width: 56px;
		display: inline-block;
	}
	.page-wrapper .main-content {
		display: block !important;
		visibility: visible !important;
		min-height: 60vh;
	}
 </style>
<?php
	$order_mst_row = isset($order_mst) ? $order_mst : NULL;
	if ( ! $order_mst_row && ! empty($Measurement_data) && is_array($Measurement_data))
	{
		$order_mst_row = $Measurement_data[0];
	}
	if ( ! is_array($order_data))
	{
		$order_data = array();
	}
	$edit_trnno = isset($edit_trnno) ? (int) $edit_trnno : (int) $this->uri->segment(3);
?>
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						<?php if ($this->session->flashdata('suss') == 'yes') { ?>
						<div class="alert alert-success" role="alert"><?php echo sprintf(t('app_record_entered'), $edit_trnno); ?></div>
						<?php } elseif ($this->session->flashdata('suss') == 'incomplete') { ?>
						<div class="alert alert-danger" role="alert"><?php echo t('order_edit_save_failed'); ?></div>
						<?php } ?>
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card">
                                    <div class="erp-card-head">
                                            <h2 class="erp-module-title mb-0"><?php echo t('order_edit_form'); ?></h2>
                                            <span class="erp-entry-number">Order # : <?php echo $edit_trnno; ?></span>
                                        </div>
                                    <div class="card-body erp-card-body data-entry-body">
<?php if ( ! $order_mst_row) { ?>
						<div class="alert alert-danger"><?php echo sprintf(t('order_edit_not_found'), $edit_trnno); ?></div>
<?php } else { ?>
						<form name="Form" action="<?php echo site_url('Order/process_edit/'.$edit_trnno); ?>" method="post" data-no-ajax="true">
                              <div id="page1" style="display:block;">
           
			 				
 				
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_naap_no'); ?></label>
 
           
            <input id="customer_name" name="Naap12no"   readonly="readonly" type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->man_napno; ?>"  >                                  
            <input id="customer_name" name="Naapno"   readonly="readonly" type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->MCODE; ?>"  >                                </div>  
						</div>
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_customer_name'); ?></label>
										 
  <input id="customer_name" name="customer_name"  disabled type="text" class="form-control urd" dir="rtl" lang="ur" aria-required="true" aria-invalid="false" value="<?php echo esc_html((string) $order_mst_row->name); ?>"  >
  <input id="customer_NO" name="customer_NO"  readonly="readonly" type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->Customerno; ?>"  >
                                            </div>  
						</div>
						 
					</div>	
			 
				<div class="row">
									<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_current_date'); ?></label>
  <input id="current" name="current"   type="date" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $order_mst_row->EDATE; ?>" re>
                                            </div>  
						</div>
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_return_date'); ?></label>
  <input id="retrun_Date" name="retrun_Date"   type="date" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->RDATE; ?>" >
                                            </div>  
						</div>
														<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_quantity'); ?></label>
  <input id="Quantity" name="Quantity"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->qty; ?>" >
                                            </div>  
						</div>
						 
					</div>	
					
				 
					 
									<?php  
					 $i = 0;
					  $SR = 1;
					  $edit_qty = (int) $order_mst_row->qty;
			 foreach($order_data as $order_line){
				 
					?>
					<div class="order-suit-line" data-line="<?php echo $i; ?>">
					<input type="hidden" name="sr_no[]" value="<?php echo $order_line->trnno; ?>" >
<?php
	$remark_field = 'Remarks'.$i;
	$this->load->view('partials/order_predefined_tags', array(
		'remark_field' => $remark_field,
		'order_predefined_by_row' => isset($order_predefined_by_row) ? $order_predefined_by_row : array(),
	));
?>
					<div class="row">
			
<div class="col">		
                                          <a href="<?php echo site_url('Order/order_line_delete/'.$edit_trnno.'/'.$order_line->trnno); ?>" data-no-ajax="true" class="btn btn-primary dz-btn--filled btn-sm" style="    width: 75px;
    height: 50px;"><span style ="    margin-top: 10px;
    float: left;
    margin-left: 6px;"><?php echo t('app_delete'); ?></span></a>
						</div>
						</div>
					<div class="row">
						<div class="col-12">
                                             <div class="form-group order-remark-group">
                                                <label for="Remarks<?php echo $i ?>" class="control-label mb-1 urd">نوٹ لائن</label>
                                                <input id="Remarks<?php echo $i ?>" name="Remarks[]" type="text" class="form-control urd txtbox" dir="rtl" lang="ur" aria-required="true" aria-invalid="false" value="<?php echo esc_html($order_line->order_remarks); ?>">
                                            </div>
						</div>
						</div>
						
							<div class="row">
					 	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_front_pocket'); ?></label>
                                              <select 	id="fpocket<?php echo $i; ?>"   name="fpocket[]"  type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
 
  <option value="1" <?php if($order_line->fpocket == '1') {   ?> selected <?php  } ?>>1</option>
  <option value=" بغیر فلاپ 2" <?php if($order_line->fpocket == ' بغیر فلاپ 2') {   ?> selected <?php  } ?>> 2بغیر فلاپ </option>
  <option value="2  فلاپ والی" <?php if($order_line->fpocket == '2  فلاپ والی') {   ?> selected <?php  } ?>>2فلاپ والی </option>
 
			 
</select>

		</div>  
						</div>	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_side_pocket'); ?></label>
                                              <select 	id="spocket<?php echo $i; ?>"   name="spocket[]" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
  <option value="1" <?php if($order_line->spocket == '1') {   ?> selected <?php  } ?> >1</option>
  <option value="2" <?php if($order_line->spocket == '2') {   ?> selected <?php  } ?> >2</option>
 
   
</select>

		</div>  
						</div>	
	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_shalwar_pocket'); ?></label>
                                              <select 	id="shawpocket<?php echo $i; ?>"   name="shawpocket[]" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
  <option value="1" <?php if($order_line->Shawlarpocket == '1') {   ?> selected <?php  } ?>>1</option>
  <option value="آگے پیچے 2 " <?php if($order_line->Shawlarpocket == 'آگے پیچے 2') {   ?> selected <?php  } ?>>آگے پیچے 2 </option>
  <option value="1 سائیڈ پاکٹ والی" <?php if($order_line->Shawlarpocket == '1 سائیڈ پاکٹ والی') {   ?> selected <?php  } ?>>سائیڈ پاکٹ والی 1</option>
  <option value="1 کاج بٹن والی " <?php if($order_line->Shawlarpocket == '1 کاج بٹن والی ') {   ?> selected <?php  } ?>>کاج بٹن والی1  </option>
  <option value="1 فکس پاکٹ" <?php if($order_line->Shawlarpocket == '1 فکس پاکٹ') {   ?> selected <?php  } ?>>فکس پاکٹ1</option>
  <option value="فرنٹ پر2" <?php if($order_line->Shawlarpocket == 'فرنٹ پر2') {   ?> selected <?php  } ?>>فرنٹ پر2    </option>
</select>

		</div>  
						</div>							
						</div>	
						
											<div class="row order-line-header-row">
			
<div class="col"  >	
				                                             <label for="emp-EMPNAME" class="control-label mb-1" style="    float: right;
    border: 1px solid black;
    border-radius: 29px;
    width: 29px;
    text-align: center;" ><?php
	echo $SR;
	$SR = $SR + 1;
	?></label>
						</div>
						<div class="col">
<?php
	$this->load->view('partials/order_copy_line_bar', array(
		'line_index' => $i,
		'qty' => $edit_qty,
	));
?>
						</div>
						</div>
						
										<div class="row">
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_design_type'); ?></label>
                                              <select 	id="Designtype"    name="desigtype[]" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="0"><?php echo t('order_select_design_type'); ?></option>
  <option value="Kurta" <?php if($order_line->type == 'Kurta')  { ?> selected  <?php }  ?> >قمیض شلوار</option>
  <option value="Pajama" <?php if($order_line->type == 'Pajama')  { ?> selected  <?php }  ?> >پاجامہ</option>
  <option value="Pent" <?php if($order_line->type == 'Pent')  { ?> selected  <?php }  ?> >پینٹ</option>
  <option value="Shirt" <?php if($order_line->type == 'Shirt')  { ?> selected  <?php }  ?> >شرٹ</option>
  <option value="Coat" <?php if($order_line->type == 'Coat')  { ?> selected  <?php }  ?> >شروانی/کوٹ</option>
  <option value="Waistcoat" <?php if($order_line->type == 'Waistcoat')  { ?> selected  <?php }  ?> >ویسکوٹ</option>
 
 
</select>
<span  style="float:left;"> <input style="float:left; width:75px;" onchange="get_id11('add<?php echo $i; ?>','itotal<?php echo $i; ?>')" id="add<?php echo $i; ?>" name="add[]"    type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_line->addprice; ?>"  >     &nbsp;  +  &nbsp;</span>
  <span> <input style=" width:75px;" id="itotal<?php echo $i; ?>" name="itotal<?php echo $i; ?>"  readonly="readonly"  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo (float) $order_line->suitrate + (float) $order_line->addprice; ?>"  >        
  
                                            </div>  
						</div>
							<div class="col">		
                                             <div class="form-group" >
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_suit_type'); ?></label>
                                              <select 	id="suit<?php echo $i; ?>"   name="suit[]" data-initial-sno="<?php echo (int) $order_line->SNO; ?>" onchange="get_id2('suit<?php echo $i; ?>','srate<?php echo $i; ?>')" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="0"><?php echo t('order_select_suit'); ?></option>
 
 <?php 
			 
			 foreach($suit as $suits){
									 
						                    ?>
  <option value="<?php  echo $suits->SNO; ?>" <?php if($order_line->SNO == $suits->SNO)  { ?> selected  <?php }  ?> ><?php echo $suits->name; ?></option>
			 <?php  } ?>
</select>
<input id="srate<?php echo $i; ?>" name="ssrate[]" type="text" class="form-control order-line-rate" readonly="readonly" aria-required="true" aria-invalid="false" value="<?php echo (float) $order_line->suitrate; ?>" >        
		</div>  
						</div>
					<input type="hidden" name="designo[]" value="0">
					<input type="hidden" name="Embroidery[]" value="0">
					<input type="hidden" id="drate<?php echo $i; ?>" name="ddrate[]" value="0">
					<input type="hidden" id="erate<?php echo $i; ?>" name="eerate[]" value="0">
		
					</div>	
					
					
					
					
					
												
					
					
					
					
					
					
					
					
					
					
					
								<div class="row">
					 	<div class="col">		
                                             <div class="form-group">
                                                
       <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="1" <?php if($order_line->kolor_style == 1 )  { ?> checked="checked"  <?php }  ?> >  <image src="<?php echo base_url(); ?>/images/1.jpg" style="height:100px;">
	                          </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"   <?php if($order_line->kolor_style == 2 )  { ?> checked="checked"  <?php }  ?> type="radio" class="form-control" aria-required="true" aria-invalid="false" value="2"  ><image src="<?php echo base_url(); ?>/images/2.jpg" style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"  <?php if($order_line->kolor_style == 3 )  { ?> checked="checked"  <?php }  ?> type="radio" class="form-control" aria-required="true" aria-invalid="false" value="3"  ><image src="<?php echo base_url(); ?>/images/3.jpg" style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>" <?php if($order_line->kolor_style == 4 )  { ?> checked="checked"  <?php }  ?>  type="radio" class="form-control" aria-required="true" aria-invalid="false" value="4"  ><image src="<?php echo base_url(); ?>/images/4.jpg" style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"  <?php if($order_line->kolor_style == 5 )  { ?> checked="checked"  <?php }  ?>  type="radio" class="form-control" aria-required="true" aria-invalid="false" value="5"  ><image src="<?php echo base_url(); ?>/images/5.jpg" style="height:100px;">
</div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group"   for="kolorstyle"  > 
	  <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"  <?php if($order_line->kolor_style == 6 )  { ?> checked="checked"  <?php }  ?>  type="radio" class="form-control" aria-required="true" aria-invalid="false" value="6"  ><image src="<?php echo base_url(); ?>/images/6.jpg" style="height:100px;">
                                            </div>  
						</div>
						<div class="col">		
                                             <div class="form-group"   for="kolorstyle"  > 
	  <input id="kolorstyle<?php echo $i ?>" name="kolorstyle<?php echo $i ?>"  <?php if($order_line->kolor_style == 7 )  { ?> checked="checked"  <?php }  ?>  type="radio" class="form-control" aria-required="true" aria-invalid="false" value="7"  ><image src="<?php echo base_url(); ?>/images/7.jpg" style="height:100px;">
                                            </div>  
						</div>
						</div>
			<hr style="border: 2px solid #CCC;">
					</div>
							<?php  
							$i = $i + 1; 
							}  ?>
							<div class="row">
									<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_total_amount'); ?></label>
  <input id="total_amount" name="total_amount"    type="text" onchange="bal()" readonly="readonly" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->total; ?>" >
                                            </div>  
						</div>
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_advance'); ?></label>
  <input id="Advance" name="Advance"   type="text" class="form-control" onchange="bal()" aria-required="true" aria-invalid="false" value="<?php echo   $order_mst_row->advance; ?>" >
                                            </div>  
						</div>
													<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_discount'); ?></label>
  <input id="Discount" name="Discount"   type="text" class="form-control" onchange="bal()" aria-required="true" aria-invalid="false" value="<?php echo isset($order_mst_row->Discount) ? $order_mst_row->Discount : (isset($order_mst_row->discount) ? $order_mst_row->discount : 0); ?>" >
                                            </div>  
						</div>
														<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_balance'); ?></label>
<?php
	$edit_discount = isset($order_mst_row->Discount) ? (float) $order_mst_row->Discount : (isset($order_mst_row->discount) ? (float) $order_mst_row->discount : 0);
	$edit_balance = (float) $order_mst_row->total - (float) $order_mst_row->advance - $edit_discount;
?>
  <input id="Balance" name="Balance"  disabled  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $edit_balance; ?>" >
                                            </div>  
						</div>
						 
					</div>		  			
							<div class="row data-entry-actions order-payment-actions">
								<div class="col-md-4">
									<a href="<?php echo site_url('Order/Order_Print_det/'.$edit_trnno); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true" target="_blank" rel="noopener noreferrer"><?php echo t('app_view'); ?></a>
								</div>
								<div class="col-md-4">
									<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
										<span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
										<span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
									</button>
								</div>
								<div class="col-md-4"></div>
							</div>
</div>
						</form>
<?php } ?>
                                    </div>
                                </div>
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
<?php $this->load->view('partials/order_edit_head_scripts'); ?>
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
		if ($('#datetimepicker1').length) {
			$('#datetimepicker1').datetimepicker();
		}
		if (!window.jQuery || !jQuery.fn.setUrduInput) {
			return;
		}
		var qtyEl = document.getElementById('Quantity');
		if (!qtyEl) {
			return;
		}
		var qty = parseInt(qtyEl.value, 10) || 0;
		if (document.getElementById('Remarks0')) {
			$('#Remarks0').setUrduInput();
		}
		for (var i = 0; i < qty; i++) {
			var remarks = '#Remarks' + i;
			if ($(remarks).length) {
				$(remarks).setUrduInput();
			}
		}
	});
 </script>
<?php $this->load->view('partials/order_copy_suit_line_js'); ?>
</body>

</html>
<!-- end document-->
