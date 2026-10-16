<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_pajama')); ?>">
	<defs>
		<linearGradient id="mgPajSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#faf5ff"/><stop offset="100%" stop-color="#ede9fe"/></linearGradient>
		<linearGradient id="mgPajFront" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#a78bfa"/><stop offset="100%" stop-color="#6d28d9"/></linearGradient>
		<linearGradient id="mgPajSide" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#5b21b6"/><stop offset="100%" stop-color="#4c1d95"/></linearGradient>
		<filter id="mgPajDrop"><feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#4c1d95" flood-opacity="0.2"/></filter>
	</defs>
	<rect width="420" height="580" fill="url(#mgPajSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="96" ry="16" fill="#cbd5e1" opacity="0.5"/>
	<g filter="url(#mgPajDrop)">
		<path d="M236 92 L278 108 L272 530 L236 544 L236 92 Z" fill="url(#mgPajSide)"/>
		<path d="M148 92 L236 92 L236 544 L112 530 L148 92 Z" fill="url(#mgPajFront)"/>
		<path d="M148 92 L236 92 L278 108 L190 108 Z" fill="#c4b5fd" opacity="0.8"/>
		<path d="M148 92 L236 92 L236 120 L148 120 Z" fill="#7c3aed" opacity="0.35"/>
		<path d="M148 120 L236 120 L236 130 L148 130 Z" fill="#ddd6fe"/>
		<path d="M172 530 L252 530" stroke="#ddd6fe" stroke-width="2.5" opacity="0.55"/>
		<ellipse cx="188" cy="538" rx="24" ry="8" fill="#4c1d95" opacity="0.25"/>
		<ellipse cx="232" cy="538" rx="24" ry="8" fill="#4c1d95" opacity="0.25"/>
		<path d="M192 92 L228 92 L228 544 L192 544 Z" fill="#ffffff" opacity="0.06"/>
	</g>
	<?php
	echo mg_anno('PLambai', 302, 108, 302, 530, 334, 310, 'Length', 'لمبائی', 'start');
	echo mg_anno('PAsan', 278, 260, 248, 280, 334, 268, 'Seat', 'آسن', 'start');
	echo mg_anno('PPoncha', 302, 510, 272, 532, 334, 520, 'Ankle', 'پانچہ', 'start');
	echo mg_anno('PPeeir', 112, 420, 148, 440, 24, 420, 'Pleat', 'Peeir', 'start');
	?>
</svg>
