<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! defined('APP_LANGUAGE_COOKIE'))
{
	define('APP_LANGUAGE_COOKIE', 'app_language');
}

if ( ! function_exists('app_lang_config'))
{
	function app_lang_config($key = NULL, $default = NULL)
	{
		$CI =& get_instance();
		$CI->config->load('app_languages', TRUE);
		if ($key === NULL)
		{
			return $CI->config->item('app_languages', 'app_languages');
		}
		return $CI->config->item($key, 'app_languages') !== NULL
			? $CI->config->item($key, 'app_languages')
			: $default;
	}
}

if ( ! function_exists('app_language_allowed'))
{
	function app_language_allowed()
	{
		$codes = app_lang_config('app_language_codes');
		return is_array($codes) ? $codes : array('english', 'urdu', 'arabic');
	}
}

if ( ! function_exists('app_language_meta'))
{
	function app_language_meta($lang = NULL)
	{
		$lang = ($lang === NULL) ? app_language() : (string) $lang;
		$all = app_lang_config('app_languages');
		if (is_array($all) && isset($all[$lang]))
		{
			return $all[$lang];
		}
		return isset($all['english']) ? $all['english'] : array(
			'label' => 'EN',
			'native' => 'English',
			'html_lang' => 'en',
			'dir' => 'ltr',
			'css_class' => 'app-lang-en',
			'rtl' => FALSE,
		);
	}
}

if ( ! function_exists('app_html_lang'))
{
	function app_html_lang()
	{
		$meta = app_language_meta();
		return isset($meta['html_lang']) ? $meta['html_lang'] : 'en';
	}
}

if ( ! function_exists('app_lang_css_class'))
{
	function app_lang_css_class()
	{
		$meta = app_language_meta();
		$classes = array(isset($meta['css_class']) ? $meta['css_class'] : 'app-lang-en');
		if (app_is_rtl())
		{
			$classes[] = 'app-lang-rtl';
		}
		return implode(' ', $classes);
	}
}

if ( ! function_exists('app_language_cookie_path'))
{
	function app_language_cookie_path()
	{
		$CI =& get_instance();
		$path = $CI->config->item('cookie_path');
		return ($path !== NULL && $path !== '') ? $path : '/';
	}
}

if ( ! function_exists('app_resolve_language'))
{
	/**
	 * Read preferred UI language from session, then cookie, else default.
	 */
	function app_resolve_language()
	{
		$allowed = app_language_allowed();
		$default = app_lang_config('app_default_language', 'english');
		$CI =& get_instance();

		$lang = $CI->session->userdata(APP_LANGUAGE_COOKIE);
		if (in_array($lang, $allowed, TRUE))
		{
			return $lang;
		}

		$CI->load->helper('cookie');
		$lang = get_cookie(APP_LANGUAGE_COOKIE);
		if (in_array($lang, $allowed, TRUE))
		{
			$CI->session->set_userdata(APP_LANGUAGE_COOKIE, $lang);
			return $lang;
		}

		return $default;
	}
}

if ( ! function_exists('app_set_language'))
{
	/**
	 * Persist UI language in session and cookie (survives login redirect).
	 */
	function app_set_language($lang)
	{
		$allowed = app_language_allowed();
		if ( ! in_array($lang, $allowed, TRUE))
		{
			$lang = app_lang_config('app_default_language', 'english');
		}

		$CI =& get_instance();
		$CI->load->library('session');
		$CI->session->set_userdata(APP_LANGUAGE_COOKIE, $lang);

		$CI->load->helper('cookie');
		set_cookie(array(
			'name' => APP_LANGUAGE_COOKIE,
			'value' => $lang,
			'expire' => 60 * 60 * 24 * 365,
			'path' => app_language_cookie_path(),
			'secure' => (bool) $CI->config->item('cookie_secure'),
			'httponly' => TRUE,
		));

		$CI->config->set_item('language', $lang);

		return $lang;
	}
}

if ( ! function_exists('app_language'))
{
	function app_language()
	{
		$CI =& get_instance();
		$lang = $CI->config->item('language');
		return in_array($lang, app_language_allowed(), TRUE) ? $lang : app_lang_config('app_default_language', 'english');
	}
}

if ( ! function_exists('app_is_rtl'))
{
	function app_is_rtl()
	{
		$meta = app_language_meta();
		return ! empty($meta['rtl']);
	}
}

if ( ! function_exists('t_urdu'))
{
	/**
	 * Urdu string by key (for messages that should always show in Urdu).
	 */
	function t_urdu($key, $sprintf_args = array())
	{
		static $urdu_lines = NULL;
		if ($urdu_lines === NULL)
		{
			$urdu_lines = array();
			$files = app_lang_config('app_lang_files');
			if ( ! is_array($files))
			{
				$files = array();
			}
			foreach ($files as $file)
			{
				$path = APPPATH.'language/urdu/'.$file.'_lang.php';
				if ( ! is_readable($path))
				{
					continue;
				}
				$lang = array();
				include $path;
				if (isset($lang) && is_array($lang))
				{
					$urdu_lines = array_merge($urdu_lines, $lang);
				}
			}
		}

		$line = isset($urdu_lines[$key]) ? $urdu_lines[$key] : $key;
		if ( ! empty($sprintf_args) && is_array($sprintf_args))
		{
			$line = vsprintf($line, $sprintf_args);
		}

		return $line;
	}
}

if ( ! function_exists('t'))
{
	/**
	 * Translate a language key (loads from merged CI lang files).
	 */
	function t($key, $sprintf_args = array())
	{
		$CI =& get_instance();
		$line = $CI->lang->line($key);

		if ($line === FALSE || $line === '')
		{
			if (ENVIRONMENT === 'development')
			{
				log_message('debug', 'Missing language key: '.$key);
			}
			$line = $key;
		}

		if ( ! empty($sprintf_args) && is_array($sprintf_args))
		{
			$line = vsprintf($line, $sprintf_args);
		}

		return $line;
	}
}

if ( ! function_exists('app_lang_switcher_urls'))
{
	function app_lang_switcher_urls()
	{
		$urls = array();
		foreach (app_language_allowed() as $code)
		{
			$urls[$code] = site_url('Language/set/'.$code);
		}
		return $urls;
	}
}

if ( ! function_exists('app_lang_js_strings'))
{
	function app_lang_js_strings()
	{
		$keys = array(
			'customer_not_found',
			'confirm_submit_order',
			'loading',
			'could_not_load_history',
			'order_history_load_error',
			'whatsapp_failed',
			'saved_success',
			'enter_qty',
			'select_design',
			'select_suit_type',
			'phone_missing',
		);

		$out = array();
		foreach ($keys as $key)
		{
			$out[$key] = t('js_'.$key);
		}

		$out['app_confirm'] = t('app_confirm');
		$out['app_cancel'] = t('app_cancel');

		return $out;
	}
}
