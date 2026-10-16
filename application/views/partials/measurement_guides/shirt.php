<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_shirt')); ?>">
	<defs>
		<linearGradient id="mgShirtSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f0fdf4"/><stop offset="100%" stop-color="#dcfce7"/></linearGradient>
		<linearGradient id="mgShirtFront" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#86efac"/><stop offset="100%" stop-color="#16a34a"/></linearGradient>
		<linearGradient id="mgShirtSide" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#15803d"/><stop offset="100%" stop-color="#14532d"/></linearGradient>
		<filter id="mgShirtDrop"><feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#14532d" flood-opacity="0.16"/></filter>
	</defs>
	<rect width="420" height="580" fill="url(#mgShirtSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="100" ry="15" fill="#cbd5e1" opacity="0.45"/>
	<g filter="url(#mgShirtDrop)">
		<path d="M246 88 L288 104 L282 330 L246 336 L246 88 Z" fill="url(#mgShirtSide)"/>
		<path d="M134 88 L246 88 L246 336 L118 330 L134 88 Z" fill="url(#mgShirtFront)"/>
		<path d="M134 88 L246 88 L288 104 L176 104 Z" fill="#bbf7d0" opacity="0.85"/>
		<path d="M176 88 L244 88 L244 104 L176 104 Z" fill="#f0fdf4" stroke="#15803d" stroke-width="1"/>
		<path d="M210 104 L210 118" stroke="#15803d" stroke-width="1"/>
		<path d="M134 104 L82 128 L72 210 L102 220 L134 132 Z" fill="#4ade80" stroke="#15803d" stroke-width="1.2"/>
		<path d="M246 104 L298 128 L308 210 L278 220 L246 132 Z" fill="#4ade80" stroke="#15803d" stroke-width="1.2"/>
		<path d="M72 210 L102 220 L104 232 L76 224 Z" fill="#22c55e"/>
		<path d="M278 220 L308 210 L306 232 L278 224 Z" fill="#22c55e"/>
		<path d="M118 330 L282 330" stroke="#bbf7d0" stroke-width="2"/>
	</g>
	<?php
	echo mg_anno('SGaala', 176, 88, 210, 96, 24, 72, 'Collar', 'گلا', 'start');
	echo mg_anno('STeera', 134, 104, 176, 104, 24, 104, 'Shoulder', 'تیرا', 'start');
	echo mg_anno('SBazoo', 82, 168, 102, 188, 18, 178, 'Sleeve', 'بازو', 'start');
	echo mg_anno('SLambai', 302, 104, 302, 330, 334, 210, 'Length', 'لمبائی', 'start');
	echo mg_anno('SChaati', 288, 104, 246, 140, 334, 128, 'Chest', 'چھاتی', 'start');
	echo mg_anno('SKamar', 288, 190, 246, 210, 334, 198, 'Waist', 'کمر', 'start');
	echo mg_anno('SGeera', 288, 310, 246, 330, 334, 318, 'Hem', 'گیرا', 'start');
	?>
</svg>
