<table width="100%" cellpadding="1" cellspacing="0" class="meas" dir="ltr" style="table-layout:fixed;">
<colgroup>
	<col style="width:14mm">
	<col style="width:32mm">
	<col style="width:3mm">
	<col style="width:14mm">
	<col style="width:32mm">
</colgroup>
<?php
foreach ($order as $orders) { break; }
if ($type == 'Kurta') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">قمیض شلوار</td></tr>';
} elseif ($type == 'Pajama') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">پاجامہ</td></tr>';
} elseif ($type == 'Pent') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">پینٹ</td></tr>';
} elseif ($type == 'Shirt') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">شرٹ</td></tr>';
} elseif ($type == 'Coat') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">شروانی/کوٹ</td></tr>';
} elseif ($type == 'Waistcoat') {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">ویسکوٹ</td></tr>';
}
if ($type == 'Kurta') {
?>
						<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->BL; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>باذو   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->KL; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>قمیض لمبائی  </b> 
							 
							</td>
						</tr>
								<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->geera; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>گیرا    </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Teera; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>تیرا  </b>  
							 
							</td>
						</tr>	
				<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->pait; ?></td>
						<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>پیٹ     </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Chaati; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>چھاتی    </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Shalwar_Lenght; ?></td>
						<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>شلوار لمبائی    </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Gala; ?></td>
									<td align="right" class="m-lbl"style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>گلا    </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SC; ?></td>
						<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>شلوار پھیر</b>
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Poncha_Choraic; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>پانچہ    </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->FPL; ?></td>
						<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>فرنٹ پٹی     </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Asan; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>آسن     </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CAKLAMBAI; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>چاک لمبائی      </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->BP; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>باذو پٹی     </b>  
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->KAAFCURAI; ?></td>
						<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;">
							   &nbsp; &nbsp; <b>کف چوڑائی       </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CL; ?></td>
									<td align="right" class="m-lbl"  style="border-bottom: 1px solid black;" >
							   &nbsp; &nbsp; <b>کف لمبائی      </b> 
							 
							</td>
						</tr>	
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Poncha_Chorai; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>پانچہ چوڑائی       </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->MODA_CURAI; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>موڈاچوڑائی   </b>  
							 
							</td>
						</tr>
			<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->FRONT_Pocket_Size; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>فرنٹ پاکٹ سائز</b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->WASIT; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>کمر (ویسٹ) </b>  
							 
							</td>
						</tr>	
		<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Side_Pocket_Size; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>   سائیڈ پاکٹ سائز </b> 
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->BG; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
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
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PLambai; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PPoncha; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>پانچہ  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PAsan; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>آسن   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PPeeir; ?></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>پپھیر  </b> 
							 
							</td>
						</tr>			
			  
			  <?php  }elseif($type == 'Pent')
			  {
			  ?>
			  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PentLambai; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->pentWaiste; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>کمر  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->ہپ; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>ہپ   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PentPoncha; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> پانچہ  </b> 
							 
							</td>
						</tr>		
					  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->PentThai; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>Thai
  : </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"> </td>
									<td align="right" class="m-lbl" >
							   
							 
							</td>
						</tr>
			 <?php  }elseif($type == 'Shirt')
			  {
			  ?>
			  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SLambai; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SBazoo; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>باذو  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->STeera; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>تیرا   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SGeera; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> گیرا  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SChaati; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>چھاتی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SKamar; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"> </td>
						<td align="right" class="m-lbl"  >
							   
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->SGaala; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>	
						 <?php  }elseif($type == 'Coat')
			  {
			  ?>
			  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatLambai; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatBazoo; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>باذو  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatTeera; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>تیرا   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatChaati; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> چھاتی  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->Coatہپ; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>ہپ   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatKamar; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->crossback; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>Cross Back
   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->CoatGaala; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>		
						 <?php  }elseif($type == 'Waistcoat')
			  {
			  ?>
			  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastLambai; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>لمبائی   </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastTeera; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>تیرا  </b> 
							 
							</td>
						</tr>
						  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastہپ; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b>ہپ 
   </b>  
 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastChaati; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> چھاتی  </b> 
							 
							</td>
						</tr>		
		 								  <tr>
		 
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  //echo $orders->Coatہپ; ?></td>
						<td align="right" class="m-lbl"  >
							   &nbsp; &nbsp; <b><!--ہپ-->  </b>  
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastKamar; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> کمر  </b> 
							 
							</td>
						</tr>		
						<tr>
		 
						<td class="m-val" style="border-bottom:1px solid black;" align="left"> </td>
						<td align="right" class="m-lbl"  >
							   
							 
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php  echo $orders->wastGaala; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b> گلا  </b> 
							 
							</td>
						</tr>		
			  <?php }  ?>
						<tr>
							<td class="m-val" style="border-bottom:1px solid black;" colspan="2" align="left"><?php echo $orders->MREMARKS; ?></td>
							<td class="m-gap">&nbsp;</td>
							<td class="m-lbl" style="border-bottom:1px solid black;" colspan="2" align="right"><b>نوٹ لائن</b></td>
						</tr>
</table>
