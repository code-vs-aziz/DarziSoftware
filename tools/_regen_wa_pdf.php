<?php
/**
 * Wrapper: php tools/_regen_wa_pdf.php <trnno>
 */
if (PHP_SAPI !== 'cli') {
	exit(1);
}
$trnno = isset($argv[1]) ? (int) $argv[1] : 0;
if ($trnno < 1) {
	fwrite(STDERR, "Usage: php tools/_regen_wa_pdf.php <trnno>\n");
	exit(1);
}
passthru('php '.escapeshellarg(dirname(__DIR__).'/index.php').' order regen_pdf_cli '.$trnno, $code);
exit($code);
