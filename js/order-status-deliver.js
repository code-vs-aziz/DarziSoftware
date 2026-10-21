(function (window, document, $) {
	'use strict';

	var cfg = window.ORDER_STATUS_DELIVER || {};
	var labels = (cfg && cfg.labels) || {};
	var modalMode = 'view';
	var currentData = null;
	var clickBound = false;
	var submitBound = false;

	function syncCfg() {
		cfg = window.ORDER_STATUS_DELIVER || cfg || {};
		labels = cfg.labels || labels || {};
	}

	function money(n) {
		var v = Number(n || 0);
		return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function fmtDate(v) {
		if (!v) { return '—'; }
		var s = String(v);
		if (/^\d{4}-\d{2}-\d{2}/.test(s)) {
			var p = s.slice(0, 10).split('-');
			return p[2] + '-' + p[1] + '-' + p[0];
		}
		return s;
	}

	function showModal() {
		if (window.AppModal && typeof window.AppModal.show === 'function') {
			window.AppModal.show('#osDeliverModal');
		} else if ($ && $.fn.modal) {
			$('#osDeliverModal').modal('show');
		}
	}

	function setLoading(on) {
		var load = document.getElementById('os-modal-loading');
		var body = document.getElementById('os-modal-body');
		var err = document.getElementById('os-modal-error');
		if (load) { load.hidden = !on; }
		if (body) { body.hidden = on; }
		if (err) { err.hidden = true; err.textContent = ''; }
	}

	function showError(msg) {
		var err = document.getElementById('os-modal-error');
		var load = document.getElementById('os-modal-loading');
		var body = document.getElementById('os-modal-body');
		if (load) { load.hidden = true; }
		if (body) { body.hidden = true; }
		if (err) {
			err.hidden = false;
			err.textContent = msg || labels.load_fail || 'Could not load payment details.';
		}
	}

	function fillMoney(data) {
		var m = data.money || {};
		var map = {
			'os-m-total': m.order_total,
			'os-m-booking-adv': m.booking_advance,
			'os-m-advance-total': m.advance_total,
			'os-m-discount': m.discount,
			'os-m-order-remaining': m.order_remaining,
			'os-m-prev': m.previous_outstanding,
			'os-m-due': m.amount_due_now
		};
		Object.keys(map).forEach(function (id) {
			var el = document.getElementById(id);
			if (el) { el.textContent = money(map[id]); }
		});

		var remLbl = document.getElementById('os-lbl-order-remaining');
		if (remLbl) {
			remLbl.textContent = (labels.order_remaining || 'This order remaining')
				+ (data.order_id ? (' (#' + data.order_id + ')') : '');
		}
		var hint = document.getElementById('os-due-hint');
		if (hint) {
			hint.textContent = (labels.due_breakdown || 'This order + previous unpaid')
				+ ': '
				+ money(m.order_remaining)
				+ ' + '
				+ money(m.previous_outstanding)
				+ ' = '
				+ money(m.amount_due_now);
		}
	}

	function fillSuits(data, canEdit) {
		var tbody = document.querySelector('#os-suits-table tbody');
		var lineFields = document.getElementById('os-line-fields');
		if (!tbody) { return; }
		tbody.innerHTML = '';
		if (lineFields) { lineFields.innerHTML = ''; }
		var suits = data.suits || [];
		suits.forEach(function (s, idx) {
			var tr = document.createElement('tr');
			var deliverCell = '';
			if (canEdit && s.is_ready) {
				deliverCell = '<select class="form-control form-control-sm os-returndel" data-sr="' + s.line_sr + '">'
					+ '<option value="yes" selected>' + (labels.yes || 'YES') + '</option>'
					+ '<option value="no">' + (labels.no || 'NO') + '</option>'
					+ '</select>';
				if (lineFields) {
					var hid = document.createElement('input');
					hid.type = 'hidden';
					hid.name = 'order_sr[]';
					hid.value = String(s.line_sr);
					lineFields.appendChild(hid);
					var hid2 = document.createElement('input');
					hid2.type = 'hidden';
					hid2.name = 'returndel[]';
					hid2.value = 'yes';
					hid2.className = 'os-returndel-hidden';
					hid2.setAttribute('data-sr', String(s.line_sr));
					lineFields.appendChild(hid2);
				}
			} else {
				deliverCell = s.is_ready ? (labels.yes || 'YES') : (s.is_delivered ? (labels.yes || 'YES') : '—');
			}
			tr.innerHTML = '<td>' + (idx + 1) + '</td>'
				+ '<td>' + (s.suit_name || '—') + '</td>'
				+ '<td>' + (s.type || '—') + '</td>'
				+ '<td>' + money(s.line_total) + '</td>'
				+ '<td>' + (s.status_label || '—') + '</td>'
				+ '<td class="os-deliver-col">' + deliverCell + '</td>';
			tbody.appendChild(tr);
		});

		if (canEdit) {
			tbody.querySelectorAll('.os-returndel').forEach(function (sel) {
				sel.addEventListener('change', function () {
					var sr = sel.getAttribute('data-sr');
					var hidden = document.querySelector('.os-returndel-hidden[data-sr="' + sr + '"]');
					if (hidden) { hidden.value = sel.value; }
				});
			});
		}
	}

	function fillHistory(data) {
		var tbody = document.querySelector('#os-history-table tbody');
		if (!tbody) { return; }
		tbody.innerHTML = '';
		var rows = data.history || [];
		if (!rows.length) {
			tbody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">—</td></tr>';
			return;
		}
		rows.forEach(function (h) {
			var tr = document.createElement('tr');
			var label = h.label || '';
			if (h.order_id && label.indexOf('#' + h.order_id) < 0 && label.indexOf(String(h.order_id)) < 0) {
				label = label + ' (#' + h.order_id + ')';
			}
			var balTxt = (h.balance === null || h.balance === undefined) ? '—' : money(h.balance);
			tr.innerHTML = '<td>' + fmtDate(h.date) + '</td>'
				+ '<td>' + label + '</td>'
				+ '<td>' + money(h.paid) + '</td>'
				+ '<td>' + balTxt + '</td>';
			tbody.appendChild(tr);
		});
	}

	function applyData(data, mode) {
		currentData = data;
		modalMode = mode;
		var title = document.getElementById('osDeliverModalTitle');
		var sub = document.getElementById('os-modal-subtitle');
		var collectRow = document.getElementById('os-collect-row');
		var submitBtn = document.getElementById('os-submit-deliver');
		var form = document.getElementById('os-deliver-form');
		var openFull = document.getElementById('os-open-full');
		var canDeliver = !!(data.can_deliver) && mode === 'deliver';

		if (title) {
			title.textContent = canDeliver
				? (labels.deliver_title || 'Deliver order')
				: (labels.payment_title || 'Payment summary');
		}
		if (sub) {
			var c = data.customer || {};
			sub.textContent = '#' + data.order_id
				+ (c.name ? (' · ' + c.name) : '')
				+ (c.phone ? (' · ' + c.phone) : '')
				+ (c.naap_no ? (' · Naap ' + c.naap_no) : '');
		}

		fillMoney(data);
		fillSuits(data, canDeliver);
		fillHistory(data);

		var custCode = document.getElementById('os-customer-code');
		var totalAmount = document.getElementById('os-total-amount');
		var advance = document.getElementById('os-advance');
		var discount = document.getElementById('os-discount');
		var returnDate = document.getElementById('os-return-date');
		var collectNow = document.getElementById('os-collect-now');
		if (custCode) { custCode.value = (data.customer && data.customer.code) || ''; }
		if (totalAmount) { totalAmount.value = (data.money && data.money.order_total) || 0; }
		if (advance) { advance.value = (data.money && data.money.advance_total) || 0; }
		if (discount) { discount.value = (data.money && data.money.discount) || 0; }
		if (returnDate) { returnDate.value = (data.dates && data.dates.return_date) || ''; }
		if (collectNow) { collectNow.value = (data.money && data.money.order_remaining) || 0; }

		if (form) {
			form.action = (cfg.deliverUrlBase
				? (cfg.deliverUrlBase + data.order_id)
				: ('/index.php/Order/process_del/' + data.order_id));
			if (data._deliver_url) {
				form.action = data._deliver_url;
			}
		}
		if (openFull) {
			openFull.href = data._full_page_url || '#';
			openFull.style.display = canDeliver ? '' : 'none';
		}
		if (collectRow) { collectRow.hidden = !canDeliver; }
		if (submitBtn) {
			submitBtn.hidden = !canDeliver;
			submitBtn.disabled = !canDeliver;
		}
		if (!canDeliver && mode === 'deliver') {
			var err = document.getElementById('os-modal-error');
			if (err) {
				err.hidden = false;
				err.textContent = labels.no_ready || 'No ready suits to deliver on this order.';
			}
		}
	}

	function openForOrder(orderId, mode) {
		syncCfg();
		if (!document.getElementById('osDeliverModal')) {
			window.alert(labels.load_fail || 'Deliver dialog is not available. Refresh the page.');
			return;
		}
		setLoading(true);
		showModal();
		var base = cfg.summaryUrl || '';
		var url = base + (String(base).indexOf('?') >= 0 ? '&' : '?') + 'order_id=' + encodeURIComponent(orderId);
		fetch(url, {
			credentials: 'same-origin',
			headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (r) { return r.text(); })
			.then(function (raw) {
				var res;
				try { res = JSON.parse(raw); } catch (err) {
					throw new Error(labels.load_fail || 'Could not load payment details.');
				}
				if (!res || !res.success || !res.data) {
					throw new Error((res && res.message) ? res.message : (labels.load_fail || 'Failed'));
				}
				res.data._deliver_url = res.deliver_url;
				res.data._full_page_url = res.full_page_url;
				setLoading(false);
				var body = document.getElementById('os-modal-body');
				if (body) { body.hidden = false; }
				applyData(res.data, mode);
			})
			.catch(function (err) {
				showError(err.message || labels.load_fail || 'Failed');
			});
	}

	function bindEvents() {
		if (!clickBound) {
			clickBound = true;
			document.addEventListener('click', function (e) {
				var t = e.target;
				if (!t) { return; }
				if (typeof t.closest !== 'function') {
					while (t && t !== document) {
						if (t.classList && (t.classList.contains('btn-os-payment') || t.classList.contains('btn-os-deliver'))) {
							break;
						}
						t = t.parentNode;
					}
				}
				var payBtn = (e.target.closest ? e.target.closest('.btn-os-payment') : null)
					|| (t && t.classList && t.classList.contains('btn-os-payment') ? t : null);
				var delBtn = (e.target.closest ? e.target.closest('.btn-os-deliver') : null)
					|| (t && t.classList && t.classList.contains('btn-os-deliver') ? t : null);
				if (payBtn) {
					e.preventDefault();
					e.stopPropagation();
					openForOrder(payBtn.getAttribute('data-order-id'), 'view');
				}
				if (delBtn) {
					e.preventDefault();
					e.stopPropagation();
					openForOrder(delBtn.getAttribute('data-order-id'), 'deliver');
				}
			}, true);
		}

		if (!submitBound) {
			submitBound = true;
			document.addEventListener('submit', function (e) {
				if (!e.target || e.target.id !== 'os-deliver-form') {
					return;
				}
				syncCfg();
				if (modalMode !== 'deliver' || !currentData || !currentData.can_deliver) {
					e.preventDefault();
					return;
				}
				var ok = window.confirm(labels.confirm || 'Deliver selected suits and save payment?');
				if (!ok) {
					e.preventDefault();
				}
			});
		}
	}

	bindEvents();

	if ($ && $.fn && $.fn.on) {
		$(document).on('app:ajax-loaded order-status:ready', function () {
			syncCfg();
		});
	}
})(window, document, window.jQuery);
