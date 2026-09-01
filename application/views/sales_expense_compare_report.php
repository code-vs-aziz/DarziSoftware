<?php
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$group = isset($group) ? (string) $group : (string) $this->uri->segment(5);
$group = $group === 'monthly' ? 'monthly' : 'daily';
$rows_data = isset($rows) && is_array($rows) ? $rows : array();
$has_report = ($fromdate !== '' && $todate !== '');
$group_labels = array('daily' => 'Daily', 'monthly' => 'Monthly');

$rows = array();
$total_sales = 0;
$total_discount = 0;
$total_inflow = 0;
$total_expense = 0;
$total_salary = 0;
$total_payment = 0;
$total_net = 0;

foreach ($rows_data as $row) {
	$sales = (float) $row->booked_sales;
	$discount = (float) $row->discount;
	$net_sales = $sales - $discount;
	$cash_inflow = (float) $row->advance + (float) $row->cash_receipts;
	$expenses = (float) $row->expenses;
	$salary = (float) $row->salary;
	$payments = (float) $row->cash_payments;
	$net_result = $sales - $discount - $expenses - $salary - $payments;
	$total_sales += $sales;
	$total_discount += $discount;
	$total_inflow += $cash_inflow;
	$total_expense += $expenses;
	$total_salary += $salary;
	$total_payment += $payments;
	$total_net += $net_result;
	$rows[] = array(
		$row->period_label,
		number_format($sales, 2),
		number_format($discount, 2),
		number_format($net_sales, 2),
		number_format($cash_inflow, 2),
		number_format($expenses, 2),
		number_format($salary, 2),
		number_format($payments, 2),
		number_format($net_result, 2),
	);
}

$report_title = 'Sales vs Expense Comparison';
$report_form_action = site_url('Cash/sales_expense_compare_pro');
$report_fields = array(
	array('label' => 'Group By', 'name' => 'group', 'type' => 'select', 'options' => $group_labels),
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Period', 'Sales', 'Discount', 'Net Sales', 'Cash Inflow', 'Expenses', 'Salary', 'Payments', 'Net Result');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Group By', 'value' => $group_labels[$group]),
);
$report_totals = array(
	array('label' => 'Total Sales', 'value' => number_format($total_sales, 2)),
	array('label' => 'Discount', 'value' => number_format($total_discount, 2)),
	array('label' => 'Cash Inflow', 'value' => number_format($total_inflow, 2)),
	array('label' => 'Expenses', 'value' => number_format($total_expense, 2)),
	array('label' => 'Salary', 'value' => number_format($total_salary, 2)),
	array('label' => 'Payments', 'value' => number_format($total_payment, 2)),
	array('label' => 'Net Result', 'value' => number_format($total_net, 2)),
	array('label' => 'Rows', 'value' => number_format(count($rows))),
);
$report_excel_url = site_url('Cash/sales_expense_compare/'.$fromdate.'/'.$todate.'/'.$group.'?export=excel');
$report_pdf_url = site_url('Cash/sales_expense_compare/'.$fromdate.'/'.$todate.'/'.$group.'?export=pdf');
$report_excel_filename = 'sales-expense-comparison-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No sales or expense activity found for this date range.';
include(APPPATH.'views/report_layout.php');
 
