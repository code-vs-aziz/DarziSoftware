(function (window, document, $) {
	'use strict';

	/**
	 * Auto plain-text WhatsApp after order create is disabled.
	 * Staff must pick / enter a number and send the order PDF from the print page.
	 */
	function runAutoDispatch() {
		var cfg = window.ORDER_PRINT_NOTIFY || {};
		if (!cfg.autoDispatch) {
			return;
		}
		// Kept only for emergency re-enable; default autoDispatch is always false.
		if (!cfg.orderId || !cfg.endpoint || !$ || !$.post) {
			return;
		}
		$.post(cfg.endpoint, {
			order_id: cfg.orderId,
			event_type: 'order_booked'
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		setTimeout(runAutoDispatch, 400);
	});
})(window, document, window.jQuery);
