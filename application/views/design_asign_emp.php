<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
 
  
?>

 
 
<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/select2.full.js"></script>
<script>
$.fn.select2.amd.require([
  'select2/selection/multiple',
  'select2/selection/search',
  'select2/dropdown',
  'select2/dropdown/attachBody',
  'select2/dropdown/closeOnSelect',
  'select2/compat/containerCss',
  'select2/utils'
], function (MultipleSelection, Search, Dropdown, AttachBody, CloseOnSelect, ContainerCss, Utils) {
  var SelectionAdapter = Utils.Decorate(
    MultipleSelection,
    Search
  );
  
  var DropdownAdapter = Utils.Decorate(
    Utils.Decorate(
      Dropdown,
      CloseOnSelect
    ),
    AttachBody
  );
  
  $('.inline-search').select2({
   
    selectionAdapter: SelectionAdapter
  });
  

  

});

</script>
 
 </script>
<body class="animsition erp-app">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Employee/process_rate'); ?>" method="post">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                        <div class="card-title">
                                            <h5 class="text-center"><?php echo t('master_design_assign_form'); ?></h5>
                                            <h6 class="text-right ">Entry # :  <?php   /*
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
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('master_employee_name_phone'); ?></label>
                                              <select 	id="emp-EMPTITLE" name="empno" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  	<?php 
			 foreach($employee as $employee){
									 
						                    ?>
  <option value="<?php  echo $employee->empno; ?>"><?php echo $employee->phone.' '.$employee->name; ?></option>
			 <?php  } ?>
</select>
                                            </div>  
						</div>
						 
					</div>	
 
						<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('master_style_no'); ?></label>
                                                				<select 	id="emp-EMPTITLE" name="styleno" type="text" class="inline-search form-control" aria-required="true" aria-invalid="false">
										
<?php
	 foreach($style as $style){
									 
						                    ?>
  <option value="<?php  echo $style->DNO; ?>"><?php echo $style->vol_name.' '.$style->vol_no; ?></option>

	 <?php  } ?>
												 
											<select>
                                            </div>  
						</div>
						 
					</div>				 
 	
 							<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('master_rate'); ?></label>
                                                <input id="emp-EMPNAME" name="rate"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" re>
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
