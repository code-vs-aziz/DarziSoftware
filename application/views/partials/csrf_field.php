<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<input type="hidden" name="<?php echo esc_html($this->security->get_csrf_token_name()); ?>" value="<?php echo esc_html($this->security->get_csrf_hash()); ?>">
