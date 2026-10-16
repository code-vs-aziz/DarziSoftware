<?php
$SR = 0;
foreach ($order as $orders) {
	$SR++;
	$price = (float) ($orders->suitrate ?? 0) + (float) ($orders->drate ?? 0) + (float) ($orders->erate ?? 0) + (float) ($orders->addprice ?? 0);
	$fabric_src = '';
	if ( ! empty($orders->kolor_style) && isset($pdf_img) && is_callable($pdf_img)) {
		$candidate = $pdf_img('images/'.$orders->kolor_style.'.jpg');
		if ($candidate !== '') {
			$fabric_src = $candidate;
		}
	}
	$pocket_parts = array();
	if (empty($orders->fpocket)) {
		$pocket_parts[] = '(فرنٹ پاکٹ X)';
	} else {
		$pocket_parts[] = '(فرنٹ پاکٹ '.$orders->fpocket.')';
	}
	if (empty($orders->spocket)) {
		$pocket_parts[] = '(سائیڈ پاکٹ X)';
	} else {
		$pocket_parts[] = '(سائیڈ پاکٹ '.$orders->spocket.')';
	}
	if (empty($orders->Shawlarpocket)) {
		$pocket_parts[] = '(شلوار پاکٹ X)';
	} else {
		$pocket_parts[] = '(شلوار پاکٹ '.$orders->Shawlarpocket.')';
	}
	$detail_parts = array();
	if ( ! empty($orders->order_remarks)) {
		$detail_parts[] = $orders->order_remarks;
	}
	if ( ! empty($suit)) {
		foreach ($suit as $suits) {
			if ($suits->SNO == $orders->SNO) {
				$detail_parts[] = $suits->name;
			}
		}
	}
	if ( ! empty($style)) {
		foreach ($style as $styles) {
			if (isset($styles->DNO, $orders->design) && $styles->DNO == $orders->design) {
				$detail_parts[] = $styles->vol_name.$styles->vol_no.' -DESIGN NO:'.$styles->design;
			}
		}
	}
	if ( ! empty($embroidery)) {
		foreach ($embroidery as $embroiderys) {
			if (isset($embroiderys->ENO, $orders->ENO) && $embroiderys->ENO == $orders->ENO) {
				$detail_parts[] = $embroiderys->embroidery_name.$embroiderys->vol_no.' -EMBROIDERY NO:'.$embroiderys->design;
			}
		}
	}
	// Measurement note (MREMARKS) belongs with measurements, not every suit line.
	$detail_text = implode(' · ', array_filter($detail_parts));
	$pocket_text = implode(' ', $pocket_parts);
?>
<table width="100%" cellpadding="1" cellspacing="0" class="items" dir="ltr" style="table-layout:fixed;margin-bottom:4px;">
<colgroup>
	<col style="width:8mm">
	<col style="width:14mm">
	<col style="width:16mm">
	<col>
</colgroup>
	<tr>
		<td class="i-no lbl">-<?php echo $SR; ?></td>
		<td class="val" colspan="3" style="border-bottom:1px dotted #999;"><?php echo esc_html($detail_text); ?></td>
	</tr>
	<tr>
		<td></td>
		<td class="i-price val" style="border-bottom:1px dotted #999;"><?php echo $price; ?></td>
		<td class="i-img" style="border-bottom:1px dotted #999;">
			<?php if ($fabric_src !== '') { ?><img src="<?php echo $fabric_src; ?>" style="height:28px;max-width:48px;"><?php } ?>
		</td>
		<td class="lbl" style="border-bottom:1px dotted #999;"><?php echo esc_html($pocket_text); ?></td>
	</tr>
</table>
<?php } ?>
