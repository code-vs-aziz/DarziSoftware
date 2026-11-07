 

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

function orderBookingLimitMsg(res)
{
	var fmt = orderLang('booking_limit_reached', 'Booking limit full for return date %1$s. Limit: %2$d, already booked: %3$d, remaining: %4$d. Reduce quantity or change return date.');
	if (!res) {
		return orderLang('booking_limit_generic', 'Daily booking limit reached for this return date. Reduce quantity or choose another date.');
	}
	return fmt
		.replace('%1$s', res.return_date || '')
		.replace('%2$d', String(res.limit != null ? res.limit : 0))
		.replace('%3$d', String(res.booked != null ? res.booked : 0))
		.replace('%4$d', String(res.remaining != null ? res.remaining : 0));
}

function showOrderBookingLimitAlert(msg)
{
	var $box = $('#order-booking-limit-alert');
	if (!$box.length) {
		return;
	}
	$box.find('.order-booking-limit-alert__text').text(msg || '');
	$box.removeClass('d-none').show();
	try {
		$box[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
	} catch (e) { /* ignore */ }
}

function clearOrderBookingLimitAlert()
{
	var $box = $('#order-booking-limit-alert');
	if ($box.length) {
		$box.addClass('d-none').hide();
		$box.find('.order-booking-limit-alert__text').text('');
	}
}

/**
 * Check daily booking limit before opening suit lines.
 * @param {function(boolean, object=)} done
 */
function checkOrderBookingLimit(done)
{
	var qtyEl = document.getElementById('Quantity');
	var dateEl = document.getElementById('retrun_Date');
	var qty = qtyEl ? String(qtyEl.value || '').trim() : '';
	var rdate = dateEl ? String(dateEl.value || '').trim() : '';
	var qtyNum = parseInt(qty, 10) || 0;

	if (!qty || qtyNum < 1) {
		clearOrderBookingLimitAlert();
		done(true);
		return;
	}
	if (!rdate) {
		showOrderBookingLimitAlert(orderLang('select_return_date_first', 'Please select return date first.'));
		done(false);
		return;
	}

	$.ajax({
		type: 'POST',
		url: '<?php echo site_url('Order/booking_limit_check'); ?>',
		dataType: 'json',
		data: {
			qty: qtyNum,
			return_date: rdate,
			exclude_order: 0
		},
		success: function (res) {
			if (res && res.ok === false && (!res.error || res.error === 'booking_limit_exceeded' || res.message === 'booking_limit_exceeded')) {
				showOrderBookingLimitAlert(orderBookingLimitMsg(res));
				done(false, res);
				return;
			}
			clearOrderBookingLimitAlert();
			done(true, res);
		},
		error: function () {
			/* Network fail — still allow UI; server enforces on submit. */
			clearOrderBookingLimitAlert();
			done(true);
		}
	});
}

function loadOrderSuitLines(qty)
{
	document.getElementById('total_amount').value = 0;
	$.ajax({
		type: 'POST',
		url: '<?php echo site_url('Order/order_table'); ?>',
		data: 'qty=' + encodeURIComponent(qty),
		success: function (html) {
			$('#maincat').html(html);
			initOrderRemarkFields('#maincat');
			if (typeof window.orderFormOnSuitLinesLoaded === 'function') {
				window.orderFormOnSuitLinesLoaded();
			}
		}
	});
}

$(document).ready(function(){
	$(document).on('click', '.order-predefined-tag', function (e) {
		e.preventDefault();
		orderRemarkInsert(this.getAttribute('data-target'), this.getAttribute('data-insert'));
	});

    $(document).off('change.orderQty').on('change.orderQty', '#Quantity', function(){
        var qty = $(this).val();
        if (!qty) {
			clearOrderBookingLimitAlert();
			$('#maincat').empty();
			return;
		}
		checkOrderBookingLimit(function (ok) {
			if (!ok) {
				$('#maincat').empty();
				document.getElementById('total_amount').value = 0;
				return;
			}
			loadOrderSuitLines(qty);
		});
    });

    $(document).off('keydown.orderQty').on('keydown.orderQty', '#Quantity', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).trigger('change');
        }
    });

	/* Return date change: re-check if qty already entered; block suits if over limit. */
	$(document).off('change.orderRdate blur.orderRdate').on('change.orderRdate blur.orderRdate', '#retrun_Date', function () {
		var qty = $('#Quantity').val();
		if (!qty) {
			clearOrderBookingLimitAlert();
			return;
		}
		checkOrderBookingLimit(function (ok) {
			if (!ok) {
				$('#maincat').empty();
				document.getElementById('total_amount').value = 0;
			} else if (!$('#maincat').children().length) {
				loadOrderSuitLines(qty);
			}
		});
	});
	
});



function bal()
{
	document.getElementById('Balance').value =   parseFloat(document.getElementById('addprice').value) +  	parseFloat(document.getElementById('total_amount').value)   - document.getElementById('Advance').value - document.getElementById('Discount').value ;
	
	
	 
}

function orderLang(key, fallback)
{
	return (window.APP_LANG && APP_LANG[key]) ? APP_LANG[key] : fallback;
}

function orderHasCapturedImage()
{
	var imgtxt = document.getElementById('imgtxt');
	var val = imgtxt ? String(imgtxt.value || '').trim() : '';
	return val.indexOf('data:image') === 0;
}

function setOrderSubmitBusy(busy)
{
	var submitButton = document.getElementById('submit-button');
	var amountEl = document.getElementById('submit-button-amount');
	if (!submitButton)
	{
		return;
	}
	submitButton.disabled = !!busy;
	if (amountEl)
	{
		amountEl.innerHTML = busy
			? orderLang('loading', 'Saving...')
			: orderLang('app_submit', <?php echo json_encode(t('app_submit'), JSON_UNESCAPED_UNICODE); ?>);
	}
}

function setOrderImageData(dataUri)
{
	if (!dataUri)
	{
		return;
	}
	var imgtxt = document.getElementById('imgtxt');
	if (imgtxt)
	{
		imgtxt.value = dataUri;
	}
	if (window.jQuery)
	{
		jQuery('.image-tag').val(dataUri);
	}
	var teimg = document.getElementById('teimg');
	if (teimg)
	{
		teimg.src = dataUri;
	}
}

function finishOrderSubmit()
{
	var form = document.forms.Form;
	if (!form)
	{
		setOrderSubmitBusy(false);
		return false;
	}
	setOrderSubmitBusy(true);
	form.submit();
	return false;
}

function confirmSaveWithoutPhoto(onYes)
{
	var msg = orderLang(
		'order_no_image_confirm',
		'No image has been captured. Do you want to save the order without an image and use the default avatar instead?'
	);
	var proceed = function () {
		var toggle = document.getElementById('orderUseAvatar');
		if (toggle && !toggle.checked)
		{
			toggle.checked = true;
			if (typeof setOrderAvatarMode === 'function')
			{
				setOrderAvatarMode(true);
			}
		}
		if (typeof window.applyOrderAvatarImage === 'function')
		{
			window.applyOrderAvatarImage(function (dataUri) {
				if (dataUri)
				{
					setOrderImageData(dataUri);
				}
				onYes();
			});
			return;
		}
		onYes();
	};
	if (window.AppConfirm)
	{
		window.AppConfirm.show({
			title: orderLang('app_confirm', 'Confirm'),
			message: msg,
			okLabel: orderLang('order_save_without_photo', 'Save with avatar'),
			cancelLabel: orderLang('app_cancel', 'Cancel'),
			onConfirm: proceed
		});
		return;
	}
	if (window.confirm(msg))
	{
		proceed();
	}
}

function snapOrderWebcam(done, fail)
{
	if (!window.Webcam || !window.Webcam.live || !window.Webcam.loaded)
	{
		if (typeof fail === 'function')
		{
			fail();
		}
		return;
	}
	var settled = false;
	var timer = window.setTimeout(function () {
		if (settled)
		{
			return;
		}
		settled = true;
		if (typeof fail === 'function')
		{
			fail();
		}
	}, 4000);
	try
	{
		Webcam.snap(function (data_uri) {
			if (settled)
			{
				return;
			}
			settled = true;
			window.clearTimeout(timer);
			if (data_uri && String(data_uri).indexOf('data:image') === 0)
			{
				setOrderImageData(data_uri);
				window.__orderPhotoCaptured = true;
				done(data_uri);
			}
			else if (typeof fail === 'function')
			{
				fail();
			}
		});
	}
	catch (err)
	{
		if (settled)
		{
			return;
		}
		settled = true;
		window.clearTimeout(timer);
		if (typeof fail === 'function')
		{
			fail();
		}
	}
}

function submitConfirmedOrder()
{
	var submitButton = document.getElementById('submit-button');
	if (submitButton && submitButton.disabled)
	{
		return false;
	}

	var go = function () {
		finishOrderSubmit();
	};

	if (orderHasCapturedImage() || window.orderAvatarModeActive)
	{
		if (window.orderAvatarModeActive && !orderHasCapturedImage() && typeof window.applyOrderAvatarImage === 'function')
		{
			window.applyOrderAvatarImage(function (dataUri) {
				if (dataUri)
				{
					setOrderImageData(dataUri);
				}
				go();
			});
			return false;
		}
		return go();
	}

	if (window.Webcam && window.Webcam.live && window.Webcam.loaded)
	{
		snapOrderWebcam(function () {
			go();
		}, function () {
			confirmSaveWithoutPhoto(go);
		});
		return false;
	}

	confirmSaveWithoutPhoto(go);
	return false;
}

function valid(e, options)
{
	options = options || {};
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
	if (options.previewOnly)
	{
		return true;
	}
	if (form && form.getAttribute('data-order-confirmed') === '1')
	{
		form.removeAttribute('data-order-confirmed');
		return submitConfirmedOrder();
	}

	if (e && e.preventDefault)
	{
		e.preventDefault();
	}

	if (document.getElementById('orderPreviewModal') && typeof window.orderFormShowPreview === 'function')
	{
		window.orderFormShowPreview(function () {
			if (form)
			{
				form.setAttribute('data-order-confirmed', '1');
				submitConfirmedOrder();
			}
		});
		return false;
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
<link href="<?php echo base_url(); ?>css/order-form-enhancements.css?v=11" rel="stylesheet" media="all">
<body class="animsition erp-app erp-form-page order-form-page">
    <div class="page-wrapper order-form-page">
        <!-- HEADER MOBILE-->
            <!-- MAIN CONTENT-->
            <div class="main-content order-form-page">
			<form name="Form" action="<?php echo site_url('Order/process'); ?>" method="post" onsubmit="return valid(event)" data-no-ajax="true">
			<input type="hidden" name="order_submit_token" value="<?php echo isset($order_submit_token) ? $order_submit_token : ''; ?>">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						 
						<?php
						$order_flash_msg = app_take_flash('success_msg');
						$order_flash_suss = app_take_flash('suss');
						if ($order_flash_msg !== NULL && $order_flash_msg !== '') {
						?>
					 <div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
											<span class="badge badge-pill badge-success"><?php echo t('app_success'); ?></span>
											<?php echo esc_html((string) $order_flash_msg); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php } elseif ($order_flash_suss === 'yes') { ?>
					 <div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
											<span class="badge badge-pill badge-success"><?php echo t('app_success'); ?></span>
											<?php echo sprintf(t('app_record_entered'), $last_id); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php } elseif ($order_flash_suss === 'duplicate') { ?>
					 <div class="sufee-alert alert with-close alert-warning alert-dismissible fade show">
											<?php echo t('order_flash_duplicate'); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										</div>
						<?php } elseif ($order_flash_suss === 'incomplete') { ?>
					 <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
											<?php echo t('order_flash_incomplete'); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										</div>
						<?php } elseif ($order_flash_suss === 'booking_limit') { ?>
					 <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
											<?php
											$bli = app_take_flash('booking_limit_info');
											if (is_array($bli))
											{
												echo esc_html(sprintf(
													t('order_flash_booking_limit'),
													isset($bli['return_date']) ? $bli['return_date'] : '',
													isset($bli['limit']) ? (int) $bli['limit'] : 0,
													isset($bli['booked']) ? (int) $bli['booked'] : 0,
													isset($bli['remaining']) ? (int) $bli['remaining'] : 0
												));
											}
											else
											{
												echo t('order_flash_booking_limit_generic');
											}
											?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										</div>
						<?php } ?>				
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card order-entry-card">
                                   <!-- <div class="card-header">Employee Form</div> -->
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('order_form'); ?></h2>
										<div class="erp-card-actions">
											<span class="erp-entry-number"><?php echo t('app_entry'); ?> : <?php $last_id = $last_id + 1 ;echo $last_id;  ?></span>
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
			$order_ctx = isset($customer_context) && is_array($customer_context) ? $customer_context : array();
			$order_default_return = date('d-M-Y');
			?>
					<div class="order-customer-panel">
						<div class="order-section__head">
							<h3 class="order-section__title"><?php echo t('order_section_customer'); ?></h3>
							<div class="order-section__actions">
								<button type="button" class="btn btn-outline-primary btn-sm" id="customerOrderHistoryBtn"><?php echo t('order_customer_history'); ?></button>
								<?php if (!empty($order_ctx['has_last_order'])) { ?>
								<button type="button" class="btn btn-outline-secondary btn-sm" id="orderCopyLastBtn"><?php echo t('order_copy_last'); ?></button>
								<?php } ?>
							</div>
						</div>
						<div class="order-customer-panel__grid">
							<div class="order-customer-panel__item">
								<span><?php echo t('order_naap_no'); ?></span>
								<strong><?php echo $order_measurement_row ? esc_html((string) $order_measurement_row->MANNAAPNO) : '—'; ?></strong>
							</div>
							<div class="order-customer-panel__item">
								<span><?php echo t('order_customer_name'); ?></span>
								<strong><?php echo esc_html($order_customer_display_name !== '' ? $order_customer_display_name : '—'); ?></strong>
							</div>
							<div class="order-customer-panel__item">
								<span><?php echo t('order_customer_phone'); ?></span>
								<strong><?php echo !empty($order_ctx['phone']) ? esc_html($order_ctx['phone']) : '—'; ?></strong>
							</div>
							<div class="order-customer-panel__item<?php echo !empty($order_ctx['pending_balance']) ? ' is-warning' : ''; ?>">
								<span><?php echo t('order_customer_pending'); ?></span>
								<strong><?php echo !empty($order_ctx['pending_balance']) ? esc_html(number_format((float) $order_ctx['pending_balance'])) : '0'; ?></strong>
							</div>
							<div class="order-customer-panel__item">
								<span><?php echo t('order_customer_orders'); ?></span>
								<strong><?php echo (int) (isset($order_ctx['order_count']) ? $order_ctx['order_count'] : 0); ?></strong>
							</div>
							<div class="order-customer-panel__item">
								<span><?php echo t('order_customer_last_order'); ?></span>
								<strong><?php
									if (!empty($order_ctx['has_last_order'])) {
										echo '#'.(int) $order_ctx['last_order_no'].' · '.esc_html(date('d-M-Y', strtotime($order_ctx['last_order_date'])));
									} else {
										echo '—';
									}
								?></strong>
							</div>
						</div>
					</div>

					<div class="order-section order-section--details">
						<div class="order-section__head">
							<h3 class="order-section__title"><?php echo t('order_section_details'); ?></h3>
						</div>
					<?php /* Naap + customer already shown in customer panel — keep submit values hidden. */ ?>
					<input id="Naap12no" name="Naap12no" type="hidden" value="<?php echo $order_measurement_row ? esc_html((string) $order_measurement_row->MANNAAPNO) : ''; ?>">
					<input id="Naapno" name="Naapno" type="hidden" value="<?php echo $order_measurement_row ? (int) $order_measurement_row->MCODE : ''; ?>">
					<input id="order_customer_name_display" name="customer_name" type="hidden" value="<?php echo esc_html($order_customer_display_name); ?>">
					<input id="customer_NO" name="customer_NO" type="hidden" value="<?php echo $order_measurement_row ? (int) $order_measurement_row->Customerno : ''; ?>">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label mb-1" for="staff_employee"><?php echo t('order_staff_employee'); ?></label>
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
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label mb-1" for="current"><?php echo t('order_current_date'); ?></label>
								<input id="current" name="current" disabled type="text" class="form-control dz-date" value="<?php echo date('d-M-Y'); ?>" autocomplete="off">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label mb-1" for="retrun_Date"><?php echo t('order_return_date'); ?></label>
								<input id="retrun_Date" name="retrun_Date" type="text" class="form-control dz-date" required value="<?php echo esc_attr($order_default_return); ?>" autocomplete="off" placeholder="dd-Mon-yyyy">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label mb-1" for="Quantity"><?php echo t('app_quantity'); ?></label>
								<input id="Quantity" name="Quantity" type="number" min="1" class="form-control" required value="">
							</div>
						</div>
						<div class="col-md-12">
							<div id="order-booking-limit-alert" class="sufee-alert alert with-close alert-danger fade show d-none" role="alert" style="display:none;">
								<span class="order-booking-limit-alert__text"></span>
								<button type="button" class="close" aria-label="Close" onclick="clearOrderBookingLimitAlert(); return false;"><span aria-hidden="true">×</span></button>
							</div>
						</div>
					</div>
					</div>

					<div class="order-section">
						<div class="order-section__head">
							<h3 class="order-section__title"><?php echo t('order_section_suits'); ?></h3>
						</div>
										<div id="maincat">
										
										</div>
					</div>

					<div class="order-section order-section--payment">
						<div class="order-section__head">
							<h3 class="order-section__title"><?php echo t('order_section_payment'); ?></h3>
						</div>
					<div class="row order-payment-fields">
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
					<div class="order-payment-sticky">
						<div class="order-payment-sticky__summary">
							<div class="order-payment-sticky__metric">
								<span><?php echo t('order_total_amount'); ?></span>
								<strong id="orderStickyTotal">0</strong>
							</div>
							<div class="order-payment-sticky__metric">
								<span><?php echo t('app_advance'); ?></span>
								<strong id="orderStickyAdvance">0</strong>
							</div>
							<div class="order-payment-sticky__metric">
								<span><?php echo t('app_discount'); ?></span>
								<strong id="orderStickyDiscount">0</strong>
							</div>
							<div class="order-payment-sticky__metric">
								<span><?php echo t('app_balance'); ?></span>
								<strong id="orderStickyBalance">0</strong>
							</div>
						</div>
						<div class="order-payment-sticky__actions">
							<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
								<span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
								<span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
							</button>
						</div>
					</div>
					</div>
</div>
                                </div>
                                <div class="col-lg-4 col-xl-3 order-form-camera-col">
                                  <div class="order-camera-panel" id="orderCameraPanel"
									data-avatar-female="<?php echo esc_attr(base_url('images/avatar-female.svg')); ?>"
									data-avatar-male="<?php echo esc_attr(base_url('images/avatar-male.svg')); ?>">
                                    <div class="order-camera-panel__head"><?php echo t('order_camera'); ?></div>
                                    <div class="order-camera-panel__view">
										<div id="my_camera" class="order-camera-panel__stage"></div>
										<img id="orderAvatarPreview" class="order-camera-panel__avatar" alt="" hidden>
									</div>
									<p class="order-camera-status" id="orderCameraStatus" hidden role="status"></p>
									<label class="order-camera-avatar-toggle">
										<input type="checkbox" id="orderUseAvatar" name="use_avatar" value="1">
										<span>
											<strong><?php echo t('order_use_avatar'); ?></strong>
											<small><?php echo t('order_use_avatar_hint'); ?></small>
										</span>
									</label>
									<div class="order-camera-avatar-choices" id="orderAvatarChoices" hidden>
										<label class="order-camera-avatar-choice">
											<input type="radio" name="avatar_gender" value="female" checked>
											<span><?php echo t('order_avatar_female'); ?></span>
										</label>
										<label class="order-camera-avatar-choice">
											<input type="radio" name="avatar_gender" value="male">
											<span><?php echo t('order_avatar_male'); ?></span>
										</label>
									</div>
                                  <div class="order-camera-panel__actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-block" id="orderCapturePhotoBtn"><?php echo t('order_capture_photo'); ?></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="orderRetryCameraBtn" hidden></button>
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

<div class="modal fade" id="orderPreviewModal" tabindex="-1" role="dialog" aria-labelledby="orderPreviewModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="orderPreviewModalLabel"><?php echo t('order_preview_title'); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="orderPreviewBody"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo t('order_preview_edit'); ?></button>
				<button type="button" class="btn btn-primary" id="orderPreviewConfirmBtn"><?php echo t('order_preview_confirm'); ?></button>
			</div>
		</div>
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
window.orderAvatarModeActive = false;
window.__orderWebcamLive = false;
window.__orderWebcamInitialized = false;
window.__orderWebcamFallbackTried = false;
window.__orderPhotoCaptured = false;
window.__orderWebcamHandlersBound = false;

function getOrderAvatarGender()
{
	var selected = document.querySelector('input[name="avatar_gender"]:checked');
	return selected ? selected.value : 'female';
}

function getOrderAvatarUrl()
{
	var panel = document.getElementById('orderCameraPanel');
	if (!panel)
	{
		return '';
	}
	return getOrderAvatarGender() === 'male'
		? (panel.getAttribute('data-avatar-male') || '')
		: (panel.getAttribute('data-avatar-female') || '');
}

function setOrderCameraStatus(message, isError)
{
	var el = document.getElementById('orderCameraStatus');
	var retryBtn = document.getElementById('orderRetryCameraBtn');
	if (!el)
	{
		return;
	}
	if (!message)
	{
		el.hidden = true;
		el.textContent = '';
		el.classList.remove('is-error');
		if (retryBtn)
		{
			retryBtn.hidden = true;
		}
		return;
	}
	el.hidden = false;
	el.textContent = message;
	el.classList.toggle('is-error', !!isError);
	if (retryBtn)
	{
		retryBtn.hidden = !isError;
		retryBtn.textContent = orderLang('order_camera_retry', 'Retry camera');
	}
}

window.applyOrderAvatarImage = function (done)
{
	var url = getOrderAvatarUrl();
	var preview = document.getElementById('orderAvatarPreview');
	if (!url)
	{
		if (typeof done === 'function')
		{
			done('');
		}
		return;
	}
	if (preview)
	{
		preview.src = url;
		preview.hidden = false;
	}
	var img = new Image();
	img.onload = function () {
		var canvas = document.createElement('canvas');
		canvas.width = 400;
		canvas.height = 400;
		var ctx = canvas.getContext('2d');
		ctx.fillStyle = '#ffffff';
		ctx.fillRect(0, 0, 400, 400);
		try
		{
			ctx.drawImage(img, 0, 0, 400, 400);
			var dataUri = canvas.toDataURL('image/jpeg', 0.92);
			setOrderImageData(dataUri);
			if (preview)
			{
				preview.src = dataUri;
			}
			window.__orderPhotoCaptured = true;
			if (typeof done === 'function')
			{
				done(dataUri);
			}
		}
		catch (err)
		{
			if (typeof done === 'function')
			{
				done('');
			}
		}
	};
	img.onerror = function () {
		if (typeof done === 'function')
		{
			done('');
		}
	};
	img.src = url;
};

function setOrderAvatarMode(enabled)
{
	window.orderAvatarModeActive = !!enabled;
	var panel = document.getElementById('orderCameraPanel');
	var preview = document.getElementById('orderAvatarPreview');
	var choices = document.getElementById('orderAvatarChoices');
	var stage = document.getElementById('my_camera');
	var captureBtn = document.getElementById('orderCapturePhotoBtn');
	if (panel)
	{
		panel.classList.toggle('is-avatar-mode', !!enabled);
	}
	if (choices)
	{
		choices.hidden = !enabled;
	}
	if (captureBtn)
	{
		captureBtn.disabled = !!enabled;
	}
	if (enabled)
	{
		try
		{
			if (window.Webcam && (window.Webcam.live || window.Webcam.loaded))
			{
				Webcam.reset();
			}
		}
		catch (err) {}
		window.__orderWebcamInitialized = false;
		window.__orderWebcamLive = false;
		if (stage)
		{
			stage.innerHTML = '';
		}
		setOrderCameraStatus('');
		window.applyOrderAvatarImage();
	}
	else
	{
		if (preview)
		{
			preview.hidden = true;
			preview.removeAttribute('src');
		}
		var imgtxt = document.getElementById('imgtxt');
		if (imgtxt)
		{
			imgtxt.value = '';
		}
		window.__orderPhotoCaptured = false;
		window.__orderWebcamFallbackTried = false;
		window.setTimeout(function () {
			initOrderWebcam(true);
		}, 200);
	}
}

function bindOrderAvatarControls()
{
	var toggle = document.getElementById('orderUseAvatar');
	if (!toggle || toggle.getAttribute('data-avatar-bound') === '1')
	{
		return;
	}
	toggle.setAttribute('data-avatar-bound', '1');
	toggle.addEventListener('change', function () {
		setOrderAvatarMode(this.checked);
	});
	var radios = document.querySelectorAll('input[name="avatar_gender"]');
	for (var i = 0; i < radios.length; i++)
	{
		radios[i].addEventListener('change', function () {
			if (window.orderAvatarModeActive)
			{
				window.applyOrderAvatarImage();
			}
		});
	}
	var retryBtn = document.getElementById('orderRetryCameraBtn');
	if (retryBtn && retryBtn.getAttribute('data-retry-bound') !== '1')
	{
		retryBtn.setAttribute('data-retry-bound', '1');
		retryBtn.addEventListener('click', function () {
			window.__orderWebcamFallbackTried = false;
			initOrderWebcam(true);
		});
	}
	setOrderAvatarMode(toggle.checked);
}

function bindOrderWebcamHandlers()
{
	if (!window.Webcam || window.__orderWebcamHandlersBound)
	{
		return;
	}
	window.__orderWebcamHandlersBound = true;
	Webcam.on('live', function () {
		window.__orderWebcamLive = true;
		window.__orderWebcamInitialized = true;
		setOrderCameraStatus('');
	});
	Webcam.on('error', function (err) {
		if (window.console && console.error)
		{
			console.error('Webcam error:', err);
		}
		window.__orderWebcamLive = false;
		window.__orderWebcamInitialized = false;
		if (!window.__orderWebcamFallbackTried && !window.orderAvatarModeActive)
		{
			window.__orderWebcamFallbackTried = true;
			window.setTimeout(function () {
				tryAttachOrderWebcam(true);
			}, 150);
			return;
		}
		setOrderCameraStatus(
			orderLang('order_camera_unavailable', 'Camera could not start. Check permission/device, tap Retry, or use the default avatar.'),
			true
		);
	});
}

function tryAttachOrderWebcam(useSimpleConstraints)
{
	var view = document.querySelector('.order-camera-panel__view');
	var stage = document.getElementById('my_camera');
	if (!view || !stage || !window.Webcam || window.orderAvatarModeActive)
	{
		return;
	}

	var size = Math.floor(view.clientWidth || view.offsetWidth || 0);
	if (size < 200)
	{
		size = 280;
	}
	var dest = Math.min(Math.max(size, 240), 480);

	try
	{
		if (window.Webcam.live || window.Webcam.loaded)
		{
			Webcam.reset();
		}
	}
	catch (err) {}

	stage.innerHTML = '';
	setOrderCameraStatus(orderLang('order_camera_starting', 'Starting camera…'), false);

	var settings = {
		width: size,
		height: size,
		dest_width: dest,
		dest_height: dest,
		image_format: 'jpeg',
		jpeg_quality: 90,
		force_flash: false,
		enable_flash: false,
		constraints: useSimpleConstraints
			? true
			: {
				facingMode: 'user',
				width: { ideal: 640 },
				height: { ideal: 480 }
			}
	};
	Webcam.set(settings);
	bindOrderWebcamHandlers();
	try
	{
		Webcam.attach('#my_camera');
	}
	catch (attachErr)
	{
		window.__orderWebcamInitialized = false;
		window.__orderWebcamLive = false;
		setOrderCameraStatus(
			orderLang('order_camera_unavailable', 'Camera could not start. Check permission/device, tap Retry, or use the default avatar.'),
			true
		);
	}
}

function initOrderWebcam(force)
{
	if (window.orderAvatarModeActive)
	{
		return;
	}
	if (window.__orderWebcamInitialized && window.__orderWebcamLive && !force)
	{
		return;
	}
	if (!window.Webcam)
	{
		setOrderCameraStatus(
			orderLang('order_camera_unavailable', 'Camera could not start. Check permission/device, tap Retry, or use the default avatar.'),
			true
		);
		return;
	}
	window.__orderWebcamInitialized = false;
	window.__orderWebcamLive = false;
	if (force)
	{
		window.__orderWebcamFallbackTried = false;
	}
	tryAttachOrderWebcam(false);
}

if (document.getElementById('my_camera') || document.getElementById('orderUseAvatar'))
{
	window.addEventListener('load', function () {
		bindOrderAvatarControls();
		window.setTimeout(function () {
			initOrderWebcam(true);
		}, 250);
	});
	if (window.jQuery)
	{
		jQuery(document).on('app:ajax-loaded', function () {
			if (!document.getElementById('orderCameraPanel'))
			{
				return;
			}
			try
			{
				if (window.Webcam && (window.Webcam.live || window.Webcam.loaded))
				{
					Webcam.reset();
				}
			}
			catch (err) {}
			window.__orderWebcamInitialized = false;
			window.__orderWebcamLive = false;
			window.__orderWebcamFallbackTried = false;
			var toggle = document.getElementById('orderUseAvatar');
			if (toggle)
			{
				toggle.removeAttribute('data-avatar-bound');
			}
			var retryBtn = document.getElementById('orderRetryCameraBtn');
			if (retryBtn)
			{
				retryBtn.removeAttribute('data-retry-bound');
			}
			bindOrderAvatarControls();
			window.setTimeout(function () {
				initOrderWebcam(true);
			}, 300);
		});
	}
}

function take_snapshot() {
	if (window.orderAvatarModeActive) {
		if (typeof window.applyOrderAvatarImage === 'function') {
			window.applyOrderAvatarImage();
		}
		return;
	}
	if (!window.Webcam || !window.Webcam.live) {
		setOrderCameraStatus(
			orderLang('order_camera_unavailable', 'Camera could not start. Check permission/device, tap Retry, or use the default avatar.'),
			true
		);
		return;
	}
	snapOrderWebcam(function () {
		setOrderCameraStatus('');
	}, function () {
		setOrderCameraStatus(
			orderLang('order_camera_unavailable', 'Camera could not start. Check permission/device, tap Retry, or use the default avatar.'),
			true
		);
	});
}
</script>
<?php $this->load->view('partials/order_copy_suit_line_js'); ?>
<script>window.ORDER_FORM_CFG = <?php echo json_encode(array(
	'lastOrderUrl' => site_url('Order/customer_last_order_lines'),
), JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo base_url('js/order-form-enhancements.js?v=5'); ?>"></script>
 
</body>

</html>
<!-- end document-->
