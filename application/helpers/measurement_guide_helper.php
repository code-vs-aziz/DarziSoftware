<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('mg_anno'))
{
	/**
	 * SVG measurement callout — label always visible on the diagram.
	 */
	function mg_anno($key, $x1, $y1, $x2, $y2, $labelX, $labelY, $labelEn, $labelUr, $anchor = 'start')
	{
		$w = 88;
		$h = 34;
		$rx = $labelX;
		if ($anchor === 'end') {
			$rx = $labelX - $w;
		} elseif ($anchor === 'middle') {
			$rx = $labelX - ($w / 2);
		}
		$ry = $labelY - ($h / 2);

		return '
		<g class="measurement-anno" data-measure-key="' . esc_attr($key) . '" role="button" tabindex="0" aria-label="' . esc_attr($labelEn . ' / ' . $labelUr) . '">
			<line class="measurement-anno__line" x1="' . (float) $x1 . '" y1="' . (float) $y1 . '" x2="' . (float) $x2 . '" y2="' . (float) $y2 . '"/>
			<circle class="measurement-anno__dot" cx="' . (float) $x2 . '" cy="' . (float) $y2 . '" r="3.5"/>
			<rect class="measurement-anno__tag" x="' . (float) $rx . '" y="' . (float) $ry . '" width="' . $w . '" height="' . $h . '" rx="6"/>
			<text class="measurement-anno__en" x="' . (float) ($rx + 8) . '" y="' . (float) ($ry + 13) . '">' . esc_html($labelEn) . '</text>
			<text class="measurement-anno__ur" x="' . (float) ($rx + $w - 8) . '" y="' . (float) ($ry + 27) . '" text-anchor="end">' . esc_html($labelUr) . '</text>
		</g>';
	}
}
