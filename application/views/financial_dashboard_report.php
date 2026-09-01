<?php
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$group = isset($group) ? (string) $group : (string) $this->uri->segment(5);
$group = $group === 'monthly' ? 'monthly' : 'daily';
$summary = isset($summary) && is_array($summary) ? $summary : array();
$rows_data = isset($rows) && is_array($rows) ? $rows : array();
$has_report = ($fromdate !== '' && $todate !== '');
$group_labels = array('daily' => 'Daily', 'monthly' => 'Monthly');

$period_rows = array();
foreach ($rows_data as $row) {
	$cash_inflow = (float) $row->advance + (float) $row->cash_receipts;
	$cash_outflow = (float) $row->cash_payments + (float) $row->expenses + (float) $row->salary;
	$period_rows[] = array(
		$row->period_label,
		number_format((float) $row->bookings),
		number_format((float) $row->booked_qty),
		number_format((float) $row->booked_sales, 2),
		number_format($cash_inflow, 2),
		number_format($cash_outflow, 2),
		number_format((float) $row->pending_receivable, 2),
		number_format($cash_inflow - $cash_outflow, 2),
	);
}

$report_title = 'Financial Dashboard';
$report_form_action = site_url('Cash/financial_dashboard_pro');
$report_fields = array(
	array('label' => 'Group By', 'name' => 'group', 'type' => 'select', 'options' => $group_labels),
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Period', 'Bookings', 'Qty', 'Booked Sales', 'Cash Inflow', 'Cash Outflow', 'Pending Receivable', 'Net Cash');
$report_rows = $period_rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Group By', 'value' => $group_labels[$group]),
);
$report_totals = array(
	array('label' => 'Booked Sales', 'value' => number_format(isset($summary['booked_sales']) ? $summary['booked_sales'] : 0, 2)),
	array('label' => 'Cash Inflow', 'value' => number_format(isset($summary['cash_inflow']) ? $summary['cash_inflow'] : 0, 2)),
	array('label' => 'Cash Outflow', 'value' => number_format(isset($summary['cash_outflow']) ? $summary['cash_outflow'] : 0, 2)),
	array('label' => 'Net Cash', 'value' => number_format(isset($summary['net_cash']) ? $summary['net_cash'] : 0, 2)),
	array('label' => 'Pending Receivable', 'value' => number_format(isset($summary['pending_receivable']) ? $summary['pending_receivable'] : 0, 2)),
	array('label' => 'Net Profit Estimate', 'value' => number_format(isset($summary['net_profit_estimate']) ? $summary['net_profit_estimate'] : 0, 2)),
	array('label' => 'Delivered Orders', 'value' => number_format(isset($summary['delivered_orders']) ? $summary['delivered_orders'] : 0)),
	array('label' => 'Bookings', 'value' => number_format(isset($summary['bookings']) ? $summary['bookings'] : 0)),
);
$report_excel_url = site_url('Cash/financial_dashboard/'.$fromdate.'/'.$todate.'/'.$group.'?export=excel');
$report_pdf_url = site_url('Cash/financial_dashboard/'.$fromdate.'/'.$todate.'/'.$group.'?export=pdf');
$report_excel_filename = 'financial-dashboard-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No financial activity found for this date range.';
include(APPPATH.'views/report_layout.php');
 
