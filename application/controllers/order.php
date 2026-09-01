<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends MY_Controller {

	private function order_submit_token()
	{
		$this->load->library('session');
		$token = md5(uniqid('', TRUE));
		$this->session->set_userdata('order_submit_token', $token);
		return $token;
	}

	private function is_valid_order_submit_token()
	{
		$this->load->library('session');
		$post_token = (string) $this->input->post('order_submit_token', TRUE);
		$session_token = (string) $this->session->userdata('order_submit_token');
		if ($post_token === '' || $session_token === '' || $post_token !== $session_token)
		{
			return FALSE;
		}
		$this->session->unset_userdata('order_submit_token');
		return TRUE;
	}

	private function business_config()
	{
		$this->load->model('Business_config_model');
		return $this->Business_config_model->get_config();
	}

	private static $order_garment_types = array('Kurta', 'Pajama', 'Pent', 'Shirt', 'Coat', 'Waistcoat');

	private function _order_print_slips_data($trnno)
	{
		$trnno = (int) $trnno;
		$business_config = $this->business_config();
		$embroidery = $this->master_data->get_dat(array('embroidery'));
		$style = $this->master_data->get_dat(array('style'));
		$suit = $this->master_data->get_dat(array('suit'));
		$print_img_base = rtrim(str_replace('\\', '/', FCPATH), '/').'/';
		$slips = array();
		$sr = 0;
		$types = $this->order_query->get_order_distinct_types($trnno);
		$ordered_types = array();

		foreach (self::$order_garment_types as $type)
		{
			if (in_array($type, $types, TRUE))
			{
				$ordered_types[] = $type;
			}
		}
		foreach ($types as $type)
		{
			if ( ! in_array($type, $ordered_types, TRUE))
			{
				$ordered_types[] = $type;
			}
		}

		foreach ($ordered_types as $type)
		{
			$where = 'order_mst.trnno = '.$trnno.' and order_dtl.type = '.$this->db->escape($type);
			$order = $this->order_query->get_order($where);
			if (empty($order))
			{
				continue;
			}
			$sr++;
			$slips[] = array(
				'embroidery' => $embroidery,
				'style' => $style,
				'suit' => $suit,
				'order' => $order,
				'type' => $type,
				'business_config' => $business_config,
				'SR' => $sr,
				'pdf_mode' => FALSE,
				'print_img_base' => $print_img_base,
			);
		}

		return $slips;
	}

	private function _render_order_print_slips($trnno)
	{
		$trnno = (int) $trnno;
		$slips = $this->_order_print_slips_data($trnno);
		if ( ! empty($slips))
		{
			foreach ($slips as $slip)
			{
				$slip['pdf_mode'] = FALSE;
				$this->load->view('order_print', $slip);
			}
			return TRUE;
		}
		$data['trnno'] = $trnno;
		$data['order_header'] = $this->order_query->get_order_header($trnno);
		$this->load->view('order_print_missing', $data);
		return FALSE;
	}

	private function _normalize_whatsapp_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if ($digits === '')
		{
			return '';
		}
		if (strpos($digits, '92') === 0 && strlen($digits) >= 12)
		{
			return $digits;
		}
		if ($digits[0] === '0' && strlen($digits) >= 11)
		{
			return '92'.substr($digits, 1);
		}
		if (strlen($digits) === 10 && $digits[0] === '3')
		{
			return '92'.$digits;
		}

		return $digits;
	}

	private function _load_composer_autoload()
	{
		$autoload = FCPATH.'vendor-php/autoload.php';
		if ( ! is_file($autoload))
		{
			throw new Exception('Composer autoload not found. Run composer install.');
		}
		require_once $autoload;
	}

	private function _pdf_print_styles()
	{
		return '
body, .pdf-body { margin: 0; padding: 0; color: #000; direction: ltr; }
.order-pdf { direction: ltr; width: 100%; }
.order-pdf, .order-pdf table, .order-pdf td, .order-pdf th, .order-pdf span {
	font-family: lateef, xbriyaz, sans-serif;
	font-size: 12pt;
	line-height: 18px;
	font-weight: normal;
}
.order-pdf b, .order-pdf th, .order-pdf .lbl { font-weight: bold; }
.order-pdf .val { font-weight: normal; }
.order-pdf .body { table-layout: fixed; width: 100%; border-collapse: collapse; }
.order-pdf .body .col-meas { width: 88mm; vertical-align: top; padding-right: 3mm; }
.order-pdf .body .col-items { width: 98mm; vertical-align: top; }
.order-pdf .meas { table-layout: fixed; width: 100%; border-collapse: collapse; direction: ltr; }
.order-pdf .meas td { font-size: 10pt; padding: 1px 2px; vertical-align: middle; overflow: hidden; }
.order-pdf .meas .m-val { text-align: left; font-weight: normal; width: 14mm; }
.order-pdf .meas .m-lbl { text-align: right; font-weight: bold; }
.order-pdf .meas .m-gap { width: 3mm; padding: 0; }
.order-pdf .info-wrap { width: 100%; border-collapse: collapse; }
.order-pdf .info-wrap td { text-align: right; padding: 0; }
.order-pdf .info { table-layout: fixed; width: 186mm; direction: rtl; border-collapse: collapse; }
.order-pdf .info td.i-val { text-align: right; font-weight: normal; border-bottom: 1px dotted #000; padding: 1px 4px 2px; vertical-align: bottom; white-space: nowrap; }
.order-pdf .info th.i-lbl { text-align: right; font-weight: bold; padding: 1px 4px 2px; vertical-align: bottom; white-space: nowrap; }
.order-pdf .info th.i-lbl.i-left { text-align: left; font-weight: bold; }
.order-pdf .info td.i-gap { border: none; padding: 0; height: 10px; }
.order-pdf .info td.i-blank { border: none; padding: 0; }
.order-pdf .items { table-layout: fixed; width: 100%; border-collapse: collapse; direction: ltr; margin-bottom: 3px; }
.order-pdf .items td { font-size: 10pt; padding: 2px; vertical-align: top; word-wrap: break-word; overflow: hidden; }
.order-pdf .items td.val { font-weight: normal; text-align: left; }
.order-pdf .items td.lbl { font-weight: bold; text-align: right; }
.order-pdf .items .i-no { width: 8mm; text-align: right; font-weight: bold; }
.order-pdf .items .i-price { width: 14mm; text-align: left; font-weight: normal; }
.order-pdf .items .i-img { width: 16mm; text-align: center; }
.order-pdf .badge { background: #000; color: #fff; padding: 2px 8px; font-size: 10pt; font-weight: bold; }
.order-pdf .hdr { table-layout: fixed; width: 100%; border-collapse: collapse; margin-bottom: 2mm; }
.order-pdf .hdr td { vertical-align: top; padding: 2px; }
.order-pdf .hdr .hdr-phone { font-weight: bold; font-size: 11pt; text-align: center; }
.order-pdf .hdr .hdr-trnno { font-weight: bold; font-size: 12pt; text-align: left; vertical-align: middle; }
.order-pdf .hdr .hdr-address { font-weight: bold; font-size: 11pt; text-align: right; direction: rtl; margin-bottom: 2px; }
.order-pdf .hdr .hdr-badges { text-align: right; direction: rtl; vertical-align: top; }
.order-pdf .hdr .hdr-badge { background: #000; color: #fff; font-weight: bold; font-size: 9pt; padding: 2px 8px; margin-left: 4px; border-radius: 8px; }
.order-pdf .ftr { direction: rtl; border-collapse: collapse; }
.order-pdf .ftr td { background-color: #BBB; }
.order-pdf .ftr-big { font-size: 24px; font-weight: bold; text-align: center; }
.order-pdf .ftr-sm { font-size: 11pt; font-weight: bold; text-align: right; }
';
	}

	private function _create_order_mpdf($tempDir)
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
		$fontData['xbriyaz'] = array(
			'R' => 'XB Riyaz.ttf',
			'useOTL' => 0xFF,
			'useKashida' => 75,
		);

		$mpdf = new \Mpdf\Mpdf(array(
			'mode' => 'utf-8',
			'format' => 'Letter',
			'margin_left' => 10,
			'margin_right' => 10,
			'margin_top' => 8,
			'margin_bottom' => 8,
			'tempDir' => $tempDir,
			'fontDir' => $fontDirs,
			'fontdata' => $fontData,
			'default_font' => 'lateef',
			'default_font_size' => 12,
			'autoScriptToLang' => FALSE,
			'autoLangToFont' => FALSE,
		));
		$mpdf->SetDirectionality('ltr');

		return $mpdf;
	}

	private function _generate_order_pdf_html($trnno)
	{
		$slips = $this->_order_print_slips_data($trnno);
		if (empty($slips))
		{
			return '';
		}

		$html = '<!DOCTYPE html><html lang="ur" dir="ltr"><head><meta charset="UTF-8"><style>'
			.$this->_pdf_print_styles()
			.'</style></head><body class="pdf-body">';

		$first = TRUE;
		foreach ($slips as $data)
		{
			if ( ! $first)
			{
				$html .= '<pagebreak />';
			}
			$first = FALSE;
			$html .= $this->load->view('order_pdf', $data, TRUE);
		}

		$html .= '</body></html>';

		return $html;
	}

	private function _get_order_customer_phone($trnno)
	{
		$trnno = (int) $trnno;
		$q = $this->db->select('customer.Phone')
			->from('order_mst')
			->join('measurement', 'measurement.code = order_mst.naap_no')
			->join('customer', 'customer.code = measurement.customer_code')
			->where('order_mst.trnno', $trnno)
			->limit(1)
			->get();
		if ($q->num_rows() === 0)
		{
			return '';
		}

		return (string) $q->row()->Phone;
	}

	public function order_whatsapp_pdf()
	{
		$this->load->helper('url');
		$this->load->library('session');
		@ini_set('display_errors', '0');
		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}
		header('Content-Type: application/json; charset=UTF-8');

		if ( ! $this->session->userdata('user'))
		{
			echo json_encode(array('success' => FALSE, 'message' => 'Session expired. Please log in again.'));
			return;
		}

		$trnno = (int) $this->input->post('trnno', TRUE);
		if ($trnno <= 0)
		{
			echo json_encode(array('success' => FALSE, 'message' => 'Invalid order number.'));
			return;
		}

		$phone = $this->_normalize_whatsapp_phone($this->_get_order_customer_phone($trnno));
		if ($phone === '' || strlen($phone) < 11)
		{
			echo json_encode(array('success' => FALSE, 'message' => 'Customer phone number is missing or invalid.'));
			return;
		}

		try
		{
			$this->_load_composer_autoload();
			ob_start();
			$html = $this->_generate_order_pdf_html($trnno);
			$leaked = ob_get_clean();
			if ($leaked !== '')
			{
				log_message('error', 'order_whatsapp_pdf view leak: '.$leaked);
			}
			if ($html === '')
			{
				echo json_encode(array('success' => FALSE, 'message' => 'Order not found.'));
				return;
			}

			$dir = FCPATH.'uploads/orders/';
			if ( ! is_dir($dir))
			{
				mkdir($dir, 0755, TRUE);
			}

			$filename = 'order_'.$trnno.'.pdf';
			$filepath = $dir.$filename;
			$tempDir = APPPATH.'cache/mpdf';
			if ( ! is_dir($tempDir))
			{
				mkdir($tempDir, 0755, TRUE);
			}

			$mpdf = $this->_create_order_mpdf($tempDir);
			$mpdf->WriteHTML($html);
			$mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

			echo json_encode(array(
				'success' => TRUE,
				'phone' => $phone,
				'download_url' => site_url('Order/order_pdf_download/'.$trnno),
				'filename' => $filename,
				'message' => 'آپ کا آرڈر #'.$trnno.'۔',
			));
		}
		catch (Throwable $e)
		{
			if (ob_get_level() > 0)
			{
				ob_end_clean();
			}
			log_message('error', 'order_whatsapp_pdf: '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
			echo json_encode(array('success' => FALSE, 'message' => 'Could not generate PDF. Please try again.'));
		}
	}

	public function order_pdf_download($trnno = 0)
	{
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			show_error('Unauthorized', 401);
		}

		$trnno = (int) $trnno;
		if ($trnno <= 0)
		{
			$trnno = (int) $this->uri->segment(3);
		}
		if ($trnno <= 0)
		{
			show_error('Invalid order number.', 400);
		}

		$filepath = FCPATH.'uploads/orders/order_'.$trnno.'.pdf';
		if ( ! is_file($filepath))
		{
			show_error('PDF not found. Click WhatsApp on the order print page first.', 404);
		}

		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		$filename = 'order_'.$trnno.'.pdf';
		$inline = ($this->input->get('inline') === '1');
		header('Content-Type: application/pdf');
		header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.$filename.'"');
		header('Content-Length: '.filesize($filepath));
		readfile($filepath);
		exit;
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 
	 
	 
	 
		public function Order_deactive()
	{	
	// $this->load->view('Order');
	 $this->load->model("insert");
	 $this->load->helper('url'); 
	 $last = $this->uri->segment(3);
	 $this->db->query("update order_mst set ostatus = 1 where trnno='".$last."'");
	 $this->dashboard_data->clear_dashboard_summary_cache();
	// $this->db->query("delete  from order_dtl where order_trnno='".$last."'");
	// row_delete
	redirect('Order/order_view_det'); 
	 
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	public function Order_delete()
	{	
	// $this->load->view('Order');
	 $this->load->model("insert");
	 $this->load->helper('url'); 
	 $last = $this->uri->segment(3);
	 $this->db->query("delete  from order_mst where trnno='".$last."'");
	 $this->db->query("delete  from order_dtl where order_trnno='".$last."'");
	// row_delete
	redirect('Order/order_view_det'); 
	 
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	public function Order_add()
	{
	 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
	$data['order_submit_token'] = $this->order_submit_token();
	$data['staff_employees'] = $this->employee_query->get_staff_employees();
 	$this->load->view('Order', $data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
		public function Order_Print()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
		$trnno = (int) $this->master_data->get_max_id('trnno', 'order_mst');
		$this->_render_order_print_slips($trnno);
	}
	
	
	
	
		public function print_blank()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	   $data['business_config'] = $this->business_config();
	//  $data["store"]= $this->master_data->get_dat(array('branch_codi'));
		
		 $this->load->view('print_blank', $data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
		//$this->load->view('order_print',$data03);
	}	

		
	}
	
			
	
	
			public function Order_Print_det()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		$trnno = (int) $this->uri->segment(3);
		$this->_render_order_print_slips($trnno);
	}
	
	public function Order_edit()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		$trnno = (int) $this->uri->segment(3);
		if ($trnno < 1)
		{
			show_error('Invalid order number.', 400);
			return;
		}
		$this->load->model('Order_predefined_model');

		$data3 = array();
		$data3['suit'] = $this->master_data->get_dat(array('suit'));
		if ( ! is_array($data3['suit']))
		{
			$data3['suit'] = array();
		}
		$data3['order_predefined_by_row'] = $this->Order_predefined_model->get_grouped_active();
		$data3['Measurement_data'] = $this->order_query->get_order_mst('order_mst.trnno = '.$trnno);
		if ( ! is_array($data3['Measurement_data']))
		{
			$data3['Measurement_data'] = array();
		}
		$data3['order_mst'] = ! empty($data3['Measurement_data'][0]) ? $data3['Measurement_data'][0] : NULL;
		$data3['order_data'] = $this->master_data->get_dat_where('order_dtl', 'order_dtl.order_trnno = '.$trnno);
		if ( ! is_array($data3['order_data']))
		{
			$data3['order_data'] = array();
		}
		$data3['edit_trnno'] = $trnno;

		header('Content-Type: text/html; charset=UTF-8');
		$this->load->view('order_edit', $data3);
	}
	
		public function order_table()
	{
	 	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
		$this->load->model('Order_predefined_model');
		 $data["suit"]= $this->master_data->get_dat(array('suit'));
		$data['order_predefined_by_row'] = $this->Order_predefined_model->get_grouped_active();
	// $data["measurement"]= $this->order_query->measurement();
	// $data["customer_name"]= $this->order_query->get_customer($this->uri->segment(4));
		header('Content-Type: text/html; charset=UTF-8');
 	$this->load->view('order_table',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	public function order_issue()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
		$ready_filters = $this->_ready_deliver_filters_from_request();
		if ($this->order_query->has_order_status_detail_filters($ready_filters))
		{
			$this->session->set_userdata('ready_deliver_filters', $ready_filters);
		}
		$data['ready_filters'] = $ready_filters;
		if ($this->uri->segment(3) === 'D')
		{
			$this->session->unset_userdata('ready_deliver_filters');
			$data['order_det'] = array();
		}
		else
		{
			$data['order_det'] = $this->order_query->get_order_ready_deliver($ready_filters);
		}
		if (empty($data['order_det']))
		{
			$data['order_det'] = array();
		}
		$this->load->view('order_issue', $data);
	}

	public function ready_return_stitching($transfer_id = 0)
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}

		$transfer_id = (int) $transfer_id;
		if ($transfer_id < 1)
		{
			$transfer_id = (int) $this->uri->segment(3);
		}
		$ok = $this->order_query->return_ready_line_to_stitching($transfer_id);

		if ($ok)
		{
			$this->session->set_flashdata('ready_return_msg', t('order_return_stitching_success'));
		}
		else
		{
			$this->session->set_flashdata('ready_return_msg', t('order_return_stitching_failed'));
		}

		$ready_filters = $this->_ready_deliver_filters_from_request();
		if ($this->order_query->has_order_status_detail_filters($ready_filters))
		{
			$this->session->set_userdata('ready_deliver_filters', $ready_filters);
		}
		else
		{
			$saved_filters = $this->session->userdata('ready_deliver_filters');
			if (is_array($saved_filters))
			{
				$ready_filters = $saved_filters;
			}
		}

		$redirect_url = site_url('Order/order_issue');
		$redirect_params = $this->_ready_deliver_filter_query_params($ready_filters);
		if ( ! empty($redirect_params))
		{
			$redirect_url .= '?'.http_build_query($redirect_params);
		}
		redirect($redirect_url);
	}

	public function ready_return_order_stitching($order_id = 0)
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}

		$order_id = (int) $order_id;
		if ($order_id < 1)
		{
			$order_id = (int) $this->uri->segment(3);
		}
		$updated = $this->order_query->return_ready_order_to_stitching($order_id);

		if ($updated > 0)
		{
			$this->session->set_flashdata('ready_return_msg', t('order_return_stitching_success'));
		}
		else
		{
			$this->session->set_flashdata('ready_return_msg', t('order_return_stitching_failed'));
		}

		$ready_filters = $this->_ready_deliver_filters_from_request();
		if ($this->order_query->has_order_status_detail_filters($ready_filters))
		{
			$this->session->set_userdata('ready_deliver_filters', $ready_filters);
		}
		else
		{
			$saved_filters = $this->session->userdata('ready_deliver_filters');
			if (is_array($saved_filters))
			{
				$ready_filters = $saved_filters;
			}
		}

		$redirect_url = site_url('Order/order_issue');
		$redirect_params = $this->_ready_deliver_filter_query_params($ready_filters);
		if ( ! empty($redirect_params))
		{
			$redirect_url .= '?'.http_build_query($redirect_params);
		}
		redirect($redirect_url);
	}

	protected function _ready_deliver_filters_from_request()
	{
		$ready_filters = array(
			'customer_name' => trim((string) $this->input->get('customer_name', TRUE)),
			'order_no' => trim((string) $this->input->get('order_no', TRUE)),
			'naap_no' => trim((string) $this->input->get('naap_no', TRUE)),
			'mobile_no' => trim((string) $this->input->get('mobile_no', TRUE)),
			'return_from' => trim((string) $this->input->get('return_from', TRUE)),
			'return_to' => trim((string) $this->input->get('return_to', TRUE)),
			'order_from' => trim((string) $this->input->get('order_from', TRUE)),
			'order_to' => trim((string) $this->input->get('order_to', TRUE)),
			'stage' => '',
		);
		if ($ready_filters['return_from'] === '' && $this->input->get('fromdate', TRUE) !== NULL)
		{
			$ready_filters['return_from'] = trim((string) $this->input->get('fromdate', TRUE));
		}
		if ($ready_filters['return_to'] === '' && $this->input->get('todate', TRUE) !== NULL)
		{
			$ready_filters['return_to'] = trim((string) $this->input->get('todate', TRUE));
		}
		return $ready_filters;
	}

	protected function _ready_deliver_filter_query_params($filters)
	{
		$params = array();
		$filter_keys = array('customer_name', 'order_no', 'naap_no', 'mobile_no', 'return_from', 'return_to', 'order_from', 'order_to');
		if ( ! is_array($filters))
		{
			return $params;
		}
		foreach ($filter_keys as $key)
		{
			if (isset($filters[$key]) && trim((string) $filters[$key]) !== '')
			{
				$params[$key] = trim((string) $filters[$key]);
			}
		}
		return $params;
	}

	 	public function order_deliverd()
	{
	 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	// $data["measurement"]= $this->order_query->measurement();
			$where = $this->uri->segment(3);
	 	//$last = $this->order_query->measurement($where);;
	//$where = 'measurement.code  = '.$this->uri->segment(4);

				 	$data["order_single_data"]= $this->order_query->get_order_det_single($where);
				 	$data["Measurement_data"]= $this->order_query->get_order_det_single($where);
				 	$data["TOTRECED"]= $this->order_query->get_recevied($where);
				 	$data["TOTCASHRECEVIED"]= $this->order_query->get_recevied_cash($where);
					  
				 	
			 
				 	 $data["style"]= $this->master_data->get_dat(array('style'));
				 	 $data["embroidery"]= $this->master_data->get_dat(array('embroidery'));
				 	 $data["suit"]= $this->master_data->get_dat(array('suit'));
				 	 $data["employee"]= $this->master_data->get_dat(array('employee'));
				 	 
				     $data["embroidery"]= $this->master_data->get_dat(array('embroidery'));
				 // 	 $data["employee"]= $this->master_data->get_dat(array('employee'));
					
 	$this->load->view('order_deliverd',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	
		public function order_cal()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	  $data["style"]= $this->master_data->get_dat(array('style'));
	  $data["embroidery"]= $this->master_data->get_dat(array('embroidery'));
	  $data["suit"]= $this->master_data->get_dat(array('suit'));
	 	
	// $data["measurement"]= $this->order_query->measurement();
	// $data["customer_name"]= $this->order_query->get_customer($this->uri->segment(4));
	$this->load->view('order_rate',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	
	
	
		public function order_cam()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	//   $data["style"]= $this->master_data->get_dat(array('style'));
	 	
	// $data["measurement"]= $this->order_query->measurement();
	// $data["customer_name"]= $this->order_query->get_customer($this->uri->segment(4));
 	$this->load->view('order_cam');
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	    public function saveImage()
	{
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			show_error('Unauthorized', 401);
			return;
		}

		$image = trim((string) $this->input->post('image'));
		if ($image === '')
		{
			show_error('No image data supplied.', 400);
			return;
		}

		$ext = 'jpg';
		if (preg_match('/^data:image\/(\w+);base64,/', $image, $matches))
		{
			$mimeExt = strtolower($matches[1]);
			$ext = ($mimeExt === 'jpeg') ? 'jpg' : $mimeExt;
			$image = substr($image, strpos($image, ',') + 1);
		}

		$binary = base64_decode($image, TRUE);
		if ($binary === FALSE || strlen($binary) > 5242880)
		{
			show_error('Invalid image upload.', 400);
			return;
		}

		if (@getimagesizefromstring($binary) === FALSE)
		{
			show_error('Invalid image upload.', 400);
			return;
		}

		$dir = FCPATH.'uploads/captures/';
		if ( ! is_dir($dir))
		{
			mkdir($dir, 0755, TRUE);
		}

		$filename = 'capture_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$ext;
		if (file_put_contents($dir.$filename, $binary) === FALSE)
		{
			show_error('Could not save image.', 500);
			return;
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(array('ok' => TRUE, 'file' => $filename)));
	}
				public function order_add2()
	{
		
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	   $data["style"]= $this->master_data->get_dat(array('style'));
	  
	 
	 	
	// $data["measurement"]= $this->order_query->measurement();
	  $data["customer_name"]= $this->order_query->get_customer($this->uri->segment(4));
	  $where = 'customer.code  = '.$this->uri->segment(3);
	 	//$last = $this->order_query->measurement($where);;
	//$where = 'measurement.code  = '.$this->uri->segment(4);

				 	$data["Measurement_data"]= $this->order_query->measurement($where);
 	
	 $data["last_id"] = $this->master_data->get_max_id('trnno','order_mst');
 	$this->load->view('Order1',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
 
	
			public function order_add1()
	{
		
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if (!$this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}
	   $data["style"]= $this->master_data->get_dat(array('style'));

		$customer_code = (int) $this->uri->segment(3);
		$measurement_code = (int) $this->uri->segment(4);
		if ($customer_code < 1 && $measurement_code < 1)
		{
			redirect('Customer');
			return;
		}

		$meas_rows = $this->customer_query->get_measurements_for_order($customer_code, $measurement_code);
		if (count($meas_rows) < 1)
		{
			if ($customer_code > 0)
			{
				$this->session->set_flashdata('customer_notice', 'Add measurement (Naap) before creating an order.');
				redirect('Measurement/measurement_add/'.$customer_code.'?return=order');
				return;
			}
			redirect('Customer');
			return;
		}
		if ($customer_code < 1)
		{
			$customer_code = (int) $meas_rows[0]->Customerno;
		}

		$data['measurement_row'] = $meas_rows[0];
		$data['Measurement_data'] = $meas_rows;
		$data['customer_name'] = $this->order_query->get_customer($customer_code > 0 ? $customer_code : (int) $meas_rows[0]->Customerno);
 	
	 $data["last_id"] = $this->master_data->get_max_id('trnno','order_mst');
	 $data['staff_employees'] = $this->employee_query->get_staff_employees();
	 $data['order_submit_token'] = $this->order_submit_token();
 	$this->load->view('Order',$data);
	}
 
	
		public function order_view()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	$this->load->library('form_validation');
	   $data["style"]= $this->master_data->get_dat(array('style'));
	 	
	// $data["measurement"]= $this->order_query->measurement();
	  $data["customer_name"]= $this->order_query->get_customer($this->uri->segment(3));
		$last = $this->uri->segment(3);
		if($last == '')
		{
	 	$last = $this->master_data->get_max_id('code','Measurement');
		}
			$where = 'measurement.code  = '.$last;

					$data["Measurement_data"]= $this->order_query->measurement($where);
	 
	 $data["last_id"] = $this->master_data->get_max_id('trnno','order_mst');
	 $data['staff_employees'] = $this->employee_query->get_staff_employees();
	 $data['order_submit_token'] = $this->order_submit_token();
 	$this->load->view('Order',$data);
	if(!$this->session->userdata('user')){
		redirect('Login'); 
	}	

		
	}
	 
	 
		public function order_view_det()
	{
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
		}
	   $detail_filters = array(
		   'customer_name' => trim((string) ($this->input->post('customer_name', TRUE) !== NULL ? $this->input->post('customer_name', TRUE) : $this->input->get('customer_name', TRUE))),
		   'order_no' => trim((string) ($this->input->post('order_no', TRUE) !== NULL ? $this->input->post('order_no', TRUE) : $this->input->get('order_no', TRUE))),
		   'naap_no' => trim((string) ($this->input->post('naap_no', TRUE) !== NULL ? $this->input->post('naap_no', TRUE) : $this->input->get('naap_no', TRUE))),
		   'mobile_no' => trim((string) ($this->input->post('mobile_no', TRUE) !== NULL ? $this->input->post('mobile_no', TRUE) : $this->input->get('mobile_no', TRUE))),
		   'return_from' => trim((string) ($this->input->post('return_from', TRUE) !== NULL ? $this->input->post('return_from', TRUE) : $this->input->get('return_from', TRUE))),
		   'return_to' => trim((string) ($this->input->post('return_to', TRUE) !== NULL ? $this->input->post('return_to', TRUE) : $this->input->get('return_to', TRUE))),
		   'order_from' => trim((string) ($this->input->post('order_from', TRUE) !== NULL ? $this->input->post('order_from', TRUE) : $this->input->get('order_from', TRUE))),
		   'order_to' => trim((string) ($this->input->post('order_to', TRUE) !== NULL ? $this->input->post('order_to', TRUE) : $this->input->get('order_to', TRUE))),
		   'row_limit' => trim((string) ($this->input->post('row_limit', TRUE) !== NULL ? $this->input->post('row_limit', TRUE) : $this->input->get('row_limit', TRUE))),
	   );
	   if ($detail_filters['row_limit'] === '')
	   {
		   $detail_filters['row_limit'] = '500';
	   }
	   if ($detail_filters['order_from'] === '' && $this->input->post('fromdate', TRUE) !== NULL)
	   {
		   $detail_filters['order_from'] = trim((string) $this->input->post('fromdate', TRUE));
	   }
	   if ($detail_filters['order_to'] === '' && $this->input->post('todate', TRUE) !== NULL)
	   {
		   $detail_filters['order_to'] = trim((string) $this->input->post('todate', TRUE));
	   }
	   $cust_search = $this->input->post('cust_search', TRUE);
	   if ($cust_search === NULL)
	   {
		   $cust_search = $this->input->get('cust_search', TRUE);
	   }
	   $cust_search = $cust_search !== NULL ? trim((string) $cust_search) : '';
	   if ($detail_filters['customer_name'] === '' && $cust_search !== '')
	   {
		   $detail_filters['customer_name'] = $cust_search;
	   }
	   $detail_filters = $this->order_query->apply_default_order_date_filters($detail_filters);
	   $data['detail_filters'] = $detail_filters;
	   $data['cust_search'] = $cust_search;
	   $data['fromdate'] = $detail_filters['order_from'];
	   $data['todate'] = $detail_filters['order_to'];
	    $data['orderdata'] = $this->order_query->get_order_order_Detail($detail_filters);
		if (empty($data['orderdata']))
		{
			$data['orderdata'] = array();
		}
 	$this->load->view('Order_view_report',$data);
	}

	public function customer_order_history()
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			show_error('Unauthorized', 403);
		}
		$customer_code = (int) $this->input->post('customer_code', TRUE);
		$data['orderdata'] = $this->order_query->get_customer_order_history($customer_code);
		header('Content-Type: text/html; charset=UTF-8');
		$this->load->view('partials/customer_order_history_table', $data);
	}

	public function order_lifecycle_history($order_id = 0)
	{
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url');
		$this->load->library('session');
		if ( ! $this->session->userdata('user'))
		{
			show_error('Unauthorized', 403);
		}
		$order_id = (int) $order_id;
		if ($order_id < 1)
		{
			$order_id = (int) $this->input->post('order_id', TRUE);
		}
		if ($order_id < 1)
		{
			$order_id = (int) $this->uri->segment(3);
		}
		$data['history'] = $this->order_query->get_order_lifecycle_history($order_id);
		header('Content-Type: text/html; charset=UTF-8');
		$this->load->view('partials/order_lifecycle_history', $data);
	}
	 
 public function order_line_delete()  
		{ 
		 
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
        
	 	$this->load->model("insert");
		//$data = array(
			//	'qty' => $this->input->post('Quantity') - 1,
				//'total' => $this->input->post('total_amount') - $this->input->post('itotal1'),
				 
				 
	 
  
//);
		//	$this->db->query("delete  from order_dtl where trnno='".$this->uri->segment(4)."'");
			$this->db->query("delete  from transfer_mst where sr='".$this->uri->segment(4)."' and order_id='".$this->uri->segment(3)."'");
			// 	$where = "trnno = ".$this->uri->segment(3)."";//.' and sr = '.$ordersr;
					//$where = 'trnno = 191';
			 //		$this->db->where($where);
			 	//	$this->db->update('order_mst', $data);
					redirect('Order/Order_edit/'.$this->uri->segment(3).'');
				 
		}	
		
public function process()  
		{ 
		
	 
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
	 	$this->load->model("insert");
		if (!$this->is_valid_order_submit_token())
		{
			$this->session->set_flashdata('suss', 'duplicate');
			redirect('Order/Order_Print');
			return;
		}

		$desigtype = $this->input->post('desigtype');
		if ( ! is_array($desigtype) || count($desigtype) < 1)
		{
			$this->session->set_flashdata('suss', 'incomplete');
			$naap = (int) $this->input->post('Naapno');
			redirect($naap > 0 ? 'Order/order_view/'.$naap : 'Welcome');
			return;
		}

		$line_count = count($desigtype);
		for ($i = 0; $i < $line_count; $i++)
		{
			$line_type = trim((string) $this->input->post('desigtype['.$i.']'));
			$line_suit = (int) $this->input->post('suit['.$i.']');
			if ($line_type === '' || $line_suit <= 0)
			{
				$this->session->set_flashdata('suss', 'incomplete');
				$naap = (int) $this->input->post('Naapno');
				redirect($naap > 0 ? 'Order/order_view/'.$naap : 'Welcome');
				return;
			}
		}

		$total_amount = (float) $this->input->post('total_amount');
		$discount = (float) $this->input->post('Discount');
		$advance = (float) $this->input->post('Advance');

		$naap_code = (int) $this->input->post('Naapno');
		$meas_rows = $this->order_query->measurement('measurement.code = '.$naap_code);
		if (empty($meas_rows))
		{
			$this->session->set_flashdata('suss', 'incomplete');
			redirect($naap_code > 0 ? 'Order/order_view/'.$naap_code : 'Welcome');
			return;
		}
		$meas_row = $meas_rows[0];

		$staff_empno = (int) $this->input->post('staff_employee', TRUE);
		if ($staff_empno <= 0 || ! $this->employee_query->is_staff_employee($staff_empno))
		{
			$this->session->set_flashdata('suss', 'incomplete');
			redirect($naap_code > 0 ? 'Order/order_view/'.$naap_code : 'Welcome');
			return;
		}

		$firstDate = date("Y-m-d", strtotime($this->input->post('retrun_Date')));
		 	$data1 = array(
				'EDATE' => date('Y-m-d'),
				'naap_no' => $naap_code,
				'snap_man_napno' => isset($meas_row->MANNAAPNO) ? $meas_row->MANNAAPNO : '',
				'snap_customer_name' => $this->order_query->measurement_customer_name_from_row($meas_row),
				'snap_customer_code' => isset($meas_row->Customerno) ? (int) $meas_row->Customerno : (isset($meas_row->customer_code) ? (int) $meas_row->customer_code : 0),
				'RDATE' => $firstDate,
				'staff_empno' => $staff_empno,
				'qty' => $this->input->post('Quantity'),
				'total' => $total_amount,
				'advance' => $advance,
				'Discount' => $discount,
				'addprice' => $this->input->post('addprice'),
				'image' => $this->input->post('iamge'),
		 
  
); 
 
		$this->db->trans_start();
 $res=$this->insert->insert_dat1($data1,'order_mst');
 $order_trnno = $this->db->insert_id();
		$this->order_query->insert_order_measurement_snap($order_trnno, $meas_row);
 
 
 	 	$jmst = array(
				'TRNDATE' => date('Y-m-d'),
				'ACCOUNT' => $this->input->post('customer_NO'),
				'ACCOUNT' => 'CUSTOMER',
				'REMARKS' => $this->input->post('retrun_Date'),
				 
		 
  
); 
 
 	 $res=$this->insert->insert_dat1($jmst,'jmst');	
	$jmst_trnno = $this->db->insert_id();
	$jdtl ['SR'] = $jmst_trnno;
	$jdtl ['NARRATION'] = 'Advance Recevied from Customer agasint Order #'.$order_trnno;
	$jdtl ['DR'] = $advance;
	$jdtl ['CR'] = 0;
	$res=$this->insert->insert_dat1($jdtl,'jdtl');
	$jdtl ['SR'] = $jmst_trnno;
	$jdtl ['NARRATION'] = 'Credit Customer agasint Order';
	$jdtl ['DR'] = 0;
	$jdtl ['CR'] = $total_amount - $discount;
	$res=$this->insert->insert_dat1($jdtl,'jdtl');
  
  

	
for($i=0; $i<$line_count; $i++)
{
	$data ['order_trnno'] = $order_trnno;
  $data ['design'] = 0;
  $data ['type'] = $this->input->post("desigtype[".$i."]");
  $data ['kolor_style'] = $this->input->post("kolorstyle".$i."");
  $data ['ENO'] = 0;
  $line_sno = (int) $this->input->post("suit[".$i."]");
  $data ['SNO'] = $line_sno;
  $data ['snap_suit_name'] = $this->order_query->get_suit_name_by_sno($line_sno);
  $data ['suitrate'] = $this->input->post("ssrate[".$i."]");
  $data ['drate'] = 0;
  $data ['erate'] = 0;
  $data ['addprice'] = $this->input->post("add[".$i."]");
  $data ['spocket'] = $this->input->post("spocket[".$i."]");
  $data ['fpocket'] = $this->input->post("fpocket[".$i."]");
  $data ['Shawlarpocket'] = $this->input->post("shawpocket[".$i."]");
  $data ['order_remarks'] = $this->input->post("Remarks[".$i."]");
  
 
  $res=$this->insert->insert_dat1($data,'order_dtl');
  $order_detail_sr = $this->db->insert_id();
	

  
  
  
 $data2 ['EDATE'] = date('Y-m-d');
  $data2 ['order_id'] = $order_trnno;
  $data2 ['sr'] = $order_detail_sr;
   
  $data2 ['qty'] = 1;
  $data2 ['dept'] = 'HO';
  $data2 ['status'] = 'Active';
  
  $res=$this->insert->insert_dat1($data2,'transfer_mst');
  $this->dashboard_data->clear_dashboard_summary_cache();
}

		$this->db->trans_complete();
		if ( ! $this->db->trans_status())
		{
			$this->session->set_flashdata('suss', 'incomplete');
			$naap = (int) $this->input->post('Naapno');
			redirect($naap > 0 ? 'Order/order_view/'.$naap : 'Welcome');
			return;
		}

		if ($advance != 0)
		{
			$this->cash_ledger->invalidate_cash_ledger_opening_cache($data1['EDATE']);
		}
		
			// echo $where5;
 
			redirect('Order/Order_Print',$data3); 
			//	print_r($data);
			//	die;
				
				
				if($res==true)
				{	
					$data['suss'] = 'yes';
				 	
				}
				else
				{
				 //	$this->session->set_flashdata('suss', 'no');
				}
		}
				
public function process_edit()  
		{ 
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
	 	$this->load->model("insert");
		$last = (int) $this->uri->segment(3);
		if ($last <= 0)
		{
			redirect('Order/Order_Print');
			return;
		}

		$sr_nos = $this->input->post('sr_no');
		if ( ! is_array($sr_nos) || count($sr_nos) < 1)
		{
			$this->session->set_flashdata('suss', 'incomplete');
			redirect('Order/Order_edit/'.$last);
			return;
		}

		$line_count = count($sr_nos);
		$ssrates = $this->input->post('ssrate');
		$adds = $this->input->post('add');
		$suits = $this->input->post('suit');
		$desigtypes = $this->input->post('desigtype');
		$spockets = $this->input->post('spocket');
		$fpockets = $this->input->post('fpocket');
		$shawpockets = $this->input->post('shawpocket');
		$remarks = $this->input->post('Remarks');
		if ( ! is_array($ssrates)) { $ssrates = array(); }
		if ( ! is_array($adds)) { $adds = array(); }
		if ( ! is_array($suits)) { $suits = array(); }
		if ( ! is_array($desigtypes)) { $desigtypes = array(); }
		if ( ! is_array($spockets)) { $spockets = array(); }
		if ( ! is_array($fpockets)) { $fpockets = array(); }
		if ( ! is_array($shawpockets)) { $shawpockets = array(); }
		if ( ! is_array($remarks)) { $remarks = array(); }

		$order_line_total = 0;
		for ($i = 0; $i < $line_count; $i++)
		{
			$srate = isset($ssrates[$i]) ? (float) str_replace(',', '', (string) $ssrates[$i]) : 0;
			$add = isset($adds[$i]) ? (float) str_replace(',', '', (string) $adds[$i]) : 0;
			$order_line_total += $srate + $add;
		}
		$posted_total = (float) str_replace(',', '', (string) $this->input->post('total_amount'));
		$order_total = $posted_total > 0 ? $posted_total : $order_line_total;

		$firstDate = date("Y-m-d", strtotime($this->input->post('retrun_Date')));
		$current_date = date("Y-m-d", strtotime($this->input->post('current')));
		 	$data1 = array(
				'EDATE' => $current_date,
				//'naap_no' => $this->input->post('Naapno'),
				'RDATE' => $firstDate,
				'qty' => $this->input->post('Quantity'),
				'total' => $order_total,
				'advance' => (float) str_replace(',', '', (string) $this->input->post('Advance')),
				'Discount' => (float) str_replace(',', '', (string) $this->input->post('Discount')),
				'addprice' => $this->input->post('addprice'),
		 
  
); 
 
 //$res=$this->insert->insert_dat1($data1,'order_mst');
			
			$this->db->where('trnno', $last);

$this->db->update('order_mst', $data1);
		if ((float) $data1['advance'] != 0)
		{
			$this->cash_ledger->invalidate_cash_ledger_opening_cache($current_date);
		}
 
			//	$res=$this->insert->entry_update1('trnno',$last,$data1,'order_mst');
 
 	 	$jmst = array(
				'TRNDATE' => date('Y-m-d'),
				'ACCOUNT' => $this->input->post('customer_NO'),
				'ACCOUNT' => 'CUSTOMER',
				'REMARKS' => $this->input->post('retrun_Date'),
				 
		 
  
); 
 
 /*	 $res=$this->insert->insert_dat1($jmst,'jmst');	
	$jdtl ['SR'] = $this->master_data->get_max_id('TRNNO','jmst');
	$jdtl ['NARRATION'] = 'Advance Recevied from Customer agasint Order #'.$this->master_data->get_max_id('trnno','order_mst');
	$jdtl ['DR'] = $this->input->post('Advance');
	$jdtl ['CR'] = 0;
	$res=$this->insert->insert_dat1($jdtl,'jdtl');
	$jdtl ['SR'] = $this->master_data->get_max_id('TRNNO','jmst');
	8$jdtl ['NARRATION'] = 'Credit Customer agasint Order';
	$jdtl ['DR'] = 0;
	$jdtl ['CR'] = $this->input->post('total_amount') - $this->input->post('Discount');
	$res=$this->insert->insert_dat1($jdtl,'jdtl');
	
  */
  

	//$res=$this->insert->row_delete('trnno',$last,'order_mst');
	// $res=$this->insert->row_delete('order_trnno',$last,'order_dtl');
	// $this->db->query("delete  from order_dtl where order_trnno='".$last."'");
	// $this->db->query("delete  from transfer_mst where order_id='".$last."'");
 
	//$this->db->delete('order_dtl', array('order_trnno' => $last)); 
	//$res=$this->insert->row_delete('order_id',$last,'transfer_mst');
	
for($i=0; $i<$line_count; $i++)
{
	$line_sno = isset($suits[$i]) ? (int) $suits[$i] : 0;
	$line_srate = isset($ssrates[$i]) ? (float) str_replace(',', '', (string) $ssrates[$i]) : 0;
	$data12 = array(
	 'order_trnno' => $last,
	 
    'design'  => 0,
	'type' => isset($desigtypes[$i]) ? $desigtypes[$i] : '',
	'kolor_style' => $this->input->post("kolorstyle".$i.""),
	'ENO' => 0,
	'SNO' => $line_sno,
    'suitrate' => $line_srate,
	'snap_suit_name' => $this->order_query->get_suit_name_by_sno($line_sno),
	'drate' => 0,
	'erate' => 0,
	'addprice' => isset($adds[$i]) ? (float) str_replace(',', '', (string) $adds[$i]) : 0,
	'spocket' => isset($spockets[$i]) ? $spockets[$i] : '',
    'fpocket' => isset($fpockets[$i]) ? $fpockets[$i] : '',
    'Shawlarpocket' => isset($shawpockets[$i]) ? $shawpockets[$i] : '',
	'order_remarks' => isset($remarks[$i]) ? $remarks[$i] : '',
  ); 
 
	$last1 = (int) $sr_nos[$i];
  //$res=$this->insert->insert_dat1($data,'order_dtl');

  			$this->db->where('trnno', $last1);
			$this->db->update('order_dtl', $data12);
				//echo $last;
	//die;
  
	//$res=$this->insert->entry_update1('trnno','31',$data12,'order_dtl');

// print_r( $data);
 //die;
  
  
  
  //$data2 ['EDATE'] = date('Y-m-d');
  //$data2 ['order_id'] = $this->master_data->get_max_id('trnno','order_mst');
  //$data2 ['sr'] = $this->master_data->get_max_id('trnno','order_dtl');
   
  //$data2 ['qty'] = 1;
  //$data2 ['dept'] = 'HO';
 // $data2 ['status'] = 'Active';
  
 // $res=$this->insert->insert_dat1($data2,'transfer_mst');
}
 
		$this->session->set_flashdata('order_edit_saved', $last);
		redirect('Order/Order_Print_det/'.$last);
		return;
		}		

		
 		
		public function process_del()  
		{ 
		 error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
		$this->load->helper('url'); 
		$this->load->library('session');
	//	 $this->form_validation->set_rules('Name', 'Text Field One', 'required');
	 	$this->load->model("insert");
		
		$bal = $this->input->post('total_Amount') - (  $this->input->post('Advance')
				+ $this->input->post('Discount') + $this->input->post('Balance') );
				 
		$firstDate = date("Y-m-d", strtotime($this->input->post('retrun_Date')));
			 	$data1 = array(
				'EDATE' => date('Y-m-d'),
				'order_id' => $this->uri->segment(3),
				'PAID' => $this->input->post('Balance'),
				'NPAID_dATE' => $this->input->post('paid_Date'),
				'customer_id' => $this->input->post('customer_code'),
			 'balance' => $bal,
				'pending' => $this->input->post('udaar'),
); 
			$narration  = 'Recevied Agaisnt Order '. $this->uri->segment(3);
			$res=$this->insert->insert_dat1($data1,'dmst');
			$data5 = array(
				'EDATE' => date('Y-m-d'),
				'type' => '2',
				'customer_id	' => $this->input->post('customer_code'),
				'amount' => $this->input->post('Balance'),
				'narration' => $narration,
); 	
			
			
			$res=$this->insert->insert_dat1($data5,'cash');
	
 
 $last = $this->master_data->get_max_id('trnno','dmst');
 $last_dmst = $this->master_data->get_max_id('trnno','dmst');
 for($i=0; $i<count($this->input->post("order_sr")); $i++)
{
	if($this->input->post("returndel[".$i."]") == 'yes')
	{
	$data ['DTRNNO'] = $last_dmst;
	$data ['ORDER_TRNNO'] = $this->uri->segment(3);
	$data ['ORDERSR'] = $this->input->post("order_sr[".$i."]");
	$data ['ord_dtl_date'] = date('Y-m-d');
   
  
 
  $res=$this->insert->insert_dat1($data,'ddtl');

   $data1 = array(
				'status' => 'DELIVER',
);				 

							$last = $this->uri->segment(3);
							$last2 = $this->input->post("order_sr[".$i."]");
				 	$res=$this->insert->entry_update3_transfer($last,$last2,$data1,'transfer_mst');
  	 

	}
  
}
redirect('Order/order_issue'); 
}
}
