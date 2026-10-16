(function (window, document) {
	'use strict';

	var jq = window.jQuery;
	if (!jq) {
		return;
	}

	var contentSelector = '.main-content';
	var reportContentSelector = '.report-shell';
	var activeRequest = null;
	var suppressPop = false;
	var progressValue = 0;
	var loadingActive = false;
	var loadingStartedAt = 0;
	var progressRafId = null;
	var finishTimerId = null;
	var MIN_LOADING_MS = 0;
	var LOADER_SHOW_DELAY_MS = 300;
	var loaderShowTimerId = null;
	var skippedPathParts = [
		'/login',
		'/logout',
		'/process',
		'/process_status',
		'/delete',
		'/remove',
		'/print',
		'/order_print',
		'/transfer_print',
		'/print_blank',
		'/order_cam',
		'/order_edit',
		'/order_lifecycle_history',
		'/ready_return_stitching',
		'/ready_return_order_stitching',
		'/transfer_process_bulk',
		'/transfer_return_bulk',
		'/order_add1',
		'/order_add2',
		'/predefined_update',
		'/predefined_add',
		'/predefined_delete',
		'/predefined_reseed',
		'/process_edit',
		'/employee_edit',
		'/customer_edit',
		'/measurement_add',
		'/measurement_edit',
		'/customer_add',
		'/employee_add',
		'/search_post',
		'/save',
		'/save_user',
		'/delete_user',
		'/reset_password',
		'/transfer_order',
		'/transfer_return',
		'/transfer_print',
		'/transfer_process',
		'/transfer_process_bulk'
	];

	function getJq() {
		return window.jQuery || jq;
	}

	function lang(key, fallback) {
		if (window.APP_LANG && window.APP_LANG[key]) {
			return window.APP_LANG[key];
		}
		return fallback || key;
	}

	function el(id) {
		return document.getElementById(id);
	}

	function sameOrigin(url) {
		return url.origin === window.location.origin;
	}

	function normalizePath(path) {
		return String(path || '').toLowerCase();
	}

	function isDataTablesControlLink(link) {
		return !!(link && link.closest && link.closest('.dataTables_paginate, .dataTables_length'));
	}

	function shouldForceFullPageLink(link) {
		var href;
		var cls;
		if (!link) {
			return false;
		}
		href = link.getAttribute('href') || '';
		if (!href || href === '#' || href.indexOf('javascript:') === 0 || href.charAt(0) === '#') {
			return false;
		}
		if (link.getAttribute('data-no-ajax') === 'true' || link.getAttribute('data-app-modal-nav') === 'true') {
			return true;
		}
		if (link.getAttribute('data-toggle') === 'modal' || link.getAttribute('data-dismiss') === 'modal') {
			return false;
		}
		if (link.target && link.target !== '_self') {
			return false;
		}
		if (link.closest && link.closest('.pagination, .modal, .modal-footer, .customer-pagination-wrap')) {
			return true;
		}
		cls = link.className || '';
		if (/\bbtn(\s|$|-|_)/.test(cls)) {
			return true;
		}
		return false;
	}

	function forceFullPageNavigation(link, event) {
		if (event) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
		window.location.href = link.href;
	}

	function shouldSkipUrl(url, link) {
		var href = link ? link.getAttribute('href') : url.href;
		var path = normalizePath(url.pathname);
		var search = normalizePath(url.search);
		var i;

		if (!href || href === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) {
			return true;
		}
		if (link && (href.charAt(0) === '#' || link.getAttribute('data-toggle') === 'tab' || link.getAttribute('data-toggle') === 'pill')) {
			return true;
		}
		if (link && (link.getAttribute('data-toggle') === 'modal' || link.getAttribute('data-dismiss') === 'modal')) {
			return true;
		}
		if (link && isDataTablesControlLink(link)) {
			return true;
		}
		if (!sameOrigin(url)) {
			return true;
		}
		if (link && (link.target && link.target !== '_self')) {
			return true;
		}
		if (link && (link.hasAttribute('download') || link.getAttribute('data-no-ajax') === 'true')) {
			return true;
		}
		if (search.indexOf('export=excel') !== -1 || search.indexOf('export=pdf') !== -1) {
			return true;
		}
		if (/\.(pdf|xls|xlsx|csv|doc|docx|zip|rar|jpg|jpeg|png|gif|webp)$/i.test(path)) {
			return true;
		}
		for (i = 0; i < skippedPathParts.length; i++) {
			if (path.indexOf(skippedPathParts[i]) !== -1) {
				return true;
			}
		}

		return false;
	}

	function ensureLoader() {
		if (el('app-ajax-overlay')) {
			return;
		}
		if (!document.body) {
			return;
		}
		document.body.insertAdjacentHTML('beforeend',
			'<div id="app-ajax-overlay" aria-hidden="true" aria-live="polite">' +
				'<div class="app-ajax-overlay-box">' +
					'<div class="app-ajax-spinner" aria-hidden="true"><i class="fas fa-circle-notch fa-spin"></i></div>' +
					'<div class="app-ajax-progress-track"><div id="app-ajax-progress-bar"></div></div>' +
					'<div id="app-ajax-progress-pct">0%</div>' +
					'<div id="app-ajax-progress-label">' + lang('loading', 'Loading…') + '</div>' +
				'</div>' +
			'</div>' +
			'<div id="app-ajax-progress" aria-hidden="true"></div>'
		);
	}

	function updateProgress(pct) {
		pct = Math.max(0, Math.min(100, Math.round(pct)));
		if (pct < progressValue && loadingActive && pct !== 100) {
			return;
		}
		progressValue = pct;

		var pctNode = el('app-ajax-progress-pct');
		var barNode = el('app-ajax-progress-bar');
		var topNode = el('app-ajax-progress');
		if (pctNode) {
			pctNode.textContent = pct + '%';
		}
		if (barNode) {
			barNode.style.width = pct + '%';
		}
		if (topNode) {
			if (document.body.classList.contains('app-ajax-loading')) {
				topNode.style.transform = 'scaleX(' + (pct / 100) + ')';
			} else {
				topNode.style.transform = 'scaleX(0)';
			}
		}
	}

	function stopProgressLoop() {
		if (progressRafId) {
			window.cancelAnimationFrame(progressRafId);
			progressRafId = null;
		}
	}

	function progressLoop(startedAt) {
		if (!loadingActive) {
			return;
		}
		var elapsed = window.performance.now() - startedAt;
		var simulated = 90 * (1 - Math.exp(-elapsed / 900));
		if (simulated > progressValue) {
			updateProgress(simulated);
		}
		progressRafId = window.requestAnimationFrame(function () {
			progressLoop(startedAt);
		});
	}

	function startProgress() {
		stopProgressLoop();
		if (finishTimerId) {
			window.clearTimeout(finishTimerId);
			finishTimerId = null;
		}
		if (loaderShowTimerId) {
			window.clearTimeout(loaderShowTimerId);
			loaderShowTimerId = null;
		}
		ensureLoader();
		loadingActive = true;
		loadingStartedAt = Date.now();
		progressValue = 0;
		updateProgress(0);
		loaderShowTimerId = window.setTimeout(function () {
			loaderShowTimerId = null;
			if (!loadingActive) {
				return;
			}
			document.body.classList.add('app-ajax-loading');
			var overlay = el('app-ajax-overlay');
			if (overlay) {
				overlay.setAttribute('aria-hidden', 'false');
			}
		}, LOADER_SHOW_DELAY_MS);
		progressLoop(window.performance.now());
	}

	function finishProgress() {
		stopProgressLoop();
		if (loaderShowTimerId) {
			window.clearTimeout(loaderShowTimerId);
			loaderShowTimerId = null;
		}
		updateProgress(100);
		finishTimerId = window.setTimeout(function () {
			loadingActive = false;
			document.body.classList.remove('app-ajax-loading');
			var overlay = el('app-ajax-overlay');
			if (overlay) {
				overlay.setAttribute('aria-hidden', 'true');
			}
			updateProgress(0);
			finishTimerId = null;
		}, 280);
	}

	function completeProgress() {
		var elapsed = Date.now() - loadingStartedAt;
		var wait = Math.max(0, MIN_LOADING_MS - elapsed);
		window.setTimeout(finishProgress, wait);
	}

	function setLoading(isLoading) {
		if (isLoading) {
			startProgress();
		} else {
			completeProgress();
		}
	}

	function normalizeNavHref(rawHref) {
		try {
			var parsed = new URL(rawHref, window.location.href);
			var path = parsed.pathname.replace(/\/+$/, '').toLowerCase();
			return path;
		} catch (err) {
			return String(rawHref || '').replace(/\/+$/, '').toLowerCase();
		}
	}

	function setActiveNavByHref(rawHref) {
		var $ = getJq();
		var target = normalizeNavHref(rawHref);
		var $items = $('.navbar-sidebar .dz-nav__item, .navbar-mobile .dz-nav__item');

		$items.removeClass('is-active active');
		$items.each(function () {
			var link = $(this).find('a[href]').get(0);
			if (link && normalizeNavHref(link.href) === target) {
				$(this).addClass('is-active');
			}
		});

		$('.dz-bottom-nav__item').removeClass('is-active');
		$('.dz-bottom-nav a.dz-bottom-nav__item').each(function () {
			if (normalizeNavHref(this.href) === target) {
				$(this).addClass('is-active');
			}
		});
	}

	function updateActiveLink(url) {
		setActiveNavByHref(url.href);
	}

	function closeMobileMenu() {
		if (window.DzResponsiveShell && typeof window.DzResponsiveShell.close === 'function') {
			window.DzResponsiveShell.close();
			return;
		}
		var $ = getJq();
		document.body.classList.remove('dz-nav-open');
		$('.hamburger').removeClass('is-active');
		$('[data-dz-nav-toggle="true"]').attr('aria-expanded', 'false');
	}

	function clearLegacyLoaders() {
		var $ = getJq();
		$('body').removeClass('fade-in fade-out modal-open');
		$('.page-loader, .animsition-loading, .animsition-overlay-slide, .modal-backdrop').remove();
		$('.modal.show').removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
		if (window.AppModal && typeof window.AppModal.cleanup === 'function') {
			window.AppModal.cleanup();
		}
	}

	function isOrderCameraPage(url) {
		var path = normalizePath(url ? url.pathname : window.location.pathname);
		return /\/order\//i.test(path) && (
			path.indexOf('/order_add') !== -1 ||
			path.indexOf('/order_edit') !== -1 ||
			path.indexOf('/order_view') !== -1
		);
	}

	function cleanupOrderCamera(url) {
		if (isOrderCameraPage(url)) {
			return;
		}
		var panel = document.getElementById('orderCameraFloat');
		if (!panel) {
			return;
		}
		if (window.Webcam && typeof window.Webcam.reset === 'function') {
			try {
				window.Webcam.reset();
			} catch (err) {
				/* ignore webcam teardown errors */
			}
		}
		if (panel.parentNode) {
			panel.parentNode.removeChild(panel);
		}
	}

	function injectPageStyles(doc) {
		var $ = getJq();
		$('style[data-app-ajax-style="true"]').remove();
		Array.prototype.forEach.call(doc.querySelectorAll('style'), function (style) {
			var clone = style.cloneNode(true);
			clone.setAttribute('data-app-ajax-style', 'true');
			document.head.appendChild(clone);
		});
	}

	function executeInlineScripts(doc) {
		var $ = getJq();
		var scripts = doc.querySelectorAll('script:not([src])');
		Array.prototype.forEach.call(scripts, function (script) {
			var code = script.text || script.textContent || script.innerHTML || '';
			if ($.trim(code) === '') {
				return;
			}
			if (/\.dataTable\s*\(|\.DataTable\s*\(/i.test(code)) {
				return;
			}
			try {
				window.eval(code);
			} catch (err) {
				if (window.console && console.warn) {
					console.warn('AJAX page script failed:', err);
				}
			}
		});
	}

	function isDataTableListPage(path) {
		return path.indexOf('/transfer_table') !== -1
			|| path.indexOf('/search_customer') !== -1
			|| /\/employee\/?$/.test(path)
			|| /\/customer\/?$/.test(path)
			|| /\/suit\/?$/.test(path)
			|| /\/style\/?$/.test(path)
			|| /\/embroidery\/?$/.test(path)
			|| /\/button\/?$/.test(path);
	}

	function isDataEntryFormPage(path) {
		return /\/(suit_add|suit_edit|style_add|style_edit|embroidery_add|embroidery_edit|employee_add|employee_edit|customer_add|customer_edit|measurement_add|measurement_edit|order_edit)\/?$/i.test(path);
	}

	function scheduleDataTableInit() {
		var run = function () {
			if (typeof window.reinitAllDataTables === 'function') {
				window.reinitAllDataTables();
			} else {
				initPluginsLegacyDataTables();
			}
		};
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(run);
		});
	}

	function initPluginsLegacyDataTables() {
		var $ = getJq();
		if ($.fn.DataTable) {
			$('table#example').not('.order-detail-table').each(function () {
				if ($.fn.DataTable.isDataTable(this)) {
					$(this).DataTable().destroy();
				}
				$(this).DataTable({ pagingType: 'full_numbers' });
			});
		}
	}

	function initPlugins() {
		var $ = getJq();
		if (typeof window.appRefreshCsrfForms === 'function') {
			window.appRefreshCsrfForms();
		}
		scheduleDataTableInit();
		if ($.fn.select2) {
			$('select.select2, select.js-select2').each(function () {
				if (!$(this).data('select2')) {
					$(this).select2();
				}
			});
		}
		if ($.fn.setUrduInput) {
			$('#Name, #City, #Remarks, #Country, #Address, #suitName').each(function () {
				var $field = $(this);
				if (!$field.data('urduBound')) {
					$field.setUrduInput();
					$field.data('urduBound', 1);
				}
			});
			$('.suit-urdu-field, .master-urdu-field, .customer-urdu-field, .order-filter-urdu-field').each(function () {
				var $field = $(this);
				if (!$field.data('urduBound')) {
					$field.setUrduInput();
					$field.data('urduBound', 1);
				}
			});
		}
		if ($.fn.tooltip) {
			$('[data-toggle="tooltip"]').tooltip();
		}
		if (typeof window.initConfigPredefinedPage === 'function' && ($('#config-order-predefined').length || $('#config-business').length)) {
			window.initConfigPredefinedPage();
		}
		if (typeof window.initCashEntryPage === 'function' && $('#cash-entry-form').length) {
			window.initCashEntryPage();
		}
	}

	function resolveContentSelector(root) {
		if (root && root.querySelector && root.querySelector(reportContentSelector)) {
			return reportContentSelector;
		}
		if (root === document && document.querySelector(reportContentSelector)) {
			return reportContentSelector;
		}
		return contentSelector;
	}

	function resolveSwapNode(root, selector) {
		var node = root.querySelector(selector);
		var parent;
		if (!node) {
			return null;
		}
		parent = node.parentElement;
		if (parent && parent.tagName === 'FORM' && parent.getAttribute('action')) {
			return parent;
		}
		return node;
	}

	function replaceContent(html, url, pushHistory) {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var incomingSelector = resolveContentSelector(doc);
		var currentSelector = resolveContentSelector(document);
		var incoming = resolveSwapNode(doc, incomingSelector);
		var current = resolveSwapNode(document, currentSelector);
		var title = doc.querySelector('title');

		if (!incoming || !current || incomingSelector !== currentSelector) {
			window.location.href = url.href;
			return;
		}

		cleanupOrderCamera(url);

		if (typeof window.destroyStaleDataTables === 'function') {
			window.destroyStaleDataTables();
		}

		if (incomingSelector === reportContentSelector) {
			current.outerHTML = incoming.outerHTML;
		} else {
			current.outerHTML = incoming.outerHTML;
		}
		if (title && title.textContent) {
			document.title = title.textContent;
		}
		if (doc.documentElement) {
			document.documentElement.lang = doc.documentElement.lang || document.documentElement.lang;
			document.documentElement.dir = doc.documentElement.dir || document.documentElement.dir;
		}
		if (pushHistory) {
			window.history.pushState({ appAjax: true, url: url.href }, '', url.href);
		}
		if (currentSelector === contentSelector) {
			updateActiveLink(url);
			closeMobileMenu();
		}
		injectPageStyles(doc);
		executeInlineScripts(doc);
		clearLegacyLoaders();
		window.scrollTo(0, 0);
		if (currentSelector === contentSelector) {
			initPlugins();
			getJq()(document).trigger('app:ajax-loaded', [url.href]);
		} else {
			getJq()(document).trigger('app:ajax-loaded', [url.href]);
		}
	}

	function handleAjaxResponse(html, url, pushHistory) {
		if (!sameOrigin(url) || normalizePath(url.pathname).indexOf('/login') !== -1) {
			window.location.href = url.href;
			return;
		}
		replaceContent(html, url, pushHistory);
	}

	function ajaxRequest(options) {
		var $ = getJq();
		if (activeRequest && activeRequest.readyState !== 4) {
			activeRequest.abort();
		}

		setLoading(true);

		activeRequest = $.ajax($.extend({
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			dataType: 'html',
			timeout: 45000,
			xhr: function () {
				var xhr = new window.XMLHttpRequest();
				xhr.addEventListener('progress', function (evt) {
					if (evt.lengthComputable && evt.total > 0) {
						var pct = Math.round((evt.loaded / evt.total) * 92);
						updateProgress(Math.max(progressValue, pct));
					}
				});
				return xhr;
			}
		}, options)).done(function (html, status, xhr) {
			var responseUrl = xhr.responseURL ? new URL(xhr.responseURL, window.location.href) : options.urlObj;
			handleAjaxResponse(html, responseUrl, options.pushHistory !== false);
		}).fail(function (xhr, status) {
			if (status !== 'abort') {
				loadingActive = false;
				stopProgressLoop();
				document.body.classList.remove('app-ajax-loading');
				window.location.href = options.fallbackUrl || options.urlObj.href;
			}
		}).always(function (_xhr, status) {
			if (status !== 'abort') {
				setLoading(false);
			}
			activeRequest = null;
		});
	}

	function loadUrl(rawUrl, pushHistory) {
		var url = new URL(rawUrl, window.location.href);
		ajaxRequest({
			url: url.href,
			method: 'GET',
			urlObj: url,
			fallbackUrl: url.href,
			pushHistory: pushHistory
		});
	}

	function loadFormPost(form, pushHistory) {
		var action = form.getAttribute('action') || window.location.href;
		var url = new URL(action, window.location.href);
		var formData = new FormData(form);

		ajaxRequest({
			url: url.href,
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			urlObj: url,
			fallbackUrl: url.href,
			pushHistory: pushHistory
		});
	}

	function onDocumentClick(event) {
		var link = event.target.closest ? event.target.closest('a[href]') : null;
		var url;

		if (!link) {
			return;
		}
		if (event.which !== 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return;
		}
		if (link.target && link.target !== '_self') {
			return;
		}
		if (link.getAttribute('data-app-logout') === 'true' || link.getAttribute('data-app-modal-nav') === 'true') {
			forceFullPageNavigation(link, event);
			return;
		}
		if (isDataTablesControlLink(link)) {
			return;
		}
		if (shouldForceFullPageLink(link)) {
			forceFullPageNavigation(link, event);
			return;
		}

		url = new URL(link.getAttribute('href'), window.location.href);
		if (isDataTableListPage(normalizePath(url.pathname)) || isDataEntryFormPage(normalizePath(url.pathname))) {
			forceFullPageNavigation(link, event);
			return;
		}
		if (shouldSkipUrl(url, link)) {
			return;
		}

		if (link.closest && link.closest('.dz-nav__item')) {
			setActiveNavByHref(link.href);
		}

		event.preventDefault();
		loadUrl(url.href, true);
	}

	function onDocumentSubmit(event) {
		var form = event.target;
		var method;
		var action;
		var url;
		var params;

		if (!form || form.tagName !== 'FORM') {
			return;
		}
		if (event.defaultPrevented || form.target || form.getAttribute('data-no-ajax') === 'true') {
			return;
		}

		method = String(form.getAttribute('method') || 'GET').toUpperCase();
		action = form.getAttribute('action') || window.location.href;
		url = new URL(action, window.location.href);
		if (shouldSkipUrl(url, null)) {
			return;
		}

		if (method === 'GET') {
			params = new URLSearchParams(new FormData(form));
			url.search = params.toString();
			event.preventDefault();
			loadUrl(url.href, true);
			return;
		}

		if (method === 'POST') {
			if (form.getAttribute('data-ajax') !== 'true') {
				return;
			}
			if (form.enctype && form.enctype.indexOf('multipart') !== -1 && form.querySelector('input[type="file"]')) {
				return;
			}
			event.preventDefault();
			loadFormPost(form, true);
		}
	}

	function initShell() {
		ensureLoader();
		suppressPop = true;
		window.history.replaceState({ appAjax: true, url: window.location.href }, '', window.location.href);
		suppressPop = false;
		clearLegacyLoaders();
		initPlugins();
	}

	document.addEventListener('click', onDocumentClick, true);
	document.addEventListener('submit', onDocumentSubmit, true);

	window.addEventListener('popstate', function () {
		if (suppressPop) {
			return;
		}
		loadUrl(window.location.href, false);
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initShell);
	} else {
		initShell();
	}

	window.addEventListener('pageshow', function (event) {
		if (loadingActive) {
			return;
		}
		stopProgressLoop();
		document.body.classList.remove('app-ajax-loading');
		var overlay = el('app-ajax-overlay');
		if (overlay) {
			overlay.setAttribute('aria-hidden', 'true');
		}
		updateProgress(0);
		clearLegacyLoaders();
	});

	window.AppAjaxShell = {
		load: function (url) {
			loadUrl(url, true);
		},
		reinit: initPlugins,
		shouldSkipUrl: shouldSkipUrl
	};
})(window, document);
