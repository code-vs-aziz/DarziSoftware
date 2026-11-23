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
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Suit/Suit_add'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add Suit Type</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
					 
						<th>Name</th>
						 
						<th>Rate</th>
						<th><?php echo t('master_employee_status'); ?></th>
						<th>Edit</th>
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($suit as $suit){
				$active = ! isset($suit->is_active) || (int) $suit->is_active === 1;
						                    ?>
					<tr>
 
						<td><?php echo $suit->name; ?></td>
						 
						<td><?php echo $suit->rate; ?></td>
						<td>
							<span class="badge badge-<?php echo $active ? 'success' : 'secondary'; ?>">
								<?php echo $active ? t('master_employee_status_active') : t('master_employee_status_inactive'); ?>
							</span>
						</td>
						<td class="text-right">
							<a class="btn btn-sm btn-outline-<?php echo $active ? 'warning' : 'success'; ?>"
							   href="<?php echo site_url('Suit/toggle_active/'.(int) $suit->SNO); ?>"
							   data-no-ajax="true"
							   onclick="return confirm('<?php echo $active ? t('master_confirm_inactive') : t('master_confirm_active'); ?>');">
								<?php echo $active ? t('master_employee_status_inactive') : t('master_employee_status_active'); ?>
							</a>
							<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Suit/suit_edit/'.$suit->SNO.''); ?>"> Edit</a>
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
