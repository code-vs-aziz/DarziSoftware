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
	$suit_note = ! empty($orders->order_remarks) ? trim((string) $orders->order_remarks) : '';
	$suit_note = preg_replace('/^سوٹ\s*تفصیل\s*[-–—:٫.\s]*/u', '', $suit_note);
	$suit_note = trim((string) $suit_note);
	$suit_end = '';
	if ( ! empty($suit)) {
		foreach ($suit as $suits) {
			if ($suits->SNO == $orders->SNO) {
				$suit_end = (string) $suits->name;
				break;
			}
		}
	}
	$detail_html = '';
	$style_body = preg_replace('/^(?:[\-\x{2013}\x{2014}:]+\s*)+/u', '', (string) $suit_note);
	$style_body = trim((string) $style_body);
	if ($style_body !== '' && preg_match('/^([^"\x{201C}:]+?)(\s*[\"\x{201C}:].*)$/u', $style_body, $cm)) {
		$detail_html = '<strong>'.esc_html(trim($cm[1])).'</strong>'.esc_html($cm[2]);
	} elseif ($style_body !== '' && preg_match('/^(\S+)(.*)$/u', $style_body, $cm)) {
		$detail_html = '<strong>'.esc_html($cm[1]).'</strong>'.esc_html($cm[2]);
	} elseif ($style_body !== '') {
		$detail_html = esc_html($style_body);
	}
	$type_raw = isset($orders->type) ? trim((string) $orders->type) : '';
	$type_note_map = array(
		'Kurta' => 'remarks_kurta',
		'Pajama' => 'remarks_pajama',
		'Pent' => 'remarks_pent',
		'Shirt' => 'remarks_shirt',
		'Coat' => 'remarks_coat',
		'Waistcoat' => 'remarks_waistcoat',
	);
	$nap_note = '';
	if (isset($type_note_map[$type_raw]) && isset($orders->{$type_note_map[$type_raw]})) {
		$nap_note = trim((string) $orders->{$type_note_map[$type_raw]});
	}
	if ($nap_note === '' && isset($orders->MREMARKS)) {
		$nap_note = trim((string) $orders->MREMARKS);
	}
	if ($nap_note !== '' && $suit_note !== '' && mb_stripos($suit_note, $nap_note) !== FALSE) {
		$nap_note = '';
	}
	$pocket_text = implode(' ', $pocket_parts);
	$price_text = $price != 0.0 ? rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') : '—';
?>
<table width="100%" cellpadding="1" cellspacing="0" class="items print-suit-classic" dir="ltr" style="table-layout:fixed;margin-bottom:2px;">
	<tr>
		<td class="lbl" colspan="3" style="border-bottom:1px dotted #111;direction:rtl;text-align:right;color:#111;">
			<?php echo (int) $SR; ?>-
			<?php echo $detail_html !== '' ? $detail_html : esc_html($suit_end); ?>
			<?php if ($detail_html !== '' && $suit_end !== '' && mb_stripos($suit_note, $suit_end) === FALSE) { ?>
			 :- <?php echo esc_html($suit_end); ?>
			<?php } ?>
			<?php if ($nap_note !== '') {
				$note_body = $nap_note;
				if (preg_match('/^\((.*)\)\s*$/us', $note_body, $nm)) {
					$note_body = trim($nm[1]);
				}
			?>
			 <span class="print-suit-classic__inline-note"><span class="print-suit-classic__note-brack">(</span><?php echo esc_html($note_body); ?><span class="print-suit-classic__note-brack">)</span></span>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="i-price val" style="width:14mm;border-bottom:1px dotted #111;color:#111;"><?php echo esc_html($price_text); ?></td>
		<td class="i-img" style="width:16mm;border-bottom:1px dotted #111;">
			<?php if ($fabric_src !== '') { ?><img src="<?php echo $fabric_src; ?>" style="height:28px;max-width:48px;"><?php } ?>
		</td>
		<td class="lbl" style="border-bottom:1px dotted #111;direction:rtl;text-align:right;color:#111;font-weight:bold;"><?php echo esc_html($pocket_text); ?></td>
	</tr>
</table>
<?php } ?>
