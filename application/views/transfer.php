<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
 $this->load->view('Header');
	
 
  
?>
<script>
$(document).ready(function(){

    $('#fromdept').on('change',function(){
			 
        var qty = $(this).val();
        if(qty){
			
            $.ajax({
                type:'POST',
                url:'<?php echo site_url('transfer/transfer_table'); ?>',
                data:'qty='+qty,
                success:function(html){
                    $('#maincat').html(html);
                    
                   // $('#subcat').html('<option value="">Select Category first</option>'); 
                }
            }); 
        }else{
           // $('#maincat').html('<option value="">Select Category first</option>');
         //   $('#subcat').html('<option value="">Select Category first</option>'); 
        }
    });
	
});

</script>
<head>
    <!-- Required meta tags-->
  

    <!-- Title Page-->
    <title> </title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/jquery.dataTables.css">
 
	<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/jquery.dataTables.js"></script>
	  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:300i" rel="stylesheet">
    <!-- Fontfaces CSS-->
   

</head>
	<script type="text/javascript" language="javascript" class="init">
	
$(document).ready(function() {
	$('#example').DataTable( {
		"pagingType": "full_numbers"
	} );
} );

	</script>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Transfer/transfer_table'); ?>" method="post">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
										 
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
										<section>
														<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('transfer_from_dept'); ?></label>
                                              <select 	id="fromdept"  name="fromdept" type="text" class="form-control inline-search" aria-required="true" aria-invalid="false">
  <option value="">Select Dept</option>
 
  <option value="HO" >HO to Cutting</option>
  <option value="CUT" >Cutting to HO</option>
<option value="HO2" >HO to Stitching</option> 
 <option value="STIT" >Stitching to HO</option> 
 

			 
</select>
                                            </div>  
						</div>
			 
						 
					</div>	
	 
      <button id="submit-button" type="submit" name="savBtn"    class="btn btn-lg btn-info btn-block">
	
                                                    <span id="submit-button-amount">Genrate List</span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>				
										</section>
										
									<section>
									<hr>

			 
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

 
</body>

</html>
<!-- end document-->
