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
$export_mode = isset($export_mode) ? (string) $export_mode : strtolower(trim((string) $this->input->get('export', TRUE)));
$export_mode = ($export_mode === 'excel' || $export_mode === 'pdf') ? $export_mode : '';
$report_excel_url = isset($report_excel_url) ? (string) $report_excel_url : current_url().'?export=excel';
$report_pdf_url = isset($report_pdf_url) ? (string) $report_pdf_url : current_url().'?export=pdf';
$report_excel_filename = isset($report_excel_filename) ? (string) $report_excel_filename : strtolower(str_replace(' ', '-', $report_title)).'.xls';

if (!function_exists('report_column_is_date')) {
	function report_column_is_date($column_name)
	{
		return stripos((string) $column_name, 'date') !== FALSE;
	}
}
if (!function_exists('report_cell_class')) {
	function report_cell_class($column_name, $cell, $idx)
	{
		$classes = array();
		if (report_column_is_date($column_name)) {
			$classes[] = 'col-date';
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
		return report_column_is_date($column_name) ? 'col-date' : '';
	}
}

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
<link rel="stylesheet" href="<?php echo base_url('css/report-print-landscape.css'); ?>">
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
	.report-table .col-date { white-space: nowrap; width: 1%; min-width: 92px; text-align: center; }
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
		<?php if (!$report_has_report) { ?>
		<form class="report-form print-hide" action="<?php echo $report_form_action; ?>" method="post" data-no-ajax="true" id="report-filter-form">
			<?php $this->load->view('partials/csrf_field'); ?>
			<div class="report-presets" role="group" aria-label="Date presets">
				<button type="button" class="report-preset-btn" data-days="0">Today</button>
				<button type="button" class="report-preset-btn" data-days="6">Last 7 days</button>
				<button type="button" class="report-preset-btn" data-days="29">Last 30 days</button>
				<button type="button" class="report-preset-btn" data-mode="this_month">This month</button>
				<button type="button" class="report-preset-btn" data-mode="last_month">Last month</button>
			</div>
			<div class="report-form-grid">
				<?php foreach ($report_fields as $field) { ?>
				<div class="report-field">
					<label><?php echo esc_html($field['label']); ?></label>
					<?php if (isset($field['type']) && $field['type'] === 'select') { ?>
					<select name="<?php echo esc_html($field['name']); ?>">
						<?php foreach ($field['options'] as $value => $label) { ?>
						<option value="<?php echo esc_html((string) $value); ?>"><?php echo esc_html((string) $label); ?></option>
						<?php } ?>
					</select>
					<?php } else { ?>
					<input type="<?php echo isset($field['type']) ? esc_html($field['type']) : 'text'; ?>" name="<?php echo esc_html($field['name']); ?>" id="rf-<?php echo esc_html($field['name']); ?>" <?php echo (!isset($field['type']) || $field['type'] === 'date') ? 'required' : ''; ?>>
					<?php } ?>
				</div>
				<?php } ?>
				<div class="report-field"><button type="submit" class="report-btn" style="width:100%;"><?php echo t('app_submit'); ?></button></div>
			</div>
		</form>
		<script>
		(function(){
			function ymd(d){var m=d.getMonth()+1,day=d.getDate();return d.getFullYear()+'-'+(m<10?'0':'')+m+'-'+(day<10?'0':'')+day;}
			function fill(from,to){
				var a=document.getElementById('rf-fromdate')||document.querySelector('input[name="fromdate"]');
				var b=document.getElementById('rf-todate')||document.querySelector('input[name="todate"]');
				if(a)a.value=from; if(b)b.value=to;
			}
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
			// default last 7 days
			(function(){ var t=new Date(); t.setHours(0,0,0,0); var f=new Date(t); f.setDate(f.getDate()-6); fill(ymd(f),ymd(t)); })();
		})();
		</script>
		<?php } else { ?>
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
			<table class="report-table" style="margin-bottom:18px;">
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
			<table class="report-table">
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
