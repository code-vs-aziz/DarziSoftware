<?php
	ob_Start();
$this->load->helper('ui');
 
	$this->load->view('Head');
	$this->load->view('Header');
	$report_can = array();
	$report_permissions = array(
		'report.financial_dashboard',
		'report.sales_expense',
		'report.cash_flow',
		'report.employee_ledger',
		'report.customer_ledger',
		'report.cash_ledger',
		'report.expenses',
		'report.pending_payment',
		'report.all_sale',
		'report.delivery_order',
		'report.customer_total_sale',
		'report.employee_report',
		'report.booking'
	);
	foreach ($report_permissions as $report_permission) {
		$report_can[$report_permission] = isset($this->rbac) && is_object($this->rbac) ? $this->rbac->can($report_permission) : FALSE;
	}
	
  
  
?>
 <script>
	function sear(URL)
	{
		var id = document.getElementById('search').value;
		 	document.Form.action = URL+  id;
		 document.Form.submit();
	}
 </script>
 <style>
	.report-section {
		margin-bottom: 26px;
		padding-bottom: 18px;
		border-bottom: 1px solid #e5edf5;
	}
	.report-section:last-child {
		border-bottom: 0;
	}
	.report-section-title {
		margin: 0 0 14px;
		font-size: 15px;
		font-weight: 700;
		color: #334155;
		letter-spacing: .02em;
	}
	.report-button-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}
	.report-button-grid .btn {
		width: 200px;
		height: 50px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
	.report-button-grid .btn span {
		float: none !important;
		margin: 0 !important;
	}
 </style>
<body class="animsition erp-app">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="#" method="post">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="col-md-12">
							<div class="col-md-12">
                                <div class="card" style="background-image: url('images/bg.png'); background-repeat: no-repeat;">
                                   <!-- <div class="card-header">Employee Form</div> -->
                                    <div class="card-body">
                                        
                                        </div>
										<hr>
										     <div class="card-body">
                                        <div class="card-title">
										<div class="report-section">
											<h3 class="report-section-title">Financial Reports</h3>
											<div class="report-button-grid">
												<?php if (!empty($report_can['report.financial_dashboard'])) { ?><a href="<?php echo site_url('Cash/reprtfinancial_dashboard'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>Financial Dashboard</span></a><?php } ?>
												<?php if (!empty($report_can['report.sales_expense'])) { ?><a href="<?php echo site_url('Cash/reprtsales_expense_compare'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>Sales Vs Expense</span></a><?php } ?>
												<?php if (!empty($report_can['report.cash_flow'])) { ?><a href="<?php echo site_url('Cash/reprtcash_flow_receivable'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>Cash Flow Report</span></a><?php } ?>
											</div>
										</div>
										<div class="report-section">
											<h3 class="report-section-title">Operational Reports</h3>
											<div class="report-button-grid">
												<?php if (!empty($report_can['report.employee_ledger'])) { ?><a href="<?php echo site_url('Cash/reprtemployee_ledger'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>Employee Ledger</span></a><?php } ?>
												<?php if (!empty($report_can['report.customer_ledger'])) { ?><a href="<?php echo site_url('Cash/reprtcustomer_ledger'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Customer Ledger</span></a><?php } ?>
												<?php if (!empty($report_can['report.cash_ledger'])) { ?><a href="<?php echo site_url('Cash/reprtcash_ledger'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Cash Ledger</span></a><?php } ?>
												<?php if (!empty($report_can['report.expenses'])) { ?><a href="<?php echo site_url('Cash/reprtexp_ledger'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Expenses Sheet</span></a><?php } ?>
												<?php if (!empty($report_can['report.pending_payment'])) { ?><a href="<?php echo site_url('Cash/reprtcustomer_pending'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>Pending Payment Sheet</span></a><?php } ?>
												<?php if (!empty($report_can['report.all_sale'])) { ?><a href="<?php echo site_url('Cash/reprtall_sale_employee_wise'); ?>" target="_blank" class="btn btn-primary dz-btn--filled btn-sm"><span>All Sale Report</span></a><?php } ?>
												<?php if (!empty($report_can['report.delivery_order'])) { ?><a href="<?php echo site_url('Cash/reprtall_delivery_order'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Delivery Order Sheet</span></a><?php } ?>
												<?php if (!empty($report_can['report.customer_total_sale'])) { ?><a href="<?php echo site_url('Cash/rep_customer_sale'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Customer Total Sale</span></a><?php } ?>
												<?php if (!empty($report_can['report.employee_report'])) { ?><a href="<?php echo site_url('employee_rep/reprtemployee_report'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Employee Report</span></a><?php } ?>
												<?php if (!empty($report_can['report.booking'])) { ?><a href="<?php echo site_url('employee_rep/booking_rep'); ?>" class="btn btn-primary dz-btn--filled btn-sm" target="_blank"><span>Booking</span></a><?php } ?>
											</div>
										</div>
                                        </div>	
										
                                        </div>
                                         <div class="card-body">
                                    
                                        </div>   
                              <div   id="page1" style="display:block;">
				 
									
 
											
         
							
					 
	 
					 
                       
                      
                    
                        </div>
                      </form>
 
                     
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

    <!-- Jquery JS-->
   
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
 
</body>

</html>
<!-- end document-->
