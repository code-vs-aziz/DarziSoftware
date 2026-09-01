<?php
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$group = isset($group) ? (string) $group : (string) $this->uri->segment(5);
$group = $group === 'monthly' ? 'monthly' : 'daily';
$opening = isset($opening) ? (float) $opening : 0;
$rows_data = isset($rows) && is_array($rows) ? $rows : array();
$has_report = ($fromdate !== '' && $todate !== '');
$group_labels = array('daily' => 'Daily', 'monthly' => 'Monthly');

$rows = array();
$running_cash = $opening;
$total_inflow = 0;
$total_outflow = 0;
$total_delivery_paid = 0;
$latest_pending = 0;
$latest_pending_customers = 0;

foreach ($rows_data as $row) {
	$row_opening = $running_cash;
	$inflow = (float) $row->advance + (float) $row->cash_receipts;
	$outflow = (float) $row->cash_payments + (float) $row->expenses + (float) $row->salary;
	$running_cash = $running_cash + $inflow - $outflow;
	$delivery_paid = (float) $row->delivery_paid;
	$pending = (float) $row->pending_receivable;
	$pending_customers = (float) $row->pending_customers;
	$total_inflow += $inflow;
	$total_outflow += $outflow;
	$total_delivery_paid += $delivery_paid;
	$latest_pending = $pending;
	$latest_pending_customers = $pending_customers;
	$rows[] = array(
		$row->period_label,
		number_format($row_opening, 2),
		number_format($inflow, 2),
		number_format($outflow, 2),
		number_format($running_cash, 2),
		number_format($delivery_paid, 2),
		number_format($pending, 2),
		number_format($pending_customers),
	);
}

$report_title = 'Cash Flow & Receivables';
$report_form_action = site_url('Cash/cash_flow_receivable_pro');
$report_fields = array(
	array('label' => 'Group By', 'name' => 'group', 'type' => 'select', 'options' => $group_labels),
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Period', 'Opening Cash', 'Cash Inflow', 'Cash Outflow', 'Closing Cash', 'Delivery Paid', 'Pending Receivable', 'Pending Customers');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Group By', 'value' => $group_labels[$group]),
);
$report_totals = array(
	array('label' => 'Opening Cash', 'value' => number_format($opening, 2)),
	array('label' => 'Total Inflow', 'value' => number_format($total_inflow, 2)),
	array('label' => 'Total Outflow', 'value' => number_format($total_outflow, 2)),
	array('label' => 'Closing Cash', 'value' => number_format($running_cash, 2)),
	array('label' => 'Delivery Paid', 'value' => number_format($total_delivery_paid, 2)),
	array('label' => 'Pending Receivable', 'value' => number_format($latest_pending, 2)),
	array('label' => 'Pending Customers', 'value' => number_format($latest_pending_customers)),
	array('label' => 'Rows', 'value' => number_format(count($rows))),
);
$report_excel_url = site_url('Cash/cash_flow_receivable/'.$fromdate.'/'.$todate.'/'.$group.'?export=excel');
$report_pdf_url = site_url('Cash/cash_flow_receivable/'.$fromdate.'/'.$todate.'/'.$group.'?export=pdf');
$report_excel_filename = 'cash-flow-receivables-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No cash flow or receivable activity found for this date range.';
include(APPPATH.'views/report_layout.php');
 
