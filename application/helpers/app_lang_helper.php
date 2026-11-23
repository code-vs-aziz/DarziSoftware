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
			'order_no_image_confirm',
			'order_camera_unavailable',
			'order_camera_retry',
			'order_camera_starting',
			'order_save_without_photo',
			'loading',
			'could_not_load_history',
			'order_history_load_error',
			'whatsapp_failed',
			'saved_success',
			'enter_qty',
			'booking_limit_reached',
			'booking_limit_generic',
			'select_return_date_first',
			'select_design',
			'select_suit_type',
			'phone_missing',
			'wa_enter_phone',
			'wa_pdf_sent',
			'wa_send_pdf',
			'notify_print_sent',
			'notify_print_failed',
			'notify_bridge_disabled',
			'notify_bridge_not_ready',
			'bridge_chip_ok',
			'bridge_chip_wait',
			'bridge_chip_off',
			'bridge_chip_checking',
			'bridge_banner_down',
			'bridge_banner_scan',
			'bridge_banner_down_staff',
			'bridge_banner_scan_staff',
			'report_voice_processing',
			'report_voice_listening',
			'report_voice_ready',
			'report_voice_fail',
			'report_voice_unrecognized',
			'report_voice_permission',
			'report_voice_no_mic',
			'report_voice_mic_error',
			'report_voice_ollama_on',
			'offline_chip_online',
			'offline_chip_offline',
			'offline_chip_pending',
			'offline_chip_syncing',
			'offline_chip_synced',
			'offline_chip_failed',
			'offline_chip_checking',
			'offline_sync_now',
			'offline_banner_offline',
			'offline_banner_online',
			'offline_banner_pending',
			'offline_banner_failed',
			'offline_saved_pending',
			'offline_online_only',
			'offline_whatsapp_online_only',
			'offline_report_cached_only',
			'offline_form_unsupported',
			'offline_save_failed',
			'offline_sync_complete',
			'offline_sync_failed',
			'offline_sync_orders',
			'offline_sync_customers',
			'offline_sync_cash',
			'offline_sync_deliveries',
			'offline_sync_transfers',
			'offline_sync_conflicts',
			'offline_last_synced',
			'offline_sync_status',
			'offline_pending_count',
			'offline_ready_yes',
			'offline_ready_stale',
			'offline_ready_no',
			'offline_chip_ready',
			'offline_chip_stale',
			'offline_chip_no_data',
			'offline_chip_caching',
			'offline_cached_customers',
			'offline_cached_measurements',
			'offline_cached_orders',
			'offline_pages_cached',
			'offline_refresh_data',
			'offline_never_synced',
			'offline_age_just_now',
			'offline_age_min',
			'offline_age_hr',
			'offline_prepare_working',
			'offline_prepare_ready',
			'offline_prepare_failed',
			'offline_prepare_missing',
		);

		$out = array();
		foreach ($keys as $key)
		{
			// Prefer dedicated offline_* / js_* keys; fall back gracefully.
			if (strpos($key, 'offline_') === 0)
			{
				$out[$key] = t($key);
			}
			else
			{
				$out[$key] = t('js_'.$key);
			}
		}

		$out['app_confirm'] = t('app_confirm');
		$out['app_cancel'] = t('app_cancel');

		$order_form_keys = array(
			'order_suit_progress',
			'order_suit_status_active',
			'order_suit_status_done',
			'order_suit_status_locked',
			'order_suit_status_pending',
			'order_suit_complete_first',
			'order_copy_last_none',
			'order_copy_last_fail',
			'order_same_remaining_done',
			'report_err_select_report',
			'report_status_opening',
			'report_status_opened',
			'report_status_pdf',
			'report_status_pdf_done',
			'report_status_whatsapp',
			'report_status_whatsapp_ok',
			'report_status_whatsapp_link',
			'report_prompt_phone',
			'report_err_missing_dates',
			'report_err_invalid_dates',
			'report_err_from_after_to',
			'report_err_range_too_large',
			'report_err_permission',
			'report_err_pdf_unsupported',
			'report_err_missing_phone',
			'report_err_bridge',
			'report_err_session',
			'report_err_network',
			'report_err_generic',
		);
		foreach ($order_form_keys as $key)
		{
			$out[$key] = t($key);
		}

		return $out;
	}
}

if ( ! function_exists('transfer_stage_label'))
{
	/**
	 * Human label for transfer department code (HO, CUT, HO2, STIT).
	 */
	function transfer_stage_label($code)
	{
		$code = strtoupper(trim((string) $code));
		$map = array(
			'HO' => 'transfer_stage_ho',
			'CUT' => 'transfer_stage_cut',
			'HO2' => 'transfer_stage_ho2',
			'STIT' => 'transfer_stage_stit',
		);
		return isset($map[$code]) ? t($map[$code]) : $code;
	}
}

if ( ! function_exists('transfer_next_stage'))
{
	/**
	 * Next department after a transfer from $from_code, or empty when stitching completes.
	 */
	function transfer_next_stage($from_code)
	{
		$from_code = strtoupper(trim((string) $from_code));
		$map = array(
			'HO' => 'CUT',
			'CUT' => 'HO2',
			'HO2' => 'STIT',
			'STIT' => '',
		);
		return isset($map[$from_code]) ? $map[$from_code] : '';
	}
}

if ( ! function_exists('transfer_flow_title'))
{
	/**
	 * Title like "Booking → Cutting" or "Stitching → Ready".
	 */
	function transfer_flow_title($from_code)
	{
		$from_code = strtoupper(trim((string) $from_code));
		$from_label = transfer_stage_label($from_code);
		$next = transfer_next_stage($from_code);
		if ($next === '')
		{
			return sprintf(t('transfer_flow_complete'), $from_label);
		}
		return sprintf(t('transfer_flow_title'), $from_label, transfer_stage_label($next));
	}
}

if ( ! function_exists('transfer_pending_title'))
{
	/**
	 * List-page title: "Pending Cutting → HO2".
	 */
	function transfer_pending_title($from_code)
	{
		$from_code = strtoupper(trim((string) $from_code));
		$from_label = transfer_stage_label($from_code);
		$next = transfer_next_stage($from_code);
		if ($next === '')
		{
			return sprintf(t('transfer_pending_complete'), $from_label);
		}
		return sprintf(t('transfer_pending_flow'), $from_label, transfer_stage_label($next));
	}
}
