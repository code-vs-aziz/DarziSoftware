<?php
ob_start();
$erp_light_page = TRUE;
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$customer_search_url_prefix = rtrim(site_url('Customer/search_customer'), '/').'/';
$suggest_url = site_url('Customer/search_suggest');
$measurement_edit_prefix = rtrim(site_url('Measurement/measurement_edit'), '/').'/';
$measurement_add_prefix = rtrim(site_url('Measurement/measurement_add'), '/').'/';
$customer_add_url = site_url('Customer/Customer_add');
?>
<body class="animsition erp-app erp-home">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="dz-container">
						<section class="dz-home" aria-label="<?php echo esc_attr(t('menu_dashboard_home')); ?>">
							<div class="dz-home__logo">
								<img
									src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=5"
									alt="<?php echo esc_attr(t('app_brand')); ?>"
									width="640"
									height="402"
									decoding="async"
								>
							</div>
							<div class="dz-home__actions">
								<form id="erp-home-search-form" class="dz-home__search" autocomplete="off" onsubmit="return erpHomeSearchSubmit(event);">
									<label class="sr-only" for="erp-home-search"><?php echo esc_html(t('dash_search_placeholder')); ?></label>
									<div class="dz-home__search-shell">
										<span class="dz-home__search-icon" aria-hidden="true"><?php echo ui_icon('search'); ?></span>
										<input
											class="dz-home__input"
											type="search"
											name="search"
											id="erp-home-search"
											placeholder="<?php echo esc_attr(t('dash_search_placeholder')); ?>"
											autocomplete="off"
											enterkeyhint="search"
											autofocus
											aria-autocomplete="list"
											aria-controls="erp-home-suggest"
											aria-expanded="false"
										>
										<button type="submit" class="dz-home__submit"><?php echo esc_html(t('app_search')); ?></button>
										<a href="<?php echo $customer_add_url; ?>" class="dz-home__add-customer" id="erp-home-add-customer">
											<span aria-hidden="true"><?php echo ui_icon('user-plus'); ?></span>
											<span><?php echo esc_html(t('dash_create_customer')); ?></span>
										</a>
									</div>
									<div id="erp-home-suggest" class="dz-home__suggest" role="listbox" hidden></div>
								</form>
							</div>
							<p class="dz-home__hint"><?php echo esc_html(t('home_search_hint')); ?></p>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
	(function () {
		var prefix = <?php echo json_encode($customer_search_url_prefix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var suggestUrl = <?php echo json_encode($suggest_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var measEditPrefix = <?php echo json_encode($measurement_edit_prefix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var measAddPrefix = <?php echo json_encode($measurement_add_prefix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var addUrl = <?php echo json_encode($customer_add_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var needTerm = <?php echo json_encode(t('customer_search_need_field'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var emptyMsg = <?php echo json_encode(t('home_search_empty'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var emptyHint = <?php echo json_encode(t('home_search_empty_hint'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var addLabel = <?php echo json_encode(t('home_search_add_customer'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		var input = document.getElementById('erp-home-search');
		var box = document.getElementById('erp-home-suggest');
		var addBtn = document.getElementById('erp-home-add-customer');
		var timer = null;
		var lastQ = '';
		var active = -1;
		var items = [];
		var reqId = 0;

		function go(url) {
			if (window.AppAjaxShell && typeof window.AppAjaxShell.load === 'function') {
				window.AppAjaxShell.load(url);
			} else {
				window.location.href = url;
			}
		}

		function measurementUrl(row) {
			var code = String(row && row.code ? row.code : '');
			if (!code) return '';
			if (row && row.has_measurement) {
				return measEditPrefix + code + '?return=order';
			}
			return measAddPrefix + code + '?return=order';
		}

		function escapeHtml(s) {
			return String(s || '')
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;');
		}

		function syncAddLink(term) {
			if (!addBtn) return;
			var q = String(term || '').replace(/^\s+|\s+$/g, '');
			var digits = q.replace(/\D+/g, '');
			var href = addUrl;
			if (digits.length >= 4) {
				href = addUrl + (addUrl.indexOf('?') >= 0 ? '&' : '?') + 'phone=' + encodeURIComponent(digits);
			}
			addBtn.setAttribute('href', href);
		}

		window.erpHomeSearchSubmit = function (event) {
			if (event && event.preventDefault) event.preventDefault();
			var term = input ? String(input.value || '').replace(/^\s+|\s+$/g, '') : '';
			if (!term) {
				alert(needTerm);
				return false;
			}
			if (active >= 0 && items[active] && items[active].url) {
				go(items[active].url);
				return false;
			}
			if (items.length === 1 && items[0].url) {
				go(items[0].url);
				return false;
			}
			go(prefix + encodeURIComponent(term) + '?per_page=25');
			return false;
		};

		function hideSuggest() {
			if (!box) return;
			box.hidden = true;
			box.innerHTML = '';
			items = [];
			active = -1;
			if (input) input.setAttribute('aria-expanded', 'false');
		}

		function renderEmpty(term) {
			if (!box) return;
			items = [];
			active = -1;
			syncAddLink(term);
			var href = addBtn ? addBtn.getAttribute('href') : addUrl;
			box.innerHTML = '<div class="dz-home__suggest-empty">'
				+ '<div class="dz-home__suggest-empty-title">' + escapeHtml(emptyMsg) + '</div>'
				+ '<div class="dz-home__suggest-empty-hint">' + escapeHtml(emptyHint) + '</div>'
				+ '<a class="dz-home__suggest-add" href="' + escapeHtml(href) + '">' + escapeHtml(addLabel) + '</a>'
				+ '</div>';
			box.hidden = false;
			if (input) input.setAttribute('aria-expanded', 'true');
		}

		function render(rows) {
			if (!box) return;
			items = rows || [];
			active = -1;
			if (!items.length) {
				renderEmpty(input ? input.value : '');
				return;
			}
			var html = '';
			for (var i = 0; i < items.length; i++) {
				var r = items[i];
				html += '<button type="button" class="dz-home__suggest-item" role="option" data-idx="' + i + '">'
					+ '<span class="dz-home__suggest-name">' + escapeHtml(r.name) + '</span>'
					+ '<span class="dz-home__suggest-meta">'
					+ escapeHtml(r.phone || '')
					+ (r.phone && r.naap ? ' · ' : '')
					+ escapeHtml(r.naap || '')
					+ '</span></button>';
			}
			box.innerHTML = html;
			box.hidden = false;
			if (input) input.setAttribute('aria-expanded', 'true');
		}

		function fetchSuggest(q) {
			if (q.length < 2) {
				hideSuggest();
				return;
			}
			if (q === lastQ) return;
			lastQ = q;
			syncAddLink(q);
			var myReq = ++reqId;
			var xhr = new XMLHttpRequest();
			xhr.open('GET', suggestUrl + '?q=' + encodeURIComponent(q), true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.onreadystatechange = function () {
				if (xhr.readyState !== 4) return;
				if (myReq !== reqId) return;
				if (xhr.status !== 200) {
					hideSuggest();
					return;
				}
				try {
					var data = JSON.parse(xhr.responseText);
					var rows = (data && data.results) ? data.results : [];
					for (var i = 0; i < rows.length; i++) {
						rows[i].url = measurementUrl(rows[i]);
						rows[i].name = rows[i].name || ('#' + rows[i].code);
					}
					render(rows);
				} catch (e) {
					hideSuggest();
				}
			};
			xhr.send();
		}

		if (input) {
			input.addEventListener('input', function () {
				var q = String(input.value || '').replace(/^\s+|\s+$/g, '');
				syncAddLink(q);
				clearTimeout(timer);
				timer = setTimeout(function () { fetchSuggest(q); }, 180);
			});
			input.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') {
					hideSuggest();
					return;
				}
				if (box.hidden) return;
				var nodes = box.querySelectorAll('.dz-home__suggest-item');
				if (!nodes.length) return;
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					active = Math.min(active + 1, items.length - 1);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					active = Math.max(active - 1, 0);
				} else if (e.key === 'Enter' && active >= 0 && items[active] && items[active].url) {
					e.preventDefault();
					go(items[active].url);
					return;
				} else {
					return;
				}
				for (var i = 0; i < nodes.length; i++) {
					nodes[i].classList.toggle('is-active', i === active);
				}
			});
		}

		if (box) {
			box.addEventListener('click', function (e) {
				var add = e.target.closest('.dz-home__suggest-add');
				if (add) {
					return;
				}
				var btn = e.target.closest('.dz-home__suggest-item');
				if (!btn) return;
				var idx = parseInt(btn.getAttribute('data-idx'), 10);
				if (items[idx] && items[idx].url) go(items[idx].url);
			});
		}

		document.addEventListener('click', function (e) {
			if (!e.target.closest('#erp-home-search-form')) hideSuggest();
		});
	})();
	</script>
</body>
</html>
<?php ob_end_flush(); ?>
