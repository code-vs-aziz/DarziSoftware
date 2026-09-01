<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Language extends CI_Controller {

	public function set($lang = 'english')
	{
		$this->load->helper('app_lang');
		app_set_language($lang);

		$redirect = $this->input->get('redirect', TRUE);
		if ($redirect !== NULL && $redirect !== '')
		{
			$redirect = rawurldecode($redirect);
			if (strpos($redirect, '://') === FALSE && strpos($redirect, '//') !== 0)
			{
				redirect($redirect);
				return;
			}
		}

		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if ($referer !== '')
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			if ($host !== '' && stripos($referer, $host) !== FALSE)
			{
				redirect($referer);
				return;
			}
		}

		redirect('Welcome');
	}
}
