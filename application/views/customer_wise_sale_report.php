<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$rows = array();
$total_amount = 0;
$total_qty = 0;
$sr = 0;

foreach ($payment as $payments) {
	$qty = isset($payments->TOTAL_QTY) ? (float) $payments->TOTAL_QTY : 0;
	if ($qty <= 0) {
		continue;
	}
	$amount = isset($payments->TOTAL_AMOUNT) ? (float) $payments->TOTAL_AMOUNT : 0;
	$sr++;
	$total_qty += $qty;
	$total_amount += $amount;
	$rows[] = array(
		$sr,
		trim((string) $payments->man_napno.' '.(string) $payments->CUSTOMER_NAME),
		number_format($qty),
		number_format($amount, 2),
	);
}

$report_title = 'Customer Wise Sale Report';
$report_form_action = site_url('Cash/customer_sale_pro');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('SR', 'Customer Name', 'Total Suit', 'Total Amount');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
);
$report_totals = array(
	array('label' => 'Total Customers', 'value' => number_format(count($rows))),
	array('label' => 'Total Suit', 'value' => number_format($total_qty)),
	array('label' => 'Total Amount', 'value' => number_format($total_amount, 2)),
);
$report_excel_url = site_url('Cash/rep_customer_sale/'.$fromdate.'/'.$todate.'?export=excel');
$report_pdf_url = site_url('Cash/rep_customer_sale/'.$fromdate.'/'.$todate.'?export=pdf');
$report_excel_filename = 'customer-wise-sale-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No customer sale found for this date range.';
include(APPPATH.'views/report_layout.php');
