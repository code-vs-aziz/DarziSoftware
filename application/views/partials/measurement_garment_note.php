<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$note_key = isset($note_key) ? (string) $note_key : 'kurta';
$note_name = isset($note_name) ? (string) $note_name : ('Remarks'.ucfirst($note_key));
$note_id = isset($note_id) ? (string) $note_id : $note_name;
$note_value = isset($note_value) ? (string) $note_value : '';
$note_label = isset($note_label) ? (string) $note_label : t('measurement_note_line');
?>
<div class="measurement-garment-note">
	<label for="<?php echo esc_attr($note_id); ?>" class="measurement-garment-note__label"><?php echo esc_html($note_label); ?></label>
	<input
		name="<?php echo esc_attr($note_name); ?>"
		id="<?php echo esc_attr($note_id); ?>"
		type="text"
		class="form-control measurement-garment-note__input js-urdu-note"
		dir="rtl"
		lang="ur"
		autocomplete="off"
		placeholder="<?php echo esc_attr(t('measurement_note_placeholder')); ?>"
		value="<?php echo esc_html($note_value); ?>"
	>
</div>
