(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	var initSeq = 0;

	// Never show the DataTables "Cannot reinitialise" alert on transfer pages.
	if ($.fn.dataTable && $.fn.dataTable.ext) {
		$.fn.dataTable.ext.errMode = 'none';
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
		if (!val) {
			var cookieName = $('meta[name="csrf-cookie-name"]').attr('content') || 'dz_csrf_cookie';
			var m = document.cookie.match(
				new RegExp('(?:^|; )' + cookieName.replace(/[$()*+.?[\\\]^{|}]/g, '\\$&') + '=([^;]*)')
			);
			if (m && m[1]) {
				val = decodeURIComponent(m[1]);
			}
		}
		var out = {};
		if (val) {
			out[name] = val;
		}
		return out;
	}

	function safeDestroy($table) {
		if (!$table.length || !$.fn.DataTable) {
			return;
		}
		if ($.fn.DataTable.isDataTable($table[0])) {
			try {
				$table.DataTable().clear().destroy();
			} catch (e) {
				try {
					$table.DataTable().destroy();
				} catch (e2) {
					/* ignore */
				}
			}
		}
		// Strip leftover DataTables wrapper if destroy left the DOM messy.
		var $wrap = $table.parent();
		if ($wrap.hasClass('dataTables_wrapper')) {
			$wrap.before($table);
			$wrap.remove();
		}
		$table.removeClass('dataTable no-footer');
		$table.removeAttr('role').removeAttr('aria-describedby');
	}

	function fillClientRows(rows) {
		var dt = getTransferBulkDataTable();
		if (!dt) {
			return false;
		}
		try {
			dt.clear();
			dt.rows.add(rows || []);
			dt.draw(false);
			return true;
		} catch (e) {
			return false;
		}
	}

	window.initTransferBulkTable = function () {
		var $table = transferBulkTableEl();
		if (!$table.length || !$.fn.DataTable) {
			return;
		}

		var mySeq = ++initSeq;
		var config = readBulkFormConfig();
		var bulkEnabled = config && config.enabled;
		var sspUrl = String($table.attr('data-ssp-url') || '');
		var useSsp = String($table.attr('data-ssp')) === '1' && sspUrl !== '';

		var colCount = $table.find('thead th').length;
		var actionColStart = colCount - 3;
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
			order: [],
			retrieve: true
		};

		// Only use IndexedDB when explicitly offline — "checking" must hit live SSP
		// or transferred rows stay on screen until a manual refresh.
		var connState =
			window.DarziOffline &&
			window.DarziOffline.connectivity &&
			typeof window.DarziOffline.connectivity.getState === 'function'
				? window.DarziOffline.connectivity.getState()
				: null;
		var offline = !!(connState && connState.status === 'offline');
		var dept = String($table.attr('data-dept') || '').toUpperCase();

		function finishInit(extraOpts) {
			if (mySeq !== initSeq) {
				return null;
			}
			safeDestroy($table);
			if (mySeq !== initSeq) {
				return null;
			}
			var opts = $.extend(true, {}, dtOptions, extraOpts || {});
			if (bulkEnabled) {
				opts.columnDefs = [{ orderable: false, targets: 0 }];
			}
			var dt;
			try {
				dt = $table.DataTable(opts);
			} catch (err) {
				safeDestroy($table);
				try {
					dt = $table.DataTable(opts);
				} catch (err2) {
					return null;
				}
			}
			try {
				$(dt.column(empCol).header()).attr('title', 'Sort by employee');
			} catch (e) {
				/* ignore */
			}
			return dt;
		}

		if (useSsp) {
			dtOptions.columns = [];
			for (var i = 0; i < colCount; i++) {
				var canOrder = i >= firstDataCol && i < actionColStart;
				dtOptions.columns.push({ data: i, orderable: canOrder });
			}
			if (
				offline &&
				window.DarziOffline &&
				window.DarziOffline.lists &&
				typeof window.DarziOffline.lists.transferRows === 'function'
			) {
				// Sync shell first so a second reinit cannot race an empty table.
				finishInit({
					serverSide: false,
					processing: true,
					data: []
				});
				window.DarziOffline.lists.transferRows(dept).then(function (rows) {
					if (mySeq !== initSeq) {
						return;
					}
					if (!fillClientRows(rows || [])) {
						finishInit({
							serverSide: false,
							processing: false,
							data: rows || []
						});
					}
				});
				return;
			}
			dtOptions.processing = true;
			dtOptions.serverSide = true;
			dtOptions.ajax = {
				url: sspUrl,
				type: 'POST',
				cache: false,
				data: function (d) {
					var csrf = csrfFields();
					Object.keys(csrf).forEach(function (k) {
						d[k] = csrf[k];
					});
					d.dept = dept;
					d._ts = Date.now();
				},
				error: function () {
					if (mySeq !== initSeq) {
						return;
					}
					if (
						window.DarziOffline &&
						window.DarziOffline.lists &&
						typeof window.DarziOffline.lists.transferRows === 'function'
					) {
						window.DarziOffline.lists.transferRows(dept).then(function (rows) {
							if (mySeq !== initSeq) {
								return;
							}
							finishInit({
								serverSide: false,
								processing: false,
								data: rows || []
							});
						});
					}
				}
			};
		}

		finishInit();
	};

	function refreshOfflineListsQuiet() {
		if (
			window.DarziOffline &&
			window.DarziOffline.lists &&
			typeof window.DarziOffline.lists.warm === 'function' &&
			window.DarziOffline.connectivity &&
			window.DarziOffline.connectivity.isOnline()
		) {
			window.DarziOffline.lists.warm(true).catch(function () {
				/* quiet */
			});
		}
	}

	function bindTransferBulkHandlers() {
		$(document)
			.off('change.transferBulk', '#select-all-transfer')
			.on('change.transferBulk', '#select-all-transfer', function () {
				var dt = getTransferBulkDataTable();
				if (!dt) {
					return;
				}
				var checked = this.checked;
				dt.rows({ page: 'current' }).nodes().to$().find('.bulk-transfer-cb').prop('checked', checked);
			});

		$(document)
			.off('click.transferBulk', '#bulk-transfer-btn')
			.on('click.transferBulk', '#bulk-transfer-btn', function () {
				var config = readBulkFormConfig();
				if (!config || !config.enabled) {
					return;
				}
				var $btn = $(this);
				if ($btn.data('bulkBusy') || config.$form.data('bulkBusy')) {
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
				// Prevent double-submit (was causing success + "could not complete" together).
				$btn.data('bulkBusy', 1).prop('disabled', true);
				config.$form.data('bulkBusy', 1);
				$('#transfer_items_json').val(JSON.stringify(items));
				config.$form.trigger('submit');
			});
	}

	var statusBound = false;
	function bindConnectivityReinit() {
		if (statusBound) {
			return;
		}
		if (!window.DarziOffline || !window.DarziOffline.connectivity || !window.DarziOffline.connectivity.onChange) {
			return;
		}
		statusBound = true;
		var last = '';
		window.DarziOffline.connectivity.onChange(function (st) {
			var next = st && st.status ? st.status : '';
			if (last === 'offline' && next === 'online' && transferBulkTableEl().length) {
				window.initTransferBulkTable();
				refreshOfflineListsQuiet();
			}
			last = next;
		});
	}

	$(function () {
		bindTransferBulkHandlers();
		bindConnectivityReinit();
		// After transfer/return success, refresh lists so offline cache matches server.
		if (document.querySelector('[data-flash-once], .alert-success')) {
			refreshOfflineListsQuiet();
		}
	});
	$(document).on('app:ajax-loaded', function () {
		bindTransferBulkHandlers();
		bindConnectivityReinit();
		if (document.querySelector('[data-flash-once], .alert-success')) {
			refreshOfflineListsQuiet();
		}
	});
})(window, window.jQuery);
