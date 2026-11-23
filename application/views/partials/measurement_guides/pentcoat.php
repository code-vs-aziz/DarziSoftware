<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_pentcoat')); ?>">
	<defs>
		<linearGradient id="mgCoatSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="100%" stop-color="#fef3c7"/></linearGradient>
		<linearGradient id="mgCoatFront" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#fcd34d"/><stop offset="100%" stop-color="#b45309"/></linearGradient>
		<linearGradient id="mgCoatSide" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#92400e"/><stop offset="100%" stop-color="#78350f"/></linearGradient>
		<filter id="mgCoatDrop"><feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#78350f" flood-opacity="0.2"/></filter>
	</defs>
	<rect width="420" height="580" fill="url(#mgCoatSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="110" ry="16" fill="#cbd5e1" opacity="0.5"/>
	<g filter="url(#mgCoatDrop)">
		<path d="M252 72 L302 92 L294 390 L252 398 L252 72 Z" fill="url(#mgCoatSide)"/>
		<path d="M118 72 L252 72 L252 398 L98 390 L118 72 Z" fill="url(#mgCoatFront)"/>
		<path d="M118 72 L252 72 L302 92 L168 92 Z" fill="#fde68a" opacity="0.85"/>
		<path d="M168 72 L252 72 L252 390 L118 390 Z" fill="#000" opacity="0.04"/>
		<path d="M168 72 L182 52 L210 44 L238 52 L252 72 L210 86 Z" fill="#fff7ed" stroke="#92400e" stroke-width="1.2"/>
		<path d="M168 92 L168 390 M252 92 L252 390" stroke="#92400e" stroke-width="1.5" opacity="0.45"/>
		<path d="M118 112 L68 168 L82 390 L98 390 L118 132 Z" fill="#fbbf24" stroke="#92400e" stroke-width="1.2"/>
		<path d="M252 112 L302 168 L288 390 L252 390 L252 132 Z" fill="#fbbf24" stroke="#92400e" stroke-width="1.2"/>
		<path d="M210 92 L210 370" stroke="#fef3c7" stroke-width="1" stroke-dasharray="4 4" opacity="0.35"/>
		<circle cx="210" cy="130" r="2" fill="#fff7ed" stroke="#92400e" stroke-width="0.8"/>
		<circle cx="210" cy="170" r="2" fill="#fff7ed" stroke="#92400e" stroke-width="0.8"/>
		<circle cx="210" cy="210" r="2" fill="#fff7ed" stroke="#92400e" stroke-width="0.8"/>
	</g>
	<?php
	echo mg_anno('CoatGaala', 182, 52, 210, 68, 24, 48, 'Collar', 'گلا', 'start');
	echo mg_anno('CoatTeera', 118, 112, 168, 98, 24, 98, 'Shoulder', 'تیرا', 'start');
	echo mg_anno('CoatBazoo', 68, 220, 98, 240, 18, 228, 'Sleeve', 'بازو', 'start');
	echo mg_anno('CoatLambai', 318, 92, 318, 390, 334, 230, 'Length', 'لمبائی', 'start');
	echo mg_anno('CoatChaati', 302, 92, 252, 140, 334, 128, 'Chest', 'چھاتی', 'start');
	echo mg_anno('CoatKamar', 302, 220, 252, 250, 334, 232, 'Waist', 'کمر', 'start');
	echo mg_anno('CoatHIP', 302, 300, 252, 330, 334, 312, 'Hip', 'HIP', 'start');
	echo mg_anno('crossback', 302, 140, 252, 160, 334, 168, 'Cross Back', 'Cross Back', 'start');
	?>
</svg>
