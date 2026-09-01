<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$emp_type = (string) $this->uri->segment(5);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$rows = array();
$total_suit = 0;
$total_salary = 0;
$total_paid = 0;

foreach ($payment as $payments) {
	$suit = isset($payments->Total_SUIT) ? abs((float) $payments->Total_SUIT) : 0;
	$salary = isset($payments->Salary) ? abs((float) $payments->Salary) : 0;
	$paid = isset($payments->Paid) ? abs((float) $payments->Paid) : 0;
	if ($salary <= 0 && $paid <= 0) {
		continue;
	}
	$total_suit += $suit;
	$total_salary += $salary;
	$total_paid += $paid;
	$rows[] = array(
		isset($payments->empno) ? $payments->empno : '',
		isset($payments->name) ? $payments->name : '',
		number_format($suit),
		number_format($salary, 2),
		number_format($paid, 2),
	);
}

$net_label = ($emp_type === '10') ? 'Net Paid' : 'Net Payable';
$net_amount = $total_salary - $total_paid;
$report_title = 'Employee Summary Report';
$report_form_action = site_url('employee_rep/employee_report');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
);
$report_columns = array('Employee No', 'Employee', 'Total Suit', 'Salary', 'Payment');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
);
$report_totals = array(
	array('label' => 'Total Suit', 'value' => number_format($total_suit)),
	array('label' => 'Salary', 'value' => number_format($total_salary, 2)),
	array('label' => 'Payment', 'value' => number_format($total_paid, 2)),
	array('label' => $net_label, 'value' => number_format($net_amount, 2)),
);
$report_excel_url = site_url('employee_rep/employee_ledreport/'.$fromdate.'/'.$todate.'/'.$emp_type.'?export=excel');
$report_pdf_url = site_url('employee_rep/employee_ledreport/'.$fromdate.'/'.$todate.'/'.$emp_type.'?export=pdf');
$report_excel_filename = 'employee-summary-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No employee data found for this date range.';
include(APPPATH.'views/report_layout.php');
