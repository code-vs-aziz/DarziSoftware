(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	window.initOrderDetailDataTable = function () {
		var $table = $('table#example.order-detail-table');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var pageLen = parseInt($table.data('page-length'), 10) || 10;
		pageLen = Math.min(100, Math.max(1, pageLen));
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			pageLength: pageLen,
			lengthChange: false,
			dom: 'frtip'
		});
		var $wrapper = $table.closest('.dataTables_wrapper');
		$wrapper.find('.order-detail-length').remove();
		var $ctrl = $('<div class="dataTables_length order-detail-length"><label>Show <input type="number" min="1" max="100" step="1" class="order-detail-page-size form-control form-control-sm"> entries</label></div>');
		$ctrl.find('input').val(pageLen);
		$wrapper.find('.dataTables_filter').before($ctrl);
		$ctrl.find('input').on('change', function () {
			var n = parseInt($(this).val(), 10) || pageLen;
			n = Math.min(100, Math.max(1, n));
			$(this).val(n);
			dt.page.len(n).draw();
		});
		$ctrl.find('input').on('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				$(this).trigger('change');
			}
		});
	};

	function orderStatusFormAction() {
		var $form = $('#os-status-filter-form');
		return ($form.length && $form.attr('action')) || window.location.pathname;
	}

	/**
	 * Table Search → server lookup.
	 * Uses dt_q so one term matches order # OR naap OR phone OR name
	 * (same idea as stitching SSP search).
	 */
	function isOrderStatusLookupQuery(q) {
		var raw = String(q || '').replace(/^\s+|\s+$/g, '');
		if (!raw) {
			return null;
		}
		var digits = raw.replace(/\D/g, '');
		// Partial PK phone (03…) — keep typing, no server trip yet.
		if (digits.length > 0 && digits.length < 11 && digits.charAt(0) === '0') {
			return null;
		}
		// Full-ish phone.
		if (digits.length >= 11 || (digits.length >= 10 && digits.charAt(0) !== '0')) {
			return { field: 'dt_q', value: digits };
		}
		// Order # / naap / short code (e.g. 46902) — 3+ chars.
		if (raw.length >= 3) {
			return { field: 'dt_q', value: raw };
		}
		return null;
	}

	function orderStatusScope() {
		var $scope = $('#os-scope-input');
		var v = $scope.length ? String($scope.val() || '').toLowerCase() : '';
		return v === 'delivered' ? 'delivered' : 'current';
	}

	function goOrderStatusLookup(lookup) {
		if (!lookup) {
			return false;
		}
		var params = new URLSearchParams();
		params.set('scope', orderStatusScope());
		params.set(lookup.field, lookup.value);
		params.set('via', 'dt');
		window.location.href = orderStatusFormAction() + '?' + params.toString();
		return true;
	}

	function goOrderStatusAll() {
		var params = new URLSearchParams();
		params.set('scope', orderStatusScope());
		window.location.href = orderStatusFormAction() + '?' + params.toString();
	}

	function topFiltersAreEmpty($form) {
		var keys = ['customer_name', 'order_no', 'naap_no', 'mobile_no', 'return_from', 'return_to'];
		for (var i = 0; i < keys.length; i++) {
			var v = String($form.find('[name="' + keys[i] + '"]').val() || '').replace(/^\s+|\s+$/g, '');
			if (v !== '') {
				return false;
			}
		}
		return true;
	}

	function bindTopFilterClear($form) {
		if (!$form.length) {
			return;
		}
		var clearTimer = null;
		$form.off('.osTopClear');
		$form.on('input.osTopClear', '.os-filter-clearable', function () {
			clearTimeout(clearTimer);
			clearTimer = setTimeout(function () {
				if (!topFiltersAreEmpty($form)) {
					return;
				}
				$form.find('[name="stage"]').val('');
				goOrderStatusAll();
			}, 350);
		});
	}

	window.initOrderListDataTable = function () {
		var $table = $('table#example[data-order-list-table]');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}

		var $prevFilter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		var preserveVal = $prevFilter.length ? String($prevFilter.val() || '') : '';
		var preserveFocus = $prevFilter.length && document.activeElement === $prevFilter.get(0);
		var preserveStart = preserveFocus && typeof $prevFilter.get(0).selectionStart === 'number'
			? $prevFilter.get(0).selectionStart : null;
		var preserveEnd = preserveFocus && typeof $prevFilter.get(0).selectionEnd === 'number'
			? $prevFilter.get(0).selectionEnd : null;

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var viaDt = String($table.attr('data-os-via-dt') || '') === '1';
		var seed = String($table.attr('data-os-dt-seed') || '');
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			searching: true,
			searchDelay: 10000
		});
		var $form = $('#os-status-filter-form');
		bindTopFilterClear($form);

		var $filter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		if (!$filter.length) {
			return;
		}

		var suppressInput = false;
		var lookupTimer = null;

		function sameAsCurrentSeed(lookup) {
			if (!viaDt || !seed || !lookup) {
				return false;
			}
			return String(lookup.value) === String(seed)
				|| String(lookup.value) === String(seed).split('-')[0];
		}

		function restoreFilterCaret(el, start, end) {
			if (!el) {
				return;
			}
			try {
				el.focus({ preventScroll: true });
			} catch (errFocus) {
				try { el.focus(); } catch (err2) { /* ignore */ }
			}
			if (start !== null && typeof el.setSelectionRange === 'function') {
				try { el.setSelectionRange(start, end); } catch (errSel) { /* ignore */ }
			}
		}

		function applyClientSearch(term) {
			var el = $filter.get(0);
			var start = el && typeof el.selectionStart === 'number' ? el.selectionStart : null;
			var end = el && typeof el.selectionEnd === 'number' ? el.selectionEnd : null;
			var val = $filter.val();
			var hadFocus = el && document.activeElement === el;
			suppressInput = true;
			try {
				dt.search(term).draw(false);
			} finally {
				suppressInput = false;
			}
			$filter.val(val);
			if (hadFocus) {
				restoreFilterCaret(el, start, end);
			}
			return dt.rows({ search: 'applied' }).count();
		}

		$filter.off('keyup.DT search.DT input.DT paste.DT cut.DT keypress.DT');
		$filter.off('.osServerLookup');

		if (preserveVal !== '') {
			$filter.val(preserveVal);
			applyClientSearch(preserveVal);
		} else if (viaDt && seed) {
			// Server already filtered rows for this seed — show all loaded matches.
			$filter.val(seed);
			applyClientSearch('');
		}

		if (preserveFocus) {
			restoreFilterCaret($filter.get(0), preserveStart, preserveEnd);
		}

		function runLookupFromInput(raw) {
			if (raw === '') {
				goOrderStatusAll();
				return;
			}
			var lookup = isOrderStatusLookupQuery(raw);
			if (!lookup) {
				applyClientSearch(raw);
				return;
			}
			if (sameAsCurrentSeed(lookup)) {
				applyClientSearch('');
				$filter.val(raw);
				return;
			}
			goOrderStatusLookup(lookup);
		}

		$filter.on('keydown.osServerLookup', function (e) {
			if (e.key !== 'Enter' && e.which !== 13) {
				return;
			}
			e.preventDefault();
			clearTimeout(lookupTimer);
			runLookupFromInput(String($(this).val() || '').replace(/^\s+|\s+$/g, ''));
		});

		$filter.on('input.osServerLookup', function () {
			if (suppressInput) {
				return;
			}
			clearTimeout(lookupTimer);
			var raw = String($(this).val() || '').replace(/^\s+|\s+$/g, '');
			if (raw === '') {
				applyClientSearch('');
				return;
			}
			var matched = applyClientSearch(raw);
			var lookup = isOrderStatusLookupQuery(raw);
			if (!lookup || sameAsCurrentSeed(lookup)) {
				return;
			}
			// Not in the loaded 200 rows (e.g. naap 46902) — server search after pause,
			// same practical result as stitching SSP. Partial phones never hit this.
			if (matched === 0) {
				lookupTimer = window.setTimeout(function () {
					goOrderStatusLookup(lookup);
				}, 450);
			}
		});
	};

}(window, window.jQuery));
