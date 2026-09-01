<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<meta name="csrf-token-name" content="<?php echo esc_html($this->security->get_csrf_token_name()); ?>">
<meta name="csrf-token-value" content="<?php echo esc_html($this->security->get_csrf_hash()); ?>">
<meta name="csrf-cookie-name" content="<?php echo esc_html($this->config->item('csrf_cookie_name')); ?>">
<script src="<?php echo base_url('js/app-csrf.js?v=2'); ?>"></script>
