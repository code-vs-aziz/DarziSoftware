<?php
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$trnno = isset($trnno) ? (int) $trnno : 0;
$order_header = isset($order_header) ? $order_header : NULL;
?>
<body class="animsition erp-app">
<div class="page-wrapper">
	<div class="main-content">
		<div class="section__content section__content--p30">
			<div class="container-fluid">
				<div class="col-md-12">
					<div class="dz-card erp-module-card">
						<div class="erp-card-head">
							<h2 class="erp-module-title mb-0"><?php echo t('order_print_unavailable'); ?></h2>
						</div>
						<div class="card-body erp-card-body">
							<?php if ($order_header) { ?>
							<p class="mb-2"><?php echo sprintf(t('order_no_lines'), $trnno); ?></p>
							<p class="mb-1"><strong><?php echo t('order_customer_name'); ?>:</strong> <?php echo esc_html($order_header->name); ?></p>
							<p class="mb-3"><strong><?php echo t('order_naap_no'); ?>:</strong> <?php echo esc_html($order_header->man_napno); ?></p>
							<p class="text-muted"><?php echo t('order_reenter_lines'); ?></p>
							<a class="btn btn-primary dz-btn--filled" href="<?php echo site_url('Order/Order_edit/'.$trnno); ?>"><?php echo sprintf(t('order_edit_link'), $trnno); ?></a>
							<?php } else { ?>
							<p class="mb-3"><?php echo sprintf(t('order_not_found'), $trnno); ?></p>
							<?php } ?>
							<a class="btn btn-outline-secondary ml-2" href="javascript:window.close();"><?php echo t('order_close_tab'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>
