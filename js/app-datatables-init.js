(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	function destroyStaleDataTables() {
		if (!$.fn.DataTable) {
			return;
		}
		$('table#example').each(function () {
			if ($.fn.DataTable.isDataTable(this)) {
				try {
					$(this).DataTable().destroy();
				} catch (e) {
					/* ignore stale instance */
				}
			}
		});
	}

	var reinitLock = false;
	var reinitQueued = false;

	function runReinitAllDataTables() {
		if (!$.fn.DataTable) {
			return;
		}
		if (reinitLock) {
			reinitQueued = true;
			return;
		}
		reinitLock = true;
		reinitQueued = false;

		try {
			destroyStaleDataTables();

			if ($('table#example.order-detail-table').length && typeof window.initOrderDetailDataTable === 'function') {
				window.initOrderDetailDataTable();
			}
			if ($('table#example.order-ready-table').length && typeof window.initOrderReadyDataTable === 'function') {
				window.initOrderReadyDataTable();
			}
			if ($('table#example[data-order-list-table]').length && typeof window.initOrderListDataTable === 'function') {
				window.initOrderListDataTable();
			}
			if ($('table#example[data-transfer-table]').length && typeof window.initTransferBulkTable === 'function') {
				window.initTransferBulkTable();
			}
			if ($('table#example[data-master-list]').length && typeof window.initMasterListDataTable === 'function') {
				window.initMasterListDataTable();
			}
			if ($('table#example[data-customer-list-table]').length && typeof window.initCustomerListDataTable === 'function') {
				window.initCustomerListDataTable();
			}

			$('table#example')
				.not('.order-detail-table')
				.not('.order-ready-table')
				.not('[data-transfer-table]')
				.not('[data-master-list]')
				.not('[data-customer-list-table]')
				.not('[data-order-list-table]')
				.each(function () {
					if ($.fn.DataTable.isDataTable(this)) {
						return;
					}
					$(this).DataTable({
						pagingType: 'full_numbers',
						pageLength: 25,
						lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
						lengthChange: true,
						searching: true,
						info: true,
						retrieve: true
					});
				});
		} finally {
			reinitLock = false;
			if (reinitQueued) {
				reinitQueued = false;
				window.setTimeout(runReinitAllDataTables, 0);
			}
		}
	}

	window.reinitAllDataTables = runReinitAllDataTables;

	window.destroyStaleDataTables = destroyStaleDataTables;

	var scheduleToken = 0;
	function scheduleReinit() {
		var token = ++scheduleToken;
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				if (token !== scheduleToken) {
					return;
				}
				if ($('table#example').length) {
					window.reinitAllDataTables();
				}
			});
		});
	}

	$(function () {
		scheduleReinit();
	});

	$(document).on('app:ajax-loaded', scheduleReinit);
}(window, window.jQuery));
