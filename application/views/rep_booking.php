<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$payment = isset($payment) && is_array($payment) ? $payment : array();
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
	'Waistcoat' => t('design_type_waistcoat'),
);

foreach ($payment as $orderdata) {
	if (!isset($orderdata->ostatus) || (string) $orderdata->ostatus !== '0') {
		continue;
	}
	$order_no = isset($orderdata->ORDERNO) ? (string) $orderdata->ORDERNO : '';
	if (!isset($order_counter[$order_no])) {
		$order_counter[$order_no] = 0;
	}
	$order_counter[$order_no]++;
	$dept = isset($orderdata->dept) ? (string) $orderdata->dept : '';
	$status = isset($orderdata->status) ? (string) $orderdata->status : '';
	if ($status === 'Deactive') {
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
	if (!isset($status_counts[$stage])) {
		$status_counts[$stage] = 0;
	}
	$status_counts[$stage]++;

	$raw_type = isset($orderdata->suit_type) ? trim((string) $orderdata->suit_type) : '';
	$suit_type = ($raw_type !== '' && isset($design_type_labels[$raw_type]))
		? $design_type_labels[$raw_type]
		: $raw_type;
	$suit_name = isset($orderdata->suit_name) ? trim((string) $orderdata->suit_name) : '';
	if ($suit_type !== '' && $suit_name !== '') {
		$order_type = $suit_type.' — '.$suit_name;
	} elseif ($suit_type !== '') {
		$order_type = $suit_type;
	} else {
		$order_type = $suit_name;
	}

	$karigar = isset($orderdata->karigar_name) ? trim((string) $orderdata->karigar_name) : '';
	if ($karigar === '' && ($stage === 'Booking' || $stage === 'HO2')) {
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
$status_order = array('Booking', 'Cutting', 'HO2', 'Stitching', 'Ready 4 Del');
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

$report_title = 'Booking Status Report';
$report_form_action = site_url('employee_rep/booking');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Order', 'Return Date', 'Phone', 'Customer', 'Naap No', 'Order Type', 'Status', 'Karigar');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
);
$report_excel_url = site_url('employee_rep/booking_ledreport/'.$fromdate.'/'.$todate.'?export=excel');
$report_pdf_url = site_url('employee_rep/booking_ledreport/'.$fromdate.'/'.$todate.'?export=pdf');
$report_excel_filename = 'booking-status-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No booking status data found for this date range.';
include(APPPATH.'views/report_layout.php');
