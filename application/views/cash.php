<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
	
 
 
  
?>
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
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', sans serif;
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
<body class="animsition erp-app erp-form-page">
    <div class="page-wrapper">
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
						<div class="alert alert-success cash-flash-alert" role="alert">
											Cash Payment/Receipt Genrated
										</div>
						<?php  } ?>				
						<div id="cash-entry-alert" class="alert" role="alert" style="display:none;"></div>
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card cash-entry-card">
                                   <!-- <div class="card-header">Employee Form</div> -->
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('cash_title'); ?></h2>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
						<form id="cash-entry-form" name="Form" action="<?php echo site_url('Cash/process'); ?>" method="post" data-no-ajax="true" data-total-wages-url="<?php echo site_url('Cash/Total_wages'); ?>">
                              <div   id="page1" style="display:block;">
           
			 
 				
					<div class="row align-items-end">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('cash_type'); ?></label>
                                                <select class="form-control" name="type" aria-required="true" aria-invalid="false" required>
													
													<option value="1">Payment</option>
													<option value="2">Receipt</option>
													<option value="3">Shop Expense</option>
													<option value="4">House Expense</option>
													<option value="5">Nasir sb Expense</option>
													<option value="6">Extra Expense</option>
													<option value="7">Salary</option>
												</select>
                                            </div>  
						</div>
						 <div class="col">		
						 <div class="form-group">
						 <label for="employee" class="control-label mb-1"><?php echo t('cash_employee'); ?></label>
						 <?php
						 $emp_status_filter = isset($emp_status_filter) ? (string) $emp_status_filter : 'Active';
						 if (!in_array($emp_status_filter, array('All', 'Active', 'Inactive'), TRUE))
						 {
							 $emp_status_filter = 'Active';
						 }
						 ?>
						 <select id="cash_emp_status_filter" class="form-control form-control-sm mb-2" aria-label="<?php echo esc_html(t('report_employee_status')); ?>" onchange="window.location='<?php echo site_url('Cash'); ?>?emp_status='+encodeURIComponent(this.value);">
							<option value="Active"<?php echo $emp_status_filter === 'Active' ? ' selected' : ''; ?>><?php echo t('master_employee_status_active'); ?></option>
							<option value="Inactive"<?php echo $emp_status_filter === 'Inactive' ? ' selected' : ''; ?>><?php echo t('master_employee_status_inactive'); ?></option>
							<option value="All"<?php echo $emp_status_filter === 'All' ? ' selected' : ''; ?>><?php echo t('report_employee_status_all'); ?></option>
						 </select>
                                           <select id="employee" name="Employee" class="form-control" type="text" aria-required="true" aria-invalid="false">
  <option value="">Select Employee</option>
 
 <?php 
			 
			 foreach ($employee as $emp_row) {
						                    ?>
  <option value="<?php echo (int) $emp_row->empno; ?>"><?php echo esc_html($emp_row->name.'  '.$emp_row->phone); ?></option>
			 <?php  } ?>
</select>
						</div>
					</div>	
					
							 <div class="col">		
						 <div class="form-group">
						 <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('cash_customer'); ?></label>
                                           <select 	 id="Customer"   name="Customer" onchange="get_order_number()" class="form-control" type="text" aria-required="true" aria-invalid="false">
  <option value="">Select Customer</option>
 
 <?php 
			 
			 foreach($customer as $customer){
									 
						                    ?>
  <option value="<?php  echo $customer->code; ?>" ><?php echo $customer->man_napno. $customer->name.'  ' ; ?></option>
			 <?php  } ?>
</select>
<a href="<?php echo site_url('Cash/reprtcustomer_ledger'); ?>" class="btn btn-primary dz-btn--filled btn-sm mt-2"  target="_blank" >
  Customer Ledger
</a>
<br>
<div class="form-group" id="order_window" style="display:none">
                                                <label for="emp-middle_name" class="control-label mb-1"><?php echo t('cash_order_number'); ?></label>
                                                <input id="emp-middle_name"  name="order_number" type="text" class="form-control" aria-required="true" aria-invalid="false" value="">
                                            </div>   

						</div>
					</div>				
					</div>	
	<div class="row">
				 
					 
								<div class="col">	
					<div class="form-group">
                                                <label for="emp-middle_name" class="control-label mb-1"><?php echo t('cash_paid_receipt'); ?></label>
                                                <input id="emp-middle_name" required name="paid" type="text" class="form-control" aria-required="true" aria-invalid="false" value="">
                                            </div>    
						</div>
					</div>	
				 
 	
 
 	
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
                                                <input id="Remarks" name="Remarks"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                            </div>  
						</div>
		 
					</div>	
					<div class="row cash-form-actions">
												<div class="col">
											 <div>
                                                <button id="cash-back-button" type="button" onclick="window.history.back()"   class="btn btn-lg btn-secondary btn-block">
	
                                                    <span><?php echo t('app_back'); ?></span>
                                                </button>
                                            </div>
												
											
											</div>
												<div class="col">
                                               <!-- <button id="submit-button" type="submit" name="savBtn" onclick="return valid()"  class="btn btn-lg btn-info btn-block">-->
                                                <button id="submit-button" type="submit" name="savBtn"    class="btn btn-lg btn-primary btn-block">
	
                                                    <span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>
                                            </div>
										 
										
</div>
</div>
						</form>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
<script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Jquery JS-->
    <script src="<?php echo base_url(); ?>vendor/slick/slick.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/wow/wow.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/animsition/animsition.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
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
