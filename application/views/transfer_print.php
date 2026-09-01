<style type="text/css">
.font18{
	font-size:20px !important;
}
.font19{
	font-size:24px !important;
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
}
   </style>
   <style>
  
  
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
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
      <form name="Form" action="<?php echo site_url('Customer/process'); ?>" method="post">
            <!-- MAIN CONTENT-->

            <div class=" ">
                <div  >
                    <div class="container-fluid">
						      <div class="row">
							  	<div class="col">
									 
									<a  href="<?php echo site_url('Transfer/transfer_table/'.$this->uri->segment(3).'/'.$this->uri->segment(4).''); ?>"   class="btn btn-lg btn-info btn-block print" ><?php echo t('app_back'); ?> </a>
                                      </div> 
												<div class="col">
									<input type="button" value="Print" class="btn btn-lg btn-info btn-block print" onclick="window.print()">
									</div>
									
                                      </div> 
					 			
                     	<div class="col-md-11">
                                	  	<?php 
			 foreach($order as $orders){
									 
			 }
						                    ?>
                                   <!-- <div class="card-header">Employee Form</div> -->
                                 
									<section>
 				
			<table class="col-md-11" >
					<tr>
					 
								<td><span style="">
								<b style=" margin-top:2px; float:left"><?php  echo $orders->ORDER; ?></b>
								
								</span></td>
			 
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
							
							<td align="right" colspan="2" class="font18" style="border-bottom:1px dotted black;" class="ur urd"><?php  echo $orders->man_napno; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60" class="ur urd">ناپ نمبر</th>
							<?php
														$employee_name = '';
			 foreach($employee as $employees){
							if($employees->empno == $orders->empno)
							{
								 $employee_name = $employees->name;
							}								
			 }
						                    ?>
						 
							
							<td align="right" width="400" class="ur urd" style="border-bottom:1px dotted black;"><?php  echo $employee_name; ?>&nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25" class="ur urd">نام</th>
					</tr>
						<tr>
							
							<td align="right" colspan="2"  class="ur urd"> &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60"> </th>
							<td align="right" width="400"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25"> </th>
					</tr>
							 
						<tr>
							
							<td align="right" colspan="2"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="60"> </th>
							<td align="right" width="400"  > &nbsp;&nbsp;&nbsp;</td>
							<th align="right" width="25"> </th>
					</tr>
					</table>
  
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
							
						
								  
										 
						<td align="right" width="400"  style="border-bottom:1px dotted grey;"  class="ur urd font19">
						
						 
							<?php 
						
								echo '  &nbsp;'. $orders->order_remarks. '&nbsp;' ;
							//	foreach($suit as $suits){  
							
							foreach($suit as $suits){
								  if($suits->SNO == $orders->SNO)
									echo '&nbsp; ' . $suits->name . '&nbsp;'; 
					 		 }	 
							 echo  $orders->MREMARKS;
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
						
								<td align="right" width="250"  style="border-bottom:1px dotted grey;" class="ur urd font18">
							<?php   
								
				 	if($orders->Shawlarpocket == ''){ echo '&nbsp; (شلوار پاکٹ X)'; } else{ echo  '&nbsp; (شلوار پاکٹ'.' ' .$orders->Shawlarpocket.')&nbsp;' ; } 
				
				
			 //if($orders->Shawlarpocket != '') { echo 'سائیڈ  پاکٹ '.$orders->Shawlarpocket.','; }
							?>
							
							</td>
										<td align="right"  width="150"  style="border-bottom:1px dotted grey;" class="ur urd font18">
							<?php   
								 
					 		 
			 	if($orders->spocket == ''){ echo '&nbsp; (سائیڈ  پاکٹ X )'; }else { echo '(&nbsp; سائیڈ  پاکٹ '.$orders->spocket. ')&nbsp; '; }
		 	
				
			 //if($orders->Shawlarpocket != '') { echo 'سائیڈ  پاکٹ '.$orders->Shawlarpocket.','; }
							?>
							
							</td>
										<td align="right"  width="200" style="border-bottom:1px dotted grey;" class="ur urd font18">
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
			<div class="cols col-md-12">
			<div class="cols col-md-12">
		 <div style="float:right" class="font18">
			 		 
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
			
 <table   style="font-size:18px;     float: left;
    margin-right: 50px; width:350px;"  >					
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->FPL; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>فرنٹ پٹی    : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" class="font18" align="right"><?php  echo $orders->BP; ?></td>
									<td align="right" width="120"  class="font18" style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>باذو پٹی    : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->CAKLAMBAI; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>چاک لمبائی     : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->CL; ?></td>
									<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کف لمبائی     : </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->KAAFCURAI; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کف چوڑائی      : </b>  
							 
							</td>
									</tr>	
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->MODA_CURAI; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>موڈاچوڑائی     : </b>  
							 
							</td>
						</tr>
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Poncha_Chorai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ چوڑائی      : </b>  
							 
							</td>
									</tr>	
			<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->WASIT; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کمر (ویسٹ)     : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->FRONT_Pocket_Size; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>کہنی      : </b>  
							 
							</td>
									</tr>	
									<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->BG; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>باذو گول       : </b> 
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Side_Pocket_Size; ?></td>
									<td align="right" width="150" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>ہیپ     : </b>  
							 
							</td>
			  </tr>	
		
				
			  
			  <tr>
				<td> </td> 
			  </tr>
		</table>
		<table   style="font-size:18px; "  >
										<tr>
							<td style="border-bottom: 1px solid black;" width="70" align="right" class="font18"><?php  echo $orders->KL; ?></td>
									<td align="right" width="200" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>قمیض لمبائی : </b> 
							 
							</td>
						</tr>
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="70" align="right" class="font18"><?php  echo $orders->BL; ?></td>
						<td align="right" width="200" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>باذو  : </b>  
							 
							</td>
						</tr>
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Teera; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>تیرا  : </b>  
							 
							</td>
						</tr>	
								<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->geera; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گیرا   : </b>  
							 
							</td>
						 
					</tr>
					<tr>			
						
							<td style="border-bottom: 1px solid black;" align="right"><?php  echo $orders->Chaati; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>چھاتی   : </b> 
							 
							</td>
						</tr>	
				<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->pait; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پیٹ    : </b>  
							 
							</td>
					</tr>
					<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Gala; ?></td>
									<td align="right" width="120"style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>گلا   : </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Shalwar_Lenght; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;"class="font18">
							   &nbsp; &nbsp; <b>شلوار لمبائی    : </b>  
							 
							</td>
									</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Poncha_Choraic; ?></td>
									<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ   : </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->SC; ?></td>
						<td align="right" width="120"  style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>شلوار پھیر    :  
							 
							</td>
								</tr>	
				<tr>
							<td style="border-bottom: 1px solid black;" align="right" class="font18"><?php  echo $orders->Asan; ?></td>
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
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->PLambai; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->PPoncha; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پانچہ : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->PAsan; ?></td>
						<td align="right" width="120" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>آسن  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->PPeeir; ?></td>
									<td align="right" width="100" style="border-bottom: 1px solid black;" class="font18">
							   &nbsp; &nbsp; <b>پپھیر : </b> 
							 
							</td>
						</tr>			
			  
			  <?php  }elseif($type == 'Pent')
			  {
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->PentLambai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" width="35" align="right" class="font18"><?php  echo $orders->pentWaiste; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>کمر : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->HIP; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>HIP  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;"  class="font18" width="35" align="right"><?php  echo $orders->PentPoncha; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> پانچہ : </b> 
							 
							</td>
						</tr>		
					  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->PentThai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>Thai
  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"> </td>
									<td align="right" width="100" class="font18">
							   
							 
							</td>
						</tr>
			 <?php  }elseif($type == 'Shirt')
			  {
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->SLambai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->SBazoo; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>باذو : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->STeera; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>تیرا  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->SGeera; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> گیرا : </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->SChaati; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>چھاتی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->SKamar; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> کمر : </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"> </td>
						<td align="right" width="120"  class="font18">
							   
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;"  class="font18" width="35" align="right"><?php  echo $orders->SGaala; ?></td>
									<td align="right" width="100"  class="font18">
							   &nbsp; &nbsp; <b> گلا : </b> 
							 
							</td>
						</tr>	
						 <?php  }elseif($type == 'Coat')
			  {
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->CoatLambai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->CoatBazoo; ?></td>
									<td align="right" width="100" class="font18" >
							   &nbsp; &nbsp; <b>باذو : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->CoatTeera; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>تیرا  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->CoatChaati; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> چھاتی : </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->CoatHIP; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>HIP  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->CoatKamar; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b> کمر : </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->crossback; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>Cross Back
  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->CoatGaala; ?></td>
									<td align="right" width="100"class="font18" >
							   &nbsp; &nbsp; <b> گلا : </b> 
							 
							</td>
						</tr>		
						 <?php  }elseif($type == 'Waistcoat')
			  {
				  
			  ?>
			  <table   style="font-size:18px;"  >
			  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->wastLambai; ?></td>
						<td align="right" width="120"  class="font18">
							   &nbsp; &nbsp; <b>لمبائی  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->wastTeera; ?></td>
									<td align="right" width="100" class="font18">
							   &nbsp; &nbsp; <b>تیرا : </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->wastHIP; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>HIP 
  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->wastChaati; ?></td>
									<td align="right" width="100"class="font18" >
							   &nbsp; &nbsp; <b> چھاتی : </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"><?php  echo $orders->CoatHIP; ?></td>
						<td align="right" width="120" class="font18" >
							   &nbsp; &nbsp; <b>HIP  : </b>  
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;" class="font18" width="35" align="right"><?php  echo $orders->wastKamar; ?></td>
									<td align="right" width="100" class="font18" >
							   &nbsp; &nbsp; <b> کمر : </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td style="border-bottom: 1px solid black;" width="35" class="font18" align="right"> </td>
						<td align="right" width="120"  class="font18" >
							   
							 
							</td>
							<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
							<td style="border-bottom: 1px solid black;"  class="font18"width="35" align="right"><?php  echo $orders->wastGaala; ?></td>
									<td align="right" width="100" class="font18" >
							   &nbsp; &nbsp; <b> گلا : </b> 
							 
							</td>
						</tr>	
						
			  <?php }  ?>
			  
			
			 </table>
<table  style="width: 100%;"   >			 
			  
					  <tr>
		 
						<td style="border-bottom: 1px solid black;"   align="right"> </td>
						 
							 
							<td style="border-bottom: 1px solid black;"  class="font18"  align="right"><?php  echo $orders->MREMARKS; ?></td>
								<td align="right"   class="font18" style="border-bottom: 1px solid black; width: 94px;">
							   &nbsp; &nbsp; <b> نوٹ لائن : </b> 
							 
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
<Script>
 
</script>

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
   
   </style>
   <style>
  
  
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
