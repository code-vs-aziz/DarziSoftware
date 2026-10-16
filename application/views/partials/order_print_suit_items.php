<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Suit-detail cards for order screen print + WhatsApp/mPDF (pdf_mode).
 * Expects: $order, $suit, $style, $embroidery, $pdf_img (optional callable), $pdf_mode (optional)
 */
if (empty($order) || ! is_array($order))
{
	return;
}
$pdf_mode = ! empty($pdf_mode);
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
	$remarks = isset($line->order_remarks) ? trim((string) $line->order_remarks) : '';

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

	$extra_bits = array();
	if ( ! empty($style))
	{
		foreach ($style as $styles)
		{
			if (isset($styles->DNO, $line->design) && (string) $styles->DNO === (string) $line->design)
			{
				$extra_bits[] = trim($styles->vol_name.$styles->vol_no.' · Design '.$styles->design);
			}
		}
	}
	if ( ! empty($embroidery))
	{
		foreach ($embroidery as $embroiderys)
		{
			if (isset($embroiderys->ENO, $line->ENO) && (string) $embroiderys->ENO === (string) $line->ENO)
			{
				$extra_bits[] = trim($embroiderys->embroidery_name.$embroiderys->vol_no.' · Emb '.$embroiderys->design);
			}
		}
	}

	$primary_title = $type_label;
	if ($suit_name !== '' && strcasecmp($suit_name, $type_raw) !== 0)
	{
		$primary_title = $suit_name;
	}
	$title_bits = array();
	if ($primary_title !== '')
	{
		$title_bits[] = $primary_title;
	}
	if ($remarks !== '' && strcasecmp($remarks, $primary_title) !== 0 && strcasecmp($remarks, $type_raw) !== 0)
	{
		$title_bits[] = $remarks;
	}
	$title_text = implode(' ', $title_bits);
	$price_text = ($price != 0.0)
		? esc_html(rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.'))
		: '—';

	if ($SR === 1)
	{
		echo '<div class="print-slip-panel print-slip-panel--suits">';
		echo '<div class="print-slip-panel__head ur urd">سوٹ تفصیل</div>';
	}

	if ($pdf_mode)
	{
		// mPDF-safe table card (same content as screen print cards).
		?>
<article class="print-suit-card<?php echo $fabric_src !== '' ? ' has-fabric' : ''; ?>">
	<table class="print-suit-card__table" width="100%" cellpadding="2" cellspacing="0">
		<tr>
			<td class="print-suit-card__price" width="16%"><?php echo $price_text; ?></td>
			<?php if ($fabric_src !== '') { ?>
			<td class="print-suit-card__fabric" width="12%" align="center">
				<img src="<?php echo esc_html($fabric_src); ?>" alt="">
			</td>
			<?php } ?>
			<td class="print-suit-card__titles ur urd">
				<?php if ($title_text !== '') { ?>
				<span class="print-suit-card__name"><?php echo esc_html($title_text); ?></span>
				<?php } ?>
				<span class="print-suit-card__pockets">
					(فرنٹ پاکٹ <?php echo esc_html($fpocket); ?>)
					(سائیڈ پاکٹ <?php echo esc_html($spocket); ?>)
					(شلوار پاکٹ <?php echo esc_html($shawpocket); ?>)
				</span>
				<?php if ( ! empty($extra_bits)) { ?>
				<span class="print-suit-card__extra"><?php echo esc_html(implode(' · ', $extra_bits)); ?></span>
				<?php } ?>
			</td>
			<td width="10%" align="right" valign="top">
				<span class="print-suit-card__badge"><?php echo (int) $SR; ?></span>
			</td>
		</tr>
	</table>
</article>
		<?php
		continue;
	}
?>
<article class="print-suit-card<?php echo $fabric_src !== '' ? ' has-fabric' : ''; ?>">
	<div class="print-suit-card__row">
		<span class="print-suit-card__price"><?php echo $price_text; ?></span>
		<?php if ($fabric_src !== '') { ?>
		<span class="print-suit-card__fabric">
			<img src="<?php echo esc_html($fabric_src); ?>" alt="">
		</span>
		<?php } ?>
		<div class="print-suit-card__titles">
			<?php if ($title_text !== '') { ?>
			<span class="print-suit-card__titleline">
				<span class="print-suit-card__name ur urd"><?php echo esc_html($title_text); ?></span>
			</span>
			<?php } ?>
			<span class="print-suit-card__pockets ur urd">
				<span>(فرنٹ پاکٹ <?php echo esc_html($fpocket); ?>)</span>
				<span>(سائیڈ پاکٹ <?php echo esc_html($spocket); ?>)</span>
				<span>(شلوار پاکٹ <?php echo esc_html($shawpocket); ?>)</span>
			</span>
			<?php if ( ! empty($extra_bits)) { ?>
			<span class="print-suit-card__extra ur urd"><?php echo esc_html(implode(' · ', $extra_bits)); ?></span>
			<?php } ?>
		</div>
		<span class="print-suit-card__badge"><?php echo (int) $SR; ?></span>
	</div>
</article>
<?php
}
if ($SR > 0)
{
	echo '</div>';
}
?>
