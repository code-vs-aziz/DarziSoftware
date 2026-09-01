(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	var pendingCallback = null;

	function getModal() {
		return $('#dzConfirmModal');
	}

	function hideModal() {
		var $modal = getModal();
		if (window.AppModal) {
			window.AppModal.hide('#dzConfirmModal');
		} else if ($.fn.modal) {
			$modal.modal('hide');
		} else {
			$modal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
		}
	}

	function showModal() {
		var $modal = getModal();
		if (!$modal.length) {
			return false;
		}
		if (window.AppModal) {
			window.AppModal.show('#dzConfirmModal');
		} else if ($.fn.modal) {
			$modal.modal('show');
		} else {
			$modal.addClass('show').css('display', 'block').attr('aria-hidden', 'false');
		}
		return true;
	}

	window.AppConfirm = {
		show: function (options) {
			options = options || {};
			var $modal = getModal();
			if (!$modal.length) {
				if (options.message && window.confirm(options.message)) {
					if (options.onConfirm) {
						options.onConfirm();
					}
				}
				return;
			}

			var title = options.title || ((window.APP_LANG && APP_LANG.app_confirm) ? APP_LANG.app_confirm : 'Confirm');
			var message = options.message || '';
			var okLabel = options.okLabel || ((window.APP_LANG && APP_LANG.app_confirm) ? APP_LANG.app_confirm : 'Confirm');
			var cancelLabel = options.cancelLabel || ((window.APP_LANG && APP_LANG.app_cancel) ? APP_LANG.app_cancel : 'Cancel');

			$('#dzConfirmModalTitle').text(title);
			$('#dzConfirmModalMessage').text(message);
			$('#dzConfirmOk').text(okLabel);
			$('#dzConfirmCancel').text(cancelLabel);

			pendingCallback = options.onConfirm || null;
			showModal();
		}
	};

	$(document).on('click', '#dzConfirmOk', function () {
		hideModal();
		if (pendingCallback) {
			var fn = pendingCallback;
			pendingCallback = null;
			fn();
		}
	});

	$(document).on('click', '#dzConfirmCancel, #dzConfirmModal [data-dismiss="modal"]', function () {
		pendingCallback = null;
		hideModal();
	});

	$(function () {
		if (window.AppModal) {
			window.AppModal.ensureOnBody(getModal());
		}
	});
}(window, window.jQuery));
