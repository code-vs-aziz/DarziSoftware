<?php
ob_start();
$erp_light_page = TRUE;
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$customer_search_url_prefix = rtrim(site_url('Customer/search_customer'), '/').'/';
$dashboard_summary = isset($dashboard_summary) && is_array($dashboard_summary) ? $dashboard_summary : array();
?>
<body class="animsition erp-app">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="dz-container">
						<?php $this->load->view('partials/dashboard_pro_body', array('dashboard_summary' => $dashboard_summary)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
	(function () {
		var prefix = <?php echo json_encode($customer_search_url_prefix, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

		window.dashboardHomeSearchSubmit = function (event) {
			if (event) {
				if (event.preventDefault) event.preventDefault();
				if (event.stopPropagation) event.stopPropagation();
			}
			var input = document.getElementById('dash-home-search');
			var term = input ? String(input.value || '').replace(/^\s+|\s+$/g, '') : '';
			if (term === '') {
				alert(<?php echo json_encode(t('customer_search_need_field'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
				return false;
			}
			var url = prefix + encodeURIComponent(term) + '?per_page=25';
			if (window.AppAjaxShell && typeof window.AppAjaxShell.load === 'function') {
				window.AppAjaxShell.load(url);
			} else {
				window.location.href = url;
			}
			return false;
		};
	})();
	</script>
</body>
</html>
