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
						 
						<?php $this->load->view('partials/success_alert', array('success_default' => t('master_employee_created'))); ?>				
                     	<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                  
                                        <hr>
									<section>
<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Employee/Employee_add'); ?>" style="    float: right;
    margin-bottom: 3px; color: white;">Add New Employee</a>
			<div class="demo-html"></div>
			<table id="example" class="display" style="width:100%" data-master-list="1">
			<thead>
					<tr>
						<th>Employee No</th>
						<th>  Name</th>
						<th><?php echo t('master_employee_type'); ?></th>
						<th><?php echo t('master_employee_status'); ?></th>
						<th> Phone</th>
						<th> CNIC</th>
						<th> 	Addres</th>
						<th> 	Emergency Phone</th>
						<th> 	Edit</th>
				 
					 
					</tr>
				</thead>
				<tbody>
				<?php 
			 foreach($employee as $employee){
									 
						                    ?>
					<tr>
						<td><?php echo $employee->empno; ?></td>
						<td><?php echo $employee->name; ?></td>
						<td><?php echo esc_html(isset($employee->type) ? $employee->type : ''); ?></td>
						<td><?php
						$st = (isset($employee->status) && $employee->status === 'Inactive') ? t('master_employee_status_inactive') : t('master_employee_status_active');
						echo esc_html($st);
						?></td>
						<td><?php echo $employee->phone; ?></td>
						<td><?php echo $employee->nic; ?></td>
						<td><?php echo $employee->addres; ?></td>
						<td><?php echo $employee->emergencyphone	; ?></td>
				 <td>  <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Employee/employee_edit/'.$employee->empno.''); ?>" style="    float: right;
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
