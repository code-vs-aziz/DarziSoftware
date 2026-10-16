<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->helper('measurement_guide'); ?>
<svg class="measurement-guide__svg" viewBox="0 0 420 580" role="img" aria-label="<?php echo esc_attr(t('measurement_tab_kurta')); ?>">
	<defs>
		<linearGradient id="mgSky" x1="0" y1="0" x2="0" y2="1">
			<stop offset="0%" stop-color="#f8fafc"/>
			<stop offset="100%" stop-color="#e8eef5"/>
		</linearGradient>
		<linearGradient id="mgFabricFront" x1="0%" y1="0%" x2="100%" y2="100%">
			<stop offset="0%" stop-color="#6fa8dc"/>
			<stop offset="55%" stop-color="#4a86b8"/>
			<stop offset="100%" stop-color="#356a99"/>
		</linearGradient>
		<linearGradient id="mgFabricSide" x1="0%" y1="0%" x2="100%" y2="0%">
			<stop offset="0%" stop-color="#2f587a"/>
			<stop offset="100%" stop-color="#1e3a52"/>
		</linearGradient>
		<linearGradient id="mgFabricShalwar" x1="0%" y1="0%" x2="0%" y2="100%">
			<stop offset="0%" stop-color="#5b95c9"/>
			<stop offset="100%" stop-color="#2f5f87"/>
		</linearGradient>
		<linearGradient id="mgSleeve" x1="0%" y1="0%" x2="100%" y2="100%">
			<stop offset="0%" stop-color="#7eb3df"/>
			<stop offset="100%" stop-color="#467aa8"/>
		</linearGradient>
		<filter id="mgDrop" x="-20%" y="-20%" width="140%" height="140%">
			<feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#0f172a" flood-opacity="0.18"/>
		</filter>
		<pattern id="mgStitch" width="6" height="6" patternUnits="userSpaceOnUse">
			<path d="M0 6 L6 0" stroke="#ffffff" stroke-opacity="0.12" stroke-width="0.6"/>
		</pattern>
	</defs>

	<rect width="420" height="580" fill="url(#mgSky)" rx="12"/>
	<ellipse cx="210" cy="548" rx="118" ry="18" fill="#cbd5e1" opacity="0.55"/>

	<!-- Kameez — isometric front + side -->
	<g filter="url(#mgDrop)">
		<path d="M248 86 L292 102 L286 268 L248 272 L248 86 Z" fill="url(#mgFabricSide)"/>
		<path d="M132 86 L248 86 L248 272 L112 268 L132 86 Z" fill="url(#mgFabricFront)"/>
		<path d="M132 86 L248 86 L292 102 L176 102 Z" fill="#89b7dd" opacity="0.85"/>
		<path d="M132 86 L248 86 L248 272 L112 268 Z" fill="url(#mgStitch)" opacity="0.35"/>
		<!-- Collar -->
		<path d="M168 86 L180 68 L210 62 L240 68 L252 86 L210 98 Z" fill="#eef6ff" stroke="#2f587a" stroke-width="1.2"/>
		<path d="M188 86 L210 74 L232 86" fill="none" stroke="#2f587a" stroke-width="1"/>
		<!-- Placket + buttons -->
		<line x1="210" y1="98" x2="210" y2="250" stroke="#2f587a" stroke-width="1.2" opacity="0.55"/>
		<circle cx="210" cy="118" r="2.2" fill="#eef6ff" stroke="#2f587a" stroke-width="0.8"/>
		<circle cx="210" cy="142" r="2.2" fill="#eef6ff" stroke="#2f587a" stroke-width="0.8"/>
		<circle cx="210" cy="166" r="2.2" fill="#eef6ff" stroke="#2f587a" stroke-width="0.8"/>
		<!-- Sleeves -->
		<path d="M132 98 L78 118 L68 198 L96 208 L132 128 Z" fill="url(#mgSleeve)" stroke="#356a99" stroke-width="1.2"/>
		<path d="M248 98 L302 118 L312 198 L284 208 L248 128 Z" fill="url(#mgSleeve)" stroke="#356a99" stroke-width="1.2"/>
		<path d="M68 198 L96 208 L98 222 L72 214 Z" fill="#467aa8" stroke="#356a99" stroke-width="1"/>
		<path d="M284 208 L312 198 L310 222 L284 214 Z" fill="#467aa8" stroke="#356a99" stroke-width="1"/>
	</g>

	<!-- Shalwar — 3D trouser volume -->
	<g filter="url(#mgDrop)">
		<path d="M148 274 L272 274 L298 520 L210 548 L122 520 Z" fill="url(#mgFabricShalwar)" stroke="#1e3a52" stroke-width="1.5"/>
		<path d="M210 274 L298 290 L298 520 L210 548 Z" fill="#2f587a" opacity="0.35"/>
		<path d="M148 274 L122 290 L122 420 L148 410 Z" fill="#356a99" opacity="0.45"/>
		<path d="M272 274 L298 290 L298 420 L272 410 Z" fill="#1e3a52" opacity="0.35"/>
		<path d="M148 274 L272 274 L298 290 L122 290 Z" fill="#7eb3df" opacity="0.55"/>
		<path d="M210 274 L210 548" stroke="#eef6ff" stroke-width="1" stroke-dasharray="5 4" opacity="0.35"/>
		<path d="M132 518 L288 518" stroke="#eef6ff" stroke-width="2" opacity="0.5"/>
		<ellipse cx="168" cy="524" rx="22" ry="8" fill="#1e3a52" opacity="0.25"/>
		<ellipse cx="252" cy="524" rx="22" ry="8" fill="#1e3a52" opacity="0.25"/>
	</g>

	<?php
	echo mg_anno('Gala', 168, 74, 198, 78, 24, 52, 'Collar', 'گلا', 'start');
	echo mg_anno('Teera', 132, 98, 168, 98, 24, 98, 'Shoulder', 'تیرا', 'start');
	echo mg_anno('BL', 78, 158, 96, 178, 18, 168, 'Sleeve', 'بازو', 'start');
	echo mg_anno('KL', 302, 98, 302, 268, 334, 180, 'Length', 'لمبائی', 'start');
	echo mg_anno('Chaati', 292, 102, 248, 138, 334, 128, 'Chest', 'چھاتی', 'start');
	echo mg_anno('pait', 286, 168, 248, 198, 334, 198, 'Waist', 'پیٹ', 'start');
	echo mg_anno('geera', 286, 252, 248, 268, 334, 258, 'Hem', 'گیرا', 'start');
	echo mg_anno('Shalwar_Lenght', 318, 290, 318, 518, 334, 400, 'Shalwar L.', 'شلوار لمبائی', 'start');
	echo mg_anno('SC', 122, 360, 168, 380, 24, 360, 'Shalwar W.', 'شلوار پھیر', 'start');
	echo mg_anno('Poncha_Choraic', 298, 510, 272, 524, 334, 524, 'Ankle', 'پانچہ', 'start');
	?>
</svg>
