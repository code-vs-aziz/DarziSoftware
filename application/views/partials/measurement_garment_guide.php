<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$measurement_guide_categories = array('kurta', 'pajama', 'pent', 'shirt', 'coat', 'pentcoat', 'waistcoat');
?>
<div class="measurement-guide" id="measurementGarmentGuide" aria-live="polite">
	<div class="measurement-guide__head">
		<strong class="measurement-guide__title"><?php echo t('measurement_guide_title'); ?></strong>
		<span class="measurement-guide__hint"><?php echo t('measurement_guide_hint'); ?></span>
	</div>
	<div class="measurement-guide__stage">
		<?php foreach ($measurement_guide_categories as $cat) { ?>
		<div class="measurement-guide__view<?php echo ($cat === 'kurta') ? ' is-active' : ''; ?>" data-guide-category="<?php echo esc_attr($cat); ?>"<?php echo ($cat !== 'kurta') ? ' hidden' : ''; ?>>
			<?php $this->load->view('partials/measurement_guides/'.$cat); ?>
		</div>
		<?php } ?>
	</div>
</div>
