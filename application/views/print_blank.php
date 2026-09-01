
<?php
	$business_config = isset($business_config) && is_array($business_config) ? $business_config : array();
	$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';
	$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';

	if (!isset($order) || empty($order))
	{
		$order = array((object) array(
			'TRNNO' => '', 'man_napno' => '', 'name' => '', 'RDATE' => '', 'ORDERDATE' => '',
			'total' => 0, 'advance' => 0, 'discount' => 0, 'qty' => '',
			'BL' => '', 'KL' => '', 'geera' => '', 'Teera' => '', 'pait' => '', 'Chaati' => '',
			'Shalwar_Lenght' => '', 'Gala' => '', 'SC' => '', 'Poncha_Choraic' => '', 'FPL' => '',
			'Asan' => '', 'CAKLAMBAI' => '', 'BP' => '', 'KAAFCURAI' => '', 'CL' => '',
			'Poncha_Chorai' => '', 'MODA_CURAI' => '', 'FRONT_Pocket_Size' => '', 'WASIT' => '',
			'Side_Pocket_Size' => '', 'BG' => '', 'PLambai' => '', 'PPoncha' => '', 'PAsan' => '',
			'PPeeir' => '', 'PentLambai' => '', 'pentWaiste' => '', 'HIP' => '', 'PentPoncha' => '',
			'PentThai' => '', 'SLambai' => '', 'SBazoo' => '', 'STeera' => '', 'Geera' => '',
			'SChaati' => '', 'SKamar' => '', 'SGaala' => '', 'CoatLambai' => '', 'CoatBazoo' => '',
			'CoatTeera' => '', 'CoatChaati' => '', 'CoatHIP' => '', 'CoatKamar' => '', 'crossback' => '',
			'CoatGaala' => '', 'wastLambai' => '', 'wastTeera' => '', 'wastHIP' => '', 'wastChaati' => '',
			'wastKamar' => '', 'wastGaala' => '', 'MREMARKS' => '', 'order_remarks' => '', 'SNO' => '',
			'suitrate' => 0, 'drate' => 0, 'erate' => 0, 'addprice' => 0, 'kolor_style' => '',
			'Shawlarpocket' => '', 'spocket' => '', 'fpocket' => '', 'design' => '', 'ENO' => '', 'image' => '',
		));
	}
	if (!isset($type))
	{
		$type = 'Kurta';
	}
	if (!isset($suit))
	{
		$suit = array();
	}
	if (!isset($style))
	{
		$style = array();
	}
	if (!isset($embroidery))
	{
		$embroidery = array();
	}
	if (!isset($SR))
	{
		$SR = 0;
	}

	ob_Start();
 
 	$this->load->view('Head');
	//$this->load->view('Header');
 
  
?>
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
  @media print {
	.page-break	{ display: block; page-break-before: always; }
	
	 @page { margin: 0; }
  body { margin: 5.6cm; }
}
  
	.urd{
			font-weight:bold;
			    font-family: 'Noto Nastaliq Urdu Draft', serif;
				 
	}
 
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', sans serif;;
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
<?php
 

	if($SR > 1)
	{

	echo '<span class="page-break"></span>';
									echo "</br>";
	}
?>
									<div class="page-wrapper top">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Customer/process'); ?>" method="post">
            <!-- MAIN CONTENT-->

            <div class=" ">
                <div  >
                    <div class="container-fluid">
						 
					 			
                     	<div class="col-md-14"  style="float: right;">
                                	  	<?php 
			 foreach($order as $orders){
									 
			 }
						                    ?>
                                   <!-- <div class="card-header">Employee Form</div> -->
                                   <div class="row">
												<div class="col" style="width:70px;">
									
									<a  href="<?php echo site_url('Welcome'); ?>" style="width:80px; "  class="btn btn-lg btn-info btn-block print" ><?php echo t('app_back'); ?> </a>
									</div>
												<div class="col">
									<?php  
											$order_number = $orders->TRNNO;
										?>
									<a  href="<?php echo site_url('Order/Order_edit/'.$order_number.''); ?>" style="width:140px; margin-left: 200px;"  class="btn btn-lg btn-info btn-block print" >Order Edit </a>
									</div>
										<div class="col">
										
									
<input type="button" value="Print" style="width:80px; margin-left: 200px; float:right" class="btn btn-lg btn-info btn-block print" onclick="window.print()">                                     
									 </div> 
                                      </div> 
									<section>
 				
			<table class="col-md-11" >
					<tr>
						<td align="center" width="20%"><img src="<?php echo base_url(); ?>/images/Logo 1.png"  style="height:150px; padding:0px 0px 0px 0px;">
						
								</td>
								<td> </td>
					<td align="center">
					
						<img src="<?php echo base_url(); ?>/images/Logo 3.jpg" class="col-md-9" style="height:150px;     float: right; padding:0px 0px 0px 0px;">
						<span style="border:1px solid black; float:left; border-radius:30px; width:30px; height:30px;     margin-left: 100px;
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
			<table   style="width: 100%;
    line-height: 18px;
    font-family: sans-serif;"   class="col-md-11"  >
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
						$newDate = ($originalDate !== null && $originalDate !== '') ? date('d-m-Y', strtotime($originalDate)) : '';
							echo $newDate; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" class="ur urd" width="160">تاریخ واپسی</th>
							<td align="right" class="ur urd" width="400" style="border-bottom:1px dotted black;"><?php 

$originalDate1 = $orders->ORDERDATE;
						$newDate1 = ($originalDate1 !== null && $originalDate1 !== '') ? date('d-m-Y', strtotime($originalDate1)) : '';
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
    font-family: sans-serif;"   class="col-md-11"  >
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
				  		<div   class="row col-md-12"> 			  
			<div class="cols col-md-4">
		 
			 		 <table     >
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
					 echo"</u></div>";
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
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->HIP; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>HIP   </b>  
							 
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
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->Geera; ?></td>
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
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatHIP; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>HIP   </b>  
							 
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
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastHIP; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>HIP 
   </b>  
							 
							</td>
							<td>&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->wastChaati; ?></td>
									<td align="right" width="100" >
							   &nbsp; &nbsp; <b> چھاتی  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right"><?php  echo $orders->CoatHIP; ?></td>
						<td align="right" width="120"  >
							   &nbsp; &nbsp; <b>HIP  </b>  
							 
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
					<div class="cols col-md-8">
					<div class="cols col-md-14">
			 		
		<?php 
										$SR = 0;
									
			 foreach($order as $orders){
									 $SR = $SR + 1;
								if( $SR == 7)
								{
									echo '<span class="page-break"></span>';
									echo "</br></br></br>";
								}
						                    ?>
											 <table   style="width:100%" >
						<tr>
							
						
								  
										 
						<td align="right" width="400"  style="border-bottom:1px dotted grey;" class="ur urd">
						
						 
							<?php 
						
								echo '  &nbsp;'. $orders->order_remarks. '&nbsp;';
							//	foreach($suit as $suits){  
							
							foreach($suit as $suits){
								  if($suits->SNO == $orders->SNO)
									echo '&nbsp; ' . $suits->name . '&nbsp;'; 
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
							 <b><?PHP echo $orders->suitrate + $orders->drate + $orders->erate + $orders->addprice;   ?></b>
							</td>
						
						 
							<td style="border-bottom:1px dotted grey; " width="50"><img style="height: 32px;
    max-width: 64px;"src="<?php  echo base_url().'images/'.$orders->kolor_style.'.jpg'; ?>"
							
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
					
	</section>
                      </form>
 <TABLE  width="600">
 		<tr height="15">
		<td width="250"  ></td>
		<td> </td>
	</tr>
	<tr>
		
		<td><img src="<?php  echo $orders->image; ?>" style="height: 200px;"></td>
	</tr>
		 
		<td   ></td>
		<td> </td>
	</tr>
 </TABLE>
  <TABLE   style="width: 100%;
    line-height: 18px;
    font-family: sans-serif; background-color: #BBB;"     >
	<tr>
		 <td rowspan="2" align=""center><span style="margin-right:20px; font-weight:bold;   width: 275px;
    float: right;
	font-size: 30px;
    text-align: center">ناغہ بروز جمعتہ المبارک </span></td>
		<td align="right">	<span style="margin-right:20px; font-weight:bold;   width: 350px;
    float: right;
    text-align: center">بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</span></td>
	</tr>
		<tr>
			 
		<td align="right">	<span style="margin-right:20px; font-weight:bold;   width: 275px;
    float: right;
    text-align: center">دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</span></td>
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
