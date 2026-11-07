<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$emp_type_seg = (string) $this->uri->segment(5);
$emp_type_filter = isset($emp_type_filter) ? (string) $emp_type_filter : '';
if ($emp_type_filter === '' && in_array($emp_type_seg, array('Cutting', 'Stitching', 'Staff'), TRUE))
{
	$emp_type_filter = $emp_type_seg;
}
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
	$type_label = isset($payments->emp_type) ? trim((string) $payments->emp_type) : '';
	if ($type_label === '') {
		$type_label = '-';
	}
	$total_suit += $suit;
	$total_salary += $salary;
	$total_paid += $paid;
	$rows[] = array(
		isset($payments->empno) ? $payments->empno : '',
		isset($payments->name) ? $payments->name : '',
		$type_label,
		number_format($suit),
		number_format($salary, 2),
		number_format($paid, 2),
	);
}

$net_label = 'Net Payable';
$net_amount = $total_salary - $total_paid;
$report_title = 'Employee Summary Report';
$report_form_action = site_url('employee_rep/employee_report');
$report_filter_url = site_url('employee_rep/employee_ledreport');
$report_result_base = site_url('employee_rep/employee_ledreport');
$emp_type_url = ($emp_type_filter !== '') ? $emp_type_filter : 'all';
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
	array(
		'label' => 'Employee Type',
		'name' => 'emp_type',
		'type' => 'select',
		'options' => array(
			'all' => 'All',
			'Cutting' => 'Cutting',
			'Stitching' => 'Stitching',
			'Staff' => 'Staff',
		),
	),
);
$report_field_values = array(
	'fromdate' => $fromdate,
	'todate' => $todate,
	'emp_type' => $emp_type_url,
);
$report_columns = array('Employee No', 'Employee', 'Type', 'Total Suit', 'Salary', 'Payment');
$report_rows = $rows;
$report_has_report = $has_report;
$report_align_all = 'center';
$type_summary = ($emp_type_filter !== '') ? $emp_type_filter : 'All';
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Employee Type', 'value' => $type_summary),
);
$report_totals = array(
	array('label' => 'Total Suit', 'value' => number_format($total_suit)),
	array('label' => 'Salary', 'value' => number_format($total_salary, 2)),
	array('label' => 'Payment', 'value' => number_format($total_paid, 2)),
	array('label' => $net_label, 'value' => number_format($net_amount, 2)),
);
$report_excel_url = site_url('employee_rep/employee_ledreport/'.$fromdate.'/'.$todate.'/'.$emp_type_url.'?export=excel');
$report_pdf_url = site_url('employee_rep/employee_ledreport/'.$fromdate.'/'.$todate.'/'.$emp_type_url.'?export=pdf');
$report_excel_filename = 'employee-summary-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No employee data found for this date range.';
include(APPPATH.'views/report_layout.php');
