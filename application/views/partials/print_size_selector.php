<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$default_size = (isset($default_size) && $default_size === 'a4') ? 'a4' : 'small';
?>
<label class="print-size-selector" for="printSizeSelect">
	<span class="print-size-selector__label"><?php echo t('print_paper_size'); ?></span>
	<select id="printSizeSelect" class="form-control form-control-sm print-size-selector__select" data-print-size-select>
		<option value="small"<?php echo ($default_size === 'small') ? ' selected' : ''; ?>><?php echo t('print_size_small'); ?></option>
		<option value="a4"<?php echo ($default_size === 'a4') ? ' selected' : ''; ?>><?php echo t('print_size_a4'); ?></option>
	</select>
</label>
<script>
(function () {
	if (window.__printSizeSelectorInit) {
		return;
	}
	window.__printSizeSelectorInit = true;

	function applyPrintSize(size) {
		size = (size === 'a4') ? 'a4' : 'small';
		var body = document.body;
		if (!body) {
			return;
		}
		body.classList.remove('print-size-small', 'print-size-a4');
		body.classList.add('print-size-' + size);
		body.setAttribute('data-print-size', size);

		var sheets = document.querySelectorAll('[data-print-sheet]');
		for (var i = 0; i < sheets.length; i++) {
			sheets[i].setAttribute('data-print-size', size);
		}

		var styleEl = document.getElementById('printPageSizeStyle');
		if (!styleEl) {
			styleEl = document.createElement('style');
			styleEl.id = 'printPageSizeStyle';
			document.head.appendChild(styleEl);
		}
		/* Width changes only — keep the same standard height (148mm). */
		if (size === 'a4') {
			styleEl.textContent = '@page { size: 210mm 148mm; margin: 0.5cm; }';
		} else {
			styleEl.textContent = '@page { size: 160mm 148mm; margin: 0.5cm; }';
		}

		try {
			window.localStorage.setItem('darzi_print_paper_size', size);
		} catch (e) {}
	}

	function init() {
		var select = document.querySelector('[data-print-size-select]');
		if (!select) {
			return;
		}
		var saved = '';
		try {
			saved = window.localStorage.getItem('darzi_print_paper_size') || '';
		} catch (e) {}
		if (saved === 'small' || saved === 'a4') {
			select.value = saved;
		}
		applyPrintSize(select.value);
		select.addEventListener('change', function () {
			applyPrintSize(select.value);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
</script>
