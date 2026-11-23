<?php
$export_mode = isset($export_mode) ? (string) $export_mode : '';
$is_export = ($export_mode === 'excel' || $export_mode === 'pdf');
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$employee_id = isset($employee_id) ? (string) $employee_id : (string) $this->uri->segment(5);
$employee_name = isset($employee_name) ? (string) $employee_name : '';
$empopening = isset($empopening) ? (float) $empopening : 0;
$payment = isset($payment) && is_array($payment) ? $payment : array();
$employee = isset($employee) && is_array($employee) ? $employee : array();
$emp_status_filter = isset($emp_status_filter) ? (string) $emp_status_filter : 'All';
if (!in_array($emp_status_filter, array('All', 'Active', 'Inactive'), TRUE))
{
	$emp_status_filter = 'All';
}
$has_report = ($fromdate !== '' && $todate !== '' && $employee_id !== '');
$employee_record_status_key = isset($employee_record_status) ? (string) $employee_record_status : '';
$employee_record_status_label = '';
if ($employee_record_status_key === 'Inactive') {
	$employee_record_status_label = t('master_employee_status_inactive');
} elseif ($employee_record_status_key === 'Active') {
	$employee_record_status_label = t('master_employee_status_active');
}

$from_label = $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : '';
$to_label = $todate !== '' ? date('d-m-Y', strtotime($todate)) : '';
$excel_url = $has_report ? site_url('Cash/employee_ledger/'.$fromdate.'/'.$todate.'/'.$employee_id.'?export=excel') : '#';
$pdf_url = $has_report ? site_url('Cash/employee_ledger/'.$fromdate.'/'.$todate.'/'.$employee_id.'?export=pdf') : '#';

$rows = array();
$wages_total = 0;
$payment_total = 0;
$sr = 0;
foreach ($payment as $payments) {
	$sr++;
	$type = isset($payments->type) ? (int) $payments->type : 0;
	$price = isset($payments->price) ? (float) $payments->price : 0;
	$amount = isset($payments->amount) ? (float) $payments->amount : 0;
	$wages = 0;
	$paid = 0;
	$is_cash_row = ($price == 0 && $amount != 0);

	if ($type === 7) {
		$wages = abs($amount);
	} elseif ($price != 0) {
		$wages = $price;
	}

	// type 1 = payment TO employee (reduces payable); type 2 = receipt FROM employee (increases payable).
	// Amounts are stored positive; direction comes from type.
	if ($type === 1) {
		$paid = abs($amount);
	} elseif ($type === 2) {
		$paid = -1 * abs($amount);
	}

	$wages_total += $wages;
	$payment_total += $paid;

	$order_no = isset($payments->ORDERNO) ? (string) $payments->ORDERNO : '';
	$naap_no = isset($payments->man_napno) ? (string) $payments->man_napno : '';
	$order_remarks = isset($payments->order_remarks) ? (string) $payments->order_remarks : '';
	$narration = isset($payments->narration) ? (string) $payments->narration : '';
	if ($is_cash_row) {
		$direction_label = '';
		if ($type === 1) {
			$direction_label = t('cash_emp_payment');
		} elseif ($type === 2) {
			$direction_label = t('cash_emp_receipt');
		}
		$description = trim($narration);
		if ($description === '' && $direction_label !== '') {
			$description = $direction_label;
		} elseif ($description !== '' && $direction_label !== '') {
			$description = $direction_label.' — '.$description;
		}
	} else {
		$description = trim('Order #'.$order_no.' Naap # '.$naap_no.' '.$order_remarks.' '.$narration);
	}

	$rows[] = array(
		'sr' => $sr,
		'date' => isset($payments->ENDATE) && $payments->ENDATE !== '' ? date('d-m-Y', strtotime($payments->ENDATE)) : '',
		'description' => $description,
		'wages' => $wages,
		'payment' => $paid,
	);
}

$net_payable = $empopening + $wages_total - $payment_total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Employee Ledger</title>
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>?v=portrait-a4-2">
<style>
	body.report-body { background: #f4f7fb; color: #1f2933; font-family: Arial, sans-serif; }
	.report-shell { max-width: 1180px; margin: 18px auto; padding: 0 14px; }
	.report-card { background: #fff; border: 1px solid #e6edf5; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 35, 65, .08); overflow: hidden; }
	.report-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #eef7f8; border-bottom: 1px solid #dcebee; }
	.report-btn { border: 0; border-radius: 8px; padding: 9px 16px; color: #fff; background: #16a1ad; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
	.report-btn.secondary { background: #5f6f7a; }
	.report-btn.ghost { background: #fff; color: #0f766e; border: 1px solid #99f6e4; }
	.report-header { display: grid; grid-template-columns: 180px 1fr 180px; align-items: center; gap: 12px; padding: 18px 24px 12px; border-bottom: 1px solid #e6edf5; }
	.report-logo-small { max-height: 120px; max-width: 160px; }
	.report-logo-main { max-height: 120px; max-width: 420px; }
	.report-brand { text-align: center; }
	.report-brand p { margin: 4px 0; }
	.report-pill { display: inline-block; background: #111; color: #fff; border-radius: 10px; padding: 4px 12px; margin: 4px; font-weight: bold; }
	.report-title { padding: 16px 24px; display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; border-bottom: 1px solid #e6edf5; }
	.report-title h2 { margin: 0; font-size: 24px; }
	.report-meta { color: #52616d; font-size: 14px; line-height: 1.7; }
	.report-form { padding: 20px 24px; border-bottom: 1px solid #e6edf5; }
	.report-form-grid { display: grid; grid-template-columns: repeat(5, minmax(130px, 1fr)); gap: 14px; align-items: end; }
	.report-field label { display: block; font-weight: 700; margin-bottom: 5px; }
	.report-field input, .report-field select { width: 100%; border: 1px solid #d7e0ea; border-radius: 8px; padding: 9px 10px; }
	.report-table-wrap { padding: 18px 24px 24px; }
	.report-table { width: 100%; border-collapse: collapse; font-size: 16px; line-height: 1.45; }
	.report-table th { background: #1f9faa; color: #fff; padding: 12px 10px; border: 1px solid #128995; text-align: center; font-size: 16px; font-weight: 800; }
	.report-table td { padding: 11px 10px; border: 1px solid #dde6ef; font-size: 16px; font-weight: 600; color: #111827; vertical-align: middle; }
	.report-table .num { text-align: right; }
	.report-table .center { text-align: center; }
	.report-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0; }
	.report-summary div { background: #f7fbfc; border: 1px solid #dfecef; border-radius: 10px; padding: 12px; }
	.report-summary span { display: block; color: #52616d; font-size: 12px; }
	.report-summary strong { font-size: 18px; }
	.report-footer-note { background: #d9d9d9; padding: 8px 20px; display: flex; justify-content: space-between; gap: 10px; font-weight: 700; }
	.signature-line { margin: 45px 24px 22px; width: 280px; border-top: 1px dotted #222; text-align: center; padding-top: 5px; }
	.empty-state { padding: 24px; color: #687782; text-align: center; }
	.text-right { text-align: right; }
</style>
</head>
<body class="report-body">
<div class="report-shell">
	<div class="report-card">
		<?php
		$emp_filter_q = array();
		if ($fromdate !== '') { $emp_filter_q['fromdate'] = $fromdate; }
		if ($todate !== '') { $emp_filter_q['todate'] = $todate; }
		if ($employee_id !== '') { $emp_filter_q['employee'] = $employee_id; }
		if ($emp_status_filter !== '' && $emp_status_filter !== 'All') {
			$emp_filter_q['emp_status'] = $emp_status_filter;
		}
		$emp_filter_url = site_url('Cash/reprtemployee_ledger');
		if ( ! empty($emp_filter_q)) {
			$emp_filter_url .= '?'.http_build_query($emp_filter_q);
		}
		// Prefill when landing on filter screen via Back.
		if ( ! $has_report) {
			$g_from = trim((string) $this->input->get('fromdate', TRUE));
			$g_to = trim((string) $this->input->get('todate', TRUE));
			$g_emp = trim((string) $this->input->get('employee', TRUE));
			$g_st = trim((string) $this->input->get('emp_status', TRUE));
			if ($g_from !== '') { $fromdate = $g_from; }
			if ($g_to !== '') { $todate = $g_to; }
			if ($g_emp !== '') { $employee_id = $g_emp; }
			if (in_array($g_st, array('All', 'Active', 'Inactive'), TRUE)) {
				$emp_status_filter = $g_st;
			}
		}
		?>
		<?php if ($export_mode !== 'excel') { ?>
		<div class="report-actions print-hide">
			<?php if ($has_report) { ?>
			<a class="report-btn ghost" href="<?php echo esc_html($emp_filter_url); ?>"><?php echo esc_html(t('report_back_filters')); ?></a>
			<?php } ?>
			<button type="button" class="report-btn" onclick="window.print()">Print / PDF</button>
			<?php if ($has_report) { ?>
			<a class="report-btn secondary" href="<?php echo $excel_url; ?>">Export Excel</a>
			<a class="report-btn secondary" href="<?php echo $pdf_url; ?>" target="_blank">Open PDF View</a>
			<?php } ?>
		</div>
		<?php } ?>

		<?php $this->load->view('report_print_header'); ?>

		<div class="report-title">
			<div>
				<h2>Employee Ledger</h2>
				<div class="report-meta">
					Employee: <strong><?php echo esc_html($employee_name); ?></strong><?php if ($has_report && $employee_record_status_label !== '') { ?> (<?php echo esc_html($employee_record_status_label); ?>)<?php } ?><br>
					Period: <strong><?php echo esc_html($from_label); ?></strong> to <strong><?php echo esc_html($to_label); ?></strong>
				</div>
			</div>
			<div class="report-meta">
				Generated: <?php echo date('d-m-Y h:i A'); ?>
			</div>
		</div>

		<?php if ($export_mode !== 'excel' && $export_mode !== 'pdf') { ?>
		<form class="report-form print-hide" action="<?php echo site_url('Cash/employee_ledger_pro'); ?>" method="post" data-no-ajax="true" id="emp-ledger-filter-form">
			<?php $this->load->view('partials/csrf_field'); ?>
			<?php if ($has_report) { ?>
			<p class="report-meta" style="margin:0 0 10px;"><?php echo esc_html(t('report_change_filters')); ?></p>
			<?php } ?>
			<div class="report-form-grid">
				<div class="report-field">
					<label><?php echo t('report_employee_status'); ?></label>
					<select name="emp_status" id="emp_status_filter" onchange="window.location='<?php echo site_url('Cash/reprtemployee_ledger'); ?>?emp_status='+encodeURIComponent(this.value);">
						<option value="All"<?php echo $emp_status_filter === 'All' ? ' selected' : ''; ?>><?php echo t('report_employee_status_all'); ?></option>
						<option value="Active"<?php echo $emp_status_filter === 'Active' ? ' selected' : ''; ?>><?php echo t('master_employee_status_active'); ?></option>
						<option value="Inactive"<?php echo $emp_status_filter === 'Inactive' ? ' selected' : ''; ?>><?php echo t('master_employee_status_inactive'); ?></option>
					</select>
				</div>
				<div class="report-field">
					<label>From Date</label>
					<input type="date" name="fromdate" value="<?php echo esc_attr($fromdate); ?>" required>
				</div>
				<div class="report-field">
					<label>To Date</label>
					<input type="date" name="todate" value="<?php echo esc_attr($todate); ?>" required>
				</div>
				<div class="report-field">
					<label><?php echo t('report_employee'); ?></label>
					<select name="employee" required>
						<option value=""><?php echo t('report_select_employee'); ?></option>
						<?php foreach ($employee as $employees) { ?>
						<option value="<?php echo esc_html((string) $employees->empno); ?>"<?php echo ((string) $employees->empno === (string) $employee_id) ? ' selected' : ''; ?>><?php echo esc_html((string) $employees->name); ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="report-field">
					<button type="submit" class="report-btn" style="width:100%;"><?php echo esc_html(t('report_generate')); ?></button>
				</div>
			</div>
		</form>
		<script>
		(function () {
			var form = document.getElementById('emp-ledger-filter-form');
			if (!form) return;
			function buildEmpLedgerUrl() {
				var from = String((form.querySelector('[name="fromdate"]') || {}).value || '').trim();
				var to = String((form.querySelector('[name="todate"]') || {}).value || '').trim();
				var emp = String((form.querySelector('[name="employee"]') || {}).value || '').trim();
				var st = String((form.querySelector('[name="emp_status"]') || {}).value || 'All').trim();
				if (!from || !to || !emp) return '';
				var url = <?php echo json_encode(site_url('Cash/employee_ledger')); ?> + '/' + encodeURIComponent(from) + '/' + encodeURIComponent(to) + '/' + encodeURIComponent(emp);
				if (st && st !== 'All') url += '?emp_status=' + encodeURIComponent(st);
				return url;
			}
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var url = buildEmpLedgerUrl();
				if (!url) return;
				window.location.href = url;
			});
			<?php if ( ! $has_report) { ?>
			// Refresh recovery: old history bug left filter URL in the bar with params filled.
			var navEntry = (window.performance && performance.getEntriesByType)
				? performance.getEntriesByType('navigation')[0]
				: null;
			var isReload = (navEntry && navEntry.type === 'reload')
				|| (window.performance && performance.navigation && performance.navigation.type === 1);
			if (isReload) {
				var recoveryUrl = buildEmpLedgerUrl();
				if (recoveryUrl) {
					window.location.replace(recoveryUrl);
				}
			}
			<?php } ?>
		})();
		</script>
		<?php } ?>

		<?php if ($has_report) { ?>
		<div class="report-table-wrap">
			<div class="report-summary">
				<div><span>Opening Balance</span><strong><?php echo number_format($empopening, 2); ?></strong></div>
				<div><span>Total Wages</span><strong><?php echo number_format($wages_total, 2); ?></strong></div>
				<div><span>Total Payment</span><strong><?php echo number_format($payment_total, 2); ?></strong></div>
				<div><span>Net Payable</span><strong><?php echo number_format($net_payable, 2); ?></strong></div>
			</div>
			<table class="report-table">
				<thead>
					<tr>
						<th style="width:5%;">SR</th>
						<th style="width:12%;"><?php echo t('report_date'); ?></th>
						<th><?php echo t('report_narration'); ?></th>
						<th style="width:14%;"><?php echo t('report_wages'); ?></th>
						<th style="width:14%;"><?php echo t('report_payment'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="center"></td>
						<td class="center"></td>
						<td><strong>Opening Balance</strong></td>
						<td colspan="2" class="num"><strong><?php echo number_format($empopening, 2); ?></strong></td>
					</tr>
					<?php if (!empty($rows)) { ?>
						<?php foreach ($rows as $row) { ?>
						<tr>
							<td class="center"><?php echo (int) $row['sr']; ?></td>
							<td class="center"><?php echo esc_html($row['date']); ?></td>
							<td><?php echo esc_html($row['description']); ?></td>
							<td class="num"><?php echo $row['wages'] != 0 ? number_format($row['wages'], 2) : ''; ?></td>
							<td class="num"><?php echo $row['payment'] != 0 ? number_format($row['payment'], 2) : ''; ?></td>
						</tr>
						<?php } ?>
					<?php } else { ?>
						<tr><td colspan="5" class="empty-state">No ledger activity found for this period.</td></tr>
					<?php } ?>
					<tr>
						<td></td>
						<td></td>
						<td><strong><?php echo t('report_total'); ?></strong></td>
						<td class="num"><strong><?php echo number_format($wages_total, 2); ?></strong></td>
						<td class="num"><strong><?php echo number_format($payment_total, 2); ?></strong></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td><strong>Net Payable</strong></td>
						<td colspan="2" class="num"><strong><?php echo number_format($net_payable, 2); ?></strong></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="signature-line"><?php echo t('report_signature'); ?></div>
		<?php } ?>

		<div class="report-footer-note">
			<span>ناغہ بروز جمعتہ المبارک</span>
			<span>بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</span>
			<span>دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</span>
		</div>
	</div>
</div>
<?php if ($export_mode === 'pdf') { ?>
<script>
window.onload = function() {
	window.print();
};
</script>
<?php } ?>
<?php
$this->load->view('partials/report_back_nav', array(
	'report_has_report' => $has_report,
	'report_filter_url' => $emp_filter_url,
));
$this->load->view('partials/report_ajax_shell');
?>
</body>
</html>
