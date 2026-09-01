<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$report_shell_export = isset($export_mode) ? (string) $export_mode : '';
if ($report_shell_export === 'excel' || $report_shell_export === 'pdf') {
	return;
}
?>
<!-- Standalone report pages use full page POST/redirect (no AJAX shell). -->
