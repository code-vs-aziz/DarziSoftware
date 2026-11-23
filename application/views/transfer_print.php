<style type="text/css">
.font18{
	font-size:20px !important;
	font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
}
.font19{
	font-size:24px !important;
	font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
}
/* Top header — old slip layout, lines fill page (NO fixed 960px — that clipped نام on small screens).
   [آرڈر]  [ناپ#________] ناپ نمبر [________نام________] نام */
.tp-header-bar {
	display: flex !important;
	flex-direction: row !important;
	align-items: flex-end !important;
	justify-content: flex-start !important;
	width: 100% !important;
	max-width: 100% !important;
	direction: ltr !important;
	margin: 20px 0 8px 0 !important;
	padding: 6px 12px 0 8px !important;
	box-sizing: border-box !important;
	overflow: visible !important;
}
.tp-header-left {
	display: flex !important;
	flex-direction: row !important;
	align-items: flex-end !important;
	gap: 6px !important;
	flex: 0 0 auto !important;
	margin-left: 80px !important;
	margin-right: 16px !important;
}
.tp-header-right {
	display: flex !important;
	flex-direction: row !important;
	flex-wrap: nowrap !important;
	align-items: flex-end !important;
	gap: 0 !important;
	flex: 1 1 auto !important;
	min-width: 0 !important;
	direction: rtl !important;
	overflow: visible !important;
}
.tp-h-lbl {
	flex: 0 0 auto !important;
	white-space: nowrap !important;
	font-size: 17px !important;
	font-weight: 700 !important;
	line-height: 1.2 !important;
	padding: 0 0 4px 0 !important;
	margin: 0 !important;
}
.tp-h-uline {
	display: block !important;
	border-bottom: 1.5px solid #000 !important;
	padding: 0 6px 2px 6px !important;
	margin: 0 !important;
	line-height: 1.2 !important;
	font-size: 24px !important;
	font-weight: 800 !important;
	font-family: Arial, Helvetica, sans-serif !important;
	color: #000 !important;
	white-space: nowrap !important;
	box-sizing: border-box !important;
	vertical-align: bottom !important;
}
/* Equal long lines — share remaining width so نام never clips off-screen */
.tp-h-uline.tp-cutting-name {
	flex: 1 1 0 !important;
	width: auto !important;
	min-width: 140px !important;
	max-width: none !important;
	margin: 0 4px 0 8px !important;
	text-align: right !important;
	font-size: 20px !important;
	font-weight: 700 !important;
	line-height: 1.7 !important;
	padding: 0 6px 4px 6px !important;
	font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
	word-break: keep-all !important;
	overflow: visible !important;
}
.tp-h-uline.tp-h-naap-val {
	flex: 1 1 0 !important;
	width: auto !important;
	min-width: 140px !important;
	max-width: none !important;
	text-align: right !important;
	font-size: 22px !important;
	font-weight: 800 !important;
	padding: 0 6px 3px 6px !important;
	margin: 0 4px 0 0 !important;
}
.tp-header-right .tp-h-lbl-name {
	margin: 0 !important;
	padding-bottom: 4px !important;
}
.tp-header-right .tp-h-lbl-naap {
	margin: 0 0 0 4px !important;
	padding-bottom: 4px !important;
}
.tp-order-no {
	display: inline-block !important;
	float: none !important;
	font-family: Arial, Helvetica, sans-serif !important;
	font-size: 26px !important;
	font-weight: 800 !important;
	line-height: 1.15 !important;
	color: #000 !important;
	margin: 0 !important;
	padding: 0 !important;
	white-space: nowrap;
}
.tp-slip {
	position: relative;
	clear: both;
	overflow: visible; /* was hidden — clipped نام when header lines were too wide */
	page-break-after: always;
	margin-top: 20px !important;
	margin-left: 24px !important;
	margin-right: 16px !important;
	padding-top: 20px !important;
	padding-left: 8px !important;
}
/* Design / style summary under header — bold so cutter can read fast */
.tp-design-line,
td.tp-design-line,
.tp-design-line.ur.urd.font19 {
	font-weight: 800 !important;
	font-size: 22px !important;
	color: #000 !important;
	line-height: 1.85 !important;
}
.tp-slip:last-child {
	page-break-after: auto;
}
/* Measurement numbers: large, bold, print-clear (not thin Nastaliq) */
.tp-meas-num,
td.tp-meas-num.font18 {
	font-family: Arial, Helvetica, sans-serif !important;
	font-size: 36px !important;
	font-weight: 800 !important;
	line-height: 1.15 !important;
	color: #000 !important;
	letter-spacing: 0.01em;
	white-space: nowrap;
	vertical-align: bottom !important;
	padding: 4px 6px 6px 2px !important;
}
/* Name / Naap values sit ABOVE the underline (Nastaliq was cutting through the rule) */
.tp-field-uline {
	border-bottom: 1.5px solid #000 !important;
	padding: 0 8px 10px 4px !important;
	vertical-align: bottom !important;
	line-height: 1.85 !important;
	font-size: 22px !important;
	font-weight: 700 !important;
	overflow: visible !important;
	color: #000 !important;
	visibility: visible !important;
}
.tp-cutting-name {
	font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
	font-size: 22px !important;
	font-weight: 700 !important;
	color: #000 !important;
	visibility: visible !important;
	opacity: 1 !important;
	max-width: none !important;
	white-space: normal !important;
	word-break: break-word;
	overflow: visible !important;
}
.tp-field-label {
	vertical-align: bottom !important;
	padding-bottom: 10px !important;
	white-space: nowrap;
}
.tp-note-label,
.tp-note-label b {
	flex: 0 0 auto !important;
	white-space: nowrap !important;
	font-size: 18px !important;
	font-weight: 700 !important;
	line-height: 1.6 !important;
	padding-bottom: 4px !important;
}
/* نوٹ لائن — flex row so label never overlaps value (was tp-meas-num 36px) */
.tp-note-row {
	display: flex !important;
	flex-direction: row !important;
	flex-wrap: nowrap !important;
	align-items: flex-end !important;
	justify-content: flex-start !important;
	width: 100% !important;
	direction: rtl !important;
	gap: 10px !important;
	box-sizing: border-box !important;
}
.tp-note-uline {
	flex: 1 1 auto !important;
	display: block !important;
	min-width: 0 !important;
	width: auto !important;
	max-width: none !important;
	border-bottom: 1.5px solid #000 !important;
	padding: 0 8px 4px 8px !important;
	box-sizing: border-box !important;
	text-align: right !important;
	vertical-align: bottom !important;
	font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
	font-size: 20px !important;
	font-weight: 800 !important;
	line-height: 1.7 !important;
	color: #000 !important;
	white-space: normal !important;
	word-break: break-word !important;
	overflow: visible !important;
}
/*
 * Waistcoat / Sherwani / Coat: نوٹ لائن neeche + lamba underline.
 * Do NOT widen the float:right block — that pushes measurements off the right edge.
 * Underline is absolutely stretched left from the right-side label.
 */
.tp-note-row--long {
	margin-top: 28px !important;
	width: auto !important;
	max-width: none !important;
	position: relative !important;
	overflow: visible !important;
	min-height: 2em !important;
}
.tp-note-row--long .tp-note-uline {
	flex: 0 0 auto !important;
	position: absolute !important;
	top: 0 !important;
	right: 110px !important; /* leave room for (نوٹ لائن) label on the right */
	left: auto !important;
	width: 560px !important;
	min-width: 560px !important;
	max-width: none !important;
	border-bottom: 1.5px solid #000 !important;
	box-sizing: border-box !important;
}
@media print {
	.tp-order-no {
		font-size: 26px !important;
		font-weight: 800 !important;
		color: #000 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.tp-header-bar {
		padding: 6px 18px 0 8px !important;
		margin: 20px 0 8px 0 !important;
	}
	.tp-design-line,
	td.tp-design-line {
		font-weight: 800 !important;
		font-size: 22px !important;
		color: #000 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.tp-header-left {
		margin-left: 80px !important;
	}
	.tp-h-uline.tp-cutting-name,
	.tp-h-uline.tp-h-naap-val {
		flex: 1 1 0 !important;
		width: auto !important;
		min-width: 140px !important;
		max-width: none !important;
	}
	.tp-note-row {
		direction: rtl !important;
	}
	.tp-note-uline {
		flex: 1 1 auto !important;
		width: auto !important;
		min-width: 0 !important;
		max-width: none !important;
		font-size: 20px !important;
		font-weight: 800 !important;
	}
	.tp-note-row--long {
		margin-top: 28px !important;
		width: auto !important;
		overflow: visible !important;
	}
	.tp-note-row--long .tp-note-uline {
		position: absolute !important;
		right: 110px !important;
		width: 560px !important;
		min-width: 560px !important;
	}
	.tp-slip {
		overflow: visible !important;
	}
	.tp-h-uline {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.tp-meas-num,
	td.tp-meas-num.font18 {
		font-size: 36px !important;
		font-weight: 800 !important;
		color: #000 !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	.tp-field-uline,
	.tp-cutting-name {
		color: #000 !important;
		visibility: visible !important;
		opacity: 1 !important;
		overflow: visible !important;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}
	/* Keep measurement-body underlines readable; header uses tighter rules above */
	.tp-slip .tp-meas-num.tp-field-uline,
	td.tp-meas-num.font18 {
		padding-bottom: 6px !important;
	}
}
</style>
<?php
	ob_Start();
 
 	$this->load->view('Head');
	//$this->load->view('Header');
 
  
?>
<head>
    <!-- Required meta tags-->
  

    <!-- Title Page-->
    <title> </title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/jquery.dataTables.css">
	  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:300i" rel="stylesheet">
    <!-- Fontfaces CSS-->
   <style media="print">
	.print{
			display:none;
	}
   @page {
  size: landscape;
  margin: 12mm 10mm 10mm 12mm;
}
	body.erp-app,
	.page-wrapper,
	.container-fluid {
		margin: 0 !important;
		padding: 0 !important;
	}
	.tp-slip {
		margin-top: 20px !important;
		margin-left: 22px !important;
		margin-right: 14px !important;
		padding-top: 20px !important;
		padding-left: 6px !important;
	}
   </style>
   <style>
  
  
	.urd{
			font-weight:bold;
			font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
	}
 
	.ur {
		font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', serif !important;
	}
	.ur.urd.font18,
	.ur.urd.font19,
	td.ur.urd {
		font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu Draft', serif !important;
		font-weight: bold;
	}
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

</head>
	<script type="text/javascript" language="javascript" class="init">
	
$(document).ready(function() {
	$('#example').DataTable( {
		"pagingType": "full_numbers"
	} );
} );

	</script>
<body class="erp-app dt-example">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Customer/process'); ?>" method="post">
            <!-- MAIN CONTENT-->

            <div class=" ">
                <div  >
                    <div class="container-fluid">
						      <div class="row">
							  	<div class="col">
									 
									<?php
									$print_back = strtoupper(trim((string) $this->uri->segment(6)));
									if ( ! in_array($print_back, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
									{
										$print_back = strtoupper(trim((string) $this->uri->segment(4)));
									}
									if ( ! in_array($print_back, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
									{
										$print_back = 'HO';
									}
									?>
									<a  href="<?php echo site_url('Transfer/transfer_table/'.$print_back.'/'.$print_back); ?>"   class="btn btn-lg btn-info btn-block print" ><?php echo t('app_back'); ?> </a>
                                      </div> 
												<div class="col">
									<input type="button" value="Print" class="btn btn-lg btn-info btn-block print" onclick="window.print()">
									</div>
									
                                      </div> 
					 			
                     	<div class="col-md-11 tp-slip">
                                	  	<?php
			$orders = NULL;
			if ( ! empty($order) && is_array($order))
			{
				$orders = reset($order);
			}
			// Prefer controller-passed id so a floated leftover / wrong join never shows another order.
			$slip_order_no = (isset($print_order_no) && (int) $print_order_no > 0) ? (int) $print_order_no : 0;
			if ($slip_order_no < 1 && $orders)
			{
				if (isset($orders->ORDER) && (int) $orders->ORDER > 0)
				{
					$slip_order_no = (int) $orders->ORDER;
				}
				elseif (isset($orders->order_id) && (int) $orders->order_id > 0)
				{
					$slip_order_no = (int) $orders->order_id;
				}
			}
						                    ?>
                                   <!-- <div class="card-header">Employee Form</div> -->
                                 
									<section>
			<?php
			$employee_name = '';
			if ($orders)
			{
				if (isset($orders->cutting_master_name) && trim((string) $orders->cutting_master_name) !== '')
				{
					$employee_name = trim((string) $orders->cutting_master_name);
				}
				else
				{
					$emp_id = 0;
					if (isset($orders->transfer_empno))
					{
						$emp_id = (int) $orders->transfer_empno;
					}
					elseif (isset($orders->empno))
					{
						$emp_id = (int) $orders->empno;
					}
					if ($emp_id > 0 && ! empty($employee) && is_array($employee))
					{
						foreach ($employee as $employees)
						{
							if ((int) $employees->empno === $emp_id)
							{
								$employee_name = trim((string) $employees->name);
								break;
							}
						}
					}
				}
			}
			$naap_no = ($orders && isset($orders->man_napno)) ? (string) $orders->man_napno : '';
			?>
			<div class="tp-header-bar">
				<div class="tp-header-left">
					<span class="tp-h-uline"><span class="tp-order-no"><?php echo esc_html((string) $slip_order_no); ?></span></span>
					<span class="ur urd tp-h-lbl">آرڈر نمبر</span>
				</div>
				<?php /* DOM RTL order → visual: [ناپ#] ناپ نمبر [نام] نام — same as old ERP */ ?>
				<div class="tp-header-right">
					<span class="ur urd tp-h-lbl tp-h-lbl-name">نام</span>
					<span class="tp-h-uline tp-cutting-name"><?php echo esc_html($employee_name); ?></span>
					<span class="ur urd tp-h-lbl tp-h-lbl-naap">ناپ نمبر</span>
					<span class="tp-h-uline tp-h-naap-val"><?php echo esc_html($naap_no); ?></span>
				</div>
			</div>
  
				  		<div   class="row col-md-12"> 					
						<div class="cols col-md-12">
			 		
		<?php 
										$SR = 0;
									
			 foreach($order as $orders){
									 $SR = $SR + 1;
								if( $SR == 7)
								{
									echo '<span class="page-break"></span>';
								}
						                    ?>
											 <table   style="width:100%" >
						<tr>
							
						
								  
										 
						<td align="right" width="400"  style="border-bottom:1px dotted grey;"  class="ur urd font19 tp-design-line">
						
						 
							<?php 
						
								echo '  &nbsp;'. $orders->order_remarks. '&nbsp;' ;
							//	foreach($suit as $suits){  
							
							foreach($suit as $suits){
								  if($suits->SNO == $orders->SNO)
									echo '&nbsp; ' . $suits->name . '&nbsp;'; 
					 		 }
							 $garment_note = measurement_garment_note_for_type($orders, isset($type) ? $type : '');
							 if ($garment_note !== '')
							 {
								 echo esc_html($garment_note);
							 }
							 ?>   
							 </td>
					
								<td align="right" width="2%" style="border-bottom:1px dotted grey;">
							<?php  echo "<b>-</b>".$SR.""; ?>
							</td>
						</tr>
				</table>
				
					 <table   style="width:100%" >
						<tr>
										<td align="left" width="5%" style="border-bottom:1px dotted grey; ">
							 <b><?PHP //echo $orders->suitrate + $orders->drate + $orders->erate + $orders->addprice;   ?></b>
							</td>
						
						 
							<td style="border-bottom:1px dotted grey; " width="50"><img style="height: 32px;
    max-width: 64px;"src="<?php  echo base_url().'images/'.$orders->kolor_style.'.jpg'; ?>"
							
							></td>
						
								<?php
								// Pockets only for قمیض شلوار / پاجامہ — never on ویسکوٹ / کوٹ / شرٹ / پینٹ etc.
								$show_pockets = in_array((string) $type, array('Kurta', 'Pajama'), TRUE);
								if ($show_pockets) {
								?>
								<td align="right" width="250"  style="border-bottom:1px dotted grey;" class="ur urd font18">
							<?php   
								
				 	if($orders->Shawlarpocket == ''){ echo '&nbsp; (شلوار پاکٹ X)'; } else{ echo  '&nbsp; (شلوار پاکٹ'.' ' .$orders->Shawlarpocket.')&nbsp;' ; } 
							?>
							
							</td>
										<td align="right"  width="150"  style="border-bottom:1px dotted grey;" class="ur urd font18">
							<?php   
			 	if($orders->spocket == ''){ echo '&nbsp; (سائیڈ  پاکٹ X )'; }else { echo '(&nbsp; سائیڈ  پاکٹ '.$orders->spocket. ')&nbsp; '; }
							?>
							
							</td>
										<td align="right"  width="200" style="border-bottom:1px dotted grey;" class="ur urd font18">
							<?php   
			 	if($orders->fpocket == ''){ echo '&nbsp; ( فرنٹ پاکٹ X )'; }else { echo '(&nbsp; فرنٹ پاکٹ  '.$orders->fpocket. ')&nbsp; '; } 
							?>
							
							</td>
								<?php } else { ?>
								<td align="right" colspan="3" style="border-bottom:1px dotted grey;">&nbsp;</td>
								<?php } ?>
							
			 
					
							 
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
			<div class="cols col-md-12">
			<div class="cols col-md-12">
		 <div style="float:right; text-align:right;" class="font18 tp-meas-block">
			 		 
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
					elseif($type == 'Coat' || $type == 'Sherwani')	
					echo '<b>شروانی/کوٹ</b>';
					elseif($type == 'PentCoat')
					 echo '<b>پینٹ کوٹ</b>';
					elseif($type == 'Waistcoat')
					 echo '<b>ویسکوٹ</b>';
					else
					 echo '<b>'.esc_html((string) $type).'</b>';
					 echo"</u></div>";
					echo '<hr>';
					
					
			  if($type == 'Kurta')
			  {
						                    ?>
			
 <table   style="font-size:18px;     float: left;
    margin-right: 50px; width:350px;"  >					
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->FPL; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>فرنٹ پٹی    : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" class="tp-meas-num font18" align="right"><?php  echo $orders->BP; ?></td>
									<td align="right" width="120"  class="font18" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>باذو پٹی    : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->CAKLAMBAI; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>چاک لمبائی     : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->CL; ?></td>
									<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کف لمبائی     : </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->KAAFCURAI; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کف چوڑائی      : </b>  
							 
							</td>
									</tr>	
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->MODA_CURAI; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>موڈاچوڑائی     : </b>  
							 
							</td>
						</tr>
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Poncha_Chorai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ چوڑائی      : </b>  
							 
							</td>
									</tr>	
			<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->WASIT; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کمر (ویسٹ)     : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->FRONT_Pocket_Size; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کہنی      : </b>  
							 
							</td>
									</tr>	
									<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->BG; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>باذو گول       : </b> 
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Side_Pocket_Size; ?></td>
									<td align="right" width="150" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>ہیپ     : </b>  
							 
							</td>
			  </tr>
			<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php echo isset($orders->CKC) ? esc_html((string) $orders->CKC) : ''; ?></td>
									<td align="right" width="150" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پینٹ کوٹ     : </b>
							</td>
			  </tr>
		
				
			  
			  <tr>
				<td> </td> 
			  </tr>
		</table>
		<?php
		$geera_parts = measurement_split_triple(isset($orders->geera) ? $orders->geera : '');
		$geera_upar = $geera_parts[0];
		$geera_darmiyan = $geera_parts[1];
		$geera_neeche = $geera_parts[2];
		$sgeera_parts = measurement_split_triple(isset($orders->SGeera) ? $orders->SGeera : '');
		$sgeera_upar = $sgeera_parts[0];
		$sgeera_darmiyan = $sgeera_parts[1];
		$sgeera_neeche = $sgeera_parts[2];
		?>
		<table   style="font-size:18px; "  >
										<tr>
							<td style="border-bottom: 1px solid black;" width="70" align="right" class="tp-meas-num font18"><?php  echo $orders->KL; ?></td>
									<td align="right" width="200" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>قمیض لمبائی : </b> 
							 
							</td>
						</tr>
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="70" align="right" class="tp-meas-num font18"><?php  echo $orders->BL; ?></td>
						<td align="right" width="200" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>باذو  : </b>  
							 
							</td>
						</tr>
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Teera; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>تیرا  : </b>  
							 
							</td>
						</tr>	
								<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php echo esc_html($geera_neeche); ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گیرا نیچے : </b>
							 
							</td>
						 
					</tr>
								<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php echo esc_html($geera_darmiyan); ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گیرا درمیان : </b>
							 
							</td>
						 
					</tr>
								<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php echo esc_html($geera_upar); ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گیرا اوپر : </b>
							 
							</td>
						 
					</tr>
					<tr>			
						
							<td  style="border-bottom: 1px solid black;" align="right" class="tp-meas-num"><?php  echo $orders->Chaati; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>چھاتی   : </b> 
							 
							</td>
						</tr>	
				<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->pait; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پیٹ    : </b>  
							 
							</td>
					</tr>
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Gala; ?></td>
									<td align="right" width="120"style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گلا   : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Shalwar_Lenght; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;"class="font18">
							   &nbsp; &nbsp; <b>شلوار لمبائی    : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Poncha_Choraic; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ   : </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->SC; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>شلوار پھیر    :  
							 
							</td>
								</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="tp-meas-num font18"><?php  echo $orders->Asan; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>آسن    : </b> 
							 
							</td>
						</tr>	
					</table>	
			  <?php  }elseif($type == 'Pajama')
			  {
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="tp-meas-num font18"><?php  echo $orders->PLambai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right" class="tp-meas-num font18"><?php  echo $orders->PPoncha; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="tp-meas-num font18"><?php  echo $orders->PAsan; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>آسن  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right" class="tp-meas-num font18"><?php  echo $orders->PPeeir; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پپھیر : </b> 
							 
							</td>
						</tr>			
			  
			  <?php  }elseif($type == 'Pent')
			  {
				$tpick = function () use ($orders) {
					$keys = func_get_args();
					$fallback = '';
					foreach ($keys as $key)
					{
						$val = isset($orders->$key) ? trim((string) $orders->$key) : '';
						if ($fallback === '') { $fallback = $val; }
						if ($val !== '') { return $val; }
					}
					return $fallback;
				};
				$pent_rows = array(
					array($tpick('PentLambai'), 'لمبائی'),
					array($tpick('pentWaiste'), 'کمر'),
					array($tpick('HIP'), 'ہپ'),
					array($tpick('PentPat', 'PentThai'), 'پٹ'),
					array($tpick('PentGhutna'), 'گھٹنہ'),
					array($tpick('PentPonchaMori', 'PentPoncha'), 'پانچہ موری'),
				);
			  ?>
			  <table   style="font-size:18px;"  >
			  <?php foreach ($pent_rows as $pr) { ?>
			  <tr>
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="tp-meas-num font18"><?php echo esc_html($pr[0]); ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b><?php echo esc_html($pr[1]); ?>  : </b>
							</td>
							<td colspan="3">&nbsp;</td>
						</tr>
			  <?php } ?>
			 <?php  }elseif($type == 'Shirt')
			  {
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php  echo $orders->SLambai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="tp-meas-num font18" width="35" align="right"><?php  echo $orders->SBazoo; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>باذو : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php  echo $orders->STeera; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>تیرا  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="tp-meas-num font18" width="35" align="right"><?php echo esc_html($sgeera_neeche); ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>گیرا نیچے : </b>
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php echo esc_html($sgeera_darmiyan); ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>گیرا درمیان : </b>
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="tp-meas-num font18" width="35" align="right"><?php echo esc_html($sgeera_upar); ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>گیرا اوپر : </b>
							 
							</td>
						</tr>
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php  echo $orders->SChaati; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>چھاتی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="tp-meas-num font18" width="35" align="right"><?php  echo $orders->SKamar; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> کمر : </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"> </td>
						<td align="right" width="120"  class="font18">
							   
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;"  class="tp-meas-num font18" width="35" align="right"><?php  echo $orders->SGaala; ?></td>
									<td align="right" width="100"  class="font18">
							   &nbsp; &nbsp; <b> گلا : </b> 
							 
							</td>
						</tr>	
						 <?php  }elseif($type == 'Coat' || $type == 'Sherwani')
			  {
				$tpick = function () use ($orders) {
					$keys = func_get_args();
					$fallback = '';
					foreach ($keys as $key)
					{
						$val = isset($orders->$key) ? trim((string) $orders->$key) : '';
						if ($fallback === '') { $fallback = $val; }
						if ($val !== '') { return $val; }
					}
					return $fallback;
				};
				$coat_rows = array(
					array($tpick('CoatLambai', 'SherwaniLambai'), 'لمبائی'),
					array($tpick('CoatChaati', 'SherwaniChaati'), 'چھاتی'),
					array($tpick('CoatKamar', 'SherwaniKamar'), 'کمر'),
					array($tpick('CoatTeera', 'SherwaniTeera'), 'تیرا'),
					array($tpick('CoatBazoo', 'SherwaniBazoo'), 'بازو'),
					array($tpick('CoatGaala', 'SherwaniGaala'), 'گلا'),
					array($tpick('CoatHIP', 'SherwaniHIP'), 'ہپ'),
					array($tpick('CoatDola', 'SherwaniDola'), 'ڈولا'),
					array($tpick('CoatBazooMori', 'SherwaniBazooMori'), 'بازو موری'),
					array($tpick('crossback', 'SherwaniCrossback'), 'کراس بیک'),
					array($tpick('CoatLambaiBack', 'SherwaniLambaiBack'), 'لمبائی بیک'),
				);
			  ?>
			  <table   style="font-size:18px;"  >
			  <?php foreach ($coat_rows as $cr) { ?>
			  <tr>
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php echo esc_html($cr[0]); ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b><?php echo esc_html($cr[1]); ?>  : </b>
							</td>
							<td colspan="3">&nbsp;</td>
						</tr>
			  <?php } ?>
						 <?php  }elseif($type == 'PentCoat')
			  {
				$tpick = function () use ($orders) {
					$keys = func_get_args();
					$fallback = '';
					foreach ($keys as $key)
					{
						$val = isset($orders->$key) ? trim((string) $orders->$key) : '';
						if ($fallback === '') { $fallback = $val; }
						if ($val !== '') { return $val; }
					}
					return $fallback;
				};
				$pc_rows = array(
					array($tpick('PCCoatLambai', 'CoatLambai'), 'کوٹ لمبائی'),
					array($tpick('PCCoatChaati', 'CoatChaati'), 'کوٹ چھاتی'),
					array($tpick('PCCoatKamar', 'CoatKamar'), 'کوٹ کمر'),
					array($tpick('PCCoatTeera', 'CoatTeera'), 'کوٹ تیرا'),
					array($tpick('PCCoatBazoo', 'CoatBazoo'), 'کوٹ بازو'),
					array($tpick('PCCoatGaala', 'CoatGaala'), 'کوٹ گلا'),
					array($tpick('PCCoatHIP', 'PCHIP', 'CoatHIP'), 'کوٹ ہپ'),
					array($tpick('PCCoatDola', 'CoatDola'), 'کوٹ ڈولا'),
					array($tpick('PCCrossback', 'crossback'), 'کراس بیک'),
					array($tpick('PCPentLambai', 'PentLambai'), 'پینٹ لمبائی'),
					array($tpick('PCpentWaiste', 'pentWaiste'), 'پینٹ کمر'),
					array($tpick('PCHIP', 'HIP'), 'پینٹ ہپ'),
					array($tpick('PCPentPat', 'PCPentThai', 'PentPat', 'PentThai'), 'پینٹ پٹ'),
					array($tpick('PCPentGhutna', 'PentGhutna'), 'پینٹ گھٹنہ'),
					array($tpick('PCPentPonchaMori', 'PCPentPoncha', 'PentPonchaMori', 'PentPoncha'), 'پینٹ پانچہ'),
				);
			  ?>
			  <table   style="font-size:18px;"  >
			  <?php foreach ($pc_rows as $cr) { ?>
			  <tr>
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php echo esc_html($cr[0]); ?></td>
						<td align="right" width="160"  class="font18">
							   &nbsp; &nbsp; <b><?php echo esc_html($cr[1]); ?>  : </b>
							</td>
							<td colspan="3">&nbsp;</td>
						</tr>
			  <?php } ?>
						 <?php  }elseif($type == 'Waistcoat')
			  {
				$wast_rows = array(
					array('wastLambai', 'لمبائی'),
					array('wastChaati', 'چھاتی'),
					array('wastKamar', 'کمر'),
					array('wastTeera', 'تیرا'),
					array('wastGaala', 'گلا'),
					array('wastHIP', 'ہپ'),
				);
			  ?>
			  <table   style="font-size:18px;"  >
			  <?php foreach ($wast_rows as $wr) {
				$wk = $wr[0];
				$wl = $wr[1];
				$wv = isset($orders->$wk) ? $orders->$wk : '';
			  ?>
			  <tr>
						 <td style="border-bottom: 1px solid black;" width="35" class="tp-meas-num font18" align="right"><?php echo esc_html($wv); ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b><?php echo esc_html($wl); ?>  : </b>
							</td>
							<td colspan="3">&nbsp;</td>
						</tr>
			  <?php } ?>
						
			  <?php }  ?>
			  
			
			 </table>
<table style="width: 100%;">
					  <tr>
							<td style="padding: 0;">
								<?php
								$tp_note_long_types = array('Waistcoat', 'Coat', 'Sherwani');
								$tp_note_long = isset($type) && in_array((string) $type, $tp_note_long_types, TRUE);
								?>
								<div class="tp-note-row<?php echo $tp_note_long ? ' tp-note-row--long' : ''; ?>">
									<span class="font18 tp-note-label"><b>(نوٹ لائن) :</b></span>
									<span class="tp-note-uline"><?php
									$note_line = measurement_garment_note_for_type($orders, isset($type) ? $type : '');
									echo esc_html($note_line);
									?></span>
								</div>
							</td>
						</tr>
					</table>
					
					</div>
					</div>

					</div>
					
	</section>
                      </form>
 <TABLE  width="600">
 		<tr height="50">
		<td width="250"  ></td>
		<td> </td>
	</tr>
	<tr>
		<td width="250" style="border-bottom:1px dotted black;"></td>
		<td>دستخط</td>
	</tr>
		<tr height="100">
		<td width="250" ></td>
		<td> </td>
	</tr>
 </TABLE>
 
                     
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>
    <!-- Jquery JS-->
    
    <!-- Bootstrap JS-->

 
    
    
    </div>

 
</body>

</html>
<!-- end document-->
