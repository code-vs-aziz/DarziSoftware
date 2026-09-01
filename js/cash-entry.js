(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	var cashAlertTimer = null;

	function showCashAlert($alert, type, message) {
		clearTimeout(cashAlertTimer);
		$('.cash-flash-alert').hide();
		$alert
			.removeClass('alert-success alert-danger')
			.addClass(type)
			.text(message)
			.fadeIn(150);
		cashAlertTimer = setTimeout(function () {
			$alert.fadeOut(250);
		}, 5000);
	}

	window.get_order_number = function () {
		var customer = document.getElementById('Customer');
		var orderWindow = document.getElementById('order_window');
		if (!customer || !orderWindow) {
			return;
		}
		orderWindow.style.display = customer.value !== '' ? 'block' : 'none';
	};

	function bindCashEntryPage() {
		var $form = $('#cash-entry-form');
		if (!$form.length) {
			return;
		}

		if ($.fn.select2) {
			$('#Customer, #employee').each(function () {
				var $el = $(this);
				if ($el.data('select2')) {
					$el.select2('destroy');
				}
				$el.select2({ width: '100%' });
			});
		}

		if ($.fn.setUrduInput && $('#Remarks').length) {
			$('#Remarks').setUrduInput();
		}

		$(document).off('submit.cashEntry').on('submit.cashEntry', '#cash-entry-form', function (e) {
			e.preventDefault();
			var $button = $form.find('button[type="submit"]');
			var $alert = $('#cash-entry-alert');
			var savedLabel = $button.find('#submit-button-amount').text() || 'Submit';
			if (!window.confirm('Are you sure you want to submit this Cash Payment/Receipt?')) {
				return;
			}
			$button.prop('disabled', true).find('#submit-button-amount').text('Saving...');
			$.ajax({
				type: 'POST',
				url: $form.attr('action'),
				data: $form.serialize(),
				dataType: 'json',
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			}).done(function (response) {
				if (response && response.success) {
					showCashAlert($alert, 'alert-success', response.message || (window.APP_LANG && window.APP_LANG.saved_success) || 'Saved successfully');
					$form.find('input[name="paid"], input[name="Remarks"], input[name="order_number"]').val('');
					$('#order_window').hide();
				} else {
					showCashAlert($alert, 'alert-danger', (response && response.message) || 'Unable to save entry');
				}
			}).fail(function () {
				showCashAlert($alert, 'alert-danger', 'Unable to save entry. Please try again.');
			}).always(function () {
				$button.prop('disabled', false).find('#submit-button-amount').text(savedLabel);
			});
		});

		$('#employee').off('change.cashEntry').on('change.cashEntry', function () {
			var employee = $(this).val();
			if (!employee) {
				return;
			}
			$.ajax({
				type: 'POST',
				url: $form.data('totalWagesUrl') || $form.attr('data-total-wages-url') || '',
				data: { employee: employee },
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			}).done(function (html) {
				$('#twages').val(html);
			});
		});
	}

	window.initCashEntryPage = bindCashEntryPage;

	$(function () {
		bindCashEntryPage();
	});

	$(document).on('app:ajax-loaded', function () {
		bindCashEntryPage();
	});
}(window, window.jQuery));
