<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$payload = isset($payload) && is_array($payload) ? $payload : array();
$title = isset($payload['title']) ? (string) $payload['title'] : 'Report';
$columns = isset($payload['columns']) && is_array($payload['columns']) ? $payload['columns'] : array();
$rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : array();
$totals = isset($payload['totals']) && is_array($payload['totals']) ? $payload['totals'] : array();
$summary = isset($payload['summary']) && is_array($payload['summary']) ? $payload['summary'] : array();
$shop = '';
$phone = '';
$address = '';
if (isset($this->business_config) || TRUE)
{
	$CI =& get_instance();
	if (!isset($CI->business_config))
	{
		$CI->load->model('Business_config_model', 'business_config');
	}
	if (isset($CI->business_config) && method_exists($CI->business_config, 'get_config'))
	{
		$cfg = $CI->business_config->get_config();
		if (is_object($cfg) || is_array($cfg))
		{
			$cfg = (object) $cfg;
			$shop = isset($cfg->location_name) ? (string) $cfg->location_name : '';
			$phone = isset($cfg->phone) ? (string) $cfg->phone : '';
			$address = isset($cfg->address_urdu) ? (string) $cfg->address_urdu : '';
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
	body { font-family: dejavusans, sans-serif; color: #0f172a; font-size: 10pt; }
	.header { border-bottom: 2px solid #0ea5e9; padding-bottom: 10px; margin-bottom: 14px; text-align: center; }
	.brand-logo { height: 72px; width: auto; max-width: 240px; margin-bottom: 6px; }
	.brand { font-size: 14pt; font-weight: bold; color: #0f172a; }
	.meta { color: #64748b; font-size: 9pt; margin-top: 4px; }
	.title { font-size: 14pt; font-weight: bold; margin: 8px 0 4px; }
	.summary { margin: 8px 0 12px; }
	.summary span { display: inline-block; margin-right: 18px; color: #475569; }
	.totals { margin: 0 0 12px; }
	.total-box { display: inline-block; border: 1px solid #e2e8f0; background: #f8fafc; padding: 6px 10px; margin-right: 8px; margin-bottom: 6px; border-radius: 4px; }
	.total-box .lbl { font-size: 8pt; color: #64748b; }
	.total-box .val { font-size: 11pt; font-weight: bold; }
	table { width: 100%; border-collapse: collapse; }
	th { background: #0ea5e9; color: #fff; padding: 6px 5px; font-size: 8.5pt; text-align: left; }
	td { border-bottom: 1px solid #e2e8f0; padding: 5px; font-size: 8.5pt; }
	tr:nth-child(even) td { background: #f8fafc; }
	.num { text-align: right; }
	.empty { text-align: center; color: #94a3b8; padding: 24px; }
	.footer { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8pt; color: #64748b; }
</style>
</head>
<body>
<?php
$logo_path = rtrim(str_replace('\\', '/', FCPATH), '/').'/images/darzi-baba-logo.png';
$logo_src = is_file($logo_path) ? $logo_path : '';
?>
<div class="header">
	<?php if ($logo_src !== '') { ?>
	<img class="brand-logo" src="<?php echo htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Darzi Baba">
	<?php } ?>
	<div class="brand"><?php echo htmlspecialchars($shop !== '' ? $shop : 'Darzi Baba', ENT_QUOTES, 'UTF-8'); ?></div>
	<div class="meta">
		<?php if ($phone !== '') { echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8').' · '; } ?>
		<?php if ($address !== '') { echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8').' · '; } ?>
		Generated <?php echo date('d-m-Y h:i A'); ?>
	</div>
</div>
<div class="title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
<?php if (!empty($summary)) { ?>
<div class="summary">
	<?php foreach ($summary as $item) { ?>
	<span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>:
		<strong><?php echo htmlspecialchars((string) $item['value'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
	<?php } ?>
</div>
<?php } ?>
<?php if (!empty($totals)) { ?>
<div class="totals">
	<?php foreach ($totals as $item) { ?>
	<div class="total-box">
		<div class="lbl"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></div>
		<div class="val"><?php echo htmlspecialchars((string) $item['value'], ENT_QUOTES, 'UTF-8'); ?></div>
	</div>
	<?php } ?>
</div>
<?php } ?>
<?php if (empty($columns)) { ?>
<div class="empty"><?php echo htmlspecialchars(t('report_no_data'), ENT_QUOTES, 'UTF-8'); ?></div>
<?php } else { ?>
<table>
	<thead>
		<tr>
			<?php foreach ($columns as $col) { ?>
			<th><?php echo htmlspecialchars((string) $col, ENT_QUOTES, 'UTF-8'); ?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php if (empty($rows)) { ?>
		<tr><td class="empty" colspan="<?php echo count($columns); ?>"><?php echo htmlspecialchars(t('report_no_data'), ENT_QUOTES, 'UTF-8'); ?></td></tr>
		<?php } else { ?>
		<?php foreach ($rows as $row) { ?>
		<tr>
			<?php foreach ($row as $idx => $cell) {
				$is_num = is_numeric(str_replace(',', '', (string) $cell));
			?>
			<td class="<?php echo $is_num ? 'num' : ''; ?>"><?php echo htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8'); ?></td>
			<?php } ?>
		</tr>
		<?php } ?>
		<?php } ?>
	</tbody>
</table>
<?php } ?>
<div class="footer">Confidential shop report · Darzibaba Reporting System</div>
</body>
</html>
