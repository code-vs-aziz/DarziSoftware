/**
 * Mobile drawer + bottom-nav shell behaviour
 */
(function () {
	'use strict';

	var OPEN_CLASS = 'dz-nav-open';

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function qsa(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function isOpen() {
		return document.body.classList.contains(OPEN_CLASS);
	}

	function setExpanded(open) {
		qsa('[data-dz-nav-toggle="true"]').forEach(function (el) {
			el.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		qsa('.hamburger').forEach(function (el) {
			el.classList.toggle('is-active', !!open);
		});
	}

	function openNav() {
		document.body.classList.add(OPEN_CLASS);
		setExpanded(true);
		var drawer = qs('#dz-mobile-drawer');
		if (drawer) {
			drawer.removeAttribute('aria-hidden');
		}
	}

	function closeNav() {
		document.body.classList.remove(OPEN_CLASS);
		setExpanded(false);
		var drawer = qs('#dz-mobile-drawer');
		if (drawer) {
			drawer.setAttribute('aria-hidden', 'true');
		}
	}

	function toggleNav() {
		if (isOpen()) {
			closeNav();
		} else {
			openNav();
		}
	}

	function onDocClick(e) {
		var t = e.target;
		if (!t || !t.closest) {
			return;
		}
		if (t.closest('[data-dz-nav-toggle="true"]')) {
			e.preventDefault();
			toggleNav();
			return;
		}
		if (t.closest('[data-dz-nav-close="true"]')) {
			e.preventDefault();
			closeNav();
			return;
		}
		if (isOpen() && t.closest('#dz-mobile-drawer a')) {
			closeNav();
		}
	}

	function onKey(e) {
		if (e.key === 'Escape' && isOpen()) {
			closeNav();
		}
	}

	function bind() {
		document.addEventListener('click', onDocClick, true);
		document.addEventListener('keydown', onKey);
		closeNav();
	}

	window.DzResponsiveShell = {
		open: openNav,
		close: closeNav,
		toggle: toggleNav,
		isOpen: isOpen
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}
})();
