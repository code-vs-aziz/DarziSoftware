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
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>				
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
						<th><?php echo t('master_employee_status'); ?></th>
						<th>Edit</th>
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($style as $style){
				$active = ! isset($style->is_active) || (int) $style->is_active === 1;
						                    ?>
					<tr>
 
						<td><?php echo $style->vol_name; ?></td>
						<td><?php echo $style->vol_no; ?></td>
						<td><?php echo $style->design; ?></td>
						<td><?php echo $style->rate; ?></td>
						<td>
							<span class="badge badge-<?php echo $active ? 'success' : 'secondary'; ?>">
								<?php echo $active ? t('master_employee_status_active') : t('master_employee_status_inactive'); ?>
							</span>
						</td>
						<td class="text-right">
							<a class="btn btn-sm btn-outline-<?php echo $active ? 'warning' : 'success'; ?>"
							   href="<?php echo site_url('Style/toggle_active/'.(int) $style->DNO); ?>"
							   data-no-ajax="true"
							   onclick="return confirm('<?php echo $active ? t('master_confirm_inactive') : t('master_confirm_active'); ?>');">
								<?php echo $active ? t('master_employee_status_inactive') : t('master_employee_status_active'); ?>
							</a>
							<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Style/style_edit/'.$style->DNO.''); ?>"> Edit</a>
						</td>
					 
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
