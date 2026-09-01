<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$type = (string) $this->uri->segment(5);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$type_labels = array('' => 'ALL', '3' => 'Shop Expense', '4' => 'House Expense', '5' => 'Nasir sb Expense', '6' => 'Extra Expense');
$rows = array();
$total = 0;

foreach ($payment as $payments) {
	$amount = isset($payments->amount) ? (float) $payments->amount : 0;
	$total += $amount;
	$row_type = isset($payments->type) ? (string) $payments->type : '';
	$rows[] = array(
		isset($payments->EDATE) && $payments->EDATE !== '' ? date('d-m-Y', strtotime($payments->EDATE)) : '',
		isset($payments->narration) ? $payments->narration : '',
		number_format($amount, 2),
		isset($type_labels[$row_type]) ? $type_labels[$row_type] : $row_type,
	);
}

$report_title = 'Expense Report';
$report_form_action = site_url('Cash/exp_ledger_pro');
$report_fields = array(
	array('label' => 'Expense Type', 'name' => 'type', 'type' => 'select', 'options' => $type_labels),
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Date', 'Narration', 'Amount', 'Type');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Type', 'value' => isset($type_labels[$type]) ? $type_labels[$type] : $type),
);
$report_totals = array(
	array('label' => 'Total Expense', 'value' => number_format($total, 2)),
	array('label' => 'Rows', 'value' => number_format(count($rows))),
);
$report_excel_url = site_url('Cash/exp_ledger/'.$fromdate.'/'.$todate.'/'.$type.'?export=excel');
$report_pdf_url = site_url('Cash/exp_ledger/'.$fromdate.'/'.$todate.'/'.$type.'?export=pdf');
$report_excel_filename = 'expense-report-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No expense found for this date range.';
include(APPPATH.'views/report_layout.php');
