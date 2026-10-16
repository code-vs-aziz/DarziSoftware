(function (window, document) {
	'use strict';

	var cfg = window.REPORT_HUB_CFG || {};

	function lang(key, fallback) {
		if (window.APP_LANG && window.APP_LANG[key]) {
			return window.APP_LANG[key];
		}
		return fallback || key;
	}

	function setStatus(text, isError) {
		var el = document.getElementById('report-quick-status');
		if (!el) {
			return;
		}
		el.textContent = text || '';
		el.className = 'dz-report-status' + (isError ? ' is-error' : (text ? ' is-ok' : ''));
	}

	function setBusy(busy) {
		var buttons = document.querySelectorAll('#report-quick-form button');
		for (var i = 0; i < buttons.length; i++) {
			buttons[i].disabled = !!busy;
		}
	}

	function ymd(d) {
		var m = d.getMonth() + 1;
		var day = d.getDate();
		return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (day < 10 ? '0' : '') + day;
	}

	function applyPreset(key) {
		var fromEl = document.getElementById('report-quick-from');
		var toEl = document.getElementById('report-quick-to');
		var presetEl = document.getElementById('report-quick-preset');
		if (!fromEl || !toEl) {
			return;
		}
		var today = new Date();
		today.setHours(0, 0, 0, 0);
		var from = new Date(today);
		var to = new Date(today);

		if (key === 'yesterday') {
			from.setDate(from.getDate() - 1);
			to = new Date(from);
		} else if (key === 'last_7') {
			from.setDate(from.getDate() - 6);
		} else if (key === 'last_30') {
			from.setDate(from.getDate() - 29);
		} else if (key === 'this_month') {
			from = new Date(today.getFullYear(), today.getMonth(), 1);
		} else if (key === 'last_month') {
			from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
			to = new Date(today.getFullYear(), today.getMonth(), 0);
		} else if (key === 'today') {
			/* same day */
		} else {
			return;
		}
		fromEl.value = ymd(from);
		toEl.value = ymd(to);
		if (presetEl) {
			presetEl.value = key;
		}
	}

	function formPayload() {
		var keyEl = document.getElementById('report-quick-key');
		var fromEl = document.getElementById('report-quick-from');
		var toEl = document.getElementById('report-quick-to');
		var presetEl = document.getElementById('report-quick-preset');
		return {
			key: keyEl ? keyEl.value : '',
			from: fromEl ? fromEl.value : '',
			to: toEl ? toEl.value : '',
			preset: presetEl ? presetEl.value : ''
		};
	}

	function postJson(url, data, done) {
		var body = new URLSearchParams();
		Object.keys(data).forEach(function (k) {
			if (data[k] != null && data[k] !== '') {
				body.append(k, data[k]);
			}
		});
		var csrfMeta = document.querySelector('meta[name="csrf-token-value"]');
		var csrfName = document.querySelector('meta[name="csrf-token-name"]');
		if (csrfMeta && csrfName) {
			body.append(csrfName.getAttribute('content'), csrfMeta.getAttribute('content'));
		}
		var hidden = document.querySelector('#report-quick-form input[name="dz_csrf_token"]');
		if (hidden) {
			body.append('dz_csrf_token', hidden.value);
		}

		fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (r) { return r.json(); }).then(done).catch(function () {
			done({ ok: false, error: 'network' });
		});
	}

	function errorMessage(code) {
		var map = {
			missing_dates: lang('report_err_missing_dates', 'Select from and to dates.'),
			invalid_dates: lang('report_err_invalid_dates', 'Dates are invalid.'),
			from_after_to: lang('report_err_from_after_to', 'From date cannot be after to date.'),
			range_too_large: lang('report_err_range_too_large', 'Date range is too large.'),
			permission: lang('report_err_permission', 'You do not have permission.'),
			pdf_unsupported: lang('report_err_pdf_unsupported', 'PDF is not available for this report — open interactively.'),
			missing_phone: lang('report_err_missing_phone', 'Set REPORT_OWNER_PHONE in .env or enter a phone.'),
			bridge_disabled: lang('report_err_bridge', 'WhatsApp bridge is disabled.'),
			bridge_unreachable: lang('report_err_bridge', 'WhatsApp bridge is not reachable.'),
			not_ready: lang('report_err_bridge', 'WhatsApp bridge is not ready — restart bridge / scan QR.'),
			send_failed: lang('report_err_send_failed', 'WhatsApp send failed — bridge restart karein, phir dobara try karein.'),
			session: lang('report_err_session', 'Please sign in again.'),
			network: lang('report_err_network', 'Network error. Try again.')
		};
		return map[code] || (lang('report_err_generic', 'Something went wrong.') + (code ? ' (' + code + ')' : ''));
	}

	document.addEventListener('DOMContentLoaded', function () {
		var form = document.getElementById('report-quick-form');
		if (!form) {
			return;
		}

		applyPreset('last_7');

		var chips = document.querySelectorAll('.dz-report-chip[data-preset]');
		for (var i = 0; i < chips.length; i++) {
			chips[i].addEventListener('click', function () {
				applyPreset(this.getAttribute('data-preset'));
			});
		}

		var presetSel = document.getElementById('report-quick-preset');
		if (presetSel) {
			presetSel.addEventListener('change', function () {
				if (this.value) {
					applyPreset(this.value);
				}
			});
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var data = formPayload();
			if (!data.key) {
				setStatus(lang('report_err_select_report', 'Select a report first.'), true);
				return;
			}
			setBusy(true);
			setStatus(lang('report_status_opening', 'Opening report…'), false);
			postJson(cfg.openUrl, data, function (res) {
				setBusy(false);
				if (!res || !res.ok) {
					setStatus(errorMessage(res && res.error), true);
					return;
				}
				setStatus(lang('report_status_opened', 'Report opened.'), false);
				window.open(res.url, '_blank', 'noopener');
			});
		});

		var pdfBtn = document.getElementById('report-quick-pdf');
		if (pdfBtn) {
			pdfBtn.addEventListener('click', function () {
				var data = formPayload();
				if (!data.key) {
					setStatus(lang('report_err_select_report', 'Select a report first.'), true);
					return;
				}
				setBusy(true);
				setStatus(lang('report_status_pdf', 'Generating PDF…'), false);
				var url = cfg.pdfUrl + '?key=' + encodeURIComponent(data.key)
					+ '&from=' + encodeURIComponent(data.from)
					+ '&to=' + encodeURIComponent(data.to)
					+ (data.preset ? '&preset=' + encodeURIComponent(data.preset) : '');
				// Use download navigation for binary PDF
				window.location.href = url;
				setTimeout(function () {
					setBusy(false);
					setStatus(lang('report_status_pdf_done', 'PDF download started.'), false);
				}, 800);
			});
		}

		var waBtn = document.getElementById('report-quick-whatsapp');
		if (waBtn) {
			waBtn.addEventListener('click', function () {
				var data = formPayload();
				if (!data.key) {
					setStatus(lang('report_err_select_report', 'Select a report first.'), true);
					return;
				}
				var phone = cfg.ownerPhone || '';
				if (!phone) {
					phone = window.prompt(lang('report_prompt_phone', 'Enter WhatsApp number (e.g. 03001234567):'), '') || '';
				}
				if (!phone) {
					setStatus(errorMessage('missing_phone'), true);
					return;
				}
				data.phone = phone;
				setBusy(true);
				setStatus(lang('report_status_whatsapp', 'Generating PDF and queuing WhatsApp…'), false);
				postJson(cfg.whatsappUrl, data, function (res) {
					setBusy(false);
					if (!res || !res.ok) {
						var msg = errorMessage(res && res.error);
						if (res && res.detail) {
							msg += ' — ' + String(res.detail).slice(0, 120);
						}
						setStatus(msg, true);
						return;
					}
					if (res.mode === 'queued' || res.queued) {
						setStatus(lang('report_status_whatsapp_queued', 'PDF queued for WhatsApp — cron will send shortly.'), false);
					} else if (res.mode === 'link') {
						setStatus(lang('report_status_whatsapp_link', 'Sent WhatsApp message with PDF link.'), false);
					} else {
						setStatus(lang('report_status_whatsapp_ok', 'PDF sent on WhatsApp.'), false);
					}
				});
			});
		}
	});
})(window, document);
