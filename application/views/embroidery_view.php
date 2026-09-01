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
											<?php echo t('master_embroidery_created'); ?>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Embroidery/embroidery_add'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add New Embroidery</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
						 
						<th>Embroidery  Name</th>
						<th>Embroidery  Vol. #</th>
						<th>Embroidery  Design</th>
						<th>Rate</th>
						<th>Edit</th>
					 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($Embroidery as $Embroidery){
									 
						                    ?>
					<tr>
						
						<td><?php echo $Embroidery->embroidery_name; ?></td>
						<td><?php echo $Embroidery->vol_no; ?></td>
						<td><?php echo $Embroidery->design; ?></td>
						<td><?php echo $Embroidery->rate; ?></td>
						<td>  <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Embroidery/Embroidery_edit/'.$Embroidery->ENO.''); ?>" style="    float: right;
    margin-bottom: 3px; color: white;"> Edit</a></td>
						 
					 
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
