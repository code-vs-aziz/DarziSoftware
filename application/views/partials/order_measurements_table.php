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
if ( ! isset($types) || ! is_array($types))
{
	$types = (isset($type) && $type !== '') ? array($type) : array();
}
$print_has_type = function ($needle) use ($types) {
	return in_array($needle, $types, TRUE);
};
$meas_trim = function ($key) use ($orders) {
	return isset($orders->$key) ? trim((string) $orders->$key) : '';
};
$geera_parts = measurement_split_triple($meas_trim('geera'));
$geera_upar = $geera_parts[0];
$geera_darmiyan = $geera_parts[1];
$geera_neeche = $geera_parts[2];
$sgeera_parts = measurement_split_triple($meas_trim('SGeera'));
$sgeera_upar = $sgeera_parts[0];
$sgeera_darmiyan = $sgeera_parts[1];
$sgeera_neeche = $sgeera_parts[2];
// Only show nap blocks for garment types on THIS order (WhatsApp PDF + print).
// Do not force قمیض شلوار just because customer measurement has kurta fields filled.
$show_kurta_block = $print_has_type('Kurta');
if ($show_kurta_block) {
	echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">قمیض شلوار</td></tr>';
}
if ($show_kurta_block) {
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
							<td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;"></td>
							<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo $orders->Teera; ?></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>تیرا  </b>
							</td>
						</tr>
								<tr>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;"></td>
							<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($geera_neeche); ?></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>گیرا نیچے</b>
							</td>
						</tr>
								<tr>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;"></td>
							<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($geera_darmiyan); ?></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>گیرا درمیان</b>
							</td>
						</tr>
								<tr>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;"></td>
							<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($geera_upar); ?></td>
							<td align="right" class="m-lbl" style="border-bottom:1px solid black;">
							   &nbsp; &nbsp; <b>گیرا اوپر</b>
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
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo isset($orders->CKC) ? esc_html((string) $orders->CKC) : ''; ?></td>
						<td align="right" class="m-lbl" style="border-bottom:1px solid black;" >
							   &nbsp; &nbsp; <b>پینٹ کوٹ</b>
							</td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
									<td align="right" class="m-lbl" style="border-bottom:1px solid black;" ></td>
			  </tr>
			  
			  <tr>
				<td> </td> 
			  </tr>
			  <?php  }
			  if($print_has_type('Pajama'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">پاجامہ</td></tr>';
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
			  
			  <?php  }
			  if($print_has_type('Pent'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">پینٹ</td></tr>';
				$pent_pick = function () use ($orders) {
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
					array($pent_pick('PentLambai'), 'لمبائی'),
					array($pent_pick('pentWaiste'), 'کمر'),
					array($pent_pick('HIP'), 'ہپ'),
					array($pent_pick('PentPat', 'PentThai'), 'پٹ'),
					array($pent_pick('PentGhutna'), 'گھٹنہ'),
					array($pent_pick('PentPonchaMori', 'PentPoncha'), 'پانچہ موری'),
				);
				foreach ($pent_rows as $pr)
				{
			  ?>
			  <tr>
						<td class="m-val" style="border-bottom:1px solid black;" align="left" colspan="4"><?php echo esc_html($pr[0]); ?></td>
						<td align="right" class="m-lbl">
							   &nbsp; &nbsp; <b><?php echo esc_html($pr[1]); ?></b>
						</td>
						</tr>
			  <?php
				}
			  }
			  if($print_has_type('Shirt'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">شرٹ</td></tr>';
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
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
						<td align="right" class="m-lbl"></td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo $orders->STeera; ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>تیرا</b>
							</td>
						</tr>
						  <tr>
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
						<td align="right" class="m-lbl"></td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($sgeera_neeche); ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>گیرا نیچے</b>
							</td>
						</tr>
						  <tr>
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
						<td align="right" class="m-lbl"></td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($sgeera_darmiyan); ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>گیرا درمیان</b>
							</td>
						</tr>
						  <tr>
						 <td class="m-val" style="border-bottom:1px solid black;" align="left"></td>
						<td align="right" class="m-lbl"></td>
<td class="m-gap">&nbsp;</td>
							<td class="m-val" style="border-bottom:1px solid black;" align="left"><?php echo esc_html($sgeera_upar); ?></td>
									<td align="right" class="m-lbl" >
							   &nbsp; &nbsp; <b>گیرا اوپر</b>
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
						 <?php  }
			  if($print_has_type('Coat'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">شروانی/کوٹ</td></tr>';
				$coat_pick = function () use ($orders) {
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
					array($coat_pick('CoatLambai'), 'لمبائی'),
					array($coat_pick('CoatChaati'), 'چھاتی'),
					array($coat_pick('CoatKamar'), 'کمر'),
					array($coat_pick('CoatTeera'), 'تیرا'),
					array($coat_pick('CoatBazoo'), 'بازو'),
					array($coat_pick('CoatGaala'), 'گلا'),
					array($coat_pick('CoatHIP', 'SherwaniHIP'), 'ہپ'),
					array($coat_pick('CoatDola', 'SherwaniDola'), 'ڈولا'),
					array($coat_pick('CoatBazooMori', 'SherwaniBazooMori'), 'بازو موری'),
					array($coat_pick('crossback', 'SherwaniCrossback'), 'کراس بیک'),
					array($coat_pick('CoatLambaiBack', 'SherwaniLambaiBack'), 'لمبائی بیک'),
				);
				foreach ($coat_rows as $cr)
				{
			  ?>
			  <tr>
						<td class="m-val" style="border-bottom:1px solid black;" align="left" colspan="4"><?php echo esc_html($cr[0]); ?></td>
						<td align="right" class="m-lbl">
							   &nbsp; &nbsp; <b><?php echo esc_html($cr[1]); ?></b>
						</td>
						</tr>
			  <?php
				}
			  }
			  if($print_has_type('PentCoat'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">پینٹ کوٹ</td></tr>';
				$pc_pick = function () use ($orders) {
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
					array($pc_pick('PCCoatLambai', 'CoatLambai'), 'کوٹ لمبائی'),
					array($pc_pick('PCCoatChaati', 'CoatChaati'), 'کوٹ چھاتی'),
					array($pc_pick('PCCoatKamar', 'CoatKamar'), 'کوٹ کمر'),
					array($pc_pick('PCCoatTeera', 'CoatTeera'), 'کوٹ تیرا'),
					array($pc_pick('PCCoatBazoo', 'CoatBazoo'), 'کوٹ بازو'),
					array($pc_pick('PCCoatGaala', 'CoatGaala'), 'کوٹ گلا'),
					array($pc_pick('PCCoatHIP', 'PCHIP', 'CoatHIP'), 'کوٹ ہپ'),
					array($pc_pick('PCCoatDola', 'CoatDola'), 'کوٹ ڈولا'),
					array($pc_pick('PCCrossback', 'crossback'), 'کراس بیک'),
					array($pc_pick('PCPentLambai', 'PentLambai'), 'پینٹ لمبائی'),
					array($pc_pick('PCpentWaiste', 'pentWaiste'), 'پینٹ کمر'),
					array($pc_pick('PCHIP', 'HIP'), 'پینٹ ہپ'),
					array($pc_pick('PCPentPat', 'PCPentThai', 'PentPat', 'PentThai'), 'پینٹ پٹ'),
					array($pc_pick('PCPentGhutna', 'PentGhutna'), 'پینٹ گھٹنہ'),
					array($pc_pick('PCPentPonchaMori', 'PCPentPoncha', 'PentPonchaMori', 'PentPoncha'), 'پینٹ پانچہ'),
				);
				foreach ($pc_rows as $cr)
				{
			  ?>
			  <tr>
						<td class="m-val" style="border-bottom:1px solid black;" align="left" colspan="4"><?php echo esc_html($cr[0]); ?></td>
						<td align="right" class="m-lbl">
							   &nbsp; &nbsp; <b><?php echo esc_html($cr[1]); ?></b>
						</td>
						</tr>
			  <?php
				}
			  }
			  if($print_has_type('Waistcoat'))
			  {
				echo '<tr><td colspan="5" align="center" style="font-weight:bold;padding:6px 0;">ویسکوٹ</td></tr>';
				$wast_rows = array(
					array('wastLambai', 'لمبائی'),
					array('wastChaati', 'چھاتی'),
					array('wastKamar', 'کمر'),
					array('wastTeera', 'تیرا'),
					array('wastGaala', 'گلا'),
					array('wastHIP', 'ہپ'),
				);
				foreach ($wast_rows as $wr)
				{
					$wk = $wr[0];
					$wl = $wr[1];
					$wv = isset($orders->$wk) ? $orders->$wk : '';
			  ?>
			  <tr>
						<td class="m-val" style="border-bottom:1px solid black;" align="left" colspan="4"><?php echo esc_html($wv); ?></td>
						<td align="right" class="m-lbl">
							   &nbsp; &nbsp; <b><?php echo esc_html($wl); ?></b>
						</td>
						</tr>
			  <?php
				}
			  }  ?>
</table>
