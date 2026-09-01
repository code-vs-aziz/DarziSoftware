<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
 $this->load->view('Header');
	
 
  
?>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
											<?php echo t('master_button_created'); ?>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Button/Button_add'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add New Button</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
						<th>Button No</th>
						<th>Button Name</th>
				 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($Button as $Button){
									 
						                    ?>
					<tr>
						<td><?php echo $Button->bcode; ?></td>
						<td><?php echo $Button->bname; ?></td>
				 
					 
					</tr>
			 <?php  } ?>		
									</tbody>
				 
			</table>
			 
	</section>
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

 <script src="<?php echo base_url(); ?>js/main.js"></script>
</body>

</html>
<!-- end document-->
