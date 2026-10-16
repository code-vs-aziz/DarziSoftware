<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_waistcoat')); ?>">
	<defs>
		<linearGradient id="mgWstSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#eef2ff"/><stop offset="100%" stop-color="#e0e7ff"/></linearGradient>
		<linearGradient id="mgWstFront" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#818cf8"/><stop offset="100%" stop-color="#4338ca"/></linearGradient>
		<linearGradient id="mgWstSide" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#3730a3"/><stop offset="100%" stop-color="#312e81"/></linearGradient>
		<filter id="mgWstDrop"><feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#312e81" flood-opacity="0.18"/></filter>
	</defs>
	<rect width="420" height="580" fill="url(#mgWstSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="88" ry="14" fill="#cbd5e1" opacity="0.45"/>
	<g filter="url(#mgWstDrop)">
		<path d="M238 98 L278 112 L272 360 L238 368 L238 98 Z" fill="url(#mgWstSide)"/>
		<path d="M142 98 L238 98 L238 368 L128 360 L142 98 Z" fill="url(#mgWstFront)"/>
		<path d="M142 98 L238 98 L278 112 L182 112 Z" fill="#a5b4fc" opacity="0.85"/>
		<path d="M182 98 L238 98 L238 360 L142 360 Z" fill="#000" opacity="0.05"/>
		<path d="M182 98 L194 78 L210 72 L226 78 L238 98 L210 108 Z" fill="#eef2ff" stroke="#4338ca" stroke-width="1.2"/>
		<path d="M168 98 L168 360 M212 98 L212 360" stroke="#4338ca" stroke-width="1.5" opacity="0.4"/>
		<circle cx="210" cy="130" r="2" fill="#eef2ff" stroke="#4338ca" stroke-width="0.8"/>
		<circle cx="210" cy="170" r="2" fill="#eef2ff" stroke="#4338ca" stroke-width="0.8"/>
		<circle cx="210" cy="210" r="2" fill="#eef2ff" stroke="#4338ca" stroke-width="0.8"/>
		<path d="M128 360 L272 360" stroke="#c7d2fe" stroke-width="2"/>
	</g>
	<?php
	echo mg_anno('wastGaala', 194, 78, 210, 92, 24, 68, 'Collar', 'گلا', 'start');
	echo mg_anno('wastTeera', 142, 112, 182, 108, 24, 108, 'Shoulder', 'تیرا', 'start');
	echo mg_anno('wastLambai', 302, 112, 302, 360, 334, 230, 'Length', 'لمبائی', 'start');
	echo mg_anno('wastChaati', 278, 112, 238, 150, 334, 138, 'Chest', 'چھاتی', 'start');
	echo mg_anno('wastKamar', 278, 220, 238, 250, 334, 232, 'Waist', 'کمر', 'start');
	echo mg_anno('wastHIP', 278, 300, 238, 330, 334, 312, 'Hip', 'HIP', 'start');
	?>
</svg>
