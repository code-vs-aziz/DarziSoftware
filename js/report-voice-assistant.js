(function (window, document, $) {
	'use strict';

	var cfg = window.REPORT_VOICE_CFG || {};
	var listening = false;
	var recognition = null;

	function lang(key, fallback) {
		if (window.APP_LANG && window.APP_LANG[key]) {
			return window.APP_LANG[key];
		}
		return fallback || key;
	}

	function setStatus(text, isError) {
		var el = document.getElementById('report-voice-status');
		if (!el) {
			return;
		}
		el.textContent = text;
		el.className = 'report-voice-status' + (isError ? ' is-error' : ' is-ok');
	}

	function showPreview(data) {
		var box = document.getElementById('report-voice-preview');
		if (!box) {
			return;
		}
		if (!data || !data.ok) {
			box.style.display = 'none';
			return;
		}
		box.style.display = 'block';
		box.textContent = data.message || '';
		box.dataset.url = data.url || '';
		box.dataset.needsForm = data.needs_form ? '1' : '0';
	}

	function openReport(data) {
		if (!data || !data.ok || !data.url) {
			return;
		}
		window.open(data.url, '_blank', 'noopener');
	}

	function runCommand(text) {
		if (!text || !cfg.parseUrl) {
			return;
		}
		setStatus(lang('report_voice_processing', 'Processing…'), false);
		$.post(cfg.parseUrl, { text: text })
			.done(function (data) {
				if (!data || !data.ok) {
					var err = (data && data.error) || 'fail';
					if (err === 'unrecognized') {
						setStatus(lang('report_voice_unrecognized', 'Command not understood. Try examples below.'), true);
					} else if (err === 'permission') {
						setStatus(lang('report_voice_permission', 'You do not have permission for this report.'), true);
					} else {
						setStatus(lang('report_voice_fail', 'Could not run command.'), true);
					}
					showPreview(null);
					return;
				}
				var src = data.source === 'ollama' ? ' (AI)' : '';
				setStatus(lang('report_voice_ready', 'Opening report') + src, false);
				showPreview(data);
				openReport(data);
			})
			.fail(function () {
				setStatus(lang('report_voice_fail', 'Could not run command.'), true);
			});
	}

	function initSpeech() {
		var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
		if (!SpeechRecognition) {
			setStatus(lang('report_voice_no_mic', 'Voice not supported — use Chrome or type command.'), true);
			return false;
		}
		recognition = new SpeechRecognition();
		recognition.lang = cfg.speechLang || 'ur-PK';
		recognition.interimResults = false;
		recognition.maxAlternatives = 1;

		recognition.onresult = function (event) {
			var text = '';
			if (event.results && event.results[0] && event.results[0][0]) {
				text = event.results[0][0].transcript;
			}
			var input = document.getElementById('report-voice-input');
			if (input) {
				input.value = text;
			}
			runCommand(text);
		};

		recognition.onerror = function () {
			listening = false;
			var btn = document.getElementById('report-voice-mic');
			if (btn) {
				btn.classList.remove('is-listening');
			}
			setStatus(lang('report_voice_mic_error', 'Mic error — type command instead.'), true);
		};

		recognition.onend = function () {
			listening = false;
			var btn = document.getElementById('report-voice-mic');
			if (btn) {
				btn.classList.remove('is-listening');
			}
		};

		return true;
	}

	function toggleMic() {
		if (!recognition && !initSpeech()) {
			return;
		}
		if (listening) {
			recognition.stop();
			return;
		}
		listening = true;
		var btn = document.getElementById('report-voice-mic');
		if (btn) {
			btn.classList.add('is-listening');
		}
		setStatus(lang('report_voice_listening', 'Listening… speak now'), false);
		try {
			recognition.start();
		} catch (e) {
			listening = false;
			if (btn) {
				btn.classList.remove('is-listening');
			}
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var form = document.getElementById('report-voice-form');
		var mic = document.getElementById('report-voice-mic');
		var chips = document.querySelectorAll('.report-voice-chip');

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var input = document.getElementById('report-voice-input');
				runCommand(input ? input.value.trim() : '');
			});
		}

		if (mic) {
			mic.addEventListener('click', toggleMic);
		}

		for (var i = 0; i < chips.length; i++) {
			chips[i].addEventListener('click', function () {
				var cmd = this.getAttribute('data-cmd') || '';
				var input = document.getElementById('report-voice-input');
				if (input) {
					input.value = cmd;
				}
				runCommand(cmd);
			});
		}

		initSpeech();
	});
})(window, document, window.jQuery);
