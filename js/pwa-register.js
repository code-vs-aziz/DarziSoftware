/**
 * Keep PWA installable (manifest + SW) while Offline Desk stays off.
 */
(function () {
	'use strict';
	if (!('serviceWorker' in navigator)) {
		return;
	}
	var swUrl = './sw.js?v=8';
	try {
		if (window.DARZI_PWA_CFG && window.DARZI_PWA_CFG.swUrl) {
			swUrl = window.DARZI_PWA_CFG.swUrl;
		}
	} catch (err) {
		/* ignore */
	}
	navigator.serviceWorker.register(swUrl, { scope: './' }).catch(function () {
		/* optional on unsupported hosts */
	});
})();
