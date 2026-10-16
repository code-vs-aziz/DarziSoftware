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

	window.initOrderListDataTable = function () {
		var $table = $('table#example[data-order-list-table]');
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}
		$table.DataTable({
			pagingType: 'full_numbers'
		});
	};

}(window, window.jQuery));
