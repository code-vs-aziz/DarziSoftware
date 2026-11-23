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
	$type_raw_early = isset($orders->garment_type) ? trim((string) $orders->garment_type) : '';
	if ($type_raw_early === '' && isset($orders->type)) {
		$type_raw_early = trim((string) $orders->type);
	}
	// Pockets only for قمیض شلوار / پاجامہ — not ویسکوٹ or other types.
	$show_pockets = in_array($type_raw_early, array('Kurta', 'Pajama'), TRUE);
	$pocket_parts = array();
	if ($show_pockets) {
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
	$type_raw = isset($orders->garment_type) ? trim((string) $orders->garment_type) : '';
	if ($type_raw === '' && isset($orders->type)) {
		$type_raw = trim((string) $orders->type);
	}
	$nap_note = function_exists('measurement_garment_note_for_type')
		? measurement_garment_note_for_type($orders, $type_raw)
		: '';
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
			<?php if ($fabric_src !== '') { ?><img src="<?php echo $fabric_src; ?>" width="30" height="22" style="width:30px;height:22px;max-width:30px;max-height:22px;"><?php } ?>
		</td>
		<td class="lbl" style="border-bottom:1px dotted #111;direction:rtl;text-align:right;color:#111;font-weight:bold;"><?php echo esc_html($pocket_text); ?></td>
	</tr>
	<tr>
		<td colspan="3" style="border-bottom:2.5px solid #000;height:0;padding:0;line-height:0;font-size:0;">&nbsp;</td>
	</tr>
</table>
<?php } ?>
