(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	window.initMasterListDataTable = function () {
		if (!$.fn.DataTable) {
			return;
		}

		$('table#example[data-master-list]').each(function () {
			var $table = $(this);
			if ($.fn.DataTable.isDataTable(this)) {
				$table.DataTable().destroy();
			}
			$table.DataTable({
				pagingType: 'full_numbers',
				pageLength: 10,
				lengthChange: true,
				searching: true,
				info: true
			});
		});
	};

}(window, window.jQuery));
