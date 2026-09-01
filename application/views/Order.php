 

 <?php
	ob_Start();
	
	$this->load->view('Head');
	$this->load->view('Header');
 
  
?>

 
<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/select2.full.js"></script>
<script>
if ($.fn.select2 && $.fn.select2.amd) {
$.fn.select2.amd.require([
  'select2/selection/multiple',
  'select2/selection/search',
  'select2/dropdown',
  'select2/dropdown/attachBody',
  'select2/dropdown/closeOnSelect',
  'select2/compat/containerCss',
  'select2/utils'
], function (MultipleSelection, Search, Dropdown, AttachBody, CloseOnSelect, ContainerCss, Utils) {
  var SelectionAdapter = Utils.Decorate(
    MultipleSelection,
    Search
  );
  
  var DropdownAdapter = Utils.Decorate(
    Utils.Decorate(
      Dropdown,
      CloseOnSelect
    ),
    AttachBody
  );
  
  $('.inline-search').select2({
   
    selectionAdapter: SelectionAdapter
  });

  $('.staff-employee-single').select2({
    allowClear: false,
    width: '100%',
    minimumResultsForSearch: 0
  });

});
}
function get_user()
{
	var naap = document.getElementById('Naapno').value;
	window.location = "http://localhost:81/alibaba/index.php/Order/order_view/order/"+naap;
}

 
 
function orderRemarkInsert(id, val)
{
	var el = document.getElementById(id);
	if (!el || val === null || val === undefined) {
		return;
	}
	val = String(val).trim();
	if (val === '') {
		return;
	}
	var cur = String(el.value || '').replace(/\s+$/, '');
	el.value = cur ? (cur + ' ' + val) : val;
	if (window.jQuery) {
		jQuery(el).trigger('input');
	}
	el.focus();
}

function addtext(id, val)
{
	orderRemarkInsert(id, val);
}

function initOrderRemarkFields(root)
{
	if (!window.jQuery || !jQuery.fn.setUrduInput) {
		return;
	}
	var $root = root ? jQuery(root) : jQuery('#maincat');
	$root.find('input.form-control.urd.txtbox').each(function () {
		var $field = jQuery(this);
		if (!$field.data('urduBound')) {
			$field.setUrduInput();
			$field.data('urduBound', 1);
		}
	});
}

$(document).ready(function(){
	$(document).on('click', '.order-predefined-tag', function (e) {
		e.preventDefault();
		orderRemarkInsert(this.getAttribute('data-target'), this.getAttribute('data-insert'));
	});

    $(document).off('change.orderQty').on('change.orderQty', '#Quantity', function(){
			 document.getElementById('total_amount').value = 0;
        var qty = $(this).val();
        if(qty){
			
            $.ajax({
                type:'POST',
                url:'<?php echo site_url('Order/order_table'); ?>',
                data:'qty='+qty,
                success:function(html){
                    $('#maincat').html(html);
                    initOrderRemarkFields('#maincat');
                }
            }); 
        }else{
           // $('#maincat').html('<option value="">Select Category first</option>');
         //   $('#subcat').html('<option value="">Select Category first</option>'); 
        }
    });

    $(document).off('keydown.orderQty').on('keydown.orderQty', '#Quantity', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).trigger('change');
        }
    });
	
});



function bal()
{
	document.getElementById('Balance').value =   parseFloat(document.getElementById('addprice').value) +  	parseFloat(document.getElementById('total_amount').value)   - document.getElementById('Advance').value - document.getElementById('Discount').value ;
	
	
	 
}

function valid(e)
{
		if(document.getElementById('Quantity').value < 1 )
		{
			alert((window.APP_LANG && APP_LANG.enter_qty) || 'Please enter the QTY');
			return false;
		}
		var tot  = document.getElementById('Quantity').value;
	for(var m=0; m<tot; m++)
	{
		var desig = 'Designtype' + m;
		var desig_type = 'suit' + m;
		 
		if(document.getElementById(desig).value == '' )
		{
			alert((window.APP_LANG && APP_LANG.select_design) || 'Please Select Design');
			return false;
		}	
		  if(document.getElementById(desig_type).value == 0 )
		{
			alert((window.APP_LANG && APP_LANG.select_suit_type) || 'Please Select Suit Type');
			return false;
		}
		
	}

	var form = document.forms['Form'];
	if (form && form.getAttribute('data-order-confirmed') === '1')
	{
		form.removeAttribute('data-order-confirmed');
		var submitButton = document.getElementById('submit-button');
		if (submitButton && submitButton.disabled)
		{
			return false;
		}
		if (submitButton)
		{
			submitButton.disabled = true;
			document.getElementById('submit-button-amount').innerHTML = (window.APP_LANG && APP_LANG.loading) ? APP_LANG.loading : 'Saving...';
		}
		if (window.Webcam)
		{
			Webcam.snap(function(data_uri) {
				$(".image-tag").val(data_uri);
				$("#imgtxt").val(data_uri);
				if (document.getElementById('teimg'))
				{
					document.getElementById('teimg').src = data_uri;
				}
			});
		}
		return true;
	}

	if (e && e.preventDefault)
	{
		e.preventDefault();
	}

	var confirmMsg = (window.APP_LANG && APP_LANG.confirm_submit_order)
		? APP_LANG.confirm_submit_order
		: 'Are you sure you want to submit this order?';

	if (window.AppConfirm)
	{
		window.AppConfirm.show({
			title: (window.APP_LANG && APP_LANG.app_confirm) ? APP_LANG.app_confirm : 'Confirm',
			message: confirmMsg,
			onConfirm: function () {
				if (form)
				{
					form.setAttribute('data-order-confirmed', '1');
					if (form.requestSubmit)
					{
						form.requestSubmit();
					}
					else
					{
						form.submit();
					}
				}
			}
		});
	}
	else if (window.confirm(confirmMsg))
	{
		if (form)
		{
			form.setAttribute('data-order-confirmed', '1');
			return valid(e);
		}
	}

	return false;
}
</script>
 
 <style>
 
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', sans serif;;
	}
	#Name,#City,#Remarks,#Address,#Country {
		direction: rtl;
		border: 1px solid #ccc;
		vertical-align: middle;
		direction: rtl;
		padding: 2px;
		font-size: 16px;
		font-weight:bold;
	}
	#kb {
		width:500px;
	}
	a {
		color:#4ea0e8;
		text-decoration:none;
	}
	</style>
<body class="animsition erp-app order-form-page">
    <div class="page-wrapper order-form-page">
        <!-- HEADER MOBILE-->
            <!-- MAIN CONTENT-->
            <div class="main-content">
			<form name="Form" action="<?php echo site_url('Order/process'); ?>" method="post" onsubmit="return valid(event)" data-no-ajax="true">
			<input type="hidden" name="order_submit_token" value="<?php echo isset($order_submit_token) ? $order_submit_token : ''; ?>">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php   if($this->session->flashdata('suss') == 'yes') { ?>
					 <div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
											<span class="badge badge-pill badge-success"><?php echo t('app_success'); ?></span>
											<?php echo sprintf(t('app_record_entered'), $last_id); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php  } elseif ($this->session->flashdata('suss') === 'duplicate') { ?>
					 <div class="sufee-alert alert with-close alert-warning alert-dismissible fade show">
											<?php echo t('order_flash_duplicate'); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										</div>
						<?php  } elseif ($this->session->flashdata('suss') === 'incomplete') { ?>
					 <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
											<?php echo t('order_flash_incomplete'); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										</div>
						<?php  } ?>				
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card order-entry-card">
                                   <!-- <div class="card-header">Employee Form</div> -->
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('order_form'); ?></h2>
										<div class="erp-card-actions">
											<button type="button" class="btn btn-outline-primary btn-sm" id="customerOrderHistoryBtn"><?php echo t('order_customer_history'); ?></button>
											<span class="erp-entry-number"><?php echo t('app_entry'); ?> : <?php $last_id = $last_id + 1 ;echo $last_id;  /*
                                            $query_last = "select TRNNO from emp order by TRNNO desc limit 1";
                                            $result_last=mysql_query($query_last);
                                            $row_last=mysql_fetch_object($result_last);
                                            echo $row_last->TRNNO;
                                            */
                                            ?></span>
										</div>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
                              <div class="row order-form-layout">
                                <div class="col-lg-8 col-xl-9 order-form-main">
                              <div   id="page1" style="display:block;">
			<?php
			$order_measurement_row = isset($measurement_row) ? $measurement_row : NULL;
			if (!$order_measurement_row && !empty($Measurement_data) && is_array($Measurement_data))
			{
				$order_measurement_row = $Measurement_data[0];
			}
			$order_customer_display_name = '';
			if ($order_measurement_row)
			{
				$order_customer_display_name = isset($order_measurement_row->customer_name) && trim((string) $order_measurement_row->customer_name) !== ''
					? (string) $order_measurement_row->customer_name
					: (isset($order_measurement_row->name) ? (string) $order_measurement_row->name : '');
			}
			?>
					<div class="row">
						<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_naap_no'); ?></label>
 
           
            <input id="Naap12no" name="Naap12no" readonly="readonly" type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $order_measurement_row ? esc_html((string) $order_measurement_row->MANNAAPNO) : ''; ?>"  >                                  
            <input id="Naapno" name="Naapno" readonly="readonly" type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $order_measurement_row ? (int) $order_measurement_row->MCODE : ''; ?>"  >                                </div>  
						</div>
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_customer_name'); ?></label>
										 
  <input id="order_customer_name_display" name="customer_name" disabled type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo esc_html($order_customer_display_name); ?>"  >
  <input id="customer_NO" name="customer_NO" readonly="readonly" type="hidden" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo $order_measurement_row ? (int) $order_measurement_row->Customerno : ''; ?>"  >
                                            </div>  
						</div>
						<div class="col">
							<div class="form-group">
								<label class="control-label mb-1"><?php echo t('order_staff_employee'); ?></label>
								<select name="staff_employee" id="staff_employee" class="form-control staff-employee-single" required>
									<option value=""><?php echo t('order_select_staff_employee'); ?></option>
									<?php
									$staff_employees = isset($staff_employees) ? $staff_employees : array();
									$selected_staff = isset($selected_staff_empno) ? (int) $selected_staff_empno : 0;
									foreach ($staff_employees as $staff_emp) {
										$sid = (int) $staff_emp->empno;
									?>
									<option value="<?php echo $sid; ?>"<?php echo ($selected_staff === $sid) ? ' selected' : ''; ?>><?php echo esc_html($staff_emp->name.'  '.$staff_emp->phone); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>	
			 
				<div class="row">
									<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_current_date'); ?></label>
  <input id="current" name="current"  disabled type="text" class="form-control" aria-required="true" aria-invalid="false" value="<?php echo date('d-M-Y'); ?>" re>
                                            </div>  
						</div>
						
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_return_date'); ?></label>
  <input id="retrun_Date" name="retrun_Date"   type="date" class="form-control" aria-required="true" aria-invalid="false"  value="<?php echo date('d-m-Y'); ?>" >
                                            </div>  
						</div>
														<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_quantity'); ?></label>
  <input id="Quantity" name="Quantity"   type="text" class="form-control" aria-required="true" aria-invalid="false" value="" >
                                            </div>  
						</div>
						 
					</div>	
					
				 
					 
										<div id="maincat">
										
										</div>
										
							<div class="row">
									<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_total_amount'); ?></label>
  <input id="total_amount" name="total_amount"    type="text" onchange="bal()" readonly="readonly" class="form-control" aria-required="true" aria-invalid="false" value="0" >
                                            </div>  
						</div>
						 
  <input id="addprice" name="addprice"   type="hidden" class="form-control" onchange="bal()" aria-required="true" aria-invalid="false" value="0" >
                        
										<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_advance'); ?></label>
  <input id="Advance" name="Advance"   type="text" class="form-control" onchange="bal()" aria-required="true" aria-invalid="false" value="" >
                                            </div>  
						</div>
													<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_discount'); ?></label>
  <input id="Discount" name="Discount"   type="text" class="form-control" onchange="bal()" aria-required="true" aria-invalid="false" value="" >
                                            </div>  
						</div>
														<div class="col">	
<textarea  id="imgtxt" name="iamge" style="display:none;" >
				
			</textarea>														
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('app_balance'); ?></label>
  <input id="Balance" name="Balance"  disabled  type="text" class="form-control" aria-required="true" aria-invalid="false" value="" >
                                            </div>  
						</div>
						 
					</div>		  			
			                	 			<div class="row data-entry-actions">
												<div class="col">
											 
											
											</div>
												<div class="col">
                                               <!-- <button id="submit-button" type="submit" name="savBtn" onclick="return valid()"  class="btn btn-lg btn-info btn-block">-->
                                                <button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
	
                                                    <span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
                                                    <span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
                                                </button>
                                            </div>
										 <div class="col">
											 
											
											</div>
										
</div>
</div>
                                </div>
                                <div class="col-lg-4 col-xl-3 order-form-camera-col">
                                  <div class="order-camera-panel">
                                    <div class="order-camera-panel__head"><?php echo t('order_camera'); ?></div>
                                    <div class="order-camera-panel__view">
                                    <div id="my_camera" class="order-camera-panel__stage"></div>
                                  </div>
                                  </div>
                                </div>
                              </div>
                                    </div>
                                </div>
                     	</div>
                    </div>
                </div>
			</form>
            </div>
            <!-- END MAIN CONTENT-->
          <?php
  /*include('footer.php'); */
 ?>
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

<div class="modal fade" id="customerOrderHistoryModal" tabindex="-1" role="dialog" aria-labelledby="customerOrderHistoryModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="customerOrderHistoryModalLabel">Customer order history</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="customerOrderHistoryBody">
				<p class="text-center text-muted py-3 mb-0">Loading…</p>
			</div>
		</div>
	</div>
</div>

<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("emp-EMPTITLE");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
</script>
    <!-- Jquery JS-->


    <script src="<?php echo base_url(); ?>vendor/animsition/animsition.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">

    </script>


    <!-- Main JS-->
    <script src="<?php echo base_url(); ?>js/main.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
 <script type="text/javascript">
            $(function () {
                if ($.fn.datetimepicker && $('#datetimepicker1').length) {
                    $('#datetimepicker1').datetimepicker();
                }
            });

			$(document).on('click', '#customerOrderHistoryBtn', function (e) {
				e.preventDefault();
				var customerCode = $('#customer_NO').val();
				var customerName = $('input[name="customer_name"]:disabled').val() || '';
				if (!customerCode) {
					alert((window.APP_LANG && APP_LANG.customer_not_found) ? APP_LANG.customer_not_found : 'Customer not found.');
					return;
				}
				$('#customerOrderHistoryModalLabel').text(
					customerName ? ('<?php echo addslashes(t('order_history_title')); ?> — ' + customerName) : '<?php echo addslashes(t('order_history_title')); ?>'
				);
				$('#customerOrderHistoryBody').html('<p class="text-center text-muted py-3 mb-0">' + ((window.APP_LANG && APP_LANG.loading) ? APP_LANG.loading : 'Loading…') + '</p>');
				if (window.AppModal) {
					window.AppModal.show('#customerOrderHistoryModal');
				} else {
					$('#customerOrderHistoryModal').modal('show');
				}
				$.ajax({
					type: 'POST',
					url: '<?php echo site_url('Order/customer_order_history'); ?>',
					data: { customer_code: customerCode },
					success: function (html) {
						$('#customerOrderHistoryBody').html(html);
					},
						error: function () {
							$('#customerOrderHistoryBody').html('<p class="text-center text-danger py-3 mb-0">' + ((window.APP_LANG && APP_LANG.could_not_load_history) ? APP_LANG.could_not_load_history : 'Could not load order history.') + '</p>');
						}
				});
			});

			$(document).on('click', '#customerOrderHistoryModal [data-dismiss="modal"]', function () {
				if (window.AppModal) {
					window.AppModal.hide('#customerOrderHistoryModal');
				} else {
					$('#customerOrderHistoryModal').modal('hide');
				}
			});

        </script>
	 <script language="JavaScript">
function initOrderWebcam()
{
	if (window.__orderWebcamInitialized)
	{
		return;
	}
	var view = document.querySelector('.order-camera-panel__view');
	var stage = document.getElementById('my_camera');
	if (!view || !stage || !window.Webcam)
	{
		return;
	}

	var size = Math.floor(view.clientWidth);
	if (size < 200)
	{
		size = 280;
	}

	Webcam.set({
		width: size,
		height: size,
		dest_width: size,
		dest_height: size,
		image_format: 'jpeg',
		jpeg_quality: 90
	});

	Webcam.on('error', function (err) {
		if (window.console && console.error)
		{
			console.error('Webcam error:', err);
		}
	});

	Webcam.attach('#my_camera');
	window.__orderWebcamInitialized = true;
}

if (window.Webcam && document.getElementById('my_camera'))
{
	window.addEventListener('load', function () {
		window.setTimeout(initOrderWebcam, 200);
	});
	if (window.jQuery)
	{
		jQuery(document).on('app:ajax-loaded', function () {
			if (!document.getElementById('my_camera'))
			{
				return;
			}
			if (window.Webcam && window.Webcam.live)
			{
				Webcam.reset();
			}
			window.__orderWebcamInitialized = false;
			window.setTimeout(initOrderWebcam, 250);
		});
	}
}
  
    function take_snapshot() {
		if (!window.Webcam) {
			return;
		}
        Webcam.snap( function(data_uri) {
            $(".image-tag").val(data_uri);
            $("#imgtxt").val(data_uri);
          //  document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
			 
			 document.getElementById('teimg').src = data_uri;
            
			
			
        } );
    }
</script>
<?php $this->load->view('partials/order_copy_suit_line_js'); ?>
 
</body>

</html>
<!-- end document-->
