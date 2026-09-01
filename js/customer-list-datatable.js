(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	window.initCustomerListDataTable = function () {
		if (!$.fn.DataTable) {
			return;
		}

		$('table#example[data-customer-list-table]').each(function () {
			var $table = $(this);
			if ($.fn.DataTable.isDataTable(this)) {
				$table.DataTable().destroy();
			}
			$table.DataTable({
				paging: false,
				pagingType: 'full_numbers',
				searching: false,
				info: true,
				language: { emptyTable: 'No data — use Search to load customers.' }
			});
		});
	};

}(window, window.jQuery));
