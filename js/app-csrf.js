(function (window, document) {
	'use strict';

	function tokenMeta(name) {
		var node = document.querySelector('meta[name="' + name + '"]');
		return node ? node.getAttribute('content') || '' : '';
	}

	function setTokenMeta(name, value) {
		var node = document.querySelector('meta[name="' + name + '"]');
		if (node) {
			node.setAttribute('content', value);
		}
	}

	function readCookie(name) {
		if (!name) {
			return '';
		}
		var pattern = new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)');
		var match = document.cookie.match(pattern);
		return match ? decodeURIComponent(match[1]) : '';
	}

	/**
	 * CI verifies POST token === cookie. Cookie is source of truth.
	 * Cached/prefetch HTML often has a stale meta/hidden value — prefer cookie.
	 */
	function csrfToken() {
		var cookieName = tokenMeta('csrf-cookie-name') || 'dz_csrf_cookie';
		var fromCookie = readCookie(cookieName);
		if (fromCookie) {
			return fromCookie;
		}
		return tokenMeta('csrf-token-value') || '';
	}

	function syncMetaFromCookie() {
		var token = csrfToken();
		if (token) {
			setTokenMeta('csrf-token-value', token);
		}
		return token;
	}

	function injectFormTokens() {
		var tokenName = tokenMeta('csrf-token-name') || 'dz_csrf_token';
		var token = syncMetaFromCookie();
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
			var input = form.querySelector('input[name="' + tokenName + '"]');
			if (input) {
				input.value = token;
				continue;
			}
			input = document.createElement('input');
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

		var tokenName = tokenMeta('csrf-token-name') || 'dz_csrf_token';

		jq.ajaxSetup({
			beforeSend: function (xhr, settings) {
				var method = String(settings.type || 'GET').toUpperCase();
				if (method !== 'POST') {
					return;
				}

				var token = syncMetaFromCookie();
				if (!token) {
					return;
				}

				if (settings.data instanceof window.FormData) {
					if (settings.data.has(tokenName)) {
						settings.data.set(tokenName, token);
					} else {
						settings.data.append(tokenName, token);
					}
					return;
				}

				if (typeof settings.data === 'string') {
					var enc = encodeURIComponent(tokenName) + '=';
					if (settings.data.indexOf(enc) === -1) {
						settings.data +=
							(settings.data ? '&' : '') + enc + encodeURIComponent(token);
					} else {
						settings.data = settings.data.replace(
							new RegExp(enc + '[^&]*'),
							enc + encodeURIComponent(token)
						);
					}
					return;
				}

				if (jq.isPlainObject(settings.data)) {
					settings.data[tokenName] = token;
				}
			}
		});
	}

	function bindSubmitRefresh() {
		document.addEventListener(
			'submit',
			function () {
				injectFormTokens();
			},
			true
		);
	}

	function bootstrap() {
		injectFormTokens();
		setupAjaxCsrf();
		bindSubmitRefresh();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bootstrap);
	} else {
		bootstrap();
	}

	window.appRefreshCsrfForms = injectFormTokens;
})(window, document);
