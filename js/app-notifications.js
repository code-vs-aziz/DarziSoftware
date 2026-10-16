(function () {
	'use strict';

	function readFlash(key) {
		var el = document.querySelector('[data-notify-flash="' + key + '"]');
		return el ? el.textContent.trim() : '';
	}

	function openWhatsApp(url) {
		if (!url) {
			return;
		}
		window.open(url, '_blank', 'noopener');
	}

	document.addEventListener('DOMContentLoaded', function () {
		var url = readFlash('notify_whatsapp_url');
		var autoOpen = readFlash('notify_auto_open') === '1';

		if (url && autoOpen) {
			setTimeout(function () {
				openWhatsApp(url);
			}, 600);
		}
	});
})();
