<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Suit detail — old ERP compact pattern:
 *   1- {COLOR}{details} :- {suit}
 *   {price} [img]   (فرنٹ…) (سائیڈ…) (شلوار…)
 *   -----------------------
 */
if (empty($order) || ! is_array($order))
{
	return;
}
$pdf_mode = ! empty($pdf_mode);

$clean_text = function ($raw) {
	$raw = trim((string) $raw);
	$raw = preg_replace('/^سوٹ\s*تفصیل\s*[-–—:٫.\s]*/u', '', $raw);
	$raw = preg_replace('/\s+/u', ' ', (string) $raw);
	return trim((string) $raw);
};

$format_detail_html = function ($raw) use ($clean_text) {
	$raw = $clean_text($raw);
	if ($raw === '')
	{
		return '';
	}
	// Drop leftover leading :- / -: junk from old ERP copy.
	$raw = preg_replace('/^(?:[\-\x{2013}\x{2014}:]+\s*)+/u', '', $raw);
	$raw = trim((string) $raw);
	if ($raw === '')
	{
		return '';
	}
	// Color may be 1–2 words before first quote / :- delimiter.
	if (preg_match('/^([^"\x{201C}:]+?)(\s*[\"\x{201C}:].*)$/u', $raw, $m))
	{
		$color = trim($m[1]);
		$rest = $m[2];
		if ($color !== '')
		{
			return '<strong class="print-suit-card__color">'.esc_html($color).'</strong>'.esc_html($rest);
		}
	}
	if (preg_match('/^(\S+)(.*)$/u', $raw, $m))
	{
		return '<strong class="print-suit-card__color">'.esc_html($m[1]).'</strong>'.esc_html($m[2]);
	}
	return esc_html($raw);
};

$SR = 0;
foreach ($order as $line)
{
	$SR++;
	$price = (float) (isset($line->suitrate) ? $line->suitrate : 0)
		+ (float) (isset($line->drate) ? $line->drate : 0)
		+ (float) (isset($line->erate) ? $line->erate : 0)
		+ (float) (isset($line->addprice) ? $line->addprice : 0);

	$suit_name = '';
	if ( ! empty($suit))
	{
		foreach ($suit as $suits)
		{
			if (isset($suits->SNO, $line->SNO) && (string) $suits->SNO === (string) $line->SNO)
			{
				$suit_name = (string) $suits->name;
				break;
			}
		}
	}

	$type_raw = isset($line->type) ? trim((string) $line->type) : '';
	$type_urdu = array(
		'Kurta' => 'قمیض شلوار',
		'Pajama' => 'پاجامہ',
		'Pent' => 'پینٹ',
		'Shirt' => 'شرٹ',
		'Coat' => 'شروانی/کوٹ',
		'Waistcoat' => 'ویسکوٹ',
	);
	$type_label = isset($type_urdu[$type_raw]) ? $type_urdu[$type_raw] : $type_raw;
	$remarks = $clean_text(isset($line->order_remarks) ? $line->order_remarks : '');

	$fabric_src = '';
	$kolor = isset($line->kolor_style) ? trim((string) $line->kolor_style) : '';
	if ($kolor !== '')
	{
		if (isset($pdf_img) && is_callable($pdf_img))
		{
			$candidate = $pdf_img('images/'.$kolor.'.jpg');
			if ($candidate !== '')
			{
				$fabric_src = $candidate;
			}
		}
		else
		{
			$abs = FCPATH.'images/'.$kolor.'.jpg';
			if (is_file($abs))
			{
				$fabric_src = base_url('images/'.$kolor.'.jpg');
			}
		}
	}

	$fpocket = isset($line->fpocket) ? trim((string) $line->fpocket) : '';
	$spocket = isset($line->spocket) ? trim((string) $line->spocket) : '';
	$shawpocket = isset($line->Shawlarpocket) ? trim((string) $line->Shawlarpocket) : '';
	if ($fpocket === '') { $fpocket = 'X'; }
	if ($spocket === '') { $spocket = 'X'; }
	if ($shawpocket === '') { $shawpocket = 'X'; }

	$primary_title = $type_label;
	if ($suit_name !== '' && strcasecmp($suit_name, $type_raw) !== 0)
	{
		$primary_title = $suit_name;
	}
	$suit_end = $primary_title;
	if ($remarks !== '' && $suit_end !== '' && mb_stripos($remarks, $suit_end) !== FALSE)
	{
		$suit_end = '';
	}

	// Nap note goes on the detail line after suit name (no label, not under pockets).
	$type_note_map = array(
		'Kurta' => 'remarks_kurta',
		'Pajama' => 'remarks_pajama',
		'Pent' => 'remarks_pent',
		'Shirt' => 'remarks_shirt',
		'Coat' => 'remarks_coat',
		'Waistcoat' => 'remarks_waistcoat',
	);
	$nap_note = '';
	if ($type_raw !== '' && isset($type_note_map[$type_raw]) && isset($line->{$type_note_map[$type_raw]}))
	{
		$nap_note = $clean_text($line->{$type_note_map[$type_raw]});
	}
	if ($nap_note === '')
	{
		foreach ($type_note_map as $field)
		{
			if (isset($line->{$field}))
			{
				$cand = $clean_text($line->{$field});
				if ($cand !== '')
				{
					$nap_note = $cand;
					break;
				}
			}
		}
	}
	if ($nap_note === '' && isset($line->MREMARKS))
	{
		$nap_note = $clean_text($line->MREMARKS);
	}
	if ($nap_note === '' && isset($line->remarks))
	{
		$nap_note = $clean_text($line->remarks);
	}
	// Skip if note already present in the style/detail text.
	if ($nap_note !== '' && $remarks !== '' && mb_stripos($remarks, $nap_note) !== FALSE)
	{
		$nap_note = '';
	}

	$price_text = ($price != 0.0)
		? esc_html(rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.'))
		: '—';

	$detail_html = $format_detail_html($remarks);
	$pockets_inline = '(فرنٹ پاکٹ '.esc_html($fpocket).') (سائیڈ پاکٹ '.esc_html($spocket).') (شلوار پاکٹ '.esc_html($shawpocket).')';

	$detail_line = '<span class="print-suit-no">'.(int) $SR.'-</span> ';
	if ($detail_html !== '')
	{
		$detail_line .= $detail_html;
		if ($suit_end !== '')
		{
			$detail_line .= ' <span class="print-suit-card__suit-end">:- '.esc_html($suit_end).'</span>';
		}
	}
	else
	{
		$detail_line .= '<strong class="print-suit-card__color">'.esc_html($primary_title).'</strong>';
	}
	if ($nap_note !== '')
	{
		// Brackets only around note (after suit type), not around style text.
		$note_body = $nap_note;
		if (preg_match('/^\((.*)\)\s*$/us', $note_body, $nm))
		{
			$note_body = trim($nm[1]);
		}
		$detail_line .= ' <span class="print-suit-classic__inline-note">'
			.'<span class="print-suit-classic__note-brack">(</span>'
			.esc_html($note_body)
			.'<span class="print-suit-classic__note-brack">)</span>'
			.'</span>';
	}

	if ($SR === 1)
	{
		echo '<div class="print-slip-panel print-slip-panel--suits print-slip-panel--suits-classic">';
		echo '<div class="print-slip-panel__head ur urd">سوٹ تفصیل</div>';
	}
?>
<article class="print-suit-card print-suit-card--classic<?php echo $fabric_src !== '' ? ' has-fabric' : ''; ?>">
	<div class="print-suit-classic__detail ur urd"><?php echo $detail_line; ?></div>
	<div class="print-suit-classic__rule" aria-hidden="true"></div>
	<div class="print-suit-classic__meta">
		<span class="print-suit-classic__price"><?php echo $price_text; ?></span>
		<?php if ($fabric_src !== '') { ?>
		<span class="print-suit-classic__fabric"><img src="<?php echo esc_html($fabric_src); ?>" alt=""></span>
		<?php } ?>
		<span class="print-suit-classic__pockets ur urd"><?php echo $pockets_inline; ?></span>
	</div>
</article>
<?php
}
if ($SR > 0)
{
	echo '</div>';
}
?>
