<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pant Coat (پینٹ کوٹ) measurement fields — coat + pant PC* columns.
 * Expects $measurement object (may be empty on add).
 */
$m = (isset($measurement) && is_object($measurement)) ? $measurement : new stdClass();
$mv = function () use ($m) {
	$keys = func_get_args();
	foreach ($keys as $key)
	{
		if (isset($m->$key) && trim((string) $m->$key) !== '')
		{
			return (string) $m->$key;
		}
	}
	$k0 = isset($keys[0]) ? $keys[0] : '';
	return ($k0 !== '' && isset($m->$k0)) ? (string) $m->$k0 : '';
};
$note_value = isset($note_value) ? (string) $note_value : '';
?>
<div class="row mb-1">
	<div class="col-12">
		<strong class="urd" style="display:block;margin:0.35rem 0 0.15rem;">کوٹ</strong>
	</div>
</div>
<div class="row">
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">لمبائی</label>
			<input id="PCCoatLambai" name="PCCoatLambai" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatLambai')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">چھاتی</label>
			<input id="PCCoatChaati" name="PCCoatChaati" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatChaati')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">کمر</label>
			<input id="PCCoatKamar" name="PCCoatKamar" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatKamar')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">تیرا</label>
			<input id="PCCoatTeera" name="PCCoatTeera" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatTeera')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">بازو</label>
			<input id="PCCoatBazoo" name="PCCoatBazoo" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatBazoo')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">گلا</label>
			<input id="PCCoatGaala" name="PCCoatGaala" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatGaala')); ?>">
		</div>
	</div>
</div>
<div class="row">
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">ہپ</label>
			<input id="PCCoatHIP" name="PCCoatHIP" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatHIP', 'PCHIP')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">ڈولا</label>
			<input id="PCCoatDola" name="PCCoatDola" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatDola')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">بازو موری</label>
			<input id="PCCoatBazooMori" name="PCCoatBazooMori" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatBazooMori')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">کراس بیک</label>
			<input id="PCCrossback" name="PCCrossback" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCrossback')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">لمبائی بیک</label>
			<input id="PCCoatLambaiBack" name="PCCoatLambaiBack" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCCoatLambaiBack')); ?>">
		</div>
	</div>
</div>

<div class="row mb-1">
	<div class="col-12">
		<strong class="urd" style="display:block;margin:0.55rem 0 0.15rem;">پینٹ</strong>
	</div>
</div>
<div class="row">
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">لمبائی</label>
			<input id="PCPentLambai" name="PCPentLambai" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCPentLambai')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">کمر</label>
			<input id="PCpentWaiste" name="PCpentWaiste" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCpentWaiste')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">ہپ</label>
			<input id="PCHIP" name="PCHIP" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCHIP')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">پٹ</label>
			<input id="PCPentPat" name="PCPentPat" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCPentPat', 'PCPentThai')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">گھٹنہ</label>
			<input id="PCPentGhutna" name="PCPentGhutna" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCPentGhutna')); ?>">
		</div>
	</div>
	<div class="col">
		<div class="form-group" style="padding:0;margin:0;">
			<label class="control-label mb-1 urd">پانچہ موری</label>
			<input id="PCPentPonchaMori" name="PCPentPonchaMori" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PCPentPonchaMori', 'PCPentPoncha')); ?>">
		</div>
	</div>
</div>

<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'pentcoat',
	'note_name' => 'RemarksPentcoat',
	'note_id' => 'RemarksPentcoat',
	'note_value' => $note_value,
)); ?>
