<script>
(function () {
	if (window.__orderCopySuitLineInit) {
		return;
	}
	window.__orderCopySuitLineInit = true;

	function lineSelect(lineIdx, nameAttr) {
		var line = document.querySelector('.order-suit-line[data-line="' + lineIdx + '"]');
		if (!line) {
			return null;
		}
		return line.querySelector('select[name="' + nameAttr + '"]');
	}

	function setVal(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.value = value != null ? value : '';
		}
	}

	function getSelectVal(id) {
		if (window.jQuery) {
			var $el = jQuery('#' + id);
			if ($el.length) {
				return $el.val();
			}
		}
		var el = document.getElementById(id);
		return el ? el.value : '';
	}

	function setSelect2Val(id, value) {
		if (!window.jQuery) {
			setVal(id, value);
			return;
		}
		var $el = jQuery('#' + id);
		if (!$el.length) {
			return;
		}
		$el.val(value);
		if ($el.data('select2')) {
			$el.trigger('change.select2');
		}
	}

	function copyKolorStyle(fromIdx, toIdx) {
		var fromChecked = document.querySelector('input[name="kolorstyle' + fromIdx + '"]:checked');
		if (!fromChecked) {
			return;
		}
		var target = document.querySelector('input[name="kolorstyle' + toIdx + '"][value="' + fromChecked.value + '"]');
		if (target) {
			target.checked = true;
		}
	}

	function recalculateOrderGrandTotal() {
		var qtyEl = document.getElementById('Quantity');
		if (!qtyEl) {
			return;
		}
		var tot = parseInt(qtyEl.value, 10) || 0;
		var sum = 0;
		for (var m = 0; m < tot; m++) {
			var erate = parseInt(document.getElementById('erate' + m).value, 10) || 0;
			var drate = parseInt(document.getElementById('drate' + m).value, 10) || 0;
			var srate = parseInt(document.getElementById('srate' + m).value, 10) || 0;
			var add = parseInt(document.getElementById('add' + m).value, 10) || 0;
			var lineTotal = erate + drate + srate + add;
			document.getElementById('itotal' + m).value = lineTotal;
			sum += lineTotal;
		}
		document.getElementById('total_amount').value = sum;
		if (typeof bal === 'function') {
			bal();
		}
	}

	window.copyOrderSuitLine = function (fromIdx, toIdx) {
		fromIdx = parseInt(fromIdx, 10);
		toIdx = parseInt(toIdx, 10);
		if (isNaN(fromIdx) || isNaN(toIdx) || fromIdx === toIdx) {
			return;
		}
		if (!document.getElementById('Remarks' + fromIdx) || !document.getElementById('Remarks' + toIdx)) {
			return;
		}

		var remarksFrom = document.getElementById('Remarks' + fromIdx);
		var remarksTo = document.getElementById('Remarks' + toIdx);
		remarksTo.value = remarksFrom.value;
		if (window.jQuery) {
			jQuery(remarksTo).trigger('input');
		}

		['fpocket', 'spocket', 'shawpocket'].forEach(function (prefix) {
			var fromEl = document.getElementById(prefix + fromIdx);
			if (fromEl) {
				setVal(prefix + toIdx, fromEl.value);
			}
		});

		var fromType = document.getElementById('Designtype' + fromIdx) || lineSelect(fromIdx, 'desigtype[]');
		var toType = document.getElementById('Designtype' + toIdx) || lineSelect(toIdx, 'desigtype[]');
		if (fromType && toType) {
			toType.value = fromType.value;
		}

		setVal('suit' + toIdx, document.getElementById('suit' + fromIdx).value);
		setVal('srate' + toIdx, document.getElementById('srate' + fromIdx).value);
		setVal('add' + toIdx, document.getElementById('add' + fromIdx).value);
		setVal('itotal' + toIdx, document.getElementById('itotal' + fromIdx).value);

		copyKolorStyle(fromIdx, toIdx);
		recalculateOrderGrandTotal();
	};

	if (window.jQuery) {
		jQuery(document).on('click', '.order-copy-apply', function (e) {
			e.preventDefault();
			var $line = jQuery(this).closest('.order-suit-line');
			var toIdx = parseInt($line.data('line'), 10);
			var fromIdx = parseInt($line.find('.order-copy-from').val(), 10) - 1;
			copyOrderSuitLine(fromIdx, toIdx);
		});
	}
})();
</script>
