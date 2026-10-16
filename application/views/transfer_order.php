<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
 
  
?>

 
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
<body class="animsition erp-app erp-form-page">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form id="transfer-order-form" name="Form" action="<?php echo site_url('Transfer/process/'.$this->uri->segment(3).'/'.$this->uri->segment(4).'/'.$this->uri->segment(5).''); ?>" method="post" target="_self" data-no-ajax="true">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 <div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
											<span class="badge badge-pill badge-success">Success</span>
											<?php echo sprintf(t('app_record_entered'), $last_id); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php  } ?>				
						<?php   if($this->session->flashdata('transfer_error')) { ?>
					 <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
											<span class="badge badge-pill badge-danger">Error</span>
											<?php echo esc_html($this->session->flashdata('transfer_error')); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php  } ?>
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card transfer-entry-card">
                                   <!-- <div class="card-header">Employee Form</div> -->
									<div class="erp-card-head">
										<?php
											$transfer_from = isset($from_dept) ? strtoupper(trim((string) $from_dept)) : strtoupper(trim((string) $this->uri->segment(4)));
											$transfer_to = transfer_next_stage($transfer_from);
											$transfer_flow = in_array($transfer_from, array('HO', 'CUT', 'HO2', 'STIT'), TRUE)
												? transfer_flow_title($transfer_from)
												: t('transfer_order_form');
										?>
										<h2 class="erp-module-title mb-0"><?php echo esc_html($transfer_flow); ?></h2>
										<?php if (in_array($transfer_from, array('HO', 'CUT', 'HO2', 'STIT'), TRUE)) { ?>
										<p class="text-muted mb-0 mt-1" style="font-size:13px;">
											<?php echo esc_html(t('transfer_from_dept')); ?>:
											<strong><?php echo esc_html(transfer_stage_label($transfer_from)); ?></strong>
											&nbsp;→&nbsp;
											<?php echo esc_html(t('transfer_to_dept')); ?>:
											<strong><?php echo esc_html($transfer_to !== '' ? transfer_stage_label($transfer_to) : t('transfer_stage_ready')); ?></strong>
										</p>
										<?php } ?>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
                              <div   id="page1" style="display:block;">
           
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_naap_no'); ?></label>
 
           
            <input id="customer_name" name="Naapno"   readonly="readonly" type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php
			echo   $Measurement_data->man_napno; ?>"  >                                </div>  
						</div>
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_customer_name'); ?></label>
										 
  <input id="customer_name" name="customer_name"  disabled type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $Measurement_data->name; ?>"  >
  
                                            </div>  
						</div>
						 
					</div>	
			 		
				<div class="row">
			
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_return_date'); ?></label>
  <input id="retrun_Date" name="retrun_Date" disabled  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php 
  $originalDate1 =  $Measurement_data->RORDERDATE;
						
						 
						$newDate1 = date("d-m-Y", strtotime($originalDate1));
						echo $newDate1;
  
   ?>" >
  <input id="order_id" name="order_id" readonly="readonly"  type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $Measurement_data->ORDERNO; ?>" >
  <input id="order_sr" name="order_sr" readonly="readonly"  type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $Measurement_data->SUITNO; ?>" >
  <input id="DEPT" name="DEPT" readonly="readonly"  type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo   $this->uri->segment(4); ?>" >
                                            </div>  
						</div>
								<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('transfer_kolar_style'); ?></label>
												<image class="form-control" style="    width: 128px;
    height: 128px;" src="<?php echo base_url(); ?>/images/<?php echo $Measurement_data->kolor_style; ?>.jpg" >
                                            </div>  
						</div> 
						</div>
						 
					</div>	
   
				 
								<div class="row">
																					<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_design_type'); ?></label>
										 
											   <label for="emp-EMPNAME" class="form-control"><?php    echo $Measurement_data->type;  ?></label>
									 

		</div>  
						</div>
								<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_suit_type'); ?></label>
											   <label for="emp-EMPNAME" class="form-control"><?php echo esc_html(isset($Measurement_data->suit_name) ? $Measurement_data->suit_name : ''); ?></label>

		</div>  
						</div>
						</div>
												<div class="row">
					 	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_front_pocket'); ?></label>
                                               <label for="emp-EMPNAME" class="form-control"><?php    echo $Measurement_data->fpocket;  ?></label>

		</div>  
						</div>	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_side_pocket'); ?></label>
												 <label for="emp-EMPNAME" class="form-control"><?php    echo $Measurement_data->spocket;  ?></label>

		</div>  
						</div>	
	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_shalwar_pocket'); ?></label>
                                               <label for="emp-EMPNAME" class="form-control"><?php    echo $Measurement_data->Shawlarpocket;  ?></label>

		</div>  
						</div>							
						</div>				
			
						
						
									<div class="row">
					 	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">نوٹ لائن</label>
												
                                                <input id="Remarks" name="Remarks[]" disabled   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php    echo $Measurement_data->order_remarks;  ?>"  >
                                            </div>  
						</div>
						</div>
						<?php 
							$from_dept = isset($from_dept) ? strtoupper((string) $from_dept) : strtoupper((string) $this->uri->segment(4));
							$transfer_employees = isset($transfer_employees) && is_array($transfer_employees) ? $transfer_employees : array();
							if ($from_dept === 'HO' || $from_dept === 'HO2')
							{
						?>
										<div class="row">
							<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('transfer_employee'); ?></label>
                                              <select name="EMPLOYEE" required class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value=""><?php echo t('transfer_select_employee_option'); ?></option>
 
 <?php 
			 foreach ($transfer_employees as $emp_row) { ?>
	 <option value="<?php echo (int) $emp_row->empno; ?>"><?php echo esc_html($emp_row->name).'  '.esc_html($emp_row->phone); ?></option>
			 <?php } ?>
</select>

		</div>  
						</div>
								 	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1 ">Return Date</label>
												
                                                <input id="RDATE" name="RDATE" required   type="date" class="form-control" aria-required="true" aria-invalid="false" value="<?php
												$date = date('Y-m-d');
												 
												echo $date;
												?>"  >
                                            </div>  
						</div>
							 	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1 ">Rate</label>
												
                                                <input id="RATE" name="Rate" required   type="text" class="form-control" aria-required="true" aria-invalid="false" value=""  >
                                            </div>  
						</div>
						</div>
							<?php }  ?>
                	 			<div class="row data-entry-actions">
												<div class="col">
											 
											
											</div>
												<div class="col">
                                               <!-- Keep this as a plain submit button; transfer flow must navigate normally. -->
                                                <button id="transfer-submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
                                                    Submit
                                                </button>
                                            </div>
										 <div class="col">
											 
											
											</div>
										
</div>
</div>

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
