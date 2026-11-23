(function (window, document) {
	'use strict';

	var TAB_CATEGORY = {
		'nav-Kurta': 'kurta',
		'nav-Pajama': 'pajama',
		'nav-Pent': 'pent',
		'nav-Shrit': 'shirt',
		'nav-Coat': 'coat',
		'nav-PentCoat': 'pentcoat',
		'nav-Waistcoat': 'waistcoat'
	};

	function byId(id) {
		return document.getElementById(id);
	}

	function clearHighlights(scope) {
		var root = scope || document;
		var annos = root.querySelectorAll('.measurement-anno.is-active');
		for (var i = 0; i < annos.length; i++) {
			annos[i].classList.remove('is-active');
		}
		var fields = root.querySelectorAll('.measurement-form-page .form-control.is-guide-active');
		for (var j = 0; j < fields.length; j++) {
			fields[j].classList.remove('is-guide-active');
		}
	}

	function findMeasureField(key) {
		if (!key) {
			return null;
		}
		var field = byId(key);
		if (field) {
			return field;
		}
		return document.querySelector('.measurement-fields-wrap input[name="' + key + '"]');
	}

	function highlightPair(key) {
		if (!key) {
			return;
		}
		clearHighlights();
		var anno = document.querySelector('.measurement-guide__view:not([hidden]) .measurement-anno[data-measure-key="' + key + '"]');
		var field = findMeasureField(key);
		if (anno) {
			anno.classList.add('is-active');
		}
		if (field) {
			field.classList.add('is-guide-active');
		}
	}

	function showGuideCategory(category) {
		var views = document.querySelectorAll('.measurement-guide__view');
		for (var i = 0; i < views.length; i++) {
			var match = views[i].getAttribute('data-guide-category') === category;
			views[i].classList.toggle('is-active', match);
			if (match) {
				views[i].removeAttribute('hidden');
			} else {
				views[i].setAttribute('hidden', 'hidden');
			}
		}
		clearHighlights();
	}

	function categoryFromTab(href) {
		if (!href) {
			return 'kurta';
		}
		var id = href.replace('#', '');
		return TAB_CATEGORY[id] || 'kurta';
	}

	function bindGuide() {
		var guide = byId('measurementGarmentGuide');
		if (!guide || guide.getAttribute('data-guide-bound') === '1') {
			return;
		}
		guide.setAttribute('data-guide-bound', '1');

		guide.addEventListener('click', function (event) {
			var anno = event.target.closest('.measurement-anno');
			if (!anno) {
				return;
			}
			var key = anno.getAttribute('data-measure-key');
			var field = findMeasureField(key);
			highlightPair(key);
			if (field) {
				field.focus();
				if (window.innerWidth < 992) {
					field.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
			}
		});

		guide.addEventListener('keydown', function (event) {
			if (event.key !== 'Enter' && event.key !== ' ') {
				return;
			}
			var anno = event.target.closest('.measurement-anno');
			if (!anno) {
				return;
			}
			event.preventDefault();
			anno.click();
		});

		document.addEventListener('focusin', function (event) {
			if (!event.target || !event.target.classList || !event.target.classList.contains('txtbox')) {
				return;
			}
			var target = event.target;
			var key = target.id || target.getAttribute('name') || '';
			highlightPair(key);
		});

		if (window.jQuery) {
			window.jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
				var href = event.target.getAttribute('href');
				showGuideCategory(categoryFromTab(href));
			});
		}

		showGuideCategory('kurta');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bindGuide);
	} else {
		bindGuide();
	}

	window.measurementGuideShowCategory = showGuideCategory;
})(window, document);
