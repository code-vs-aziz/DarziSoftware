<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$rows = array();
$total_suit = 0;
$total_amount = 0;
$sr = 0;

foreach ($payment as $payments) {
	$sr++;
	$suit_count = isset($payments->COunt_suit) ? (float) $payments->COunt_suit : 0;
	$amount = isset($payments->PAID) ? (float) $payments->PAID : 0;
	$total_suit += $suit_count;
	$total_amount += $amount;
	$status = (isset($payments->status) && $payments->status === 'Deactive') ? 'Ready4Del' : 'Delivered';
	$rows[] = array(
		$sr,
		isset($payments->ENDATE) && $payments->ENDATE !== '' ? date('d-m-Y', strtotime($payments->ENDATE)) : '',
		isset($payments->CUSTOMERNAME) ? $payments->CUSTOMERNAME : '',
		'Order #'.(string) $payments->ORDERNO.' Naap# '.(string) $payments->order_remarks.' '.(string) $payments->man_napno.' '.(string) $payments->narration,
		number_format($suit_count),
		number_format($amount, 2),
		$status,
	);
}

$report_title = 'All Delivery Order Report';
$report_form_action = site_url('Cash/all_del_ord_pro');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('SR', 'Order Date', 'Customer', 'Narration', 'Suit', 'Order Amount', 'Status');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
);
$report_totals = array(
	array('label' => 'Total Orders', 'value' => number_format(count($rows))),
	array('label' => 'Total Suit', 'value' => number_format($total_suit)),
	array('label' => 'Total Amount', 'value' => number_format($total_amount, 2)),
);
$report_excel_url = site_url('Cash/all_delivery_order/'.$fromdate.'/'.$todate.'?export=excel');
$report_pdf_url = site_url('Cash/all_delivery_order/'.$fromdate.'/'.$todate.'?export=pdf');
$report_excel_filename = 'all-delivery-order-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No delivery orders found for this date range.';
include(APPPATH.'views/report_layout.php');
