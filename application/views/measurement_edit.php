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
?>
<link href="<?php echo base_url('css/measurement-form-enhancements.css?v=7'); ?>" rel="stylesheet" media="all">
<link rel="stylesheet" href="//fonts.googleapis.com/earlyaccess/notonastaliqurdudraft.css">
<script>
	$(function() {
		$('#Remarks').setUrduInput();
	});
</script>
<body class="animsition erp-app erp-form-page measurement-form-page">
    <div class="page-wrapper">
      <form name="Form" action="<?php echo site_url('Measurement/process_edit/'.$measurement_row->MCODE.''); ?>" method="post" data-no-ajax="true">
	  <?php if (!empty($return_order)) { ?>
	  <input type="hidden" name="return_to" value="order">
	  <?php } ?>
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
						<?php if($this->session->flashdata('suss') == 'yes') { ?>
					 <div class="sufee-alert alert with-close alert-success alert-dismissible fade show measurement-alert">
											<span class="badge badge-pill badge-success"><?php echo t('app_success'); ?></span>
											<?php echo t('app_record_entered_short'); ?>
											<button type="button" class="close" data-dismiss="alert" aria-label="Close">
												<span aria-hidden="true">×</span>
											</button>
										</div>
						<?php  } ?>
                     	<div class="col-md-12">
                                <div class="dz-card erp-module-card data-entry-card measurement-entry-card">
									<div class="erp-card-head">
										<h2 class="erp-module-title mb-0"><?php echo t('measurement_edit'); ?></h2>
										<?php if ($measurement_customer) { ?>
										<div class="erp-card-actions">
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
							<h3 class="measurement-section__title"><?php echo t('measurement_section_garments'); ?></h3>
						</div>
		 <div class="measurement-tab-body">
										<div class="default-tab">
											<nav>
												<div class="nav nav-tabs" id="nav-tab" role="tablist">
													<a class="nav-item nav-link active" id="nav-home-Kurta" data-toggle="tab" href="#nav-Kurta" role="tab" aria-controls="nav-Kurta" aria-selected="true"><?php echo t('measurement_tab_kurta'); ?></a>
													<a class="nav-item nav-link" id="nav-Pajama-tab" data-toggle="tab" href="#nav-Pajama" role="tab" aria-controls="nav-Pajama" aria-selected="false"><?php echo t('measurement_tab_pajama'); ?></a>
													<a class="nav-item nav-link" id="nav-Pent-tab" data-toggle="tab" href="#nav-Pent" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t('measurement_tab_pent'); ?></a>
													<a class="nav-item nav-link" id="nav-Shrit-tab" data-toggle="tab" href="#nav-Shrit" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t('measurement_tab_shirt'); ?></a>
													<a class="nav-item nav-link" id="nav-Coat-tab" data-toggle="tab" href="#nav-Coat" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t('measurement_tab_coat'); ?></a>
													<a class="nav-item nav-link" id="nav-Waistcoat-tab" data-toggle="tab" href="#nav-Waistcoat" role="tab" aria-controls="nav-contact" aria-selected="false"><?php echo t('measurement_tab_waistcoat'); ?></a>
												</div>
											</nav>
											<div class="tab-content pl-3 pt-2" id="nav-tabContent">
												<div class="tab-pane fade show active" id="nav-Kurta" role="tabpanel" aria-labelledby="nav-Kurta-tab">
	 			<div class="row data-entry-actions">
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
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گیرا</label>
                                                <input id="BPL" name="geera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->geera; ?>"  re>
                                            </div>  
											
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
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پیٹ</label>
                                                <input id="CKC" name="pait"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->pait; ?>"  re>
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
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کمر (ویسٹ)</label>
                                                <input id="CKC" name="WASIT"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->WASIT; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کہنی</label>
                                                <input id="Moda" name="FRONT_Pocket_Size"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->FRONT_Pocket_Size; ?>"  re>
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
												</div>
												<div class="tab-pane fade" id="nav-Pent" role="tabpanel" aria-labelledby="nav-Pent-tab">
															<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="PentLambai" name="PentLambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PentLambai; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کمر </label>
                                                <input id="pentWaiste" name="pentWaiste"   type="PPoncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->pentWaiste; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">HIP</label>
                                                <input id="HIP" name="HIP"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->HIP; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">پانچہ</label>
                                                <input id="PentPoncha" name="PentPoncha"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PentPoncha; ?>"  re>
                                            </div>  
											
						</div>
									<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">Thai</label>
                                                <input id="PentThai" name="PentThai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->PentThai; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
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
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گیرا</label>
                                                <input id="SGeera" name="SGeera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->SGeera; ?>"  re>
                                            </div>  
											
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
												</div>
																								<div class="tab-pane fade" id="nav-Coat" role="tabpanel" aria-labelledby="nav-Coat-tab">
																									<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;لمبائی </label>
                                                <input id="CoatLambai" name="CoatLambai"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatLambai; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">باذو</label>
                                                <input id="CoatBazoo" name="CoatBazoo"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatBazoo; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">تیرا</label>
                                                <input id="CoatTeera" name="CoatTeera"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatTeera; ?>"  re>
                                            </div>  
											
						</div>
							<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">&nbsp;چھاتی </label>
                                                <input id="CoatChaati" name="CoatChaati"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatChaati; ?>"  re>
                                            </div>  
											
						</div>
					</div>	
											<div class="row">
								 		<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">HIP </label>
                                                <input id="CoatChaati" name="CoatHIP"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatHIP; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">کمر </label>
                                                <input id="CoatKamar" name="CoatKamar"   type="Poncha" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatKamar; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                             <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">گلا</label>
                                                <input id="CoatGaala" name="CoatGaala"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->CoatGaala; ?>"  re>
                                            </div>  
											
						</div>
								<div class="col">		
                                               <div class="form-group" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;" style="    padding: 0px 0px 0px 0px;    margin: 0px 0px 0px 0px;">
                                                <label for="emp-EMPNAME" class="control-label mb-1 urd">Cross Back</label>
                                                <input id="crossback" name="crossback"   type="text" class="form-control txtbox" aria-required="true" aria-invalid="false" value="<?php echo $measurement->crossback; ?>"  re>
                                            </div> 
											
						</div>
					</div>
							
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
					</div>
												</div>
											</div>

										</div>
									</div>
					</div>

					<div class="measurement-section measurement-notes">
						<div class="measurement-section__head">
							<h3 class="measurement-section__title"><?php echo t('measurement_section_notes'); ?></h3>
						</div>
						<div class="form-group">
							<label for="Remarks" class="control-label mb-1 urd"><?php echo t('measurement_note_line'); ?></label>
							<input name="Remarks" id="Remarks" type="text" class="form-control txtbox" value="<?php echo esc_html((string) $measurement_row->MREMARKS); ?>">
						</div>
					</div>

					<div class="measurement-submit-sticky">
						<div class="measurement-submit-sticky__actions measurement-submit-sticky__actions--split">
							<a href="<?php echo site_url('Measurement'); ?>" class="btn btn-lg btn-secondary btn-block" data-no-ajax="true"><?php echo t('app_back'); ?></a>
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
