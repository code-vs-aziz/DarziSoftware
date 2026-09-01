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
											<?php echo t('master_employee_created'); ?>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Measurement'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add New Measurement</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
						<th>Measurement Code</th>
						<th>Entry Date</th>
						<th>Customer</th>
						<th> Remarks</th>
						<th> View</th>
						 
						 
				 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($measurement as $measurement){
									 
						                    ?>
					<tr>
						<td><?php echo $measurement->code; ?></td>
						<td><?php echo $measurement->edate; ?></td>
						<td><?php echo $measurement->name; ?></td>
						<td><?php echo $measurement->remarks; ?></td>
						<td><a href="<?php echo site_url('Measurement/measurement_view/'.$measurement->code.''); ?>" style="color:white;"class="btn btn-outline-primary btn-lg">View</a></td>
				 
				 
					 
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

 
</body>

</html>
<!-- end document-->
