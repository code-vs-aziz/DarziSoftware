<?php if ($this->session->flashdata('notify_whatsapp_url')) { ?>
<span data-notify-flash="notify_whatsapp_url" hidden><?php echo esc_html($this->session->flashdata('notify_whatsapp_url')); ?></span>
<?php } ?>
<?php if ($this->session->flashdata('notify_auto_open')) { ?>
<span data-notify-flash="notify_auto_open" hidden><?php echo esc_html($this->session->flashdata('notify_auto_open')); ?></span>
<?php } ?>
