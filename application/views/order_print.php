
<?php
	$pdf_mode = !empty($pdf_mode);
	$business_config = isset($business_config) && is_array($business_config) ? $business_config : array();
	$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';
	$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';
	$web_img_base = rtrim(base_url(), '/').'/';
	$fs_img_base = (isset($print_img_base) && $print_img_base !== '')
		? rtrim(str_replace('\\', '/', (string) $print_img_base), '/').'/'
		: rtrim(str_replace('\\', '/', FCPATH), '/').'/';
	$pdf_img = function ($rel) use ($pdf_mode, $web_img_base, $fs_img_base) {
		$rel = ltrim(str_replace('\\', '/', (string) $rel), '/');
		if ($pdf_mode)
		{
			return str_replace('\\', '/', $fs_img_base.$rel);
		}
		$parts = array();
		foreach (explode('/', $rel) as $part)
		{
			if ($part !== '')
			{
				$parts[] = rawurlencode($part);
			}
		}
		return $web_img_base.implode('/', $parts);
	};
	if ( ! isset($SR))
	{
		$SR = 0;
	}

	if ( ! $pdf_mode)
	{
		ob_Start();
		$this->load->view('Head');
	}
?>
<?php if ( ! $pdf_mode) { ?>
<head>
    <!-- Required meta tags-->
  

    <!-- Title Page-->
    <title> </title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/jquery.dataTables.css">
 
	<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/jquery.dataTables.js"></script>
	  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:300i" rel="stylesheet">
    <!-- Fontfaces CSS-->
<?php } ?>
   <style media="print">
   .top{
		margin-top:50px;
   }
	.print{
			display:none;
	}
   @page {
  size: landscape;
}
   </style>
   <style>
   <?php if ($pdf_mode) { ?>
   .print { display: none !important; }
   .page-break { display: none !important; height: 0; }
   body, table, td, th, b, span, div {
     font-family: lateef, xbriyaz, sans-serif;
     font-size: 12pt;
     line-height: 1.35;
   }
   .ur, .urd { font-family: lateef, xbriyaz, sans-serif; font-weight: bold; direction: rtl; text-align: right; }
   body { margin: 0 !important; }
   .top { margin-top: 0 !important; }
   <?php } else { ?>
  @media print {
	.page-break	{ display: block; page-break-before: always; }
	
	 @page { margin: 0; }
  body { margin: 5.6cm; }
}
   <?php } ?>
  
	<?php if ($pdf_mode) { ?>
	.urd, .ur {
		font-family: lateef, xbriyaz, sans-serif;
		font-weight: bold;
	}
	<?php } else { ?>
	.urd{
			font-weight:bold;
			font-family: 'Noto Nastaliq Urdu Draft', lateef, xbriyaz, serif;
				 
	}
 
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', lateef, xbriyaz, sans-serif;
	}
	<?php } ?>
	 #Remarks  {
		direction: rtl;
		border: 1px solid #ccc;
		vertical-align: middle;
		direction: rtl;
		padding: 2px;
		font-size: 16px;
	}
	#kb {
		width:500px;
	}
	a {
		color:#4ea0e8;
		text-decoration:none;
	}
	</style>

<?php if ( ! $pdf_mode) { ?>
</head>
	<script type="text/javascript" language="javascript" class="init">
	
$(document).ready(function() {
	$('#example').DataTable( {
		"pagingType": "full_numbers"
	} );
} );

	</script>
<body class="animsition erp-app order-print-page">
<?php } ?>
<?php
 

	if($SR > 1 && ! $pdf_mode)
	{

	echo '<span class="page-break"></span>';
									echo "</br>";
	}
?>
									<div class="page-wrapper top<?php echo $pdf_mode ? ' pdf-slip' : ''; ?>">
        <!-- HEADER MOBILE-->
            <!-- MAIN CONTENT-->

            <div class=" ">
                <div  >
                    <div class="container-fluid">
						 
					 			
                     	<div class="col-md-14" style="<?php echo $pdf_mode ? 'width:100%;' : 'float: right;'; ?>">
                                	  	<?php 
			 foreach($order as $orders){
									 
			 }
			 $order_number = isset($orders->TRNNO) ? $orders->TRNNO : 0;
			 $customer_phone = isset($orders->Phone) ? trim((string) $orders->Phone) : '';
			 $has_whatsapp_phone = ($customer_phone !== '');
			 $wa_phone = '';
			 if ($has_whatsapp_phone) {
				 $digits = preg_replace('/\D+/', '', $customer_phone);
				 if ($digits !== '') {
					 if (strpos($digits, '92') === 0 && strlen($digits) >= 12) {
						 $wa_phone = $digits;
					 } elseif ($digits[0] === '0' && strlen($digits) >= 11) {
						 $wa_phone = '92'.substr($digits, 1);
					 } elseif (strlen($digits) === 10 && $digits[0] === '3') {
						 $wa_phone = '92'.$digits;
					 } else {
						 $wa_phone = $digits;
					 }
				 }
			 }
						                    ?>
                                   <!-- <div class="card-header">Employee Form</div> -->
                                   <?php if ( ! $pdf_mode) { ?>
                                   <div class="order-print-toolbar print">
										<div class="order-print-toolbar__inner">
											<div class="order-print-toolbar__left">
												<a href="<?php echo site_url('Welcome'); ?>" class="btn btn-lg btn-secondary" data-no-ajax="true"><?php echo t('print_back'); ?></a>
												<a href="<?php echo site_url('Order/Order_edit/'.$order_number.''); ?>" class="btn btn-lg btn-primary" data-no-ajax="true"><?php echo t('print_order_edit'); ?></a>
											</div>
											<div class="order-print-toolbar__right">
												<button type="button" class="btn btn-lg btn-success btn-order-whatsapp<?php echo $has_whatsapp_phone ? '' : ' is-disabled'; ?>" data-trnno="<?php echo (int) $order_number; ?>" data-has-phone="<?php echo $has_whatsapp_phone ? '1' : '0'; ?>" data-wa-phone="<?php echo esc_html($wa_phone); ?>" data-endpoint="<?php echo site_url('Order/order_whatsapp_pdf'); ?>" <?php echo $has_whatsapp_phone ? '' : ' disabled'; ?>><?php echo t('print_whatsapp'); ?></button>
												<button type="button" class="btn btn-lg btn-primary" onclick="window.print()"><?php echo t('app_print'); ?></button>
											</div>
										</div>
                                   </div>
                                   <?php } ?>
									<section>
 				
			<?php if ($pdf_mode) { ?>
			<table class="pdf-struct pdf-header" width="100%" cellpadding="4" cellspacing="0">
				<tr>
					<td width="18%" align="center"><img src="<?php echo $pdf_img('images/Logo 1.png'); ?>" style="height:95px;"></td>
					<td width="64%" align="center">
						<img src="<?php echo $pdf_img('images/Logo 3.jpg'); ?>" style="height:95px;"><br>
						<b><?php echo (int) $orders->TRNNO; ?></b><br>
						<span class="ur urd"><?php echo esc_html($print_address); ?></span><br>
						<span style="background:#000;color:#fff;padding:2px 6px;font-size:10pt;">سپشلسٹ</span>
						<span style="background:#000;color:#fff;padding:2px 6px;font-size:10pt;">پینٹ کوٹ،ویسکوٹ،شلوارقمیض</span>
					</td>
					<td width="18%" align="center"><b>Ph: <?php echo esc_html($print_phone); ?></b></td>
				</tr>
			</table>
			<?php } else { ?>
			<table class="col-md-11" >
					<tr>
						<td align="center" width="20%"><img src="<?php echo $pdf_img('images/Logo 1.png'); ?>"  style="height:150px; padding:0px 0px 0px 0px;">
						
								</td>
								<td> </td>
					<td align="center">
					
						<img src="<?php echo $pdf_img('images/Logo 3.jpg'); ?>" class="col-md-9" style="height:150px;     float: right; padding:0px 0px 0px 0px;">
						<span style=" float:left; border-radius:30px; width:30px; height:30px;     margin-left: 100px;
    margin-top: 59px;">
								<b style=" margin-top:2px; float:left"><?php  echo $orders->TRNNO; ?></b>
								
								</span>
						</td>
					</tr>
								<tr>
						<td  style="font-weight:bold;" align="center">Ph: <?php echo esc_html($print_phone); ?>
								
							
								</td>
					<td align="right" colspan="2">
						<span style="margin-right:20px; font-weight:bold; color:white; background: black; border-radius:10px;   width: 63px;
    float: right;
    text-align: center" > سپشلسٹ </span>
	
	<span style="margin-right:20px; font-weight:bold; color:white; background: black; border-radius:10px;   width: 200px;
    float: right;
    text-align: center" >  پینٹ کوٹ،ویسکوٹ،شلوارقمیض </span>
						
						<span class="ur urd" style="margin-right:20px; font-weight:bold;   width: 250px;
    float: right;
    text-align: center; direction: rtl;"><?php echo esc_html($print_address); ?></span>
						</td>
						
						
					</tr>
			</table>
			<?php } ?>
			<table   style="width: 100%;
    line-height: 18px;
    font-family: sans-serif;"   class="col-md-11 pdf-struct<?php echo $pdf_mode ? ' pdf-info-table' : ''; ?>"  >
			<thead>
					<tr>
							
							<td></td>
							<td></td>
							<td>
							&nbsp;</td>
							<td></td>
							
					</tr>
				
					<tr>
							
							<td align="right" colspan="2" style="border-bottom:1px dotted black;" class="ur urd"><?php  echo $orders->man_napno; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60" class="ur urd">ناپ نمبر</th>

							<td align="right" width="400" class="ur urd" style="border-bottom:1px dotted black;"><?php  echo $orders->name; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25" class="ur urd">نام</th>
					</tr>
						<tr>
							
							<td align="right" colspan="2"  class="ur urd"> &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60"> </th>
							<td align="right" width="400"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25"> </th>
					</tr>
							<tr>
							
							<th align="right" class="ur urd" style="float: right;">رات 00۔08 بجے</th>
							<td align="right" class="ur urd" width="150" style="border-bottom:1px dotted black;">
							
							<?php
							$originalDate = $orders->RDATE;
						$newDate = date("d-m-Y", strtotime($originalDate));
							echo $newDate; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" class="ur urd" width="160">تاریخ واپسی</th>
							<td align="right" class="ur urd" width="400" style="border-bottom:1px dotted black;"><?php 

$originalDate1 = $orders->ORDERDATE;
						$newDate1 = date("d-m-Y", strtotime($originalDate1));
							echo $newDate1; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" class="ur urd" width="25">تاریخ</th>
					</tr>
						<tr>
							
							<td align="right" colspan="2"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60"> </th>
							<td align="right" width="400"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25"> </th>
					</tr>
					</table>
					<table   style="width: 100%;
    line-height: 18px;
    font-family: sans-serif;"   class="col-md-11 pdf-struct<?php echo $pdf_mode ? ' pdf-info-table' : ''; ?>"  >
												<tr>
							<td align="right" class="ur urd" width="150" style="border-bottom:1px dotted black;"><?php  echo ($orders->total )-$orders->advance-$orders->discount; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" class="ur urd" width="80">بقایا</th>
							<td align="right" class="ur urd" width="120" style="border-bottom:1px dotted black;"><?php  echo $orders->advance; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right"  class="ur urd" width="100">ایڈوانس</th>
							<td align="right" class="ur urd" width="200" style="border-bottom:1px dotted black;"><?php  echo $orders->qty; ?>&nbsp;&nbsp;&nbsp;</td>
							<td align="right" class="ur urd" width="50"><b>سوٹ تعداد</b></td>
					</tr>
				</thead>
 
				 
			</table>
			<br>
<?php if ($pdf_mode) { ?><table class="pdf-struct pdf-layout-table" width="100%" cellpadding="0" cellspacing="0"><tr><td width="46%" valign="top"><?php } else { ?><div class="row col-md-12"><?php } ?>
			<div class="cols col-md-4">
		 
			 		 <table<?php echo $pdf_mode ? ' class="pdf-meas-table"' : ''; ?>     >
		<?php 
										$SR = 0;
									
			 foreach($order as $orders){
									 
			   }   
					echo"<div style='text-align: center;'>";
						if($type == 'Kurta')
					  echo'<b>قمیض شلوار</b>';
						elseif($type == 'Pajama')	
					echo '<b>پاجامہ </b>';
					elseif($type == 'Pent')	
					echo 		'<b>پینٹ</b>';
					elseif($type == 'Shirt')	
					echo				'<b>شرٹ</b>';
					elseif($type == 'Coat')	
					echo '<b>شروانی/کوٹ</b>';
					elseif($type == 'Waistcoat')
					 echo '<b>ویسکوٹ</b>';
					 echo '</div>';
					echo '<hr>';
			  if($type == 'Kurta')
			  {
						                    ?>
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->BL; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>باذو   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->KL; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>قمیض لمبائی  </b> 
							 
							</td>
						</tr>
								<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->geera; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>گیرا    </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Teera; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>تیرا  </b>  
							 
							</td>
						</tr>	
				<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->pait; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>پیٹ     </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Chaati; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>چھاتی    </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Shalwar_Lenght; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>شلوار لمبائی    </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Gala; ?></td>
									<td align="right" width="120"style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>گلا    </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->SC; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>شلوار پھیر      
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Poncha_Choraic; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>پانچہ    </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->FPL; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>فرنٹ پٹی     </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Asan; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>آسن     </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->CAKLAMBAI; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>چاک لمبائی      </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->BP; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>باذو پٹی     </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->KAAFCURAI; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>کف چوڑائی       </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->CL; ?></td>
									<td align="right" width="120"  style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>کف لمبائی      </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Poncha_Chorai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>پانچہ چوڑائی       </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->MODA_CURAI; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>موڈاچوڑائی   </b>  
							 
							</td>
						</tr>
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->FRONT_Pocket_Size; ?></td>
						<td align="right" width="190" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>فرنٹ پاکٹ سائز</b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->WASIT; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>کمر (ویسٹ) </b>  
							 
							</td>
						</tr>	
		<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Side_Pocket_Size; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>   سائیڈ پاکٹ سائز </b> 
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->BG; ?></td>
									<td align="right" width="140" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>  باذو گول    </b>  
							 
							</td>
			  </tr>	
			  
			  <tr>
				<td> </td> 
			  </tr>
			  <?php  }elseif($type == 'Pajama')
			  {
			  ?>
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PLambai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PPoncha; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>پانچہ  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PAsan; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>آسن   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PPeeir; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>پپھیر  </b> 
							 
							</td>
						</tr>			
			  
			  <?php  }elseif($type == 'Pent')
			  {
			  ?>
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PentLambai; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->pentWaiste; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b>کمر  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->ہپ; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>ہپ   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PentPoncha; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> پانچہ  </b> 
							 
							</td>
						</tr>		
					  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->PentThai; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>Thai
  : </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"> </td>
									<td align="right" width="100" >
							   
							 
							</td>
						</tr>
			 <?php  }elseif($type == 'Shirt')
			  {
			  ?>
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SLambai; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SBazoo; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b>باذو  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->STeera; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>تیرا   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SGeera; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> گیرا  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SChaati; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>چھاتی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SKamar; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"> </td>
						<td align="right" width="120"  >
							   
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->SGaala; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>	
						 <?php  }elseif($type == 'Coat')
			  {
			  ?>
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatLambai; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatBazoo; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b>باذو  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatTeera; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>تیرا   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatChaati; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> چھاتی  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->Coatہپ; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>ہپ   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatKamar; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->crossback; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>Cross Back
   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatGaala; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>		
						 <?php  }elseif($type == 'Waistcoat')
			  {
			  ?>
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastLambai; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastTeera; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b>تیرا  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastہپ; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>ہپ 
   </b>  
 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastChaati; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> چھاتی  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  //echo $orders->Coatہپ; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b><!--ہپ-->  </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastKamar; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td style="border-bottom: 1px solid black;" width="35" align="right"> </td>
						<td align="right" width="120"  >
							   
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastGaala; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>		
			  <?php }  ?>
			 </table>
<table     >			 
			  
						 <tr>
		 
						 
						 
							 
							<td style="border-bottom: 1px solid black;" colspan="2"  width="300"  align="right"><?php  echo $orders->MREMARKS; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b> نوٹ لائن  </b> 
							 
							</td>
						</tr> 		
					</table>
					</div>
<?php if ($pdf_mode) { ?></td><td width="54%" valign="top"><?php } ?>
					<div class="cols col-md-8">
					<div class="cols col-md-14">
			 		
		<?php 
										$SR = 0;
									
			 foreach($order as $orders){
									 $SR = $SR + 1;
								if( $SR == 7 && ! $pdf_mode)
								{
									echo '<span class="page-break"></span>';
									echo "</br></br></br>";
								}
						                    ?>
											 <table   class="pdf-items-table" style="width:100%" >
						<tr>
							
						
								  
										 
						<td align="right" width="400"  style="border-bottom:1px dotted grey;" class="ur urd">
						
						 
							<?php 
						
								echo '  &nbsp;'. $orders->order_remarks. '&nbsp;';
							//	foreach($suit as $suits){  
							
							foreach($suit as $suits){
								  if($suits->SNO == $orders->SNO)
									echo '&nbsp; ' . $suits->name . '&nbsp;'; 
					 		 }
							 echo $orders->MREMARKS;
							 ?>   
							 </td>
					
								<td align="right" width="2%" style="border-bottom:1px dotted grey;">
							<?php  echo "<b>-</b>".$SR.""; ?>
							</td>
						</tr>
				</table>
				
					 <table   class="pdf-items-table" style="width:100%" >
						<tr>
										<td align="left" width="5%" style="border-bottom:1px dotted grey; ">
							 <b><?PHP echo $orders->suitrate + $orders->drate + $orders->erate + $orders->addprice;   ?></b>
							</td>
						
						 
							<td style="border-bottom:1px dotted grey; " width="50" class="item-fabric"><img style="height: 32px;
    max-width: 64px;" src="<?php echo $pdf_img('images/'.$orders->kolor_style.'.jpg'); ?>"
							
							></td>
						
								<td align="right" width="250"  style="border-bottom:1px dotted grey;" class="ur urd">
							<?php   
								
				 	if($orders->Shawlarpocket == ''){ echo '&nbsp; (شلوار پاکٹ X)'; } else{ echo  '&nbsp; (شلوار پاکٹ'.' ' .$orders->Shawlarpocket.')&nbsp;' ; } 
				
				
			 //if($orders->Shawlarpocket != '') { echo 'سائیڈ  پاکٹ '.$orders->Shawlarpocket.','; }
							?>
							
							</td>
										<td align="right"  width="150"  style="border-bottom:1px dotted grey;" class="ur urd">
							<?php   
								 
					 		 
			 	if($orders->spocket == ''){ echo '&nbsp; (سائیڈ  پاکٹ X )'; }else { echo '(&nbsp; سائیڈ  پاکٹ '.$orders->spocket. ')&nbsp; '; }
		 	
				
			 //if($orders->Shawlarpocket != '') { echo 'سائیڈ  پاکٹ '.$orders->Shawlarpocket.','; }
							?>
							
							</td>
										<td align="right"  width="200" style="border-bottom:1px dotted grey;" class="ur urd">
							<?php   
								 
					 		 
			 	if($orders->fpocket == ''){ echo '&nbsp; ( فرنٹ پاکٹ X )'; }else { echo '(&nbsp; فرنٹ پاکٹ  '.$orders->fpocket. ')&nbsp; '; } 
			
				
			 //if($orders->Shawlarpocket != '') { echo 'سائیڈ  پاکٹ '.$orders->Shawlarpocket.','; }
							?>
							
							</td>
							
			 
					
							 
						</tr>
				</table>
<table>				
					
<tr>
							<td align="right" width="5%">
							 <b></b>
							</td>
							<td align="right" width="5%">
							 &nbsp;&nbsp;
							</td>
							<td style="border-bottom:1px dotted grey; "></td>
					
						<td align="left" width="95%" style="border-bottom:1px dotted grey;" class="ur urd">
							
							  <?php
							 
				 
							 
							 foreach($style as $styles){  
					 	 if($styles->DNO == $orders->design)
						   echo $styles->vol_name. '' .$styles->vol_no . ' -DESIGN NO:'.$styles->design . ', '; 
							 }
							 	 foreach($embroidery as $embroiderys){ 
 							 
							 if($embroiderys->ENO == $orders->ENO)
							   echo $embroiderys->embroidery_name. '' .$embroiderys->vol_no . ' -EMBROIDERY NO:'.$embroiderys->design . ', '; 
							  }
							  ?>  
							 
							<?php //if($orders->fpocket != 0) { echo 'Front Pocket '.$orders->fpocket.','; }?>
							<?php //if($orders->spocket != 0) { echo 'Side Pocket '.$orders->spocket; }?>
						 
							</td>
					 
								<td align="right" width="2%" style="border-bottom:1px dotted grey;">
							<?php   ?>
							</td>
						</tr>
						<tr>
							<td colspan="3" style="float:right"></td>
						</tr>
					</table>
							
						 <?php  }  ?>
					
					</div>
					</div>
<?php if ($pdf_mode) { ?></td></tr></table><?php } else { ?></div><?php } ?>
					
	</section>
<?php if ( ! $pdf_mode) { ?>
 <TABLE  width="600">
 		<tr height="15">
		<td width="250"  ></td>
		<td> </td>
	</tr>
	<tr>
		<?php if ( ! empty($orders->image)) { ?>
		<td><img src="<?php echo esc_html($orders->image); ?>" style="height: 200px;" alt="Customer photo"></td>
		<?php } ?>
	</tr>
		 
		<td   ></td>
		<td> </td>
	</tr>
 </TABLE>
<?php } ?>
<?php if ($pdf_mode) { ?>
<table class="pdf-struct pdf-footer" width="100%" cellpadding="6" cellspacing="0">
	<tr>
		<td width="42%" class="pdf-footer-holiday ur urd">ناغہ بروز جمعتہ المبارک</td>
		<td width="58%" class="pdf-footer-terms ur urd">بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</td>
	</tr>
	<tr>
		<td></td>
		<td class="pdf-footer-terms ur urd">دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</td>
	</tr>
</table>
<?php } else { ?>
  <TABLE   class="pdf-footer" style="width: 100%;
    line-height: 18px;
    font-family: sans-serif; background-color: #BBB;"     >
	<tr>
		 <td rowspan="2" align=""center><span class="pdf-footer-holiday" style="margin-right:20px; font-weight:bold;   width: 275px;
    float: right;
	font-size: 30px;
    text-align: center">ناغہ بروز جمعتہ المبارک </span></td>
		<td align="right">	<span class="pdf-footer-terms" style="margin-right:20px; font-weight:bold;   width: 350px;
    float: right;
    text-align: center">بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</span></td>
	</tr>
		<tr>
			 
		<td align="right">	<span class="pdf-footer-terms" style="margin-right:20px; font-weight:bold;   width: 275px;
    float: right;
    text-align: center">دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</span></td>
	</tr>
 </TABLE>
<?php } ?>
                     
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>
		
		
    <!-- Jquery JS-->
    
    <!-- Bootstrap JS-->

 
    
    
    </div>

 

<?php if ( ! $pdf_mode) { ?>
<script>
(function () {
	if (window.__orderWhatsappInit) { return; }
	window.__orderWhatsappInit = true;
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.btn-order-whatsapp');
		if (!btn || btn.disabled) { return; }
		if (btn.getAttribute('data-has-phone') !== '1') { alert((window.APP_LANG && APP_LANG.phone_missing) || 'Customer phone number is missing.'); return; }
		var trnno = btn.getAttribute('data-trnno');
		var endpoint = btn.getAttribute('data-endpoint');
		var waPhone = btn.getAttribute('data-wa-phone') || '';
		var originalText = btn.textContent;
		btn.disabled = true;
		btn.textContent = 'Please wait...';
		var waWin = null;
		if (waPhone) {
			try {
				waWin = window.open('https://wa.me/' + waPhone, '_blank', 'noopener,noreferrer');
			} catch (openErr) {
				waWin = null;
			}
		}
		var body = new URLSearchParams();
		body.append('trnno', trnno);
		fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: body.toString(), credentials: 'same-origin' })
			.then(function (res) { return res.text(); })
			.then(function (raw) { var data; try { data = JSON.parse(raw); } catch (err) { throw new Error('Server error. Please try again.'); } return data; })
			.then(function (data) {
				if (!data || !data.success) { throw new Error((data && data.message) ? data.message : 'Could not prepare PDF.'); }
				var phone = data.phone || waPhone;
				var waUrl = 'https://wa.me/' + phone + (data.message ? ('?text=' + encodeURIComponent(data.message)) : '');
				if (waWin && !waWin.closed) {
					try { waWin.location.href = waUrl; } catch (navErr) { waWin = null; }
				}
				if (!waWin || waWin.closed) {
					var waLink = document.createElement('a');
					waLink.href = waUrl;
					waLink.target = '_blank';
					waLink.rel = 'noopener noreferrer';
					document.body.appendChild(waLink);
					waLink.click();
					document.body.removeChild(waLink);
				}
				if (data.download_url) {
					var dl = document.createElement('a');
					dl.href = data.download_url;
					dl.download = data.filename || ('order_' + trnno + '.pdf');
					dl.style.display = 'none';
					document.body.appendChild(dl);
					dl.click();
					document.body.removeChild(dl);
				}
			})
			.catch(function (err) { alert(err.message || (window.APP_LANG && APP_LANG.whatsapp_failed) || 'Could not send to WhatsApp.'); })
			.finally(function () { btn.disabled = false; btn.textContent = originalText; });
	});
})();
</script>
</body>
</html>
<?php } ?>
<!-- end document-->
<?php if ($pdf_mode) { return; } ?>
