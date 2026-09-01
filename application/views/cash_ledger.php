<?php
$export_mode = isset($export_mode) ? (string) $export_mode : '';
$fromdate = isset($fromdate) ? (string) $fromdate : (string) $this->uri->segment(3);
$todate = isset($todate) ? (string) $todate : (string) $this->uri->segment(4);
$summary = isset($summary) && is_array($summary) ? $summary : NULL;
$cash_opening = $summary ? (float) $summary['opening'] : (isset($CASHopening) ? (float) $CASHopening : 0);
$payment = isset($payment) && is_array($payment) ? $payment : array();
$load_detail_inline = !empty($load_detail_inline);
$has_report = ($fromdate !== '' && $todate !== '');
$from_label = $fromdate !== '' ? date('d-m-Y', strtotime($fromdate)) : '';
$to_label = $todate !== '' ? date('d-m-Y', strtotime($todate)) : '';
$excel_url = $has_report ? site_url('Cash/cash_ledger/'.$fromdate.'/'.$todate.'?export=excel') : '#';
$pdf_url = $has_report ? site_url('Cash/cash_ledger/'.$fromdate.'/'.$todate.'?export=pdf') : '#';
$data_url = site_url('Cash/cash_ledger_data');

$receipt_total = $summary ? (float) $summary['receipt_total'] : 0;
$payment_total = $summary ? (float) $summary['payment_total'] : 0;
$period_net = $summary ? (float) $summary['period_net'] : 0;
$closing_balance = $summary ? (float) $summary['closing'] : $cash_opening;
$line_count = $summary ? (int) $summary['line_count'] : 0;

if (!function_exists('cash_ledger_format_amount_cell')) {
	function cash_ledger_format_amount_cell($amount, $blank_zero = TRUE)
	{
		$amount = (float) $amount;
		if ($blank_zero && abs($amount) < 0.00001) {
			return '';
		}
		return number_format($amount, 2);
	}
}

if (!function_exists('cash_ledger_group_rows_by_year')) {
	function cash_ledger_group_rows_by_year($rows)
	{
		$grouped = array();
		foreach ($rows as $row) {
			$year = '';
			if (!empty($row['sort_date']) && preg_match('/^(\d{4})/', (string) $row['sort_date'], $m)) {
				$year = $m[1];
			} elseif (!empty($row['date']) && preg_match('/(\d{4})$/', (string) $row['date'], $m)) {
				$year = $m[1];
			}
			if ($year === '') {
				$year = 'Unknown';
			}
			if (!isset($grouped[$year])) {
				$grouped[$year] = array(
					'label' => $year,
					'rows' => array(),
					'receipt' => 0,
					'payment' => 0,
				);
			}
			$grouped[$year]['rows'][] = $row;
			$grouped[$year]['receipt'] += isset($row['receipt']) ? (float) $row['receipt'] : 0;
			$grouped[$year]['payment'] += isset($row['payment']) ? (float) $row['payment'] : 0;
		}
		ksort($grouped);
		return $grouped;
	}
}

$rows = array();
if ($load_detail_inline) {
	foreach ($payment as $payments) {
		$amount = isset($payments->RECEIPT) ? (float) $payments->RECEIPT : 0;
		$is_receipt = !empty($payments->is_receipt);
		if (!$summary) {
			if ($is_receipt) { $receipt_total += $amount; } else { $payment_total += $amount; }
		}
		$rows[] = array(
			'date' => isset($payments->ENTRYDATE) && $payments->ENTRYDATE !== '' ? date('d-m-Y', strtotime($payments->ENTRYDATE)) : '',
			'sort_date' => isset($payments->ENTRYDATE) ? (string) $payments->ENTRYDATE : '',
			'narration' => isset($payments->NARRATION) ? (string) $payments->NARRATION : '',
			'source' => isset($payments->SOURCE) ? (string) $payments->SOURCE : 'other',
			'receipt' => $is_receipt ? $amount : 0,
			'payment' => $is_receipt ? 0 : $amount,
		);
	}
	if (!$summary) {
		$period_net = $receipt_total - $payment_total;
		$closing_balance = $cash_opening + $period_net;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Cash Ledger</title>
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>">
<style>
	body.report-body { background: #f4f7fb; color: #1f2933; font-family: Arial, sans-serif; }
	.report-shell { max-width: 1180px; margin: 18px auto; padding: 0 14px; }
	.report-card { background: #fff; border: 1px solid #e6edf5; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 35, 65, .08); overflow: hidden; }
	.report-actions { display: flex; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #eef7f8; border-bottom: 1px solid #dcebee; flex-wrap: wrap; }
	.report-btn { border: 0; border-radius: 8px; padding: 9px 16px; color: #fff; background: #16a1ad; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
	.report-btn.secondary { background: #5f6f7a; }
	.report-btn:disabled { opacity: .55; cursor: not-allowed; }
	.report-title { padding: 16px 24px; display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; border-bottom: 1px solid #e6edf5; }
	.report-title h2 { margin: 0; font-size: 24px; }
	.report-meta { color: #52616d; font-size: 14px; line-height: 1.7; }
	.report-form { padding: 20px 24px; border-bottom: 1px solid #e6edf5; }
	.report-form-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 14px; align-items: end; }
	.report-field label { display: block; font-weight: 700; margin-bottom: 5px; }
	.report-field input, .report-field select { width: 100%; border: 1px solid #d7e0ea; border-radius: 8px; padding: 9px 10px; }
	.report-table-wrap { padding: 18px 24px 24px; }
	.report-summary { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin: 0 0 16px; }
	.report-summary div { background: #f7fbfc; border: 1px solid #dfecef; border-radius: 10px; padding: 12px; }
	.report-summary span { display: block; color: #52616d; font-size: 12px; }
	.report-summary strong { font-size: 18px; }
	.drill-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px; }
	.drill-panel h3 { margin: 0 0 10px; font-size: 16px; }
	.drill-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: end; margin-bottom: 10px; }
	.drill-toolbar .report-field { flex: 1; min-width: 140px; margin: 0; }
	.drill-hint { color: #64748b; font-size: 13px; margin: 0 0 10px; }
	.drill-status { font-size: 13px; color: #0f766e; min-height: 18px; }
	.report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
	.report-table th { background: #1f9faa; color: #fff; padding: 9px; border: 1px solid #128995; text-align: center; }
	.report-table td { padding: 8px; border: 1px solid #dde6ef; }
	.report-table .num { text-align: right; }
	.report-table .center { text-align: center; }
	.report-table tr.drill-row-click { cursor: pointer; }
	.report-table tr.drill-row-click:hover { background: #f0fdfa; }
	.report-table tr.period-header td { background: #e8f4f5; font-weight: 700; border-top: 2px solid #b8dde0; }
	.report-table tr.period-total td { background: #f3fafb; font-weight: 700; border-top: 1px solid #c5e3e6; }
	.report-table tr.summary-total td { background: #eef7f8; font-weight: 700; border-top: 2px solid #9fd0d6; }
	.report-footer-note { background: #d9d9d9; padding: 8px 20px; display: flex; justify-content: space-between; gap: 10px; font-weight: 700; }
	.signature-line { margin: 45px 24px 22px; width: 280px; border-top: 1px dotted #222; text-align: center; padding-top: 5px; }
	.empty-state { padding: 24px; color: #687782; text-align: center; }
	.detail-hidden { display: none; }
	@media print {
		.detail-hidden { display: block !important; }
	}
	@media (max-width: 900px) {
		.report-summary { grid-template-columns: repeat(2, 1fr); }
	}
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
				<h2>Cash Ledger</h2>
				<div class="report-meta">
					From Date: <strong><?php echo esc_html($from_label); ?></strong><br>
					To Date: <strong><?php echo esc_html($to_label); ?></strong>
				</div>
			</div>
			<div class="report-meta">
				Generated: <?php echo date('d-m-Y h:i A'); ?>
			</div>
		</div>

		<?php if (!$has_report) { ?>
		<form class="report-form print-hide" action="<?php echo site_url('Cash/cash_ledger_pro'); ?>" method="post">
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
			<div class="report-summary" id="cl-summary">
				<div><span>Opening Balance</span><strong><?php echo number_format($cash_opening, 2); ?></strong></div>
				<div><span>Cash Receipt</span><strong id="cl-receipt"><?php echo number_format($receipt_total, 2); ?></strong></div>
				<div><span>Cash Payment</span><strong id="cl-payment"><?php echo number_format($payment_total, 2); ?></strong></div>
				<div><span>Period Net</span><strong id="cl-net"><?php echo number_format($period_net, 2); ?></strong></div>
				<div><span>Closing Balance</span><strong id="cl-closing"><?php echo number_format($closing_balance, 2); ?></strong></div>
			</div>
			<p class="drill-hint print-hide" id="cl-line-hint">
				<?php echo $line_count; ?> transaction line(s) in this period. Use drill-down below for yearly, quarterly, monthly, daily, or line detail.
			</p>

			<?php if ($export_mode !== 'excel' && !$load_detail_inline) { ?>
			<div class="drill-panel print-hide" id="cl-drill-panel">
				<h3>Drill-down</h3>
				<div class="drill-toolbar">
					<div class="report-field">
						<label>View</label>
						<select id="cl-drill-mode">
							<option value="year">Yearly summary</option>
							<option value="halfyear">Half-year summary</option>
							<option value="quarter">Quarterly summary</option>
							<option value="month">Monthly summary</option>
							<option value="daily">Daily summary</option>
							<option value="detail">Line detail</option>
						</select>
					</div>
					<div class="report-field" id="cl-day-wrap" style="display:none;">
						<label>Day (optional)</label>
						<input type="date" id="cl-drill-day" min="<?php echo esc_html($fromdate); ?>" max="<?php echo esc_html($todate); ?>">
					</div>
					<div class="report-field" id="cl-source-wrap" style="display:none;">
						<label>Category</label>
						<select id="cl-drill-source">
							<option value="">All categories</option>
							<option value="advance">Order advances</option>
							<option value="customer">Customer cash</option>
							<option value="employee">Employee cash</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="report-field" style="flex:0;">
						<label>&nbsp;</label>
						<button type="button" class="report-btn" id="cl-load-drill">Load</button>
					</div>
				</div>
				<div class="drill-status" id="cl-drill-status"></div>
			</div>
			<?php } ?>

			<div id="cl-drill-table-wrap" class="<?php echo ($load_detail_inline ? '' : 'detail-hidden print-hide'); ?>">
			<table class="report-table" id="cl-detail-table">
				<thead>
					<tr id="cl-thead-row">
						<th style="width:13%;"><?php echo t('report_date'); ?></th>
						<th><?php echo t('report_narration'); ?></th>
						<th style="width:15%;">Cash Receipt</th>
						<th style="width:15%;">Cash Payment</th>
					</tr>
				</thead>
				<tbody id="cl-tbody">
					<?php if ($load_detail_inline) { ?>
					<tr>
						<td></td>
						<td><strong>Opening Balance</strong></td>
						<td class="num"><strong><?php echo number_format($cash_opening, 2); ?></strong></td>
						<td class="num"></td>
					</tr>
					<?php if (!empty($rows)) {
						$year_groups = cash_ledger_group_rows_by_year($rows);
						foreach ($year_groups as $year_group) { ?>
					<tr class="period-header">
						<td colspan="4"><?php echo esc_html($year_group['label']); ?></td>
					</tr>
					<?php foreach ($year_group['rows'] as $row) { ?>
					<tr>
						<td class="center"><?php echo esc_html($row['date']); ?></td>
						<td><?php echo esc_html($row['narration']); ?></td>
						<td class="num"><?php echo cash_ledger_format_amount_cell(isset($row['receipt']) ? $row['receipt'] : 0); ?></td>
						<td class="num"><?php echo cash_ledger_format_amount_cell(isset($row['payment']) ? $row['payment'] : 0); ?></td>
					</tr>
					<?php } ?>
					<tr class="period-total">
						<td></td>
						<td><strong><?php echo esc_html($year_group['label']); ?> Total</strong></td>
						<td class="num"><strong><?php echo cash_ledger_format_amount_cell($year_group['receipt'], FALSE); ?></strong></td>
						<td class="num"><strong><?php echo cash_ledger_format_amount_cell($year_group['payment'], FALSE); ?></strong></td>
					</tr>
					<?php }
					} else { ?>
					<tr><td colspan="4" class="empty-state">No cash activity found for this date range.</td></tr>
					<?php } ?>
					<tr class="summary-total">
						<td></td>
						<td><strong><?php echo t('report_total'); ?></strong></td>
						<td class="num"><strong><?php echo number_format($receipt_total, 2); ?></strong></td>
						<td class="num"><strong><?php echo number_format($payment_total, 2); ?></strong></td>
					</tr>
					<tr>
						<td></td>
						<td><strong>Net Cash</strong></td>
						<td class="num"></td>
						<td class="num"><strong><?php echo number_format($period_net, 2); ?></strong></td>
					</tr>
					<?php } else { ?>
					<tr><td colspan="4" class="empty-state print-hide">Select a drill-down option above to load details.</td></tr>
					<?php } ?>
				</tbody>
			</table>
			</div>
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
<?php if ($has_report && $export_mode !== 'excel' && !$load_detail_inline) { ?>
<script>
(function () {
	var cfg = {
		url: <?php echo json_encode($data_url); ?>,
		from: <?php echo json_encode($fromdate); ?>,
		to: <?php echo json_encode($todate); ?>,
		opening: <?php echo json_encode($cash_opening); ?>
	};
	var modeEl = document.getElementById('cl-drill-mode');
	var dayWrap = document.getElementById('cl-day-wrap');
	var sourceWrap = document.getElementById('cl-source-wrap');
	var dayEl = document.getElementById('cl-drill-day');
	var sourceEl = document.getElementById('cl-drill-source');
	var statusEl = document.getElementById('cl-drill-status');
	var tbody = document.getElementById('cl-tbody');
	var tableWrap = document.getElementById('cl-drill-table-wrap');
	var loadBtn = document.getElementById('cl-load-drill');
	var scopeFrom = '';
	var scopeTo = '';
	var summaryModes = { year: 1, halfyear: 1, quarter: 1, month: 1, daily: 1 };
	var modeHints = {
		year: 'Yearly summary — click a row to drill into months for that year.',
		halfyear: 'Half-year summary — click a row to drill into months for that period.',
		quarter: 'Quarterly summary — click a row to drill into daily lines for that quarter.',
		month: 'Monthly summary — click a row to drill into daily lines for that month.',
		daily: 'Daily summary — click a row to drill into line detail for that day.'
	};
	var periodColLabels = {
		year: 'Year',
		halfyear: 'Half-year',
		quarter: 'Quarter',
		month: 'Month',
		daily: 'Date'
	};

	function fmt(n) {
		return Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function fmtCell(n) {
		n = Number(n || 0);
		return Math.abs(n) < 0.00001 ? '' : fmt(n);
	}

	function syncFilters() {
		var mode = modeEl.value;
		dayWrap.style.display = (mode === 'detail') ? '' : 'none';
		sourceWrap.style.display = (mode === 'detail') ? '' : 'none';
	}

	function setStatus(msg) {
		statusEl.textContent = msg || '';
	}

	function renderPeriodSummary(rows, mode, totals) {
		var col = periodColLabels[mode] || 'Period';
		var html = '';
		document.getElementById('cl-thead-row').innerHTML =
			'<th>' + col + '</th><th>Cash Receipt</th><th>Cash Payment</th><th>Net</th><th>Lines</th>';
		if (!rows.length) {
			tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No activity in this period.</td></tr>';
			return;
		}
		rows.forEach(function (r) {
			html += '<tr class="drill-row-click"'
				+ ' data-drill-next="' + escapeHtml(r.drill_next || 'detail') + '"'
				+ ' data-period-from="' + escapeHtml(r.period_from || '') + '"'
				+ ' data-period-to="' + escapeHtml(r.period_to || '') + '"'
				+ ' title="Click to drill down">'
				+ '<td class="center"><strong>' + escapeHtml(r.period_label || r.date_label) + '</strong></td>'
				+ '<td class="num">' + fmt(r.receipt_total) + '</td>'
				+ '<td class="num">' + fmt(r.payment_total) + '</td>'
				+ '<td class="num">' + fmt(r.period_net) + '</td>'
				+ '<td class="center">' + r.line_count + '</td></tr>';
		});
		var rt = totals ? Number(totals.receipt_total || 0) : 0;
		var pt = totals ? Number(totals.payment_total || 0) : 0;
		var net = totals ? Number(totals.period_net || (rt - pt)) : (rt - pt);
		var lines = totals ? Number(totals.line_count || 0) : 0;
		html += '<tr class="summary-total"><td><strong>Total</strong></td>'
			+ '<td class="num"><strong>' + fmt(rt) + '</strong></td>'
			+ '<td class="num"><strong>' + fmt(pt) + '</strong></td>'
			+ '<td class="num"><strong>' + fmt(net) + '</strong></td>'
			+ '<td class="center"><strong>' + lines + '</strong></td></tr>';
		tbody.innerHTML = html;
		tbody.querySelectorAll('.drill-row-click').forEach(function (tr) {
			tr.addEventListener('click', function () {
				var next = tr.getAttribute('data-drill-next') || 'detail';
				scopeFrom = tr.getAttribute('data-period-from') || '';
				scopeTo = tr.getAttribute('data-period-to') || '';
				modeEl.value = next;
				syncFilters();
				if (next === 'detail' && scopeFrom) {
					dayEl.value = scopeFrom;
				}
				loadDrill();
			});
		});
	}

	function groupRowsByYear(rows) {
		var grouped = [];
		var map = {};
		rows.forEach(function (r) {
			var year = (r.date && r.date.length >= 4) ? String(r.date).substring(0, 4) : 'Unknown';
			if (!map[year]) {
				map[year] = { label: year, rows: [], receipt: 0, payment: 0 };
				grouped.push(map[year]);
			}
			map[year].rows.push(r);
			map[year].receipt += Number(r.receipt || 0);
			map[year].payment += Number(r.payment || 0);
		});
		return grouped;
	}

	function renderDetail(rows, totals) {
		document.getElementById('cl-thead-row').innerHTML =
			'<th style="width:13%;"><?php echo t('report_date'); ?></th>'
			+ '<th><?php echo t('report_narration'); ?></th>'
			+ '<th style="width:15%;">Cash Receipt</th>'
			+ '<th style="width:15%;">Cash Payment</th>';
		var opening = totals && totals.opening != null ? Number(totals.opening) : cfg.opening;
		var html = '<tr><td></td><td><strong>Opening Balance</strong></td>'
			+ '<td class="num"><strong>' + fmt(opening) + '</strong></td><td></td></tr>';
		if (!rows.length) {
			html += '<tr><td colspan="4" class="empty-state">No lines match your filters.</td></tr>';
		} else {
			var yearGroups = groupRowsByYear(rows);
			yearGroups.forEach(function (yearGroup) {
				html += '<tr class="period-header"><td colspan="4">' + escapeHtml(yearGroup.label) + '</td></tr>';
				yearGroup.rows.forEach(function (r) {
					html += '<tr><td class="center">' + r.date_label + '</td><td>' + escapeHtml(r.narration) + '</td>'
						+ '<td class="num">' + fmtCell(r.receipt) + '</td>'
						+ '<td class="num">' + fmtCell(r.payment) + '</td></tr>';
				});
				html += '<tr class="period-total"><td></td><td><strong>' + escapeHtml(yearGroup.label) + ' Total</strong></td>'
					+ '<td class="num"><strong>' + fmt(yearGroup.receipt) + '</strong></td>'
					+ '<td class="num"><strong>' + fmt(yearGroup.payment) + '</strong></td></tr>';
			});
		}
		var rt = totals ? totals.receipt_total : 0;
		var pt = totals ? totals.payment_total : 0;
		var net = totals ? totals.period_net : (rt - pt);
		html += '<tr class="summary-total"><td></td><td><strong>Total</strong></td><td class="num"><strong>' + fmt(rt) + '</strong></td>'
			+ '<td class="num"><strong>' + fmt(pt) + '</strong></td></tr>';
		html += '<tr><td></td><td><strong>Net Cash</strong></td><td class="num"></td>'
			+ '<td class="num"><strong>' + fmt(net) + '</strong></td></tr>';
		tbody.innerHTML = html;
	}

	function escapeHtml(s) {
		var d = document.createElement('div');
		d.textContent = s;
		return d.innerHTML;
	}

	function loadDrill(resetScope) {
		if (resetScope) {
			scopeFrom = '';
			scopeTo = '';
		}
		var mode = modeEl.value;
		var fd = new FormData();
		fd.append('from', cfg.from);
		fd.append('to', cfg.to);
		fd.append('mode', mode);
		if (scopeFrom && scopeTo) {
			fd.append('scope_from', scopeFrom);
			fd.append('scope_to', scopeTo);
		}
		if (mode === 'detail') {
			if (dayEl.value) fd.append('day', dayEl.value);
			if (sourceEl.value) fd.append('source', sourceEl.value);
		}
		loadBtn.disabled = true;
		setStatus('Loading…');
		fetch(cfg.url, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res.ok) throw new Error(res.message || 'Load failed');
				tableWrap.classList.remove('detail-hidden');
				if (summaryModes[mode]) {
					renderPeriodSummary(res.rows || [], mode, res);
					setStatus(modeHints[mode] || 'Click a row to drill down.');
				} else {
					renderDetail(res.rows || [], res);
					var parts = [];
					if (scopeFrom && scopeTo) parts.push(scopeFrom + ' to ' + scopeTo);
					if (dayEl.value) parts.push('day ' + dayEl.value);
					if (sourceEl.value) parts.push(sourceEl.options[sourceEl.selectedIndex].text);
					setStatus((res.line_count || 0) + ' line(s)' + (parts.length ? ' (' + parts.join(', ') + ')' : '') + '.');
				}
			})
			.catch(function (err) {
				setStatus(err.message);
			})
			.then(function () {
				loadBtn.disabled = false;
			});
	}

	modeEl.addEventListener('change', function () {
		syncFilters();
		scopeFrom = '';
		scopeTo = '';
	});
	loadBtn.addEventListener('click', function () { loadDrill(true); });
	syncFilters();
	modeEl.value = 'year';
	loadDrill(true);
})();
</script>
<?php } ?>
<?php if ($export_mode === 'pdf') { ?>
<script>
window.onload = function() { window.print(); };
</script>
<?php } ?>
<?php $this->load->view('partials/report_ajax_shell'); ?>
</body>
</html>
