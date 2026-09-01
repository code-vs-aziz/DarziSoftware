 <script src="<?php echo base_url(); ?>/js/yauk.min.js"></script>
 
 <script>
 

   
 
 //var Remarks ;
 //var qty = document.getElementById('Quantity').value;
 	//Remarks = '#Remarks' ;
			//$(Remarks).setUrduInput();
 
 
  
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
			//	alert(itotal);
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
          recalcOrderEditGrandTotal();
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
					recalcOrderEditGrandTotal();
                }
				
            }); 
}

	function get_id11(add,total)
	{
		 if(document.getElementById(add).value == '')
		 {
			 document.getElementById(add).value = 0;
		 }
		 recalcOrderEditLine(parseInt(String(total).replace('itotal', ''), 10) || 0);
	}

	function recalcOrderEditLine(lineIdx)
	{
		var erate = parseFloat(document.getElementById('erate' + lineIdx).value) || 0;
		var drate = parseFloat(document.getElementById('drate' + lineIdx).value) || 0;
		var srate = parseFloat(document.getElementById('srate' + lineIdx).value) || 0;
		var add = parseFloat(document.getElementById('add' + lineIdx).value) || 0;
		var lineTotal = erate + drate + srate + add;
		document.getElementById('itotal' + lineIdx).value = lineTotal;
		recalcOrderEditGrandTotal();
	}

	function recalcOrderEditGrandTotal()
	{
		var qtyEl = document.getElementById('Quantity');
		if (!qtyEl) {
			return;
		}
		var tot = parseInt(qtyEl.value, 10) || 0;
		var sum = 0;
		for (var m = 0; m < tot; m++) {
			var erate = parseFloat(document.getElementById('erate' + m).value) || 0;
			var drate = parseFloat(document.getElementById('drate' + m).value) || 0;
			var srate = parseFloat(document.getElementById('srate' + m).value) || 0;
			var add = parseFloat(document.getElementById('add' + m).value) || 0;
			var lineTotal = erate + drate + srate + add;
			var itotalEl = document.getElementById('itotal' + m);
			if (itotalEl) {
				itotalEl.value = lineTotal;
			}
			sum += lineTotal;
		}
		document.getElementById('total_amount').value = sum;
		if (typeof bal === 'function') {
			bal();
		}
	}
	
	</script>
<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/select2.full.js"></script>
<script>
/*$.fn.select2.amd.require([
  'select2/selection/multiple',
  'select2/selection/search',
  'select2/dropdown',
  'select2/dropdown/attachBody',
  'select2/dropdown/closeOnSelect',
  'select2/compat/containerCss',
  'select2/utils'
], function (MultipleSelection, Search, Dropdown, AttachBody, CloseOnSelect, ContainerCss, Utils) {
  var SelectionAdapter = Utils.Decorate(
    MultipleSelection,
    Search
  );
  
  var DropdownAdapter = Utils.Decorate(
    Utils.Decorate(
      Dropdown,
      CloseOnSelect
    ),
    AttachBody
  );
  
  $('.inline-search').select2({
   
    selectionAdapter: SelectionAdapter
  });
  

  

});
function get_user()
{
	var naap = document.getElementById('Naapno').value;
	window.location = "http://localhost:81/alibaba/index.php/Order/order_view/order/"+naap;
}

 
 
$(document).ready(function(){

    $('#Quantity').on('change',function(){
			 document.getElementById('total_amount').value = 0;
        var qty = $(this).val();
        if(qty){
			
            $.ajax({
                type:'POST',
                url:'<?php echo site_url('Order/order_table'); ?>',
                data:'qty='+qty,
                success:function(html){
                    $('#maincat').html(html);
                    
                   // $('#subcat').html('<option value="">Select Category first</option>'); 
                }
            }); 
        }else{
           // $('#maincat').html('<option value="">Select Category first</option>');
         //   $('#subcat').html('<option value="">Select Category first</option>'); 
        }
    });
	
});

*/

function bal()
{
	document.getElementById('Balance').value = 	document.getElementById('total_amount').value - document.getElementById('Advance').value - document.getElementById('Discount').value ;
	
	 
}

	function orderRemarkInsert(id, val)
	{
		var el = document.getElementById(id);
		if (!el || val === null || val === undefined) {
			return;
		}
		val = String(val).trim();
		if (val === '') {
			return;
		}
		var cur = String(el.value || '').replace(/\s+$/, '');
		el.value = cur ? (cur + ' ' + val) : val;
		if (window.jQuery) {
			jQuery(el).trigger('input');
		}
		el.focus();
	}

	function addtext(ID, val)
	{
		orderRemarkInsert(ID, val);
	}
</script>
<script>
$(document).ready(function () {
	$(document).on('click', '.order-predefined-tag', function (e) {
		e.preventDefault();
		orderRemarkInsert(this.getAttribute('data-target'), this.getAttribute('data-insert'));
	});
	initOrderRemarkFields(document);
});

function initOrderRemarkFields(root)
{
	if (!window.jQuery || !jQuery.fn.setUrduInput) {
		return;
	}
	var $root = root ? jQuery(root) : jQuery(document);
	$root.find('input.form-control.urd.txtbox').each(function () {
		var $field = jQuery(this);
		if (!$field.data('urduBound')) {
			$field.setUrduInput();
			$field.data('urduBound', 1);
		}
	});
}
</script>
<style>
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', sans-serif;
	}
	#Name,#City,#Remarks,#Address,#Country {
		direction: rtl;
		border: 1px solid #ccc;
		vertical-align: middle;
		padding: 2px;
		font-size: 16px;
		font-weight: bold;
	}
	#kb {
		width: 500px;
	}
	a {
		color: #4ea0e8;
		text-decoration: none;
	}
</style>
