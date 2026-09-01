(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	function readDataAttr(el, name) {
		var value = el.getAttribute('data-' + name);
		return value === null ? '' : value;
	}

	function showPredefinedEditModal() {
		if (window.AppModal) {
			window.AppModal.show('#predefinedEditModal');
			return;
		}
		$('#predefinedEditModal').modal('show');
	}

	function hidePredefinedEditModal() {
		if (window.AppModal) {
			window.AppModal.hide('#predefinedEditModal');
			return;
		}
		$('#predefinedEditModal').modal('hide');
	}

	window.initConfigPredefinedPage = function () {
		if (window.AppModal) {
			window.AppModal.parkAll();
			window.AppModal.cleanup();
		}
		if ($.fn.setUrduInput) {
			$('#address_urdu, #predefined_label_text, #predefined_insert_text, #edit_predefined_label_text, #edit_predefined_insert_text').each(function () {
				var $field = $(this);
				if (!$field.data('urduBound')) {
					$field.setUrduInput();
					$field.data('urduBound', 1);
				}
			});
		}
		$('.config-alert-auto').delay(5000).fadeOut(400);
	};

	$(document).on('click', '.predefined-edit-btn', function (e) {
		e.preventDefault();
		var btn = this;
		$('#edit_predefined_id').val(readDataAttr(btn, 'id'));
		$('#edit_predefined_label_text').val(readDataAttr(btn, 'label'));
		$('#edit_predefined_insert_text').val(readDataAttr(btn, 'insert'));
		$('#edit_predefined_row_group').val(readDataAttr(btn, 'row') || '1');
		if ($.fn.setUrduInput) {
			$('#edit_predefined_label_text, #edit_predefined_insert_text').each(function () {
				var $field = $(this);
				if (!$field.data('urduBound')) {
					$field.setUrduInput();
					$field.data('urduBound', 1);
				}
			});
		}
		showPredefinedEditModal();
	});

	$(document).on('click', '#predefinedEditModal [data-dismiss="modal"]', function () {
		hidePredefinedEditModal();
	});

	$(document).on('hidden.bs.modal', '#predefinedEditModal', function () {
		if (window.AppModal) {
			window.AppModal.cleanup();
		}
	});

	$(function () {
		if ($('#config-order-predefined').length || $('#config-business').length) {
			window.initConfigPredefinedPage();
		}
	});

	$(document).on('app:ajax-loaded', function () {
		if ($('#config-order-predefined').length || $('#config-business').length) {
			window.initConfigPredefinedPage();
		}
	});
}(window, window.jQuery));
