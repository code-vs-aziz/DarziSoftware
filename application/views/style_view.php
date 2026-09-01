<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
 $this->load->view('Header');
 
  
?>
<body class="erp-app dt-example">
    <div class="page-wrapper">
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
											<?php echo t('master_style_created'); ?>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Style/Style_add'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add New Style</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
					 
						<th>Volume Name</th>
						<th>Volume No</th>
						<th>Design</th>
						<th>Rate</th>
						<th>Edit</th>
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($style as $style){
									 
						                    ?>
					<tr>
 
						<td><?php echo $style->vol_name; ?></td>
						<td><?php echo $style->vol_no; ?></td>
						<td><?php echo $style->design; ?></td>
						<td><?php echo $style->rate; ?></td>
						<td>  <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Style/style_edit/'.$style->DNO.''); ?>" style="    float: right;
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
