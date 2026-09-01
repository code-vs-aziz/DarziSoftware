<?php
$fromdate = (string) $this->uri->segment(3);
$todate = (string) $this->uri->segment(4);
$rtype = (string) $this->uri->segment(5);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$type_labels = array('Ready' => 'Ready For Delivered', 'Del' => 'Delivered');
$rows = array();
$total_amount = 0;
$sr = 0;

foreach ($payment as $payments) {
	$is_ready = isset($payments->status) && $payments->status === 'Deactive';
	if ($rtype === 'Ready' && !$is_ready) {
		continue;
	}
	if ($rtype === 'Del' && $is_ready) {
		continue;
	}
	$amount = (isset($payments->suitrate) ? (float) $payments->suitrate : 0)
		+ (isset($payments->drate) ? (float) $payments->drate : 0)
		+ (isset($payments->erate) ? (float) $payments->erate : 0);
	$total_amount += $amount;
	$sr++;
	$rows[] = array(
		$sr,
		isset($payments->ENDATE) && $payments->ENDATE !== '' ? date('d-m-Y', strtotime($payments->ENDATE)) : '',
		isset($payments->CUSTOMERNAME) ? $payments->CUSTOMERNAME : '',
		isset($payments->name) ? $payments->name : '',
		'Order #'.(string) $payments->ORDERNO.' Naap# '.(string) $payments->order_remarks.' '.(string) $payments->man_napno.' '.(string) $payments->narration,
		number_format($amount, 2),
		$is_ready ? 'Ready4Del' : 'Delivered',
	);
}

$report_title = 'Employee Wise Sale Report';
$report_form_action = site_url('Cash/all_sale_employee_sie_pro');
$report_fields = array(
	array('label' => 'From Date', 'name' => 'fromdate', 'type' => 'date'),
	array('label' => 'To Date', 'name' => 'todate', 'type' => 'date'),
	array('label' => 'Type', 'name' => 'rtype', 'type' => 'select', 'options' => $type_labels),
);
$report_columns = array('SR', 'Order Date', 'Customer', 'Employee', 'Narration', 'Order Amount', 'Status');
$report_rows = $rows;
$report_has_report = $has_report;
$report_summary = array(
	array('label' => 'From Date', 'value' => $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : ''),
	array('label' => 'To Date', 'value' => $todate !== '' ? date('d-m-Y', strtotime($todate)) : ''),
	array('label' => 'Type', 'value' => isset($type_labels[$rtype]) ? $type_labels[$rtype] : $rtype),
);
$report_totals = array(
	array('label' => 'Total Rows', 'value' => number_format(count($rows))),
	array('label' => 'Total Amount', 'value' => number_format($total_amount, 2)),
);
$report_excel_url = site_url('Cash/all_Sale_employee_wise/'.$fromdate.'/'.$todate.'/'.$rtype.'?export=excel');
$report_pdf_url = site_url('Cash/all_Sale_employee_wise/'.$fromdate.'/'.$todate.'/'.$rtype.'?export=pdf');
$report_excel_filename = 'employee-wise-sale-'.$fromdate.'-'.$todate.'.xls';
$report_empty_message = 'No employee-wise sale found for this date range.';
include(APPPATH.'views/report_layout.php');
