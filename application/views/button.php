<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
	
 /* include('include/conn.php');
  include('head.php');
  include('header.php');
 
  if(isset($_REQUEST['savBtn']))
  { 
	
	$query="insert into emp(EDATE,CMPCODE,EMPTITLE,EMPNAME,whallow,vexpdate,SURNAME,middle_name,MSTATUS,EHOUSENO,EADDRESS1,EADDRESS2,ETOWN,ECOUNTRY,EPOSTCODE,EHPHNO,EMOBNO,EEMAIL,
	EDOB,ENATIONALIY,ENINO,EEMRGCONTACTNO,EEMRGCONTACTNAME,EEMRGCONTACTRELATION,ECRIMINAL,ECRIMINALDTL,EBANK,EBBRANCH,EBACCNAME,EBSORTCODE,EBACCNO,
	EBROLLNO,EMEDICALACK,county,EMPSTATUS) values('".date('Y-m-d')."','".$_REQUEST['emp-Branch']."','".$_REQUEST['emp-EMPTITLE']."','".$_REQUEST['emp-EMPNAME']."','".$_REQUEST['emp-whallow']."','".$_REQUEST['emp-vexpdate']."','".$_REQUEST['emp-SURNAME']."','".$_REQUEST['emp-middle_name']."'
	,'".$_REQUEST['emp-MSTATUS']."','".$_REQUEST['emp-EHOUSENO']."','".$_REQUEST['emp-EADDRESS1']."','".$_REQUEST['emp-EADDRESS2']."'
	,'".$_REQUEST['emp-ETOWN']."','".$_REQUEST['emp-ECOUNTRY']."','".$_REQUEST['emp-EPOSTCODE']."','".$_REQUEST['emp-EHPHNO']."','".$_REQUEST['emp-EMOBNO']."'
	,'".$_REQUEST['emp-EEMAIL']."','".$_REQUEST['emp-EDOB']."','".$_REQUEST['emp-ENATIONALIY']."','".$_REQUEST['emp-ENINO']."','".$_REQUEST['emp-EEMRGCONTACTNAME']."'
	,'".$_REQUEST['emp-EEMRGCONTACTNAME']."','".$_REQUEST['emp-EEMRGCONTACTRELATION']."','".$_REQUEST['emp-ECRIMINAL']."','".$_REQUEST['emp-ECRIMINALDTL']."'
	,'".$_REQUEST['emp-EBANK']."','".$_REQUEST['emp-EBBRANCH']."','".$_REQUEST['emp-EBACCNAME']."','".$_REQUEST['emp-EBSORTCODE']."','".$_REQUEST['emp-EBACCNO']."'
	,'".$_REQUEST['emp-EBROLLNO']."','".$_REQUEST['emp-EMEDICALACK']."','".$_REQUEST['emp-county']."','PENDING')";
	mysql_query($query) or die(mysql_error());
	 
	header("location: employee.php?err=100");
  
  }
 */
  
?>
 
<body class="animsition erp-app">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Button/process'); ?>" method="post">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
						<div class="sufee-alert alert with-close alert-danger alert-dismissible fade show"  
  
 id="email_error">
											<span class="badge badge-pill badge-danger">Error</span>
											<?php //echo($suss); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                        <div class="card-title">
                                            <h5 class="text-center"><?php echo t('master_button_form'); ?></h5>
                                            <h6 class="text-right ">Button # :  <?php   /*
                                            $query_last = "select TRNNO from emp order by TRNNO desc limit 1";
                                            $result_last=mysql_query($query_last);
                                            $row_last=mysql_fetch_object($result_last);
                                            echo $row_last->TRNNO;
                                            */
                                            ?> </h6>
                                        </div>
                                        <hr>
                              <div   id="page1" style="display:block;">
           
			 
 				
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('master_button_name'); ?></label>
                                                <input id="emp-EMPNAME" name="Name"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                            </div>  
						</div>
						 
					</div>	
 
				 
 	
 					
 	
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
                                                <input id="emp-EMPNAME" name="Remarks"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
                                            </div>  
						</div>
		 
					</div>	
					 			<div class="row">
												<div class="col">
											 <div>
                                                <button id="submit-button" type="button" onclick="back()"   class="btn btn-lg btn-info btn-block">
	
                                                    <span id="submit-button-amount">Back</span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>
                                            </div>
												
											
											</div>
												<div class="col">
                                               <!-- <button id="submit-button" type="submit" name="savBtn" onclick="return valid()"  class="btn btn-lg btn-info btn-block">-->
                                                <button id="submit-button" type="submit" name="savBtn"    class="btn btn-lg btn-info btn-block">
	
                                                    <span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>
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
