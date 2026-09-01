<?php
$export_mode = isset($export_mode) ? (string) $export_mode : '';
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$has_report = ($fromdate !== '' && $todate !== '');
$from_label = $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : '';
$to_label = $todate !== '' ? date('d-m-Y', strtotime($todate)) : '';
$excel_url = $has_report ? site_url('Cash/customer_pending_ledger/'.$fromdate.'/'.$todate.'?export=excel') : '#';
$pdf_url = $has_report ? site_url('Cash/customer_pending_ledger/'.$fromdate.'/'.$todate.'?export=pdf') : '#';

$rows = array();
$total_amount = 0;
$sr = 0;
foreach ($payment as $payments) {
	$sr++;
	$amount = isset($payments->balance) ? (float) $payments->balance : 0;
	$total_amount += $amount;
	$rows[] = array(
		'sr' => $sr,
		'entry_date' => isset($payments->DEDATE) && $payments->DEDATE !== '' ? date('d-m-Y', strtotime($payments->DEDATE)) : '',
		'return_date' => isset($payments->NPAID_dATE) && $payments->NPAID_dATE !== '' ? date('d-m-Y', strtotime($payments->NPAID_dATE)) : '',
		'customer' => trim((string) $payments->name.' / '.(string) $payments->man_napno.' / '.(string) $payments->Phone.' / '.(string) $payments->order_id),
		'amount' => $amount,
		'trnno' => isset($payments->trnno) ? (int) $payments->trnno : 0,
		'customer_id' => isset($payments->CUSTOMERNO) ? (string) $payments->CUSTOMERNO : '',
	);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Pending Customer Ledger</title>
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>">
<style>
	body.report-body { background: #f4f7fb; color: #1f2933; font-family: Arial, sans-serif; }
	.report-shell { max-width: 1180px; margin: 18px auto; padding: 0 14px; }
	.report-card { background: #fff; border: 1px solid #e6edf5; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 35, 65, .08); overflow: hidden; }
	.report-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #eef7f8; border-bottom: 1px solid #dcebee; }
	.report-btn { border: 0; border-radius: 8px; padding: 9px 16px; color: #fff; background: #16a1ad; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
	.report-btn.secondary { background: #5f6f7a; }
	.report-btn.success { background: #168b7f; padding: 7px 12px; font-size: 12px; }
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
	.report-field input { width: 100%; border: 1px solid #d7e0ea; border-radius: 8px; padding: 9px 10px; box-sizing: border-box; }
	.report-table-wrap { padding: 18px 24px 24px; }
	.report-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 0 0 16px; }
	.report-summary div { background: #f7fbfc; border: 1px solid #dfecef; border-radius: 10px; padding: 12px; }
	.report-summary span { display: block; color: #52616d; font-size: 12px; }
	.report-summary strong { font-size: 18px; }
	.report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
	.report-table th { background: #1f9faa; color: #fff; padding: 9px; border: 1px solid #128995; text-align: center; }
	.report-table td { padding: 8px; border: 1px solid #dde6ef; }
	.report-table .num { text-align: right; }
	.report-table .center { text-align: center; }
	.report-footer-note { background: #d9d9d9; padding: 8px 20px; display: flex; justify-content: space-between; gap: 10px; font-weight: 700; }
	.signature-line { margin: 45px 24px 22px; width: 280px; border-top: 1px dotted #222; text-align: center; padding-top: 5px; }
	.empty-state { padding: 24px; color: #687782; text-align: center; }
	.text-right { text-align: right; }
	.report-alert { margin: 0 24px 12px; padding: 10px 14px; border-radius: 8px; font-size: 14px; }
	.report-alert.success { background: #e8f8ef; color: #1d6b3f; border: 1px solid #b9e5cb; }
	.report-alert.error { background: #fdecec; color: #9b2c2c; border: 1px solid #f5c2c2; }
	.pay-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15, 35, 65, .45); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
	.pay-modal-backdrop.is-open { display: flex; }
	.pay-modal { background: #fff; border-radius: 12px; width: 100%; max-width: 420px; box-shadow: 0 16px 40px rgba(15, 35, 65, .2); overflow: hidden; }
	.pay-modal-head { padding: 16px 18px; background: #eef7f8; border-bottom: 1px solid #dcebee; }
	.pay-modal-head h3 { margin: 0 0 6px; font-size: 18px; }
	.pay-modal-head p { margin: 0; color: #52616d; font-size: 13px; line-height: 1.5; }
	.pay-modal-body { padding: 18px; }
	.pay-modal-actions { display: flex; gap: 10px; padding: 0 18px 18px; }
	.pay-modal-actions .report-btn { flex: 1; text-align: center; }
	.pay-modal-actions .report-btn.secondary { background: #8a97a3; }
	.pay-remaining { margin-top: 8px; padding: 10px; background: #f7fbfc; border-radius: 8px; font-weight: 700; color: #1f9faa; }
	.pay-remaining.is-error { background: #fff4f0; color: #c45c26; }
	.pay-field-error { display: none; margin-top: 8px; color: #c45c26; font-size: 13px; font-weight: 600; }
	.pay-field-error.is-visible { display: block; }
	.report-btn:disabled { opacity: 0.55; cursor: not-allowed; }
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
				<h2>Pending Customer Ledger</h2>
				<div class="report-meta">
					From Date: <strong><?php echo esc_html($from_label); ?></strong><br>
					To Date: <strong><?php echo esc_html($to_label); ?></strong>
				</div>
			</div>
			<div class="report-meta">
				Generated: <?php echo date('d-m-Y h:i A'); ?>
			</div>
		</div>

		<?php if ($this->session->flashdata('pending_payment_msg')) { ?>
		<div class="report-alert success print-hide"><?php echo esc_html($this->session->flashdata('pending_payment_msg')); ?></div>
		<?php } ?>
		<?php if ($this->session->flashdata('transfer_error')) { ?>
		<div class="report-alert error print-hide"><?php echo esc_html($this->session->flashdata('transfer_error')); ?></div>
		<?php } ?>

		<?php if (!$has_report) { ?>
		<form class="report-form print-hide" action="<?php echo site_url('Cash/customer_pending_ledger_pr'); ?>" method="post">
			<div class="report-form-grid">
				<div class="report-field">
					<label>From Date</label>
					<input type="date" name="fromdate" required>
				</div>
				<div class="report-field">
					<label>To Date</label>
					<input type="date" name="todate" required>
				</div>
				<div class="report-field">
					<button type="submit" class="report-btn" style="width:100%;"><?php echo t('app_submit'); ?></button>
				</div>
			</div>
		</form>
		<?php } else { ?>
		<div class="report-table-wrap">
			<div class="report-summary">
				<div><span><?php echo t('report_total_pending_rows'); ?></span><strong><?php echo number_format(count($rows)); ?></strong></div>
				<div><span><?php echo t('report_total_amount'); ?></span><strong><?php echo number_format($total_amount, 2); ?></strong></div>
				<div><span>Net Pending</span><strong><?php echo number_format($total_amount, 2); ?></strong></div>
			</div>
			<table class="report-table">
				<thead>
					<tr>
						<th style="width:5%;">SR</th>
						<th style="width:12%;">Entry Date</th>
						<th style="width:12%;">Return Date</th>
						<th>Customer / Naap</th>
						<th style="width:14%;"><?php echo t('report_amount'); ?></th>
						<?php if ($export_mode === '') { ?><th class="print-hide" style="width:10%;"><?php echo t('report_paid'); ?></th><?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($rows)) { ?>
						<?php foreach ($rows as $row) { ?>
						<tr>
							<td class="center"><?php echo (int) $row['sr']; ?></td>
							<td class="center"><?php echo esc_html($row['entry_date']); ?></td>
							<td class="center"><?php echo esc_html($row['return_date']); ?></td>
							<td><?php echo esc_html($row['customer']); ?></td>
							<td class="num"><?php echo number_format($row['amount'], 2); ?></td>
							<?php if ($export_mode === '') { ?>
							<td class="center print-hide">
								<button type="button" class="report-btn success js-open-pending-pay"
									data-trnno="<?php echo (int) $row['trnno']; ?>"
									data-customer-id="<?php echo esc_html($row['customer_id']); ?>"
									data-balance="<?php echo esc_html(number_format($row['amount'], 2, '.', '')); ?>"
									data-customer-label="<?php echo esc_html($row['customer']); ?>">
									<?php echo t('report_paid'); ?>
								</button>
							</td>
							<?php } ?>
						</tr>
						<?php } ?>
					<?php } else { ?>
						<tr><td colspan="<?php echo $export_mode === '' ? '6' : '5'; ?>" class="empty-state">No pending customer balance found for this date range.</td></tr>
					<?php } ?>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><strong><?php echo t('report_total'); ?></strong></td>
						<td class="num"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
						<?php if ($export_mode === '') { ?><td class="print-hide"></td><?php } ?>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td><strong>Net</strong></td>
						<td class="num"><strong><?php echo number_format($total_amount, 2); ?></strong></td>
						<?php if ($export_mode === '') { ?><td class="print-hide"></td><?php } ?>
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

<?php if ($export_mode === '' && $has_report) { ?>
<div class="pay-modal-backdrop print-hide" id="pending-pay-modal" aria-hidden="true">
	<div class="pay-modal" role="dialog" aria-labelledby="pending-pay-title">
		<div class="pay-modal-head">
			<h3 id="pending-pay-title"><?php echo t('report_pending_pay_title'); ?></h3>
			<p id="pending-pay-customer"></p>
		</div>
		<form method="post" action="<?php echo site_url('Cash/process_status'); ?>" id="pending-pay-form">
			<input type="hidden" name="fromdate" value="<?php echo esc_html($fromdate); ?>">
			<input type="hidden" name="todate" value="<?php echo esc_html($todate); ?>">
			<input type="hidden" name="dmst_trnno" id="pending-pay-trnno" value="">
			<input type="hidden" name="customer_id" id="pending-pay-customer-id" value="">
			<div class="pay-modal-body">
				<div class="report-field">
					<label><?php echo t('report_pending_balance'); ?></label>
					<input type="text" id="pending-pay-balance-display" readonly>
				</div>
				<div class="report-field" style="margin-top:12px;">
					<label for="pending-pay-amount"><?php echo t('report_pending_pay_amount'); ?></label>
					<input type="number" name="pay_amount" id="pending-pay-amount" min="0" step="0.01" required>
				</div>
				<div class="report-field" style="margin-top:12px;">
					<label for="pending-pay-discount"><?php echo t('report_pending_discount'); ?></label>
					<input type="number" name="discount_amount" id="pending-pay-discount" min="0" step="0.01" value="0">
				</div>
				<div class="pay-remaining" id="pending-pay-remaining-wrap">
					<?php echo t('report_pending_remaining'); ?>:
					<span id="pending-pay-remaining">0.00</span>
				</div>
				<div class="pay-field-error" id="pending-pay-error" role="alert" lang="ur" dir="rtl"></div>
			</div>
			<div class="pay-modal-actions">
				<button type="button" class="report-btn secondary" id="pending-pay-cancel"><?php echo t('report_pending_cancel'); ?></button>
				<button type="submit" class="report-btn"><?php echo t('report_pending_save_payment'); ?></button>
			</div>
		</form>
	</div>
</div>
<script>
(function() {
	var modal = document.getElementById('pending-pay-modal');
	if (!modal) return;

	var balanceDisplay = document.getElementById('pending-pay-balance-display');
	var payInput = document.getElementById('pending-pay-amount');
	var discountInput = document.getElementById('pending-pay-discount');
	var remainingEl = document.getElementById('pending-pay-remaining');
	var remainingWrap = document.getElementById('pending-pay-remaining-wrap');
	var payErrorEl = document.getElementById('pending-pay-error');
	var submitBtn = document.querySelector('#pending-pay-form button[type="submit"]');
	var currentBalance = 0;
	var msgOver = <?php echo json_encode(t_urdu('report_pending_payment_over')); ?>;

	function roundMoney(n) {
		return Math.round((n + Number.EPSILON) * 100) / 100;
	}

	function paymentIsOver(pay, discount) {
		return roundMoney(pay) > roundMoney(currentBalance) + 0.009
			|| roundMoney(discount) > roundMoney(currentBalance) + 0.009
			|| roundMoney(discount) > roundMoney(currentBalance - pay) + 0.009
			|| roundMoney(pay + discount) > roundMoney(currentBalance) + 0.009;
	}

	function paymentBlocksSave(pay, discount) {
		if (pay < 0 || discount < 0) {
			return true;
		}
		if (pay + discount <= 0) {
			return true;
		}
		return paymentIsOver(pay, discount);
	}

	function updateRemaining() {
		var pay = parseFloat(payInput.value) || 0;
		var discount = parseFloat(discountInput.value) || 0;
		var remaining = roundMoney(currentBalance - pay - discount);
		var blocked = paymentBlocksSave(pay, discount);
		var over = paymentIsOver(pay, discount);

		remainingEl.textContent = remaining.toFixed(2);
		if (remainingWrap) {
			remainingWrap.classList.toggle('is-error', blocked);
		}
		if (payErrorEl) {
			if (over) {
				payErrorEl.textContent = msgOver;
			} else if (pay < 0 || discount < 0) {
				payErrorEl.textContent = <?php echo json_encode(t('report_pending_payment_invalid')); ?>;
			} else if (pay + discount <= 0) {
				payErrorEl.textContent = <?php echo json_encode(t('report_pending_payment_zero')); ?>;
			} else {
				payErrorEl.textContent = '';
			}
			payErrorEl.classList.toggle('is-visible', blocked);
		}
		if (submitBtn) {
			submitBtn.disabled = blocked;
		}
	}

	function openModal(btn) {
		currentBalance = parseFloat(btn.getAttribute('data-balance')) || 0;
		document.getElementById('pending-pay-trnno').value = btn.getAttribute('data-trnno') || '';
		document.getElementById('pending-pay-customer-id').value = btn.getAttribute('data-customer-id') || '';
		document.getElementById('pending-pay-customer').textContent = btn.getAttribute('data-customer-label') || '';
		balanceDisplay.value = currentBalance.toFixed(2);
		payInput.value = currentBalance.toFixed(2);
		discountInput.value = '0';
		updateRemaining();
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
	}

	function closeModal() {
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
	}

	document.addEventListener('click', function(e) {
		var btn = e.target.closest ? e.target.closest('.js-open-pending-pay') : null;
		if (btn) {
			openModal(btn);
		}
	});

	document.getElementById('pending-pay-cancel').addEventListener('click', closeModal);
	modal.addEventListener('click', function(e) {
		if (e.target === modal) closeModal();
	});

	function onPayInput() {
		updateRemaining();
	}
	function onDiscountInput() {
		updateRemaining();
	}

	payInput.addEventListener('input', onPayInput);
	payInput.addEventListener('change', onPayInput);
	payInput.addEventListener('blur', onPayInput);
	discountInput.addEventListener('input', onDiscountInput);
	discountInput.addEventListener('change', onDiscountInput);
	discountInput.addEventListener('blur', onDiscountInput);

	document.getElementById('pending-pay-form').addEventListener('submit', function(e) {
		var pay = parseFloat(payInput.value) || 0;
		var discount = parseFloat(discountInput.value) || 0;
		if (paymentBlocksSave(pay, discount)) {
			e.preventDefault();
			updateRemaining();
			if (paymentIsOver(pay, discount)) {
				alert(msgOver);
			} else if (pay < 0 || discount < 0) {
				alert(<?php echo json_encode(t('report_pending_payment_invalid')); ?>);
			} else {
				alert(<?php echo json_encode(t('report_pending_payment_zero')); ?>);
			}
		}
	});
})();
</script>
<?php } ?>

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
