<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_pent')); ?>">
	<defs>
		<linearGradient id="mgPentSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fff1f2"/><stop offset="100%" stop-color="#ffe4e6"/></linearGradient>
		<linearGradient id="mgPentFront" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#fb7185"/><stop offset="100%" stop-color="#be123c"/></linearGradient>
		<linearGradient id="mgPentSide" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#9f1239"/><stop offset="100%" stop-color="#881337"/></linearGradient>
		<filter id="mgPentDrop"><feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#881337" flood-opacity="0.18"/></filter>
	</defs>
	<rect width="420" height="580" fill="url(#mgPentSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="92" ry="15" fill="#cbd5e1" opacity="0.5"/>
	<g filter="url(#mgPentDrop)">
		<path d="M232 108 L272 122 L258 532 L232 542 L232 108 Z" fill="url(#mgPentSide)"/>
		<path d="M158 108 L232 108 L232 542 L138 532 L158 108 Z" fill="url(#mgPentFront)"/>
		<path d="M158 108 L232 108 L272 122 L198 122 Z" fill="#fda4af" opacity="0.75"/>
		<path d="M158 108 L232 108 L232 148 L158 148 Z" fill="#1f2937" opacity="0.75"/>
		<rect x="158" y="108" width="74" height="8" fill="#374151"/>
		<path d="M176 148 L214 148 L220 260 L170 260 Z" fill="#ffffff" opacity="0.08"/>
		<path d="M170 260 L220 260 L232 532 L138 532 Z" fill="#000" opacity="0.05"/>
		<path d="M168 528 L222 528" stroke="#fecdd3" stroke-width="2"/>
	</g>
	<?php
	echo mg_anno('PentLambai', 302, 122, 302, 532, 334, 320, 'Length', 'لمبائی', 'start');
	echo mg_anno('pentWaiste', 272, 122, 232, 130, 334, 118, 'Waist', 'کمر', 'start');
	echo mg_anno('HIP', 272, 210, 232, 230, 334, 218, 'Hip', 'HIP', 'start');
	echo mg_anno('PentThai', 272, 310, 220, 330, 334, 318, 'Thigh', 'Thai', 'start');
	echo mg_anno('PentPoncha', 302, 510, 272, 528, 334, 520, 'Ankle', 'پانچہ', 'start');
	?>
</svg>
