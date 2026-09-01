<?php
	ob_Start();
 
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
 	<script>
	$(function() {
			$('#Name').setUrduInput();
			$('#City').setUrduInput();
			$('#Remarks').setUrduInput();
			$('#Country').setUrduInput();
			$('#Address').setUrduInput();
			///$('#q').focus();
		});
		
		
 
	</script>
<body class="animsition erp-app">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Customer/process'); ?>" method="post">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php  
	// print_r( $data);

 
						if($this->uri->segment(3) == 'err') { ?>
					 
						<div class="sufee-alert alert with-close alert-danger alert-dismissible fade show"  
  
 id="email_error">
											<span class="badge badge-pill badge-danger">Error</span>
											Naap No already Exist
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
											
											</button>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
						
                                <div class="dz-card erp-module-card data-entry-card">
                                  


								  <!-- <div class="card-header">Employee Form</div> -->
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('customer_form'); ?></h2>
										<span class="erp-entry-number"><?php echo t('customer_number'); ?> : <?php   /*
                                            $query_last = "select TRNNO from emp order by TRNNO desc limit 1";
                                            $result_last=mysql_query($query_last);
                                            $row_last=mysql_fetch_object($result_last);
                                            echo $row_last->TRNNO;
                                            */
                                            ?></span>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
                              <div   id="page1" style="display:block;">
           
			 
 				
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('customer_name'); ?></label>
                                                <input id="Name" name="Name"   required type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
 
											
											</div>  
						</div>
						 	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('customer_manual_naap'); ?></label>
                                                <input   name="man_napno"  required  type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php  echo $max_naap+1; ?>" >
                                            </div>  
						</div>
					</div>	
	<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('customer_phone'); ?></label>
                                                <input id="emp-EMPNAME" name="Phone"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                            </div>  
						</div>
						<div class="col">	
					<div class="form-group">
                                                <label for="emp-middle_name" class="control-label mb-1"><?php echo t('customer_cnic'); ?></label>
                                                <input id="emp-middle_name" name="CNIC" type="text" class="form-control" aria-required="true" aria-invalid="false" value="">
                                            </div>    
						</div>
					</div>	
				 
                                                <input id="City" name="City"   type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                                <input id="Country" name="Country" type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="">
                 
<div class="row">
			<div class="col">	
					<div class="form-group">
                                                <label for="emp-type" class="control-label mb-1"><?php echo t('customer_type'); ?></label>
                                              	<select 	id="emp-type" name="type" type="text" class="form-control" aria-required="true" aria-invalid="false">
												<option><?php echo t('customer_type_local'); ?></option>
												<option><?php echo t('customer_type_foreign'); ?></option>
												
												 
											<select>	
                                            </div>    
						</div>
						<div class="col">		
                                            <!-- <div class="form-group">
                                                <label for="emp-EDOB" class="control-label mb-1">Date of Brith</label>
                                                <input id="emp-EDOB" name="DOB"   type="date" class="form-control" aria-required="true" aria-invalid="false" placeholder="dd-mm-yyyy" value="">
                                            </div> -->  
						</div>
				 
					</div>						
<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
                                                <input id="Address" name="Address"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                            </div>  
						</div>
		 
					</div>		
				                               <input id="Remarks" name="Remarks"   type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                	 			<div class="row data-entry-actions">
												<div class="col">
											 
											
											</div>
												<div class="col">
                                               <!-- <button id="submit-button" type="submit" name="savBtn" onclick="return valid()"  class="btn btn-lg btn-info btn-block">-->
                                                <button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
	
                                                    <span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
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
