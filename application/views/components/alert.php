<?php defined('BASEPATH') OR exit('No direct script access allowed');
$message = isset($message) ? (string) $message : '';
$type = isset($type) ? (string) $type : 'error';
if ( ! in_array($type, array('error', 'success', 'warning'), TRUE)) {
	$type = 'error';
}
?>
<div class="dz-alert dz-alert--<?php echo esc_html($type); ?>" role="alert"><?php echo esc_html($message); ?></div>
