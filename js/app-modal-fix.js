(function (window, $) {
	'use strict';

	if (!$) {
		return;
	}

	function visibleModals() {
		return $('.modal.show, .modal[style*="display: block"]').filter(':visible');
	}

	function cleanupModalState() {
		if (visibleModals().length === 0) {
			$('.modal-backdrop').remove();
			$('body').removeClass('modal-open').css({
				paddingRight: '',
				overflow: ''
			});
		}
	}

	function dedupeModal($modal) {
		var id = $modal.attr('id');
		if (!id) {
			return $modal;
		}
		var $all = $('div#' + id);
		if ($all.length <= 1) {
			return $modal;
		}
		var $inMain = $all.filter(function () {
			return $(this).closest('.main-content').length > 0;
		});
		var $keep = $inMain.length ? $inMain.last() : $all.last();
		$all.not($keep).remove();
		return $('div#' + id).first();
	}

	function ensureModalOnBody($modal) {
		if (!$modal || !$modal.length) {
			return $modal;
		}
		$modal = dedupeModal($modal);
		if (!$modal.parent().is('body')) {
			$modal.appendTo(document.body);
		}
		return $modal;
	}

	function parkAllModals() {
		$('.modal').each(function () {
			ensureModalOnBody($(this));
		});
	}

	function hideAllModals() {
		$('.modal.show').each(function () {
			var $modal = $(this);
			if ($.fn.modal) {
				try {
					$modal.modal('hide');
				} catch (e) {
					/* ignore */
				}
			}
			$modal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
		});
		cleanupModalState();
	}

	window.AppModal = {
		ensureOnBody: ensureModalOnBody,
		parkAll: parkAllModals,
		cleanup: cleanupModalState,
		hideAll: hideAllModals,
		show: function (selector) {
			var $modal = ensureModalOnBody($(selector));
			if (!$modal.length) {
				return;
			}
			if (visibleModals().length === 0) {
				$('.modal-backdrop').remove();
				$('body').removeClass('modal-open');
			}
			if ($.fn.modal) {
				$modal.modal({
					backdrop: true,
					keyboard: true,
					show: true
				});
				return;
			}
			$modal.addClass('show').css('display', 'block').attr('aria-hidden', 'false');
			if (!$('.modal-backdrop').length) {
				$('body').append('<div class="modal-backdrop fade show"></div>').addClass('modal-open');
			}
		},
		hide: function (selector) {
			var $modal = $(selector);
			if ($.fn.modal && $modal.length) {
				try {
					$modal.modal('hide');
				} catch (e) {
					/* ignore */
				}
			}
			$modal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
			cleanupModalState();
		}
	};

	$(document).on('show.bs.modal', '.modal', function () {
		ensureModalOnBody($(this));
		if ($('.modal-backdrop').length > 1) {
			$('.modal-backdrop').not(':last').remove();
		}
	});

	$(document).on('hidden.bs.modal', '.modal', function () {
		cleanupModalState();
	});

	$(document).on('click', '.modal-backdrop', function () {
		if (visibleModals().length === 0) {
			$(this).remove();
			$('body').removeClass('modal-open');
		}
	});

	$(function () {
		parkAllModals();
		cleanupModalState();
	});

	function purgeOrphanModals() {
		$('.modal').each(function () {
			if ($(this).closest('.main-content').length === 0) {
				$(this).remove();
			}
		});
	}

	function navigateFromModalLink(link, event) {
		var href = link.getAttribute('href');
		if (!href || href === '#' || href.indexOf('javascript:') === 0) {
			return false;
		}
		if (event) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
		hideAllModals();
		window.location.href = href;
		return true;
	}

	$(document).on('click', 'a[data-app-modal-nav="true"], .modal a[href]:not([href="#"]):not([data-toggle="modal"]):not([data-dismiss="modal"])', function (e) {
		if (this.getAttribute('data-toggle') === 'tab' || this.getAttribute('data-toggle') === 'pill') {
			return;
		}
		navigateFromModalLink(this, e);
	});

	$(document).on('app:ajax-loaded', function () {
		hideAllModals();
		purgeOrphanModals();
		parkAllModals();
		cleanupModalState();
	});
}(window, window.jQuery));
