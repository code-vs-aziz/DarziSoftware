<?php
	ob_Start();
$this->load->helper('ui');
 
 	$this->load->view('Head');
    $this->load->view('Header');
 
  
?>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <div class="customer-page-wrap">
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div  >
                    <div class="container-fluid">
						 
						<?php
						$customer_notice = $this->session->flashdata('customer_notice');
						if ($customer_notice) { ?>
						<div class="alert alert-warning" role="alert"><?php echo esc_html((string) $customer_notice); ?></div>
						<?php } ?>
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 
<div class="alert alert-success" role="alert">
											<?php echo t('customer_created'); ?>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
						<div class="dz-card erp-module-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title"><?php echo t('customer_list'); ?></h2>
								<div class="erp-card-actions">
									<a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Customer/Customer_add'); ?>"><?php echo t('dash_add_customer'); ?></a>
								</div>
							</div>
							<div class="card-body erp-card-body">
									<section>
<?php
$per_page_opts = isset($customer_per_page_allowed) && is_array($customer_per_page_allowed)
	? $customer_per_page_allowed
	: array(10, 25, 50, 100);
$ppp = isset($pagination_per_page) ? (int) $pagination_per_page : 25;
$customer_search_url_prefix = rtrim(site_url('Customer/search_customer'), '/').'/';
$modal_nav_attr = 'data-no-ajax="true" data-app-modal-nav="true"';
?>
			<div class="erp-toolbar">
			<form action="#" method="get" class="form-inline align-items-center flex-wrap mb-0" id="customer-search-form" onsubmit="return customerListSearchSubmit(event);">
				<label class="mr-2 mb-0" for="customer-per-page-top"><strong>Show:</strong></label>
				<select name="per_page" id="customer-per-page-top" class="form-control form-control-sm mr-3 mb-2 mb-sm-0" style="width: auto; min-width: 4.25rem;" title="How many records to show per page">
					<?php foreach ($per_page_opts as $opt) {
						$opt = (int) $opt;
						?>
					<option value="<?php echo $opt; ?>"<?php echo ($opt === $ppp) ? ' selected' : ''; ?>><?php echo $opt; ?></option>
					<?php } ?>
				</select>
				<label class="mr-2 mb-0"><strong>Search:</strong></label>
				<input type="text" name="customer_search" id="customer_search_input" class="form-control mr-2 mb-2 mb-sm-0" style="min-width:240px;" placeholder="Phone, Naap No, CNIC or name" value="<?php echo isset($search_term) ? esc_html((string) $search_term) : ''; ?>" autocomplete="off" />
				<button type="submit" class="btn btn-primary dz-btn--filled mr-2 mb-2 mb-sm-0"><?php echo t('app_search'); ?></button>
				<?php if ( ! empty($is_search)) { ?>
				<a class="btn btn-secondary dz-btn--outlined mb-2 mb-sm-0" href="<?php echo site_url('Customer'); ?>"><?php echo t('app_clear'); ?></a>
				<?php } ?>
			</form>
			</div>
			<script>
			(function () {
				var prefix = <?php echo json_encode($customer_search_url_prefix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

				function customerListSearchUrl(term, perPage) {
					return prefix + encodeURIComponent(term) + '?per_page=' + encodeURIComponent(perPage);
				}

				function customerListNavigate(url) {
					if (window.AppAjaxShell && typeof window.AppAjaxShell.load === 'function') {
						window.AppAjaxShell.load(url);
					} else {
						window.location.href = url;
					}
				}

				window.customerListSearchSubmit = function (event) {
					if (event && event.preventDefault) {
						event.preventDefault();
					}
					var input = document.getElementById('customer_search_input');
					var sel = document.getElementById('customer-per-page-top');
					var term = input ? String(input.value || '').replace(/^\s+|\s+$/g, '') : '';
					if (term === '') {
						alert(<?php echo json_encode(t('customer_search_need_field'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
						return false;
					}
					customerListNavigate(customerListSearchUrl(term, sel ? sel.value : '25'));
					return false;
				};

				var sel = document.getElementById('customer-per-page-top');
				var input = document.getElementById('customer_search_input');
				if (sel && input) {
					sel.addEventListener('change', function () {
						var term = String(input.value || '').replace(/^\s+|\s+$/g, '');
						if (term !== '') {
							customerListNavigate(customerListSearchUrl(term, sel.value));
						}
					});
				}
			})();
			</script>
			<?php if (empty($is_search)) { ?>
			<p class="text-muted small mb-2">Customers are not loaded until you search. This keeps the page fast with large databases.</p>
			<?php } elseif (empty($customer)) { ?>
			<div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between mb-3" role="status">
				<div>
					<strong><?php echo esc_html(t('home_search_empty')); ?></strong>
					<div class="small mb-0"><?php echo esc_html(t('home_search_empty_hint')); ?></div>
				</div>
				<a class="btn btn-primary dz-btn--filled btn-sm mt-2 mt-sm-0" href="<?php echo site_url('Customer/Customer_add'.( ! empty($search_term) ? '?phone='.rawurlencode(preg_replace('/\D+/', '', (string) $search_term)) : '')); ?>"><?php echo esc_html(t('home_search_add_customer')); ?></a>
			</div>
			<?php } ?>
			<div class="demo-html"></div>
			<div class="table-responsive">
			<table id="example" class="display table table-sm table-bordered table-striped" style="width:100%" data-customer-list-table="1">
			<thead>
					<tr>
						<th>Customer Code</th>
						<th>Name</th>
						<th>Phone #</th>
						<th>Naap No</th>
						 
						<th>DOB</th>
					 
						<th>View</th>
					</tr>
				</thead>
				<tbody>
				<?php 
			 if (empty($customer)) {
				 $customer = array();
			 }
			 $measurement_map = isset($customers_with_measurement) ? $customers_with_measurement : array();
			 $measurement_codes = isset($customer_measurement_codes) ? $customer_measurement_codes : array();
			 foreach($customer as $customer1){
									 
						                    ?>
					<tr>
						<td><?php echo $customer1->code; ?></td>
						<td><?php echo $customer1->name; ?></td>
						<td><?php echo $customer1->Phone; ?></td>
						<td><?php echo $customer1->man_napno; ?></td>
						 
						<td><?php echo isset($customer1->DOB) ? $customer1->DOB : ''; ?></td>
				 
	<td>
	<!-- Button trigger modal -->
<button type="button" class="btn btn-primary dz-btn--filled btn-sm" data-toggle="modal" data-target="#exampleModal<?php  echo $customer1->code ; ?>">View</button>

<!-- Modal -->
<div class="modal fade dz-actions-modal" id="exampleModal<?php  echo $customer1->code ; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel<?php echo (int) $customer1->code; ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel<?php echo (int) $customer1->code; ?>"><?php echo t('app_actions'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_html(t('app_cancel')); ?>">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body dz-actions-modal__body">
        <p class="dz-actions-modal__hint"><?php echo esc_html($customer1->name); ?> · <?php echo esc_html($customer1->man_napno); ?></p>
        <div class="dz-actions-modal__buttons">
	 <?php
	$cust_code = (int) $customer1->code;
	$meas_code = isset($measurement_codes[$cust_code]) ? (int) $measurement_codes[$cust_code] : 0;
	$has_measurement = $meas_code > 0 || ! empty($measurement_map[$cust_code]);

	if (! $has_measurement)
	{
						                    ?>
        <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Measurement/measurement_add/'.$cust_code.'?return=order'); ?>" <?php echo $modal_nav_attr; ?>><?php echo t('customer_measurement'); ?></a>
	<?php  } else { ?>
	 <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Measurement/measurement_edit/'.$cust_code.''); ?>" <?php echo $modal_nav_attr; ?>><?php echo t('customer_measurement'); ?></a>
	 <a class="btn btn-primary dz-btn--filled btn-sm" href="<?php echo site_url('Order/order_add1/'.$cust_code.'/'.$meas_code); ?>" <?php echo $modal_nav_attr; ?>><?php echo t('customer_add_order'); ?></a>
	<?php  }  ?>
	    <a class="btn btn-outline-primary dz-btn--outlined btn-sm" href="<?php echo site_url('Customer/customer_edit/'.$cust_code.''); ?>" <?php echo $modal_nav_attr; ?>><?php echo t('customer_edit'); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
	</td>
					</tr>
			 <?php  } ?>		
									</tbody>
				 
					</table>
			</div>
			<?php
			if ( ! empty($is_search) && isset($pagination_total_rows) && $pagination_total_rows > 0)
			{
				$row_count = isset($customer) ? count($customer) : 0;
				$from = $row_count > 0 ? $pagination_offset + 1 : 0;
				$to = $pagination_offset + $row_count;
			?>
			<div class="customer-pagination-wrap text-center mt-3 mb-2">
				<p class="small text-muted mb-2">Showing <strong><?php echo (int) $from; ?></strong>–<strong><?php echo (int) $to; ?></strong> of <strong><?php echo (int) $pagination_total_rows; ?></strong></p>
				<?php echo isset($pagination) ? $pagination : ''; ?>
			</div>
			<?php
			}
			elseif ( ! empty($pagination))
			{
				echo $pagination;
			}
			?>
			 
	</section>
							</div><!-- card-body erp -->
						</div><!-- card erp-module-card -->
                     	</div><!-- col-md-12 -->
          <?php
  /*include('footer.php'); */
 ?>
                     
                    </div>
                </div>
            </div>
      </div><!-- customer-page-wrap -->
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
    </div>

 
</body>

</html>
<!-- end document-->
