<?php
$export_mode = isset($export_mode) ? (string) $export_mode : '';
$asdate = isset($asdate) ? (string) $asdate : (string) $this->uri->segment(3);
$customer_id = isset($customer_id) ? (string) $customer_id : (string) $this->uri->segment(4);
$opening = isset($opening) ? (float) $opening : 0;
$payment = isset($payment) && is_array($payment) ? $payment : array();
$customer = isset($customer) && is_array($customer) ? $customer : array();
$customer_info = isset($customer_info) ? $customer_info : NULL;
$has_report = ($asdate !== '' && $customer_id !== '');

if (!$customer_info && $customer_id !== '') {
	foreach ($customer as $customers) {
		if ((string) $customers->code === (string) $customer_id) {
			$customer_info = $customers;
			break;
		}
	}
}

$customer_name = $customer_info ? (string) $customer_info->name : '';
$customer_naap = $customer_info ? (string) $customer_info->man_napno : '';
$customer_phone = $customer_info && isset($customer_info->Phone) ? (string) $customer_info->Phone : '';
$as_label = $asdate !== '' ? date('d-m-Y', strtotime($asdate)) : '';
$excel_url = $has_report ? site_url('Cash/customer_ledger/'.$asdate.'/'.$customer_id.'?export=excel') : '#';
$pdf_url = $has_report ? site_url('Cash/customer_ledger/'.$asdate.'/'.$customer_id.'?export=pdf') : '#';

$rows = array();
$debit_total = 0;
$credit_total = 0;
$sr = 0;
foreach ($payment as $payments) {
	$sr++;
	$debit = isset($payments->debit) ? (float) $payments->debit : 0;
	$credit = isset($payments->credit) ? (float) $payments->credit : 0;
	$debit_total += $debit;
	$credit_total += $credit;
	$narration = isset($payments->NARRATION) ? (string) $payments->NARRATION : '';
	$order_no = isset($payments->ORDERNO) ? (string) $payments->ORDERNO : '';
	if ($narration === '') {
		$type = isset($payments->type) ? (int) $payments->type : 0;
		if ($type === 2) {
			$narration = 'Cash Received';
		} elseif ($type === 1) {
			$narration = 'Cash Payment';
		} elseif ($order_no !== '') {
			$narration = 'Advance received against Order '.$order_no;
		}
	}

	$rows[] = array(
		'sr' => $sr,
		'date' => isset($payments->LEDGERDATE) && $payments->LEDGERDATE !== '' ? date('d-m-Y', strtotime($payments->LEDGERDATE)) : '',
		'narration' => $narration,
		'debit' => $debit,
		'credit' => $credit,
	);
}

$receivable = $opening + $debit_total - $credit_total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo t('report_customer_ledger'); ?></title>
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>?v=portrait-a4-2">
<style>
	body.report-body { background: #f4f7fb; color: #1f2933; font-family: Arial, sans-serif; }
	.report-shell { max-width: 1180px; margin: 18px auto; padding: 0 14px; }
	.report-card { background: #fff; border: 1px solid #e6edf5; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 35, 65, .08); overflow: hidden; }
	.report-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #eef7f8; border-bottom: 1px solid #dcebee; }
	.report-btn { border: 0; border-radius: 8px; padding: 9px 16px; color: #fff; background: #16a1ad; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
	.report-btn.secondary { background: #5f6f7a; }
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
	.report-form-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 14px; align-items: end; }
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
		<?php if ($export_mode !== 'excel') { ?>
		<div class="report-actions print-hide">
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
				<h2><?php echo t('report_customer_ledger'); ?></h2>
				<div class="report-meta">
					<?php echo t('report_customer_label'); ?>: <strong><?php echo esc_html($customer_name); ?></strong><br>
					Naap No: <strong><?php echo esc_html($customer_naap); ?></strong>
					<?php if ($customer_phone !== '') { ?> | Mobile: <strong><?php echo esc_html($customer_phone); ?></strong><?php } ?><br>
					<?php echo t('report_as_on_date'); ?>: <strong><?php echo esc_html($as_label); ?></strong>
				</div>
			</div>
			<div class="report-meta">
				Generated: <?php echo date('d-m-Y h:i A'); ?>
			</div>
		</div>

		<?php if (!$has_report) { ?>
		<form class="report-form print-hide" action="<?php echo site_url('Cash/customer_ledger_pro/rep'); ?>" method="post" data-no-ajax="true">
			<?php $this->load->view('partials/csrf_field'); ?>
			<div class="report-form-grid">
				<div class="report-field">
					<label><?php echo t('report_as_on_date'); ?></label>
					<input type="date" name="fromdate" required>
				</div>
				<div class="report-field">
					<label><?php echo t('report_customer'); ?></label>
					<select name="Customer" required>
						<option value=""><?php echo t('report_select_customer'); ?></option>
						<?php foreach ($customer as $customers) { ?>
						<option value="<?php echo esc_html((string) $customers->code); ?>"><?php echo esc_html((string) $customers->man_napno.'  '.$customers->name); ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="report-field">
					<button type="submit" class="report-btn" style="width:100%;"><?php echo t('app_submit'); ?></button>
				</div>
			</div>
		</form>
		<?php } else { ?>
		<div class="report-table-wrap">
			<div class="report-summary">
				<div><span>Opening Balance</span><strong><?php echo number_format($opening, 2); ?></strong></div>
				<div><span><?php echo t('report_total_amount'); ?></span><strong><?php echo number_format($debit_total, 2); ?></strong></div>
				<div><span><?php echo t('report_total_received'); ?></span><strong><?php echo number_format($credit_total, 2); ?></strong></div>
				<div><span>Receivable</span><strong><?php echo number_format($receivable, 2); ?></strong></div>
			</div>
			<table class="report-table">
				<thead>
					<tr>
						<th style="width:5%;">SR</th>
						<th style="width:12%;"><?php echo t('report_date'); ?></th>
						<th><?php echo t('report_narration'); ?></th>
						<th style="width:14%;">Amount</th>
						<th style="width:14%;">Received</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="center"></td>
						<td class="center"></td>
						<td><strong>Opening Balance</strong></td>
						<td colspan="2" class="num"><strong><?php echo number_format($opening, 2); ?></strong></td>
					</tr>
					<?php if (!empty($rows)) { ?>
						<?php foreach ($rows as $row) { ?>
						<tr>
							<td class="center"><?php echo (int) $row['sr']; ?></td>
							<td class="center"><?php echo esc_html($row['date']); ?></td>
							<td><?php echo esc_html($row['narration']); ?></td>
							<td class="num"><?php echo $row['debit'] != 0 ? number_format($row['debit'], 2) : ''; ?></td>
							<td class="num"><?php echo $row['credit'] != 0 ? number_format($row['credit'], 2) : ''; ?></td>
						</tr>
						<?php } ?>
					<?php } else { ?>
						<tr><td colspan="5" class="empty-state">No ledger activity found after the selected date.</td></tr>
					<?php } ?>
					<tr>
						<td></td>
						<td></td>
						<td><strong><?php echo t('report_total'); ?></strong></td>
						<td class="num"><strong><?php echo number_format($debit_total, 2); ?></strong></td>
						<td class="num"><strong><?php echo number_format($credit_total, 2); ?></strong></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td><strong>Receivable</strong></td>
						<td colspan="2" class="num"><strong><?php echo number_format($receivable, 2); ?></strong></td>
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
<?php $this->load->view('partials/report_ajax_shell'); ?>
</body>
</html>
