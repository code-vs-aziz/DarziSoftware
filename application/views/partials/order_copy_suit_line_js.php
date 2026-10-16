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
			var wrap = target.closest('.order-kolar-picker');
			if (wrap) {
				var siblings = wrap.querySelectorAll('.order-kolar-option');
				for (var s = 0; s < siblings.length; s++) {
					siblings[s].classList.remove('is-selected');
				}
			}
			var label = target.closest('.order-kolar-option');
			if (label) {
				label.classList.add('is-selected');
			}
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

	window.recalculateOrderGrandTotal = recalculateOrderGrandTotal;

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
		if (typeof window.orderFormUpdateSuitAccordion === 'function') {
			window.orderFormUpdateSuitAccordion();
		}
	};

	function suitLineHasDesignAndType(idx) {
		var typeEl = document.getElementById('Designtype' + idx) || lineSelect(idx, 'desigtype[]');
		var suitEl = document.getElementById('suit' + idx);
		var typeVal = typeEl ? String(typeEl.value || '').trim() : '';
		var suitVal = suitEl ? String(suitEl.value || '').trim() : '';
		return typeVal !== '' && typeVal !== '0' && suitVal !== '' && suitVal !== '0';
	}

	/**
	 * Fill all later suit lines from this one (same style/options/price/kolar/remarks).
	 * Returns how many lines were updated.
	 */
	window.copyOrderSuitLineToRemaining = function (fromIdx) {
		fromIdx = parseInt(fromIdx, 10);
		if (isNaN(fromIdx) || fromIdx < 0) {
			return 0;
		}
		if (!suitLineHasDesignAndType(fromIdx)) {
			var msg = (window.APP_LANG && APP_LANG.order_suit_complete_first)
				? String(APP_LANG.order_suit_complete_first).replace('%d', String(fromIdx + 1))
				: ('Please complete design and suit type for Suit ' + (fromIdx + 1) + ' first.');
			alert(msg);
			return 0;
		}
		var qtyEl = document.getElementById('Quantity');
		var qty = qtyEl ? (parseInt(qtyEl.value, 10) || 0) : 0;
		if (qty < 2 || fromIdx >= qty - 1) {
			return 0;
		}
		var copied = 0;
		for (var t = fromIdx + 1; t < qty; t++) {
			window.copyOrderSuitLine(fromIdx, t);
			copied++;
		}
		return copied;
	};

	function applySameAsFromSelect($line) {
		if (!$line || !window.jQuery) {
			return;
		}
		var toIdx = parseInt($line.data('line'), 10);
		if (isNaN(toIdx)) {
			toIdx = parseInt($line.attr('data-suit-index'), 10);
		}
		var fromVal = $line.find('.order-copy-from').val();
		if (!fromVal) {
			return;
		}
		var fromIdx = parseInt(fromVal, 10) - 1;
		if (isNaN(fromIdx) || isNaN(toIdx) || fromIdx === toIdx) {
			return;
		}
		if (!document.getElementById('Remarks' + fromIdx)) {
			return;
		}
		copyOrderSuitLine(fromIdx, toIdx);
	}

	if (window.jQuery) {
		jQuery(document).on('change', '.order-copy-from', function () {
			applySameAsFromSelect(jQuery(this).closest('.order-suit-line'));
		});

		jQuery(document).on('click', '.order-copy-apply', function (e) {
			e.preventDefault();
			applySameAsFromSelect(jQuery(this).closest('.order-suit-line'));
		});

		jQuery(document).on('click', '.order-suit-apply-remaining', function (e) {
			e.preventDefault();
			var $line = jQuery(this).closest('.order-suit-line');
			var fromIdx = parseInt($line.data('line'), 10);
			if (isNaN(fromIdx)) {
				fromIdx = parseInt($line.attr('data-suit-index'), 10);
			}
			var n = window.copyOrderSuitLineToRemaining(fromIdx);
			if (n > 0) {
				var tpl = (window.APP_LANG && APP_LANG.order_same_remaining_done)
					? APP_LANG.order_same_remaining_done
					: 'Same details applied to %d remaining suit(s).';
				alert(String(tpl).replace('%d', String(n)));
			}
		});
	}
})();
</script>
