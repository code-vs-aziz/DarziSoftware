(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	var SEARCH_PLACEHOLDER = 'Search order, naap, phone, name…';

	function isOfflineNow() {
		return (
			window.DarziOffline &&
			window.DarziOffline.connectivity &&
			!window.DarziOffline.connectivity.isOnline()
		);
	}

	/**
	 * Universal lookup query → dt_q (order # / naap / phone / name / employee).
	 */
	function isUniversalLookupQuery(q) {
		var raw = String(q || '').replace(/^\s+|\s+$/g, '');
		if (!raw) {
			return null;
		}
		var digits = raw.replace(/\D/g, '');
		// Partial PK phone (03…) — keep typing, no server trip yet.
		if (digits.length > 0 && digits.length < 11 && digits.charAt(0) === '0') {
			return null;
		}
		if (digits.length >= 11 || (digits.length >= 10 && digits.charAt(0) !== '0')) {
			return { field: 'dt_q', value: digits };
		}
		if (raw.length >= 3) {
			return { field: 'dt_q', value: raw };
		}
		return null;
	}

	function restoreFilterCaret(el, start, end) {
		if (!el) {
			return;
		}
		try {
			el.focus({ preventScroll: true });
		} catch (errFocus) {
			try {
				el.focus();
			} catch (err2) {
				/* ignore */
			}
		}
		if (start !== null && typeof el.setSelectionRange === 'function') {
			try {
				el.setSelectionRange(start, end);
			} catch (errSel) {
				/* ignore */
			}
		}
	}

	function styleSearchInput($filter) {
		if (!$filter || !$filter.length) {
			return;
		}
		$filter.attr('placeholder', SEARCH_PLACEHOLDER);
		$filter.attr('autocomplete', 'off');
		$filter.css({ minWidth: '220px', maxWidth: '100%' });
		var $label = $filter.closest('label');
		if ($label.length) {
			$label.contents().filter(function () {
				return this.nodeType === 3;
			}).each(function () {
				this.nodeValue = String(this.nodeValue || '').replace(/Search\s*:?/i, 'Search:');
			});
		}
	}

	/**
	 * Bind real-time client filter + server dt_q lookup when no local matches.
	 */
	function bindUniversalSearch(opts) {
		opts = opts || {};
		var dt = opts.dt;
		var $filter = opts.$filter;
		var seed = String(opts.seed || '');
		var formSelector = opts.formSelector || '';
		var buildLookupUrl = opts.buildLookupUrl;
		var buildClearUrl = opts.buildClearUrl;
		var offlineLookup = opts.offlineLookup;
		if (!dt || !$filter || !$filter.length) {
			return;
		}

		styleSearchInput($filter);

		var suppressInput = false;
		var lookupTimer = null;

		function sameAsCurrentSeed(lookup) {
			if (!seed || !lookup) {
				return false;
			}
			return (
				String(lookup.value) === String(seed) ||
				String(lookup.value) === String(seed).split('-')[0]
			);
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

		function goLookup(lookup) {
			if (!lookup) {
				return;
			}
			if (isOfflineNow()) {
				if (typeof offlineLookup === 'function') {
					offlineLookup(lookup);
				}
				return;
			}
			if (typeof buildLookupUrl === 'function') {
				window.location.href = buildLookupUrl(lookup);
			}
		}

		function goClear() {
			if (isOfflineNow()) {
				applyClientSearch('');
				return;
			}
			if (typeof buildClearUrl === 'function') {
				window.location.href = buildClearUrl();
			}
		}

		function runLookupFromInput(raw) {
			if (raw === '') {
				goClear();
				return;
			}
			var lookup = isUniversalLookupQuery(raw);
			if (!lookup) {
				applyClientSearch(raw);
				return;
			}
			if (sameAsCurrentSeed(lookup)) {
				applyClientSearch('');
				$filter.val(raw);
				return;
			}
			goLookup(lookup);
		}

		$filter.off('keyup.DT search.DT input.DT paste.DT cut.DT keypress.DT');
		$filter.off('.osServerLookup');

		if (seed) {
			$filter.val(seed);
			applyClientSearch('');
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
			var lookup = isUniversalLookupQuery(raw);
			if (!lookup || sameAsCurrentSeed(lookup)) {
				return;
			}
			if (matched === 0) {
				lookupTimer = window.setTimeout(function () {
					var latest = String($filter.val() || '').replace(/^\s+|\s+$/g, '');
					var latestLookup = isUniversalLookupQuery(latest);
					if (!latestLookup) {
						return;
					}
					goLookup(latestLookup);
				}, isOfflineNow() ? 120 : 450);
			}
		});

		// Keep hidden dt_q in sync when present (date/status form submit).
		if (formSelector) {
			var $form = $(formSelector);
			$form.off('submit.universalSearch').on('submit.universalSearch', function () {
				var $hidden = $form.find('input[name="dt_q"]');
				if ($hidden.length) {
					$hidden.val(String($filter.val() || '').replace(/^\s+|\s+$/g, ''));
				}
			});
		}
	}

	function appendFormFilters(params, formSelector, keys) {
		var $form = formSelector ? $(formSelector) : $();
		if (!$form.length) {
			return;
		}
		(keys || []).forEach(function (name) {
			var el = $form.find('[name="' + name + '"]');
			if (!el.length) {
				return;
			}
			var v = String(el.val() || '').replace(/^\s+|\s+$/g, '');
			if (v !== '') {
				params.set(name, v);
			}
		});
	}

	window.initOrderDetailDataTable = function () {
		var $table = $('table#example.order-detail-table');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		var $prevFilter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		var preserveVal = $prevFilter.length ? String($prevFilter.val() || '') : '';
		var preserveFocus = $prevFilter.length && document.activeElement === $prevFilter.get(0);
		var preserveStart =
			preserveFocus && typeof $prevFilter.get(0).selectionStart === 'number'
				? $prevFilter.get(0).selectionStart
				: null;
		var preserveEnd =
			preserveFocus && typeof $prevFilter.get(0).selectionEnd === 'number'
				? $prevFilter.get(0).selectionEnd
				: null;

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var pageLen = parseInt($table.data('page-length'), 10) || 10;
		pageLen = Math.min(100, Math.max(1, pageLen));
		var seed = String($table.attr('data-od-dt-seed') || '');
		var formSelector = String($table.attr('data-search-form') || '#od-detail-filter-form');
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			pageLength: pageLen,
			lengthChange: false,
			dom: 'frtip',
			searching: true,
			searchDelay: 10000
		});
		var $wrapper = $table.closest('.dataTables_wrapper');
		$wrapper.find('.order-detail-length').remove();
		var $ctrl = $(
			'<div class="dataTables_length order-detail-length"><label>Show <input type="number" min="1" max="100" step="1" class="order-detail-page-size form-control form-control-sm"> entries</label></div>'
		);
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

		var $filter = $wrapper.find('.dataTables_filter input');
		if (preserveVal !== '' && !seed) {
			seed = preserveVal;
		}
		bindUniversalSearch({
			dt: dt,
			$filter: $filter,
			seed: seed,
			formSelector: formSelector,
			buildLookupUrl: function (lookup) {
				var params = new URLSearchParams();
				params.set('dt_q', lookup.value);
				params.set('via', 'dt');
				appendFormFilters(params, formSelector, ['order_status', 'row_limit']);
				return (
					($('#od-detail-filter-form').attr('action') || window.location.pathname) +
					'?' +
					params.toString()
				);
			},
			buildClearUrl: function () {
				var params = new URLSearchParams();
				appendFormFilters(params, formSelector, [
					'return_from',
					'return_to',
					'order_status',
					'row_limit'
				]);
				var base = $('#od-detail-filter-form').attr('action') || window.location.pathname;
				var q = params.toString();
				return q ? base + '?' + q : base;
			}
		});
		if (preserveFocus) {
			restoreFilterCaret($filter.get(0), preserveStart, preserveEnd);
		}
	};

	window.initOrderReadyDataTable = function () {
		var $table = $('table#example.order-ready-table');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		var $prevFilter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		var preserveVal = $prevFilter.length ? String($prevFilter.val() || '') : '';

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var pageLen = parseInt($table.data('page-length'), 10) || 10;
		var seed = String($table.attr('data-od-dt-seed') || '');
		var formSelector = String($table.attr('data-search-form') || '#rd-ready-filter-form');
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			pageLength: pageLen,
			searching: true,
			searchDelay: 10000
		});
		var $filter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		if (preserveVal !== '' && !seed) {
			seed = preserveVal;
		}
		bindUniversalSearch({
			dt: dt,
			$filter: $filter,
			seed: seed,
			formSelector: formSelector,
			buildLookupUrl: function (lookup) {
				var params = new URLSearchParams();
				params.set('dt_q', lookup.value);
				params.set('via', 'dt');
				var base = $('#rd-ready-filter-form').attr('action') || window.location.pathname;
				return base + '?' + params.toString();
			},
			buildClearUrl: function () {
				var params = new URLSearchParams();
				appendFormFilters(params, formSelector, ['return_from', 'return_to']);
				var base = $('#rd-ready-filter-form').attr('action') || window.location.pathname;
				var q = params.toString();
				return q ? base + '?' + q : base;
			}
		});
	};

	function orderStatusFormAction() {
		var $form = $('#os-status-filter-form');
		return ($form.length && $form.attr('action')) || window.location.pathname;
	}

	function orderStatusScope() {
		var $scope = $('#os-scope-input');
		var v = $scope.length ? String($scope.val() || '').toLowerCase() : '';
		return v === 'delivered' ? 'delivered' : 'current';
	}

	window.initOrderListDataTable = function () {
		var $table = $('table#example[data-order-list-table]');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}

		var $prevFilter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		var preserveVal = $prevFilter.length ? String($prevFilter.val() || '') : '';
		var preserveFocus = $prevFilter.length && document.activeElement === $prevFilter.get(0);
		var preserveStart =
			preserveFocus && typeof $prevFilter.get(0).selectionStart === 'number'
				? $prevFilter.get(0).selectionStart
				: null;
		var preserveEnd =
			preserveFocus && typeof $prevFilter.get(0).selectionEnd === 'number'
				? $prevFilter.get(0).selectionEnd
				: null;

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		var viaDt = String($table.attr('data-os-via-dt') || '') === '1';
		var seed = String($table.attr('data-os-dt-seed') || '');
		if (!seed && viaDt === false && preserveVal) {
			seed = '';
		}
		var dt = $table.DataTable({
			pagingType: 'full_numbers',
			searching: true,
			searchDelay: 10000
		});

		var $filter = $table.closest('.dataTables_wrapper').find('.dataTables_filter input');
		var effectiveSeed = seed || (viaDt ? seed : '');
		if (preserveVal !== '' && !effectiveSeed) {
			// Keep typed value during reinit without forcing server seed.
			$filter.val(preserveVal);
			dt.search(preserveVal).draw(false);
			$filter.val(preserveVal);
		}

		bindUniversalSearch({
			dt: dt,
			$filter: $filter,
			seed: viaDt ? seed : '',
			formSelector: '#os-status-filter-form',
			offlineLookup: function (lookup) {
				if (
					window.DarziOffline &&
					window.DarziOffline.lists &&
					typeof window.DarziOffline.lists.applyOrderStatusFilters === 'function'
				) {
					window.DarziOffline.lists.applyOrderStatusFilters(
						orderStatusScope(),
						{
							customer_name: '',
							order_no: '',
							naap_no: '',
							mobile_no: '',
							stage: '',
							dt_q: lookup.value
						},
						{ keepSearch: String(lookup.value || '') }
					);
				}
			},
			buildLookupUrl: function (lookup) {
				var params = new URLSearchParams();
				params.set('scope', orderStatusScope());
				params.set('dt_q', lookup.value);
				params.set('via', 'dt');
				appendFormFilters(params, '#os-status-filter-form', ['stage']);
				return orderStatusFormAction() + '?' + params.toString();
			},
			buildClearUrl: function () {
				var params = new URLSearchParams();
				params.set('scope', orderStatusScope());
				appendFormFilters(params, '#os-status-filter-form', [
					'return_from',
					'return_to',
					'stage'
				]);
				return orderStatusFormAction() + '?' + params.toString();
			}
		});

		if (preserveFocus) {
			restoreFilterCaret($filter.get(0), preserveStart, preserveEnd);
		}
	};
})(window, window.jQuery);
