<?php
static $report_print_header_styles_loaded = FALSE;

if (!isset($business_config) || !is_array($business_config))
{
	$CI =& get_instance();
	$CI->load->model('Business_config_model');
	$business_config = $CI->Business_config_model->get_config();
}

$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';
$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';
$img_base = rtrim(base_url(), '/').'/';

if (!$report_print_header_styles_loaded)
{
	$report_print_header_styles_loaded = TRUE;
?>
<style>
.report-order-header-wrap { padding: 18px 24px 12px; border-bottom: 1px solid #e6edf5; }
.report-order-header-wrap table { width: 100%; }
.report-order-header-wrap .urd { font-weight: bold; font-family: 'Noto Nastaliq Urdu Draft', lateef, xbriyaz, serif; }
.report-order-header-wrap .ur { font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', lateef, xbriyaz, sans-serif; }
</style>
<?php } ?>
<div class="report-order-header-wrap">
	<table>
		<tr>
			<td align="center" width="20%">
				<img src="<?php echo $img_base; ?>images/Logo 1.png" style="height:150px; padding:0;">
			</td>
			<td></td>
			<td align="center">
				<img src="<?php echo $img_base; ?>images/Logo 3.jpg" style="height:150px; float:right; padding:0;">
			</td>
		</tr>
		<tr>
			<td style="font-weight:bold;" align="center">Ph: <?php echo esc_html($print_phone); ?></td>
			<td align="right" colspan="2">
				<span style="margin-right:20px; font-weight:bold; color:white; background:black; border-radius:10px; width:63px; float:right; text-align:center;"> سپشلسٹ </span>
				<span style="margin-right:20px; font-weight:bold; color:white; background:black; border-radius:10px; width:200px; float:right; text-align:center;"> پینٹ کوٹ،ویسکوٹ،شلوارقمیض </span>
				<span class="ur urd" style="margin-right:20px; font-weight:bold; width:250px; float:right; text-align:center; direction:rtl;"><?php echo esc_html($print_address); ?></span>
			</td>
		</tr>
	</table>
</div>
