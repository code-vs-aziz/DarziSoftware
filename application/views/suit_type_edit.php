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
	#suitName,#Remarks{
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
			$('#suitName').setUrduInput();
			$('#Remarks').setUrduInput();
	 
			///$('#q').focus();
		});
	</script>
<body class="animsition erp-app">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
		
					<?php 
			 foreach($suit as $suit){
			 }
						                    ?>
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php if ($this->session->flashdata('suss') == 'yes') { ?>
						<div class="alert alert-success" role="alert"><?php echo t('app_record_entered_short'); ?></div>
						<?php } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
						<form name="Form" action="<?php echo site_url('Suit/process_edit/'.$suit->SNO.''); ?>" method="post" data-no-ajax="true">
                                        <div class="card-title">
                                            <h5 class="text-center"><?php echo t('master_suit_form'); ?></h5>
                                            <h6 class="text-right ">Suit # :  <?php   /*
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
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('master_name'); ?></label>
                                                <input id="suitName" name="Name"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $suit->name; ?>" re>
                                            </div>  
						</div>
						  
					 
						<div class="col">	
					<div class="form-group">
                                                <label for="emp-middle_name" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
                                                <input id="emp-middle_name" name="Rate" type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $suit->rate; ?>">
                                            </div>    
						</div>
					</div>	
				 
 	
 					
 	
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_remarks'); ?></label>
                                                <input id="Remarks" name="Remarks"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $suit->remarks; ?>">
                                            </div>  
						</div>
		 
					</div>	
					 			<div class="row">
												<div class="col">
											 <div>
                                               <A id="submit-button" type="button"  href="<?php echo site_url('Suit'); ?>"  class="btn btn-lg btn-info btn-block">
	
                                                    <span id="submit-button-amount">Back</span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </a>
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
