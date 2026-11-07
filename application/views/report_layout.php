<?php
$report_title = isset($report_title) ? (string) $report_title : 'Report';
$report_form_action = isset($report_form_action) ? (string) $report_form_action : '';
$report_fields = isset($report_fields) && is_array($report_fields) ? $report_fields : array();
$report_columns = isset($report_columns) && is_array($report_columns) ? $report_columns : array();
$report_rows = isset($report_rows) && is_array($report_rows) ? $report_rows : array();
$report_summary = isset($report_summary) && is_array($report_summary) ? $report_summary : array();
$report_totals = isset($report_totals) && is_array($report_totals) ? $report_totals : array();
$report_extra_sections = isset($report_extra_sections) && is_array($report_extra_sections) ? $report_extra_sections : array();
$report_has_report = isset($report_has_report) ? (bool) $report_has_report : false;
$report_empty_message = isset($report_empty_message) ? (string) $report_empty_message : t('report_no_data');
$report_hide_date_presets = ! empty($report_hide_date_presets);
// All reports: From/To stay empty until the user picks dates (or a preset).
$report_no_default_dates = TRUE;
$export_mode = isset($export_mode) ? (string) $export_mode : strtolower(trim((string) $this->input->get('export', TRUE)));
$export_mode = ($export_mode === 'excel' || $export_mode === 'pdf') ? $export_mode : '';
$report_excel_url = isset($report_excel_url) ? (string) $report_excel_url : current_url().'?export=excel';
$report_pdf_url = isset($report_pdf_url) ? (string) $report_pdf_url : current_url().'?export=pdf';
$report_excel_filename = isset($report_excel_filename) ? (string) $report_excel_filename : strtolower(str_replace(' ', '-', $report_title)).'.xls';
// center | left | '' (legacy: numbers right, first col center)
$report_align_all = isset($report_align_all) ? strtolower(trim((string) $report_align_all)) : 'center';
if ($report_align_all !== 'center' && $report_align_all !== 'left' && $report_align_all !== '') {
	$report_align_all = 'center';
}
$report_field_values = isset($report_field_values) && is_array($report_field_values) ? $report_field_values : array();
$report_result_base = isset($report_result_base) ? rtrim((string) $report_result_base, '/') : '';
$report_filter_url = isset($report_filter_url) ? (string) $report_filter_url : '';
if ($report_filter_url === '') {
	$seg1 = (string) $this->uri->segment(1);
	$seg2 = (string) $this->uri->segment(2);
	if ($seg1 !== '' && $seg2 !== '') {
		$report_filter_url = site_url($seg1.'/'.$seg2);
	} elseif ($report_form_action !== '') {
		$report_filter_url = $report_form_action;
	} else {
		$report_filter_url = site_url('Reports');
	}
}
// Prefill filter fields from ?fromdate=&todate=… when returning via Back.
foreach ($report_fields as $_rf) {
	$_rn = isset($_rf['name']) ? (string) $_rf['name'] : '';
	if ($_rn === '') {
		continue;
	}
	$_cur = isset($report_field_values[$_rn]) ? trim((string) $report_field_values[$_rn]) : '';
	if ($_cur !== '') {
		continue;
	}
	$_get = $this->input->get($_rn, TRUE);
	if ($_get !== NULL && trim((string) $_get) !== '') {
		$report_field_values[$_rn] = trim((string) $_get);
	}
}
// Keep selected filters on "Back to filters" / browser Back.
if ($report_has_report && ! empty($report_field_values) && strpos($report_filter_url, 'fromdate=') === FALSE) {
	$_keep = array();
	foreach ($report_field_values as $_k => $_v) {
		$_v = trim((string) $_v);
		if ($_v !== '') {
			$_keep[$_k] = $_v;
		}
	}
	if ( ! empty($_keep)) {
		$report_filter_url .= (strpos($report_filter_url, '?') !== FALSE ? '&' : '?').http_build_query($_keep);
	}
}

if (!function_exists('report_column_is_date')) {
	function report_column_is_date($column_name)
	{
		return stripos((string) $column_name, 'date') !== FALSE;
	}
}
if (!function_exists('report_column_is_phone')) {
	function report_column_is_phone($column_name)
	{
		return stripos((string) $column_name, 'phone') !== FALSE;
	}
}
if (!function_exists('report_column_is_order')) {
	function report_column_is_order($column_name)
	{
		$n = strtolower(trim((string) $column_name));
		return ($n === 'order' || $n === 'order #' || $n === 'order no' || strpos($n, 'order #') === 0);
	}
}
if (!function_exists('report_column_layout_class')) {
	function report_column_layout_class($column_name)
	{
		if (report_column_is_date($column_name)) {
			return 'col-date';
		}
		if (report_column_is_phone($column_name)) {
			return 'col-phone';
		}
		if (report_column_is_order($column_name)) {
			return 'col-order';
		}
		return '';
	}
}
if (!function_exists('report_cell_class')) {
	function report_cell_class($column_name, $cell, $idx)
	{
		$classes = array();
		$layout = report_column_layout_class($column_name);
		if ($layout !== '') {
			$classes[] = $layout;
		}
		$align = isset($GLOBALS['report_align_all_runtime']) ? (string) $GLOBALS['report_align_all_runtime'] : 'center';
		if ($align === 'center') {
			$classes[] = 'center';
			return implode(' ', $classes);
		}
		if ($align === 'left') {
			$classes[] = 'text-left';
			return implode(' ', $classes);
		}
		if (is_numeric(str_replace(',', '', (string) $cell))) {
			$classes[] = 'num';
		} elseif ($idx === 0) {
			$classes[] = 'center';
		}
		return implode(' ', $classes);
	}
}
if (!function_exists('report_header_class')) {
	function report_header_class($column_name)
	{
		return report_column_layout_class($column_name);
	}
}
if (!function_exists('report_field_value')) {
	function report_field_value($name, $values)
	{
		return isset($values[$name]) ? (string) $values[$name] : '';
	}
}
$GLOBALS['report_align_all_runtime'] = $report_align_all;

if ($export_mode === 'excel') {
	header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
	header('Content-Disposition: attachment; filename="'.$report_excel_filename.'"');
	header('Pragma: no-cache');
	header('Expires: 0');
}
?>
<!DOCTYPE html>
<html lang="<?php echo app_language() === 'urdu' ? 'ur' : 'en'; ?>" dir="<?php echo app_is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo esc_html($report_title); ?></title>
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>?v=portrait-a4-2">
<style>
	body.report-body { background: #f4f7fb; color: #1f2933; font-family: Arial, sans-serif; }
	.report-shell { max-width: 1180px; margin: 18px auto; padding: 0 14px; }
	.report-card { background: #fff; border: 1px solid #e6edf5; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 35, 65, .08); overflow: hidden; }
	.report-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding: 12px 16px; background: #eef7f8; border-bottom: 1px solid #dcebee; }
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
	.report-form-grid { display: grid; grid-template-columns: repeat(4, minmax(150px, 1fr)); gap: 14px; align-items: end; }
	.report-presets { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 14px; }
	.report-preset-btn { border: 1px solid #d7e0ea; background: #f7fbfc; color: #334155; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
	.report-preset-btn:hover { border-color: #16a1ad; color: #0f766e; }
	.report-field label { display: block; font-weight: 700; margin-bottom: 5px; }
	.report-field input, .report-field select { width: 100%; border: 1px solid #d7e0ea; border-radius: 8px; padding: 9px 10px; box-sizing: border-box; }
	.report-table-wrap { padding: 18px 24px 24px; }
	.report-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 0 0 16px; }
	.report-summary div { background: #f7fbfc; border: 1px solid #dfecef; border-radius: 10px; padding: 12px; }
	.report-summary span { display: block; color: #52616d; font-size: 12px; }
	.report-summary strong { font-size: 18px; }
	.report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
	.report-table th { background: #1f9faa; color: #fff; padding: 9px; border: 1px solid #128995; text-align: center; }
	.report-table td { padding: 8px; border: 1px solid #dde6ef; }
	.report-table .num { text-align: right; }
	.report-table .center { text-align: center; }
	.report-table .text-left { text-align: left; }
	.report-table.align-center th,
	.report-table.align-center td { text-align: center !important; }
	.report-table.align-left th,
	.report-table.align-left td { text-align: left !important; }
	.report-table .col-date { white-space: nowrap; width: 1%; min-width: 92px; text-align: center; }
	.report-footer-note { background: #d9d9d9; padding: 8px 20px; display: flex; justify-content: space-between; gap: 10px; font-weight: 700; }
	.signature-line { margin: 45px 24px 22px; width: 280px; border-top: 1px dotted #222; text-align: center; padding-top: 5px; }
	.empty-state { padding: 24px; color: #687782; text-align: center; }
	.text-right { text-align: right; }
</style>
</head>
<body class="report-body" data-report-has="<?php echo $report_has_report ? '1' : '0'; ?>" data-report-filter-url="<?php echo esc_attr($report_filter_url); ?>">
<div class="report-shell">
	<div class="report-card">
		<?php if ($export_mode !== 'excel') { ?>
		<div class="report-actions print-hide">
			<?php if ($report_has_report) { ?>
			<a class="report-btn ghost" href="<?php echo esc_html($report_filter_url); ?>" id="report-back-filters"><?php echo esc_html(t('report_back_filters')); ?></a>
			<?php } ?>
			<button type="button" class="report-btn" onclick="window.print()"><?php echo t('report_print_pdf'); ?></button>
			<?php if ($report_has_report) { ?>
			<a class="report-btn secondary" href="<?php echo $report_excel_url; ?>"><?php echo t('report_export_excel'); ?></a>
			<a class="report-btn secondary" href="<?php echo $report_pdf_url; ?>" target="_blank"><?php echo t('report_open_pdf'); ?></a>
			<?php } ?>
		</div>
		<?php } ?>
		<?php $this->load->view('report_print_header'); ?>
		<div class="report-title">
			<div>
				<h2><?php echo esc_html($report_title); ?></h2>
				<div class="report-meta">
					<?php foreach ($report_summary as $item) { ?>
					<?php echo esc_html($item['label']); ?>: <strong><?php echo esc_html((string) $item['value']); ?></strong><br>
					<?php } ?>
				</div>
			</div>
			<div class="report-meta"><?php echo t('report_generated'); ?>: <?php echo date('d-m-Y h:i A'); ?></div>
		</div>

		<?php if ($export_mode !== 'excel' && $export_mode !== 'pdf') { ?>
		<form class="report-form print-hide" action="<?php echo esc_html($report_form_action); ?>" method="post" data-no-ajax="true" id="report-filter-form" data-result-base="<?php echo esc_attr($report_result_base); ?>">
			<?php $this->load->view('partials/csrf_field'); ?>
			<?php if ($report_has_report) { ?>
			<p class="report-meta" style="margin:0 0 10px;"><?php echo esc_html(t('report_change_filters')); ?></p>
			<?php } ?>
			<?php if ( ! $report_hide_date_presets) { ?>
			<div class="report-presets" role="group" aria-label="Date presets">
				<button type="button" class="report-preset-btn" data-days="0">Today</button>
				<button type="button" class="report-preset-btn" data-days="6">Last 7 days</button>
				<button type="button" class="report-preset-btn" data-days="29">Last 30 days</button>
				<button type="button" class="report-preset-btn" data-mode="this_month">This month</button>
				<button type="button" class="report-preset-btn" data-mode="last_month">Last month</button>
			</div>
			<?php } ?>
			<div class="report-form-grid">
				<?php foreach ($report_fields as $field) {
					$fname = isset($field['name']) ? (string) $field['name'] : '';
					$fval = report_field_value($fname, $report_field_values);
				?>
				<div class="report-field">
					<label><?php echo esc_html($field['label']); ?></label>
					<?php if (isset($field['type']) && $field['type'] === 'select') { ?>
					<select name="<?php echo esc_html($fname); ?>">
						<?php foreach ($field['options'] as $value => $label) { ?>
						<option value="<?php echo esc_html((string) $value); ?>"<?php echo ((string) $value === $fval) ? ' selected' : ''; ?>><?php echo esc_html((string) $label); ?></option>
						<?php } ?>
					</select>
					<?php } else { ?>
					<input type="<?php echo isset($field['type']) ? esc_html($field['type']) : 'text'; ?>" name="<?php echo esc_html($fname); ?>" id="rf-<?php echo esc_html($fname); ?>" value="<?php echo esc_attr($fval); ?>" autocomplete="off" <?php echo (!isset($field['type']) || $field['type'] === 'date') ? 'required' : ''; ?>>
					<?php } ?>
				</div>
				<?php } ?>
				<div class="report-field"><button type="submit" class="report-btn" style="width:100%;"><?php echo esc_html(t('report_generate')); ?></button></div>
			</div>
		</form>
		<?php if ( ! $report_hide_date_presets || ! $report_no_default_dates || $report_result_base !== '' || $report_has_report) { ?>
		<script>
		(function(){
			function ymd(d){var m=d.getMonth()+1,day=d.getDate();return d.getFullYear()+'-'+(m<10?'0':'')+m+'-'+(day<10?'0':'')+day;}
			function fill(from,to){
				var a=document.getElementById('rf-fromdate')||document.querySelector('input[name="fromdate"]');
				var b=document.getElementById('rf-todate')||document.querySelector('input[name="todate"]');
				if(a)a.value=from; if(b)b.value=to;
			}
			<?php if ( ! $report_hide_date_presets) { ?>
			document.querySelectorAll('.report-preset-btn').forEach(function(btn){
				btn.addEventListener('click',function(){
					var today=new Date(); today.setHours(0,0,0,0);
					var from=new Date(today), to=new Date(today);
					var mode=btn.getAttribute('data-mode');
					var days=parseInt(btn.getAttribute('data-days')||'0',10);
					if(mode==='this_month'){ from=new Date(today.getFullYear(),today.getMonth(),1); }
					else if(mode==='last_month'){ from=new Date(today.getFullYear(),today.getMonth()-1,1); to=new Date(today.getFullYear(),today.getMonth(),0); }
					else { from.setDate(from.getDate()-days); }
					fill(ymd(from), ymd(to));
				});
			});
			<?php } ?>

			var form = document.getElementById('report-filter-form');
			var resultBase = form ? String(form.getAttribute('data-result-base') || '') : '';
			if (form && resultBase) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					var fromEl = form.querySelector('[name="fromdate"]');
					var toEl = form.querySelector('[name="todate"]');
					var from = fromEl ? String(fromEl.value || '').trim() : '';
					var to = toEl ? String(toEl.value || '').trim() : '';
					if (!from || !to) { return; }
					var url = resultBase + '/' + encodeURIComponent(from) + '/' + encodeURIComponent(to);
					var extraNames = ['emp_type', 'stage', 'employee', 'group', 'rtype'];
					for (var i = 0; i < extraNames.length; i++) {
						var el = form.querySelector('[name="' + extraNames[i] + '"]');
						if (!el) continue;
						var v = String(el.value || '').trim();
						if (v !== '') {
							url += '/' + encodeURIComponent(v);
							break;
						}
					}
					window.location.href = url;
				});
			}

			// Browser Back from a generated report → filter screen (not stuck on same report).
			var hasReport = document.body.getAttribute('data-report-has') === '1';
			var filterUrl = String(document.body.getAttribute('data-report-filter-url') || '');
			if (hasReport && filterUrl) {
				try {
					var st = history.state || {};
					if (!st.dzReportResult) {
						history.replaceState({ dzReportFilter: true }, '', filterUrl);
						history.pushState({ dzReportResult: true }, '', window.location.href);
					}
				} catch (errHist) { /* ignore */ }
				window.addEventListener('popstate', function (ev) {
					var s = ev.state || {};
					if (s.dzReportFilter || !s.dzReportResult) {
						window.location.replace(filterUrl);
					}
				});
			}
		})();
		</script>
		<?php } ?>
		<?php } ?>

		<?php if ($report_has_report) { ?>
		<div class="report-table-wrap">
			<?php if (!empty($report_totals)) { ?>
			<div class="report-summary">
				<?php foreach ($report_totals as $item) { ?>
				<div><span><?php echo esc_html($item['label']); ?></span><strong><?php echo esc_html((string) $item['value']); ?></strong></div>
				<?php } ?>
			</div>
			<?php } ?>
			<?php foreach ($report_extra_sections as $section) { ?>
			<?php
				$section_title = isset($section['title']) ? (string) $section['title'] : '';
				$section_columns = isset($section['columns']) && is_array($section['columns']) ? $section['columns'] : array();
				$section_rows = isset($section['rows']) && is_array($section['rows']) ? $section['rows'] : array();
				$section_empty = isset($section['empty']) ? (string) $section['empty'] : 'No data found.';
			?>
			<?php if (!empty($section_columns)) { ?>
			<h3 style="margin:18px 0 8px;"><?php echo esc_html($section_title); ?></h3>
			<table class="report-table<?php echo $report_align_all !== '' ? ' align-'.$report_align_all : ''; ?>" style="margin-bottom:18px;">
				<thead><tr>
					<?php foreach ($section_columns as $column) { ?>
					<th class="<?php echo report_header_class($column); ?>"><?php echo esc_html($column); ?></th>
					<?php } ?>
				</tr></thead>
				<tbody>
					<?php if (!empty($section_rows)) { ?>
					<?php foreach ($section_rows as $row) { ?>
					<tr>
						<?php foreach ($row as $idx => $cell) { ?>
						<td class="<?php echo report_cell_class(isset($section_columns[$idx]) ? $section_columns[$idx] : '', $cell, $idx); ?>"><?php echo esc_html((string) $cell); ?></td>
						<?php } ?>
					</tr>
					<?php } ?>
					<?php } else { ?>
					<tr><td colspan="<?php echo count($section_columns); ?>" class="empty-state"><?php echo esc_html($section_empty); ?></td></tr>
					<?php } ?>
				</tbody>
			</table>
			<?php } ?>
			<?php } ?>
			<table class="report-table<?php echo $report_align_all !== '' ? ' align-'.$report_align_all : ''; ?>">
				<thead><tr>
					<?php foreach ($report_columns as $column) { ?>
					<th class="<?php echo report_header_class($column); ?>"><?php echo esc_html($column); ?></th>
					<?php } ?>
				</tr></thead>
				<tbody>
					<?php if (!empty($report_rows)) { ?>
					<?php foreach ($report_rows as $row) { ?>
					<tr>
						<?php foreach ($row as $idx => $cell) { ?>
						<td class="<?php echo report_cell_class(isset($report_columns[$idx]) ? $report_columns[$idx] : '', $cell, $idx); ?>"><?php echo esc_html((string) $cell); ?></td>
						<?php } ?>
					</tr>
					<?php } ?>
					<?php } else { ?>
					<tr><td colspan="<?php echo count($report_columns); ?>" class="empty-state"><?php echo esc_html($report_empty_message); ?></td></tr>
					<?php } ?>
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
window.onload = function() { window.print(); };
</script>
<?php } ?>
<?php $this->load->view('partials/report_ajax_shell'); ?>
</body>
</html>
