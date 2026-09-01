<?php

$business_config = isset($business_config) && is_array($business_config) ? $business_config : array();

$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';

$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';

$base = isset($print_img_base) ? $print_img_base : rtrim(str_replace('\\', '/', FCPATH), '/').'/';

$pdf_img = function ($rel) use ($base) {

	$rel = ltrim(str_replace('\\', '/', (string) $rel), '/');

	$path = $base.$rel;

	if ( ! is_file(str_replace('%20', ' ', $path)))

	{

		return '';

	}

	return str_replace('\\', '/', $path);

};

if (empty($order))

{

	return;

}

foreach ($order as $orders) {

	break;

}

$balance = (float) $orders->total - (float) $orders->advance - (float) $orders->discount;

$rdate = ! empty($orders->RDATE) ? date('d-m-Y', strtotime($orders->RDATE)) : '';

$odate = ! empty($orders->ORDERDATE) ? date('d-m-Y', strtotime($orders->ORDERDATE)) : '';

$logo1 = $pdf_img('images/Logo 1.png');

$logo3 = $pdf_img('images/Logo 3.jpg');

?>

<div class="order-pdf">
<table width="100%" cellpadding="2" cellspacing="0" class="hdr">
	<tr>
		<td width="20%" align="center" valign="top">
			<?php if ($logo1) { ?><img src="<?php echo $logo1; ?>" style="height:110px;"><?php } ?>
		</td>
		<td width="5%"></td>
		<td width="75%" align="center" valign="top">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="12%" align="left" valign="middle" class="hdr-trnno"><b><?php echo (int) $orders->TRNNO; ?></b></td>
					<td width="76%" align="center"><?php if ($logo3) { ?><img src="<?php echo $logo3; ?>" style="height:110px;"><?php } ?></td>
					<td width="12%"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" class="hdr-phone">Ph: <?php echo esc_html($print_phone); ?></td>
		<td></td>
		<td align="right" class="hdr-badges">
			<span class="hdr-address"><?php echo esc_html($print_address); ?></span><br>
			<span class="hdr-badge">سپشلسٹ</span>
			<span class="hdr-badge">پینٹ کوٹ،ویسکوٹ،شلوارقمیض</span>
		</td>
	</tr>
</table>



<table width="100%" cellpadding="0" cellspacing="0" class="info-wrap">
<tr>
<td align="right">
<table cellpadding="1" cellspacing="0" class="info" dir="rtl" style="table-layout:fixed;line-height:18px;">
<colgroup>
	<col style="width:28mm">
	<col style="width:24mm">
	<col style="width:28mm">
	<col style="width:24mm">
	<col style="width:28mm">
	<col style="width:24mm">
</colgroup>
	<tr><td colspan="6" class="i-gap">&nbsp;</td></tr>
	<tr>
		<th class="i-lbl">نام</th>
		<td class="i-val"><?php echo esc_html($orders->name); ?></td>
		<th class="i-lbl">ناپ نمبر</th>
		<td class="i-val"><?php echo esc_html($orders->man_napno); ?></td>
		<td colspan="2" class="i-blank"></td>
	</tr>
	<tr><td colspan="6" class="i-gap">&nbsp;</td></tr>
	<tr>
		<th class="i-lbl">تاریخ</th>
		<td class="i-val"><?php echo $odate; ?></td>
		<th class="i-lbl">تاریخ واپسی</th>
		<td class="i-val"><?php echo $rdate; ?></td>
		<th class="i-lbl i-left">رات 00۔08 بجے</th>
		<td class="i-blank"></td>
	</tr>
	<tr><td colspan="6" class="i-gap">&nbsp;</td></tr>
	<tr>
		<th class="i-lbl">سوٹ تعداد</th>
		<td class="i-val"><?php echo $orders->qty; ?></td>
		<th class="i-lbl">ایڈوانس</th>
		<td class="i-val"><?php echo $orders->advance; ?></td>
		<th class="i-lbl">بقایا</th>
		<td class="i-val"><?php echo $balance; ?></td>
	</tr>
</table>
</td>
</tr>
</table>



<table width="100%" cellpadding="0" cellspacing="0" class="body" dir="ltr" style="table-layout:fixed;">
	<tr>
		<td class="col-meas" valign="top" style="width:88mm;">
			<?php
			extract(compact('order', 'type'), EXTR_SKIP);
			include APPPATH.'views/partials/order_measurements_table.php';
			?>
		</td>
		<td class="col-items" valign="top" style="width:98mm;">
			<?php
			extract(compact('order', 'suit', 'style', 'embroidery', 'pdf_img'), EXTR_SKIP);
			include APPPATH.'views/partials/order_items_table.php';
			?>
		</td>
	</tr>
</table>



<table width="100%" cellpadding="6" cellspacing="0" class="ftr">

	<tr>

		<td width="42%" align="center" class="ftr-big">ناغہ بروز جمعتہ المبارک</td>

		<td width="58%" align="right" class="ftr-sm">بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</td>

	</tr>

	<tr>

		<td></td>

		<td align="right" class="ftr-sm">دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</td>

	</tr>

</table>

</div>

