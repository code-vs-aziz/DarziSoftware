(function (window, document) {
	'use strict';

	var cfg = window.ORDER_FORM_CFG || {};
	var pendingCopyPayload = null;

	function lang(key, fallback) {
		if (window.APP_LANG && window.APP_LANG[key]) {
			return window.APP_LANG[key];
		}
		return fallback || key;
	}

	function langf(key, fallback, args) {
		var text = lang(key, fallback);
		if (!args || !args.length) {
			return text;
		}
		for (var i = 0; i < args.length; i++) {
			text = text.replace('%d', String(args[i]));
		}
		return text;
	}

	function isSuitLineComplete(index) {
		var design = byId('Designtype' + index);
		var suit = byId('suit' + index);
		if (!design || !suit) {
			return false;
		}
		var designVal = String(design.value || '');
		var suitVal = String(suit.value || '');
		return designVal !== '' && designVal !== '0' && suitVal !== '' && suitVal !== '0';
	}

	function expandSuitLine(line) {
		if (!line) {
			return;
		}
		line.classList.add('is-expanded');
		line.classList.remove('is-collapsed');
		var head = line.querySelector('.order-suit-line__head');
		if (head) {
			head.setAttribute('aria-expanded', 'true');
		}
		var status = line.querySelector('.order-suit-line__status');
		if (status && !line.classList.contains('is-done')) {
			status.textContent = lang('order_suit_status_active', 'In progress');
		}
	}

	function collapseSuitLine(line) {
		if (!line) {
			return;
		}
		line.classList.remove('is-expanded');
		line.classList.add('is-collapsed');
		var head = line.querySelector('.order-suit-line__head');
		if (head) {
			head.setAttribute('aria-expanded', 'false');
		}
		var status = line.querySelector('.order-suit-line__status');
		if (status && !line.classList.contains('is-done') && !line.classList.contains('is-locked')) {
			status.textContent = lang('order_suit_status_pending', 'Waiting');
		}
	}

	function updateSuitAccordionState(scope) {
		var root = scope || byId('maincat');
		if (!root) {
			return;
		}
		var accordion = root.querySelector('.order-suit-accordion');
		if (!accordion) {
			return;
		}
		var qty = parseInt(accordion.getAttribute('data-suit-qty'), 10) || 0;
		var lines = root.querySelectorAll('.order-suit-line');
		var completed = 0;
		var firstIncomplete = -1;

		for (var i = 0; i < lines.length; i++) {
			var idx = parseInt(lines[i].getAttribute('data-suit-index'), 10);
			var complete = isSuitLineComplete(idx);
			var statusEl = lines[i].querySelector('.order-suit-line__status');

			// Suits can be opened in any order — never lock later lines.
			lines[i].classList.remove('is-locked');

			if (complete) {
				completed++;
				lines[i].classList.add('is-done');
				if (statusEl) {
					statusEl.textContent = lang('order_suit_status_done', 'Complete');
				}
			} else {
				lines[i].classList.remove('is-done');
				if (firstIncomplete === -1) {
					firstIncomplete = idx;
				}
				if (lines[i].classList.contains('is-expanded')) {
					if (statusEl) {
						statusEl.textContent = lang('order_suit_status_active', 'In progress');
					}
				} else if (statusEl) {
					statusEl.textContent = lang('order_suit_status_pending', 'Waiting');
				}
			}
		}

		var fill = accordion.querySelector('.order-suit-progress__fill');
		var text = accordion.querySelector('.order-suit-progress__text');
		var pct = qty > 0 ? Math.round((completed / qty) * 100) : 0;
		if (fill) {
			fill.style.width = pct + '%';
		}
		var current = firstIncomplete === -1 ? qty : firstIncomplete + 1;
		if (text) {
			text.textContent = langf('order_suit_progress', 'Suit %d of %d', [current, qty]);
		}
		// Do not auto-collapse/expand suits — multiple can stay open until the user toggles them.
	}

	function initSuitAccordion(root) {
		var scope = root || byId('maincat');
		if (!scope) {
			return;
		}
		var accordion = scope.querySelector('.order-suit-accordion');
		if (!accordion) {
			return;
		}

		if (accordion.getAttribute('data-accordion-bound') !== '1') {
			accordion.setAttribute('data-accordion-bound', '1');

			accordion.addEventListener('click', function (event) {
				if (event.target.closest('.order-suit-line__copy')) {
					return;
				}

				var nextBtn = event.target.closest('.order-suit-next-btn');
				if (nextBtn) {
					var currentLine = nextBtn.closest('.order-suit-line');
					if (!currentLine) {
						return;
					}
					var idx = parseInt(currentLine.getAttribute('data-suit-index'), 10);
					var nextLine = scope.querySelector('.order-suit-line[data-suit-index="' + (idx + 1) + '"]');
					if (nextLine) {
						// Keep current suit open; just open/scroll to the next one.
						expandSuitLine(nextLine);
						if (nextLine.scrollIntoView) {
							nextLine.scrollIntoView({ behavior: 'smooth', block: 'start' });
						}
					}
					updateSuitAccordionState(scope);
					return;
				}

				var head = event.target.closest('.order-suit-line__head');
				if (!head) {
					return;
				}
				var line = head.closest('.order-suit-line');
				if (!line) {
					return;
				}
				// Toggle only the clicked suit — leave other suits as they are.
				if (line.classList.contains('is-expanded')) {
					collapseSuitLine(line);
				} else {
					expandSuitLine(line);
				}
				updateSuitAccordionState(scope);
			});

			accordion.addEventListener('keydown', function (event) {
				if (event.key !== 'Enter' && event.key !== ' ') {
					return;
				}
				var head = event.target.closest('.order-suit-line__head');
				if (!head || event.target.closest('.order-suit-line__copy')) {
					return;
				}
				event.preventDefault();
				head.click();
			});

			accordion.addEventListener('change', function (event) {
				var id = event.target && event.target.id ? event.target.id : '';
				if (id.indexOf('Designtype') === 0 || id.indexOf('suit') === 0) {
					updateSuitAccordionState(scope);
				}
			});
		}

		updateSuitAccordionState(scope);
	}

	function byId(id) {
		return document.getElementById(id);
	}

	function formatMoney(value) {
		var num = parseFloat(value);
		if (isNaN(num)) {
			return '0';
		}
		return String(Math.round(num));
	}

	function syncStickyPayment() {
		var total = byId('total_amount');
		var advance = byId('Advance');
		var discount = byId('Discount');
		var balance = byId('Balance');
		if (typeof bal === 'function') {
			bal();
		}
		var stickyTotal = byId('orderStickyTotal');
		var stickyAdvance = byId('orderStickyAdvance');
		var stickyDiscount = byId('orderStickyDiscount');
		var stickyBalance = byId('orderStickyBalance');
		if (stickyTotal && total) stickyTotal.textContent = formatMoney(total.value);
		if (stickyAdvance && advance) stickyAdvance.textContent = formatMoney(advance.value || 0);
		if (stickyDiscount && discount) stickyDiscount.textContent = formatMoney(discount.value || 0);
		if (stickyBalance && balance) stickyBalance.textContent = formatMoney(balance.value || 0);
	}

	function enhanceKolarPickers(root) {
		var scope = root || document;
		var options = scope.querySelectorAll('.order-kolar-option');
		for (var i = 0; i < options.length; i++) {
			var label = options[i];
			var input = label.querySelector('.order-kolar-option__input');
			if (!input || label.getAttribute('data-kolar-bound') === '1') {
				continue;
			}
			label.setAttribute('data-kolar-bound', '1');
			label.classList.toggle('is-selected', input.checked);
			label.addEventListener('click', function () {
				var wrap = this.closest('.order-kolar-picker');
				if (!wrap) {
					return;
				}
				var siblings = wrap.querySelectorAll('.order-kolar-option');
				for (var s = 0; s < siblings.length; s++) {
					siblings[s].classList.remove('is-selected');
				}
				this.classList.add('is-selected');
			});
		}
	}

	function applyLineFromPayload(index, line) {
		if (!line) {
			return;
		}
		var remarks = byId('Remarks' + index);
		if (remarks) {
			remarks.value = line.remarks || '';
		}
		['fpocket', 'spocket', 'shawpocket'].forEach(function (prefix) {
			var el = byId(prefix + index);
			if (el && line[prefix] !== undefined) {
				el.value = line[prefix];
			}
		});
		var design = byId('Designtype' + index);
		if (design && line.type) {
			design.value = line.type;
		}
		var suit = byId('suit' + index);
		if (suit && line.sno) {
			suit.value = String(line.sno);
			if (typeof get_id2 === 'function') {
				get_id2('suit' + index, 'srate' + index);
			}
		}
		var add = byId('add' + index);
		if (add) {
			add.value = line.addprice || '';
		}
		var srate = byId('srate' + index);
		if (srate && line.suitrate) {
			srate.value = line.suitrate;
		}
		if (line.kolor_style) {
			var kolor = document.querySelector('input[name="kolorstyle' + index + '"][value="' + line.kolor_style + '"]');
			if (kolor) {
				kolor.checked = true;
				var label = kolor.closest('.order-kolar-option');
				if (label) {
					var wrap = label.closest('.order-kolar-picker');
					if (wrap) {
						var siblings = wrap.querySelectorAll('.order-kolar-option');
						for (var s = 0; s < siblings.length; s++) {
							siblings[s].classList.remove('is-selected');
						}
					}
					label.classList.add('is-selected');
				}
			}
		}
		if (typeof window.copyOrderSuitLine !== 'function' && typeof recalculateOrderGrandTotal === 'undefined') {
			var erate = byId('erate' + index);
			var drate = byId('drate' + index);
			var itotal = byId('itotal' + index);
			var lineTotal = (parseInt(line.suitrate, 10) || 0) + (parseInt(line.addprice, 10) || 0);
			if (itotal) itotal.value = lineTotal;
		}
	}

	function applyCopyPayload(payload) {
		if (!payload || !payload.ok || !payload.lines || !payload.lines.length) {
			alert(lang('order_copy_last_none', 'No previous order found for this customer.'));
			return;
		}
		var qtyEl = byId('Quantity');
		if (!qtyEl) {
			return;
		}
		qtyEl.value = String(payload.lines.length);
		if (window.jQuery) {
			window.jQuery(qtyEl).trigger('change');
		}
		pendingCopyPayload = payload;
	}

	function maybeApplyPendingCopy() {
		if (!pendingCopyPayload || !pendingCopyPayload.lines) {
			return;
		}
		var lines = pendingCopyPayload.lines;
		for (var i = 0; i < lines.length; i++) {
			if (!byId('Designtype' + i)) {
				return;
			}
		}
		for (var j = 0; j < lines.length; j++) {
			applyLineFromPayload(j, lines[j]);
		}
		if (typeof recalculateOrderGrandTotal === 'function') {
			recalculateOrderGrandTotal();
		} else if (document.getElementById('total_amount')) {
			var sum = 0;
			for (var m = 0; m < lines.length; m++) {
				var it = byId('itotal' + m);
				sum += parseInt(it && it.value ? it.value : 0, 10) || 0;
			}
			document.getElementById('total_amount').value = sum;
		}
		syncStickyPayment();
		pendingCopyPayload = null;
		if (byId('maincat')) {
			initSuitAccordion(byId('maincat'));
		}
	}

	function collectPreviewData() {
		var qty = parseInt(byId('Quantity') && byId('Quantity').value, 10) || 0;
		var lines = [];
		for (var i = 0; i < qty; i++) {
			var typeEl = byId('Designtype' + i);
			var suitEl = byId('suit' + i);
			var remarksEl = byId('Remarks' + i);
			var kolorChecked = document.querySelector('input[name="kolorstyle' + i + '"]:checked');
			lines.push({
				index: i + 1,
				type: typeEl ? typeEl.options[typeEl.selectedIndex].text : '',
				suit: suitEl ? suitEl.options[suitEl.selectedIndex].text : '',
				kolar: kolorChecked ? kolorChecked.value : '',
				remarks: remarksEl ? remarksEl.value : ''
			});
		}
		return {
			customer: byId('order_customer_name_display') ? byId('order_customer_name_display').value : '',
			naap: byId('Naap12no') ? byId('Naap12no').value : '',
			returnDate: byId('retrun_Date') ? byId('retrun_Date').value : '',
			qty: qty,
			total: byId('total_amount') ? byId('total_amount').value : '0',
			advance: byId('Advance') ? byId('Advance').value : '0',
			discount: byId('Discount') ? byId('Discount').value : '0',
			balance: byId('Balance') ? byId('Balance').value : '0',
			lines: lines
		};
	}

	function renderPreviewHtml(data) {
		var html = '';
		html += '<div class="mb-2"><strong>' + lang('order_customer_name', 'Customer') + ':</strong> ' + escapeHtml(data.customer) + '</div>';
		html += '<div class="mb-2"><strong>' + lang('order_naap_no', 'Naap No') + ':</strong> ' + escapeHtml(data.naap) + '</div>';
		html += '<div class="mb-2"><strong>' + lang('order_return_date', 'Return Date') + ':</strong> ' + escapeHtml(data.returnDate) + '</div>';
		html += '<div class="mb-2"><strong>' + lang('app_quantity', 'Quantity') + ':</strong> ' + data.qty + '</div>';
		if (data.lines.length) {
			html += '<ul class="order-preview-list">';
			for (var i = 0; i < data.lines.length; i++) {
				var line = data.lines[i];
				html += '<li><strong>Suit ' + line.index + ':</strong> ' + escapeHtml(line.type) + ' / ' + escapeHtml(line.suit);
				if (line.kolar) {
					html += ' / ' + lang('order_kolar_style', 'Kolar') + ' #' + escapeHtml(line.kolar);
				}
				if (line.remarks) {
					html += ' — ' + escapeHtml(line.remarks);
				}
				html += '</li>';
			}
			html += '</ul>';
		}
		html += '<div class="mt-3"><strong>' + lang('order_total_amount', 'Total') + ':</strong> ' + formatMoney(data.total);
		html += ' | <strong>' + lang('app_advance', 'Advance') + ':</strong> ' + formatMoney(data.advance);
		html += ' | <strong>' + lang('app_balance', 'Balance') + ':</strong> ' + formatMoney(data.balance) + '</div>';
		return html;
	}

	function escapeHtml(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function showPreviewModal(onConfirm) {
		var body = byId('orderPreviewBody');
		if (!body) {
			onConfirm();
			return;
		}
		body.innerHTML = renderPreviewHtml(collectPreviewData());
		if (window.AppModal) {
			window.AppModal.show('#orderPreviewModal');
		} else if (window.jQuery) {
			window.jQuery('#orderPreviewModal').modal('show');
		}
		var confirmBtn = byId('orderPreviewConfirmBtn');
		if (!confirmBtn) {
			return;
		}
		confirmBtn.onclick = function () {
			if (window.AppModal) {
				window.AppModal.hide('#orderPreviewModal');
			} else if (window.jQuery) {
				window.jQuery('#orderPreviewModal').modal('hide');
			}
			onConfirm();
		};
	}

	window.orderFormShowPreview = showPreviewModal;
	window.orderFormUpdateSuitAccordion = function () {
		updateSuitAccordionState(byId('maincat'));
	};
	window.orderFormOnSuitLinesLoaded = function () {
		enhanceKolarPickers(byId('maincat'));
		initSuitAccordion(byId('maincat'));
		maybeApplyPendingCopy();
		syncStickyPayment();
	};

	function bindEvents() {
		document.addEventListener('input', function (event) {
			var id = event.target && event.target.id;
			if (!id) {
				return;
			}
			if (id === 'total_amount' || id === 'Advance' || id === 'Discount' || id === 'Balance' || id.indexOf('add') === 0 || id.indexOf('itotal') === 0) {
				syncStickyPayment();
			}
		});

		document.addEventListener('change', function (event) {
			if (event.target && event.target.id === 'Quantity') {
				window.setTimeout(function () {
					enhanceKolarPickers(byId('maincat'));
					initSuitAccordion(byId('maincat'));
					maybeApplyPendingCopy();
					syncStickyPayment();
				}, 300);
			}
		});

		var copyBtn = byId('orderCopyLastBtn');
		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				var customerCode = byId('customer_NO') ? byId('customer_NO').value : '';
				if (!customerCode || !cfg.lastOrderUrl) {
					return;
				}
				if (!window.jQuery) {
					return;
				}
				copyBtn.disabled = true;
				window.jQuery.post(cfg.lastOrderUrl, { customer_code: customerCode }, function (payload) {
					applyCopyPayload(payload);
				}, 'json').fail(function () {
					alert(lang('order_copy_last_fail', 'Could not load previous order.'));
				}).always(function () {
					copyBtn.disabled = false;
				});
			});
		}

		var previewBtn = byId('orderPreviewBtn');
		if (previewBtn) {
			previewBtn.addEventListener('click', function (event) {
				event.preventDefault();
				if (typeof valid !== 'function') {
					return;
				}
				if (valid({ preventDefault: function () {} }, { previewOnly: true }) === false) {
					return;
				}
				showPreviewModal(function () {
					var form = document.forms.Form;
					if (!form) {
						return;
					}
					form.setAttribute('data-order-confirmed', '1');
					if (typeof submitConfirmedOrder === 'function') {
						submitConfirmedOrder();
					} else {
						form.submit();
					}
				});
			});
		}

		var captureBtn = byId('orderCapturePhotoBtn');
		if (captureBtn) {
			captureBtn.addEventListener('click', function () {
				if (typeof take_snapshot === 'function') {
					take_snapshot();
				}
			});
		}
	}

	function bootstrap() {
		if (!document.body.classList.contains('order-form-page')) {
			return;
		}
		enhanceKolarPickers(document);
		syncStickyPayment();
		bindEvents();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootstrap);
	} else {
		bootstrap();
	}
})(window, document);
