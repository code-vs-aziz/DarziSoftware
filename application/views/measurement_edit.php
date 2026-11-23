<?php
	ob_Start();
$this->load->helper('ui');

	$this->load->view('Head');
	$this->load->view('Header');

	$measurement_row = null;
	if (!empty($measurement) && is_array($measurement)) {
		foreach ($measurement as $measurement_row) {
			break;
		}
	}
	if (!$measurement_row) {
		$measurement_row = (object) array('MCODE' => 0, 'MREMARKS' => '');
	}
	$measurement = $measurement_row;
	$measurement_customer = null;
	if (!empty($customer_data) && is_array($customer_data)) {
		foreach ($customer_data as $measurement_customer) {
			break;
		}
	}
	$legacy_remarks = '';
	if (isset($measurement_row->MREMARKS)) {
		$legacy_remarks = (string) $measurement_row->MREMARKS;
	} elseif (isset($measurement_row->remarks)) {
		$legacy_remarks = (string) $measurement_row->remarks;
	}
	$measurement_notes = array(
		'kurta' => isset($measurement_row->remarks_kurta) && trim((string) $measurement_row->remarks_kurta) !== ''
			? (string) $measurement_row->remarks_kurta
			: $legacy_remarks,
		'pajama' => isset($measurement_row->remarks_pajama) ? (string) $measurement_row->remarks_pajama : '',
		'pent' => isset($measurement_row->remarks_pent) ? (string) $measurement_row->remarks_pent : '',
		'shirt' => isset($measurement_row->remarks_shirt) ? (string) $measurement_row->remarks_shirt : '',
		'coat' => isset($measurement_row->remarks_coat) ? (string) $measurement_row->remarks_coat : '',
		'pentcoat' => isset($measurement_row->remarks_pentcoat) ? (string) $measurement_row->remarks_pentcoat : '',
		'waistcoat' => isset($measurement_row->remarks_waistcoat) ? (string) $measurement_row->remarks_waistcoat : '',
	);
?>
<link href="<?php echo base_url('css/measurement-form-enhancements.css?v=13'); ?>" rel="stylesheet" media="all">
<link rel="stylesheet" href="//fonts.googleapis.com/earlyaccess/notonastaliqurdudraft.css">
<script>
	function initMeasurementUrduNotes() {
		if (!window.jQuery || !jQuery.fn.setUrduInput) return;
		jQuery('.js-urdu-note').each(function () {
			var $field = jQuery(this);
			if (!$field.data('urduBound')) {
				$field.setUrduInput();
				$field.data('urduBound', 1);
			}
		});
	}
	jQuery(function () { initMeasurementUrduNotes(); });
	jQuery(document).on('app:ajax-loaded', initMeasurementUrduNotes);
</script>
<body class="animsition erp-app erp-form-page measurement-form-page">
    <div class="page-wrapper">
      <form name="Form" action="<?php echo site_url('Measurement/process_edit/'.$measurement_row->MCODE.''); ?>" method="post" data-no-ajax="true">
	  <input type="hidden" name="row_version" value="<?php echo (int) (isset($measurement_row->row_version) ? $measurement_row->row_version : 1); ?>">
	  <input type="hidden" name="code" value="<?php echo (int) $measurement_row->MCODE; ?>">
	  <?php if (!empty($return_order)) { ?>
	  <input type="hidden" name="return_to" value="order">
	  <?php } ?>
            <div class="main-content measurement-form-page">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						<?php $this->load->view('partials/success_alert', array('success_default' => t('app_saved_success'))); ?>
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card measurement-entry-card">
									<?php
					$meas_customer_edit_url = '';
					if ($measurement_customer) {
						$meas_customer_code = (int) $measurement_customer->code;
						$meas_edit_return = 'Measurement/measurement_edit/'.$meas_customer_code;
						if (!empty($return_order)) {
							$meas_edit_return .= '?return=order';
						}
						$meas_customer_edit_url = site_url('Customer/customer_edit/'.$meas_customer_code).'?return_to='.rawurlencode($meas_edit_return);
					}
									?>
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('measurement_edit'); ?></h2>
										<?php if ($measurement_customer) { ?>
										<div class="erp-card-actions" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
											<a class="btn btn-sm btn-primary" href="<?php echo esc_html($meas_customer_edit_url); ?>" data-no-ajax="true"><?php echo esc_html(t('customer_edit')); ?></a>
											<span class="erp-entry-number"><?php echo t('customer_manual_naap'); ?> : <?php echo esc_html((string) $measurement_customer->man_napno); ?></span>
										</div>
										<?php } ?>
									</div>
                                    <div class="card-body erp-card-body data-entry-body">
									<div class="measurement-form-layout">
                              <div id="page1" style="display:block;">

					<?php if ($measurement_customer) { ?>
					<input name="customerno1" type="hidden" value="<?php echo (int) $measurement_customer->code; ?>">
					<div class="measurement-info-panel">
						<div class="measurement-section__head">
							<h3 class="measurement-section__title"><?php echo t('customer_title'); ?></h3>
							<a class="btn btn-sm btn-outline-primary dz-btn--outlined" href="<?php echo esc_html($meas_customer_edit_url); ?>" data-no-ajax="true"><?php echo esc_html(t('customer_edit')); ?></a>
						</div>
						<div class="measurement-info-panel__grid">
							<div class="measurement-info-panel__item">
								<span><?php echo t('customer_name'); ?></span>
								<strong><?php echo esc_html((string) $measurement_customer->name); ?></strong>
							</div>
							<div class="measurement-info-panel__item">
								<span><?php echo t('customer_phone'); ?></span>
								<strong><?php echo esc_html((string) $measurement_customer->Phone); ?></strong>
							</div>
							<div class="measurement-info-panel__item">
								<span><?php echo t('customer_manual_naap'); ?></span>
								<strong><?php echo esc_html((string) $measurement_customer->man_napno); ?></strong>
							</div>
						</div>
					</div>
					<input name="man_napno" type="hidden" value="<?php echo esc_html((string) $measurement_customer->man_napno); ?>">
					<?php } ?>

					<div class="measurement-section">
						<div class="measurement-section__head">
							<h3 class="measurement-section__title">Garment measurements</h3>
						</div>
		 <div class="measurement-tab-body">
										<div class="default-tab">
											<nav>
												<div class="nav nav-tabs" id="nav-tab" role="tablist">
													<a class="nav-item nav-link active urd" id="nav-home-Kurta" data-toggle="tab" href="#nav-Kurta" role="tab" aria-controls="nav-Kurta" aria-selected="true"><?php echo t_urdu('measurement_tab_kurta'); ?></a>
													<a class="nav-item nav-link urd" id="nav-Pajama-tab" data-toggle="tab" href="#nav-Pajama" role="tab" aria-controls="nav-Pajama" aria-selected="false"><?php echo t_urdu('measurement_tab_pajama'); ?></a>
													<a class="nav-item nav-link urd" id="nav-Pent-tab" data-toggle="tab" href="#nav-Pent" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t_urdu('measurement_tab_pent'); ?></a>
													<a class="nav-item nav-link urd" id="nav-Shrit-tab" data-toggle="tab" href="#nav-Shrit" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t_urdu('measurement_tab_shirt'); ?></a>
													<a class="nav-item nav-link urd" id="nav-Coat-tab" data-toggle="tab" href="#nav-Coat" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t_urdu('measurement_tab_coat'); ?></a>
													<a class="nav-item nav-link urd" id="nav-PentCoat-tab" data-toggle="tab" href="#nav-PentCoat" role="tab" aria-controls="nav-PentCoat" aria-selected="false"><?php echo t_urdu('measurement_tab_pentcoat'); ?></a>
													<a class="nav-item nav-link urd" id="nav-Waistcoat-tab" data-toggle="tab" href="#nav-Waistcoat" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t_urdu('measurement_tab_waistcoat'); ?></a>
												</div>
											</nav>
											<div class="tab-content" id="nav-tabContent">
												<div class="tab-pane fade show active" id="nav-Kurta" role="tabpanel" aria-labelledby="nav-Kurta-tab">
	 			<div class="row">
						<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="KL" class="control-label mb-1 urd">قمیض لمبائی</label>
                                                <input id="KL" name="KL"   type="text" class="form-control txtbox" aria-required="true"  aria-invalid="false" value="<?php echo $measurement->KL; ?>" >
                                            </div>  
											
						</div>
		 		<div class="col">		
				     <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">باذو</label>
                                                <input id="BL" name="BL"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->BL; ?>"   >
                                            </div>  
                                              
											
						</div>
								<div class="col">	
<div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">تیرا</label>
                                                <input id="Teera" name="Teera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Teera; ?>"  re>
                                            </div>  
																			
											
											
						</div>
								<div class="col">
<?php
	$this->load->view('partials/measurement_geera_triple', array(
		'field_name' => 'geera',
		'field_id' => 'BPL',
		'label' => 'گیرا',
		'value' => isset($measurement->geera) ? $measurement->geera : '',
	));
?>
						</div>
					</div>	
																	<div class="row">
								 		<div class="col">		
                                              <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd"> &nbsp; چھاتی</label>
                                                <input id="Chaati" name="Chaati"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Chaati; ?>"  re>
                                            </div>  
											
						</div>
						<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="pait" class="control-label mb-1 urd">پیٹ</label>
                                                <input id="pait" name="pait"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo esc_html((string) $measurement->pait); ?>"  re>
                                            </div>  
											
						</div>
										 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گلا</label>
                                                <input id="Chaati" name="Gala"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Gala; ?>"  re>
                                            </div>  
											
						</div>
										<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">شلوار لمبائی</label>
                                                <input id="Shalwar_Lenght" name="Shalwar_Lenght"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Shalwar_Lenght; ?>"  re>
                                            </div>  
											
						</div>
						
					</div>	
																		<div class="row">
																				<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پانچہ</label>
                                                <input id="Poncha_Chorai" name="Poncha_Choraic"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Poncha_Choraic; ?>"  re>
                                            </div>  
											
						</div>
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">شلوار پھیر</label>
                                                <input id="SC" name="SC"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SC; ?>"  re>
                                            </div>  
											
						</div>
								
						 								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">آسن </label>
                                                <input id="Asan" name="Asan"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Asan; ?>"  re>
                                            </div>  
											
						</div>
						 		<div class="col">		
                                          <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">فرنٹ پٹی</label>
                                                <input id="FPL" name="FPL"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->FPL; ?>"  re>
                                            </div>  
											
						</div>
						
					</div>	
	<div class="row">
						<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">باذو پٹی</label>
                                                <input id="KC" name="BP"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->BP; ?>"  >
                                            </div>  
											
						</div>
									<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">چاک لمبائی </label>
                                                <input id="Moda" name="CAKLAMBAI"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CAKLAMBAI; ?>"  re>
                                            </div>  
											
						</div>
									<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کف لمبائی </label>
                                                <input id="CL" name="CL"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CL; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کف چوڑائی </label>
                                                <input id="Moda" name="KAAFCURAI"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->KAAFCURAI; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">موڈاچوڑائی  </label>
                                                <input id="PLM" name="MODA_CURAI"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->MODA_CURAI; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پانچہ چوڑائی </label>
                                                <input id="FPS" name="Poncha_Chorai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Poncha_Chorai; ?>"  re>
                                            </div>  
											
						</div>
							<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کہنی</label>
                                                <input id="Moda" name="FRONT_Pocket_Size"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->FRONT_Pocket_Size; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="WASIT" class="control-label mb-1 urd">کمر (ویسٹ)</label>
                                                <input id="WASIT" name="WASIT"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo esc_html((string) $measurement->WASIT); ?>"  re>
                                            </div>  
											
						</div>
					</div>			
<div class="row">
							<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">ہیپ</label>
                                                <input id="Side_Pocket_Size" name="Side_Pocket_Size"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Side_Pocket_Size; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">باذو گول </label>
                                                <input id="BG" name="BG"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->BG; ?>"  >
                                            </div>  
											
						</div>
								<div class="col">
						</div>
								<div class="col">
						</div>
					</div>						
							
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'kurta',
	'note_name' => 'RemarksKurta',
	'note_id' => 'RemarksKurta',
	'note_value' => isset($measurement_notes['kurta']) ? (string) $measurement_notes['kurta'] : '',
)); ?>
</div>
												<div class="tab-pane fade" id="nav-Shalwar" role="tabpanel" aria-labelledby="nav-Shalwar-tab">
			<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd"> لمبائی </label>
                                                <input id="Lambai" name="Lambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Lambai; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پانچہ
</label>
                                                <input id="FPS" name="Poncha"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Poncha; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">آسن </label>
                                                <input id="Asan" name="shalwar_Asan"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->shalwar_Asan; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">Peeir</label>
                                                <input id="Peeir" name="Peeir"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->Peeir; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
												</div>
												<div class="tab-pane fade" id="nav-Pajama" role="tabpanel" aria-labelledby="nav-Pajama-tab">
															<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="PLambai" name="PLambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PLambai; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پانچہ</label>
                                                <input id="FPS" name="PPoncha"   type="PPoncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PPoncha; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">آسن</label>
                                                <input id="PAsan" name="PAsan"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PAsan; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">Peeir</label>
                                                <input id="PPeeir" name="PPeeir"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PPeeir; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
												
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'pajama',
	'note_name' => 'RemarksPajama',
	'note_id' => 'RemarksPajama',
	'note_value' => isset($measurement_notes['pajama']) ? (string) $measurement_notes['pajama'] : '',
)); ?>
</div>
												<div class="tab-pane fade" id="nav-Pent" role="tabpanel" aria-labelledby="nav-Pent-tab">
<?php
	$mv = function () use ($measurement) {
		$keys = func_get_args();
		foreach ($keys as $key) {
			if (isset($measurement->$key) && trim((string) $measurement->$key) !== '') {
				return (string) $measurement->$key;
			}
		}
		$k0 = $keys[0];
		return isset($measurement->$k0) ? (string) $measurement->$k0 : '';
	};
?>
															<div class="row">
								 		<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="PentLambai" name="PentLambai" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PentLambai')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">کمر </label>
                                                <input id="pentWaiste" name="pentWaiste" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('pentWaiste')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">ہپ</label>
                                                <input id="HIP" name="HIP" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('HIP')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">پٹ</label>
                                                <input id="PentPat" name="PentPat" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PentPat', 'PentThai')); ?>">
                                            </div>
						</div>
									<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">گھٹنہ</label>
                                                <input id="PentGhutna" name="PentGhutna" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PentGhutna')); ?>">
                                            </div>
						</div>
									<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">پانچہ موری</label>
                                                <input id="PentPonchaMori" name="PentPonchaMori" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('PentPonchaMori', 'PentPoncha')); ?>">
                                            </div>
						</div>
					</div>
												
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'pent',
	'note_name' => 'RemarksPent',
	'note_id' => 'RemarksPent',
	'note_value' => isset($measurement_notes['pent']) ? (string) $measurement_notes['pent'] : '',
)); ?>
</div>
												<div class="tab-pane fade" id="nav-Shrit" role="tabpanel" aria-labelledby="nav-Shrit-tab">
														<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="SLambai" name="SLambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SLambai; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">باذو</label>
                                                <input id="SBazoo" name="SBazoo"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SBazoo; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">تیرا</label>
                                                <input id="STeera" name="STeera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->STeera; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">
<?php
	$this->load->view('partials/measurement_geera_triple', array(
		'field_name' => 'SGeera',
		'field_id' => 'SGeera',
		'label' => 'گیرا',
		'value' => isset($measurement->SGeera) ? $measurement->SGeera : '',
	));
?>
						</div>
					</div>	
											<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;چھاتی </label>
                                                <input id="SChaati" name="SChaati"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SChaati; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کمر </label>
                                                <input id="SKamar" name="SKamar"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SKamar; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گلا</label>
                                                <input id="SGaala" name="SGaala"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SGaala; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                          
											
						</div>
					</div>
												
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'shirt',
	'note_name' => 'RemarksShirt',
	'note_id' => 'RemarksShirt',
	'note_value' => isset($measurement_notes['shirt']) ? (string) $measurement_notes['shirt'] : '',
)); ?>
</div>
																								<div class="tab-pane fade" id="nav-Coat" role="tabpanel" aria-labelledby="nav-Coat-tab">
<?php
	if ( ! isset($mv) || ! is_callable($mv)) {
		$mv = function () use ($measurement) {
			$keys = func_get_args();
			foreach ($keys as $key) {
				if (isset($measurement->$key) && trim((string) $measurement->$key) !== '') {
					return (string) $measurement->$key;
				}
			}
			$k0 = $keys[0];
			return isset($measurement->$k0) ? (string) $measurement->$k0 : '';
		};
	}
?>
																									<div class="row">
								 		<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="CoatLambai" name="CoatLambai" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatLambai')); ?>">
                                            </div>
						</div>
							<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">&nbsp;چھاتی </label>
                                                <input id="CoatChaati" name="CoatChaati" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatChaati')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">کمر </label>
                                                <input id="CoatKamar" name="CoatKamar" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatKamar')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">تیرا</label>
                                                <input id="CoatTeera" name="CoatTeera" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatTeera')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">بازو</label>
                                                <input id="CoatBazoo" name="CoatBazoo" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatBazoo')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">گلا</label>
                                                <input id="CoatGaala" name="CoatGaala" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatGaala')); ?>">
                                            </div>
						</div>
					</div>
											<div class="row">
								 		<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">ہپ</label>
                                                <input id="CoatHIP" name="CoatHIP" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatHIP', 'SherwaniHIP')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">ڈولا</label>
                                                <input id="CoatDola" name="CoatDola" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatDola', 'SherwaniDola')); ?>">
                                            </div>
						</div>
								<div class="col">
                                             <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">بازو موری</label>
                                                <input id="CoatBazooMori" name="CoatBazooMori" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatBazooMori', 'SherwaniBazooMori')); ?>">
                                            </div>
						</div>
								<div class="col">
                                               <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">کراس بیک</label>
                                                <input id="crossback" name="crossback" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('crossback', 'SherwaniCrossback')); ?>">
                                            </div>
						</div>
								<div class="col">
                                               <div class="form-group" style="padding:0;margin:0;">
                                                <label class="control-label mb-1 urd">لمبائی بیک</label>
                                                <input id="CoatLambaiBack" name="CoatLambaiBack" type="text" class="form-control txtbox" value="<?php echo esc_html($mv('CoatLambaiBack', 'SherwaniLambaiBack')); ?>">
                                            </div>
						</div>
					</div>
							
												
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'coat',
	'note_name' => 'RemarksCoat',
	'note_id' => 'RemarksCoat',
	'note_value' => isset($measurement_notes['coat']) ? (string) $measurement_notes['coat'] : '',
)); ?>
</div>
												<div class="tab-pane fade" id="nav-PentCoat" role="tabpanel" aria-labelledby="nav-PentCoat-tab">
<?php $this->load->view('partials/measurement_pentcoat_fields', array(
	'measurement' => $measurement,
	'note_value' => isset($measurement_notes['pentcoat']) ? (string) $measurement_notes['pentcoat'] : '',
)); ?>
</div>
																																<div class="tab-pane fade" id="nav-Waistcoat" role="tabpanel" aria-labelledby="nav-Waistcoat-tab">
																																<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="wastLambai" name="wastLambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastLambai; ?>"  re>
                                            </div>  
											
						</div>
								 
											
					 
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">تیرا</label>
                                                <input id="wastTeera" name="wastTeera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastTeera; ?>"  re>
                                            </div>  
											
						</div>
							<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;چھاتی </label>
                                                <input id="wastChaati" name="wastChaati"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastChaati; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
											<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">HIP </label>
                                                <input id="wastChaati" name="wastHIP"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastHIP; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کمر </label>
                                                <input id="wastKamar" name="wastKamar"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastKamar; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گلا</label>
                                                <input id="wastGaala" name="wastGaala"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->wastGaala; ?>"  re>
                                            </div>  
											
						</div>
								 
											
						</div>
					
<?php $this->load->view('partials/measurement_garment_note', array(
	'note_key' => 'waistcoat',
	'note_name' => 'RemarksWaistcoat',
	'note_id' => 'RemarksWaistcoat',
	'note_value' => isset($measurement_notes['waistcoat']) ? (string) $measurement_notes['waistcoat'] : '',
)); ?>
</div>
												</div>
											</div>

										</div>
									</div>
					</div>

					<div class="measurement-submit-sticky">
						<div class="measurement-submit-sticky__actions">
							<button id="submit-button" type="submit" name="savBtn" class="btn btn-lg btn-primary btn-block">
								<span id="submit-button-amount"><?php echo t('app_submit'); ?></span>
								<span id="submit-button-sending" style="display:none;"><?php echo t('app_saving'); ?></span>
							</button>
						</div>
					</div>
</div>
</div>
                                    </div>
                                </div>
                     	</div>
                    </div>
                </div>
            </div>
                      </form>
    </div>
<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("emp-EMPTITLE");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
</script>
    <!-- Jquery JS-->
  
    <!-- Bootstrap JS-->
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="<?php echo base_url(); ?>vendor/slick/slick.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/wow/wow.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/animsition/animsition.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="<?php echo base_url(); ?>vendor/circle-progress/circle-progress.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?php echo base_url(); ?>vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="<?php echo base_url(); ?>vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="<?php echo base_url(); ?>js/main.js"></script>
 <script type="text/javascript">
            $(function () {
                $('#datetimepicker1').datetimepicker();
            });
        </script>
</body>

</html>
<!-- end document-->
