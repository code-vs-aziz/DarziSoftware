<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_language_hook
{
	public function bootstrap()
	{
		$CI =& get_instance();
		$CI->load->helper('app_lang');
		$CI->config->load('app_languages', TRUE);

		$lang = app_resolve_language();
		$CI->config->set_item('language', $lang);

		$files = $CI->config->item('app_lang_files', 'app_languages');
		if ( ! is_array($files))
		{
			$files = array();
		}

		foreach ($files as $file)
		{
			$CI->lang->load($file, $lang);
		}
	}
}
