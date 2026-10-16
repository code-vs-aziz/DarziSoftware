<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sends website OTP via WhatsApp bridge; falls back to debug mode in development.
 */
class Website_otp_service
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function normalize_phone($phone)
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

	/**
	 * @return array{ok:bool, error?:string, debug_otp?:string}
	 */
	public function send_otp($phone, $otp, $customer_name = '')
	{
		$phone = $this->normalize_phone($phone);
		$name = trim((string) $customer_name);
		$greeting = $name !== '' ? $name : 'Customer';
		$message = "Darzi Baba\n\nAssalam-o-Alaikum {$greeting},\nYour verification code is: {$otp}\nValid for 10 minutes.\nDo not share this code.";

		$this->CI->load->library('Whatsapp_bridge');
		if ($this->CI->whatsapp_bridge->is_enabled())
		{
			$send = $this->CI->whatsapp_bridge->send($phone, $message);
			if ( ! empty($send['ok']))
			{
				return array('ok' => TRUE);
			}
			// Fall through to debug in development
			$bridge_error = isset($send['error']) ? $send['error'] : 'send_failed';
		}
		else
		{
			$bridge_error = 'bridge_disabled';
		}

		$env = function_exists('dz_env') ? (string) dz_env('CI_ENV', 'production') : 'production';
		$allow_debug = ($env === 'development') || (function_exists('dz_env_bool') && dz_env_bool('WEBSITE_OTP_DEBUG', FALSE));
		if ($allow_debug)
		{
			log_message('debug', 'Website OTP for '.$phone.': '.$otp.' ('.$bridge_error.')');
			return array('ok' => TRUE, 'debug_otp' => $otp, 'error' => $bridge_error);
		}

		return array('ok' => FALSE, 'error' => $bridge_error);
	}
}
