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
$prev_balance = isset($orders->prev_balance) ? (float) $orders->prev_balance : 0;

$rdate = ! empty($orders->RDATE) ? date('d-m-Y', strtotime($orders->RDATE)) : '';

$odate = ! empty($orders->ORDERDATE) ? date('d-m-Y', strtotime($orders->ORDERDATE)) : '';

$logo = $pdf_img('images/darzi-baba-logo.png');
$customer_photo = isset($orders->image) ? trim((string) $orders->image) : '';
$has_customer_photo = ($customer_photo !== '');

?>

<div class="order-pdf">
<table width="100%" cellpadding="2" cellspacing="0" class="hdr">
	<tr>
		<td width="18%" align="left" valign="middle" class="hdr-trnno"><b><?php echo (int) $orders->TRNNO; ?></b></td>
		<td width="64%" align="center" valign="top">
			<?php if ($logo) { ?><img src="<?php echo $logo; ?>" style="height:72px; width:auto; max-width:240px;" alt="Darzi Baba"><br><?php } ?>
			<span class="hdr-address"><?php echo esc_html($print_address); ?></span><br>
			<span class="hdr-phone">Ph: <?php echo esc_html($print_phone); ?></span>
		</td>
		<td width="18%" align="right" valign="top">
			<span class="hdr-badge">سپشلسٹ</span>
		</td>
	</tr>
</table>



<table width="100%" cellpadding="0" cellspacing="0" class="info-wrap">
<tr>
<?php if ($has_customer_photo) { ?>
<td width="18%" align="center" valign="top" style="padding:2px 6px 2px 0;">
	<img src="<?php echo esc_html($customer_photo); ?>" width="48" height="48" style="width:48px;height:48px;max-width:48px;max-height:48px;object-fit:cover;border:1px solid #111;" alt="">
</td>
<?php } ?>
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
		<td colspan="2" class="i-blank"></td>
	</tr>
	<tr><td colspan="6" class="i-gap">&nbsp;</td></tr>
	<tr>
		<th class="i-lbl">سوٹ تعداد</th>
		<td class="i-val"><?php echo number_format(round((float) $orders->qty), 0, '.', ','); ?></td>
		<th class="i-lbl">ایڈوانس</th>
		<td class="i-val"><?php echo number_format(round((float) $orders->advance), 0, '.', ','); ?></td>
		<th class="i-lbl">رقم</th>
		<td class="i-val"><?php echo number_format(round((float) $balance), 0, '.', ','); ?></td>
	</tr>
	<tr><td colspan="6" class="i-gap">&nbsp;</td></tr>
	<tr>
		<th class="i-lbl">سابقہ بقایا</th>
		<td class="i-val"><?php echo number_format(round((float) $prev_balance), 0, '.', ','); ?></td>
		<td colspan="4" class="i-blank"></td>
	</tr>
</table>
</td>
</tr>
</table>



<table width="100%" cellpadding="0" cellspacing="0" class="body" dir="ltr" style="table-layout:fixed;">
	<tr>
		<td class="col-meas" valign="top" style="width:72mm;">
			<?php
			if ( ! isset($types) || ! is_array($types))
			{
				$types = (isset($type) && $type !== '') ? array($type) : array();
			}
			extract(compact('order', 'type', 'types'), EXTR_SKIP);
			include APPPATH.'views/partials/order_measurements_table.php';
			?>
		</td>
		<td class="col-items" valign="top" style="width:114mm;">
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

<table width="100%" cellpadding="8" cellspacing="0" class="credit" dir="rtl">
	<tr>
		<td align="center" class="credit-text">
			یہ سافٹ ویئر <b>عزیز الرحمن</b> نے تیار کیا ہے
			<br>
			ایسا سافٹ ویئر یا کوئی بھی سافٹ ویئر بنوانا ہو تو رابطہ کریں:
			<br>
			<span class="credit-phone" dir="ltr">0306-0407042</span>
		</td>
	</tr>
</table>

</div>

