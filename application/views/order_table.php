 <style>
	.urd {
		font-weight: bold;
		font-family: 'Noto Nastaliq Urdu Draft', 'Jameel Noori Nastaleeq', serif;
	}
	.txtbox {
		direction: rtl;
		text-align: right;
	}
	.order-predefined-tags {
		direction: rtl;
		text-align: right;
		font-weight: bold;
		line-height: 1.8;
		margin: 8px 0 10px;
		width: 100%;
		clear: both;
	}
	.order-predefined-tags .order-predefined-row {
		margin-bottom: 4px;
	}
	.order-predefined-tags .order-predefined-tag {
		display: inline;
		background: none;
		border: none;
		padding: 0;
		margin: 0;
		font: inherit;
		font-weight: bold;
		color: inherit;
		cursor: pointer;
	}
	.order-predefined-tags .order-predefined-tag:hover {
		text-decoration: underline;
	}
	.order-predefined-tags .order-predefined-sep {
		font-weight: bold;
	}
	.order-remark-group {
		direction: rtl;
		text-align: right;
		width: 100%;
	}
	.order-remark-group label {
		display: block;
		text-align: right;
		width: 100%;
	}
	.order-copy-bar {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: 6px;
		margin: 4px 0 8px;
	}
	.order-copy-bar .order-copy-label {
		font-weight: bold;
		white-space: nowrap;
	}
	.order-copy-bar .order-copy-from {
		width: auto;
		min-width: 56px;
		display: inline-block;
	}
 </style>
 <script>
 var LAST_PRICE = 0;
 var films;
 var Design_id = '#Design0';
 var Design_id2 = '#Design0';
 		function get_id(ID,DRATE)
{
	
	Design_id = ID;
	



	 Design_id  = '#' + Design_id ;
	
		
	 document.getElementById('total_amount').value = 0;
  
   
	 	 
			 
        var Design = document.getElementById(ID).value;
        
	//	 alert(Design);
        if(Design){
			
            $.ajax({
                type:'POST',
                url:'<?php echo site_url('Order/order_cal'); ?>',
                data:'Design='+Design,
                success:function(html){
					 
					
				//	 alert(parseInt(html));
				//	 alert(DRATE);
				 	document.getElementById(DRATE).value = parseInt(html);
	
					var tot  = document.getElementById('Quantity').value;
					
	for(var m=0; m<tot; m++)
	{
			var erateid = 'erate' + m ;
		var rateid = 'drate' + m ;
		var itotal = 'itotal' + m ;
		var srateid = 'srate' + m ;
				var add1 = 'add' + m ;
		if(document.getElementById(erateid).value == '')
		{
			document.getElementById(erateid).value = 0;
		}
		if(document.getElementById(rateid).value == '')
		{
			document.getElementById(rateid).value = 0;
		}
				if(document.getElementById(add1).value == '')
		{
			document.getElementById(add1).value = 0;
		}
			document.getElementById('total_amount').value = parseInt(document.getElementById('total_amount').value) + parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)  + parseInt(document.getElementById(add1).value) ;
			document.getElementById(itotal).value = parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)  + parseInt(document.getElementById(add1).value);  
			}

	 
                }
				
            }); 
        }else{
          
        }
	
 
}


var emb_id = '#embroidery0';
 var emb_id2 = '#embroidery0';
  
 		function get_id1(ID,ERATE)
{
	
	emb_id = ID;
	
	


	 emb_id  = '#' + emb_id ;
	
		
	
  document.getElementById('total_amount').value = 0;
   
	 	 
			 
        var embroidery = document.getElementById(ID).value;
        
	 
        if(embroidery){
			
            $.ajax({
                type:'POST',
                url:'<?php echo site_url('Order/order_cal'); ?>',
                data:'embroidery='+embroidery,
                success:function(html){
					 
					
					
				 	 
				 	document.getElementById(ERATE).value = parseInt(html);
	  
					var tot  = document.getElementById('Quantity').value;
	for(var m=0; m<tot; m++)
	{
		 
		var erateid = 'erate' + m ;
		var rateid = 'drate' + m ;
			var itotal = 'itotal' + m ;
		var srateid = 'srate' + m ;
			var add1 = 'add' + m ;
		if(document.getElementById(erateid).value == '')
		{
			document.getElementById(erateid).value = 0;
		}
			if(document.getElementById(rateid).value == '')
		{
			document.getElementById(rateid).value = 0;
		}
	 
				if(document.getElementById(add1).value == '')
		{
			document.getElementById(add1).value = 0;
		}
		//alert("");
			document.getElementById('total_amount').value = parseInt(document.getElementById('total_amount').value) + parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)   + parseInt(document.getElementById(add1).value);
		//	document.getElementById(itotal).value = parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value) ;
			 document.getElementById(itotal).value = parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)  + parseInt(document.getElementById(add1).value);  
	}

	 
                }
				
            }); 
        }else{
        
        }
	
 
}

var suit_id = '#suit0';
 var suit_id2 = '#suit0';
	function get_id2(ID,SRATE)
{
	
	suit_id = ID;
	



	 suit_id  = '#' + suit_id ;
	
		
	 document.getElementById('total_amount').value = 0;
  
   
	 	 
			 
        var suitEl = document.getElementById(ID);
        var suit = suitEl ? suitEl.value : '';
        var rateEl = document.getElementById(SRATE);

        if ( ! suit || String(suit) === '0')
        {
          if (rateEl)
          {
            rateEl.value = 0;
          }
          var totClear = document.getElementById('Quantity').value;
          document.getElementById('total_amount').value = 0;
          for (var c = 0; c < totClear; c++)
          {
            var erateid = 'erate' + c;
            var rateid = 'drate' + c;
            var itotal = 'itotal' + c;
            var srateid = 'srate' + c;
            var add1 = 'add' + c;
            if (document.getElementById(erateid).value == '')
            {
              document.getElementById(erateid).value = 0;
            }
            if (document.getElementById(rateid).value == '')
            {
              document.getElementById(rateid).value = 0;
            }
            if (document.getElementById(srateid).value == '')
            {
              document.getElementById(srateid).value = 0;
            }
            if (document.getElementById(add1).value == '')
            {
              document.getElementById(add1).value = 0;
            }
            document.getElementById('total_amount').value = parseInt(document.getElementById('total_amount').value) + parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value) + parseInt(document.getElementById(add1).value);
            document.getElementById(itotal).value = parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value) + parseInt(document.getElementById(add1).value);
          }
          return;
        }

        $.ajax({
                type:'POST',
                url:'<?php echo site_url('Order/order_cal'); ?>',
                data:'suit='+suit,
                success:function(html){
					 
					
				 	 
				//	 alert(DRATE);
				 	if (rateEl)
          {
            rateEl.value = parseInt(html, 10) || 0;
          }
	
					var tot  = document.getElementById('Quantity').value;
	for(var m=0; m<tot; m++)
	{
			var erateid = 'erate' + m ;
		var rateid = 'drate' + m ;
			var itotal = 'itotal' + m ;
		var srateid = 'srate' + m ;
		var add1 = 'add' + m ;
		 
		if(document.getElementById(erateid).value == '')
		{
			document.getElementById(erateid).value = 0;
		}
		if(document.getElementById(rateid).value == '')
		{
			document.getElementById(rateid).value = 0;
		}
			if(document.getElementById(srateid).value == '')
		{
			document.getElementById(srateid).value = 0;
		}
		 			if(document.getElementById(add1).value == '')
		{
			document.getElementById(add1).value = 0;
		}
	
			document.getElementById('total_amount').value = parseInt(document.getElementById('total_amount').value) + parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)  + parseInt(document.getElementById(add1).value) ;
			document.getElementById(itotal).value = parseInt(document.getElementById(erateid).value) + parseInt(document.getElementById(rateid).value) + parseInt(document.getElementById(srateid).value)  + parseInt(document.getElementById(add1).value);  
		
	}

	 
                }
				
            }); 
}

	function get_id11(add,total)
	{
		//alert(add);
		document.getElementById(total).value = parseInt(document.getElementById(total).value) + parseInt(document.getElementById(add).value);
		document.getElementById('total_amount').value = parseInt(document.getElementById('total_amount').value) +  parseInt(document.getElementById(add).value);
	}
	
	function addtext(ID, val)
	{
		if (typeof orderRemarkInsert === 'function') {
			orderRemarkInsert(ID, val);
			return;
		}
		var el = document.getElementById(ID);
		if (!el || !val) {
			return;
		}
		val = String(val).trim();
		var cur = String(el.value || '').replace(/\s+$/, '');
		el.value = cur ? (cur + ' ' + val) : val;
	}
	</script>
					<?php  
						 $qty = $_POST['qty'];
						// $qty = 5;
					 $SR = 1; 
							for($i=0; $i<$qty; $i++)
							{
				 
					?>
					<div class="order-suit-line" data-line="<?php echo $i; ?>">
					<hr>
<?php
	$remark_field = 'Remarks'.$i;
	$this->load->view('partials/order_predefined_tags', array(
		'remark_field' => $remark_field,
		'order_predefined_by_row' => isset($order_predefined_by_row) ? $order_predefined_by_row : array(),
	));
?>
		                  
								<div class="row">
						<div class="col-12">
                                             <div class="form-group order-remark-group">
                                                <label for="Remarks<?php echo $i ?>" class="control-label mb-1 urd"><?php echo t('order_note_line'); ?></label>
                                                <input id="Remarks<?php echo $i ?>" name="Remarks[]" type="text" class="form-control urd txtbox" dir="rtl" lang="ur" aria-required="true" aria-invalid="false" value="">
                                            </div>
						</div>
						</div>
						
														<div class="row">
					 	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_front_pocket'); ?></label>
                                              <select 	id="fpocket<?php echo $i; ?>"   name="fpocket[]"  type="text" class="form-control " aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
 
  <option value="1" >1</option>
  <option value=" بغیر فلاپ 2" > 2بغیر فلاپ </option>
  <option value="2  فلاپ والی" >2فلاپ والی </option>
 
			 
</select>

		</div>  
						</div>	
			<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_side_pocket'); ?></label>
                                              <select 	id="spocket<?php echo $i; ?>"   name="spocket[]"  type="text" class="form-control  " aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
  <option value="1" >1</option>
  <option value="2" >2</option>
 
   
</select>

		</div>  
						</div>	
	<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_shalwar_pocket'); ?></label>
                                              <select 	id="shawpocket<?php echo $i; ?>"   name="shawpocket[]"  type="text" class="form-control  " aria-required="true" aria-invalid="false">
  <option value="X">X</option>
 
  <option value="1" >1</option>
  <option value="آگے پیچے 2 " >آگے پیچے 2 </option>
  <option value="1 سائیڈ پاکٹ والی" >سائیڈ پاکٹ والی 1</option>
  <option value="1 کاج بٹن والی " >کاج بٹن والی1  </option>
  <option value="1 فکس پاکٹ" >فکس پاکٹ1  </option>
  <option value="فرنٹ پر2" >فرنٹ پر2  </option>
</select>

		</div>  
						</div>							
						</div>	
						
						
											<div class="row order-line-header-row">
			
<div class="col"  >	
				                                             <label for="emp-EMPNAME" class="control-label mb-1" style="    float: right;
    border: 1px solid black;
    border-radius: 29px;
    width: 29px;
    text-align: center;" ><?php
	echo $SR;
	$SR = $SR + 1;
	?></label>
						</div>
						<div class="col">
<?php
	$this->load->view('partials/order_copy_line_bar', array(
		'line_index' => $i,
		'qty' => $qty,
	));
?>
						</div>
						</div>
										<div class="row">
							<div class="col">		
                                             <div class="form-group">
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_design_type'); ?></label>
                                              <select 	id="Designtype<?php echo $i; ?>"      name="desigtype[]" type="text" class="form-control" aria-required="true" aria-invalid="false">
  <option value=""><?php echo t('order_select_design_type'); ?></option>
  <option value="Kurta"><?php echo t('design_type_kurta'); ?></option>
  <option value="Pajama"><?php echo t('design_type_pajama'); ?></option>
  <option value="Pent"><?php echo t('design_type_pent'); ?></option>
  <option value="Shirt"><?php echo t('design_type_shirt'); ?></option>
  <option value="Coat"><?php echo t('design_type_coat'); ?></option>
  <option value="Waistcoat"><?php echo t('design_type_waistcoat'); ?></option>
 
 
</select>
 
  <span  style="float:left;"> <input style="float:left; width:75px;" onchange="get_id11('add<?php echo $i; ?>','itotal<?php echo $i; ?>')" id="add<?php echo $i; ?>" name="add[]"    type="text" class="form-control" aria-required="true" aria-invalid="false" value=""  >     &nbsp;  +  &nbsp;</span>
  <span> <input style=" width:75px;" id="itotal<?php echo $i; ?>" name="itotal<?php echo $i; ?>"  readonly="readonly"  type="text" class="form-control" aria-required="true" aria-invalid="false" value=""  >        
  
  
                                            </div>  
						</div>					
							<div class="col">		
                                             <div class="form-group" >
                                                <label for="emp-EMPNAME" class="control-label mb-1"><?php echo t('order_suit_type'); ?></label>
                                              <select 	id="suit<?php echo $i; ?>"   name="suit[]" onchange="get_id2('suit<?php echo $i; ?>','srate<?php echo $i; ?>')" type="text" class="form-control" aria-required="true" aria-invalid="false">
  <option value="0"><?php echo t('order_select_suit'); ?></option>
 
 <?php 
			 
			 foreach($suit as $suits){
									 
						                    ?>
  <option value="<?php  echo $suits->SNO; ?>" ><?php echo $suits->name; ?></option>
			 <?php  } ?>
</select>
<input id="srate<?php echo $i; ?>" name="ssrate[]"  readonly="readonly"  type="text" class="form-control" aria-required="true" aria-invalid="false" value=""  >        
		</div>  
						</div>
					<input type="hidden" name="designo[]" value="0">
					<input type="hidden" name="Embroidery[]" value="0">
					<input type="hidden" id="drate<?php echo $i; ?>" name="ddrate[]" value="0">
					<input type="hidden" id="erate<?php echo $i; ?>" name="eerate[]" value="0">
		
					</div>	
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
									<div class="row">
					 	<div class="col">		
                                             <div class="form-group">
                                                
       <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="1"  ><image src="<?php echo base_url(); ?>/images/1.jpg" style="height:100px;" >
	                          </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="2"  ><image src="<?php echo base_url(); ?>/images/2.jpg" style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="3"  ><image src="<?php echo base_url(); ?>/images/3.jpg"style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="4"  ><image src="<?php echo base_url(); ?>/images/4.jpg" style="height:100px;">
	    </div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group">
       <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="5"  ><image src="<?php echo base_url(); ?>/images/5.jpg" style="height:100px;">
</div>  
						</div>
									 	<div class="col">		
                                             <div class="form-group"   for="kolorstyle"  > 
	  <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="6"  ><image src="<?php echo base_url(); ?>/images/6.jpg" style="height:100px;">
                                            </div>  
						</div>
							<div class="col">		
                                             <div class="form-group"   for="kolorstyle"  > 
	  <input id="kolorstyle" name="kolorstyle<?php echo $i ?>"   type="radio" class="form-control" aria-required="true" aria-invalid="false" value="7"  ><image src="<?php echo base_url(); ?>/images/7.jpg" style="height:100px;">
                                            </div>  
						</div>
						</div>
			<hr style="border: 2px solid #CCC;">
					</div>
							<?php  }  ?>