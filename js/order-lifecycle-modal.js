(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	function openOrderLifecycle(orderId, historyUrl, modalTitle, loadError, loadingHtml) {
		var $modal = $('#orderLifecycleModal');
		var $body = $('#orderLifecycleModalBody');
		if (!$modal.length) {
			return;
		}
		$('#orderLifecycleModalLabel').text(modalTitle + ' — #' + orderId);
		$body.html(loadingHtml);
		if (window.AppModal) {
			window.AppModal.show('#orderLifecycleModal');
		} else if ($.fn.modal) {
			$modal.modal('show');
		}
		$.ajax({
			url: historyUrl + '/' + orderId,
			method: 'GET',
			dataType: 'html',
			cache: false
		}).done(function (html) {
			$body.html(html);
		}).fail(function () {
			$body.html('<p class="text-center text-danger py-3 mb-0">' + loadError + '</p>');
		});
	}

	if (!window.orderLifecycleHistoryBound) {
		window.orderLifecycleHistoryBound = true;
		$(document).on('click', '.btn-order-lifecycle', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var orderId = parseInt($(this).data('order-id'), 10);
			var cfg = window.ORDER_LIFECYCLE_CFG || {};
			if (orderId > 0 && cfg.historyUrl) {
				openOrderLifecycle(
					orderId,
					cfg.historyUrl,
					cfg.modalTitle || 'History',
					cfg.loadError || 'Could not load history.',
					cfg.loadingHtml || '<p class="text-center text-muted py-3 mb-0">Loading…</p>'
				);
			}
		});
	}
}(window, window.jQuery));
