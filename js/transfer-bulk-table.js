(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	function transferBulkTableEl() {
		return $('table#example[data-transfer-table]');
	}

	function getTransferBulkDataTable() {
		var $table = transferBulkTableEl();
		if (!$table.length || !$.fn.DataTable || !$.fn.DataTable.isDataTable($table[0])) {
			return null;
		}
		return $table.DataTable();
	}

	function readBulkFormConfig() {
		var $form = $('#bulk-transfer-form');
		if (!$form.length) {
			return null;
		}
		return {
			$form: $form,
			enabled: String($form.attr('data-bulk-enabled')) === '1',
			confirmMsg: String($form.attr('data-confirm-msg') || ''),
			noneMsg: String($form.attr('data-none-msg') || 'Select at least one order to transfer.')
		};
	}

	window.initTransferBulkTable = function () {
		var $table = transferBulkTableEl();
		if (!$table.length || !$.fn.DataTable) {
			return;
		}

		var config = readBulkFormConfig();
		var bulkEnabled = config && config.enabled;

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}

		var dtOptions = {
			pagingType: 'full_numbers',
			pageLength: 10,
			lengthChange: true,
			searching: true,
			info: true
		};
		if (bulkEnabled) {
			dtOptions.columnDefs = [{ orderable: false, targets: 0 }];
		}

		$table.DataTable(dtOptions);
	};

	function bindTransferBulkHandlers() {
		$(document).off('change.transferBulk', '#select-all-transfer').on('change.transferBulk', '#select-all-transfer', function () {
			var dt = getTransferBulkDataTable();
			if (!dt) {
				return;
			}
			var checked = this.checked;
			dt.rows({ page: 'current' }).nodes().to$().find('.bulk-transfer-cb').prop('checked', checked);
		});

		$(document).off('click.transferBulk', '#bulk-transfer-btn').on('click.transferBulk', '#bulk-transfer-btn', function () {
			var config = readBulkFormConfig();
			if (!config || !config.enabled) {
				return;
			}

			var items = [];
			$('.bulk-transfer-cb:checked').each(function () {
				var $cb = $(this);
				items.push({
					trnno: $cb.attr('data-trnno'),
					order: $cb.attr('data-order'),
					sr: $cb.attr('data-sr')
				});
			});

			if (items.length === 0) {
				window.alert(config.noneMsg);
				return;
			}
			if (!window.confirm(config.confirmMsg.replace('%d', items.length))) {
				return;
			}

			$('#transfer_items_json').val(JSON.stringify(items));
			config.$form[0].submit();
		});
	}

	bindTransferBulkHandlers();
}(window, window.jQuery));
