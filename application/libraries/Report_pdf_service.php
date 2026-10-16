<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Professional PDF generation for reports (mPDF).
 */
class Report_pdf_service
{
	/** @var CI_Controller */
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$autoload = FCPATH.'vendor-php/autoload.php';
		if (is_file($autoload))
		{
			require_once $autoload;
		}
	}

	/**
	 * @param array $payload from Report_data_service
	 * @return array{ok:bool,path?:string,filename?:string,error?:string}
	 */
	public function generate_file(array $payload, $key = 'report')
	{
		if (empty($payload['ok']))
		{
			return array('ok' => FALSE, 'error' => isset($payload['error']) ? $payload['error'] : 'bad_payload');
		}
		if (!class_exists('\\Mpdf\\Mpdf'))
		{
			return array('ok' => FALSE, 'error' => 'mpdf_missing');
		}

		$dir = FCPATH.'uploads/reports';
		if (!is_dir($dir) && !@mkdir($dir, 0755, TRUE))
		{
			return array('ok' => FALSE, 'error' => 'mkdir_failed');
		}
		$tempDir = APPPATH.'cache/mpdf';
		if (!is_dir($tempDir))
		{
			@mkdir($tempDir, 0755, TRUE);
		}

		$html = $this->CI->load->view('reports/pdf_document', array('payload' => $payload), TRUE);
		$safeKey = preg_replace('/[^a-z0-9_\-]+/i', '_', (string) $key);
		$filename = $safeKey.'_'.date('Ymd_His').'.pdf';
		$path = $dir.'/'.$filename;

		try
		{
			$mpdf = $this->create_mpdf($tempDir);
			$mpdf->SetTitle(isset($payload['title']) ? $payload['title'] : 'Report');
			$mpdf->WriteHTML($html);
			$mpdf->Output($path, \Mpdf\Output\Destination::FILE);
		}
		catch (Exception $e)
		{
			return array('ok' => FALSE, 'error' => 'pdf_failed', 'detail' => $e->getMessage());
		}

		if (!is_file($path))
		{
			return array('ok' => FALSE, 'error' => 'write_failed');
		}

		return array(
			'ok' => TRUE,
			'path' => $path,
			'filename' => $filename,
			'url' => base_url('uploads/reports/'.$filename),
		);
	}

	protected function create_mpdf($tempDir)
	{
		$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];
		$fontDirs[] = FCPATH.'application/fonts';

		$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];
		$fontData['lateef'] = array(
			'R' => 'LateefRegOT.ttf',
			'useOTL' => 0xFF,
			'useKashida' => 75,
		);

		return new \Mpdf\Mpdf(array(
			'mode' => 'utf-8',
			'format' => 'A4-L',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 12,
			'margin_bottom' => 12,
			'tempDir' => $tempDir,
			'fontDir' => $fontDirs,
			'fontdata' => $fontData,
			'default_font' => 'dejavusans',
			'default_font_size' => 9,
		));
	}
}
