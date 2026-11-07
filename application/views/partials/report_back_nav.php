<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$filter_url = isset($report_filter_url) ? (string) $report_filter_url : '';
$has_report = ! empty($report_has_report);
if ($filter_url === '' || ! $has_report) {
	return;
}
?>
<script>
(function () {
	var filterUrl = <?php echo json_encode($filter_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
	if (!filterUrl) return;
	try {
		var st = history.state || {};
		if (!st.dzReportResult) {
			history.replaceState({ dzReportFilter: true }, '', filterUrl);
			history.pushState({ dzReportResult: true }, '', window.location.href);
		}
	} catch (e) { /* ignore */ }
	window.addEventListener('popstate', function (ev) {
		var s = ev.state || {};
		if (s.dzReportFilter || !s.dzReportResult) {
			window.location.replace(filterUrl);
		}
	});
})();
</script>
