<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$stage_filter = isset($stage_filter) ? trim((string) $stage_filter) : '';
$has_report = ($fromdate !== '' && $todate !== '');
$rows = array();
$order_counter = array();
$status_counts = array();

$design_type_labels = array(
	'Kurta' => t('design_type_kurta'),
	'Pajama' => t('design_type_pajama'),
	'Pent' => t('design_type_pent'),
	'Shirt' => t('design_type_shirt'),
	'Coat' => t('design_type_coat'),
	'Sherwani' => t('design_type_sherwani'),
	'PentCoat' => t('design_type_pentcoat'),
	'Waistcoat' => t('design_type_waistcoat'),
);

$status_order = array('Booking', 'Cutting', 'HO2', 'Stitching', 'Ready 4 Del', 'Pending', 'Delivered');

foreach ($payment as $orderdata) {
	$ostatus = isset($orderdata->ostatus) ? (string) $orderdata->ostatus : '0';
	if ($ostatus !== '0' && $ostatus !== '') {
		continue;
	}
	$order_no = isset($orderdata->ORDERNO) ? (string) $orderdata->ORDERNO : '';
	if (!isset($order_counter[$order_no])) {
		$order_counter[$order_no] = 0;
	}
	$order_counter[$order_no]++;
	$dept = isset($orderdata->dept) ? strtoupper(trim((string) $orderdata->dept)) : '';
	$status = isset($orderdata->status) ? trim((string) $orderdata->status) : '';

	// Current location of this suit line.
	// DELIVER = delivered (old ERP often has no cash/dmst rows — never remap those to Pending).
	// Pending (udaar) only when user explicitly filters Pending + remaining balance.
	$remaining = isset($orderdata->remaining_balance) ? (float) $orderdata->remaining_balance : 0;
	if (strcasecmp($status, 'DELIVER') === 0) {
		if ($stage_filter === 'Pending') {
			if ($remaining <= 0.009) {
				continue;
			}
			$stage = 'Pending';
		} else {
			$stage = 'Delivered';
		}
	} elseif ($dept === 'STIT' && strcasecmp($status, 'Deactive') === 0) {
		$stage = 'Ready 4 Del';
	} elseif ($dept === 'HO') {
		$stage = 'Booking';
	} elseif ($dept === 'STIT') {
		$stage = 'Stitching';
	} elseif ($dept === 'CUT') {
		$stage = 'Cutting';
	} elseif ($dept === 'HO2') {
		$stage = 'HO2';
	} else {
		$stage = $dept !== '' ? $dept : 'Unknown';
	}

	if ($stage_filter !== '' && $stage !== $stage_filter) {
		continue;
	}
	// All Stages = open pipeline only (Booking→Ready). Delivered is opt-in via filter.
	if ($stage_filter === '' && $stage === 'Delivered') {
		continue;
	}

	if (!isset($status_counts[$stage])) {
		$status_counts[$stage] = 0;
	}
	$status_counts[$stage]++;

	$raw_type = isset($orderdata->suit_type) ? trim((string) $orderdata->suit_type) : '';
	$order_type = $raw_type;
	if ($raw_type !== '')
	{
		foreach ($design_type_labels as $eng => $urdu)
		{
			if (strcasecmp($raw_type, $eng) === 0)
			{
				$order_type = $urdu;
				break;
			}
		}
	}

	// Karigar = employee currently holding this stage (set at transfer).
	// Booking / HO2 are desk stages — no craftsman assigned.
	$karigar = isset($orderdata->karigar_name) ? trim((string) $orderdata->karigar_name) : '';
	if ($stage === 'Booking' || $stage === 'HO2') {
		$karigar = '—';
	} elseif ($karigar === '') {
		$karigar = '—';
	}

	$rows[] = array(
		$order_no.'-'.$order_counter[$order_no],
		isset($orderdata->RORDERDATE) && $orderdata->RORDERDATE !== '' ? date('d-m-Y', strtotime($orderdata->RORDERDATE)) : '',
		isset($orderdata->Phone) ? $orderdata->Phone : '',
		isset($orderdata->name) ? $orderdata->name : '',
		isset($orderdata->man_napno) ? $orderdata->man_napno : '',
		$order_type,
		$stage,
		$karigar,
	);
}

$report_totals = array();
foreach ($status_order as $status_label) {
	if (!empty($status_counts[$status_label])) {
		$report_totals[] = array(
			'label' => $status_label,
			'value' => number_format($status_counts[$status_label]),
		);
		unset($status_counts[$status_label]);
	}
}
foreach ($status_counts as $status_label => $count) {
	$report_totals[] = array(
		'label' => $status_label,
		'value' => number_format($count),
	);
}

// Multi-year HTML tables can freeze the browser — keep full totals, cap on-screen rows.
// Excel/PDF export still includes every row.
$export_mode_booking = strtolower(trim((string) $this->input->get('export', TRUE)));
$booking_full_rows = count($rows);
$html_row_cap = 5000;
$booking_rows_capped = FALSE;
if ($export_mode_booking !== 'excel' && $export_mode_booking !== 'pdf' && $booking_full_rows > $html_row_cap)
{
	$rows = array_slice($rows, 0, $html_row_cap);
	$booking_rows_capped = TRUE;
}

// Option values must be URL-safe (no spaces) — "Ready 4 Del" → Ready4Del.
$stage_options = array(
	'all' => 'All (except Delivered)',
	'Booking' => 'Booking',
	'Cutting' => 'Cutting',
	'HO2' => 'HO2',
	'Stitching' => 'Stitching',
	'Ready4Del' => 'Ready 4 Del',
	'Pending' => 'Pending',
	'Delivered' => 'Delivered only',
);
$stage_url = 'all';
if ($stage_filter === 'Ready 4 Del') {
	$stage_url = 'Ready4Del';
} elseif ($stage_filter !== '' && isset($stage_options[$stage_filter])) {
	$stage_url = $stage_filter;
} elseif ($stage_filter !== '') {
	$stage_url = str_replace(' ', '', $stage_filter);
}

$report_title = 'Booking Status Report';
$report_form_action = site_url('employee_rep/booking');
$report_filter_url = site_url('employee_rep/booking_ledreport');
$report_result_base = site_url('employee_rep/booking_ledreport');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
	array(
		'label' => 'Status / Stage',
		'name' => 'stage',
		'type' => 'select',
		'options' => $stage_options,
	),
);
$report_field_values = array(
	'fromdate' => $fromdate,
	'todate' => $todate,
	'stage' => $stage_url,
);
// Filter = Return Date (RDATE) — same as old ERP.
$report_columns = array('Order', 'Return Date', 'Phone', 'Customer', 'Naap No', 'Order Type', 'Status', 'Karigar');
$report_rows = $rows;
$report_has_report = $has_report;
$report_hide_date_presets = TRUE;
$report_no_default_dates = TRUE;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Status / Stage', 'value' => ($stage_filter !== '') ? $stage_filter : 'All (except Delivered)'),
	array('label' => 'Rows', 'value' => number_format($booking_full_rows)),
);
if ( ! empty($booking_rows_capped))
{
	$report_summary[] = array(
		'label' => 'Screen',
		'value' => 'Showing first '.number_format($html_row_cap).' rows — use Export Excel for full '.$booking_full_rows.' rows',
	);
}
$report_excel_url = site_url('employee_rep/booking_ledreport/'.$fromdate.'/'.$todate.'/'.rawurlencode($stage_url).'?export=excel');
$report_pdf_url = site_url('employee_rep/booking_ledreport/'.$fromdate.'/'.$todate.'/'.rawurlencode($stage_url).'?export=pdf');
$report_excel_filename = 'booking-status-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No booking status data found for this return-date range. Select From / To dates and submit.';
include(APPPATH.'views/report_layout.php');
