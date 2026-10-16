<?php
ob_Start();
$this->load->helper('ui');
$this->load->library('report_registry');
$this->load->view('Head');
$this->load->view('Header');

$catalog = $this->report_registry->all();
$presets = $this->report_registry->presets();
$owner_phone = function_exists('dz_env') ? trim((string) dz_env('REPORT_OWNER_PHONE', '')) : '';

$financial_keys = array('financial_dashboard', 'sales_expense', 'cash_flow');
$operational_keys = array(
	'employee_ledger', 'customer_ledger', 'cash_ledger', 'expenses',
	'pending_payment', 'all_sale', 'delivery', 'customer_total_sale',
	'employee_report', 'booking',
);

$registry = $this->report_registry;
$render_report_tile = function ($key) use ($catalog, $registry) {
	if (!isset($catalog[$key]) || !$registry->can_access($key)) {
		return;
	}
	$item = $catalog[$key];
	$url = site_url($item['form_url']);
	$label = t($item['label_key']);
	$hint_map = array(
		'financial_dashboard' => 'report_hub_hint_financial',
		'sales_expense' => 'report_hub_hint_sales_expense',
		'cash_flow' => 'report_hub_hint_cash_flow',
		'employee_ledger' => 'report_hub_hint_employee_ledger',
		'customer_ledger' => 'report_hub_hint_customer_ledger',
		'cash_ledger' => 'report_hub_hint_cash_ledger',
		'expenses' => 'report_hub_hint_expenses',
		'pending_payment' => 'report_hub_hint_pending',
		'all_sale' => 'report_hub_hint_all_sale',
		'delivery' => 'report_hub_hint_delivery',
		'customer_total_sale' => 'report_hub_hint_customer_sale',
		'employee_report' => 'report_hub_hint_employee_report',
		'booking' => 'report_hub_hint_booking',
	);
	$hint = isset($hint_map[$key]) ? t($hint_map[$key]) : '';
	$pdf_ok = !empty($item['builder']);
	?>
	<div class="dz-report-tile-wrap" data-report-key="<?php echo esc_attr($key); ?>" data-pdf="<?php echo $pdf_ok ? '1' : '0'; ?>">
		<a class="dz-report-tile" href="<?php echo esc_html($url); ?>" target="_blank" rel="noopener noreferrer">
			<span class="dz-report-tile__icon" aria-hidden="true"><?php echo ui_icon($item['icon']); ?></span>
			<span class="dz-report-tile__text">
				<span class="dz-report-tile__title"><?php echo esc_html($label); ?></span>
				<?php if ($hint !== '') { ?><span class="dz-report-tile__hint"><?php echo esc_html($hint); ?></span><?php } ?>
			</span>
			<span class="dz-report-tile__chevron" aria-hidden="true"><?php echo ui_icon('fa-chevron-right'); ?></span>
		</a>
	</div>
	<?php
};
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-report-hub">
							<header class="dz-report-hub__header">
								<h1 class="dz-report-hub__title"><?php echo esc_html(t('report_title')); ?></h1>
								<p class="dz-report-hub__subtitle"><?php echo esc_html(t('report_hub_subtitle_advanced')); ?></p>
							</header>

							<section class="dz-card dz-report-hub__section dz-report-quick" aria-labelledby="report-quick-heading">
								<div class="dz-card__header">
									<h2 id="report-quick-heading" class="dz-card__title"><?php echo esc_html(t('report_quick_title')); ?></h2>
								</div>
								<div class="dz-card__body">
									<form id="report-quick-form" class="dz-report-quick__form" data-no-ajax="true">
										<?php $this->load->view('partials/csrf_field'); ?>
										<div class="dz-report-quick__grid">
											<div class="dz-report-field">
												<label for="report-quick-key"><?php echo esc_html(t('report_quick_select')); ?></label>
												<select id="report-quick-key" name="key" required>
													<option value=""><?php echo esc_html(t('report_quick_select')); ?></option>
													<?php foreach ($catalog as $key => $item) {
														if (!$this->report_registry->can_access($key)) { continue; } ?>
													<option value="<?php echo esc_attr($key); ?>" data-pdf="<?php echo !empty($item['builder']) ? '1' : '0'; ?>">
														<?php echo esc_html(t($item['label_key'])); ?>
													</option>
													<?php } ?>
												</select>
											</div>
											<div class="dz-report-field">
												<label for="report-quick-preset"><?php echo esc_html(t('report_quick_preset')); ?></label>
												<select id="report-quick-preset" name="preset">
													<option value=""><?php echo esc_html(t('report_preset_custom')); ?></option>
													<?php foreach ($presets as $pkey => $pmeta) { ?>
													<option value="<?php echo esc_attr($pkey); ?>"><?php echo esc_html(t($pmeta['label_key'])); ?></option>
													<?php } ?>
												</select>
											</div>
											<div class="dz-report-field">
												<label for="report-quick-from"><?php echo esc_html(t('report_from')); ?></label>
												<input type="date" id="report-quick-from" name="from" required>
											</div>
											<div class="dz-report-field">
												<label for="report-quick-to"><?php echo esc_html(t('report_to')); ?></label>
												<input type="date" id="report-quick-to" name="to" required>
											</div>
										</div>
										<div class="dz-report-quick__presets" role="group" aria-label="<?php echo esc_attr(t('report_quick_preset')); ?>">
											<?php foreach ($presets as $pkey => $pmeta) { ?>
											<button type="button" class="dz-report-chip" data-preset="<?php echo esc_attr($pkey); ?>"><?php echo esc_html(t($pmeta['label_key'])); ?></button>
											<?php } ?>
										</div>
										<div class="dz-report-quick__actions">
											<button type="submit" class="btn btn-primary dz-btn--filled" id="report-quick-open"><?php echo esc_html(t('report_quick_open')); ?></button>
											<button type="button" class="btn btn-outline-primary dz-btn--outlined" id="report-quick-pdf"><?php echo esc_html(t('report_quick_pdf')); ?></button>
											<button type="button" class="btn btn-outline-secondary dz-btn--outlined" id="report-quick-whatsapp"><?php echo esc_html(t('report_quick_whatsapp')); ?></button>
										</div>
										<p id="report-quick-status" class="dz-report-status" role="status" aria-live="polite"></p>
									</form>
								</div>
							</section>

							<section class="dz-card dz-report-hub__section dz-report-voice-panel" aria-labelledby="report-voice-heading">
								<div class="dz-card__header">
									<h2 id="report-voice-heading" class="dz-card__title"><?php echo esc_html(t('report_voice_title')); ?></h2>
								</div>
								<div class="dz-card__body">
									<p class="text-muted mb-2" style="font-size:13px;"><?php echo esc_html(t('report_voice_subtitle')); ?></p>
									<form id="report-voice-form" class="dz-report-voice__form" data-no-ajax="true">
										<div class="dz-report-voice__row">
											<input type="text" id="report-voice-input" class="form-control" placeholder="<?php echo esc_attr(t('report_voice_placeholder')); ?>" autocomplete="off">
											<button type="button" id="report-voice-mic" class="btn btn-outline-primary dz-btn--outlined" title="<?php echo esc_attr(t('report_voice_mic')); ?>">
												<?php echo ui_icon('fa-microphone'); ?>
											</button>
											<button type="submit" class="btn btn-primary dz-btn--filled"><?php echo esc_html(t('report_voice_go')); ?></button>
										</div>
									</form>
									<div class="dz-report-quick__presets mt-2">
										<button type="button" class="dz-report-chip report-voice-chip" data-cmd="<?php echo esc_attr(t('report_voice_chip_cash_7')); ?>"><?php echo esc_html(t('report_voice_chip_cash_7')); ?></button>
										<button type="button" class="dz-report-chip report-voice-chip" data-cmd="<?php echo esc_attr(t('report_voice_chip_delivery_today')); ?>"><?php echo esc_html(t('report_voice_chip_delivery_today')); ?></button>
										<button type="button" class="dz-report-chip report-voice-chip" data-cmd="<?php echo esc_attr(t('report_voice_chip_expense_7')); ?>"><?php echo esc_html(t('report_voice_chip_expense_7')); ?></button>
										<button type="button" class="dz-report-chip report-voice-chip" data-cmd="<?php echo esc_attr(t('report_voice_chip_pending')); ?>"><?php echo esc_html(t('report_voice_chip_pending')); ?></button>
									</div>
									<p id="report-voice-status" class="dz-report-status" role="status"></p>
									<div id="report-voice-preview" class="dz-report-voice__preview" style="display:none;"></div>
								</div>
							</section>

							<section class="dz-card dz-report-hub__section" aria-labelledby="report-financial-heading">
								<div class="dz-card__header">
									<h2 id="report-financial-heading" class="dz-card__title"><?php echo esc_html(t('report_section_financial')); ?></h2>
								</div>
								<div class="dz-card__body">
									<div class="dz-report-grid">
										<?php foreach ($financial_keys as $key) { $render_report_tile($key); } ?>
									</div>
								</div>
							</section>

							<section class="dz-card dz-report-hub__section" aria-labelledby="report-operational-heading">
								<div class="dz-card__header">
									<h2 id="report-operational-heading" class="dz-card__title"><?php echo esc_html(t('report_section_operational')); ?></h2>
								</div>
								<div class="dz-card__body">
									<div class="dz-report-grid">
										<?php foreach ($operational_keys as $key) { $render_report_tile($key); } ?>
									</div>
								</div>
							</section>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	window.REPORT_VOICE_CFG = {
		parseUrl: <?php echo json_encode(site_url('Report_voice/parse')); ?>,
		statusUrl: <?php echo json_encode(site_url('Report_voice/status')); ?>
	};
	window.REPORT_HUB_CFG = {
		openUrl: <?php echo json_encode(site_url('Reports/open')); ?>,
		pdfUrl: <?php echo json_encode(site_url('Reports/pdf')); ?>,
		whatsappUrl: <?php echo json_encode(site_url('Reports/send_whatsapp')); ?>,
		ownerPhone: <?php echo json_encode($owner_phone); ?>,
		presets: <?php echo json_encode(array_keys($presets)); ?>
	};
	</script>
	<script src="<?php echo base_url(); ?>vendor/slick/slick.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/wow/wow.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/animsition/animsition.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/counter-up/jquery.waypoints.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/counter-up/jquery.counterup.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/circle-progress/circle-progress.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
	<script src="<?php echo base_url(); ?>vendor/chartjs/Chart.bundle.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/select2/select2.min.js"></script>
	<script src="<?php echo base_url(); ?>js/main.js"></script>
	<script src="<?php echo base_url(); ?>js/report-voice-assistant.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/report-hub.js?v=2"></script>
</body>
</html>
