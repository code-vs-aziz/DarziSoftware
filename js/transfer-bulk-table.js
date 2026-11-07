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

	function csrfFields() {
		var name = $('meta[name="csrf-token-name"]').attr('content') || 'dz_csrf_token';
		var val = $('meta[name="csrf-token-value"]').attr('content') || '';
		var out = {};
		if (val) {
			out[name] = val;
		}
		return out;
	}

	window.initTransferBulkTable = function () {
		var $table = transferBulkTableEl();
		if (!$table.length || !$.fn.DataTable) {
			return;
		}

		var config = readBulkFormConfig();
		var bulkEnabled = config && config.enabled;
		var sspUrl = String($table.attr('data-ssp-url') || '');
		var useSsp = String($table.attr('data-ssp')) === '1' && sspUrl !== '';

		if ($.fn.DataTable.isDataTable($table[0])) {
			$table.DataTable().destroy();
		}

		var colCount = $table.find('thead th').length;
		var actionColStart = colCount - 3; // View / Transfer / Return
		var firstDataCol = bulkEnabled ? 1 : 0;
		var empCol = bulkEnabled ? 2 : 1;
		var dtOptions = {
			pagingType: 'full_numbers',
			pageLength: 25,
			lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
			lengthChange: true,
			searching: true,
			info: true,
			ordering: true,
			order: [] // no default sort until user clicks a column (e.g. Employee)
		};

		if (useSsp) {
			dtOptions.processing = true;
			dtOptions.serverSide = true;
			dtOptions.ajax = {
				url: sspUrl,
				type: 'POST',
				data: function (d) {
					var csrf = csrfFields();
					Object.keys(csrf).forEach(function (k) {
						d[k] = csrf[k];
					});
					d.dept = String($table.attr('data-dept') || '');
				}
			};
			dtOptions.columns = [];
			for (var i = 0; i < colCount; i++) {
				var canOrder = i >= firstDataCol && i < actionColStart;
				dtOptions.columns.push({ data: i, orderable: canOrder });
			}
		}

		if (bulkEnabled) {
			dtOptions.columnDefs = [{ orderable: false, targets: 0 }];
		}

		var dt = $table.DataTable(dtOptions);
		/* Make Employee header clearly clickable for sort (old ERP behaviour). */
		try {
			$(dt.column(empCol).header()).attr('title', 'Sort by employee');
		} catch (e) { /* ignore */ }
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

			if (!items.length) {
				window.alert(config.noneMsg);
				return;
			}
			var confirmText = String(config.confirmMsg || '');
			if (confirmText.indexOf('%d') !== -1) {
				confirmText = confirmText.replace(/%d/g, String(items.length));
			} else if (confirmText) {
				confirmText = confirmText + ' (' + items.length + ')';
			} else {
				confirmText = 'You are about to transfer ' + items.length + ' order(s).';
			}
			if (!window.confirm(confirmText)) {
				return;
			}
			$('#transfer_items_json').val(JSON.stringify(items));
			config.$form.trigger('submit');
		});
	}

	$(function () {
		bindTransferBulkHandlers();
	});

}(window, window.jQuery));
