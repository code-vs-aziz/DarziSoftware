/**
 * Darzi Baba date picker
 * - Display / edit format: d-M-Y (e.g. 29-Oct-2026) to match Current Date
 * - Highlights Fridays in red (not Sundays)
 * - Submits the same text value; PHP strtotime() accepts d-M-Y
 */
(function (window, document) {
	'use strict';

	var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	var DOW = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
	var openPopup = null;

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function formatDisplay(date) {
		if (!date || isNaN(date.getTime())) {
			return '';
		}
		return pad(date.getDate()) + '-' + MONTHS[date.getMonth()] + '-' + date.getFullYear();
	}

	function formatIso(date) {
		if (!date || isNaN(date.getTime())) {
			return '';
		}
		return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
	}

	function parseFlexible(value) {
		var raw = String(value || '').trim();
		if (!raw) {
			return null;
		}
		var m = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (m) {
			return new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
		}
		m = raw.match(/^(\d{1,2})-([A-Za-z]{3})-(\d{4})$/);
		if (m) {
			var mi = MONTHS.indexOf(m[2].charAt(0).toUpperCase() + m[2].slice(1).toLowerCase());
			if (mi >= 0) {
				return new Date(parseInt(m[3], 10), mi, parseInt(m[1], 10));
			}
		}
		m = raw.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/);
		if (m) {
			return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10));
		}
		var d = new Date(raw);
		return isNaN(d.getTime()) ? null : d;
	}

	function startOfDay(d) {
		return new Date(d.getFullYear(), d.getMonth(), d.getDate());
	}

	function sameDay(a, b) {
		return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
	}

	function closeOpen() {
		if (openPopup && openPopup.parentNode) {
			openPopup.hidden = true;
		}
		openPopup = null;
	}

	function enhanceInput(input) {
		if (!input || input.getAttribute('data-dz-date') === '1') {
			return;
		}
		input.setAttribute('data-dz-date', '1');

		var readonly = input.disabled || input.readOnly;
		var initial = parseFlexible(input.value) || (readonly ? null : new Date());
		if (input.type === 'date') {
			input.type = 'text';
		}
		input.classList.add('dz-date-input');
		input.setAttribute('autocomplete', 'off');
		input.setAttribute('inputmode', 'text');
		input.setAttribute('placeholder', 'dd-Mon-yyyy');
		if (initial) {
			input.value = formatDisplay(initial);
			input.setAttribute('data-iso', formatIso(initial));
		}

		var wrap = document.createElement('div');
		wrap.className = 'dz-date-wrap' + (readonly ? ' is-readonly' : '');
		input.parentNode.insertBefore(wrap, input);
		wrap.appendChild(input);

		if (readonly) {
			return;
		}

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'dz-date-wrap__btn';
		btn.setAttribute('aria-label', 'Open calendar');
		btn.innerHTML = '<i class="fa fa-calendar" aria-hidden="true"></i>';
		wrap.appendChild(btn);

		var popup = document.createElement('div');
		popup.className = 'dz-date-popup';
		popup.hidden = true;
		popup.setAttribute('role', 'dialog');
		wrap.appendChild(popup);

		var viewYear;
		var viewMonth;

		function selectedDate() {
			return parseFlexible(input.value);
		}

		function setValue(date) {
			input.value = formatDisplay(date);
			input.setAttribute('data-iso', formatIso(date));
			if (typeof input.dispatchEvent === 'function') {
				try {
					input.dispatchEvent(new Event('change', { bubbles: true }));
					input.dispatchEvent(new Event('input', { bubbles: true }));
				} catch (err) {
					/* IE fallback unused */
				}
			}
		}

		function render() {
			var sel = selectedDate();
			var today = startOfDay(new Date());
			var first = new Date(viewYear, viewMonth, 1);
			var startPad = first.getDay(); // Sunday = 0
			var gridStart = new Date(viewYear, viewMonth, 1 - startPad);
			var title = MONTHS[viewMonth] + ' ' + viewYear;

			var html = '';
			html += '<div class="dz-date-popup__head">';
			html += '<button type="button" class="dz-date-popup__nav" data-nav="-1" aria-label="Previous month">&lsaquo;</button>';
			html += '<p class="dz-date-popup__title">' + title + '</p>';
			html += '<button type="button" class="dz-date-popup__nav" data-nav="1" aria-label="Next month">&rsaquo;</button>';
			html += '</div><div class="dz-date-popup__grid">';

			var d;
			for (d = 0; d < 7; d++) {
				html += '<div class="dz-date-popup__dow' + (d === 5 ? ' is-friday' : '') + '">' + DOW[d] + '</div>';
			}

			for (d = 0; d < 42; d++) {
				var cell = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + d);
				var classes = ['dz-date-popup__day'];
				if (cell.getMonth() !== viewMonth) {
					classes.push('is-outside');
				}
				if (cell.getDay() === 5) {
					classes.push('is-friday');
				}
				if (sameDay(cell, today)) {
					classes.push('is-today');
				}
				if (sel && sameDay(cell, sel)) {
					classes.push('is-selected');
				}
				html += '<button type="button" class="' + classes.join(' ') + '" data-ymd="' + formatIso(cell) + '">' + cell.getDate() + '</button>';
			}
			html += '</div>';
			html += '<div class="dz-date-popup__footer">';
			html += '<button type="button" data-today="1">Today</button>';
			html += '<button type="button" data-close="1">Close</button>';
			html += '</div>';
			popup.innerHTML = html;
		}

		function open() {
			closeOpen();
			var base = selectedDate() || new Date();
			viewYear = base.getFullYear();
			viewMonth = base.getMonth();
			render();
			popup.hidden = false;
			openPopup = popup;
		}

		function onPopupClick(e) {
			e.preventDefault();
			e.stopPropagation();
			var t = e.target;
			if (!t || !t.closest) {
				return;
			}
			var navEl = t.closest('[data-nav]');
			if (navEl) {
				var nav = parseInt(navEl.getAttribute('data-nav'), 10);
				if (!isNaN(nav)) {
					viewMonth += nav;
					if (viewMonth < 0) {
						viewMonth = 11;
						viewYear -= 1;
					} else if (viewMonth > 11) {
						viewMonth = 0;
						viewYear += 1;
					}
					render();
				}
				return;
			}
			if (t.closest('[data-today="1"]')) {
				setValue(startOfDay(new Date()));
				closeOpen();
				return;
			}
			if (t.closest('[data-close="1"]')) {
				closeOpen();
				return;
			}
			var dayEl = t.closest('[data-ymd]');
			if (dayEl) {
				var picked = parseFlexible(dayEl.getAttribute('data-ymd'));
				if (picked) {
					setValue(picked);
				}
				closeOpen();
			}
		}

		btn.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			if (popup.hidden) {
				open();
			} else {
				closeOpen();
			}
		});

		input.addEventListener('focus', function () {
			/* keep typed edits; normalize on blur */
		});

		input.addEventListener('blur', function () {
			var parsed = parseFlexible(input.value);
			if (parsed) {
				setValue(parsed);
			} else if (input.value.trim() !== '') {
				var fallback = parseFlexible(input.getAttribute('data-iso')) || new Date();
				setValue(fallback);
			}
		});

		input.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				closeOpen();
			} else if (e.key === 'ArrowDown' && popup.hidden) {
				e.preventDefault();
				open();
			}
		});

		popup.addEventListener('click', onPopupClick);
	}

	function init(root) {
		var scope = root && root.querySelectorAll ? root : document;
		var nodes = scope.querySelectorAll(
			'input.dz-date, input[data-dz-date-picker="1"], input#current[type="date"], input#retrun_Date[type="date"], input#current.dz-date, input#retrun_Date.dz-date'
		);
		for (var i = 0; i < nodes.length; i++) {
			enhanceInput(nodes[i]);
		}
	}

	document.addEventListener('click', function (e) {
		if (!openPopup) {
			return;
		}
		var wrap = openPopup.parentNode;
		var target = e.target;
		/* After month nav re-render, click target can be detached — ignore those. */
		if (target && typeof target.isConnected === 'boolean' && !target.isConnected) {
			return;
		}
		if (wrap && target && wrap.contains(target)) {
			return;
		}
		closeOpen();
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			init(document);
		});
	} else {
		init(document);
	}

	if (window.jQuery) {
		window.jQuery(document).on('app:ajax-loaded', function () {
			init(document);
		});
	}

	window.DzDatePicker = {
		init: init,
		formatDisplay: formatDisplay,
		parseFlexible: parseFlexible
	};
})(window, document);
