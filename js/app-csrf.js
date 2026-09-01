(function (window, document) {
	'use strict';

	function tokenMeta(name) {
		var node = document.querySelector('meta[name="' + name + '"]');
		return node ? node.getAttribute('content') || '' : '';
	}

	function readCookie(name) {
		var pattern = new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)');
		var match = document.cookie.match(pattern);
		return match ? decodeURIComponent(match[1]) : '';
	}

	function csrfToken() {
		var fromMeta = tokenMeta('csrf-token-value');
		if (fromMeta) {
			return fromMeta;
		}
		var cookieName = tokenMeta('csrf-cookie-name');
		return cookieName ? readCookie(cookieName) : '';
	}

	function injectFormTokens() {
		var tokenName = tokenMeta('csrf-token-name');
		var token = csrfToken();
		if (!tokenName || !token) {
			return;
		}

		var forms = document.querySelectorAll('form');
		for (var i = 0; i < forms.length; i++) {
			var form = forms[i];
			var method = String(form.getAttribute('method') || 'GET').toUpperCase();
			if (method !== 'POST') {
				continue;
			}
			if (form.querySelector('input[name="' + tokenName + '"]')) {
				continue;
			}
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = tokenName;
			input.value = token;
			form.appendChild(input);
		}
	}

	function setupAjaxCsrf() {
		var jq = window.jQuery;
		if (!jq || !jq.ajaxSetup) {
			return;
		}

		var tokenName = tokenMeta('csrf-token-name');
		if (!tokenName) {
			return;
		}

		jq.ajaxSetup({
			beforeSend: function (xhr, settings) {
				var method = String(settings.type || 'GET').toUpperCase();
				if (method !== 'POST') {
					return;
				}

				var token = csrfToken();
				if (!token) {
					return;
				}

				if (settings.data instanceof window.FormData) {
					if (!settings.data.has(tokenName)) {
						settings.data.append(tokenName, token);
					}
					return;
				}

				if (typeof settings.data === 'string') {
					if (settings.data.indexOf(encodeURIComponent(tokenName) + '=') === -1) {
						settings.data += (settings.data ? '&' : '') + encodeURIComponent(tokenName) + '=' + encodeURIComponent(token);
					}
					return;
				}

				if (jq.isPlainObject(settings.data)) {
					if (!Object.prototype.hasOwnProperty.call(settings.data, tokenName)) {
						settings.data[tokenName] = token;
					}
				}
			}
		});
	}

	function bootstrap() {
		injectFormTokens();
		setupAjaxCsrf();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootstrap);
	} else {
		bootstrap();
	}

	window.appRefreshCsrfForms = injectFormTokens;
})(window, document);
