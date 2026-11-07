<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->helper('flash');

$default_msg = isset($success_default) ? (string) $success_default : t('app_saved_success');
$msg = app_take_flash('success_msg');
$suss = app_take_flash('suss');

if (($msg === NULL || $msg === '') && ($suss === 'yes' || $suss === TRUE || $suss === 1 || $suss === '1'))
{
	$msg = $default_msg;
}

if ($msg !== NULL && $msg !== '')
{
?>
<div class="alert alert-success alert-dismissible fade show" role="alert" data-app-flash-alert="1">
	<?php echo esc_html((string) $msg); ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="<?php echo esc_attr(t('app_close')); ?>">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
<?php
}
