<?php
ob_start();
$this->load->view('Head');
$this->load->view('Header');
$max_naap_next = isset($max_naap) ? ((int) $max_naap + 1) : '';
?>
	<link href="<?php echo base_url('css/customer-form-enhancements.css?v=5'); ?>" rel="stylesheet" media="all">
<script>
	function initCustomerUrduFields() {
		if (!window.jQuery || !jQuery.fn.setUrduInput) {
			return;
		}
		jQuery('#Name, #Address').each(function () {
			var $field = jQuery(this);
			if (!$field.data('urduBound')) {
				$field.setUrduInput();
				$field.data('urduBound', 1);
			}
		});
	}
	function syncCustomerPanel() {
		var nameEl = document.getElementById('Name');
		var phoneEl = document.getElementById('Phone');
		var naapEl = document.getElementById('man_napno');
		var typeChecked = document.querySelector('input[name="type"]:checked');
		var panelName = document.getElementById('customerPanelName');
		var panelPhone = document.getElementById('customerPanelPhone');
		var panelNaap = document.getElementById('customerPanelNaap');
		var panelType = document.getElementById('customerPanelType');
		if (panelName && nameEl) {
			panelName.textContent = nameEl.value.trim() ? nameEl.value.trim() : '—';
		}
		if (panelPhone && phoneEl) {
			panelPhone.textContent = phoneEl.value.trim() ? phoneEl.value.trim() : '—';
		}
		if (panelNaap && naapEl) {
			panelNaap.textContent = naapEl.value.trim() ? naapEl.value.trim() : '—';
		}
		if (panelType && typeChecked) {
			var face = typeChecked.parentNode.querySelector('.customer-type-chip__card');
			panelType.textContent = face ? face.textContent.trim() : typeChecked.value;
		}
	}
	jQuery(function () {
		initCustomerUrduFields();
		syncCustomerPanel();
		jQuery(document).on('input change', '#Name, #Phone, #man_napno, input[name="type"]', syncCustomerPanel);
	});
	jQuery(document).on('app:ajax-loaded', function () {
		initCustomerUrduFields();
		syncCustomerPanel();
	});
</script>
<body class="animsition erp-app erp-form-page customer-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<?php if ($this->uri->segment(3) == 'err') { ?>
						<div class="alert alert-danger customer-alert" role="alert">
							<?php echo t('customer_naap_exists'); ?>
						</div>
					<?php } ?>

					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head">
								<h2 class="erp-module-title mb-0"><?php echo t('customer_add'); ?></h2>
								<div class="erp-card-actions">
									<span class="erp-entry-number"><?php echo t('customer_manual_naap'); ?> : <?php echo esc_html((string) $max_naap_next); ?></span>
								</div>
							</div>

							<div class="card-body erp-card-body data-entry-body">
								<div class="customer-form-layout">
									<form name="Form" action="<?php echo site_url('Customer/process'); ?>" method="post" data-no-ajax="true">
										<input id="City" name="City" type="hidden" value="">
										<input id="Country" name="Country" type="hidden" value="">
										<input id="Remarks" name="Remarks" type="hidden" value="">

										<div class="customer-info-panel">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_form'); ?></h3>
											</div>
											<div class="customer-info-panel__grid">
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_manual_naap'); ?></span>
													<strong id="customerPanelNaap"><?php echo esc_html((string) $max_naap_next); ?></strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_name'); ?></span>
													<strong id="customerPanelName">—</strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_phone'); ?></span>
													<strong id="customerPanelPhone">—</strong>
												</div>
												<div class="customer-info-panel__item">
													<span><?php echo t('customer_type'); ?></span>
													<strong id="customerPanelType"><?php echo t('customer_type_local'); ?></strong>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_basic'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Name" class="control-label mb-1"><?php echo t('customer_name'); ?></label>
														<input id="Name" name="Name" type="text" class="form-control customer-urdu-field" required value="" autofocus>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="man_napno" class="control-label mb-1"><?php echo t('customer_manual_naap'); ?></label>
														<input id="man_napno" name="man_napno" type="text" class="form-control" required value="<?php echo esc_html((string) $max_naap_next); ?>">
														<span class="customer-field-hint"><?php echo t('customer_naap_hint'); ?></span>
													</div>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_contact'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Phone" class="control-label mb-1"><?php echo t('customer_phone'); ?></label>
														<input id="Phone" name="Phone" type="text" class="form-control" inputmode="tel" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="CNIC" class="control-label mb-1"><?php echo t('customer_cnic'); ?></label>
														<input id="CNIC" name="CNIC" type="text" class="form-control" value="">
													</div>
												</div>
											</div>
										</div>

										<div class="customer-section">
											<div class="customer-section__head">
												<h3 class="customer-section__title"><?php echo t('customer_section_details'); ?></h3>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="control-label mb-1"><?php echo t('customer_type'); ?></label>
														<div class="customer-type-chips">
															<label class="customer-type-chip">
																<input type="radio" name="type" value="Local" checked>
																<span class="customer-type-chip__card"><?php echo t('customer_type_local'); ?></span>
															</label>
															<label class="customer-type-chip">
																<input type="radio" name="type" value="Foreign">
																<span class="customer-type-chip__card"><?php echo t('customer_type_foreign'); ?></span>
															</label>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="Address" class="control-label mb-1"><?php echo t('customer_address'); ?></label>
														<input id="Address" name="Address" type="text" class="form-control customer-urdu-field" value="">
													</div>
												</div>
											</div>
										</div>

										<div class="customer-submit-sticky">
											<div class="customer-submit-sticky__actions">
												<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
													<span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
													<span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
