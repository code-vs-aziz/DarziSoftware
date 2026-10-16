<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer portal OTP + session tokens for the public website API.
 */
class Website_auth_service
{
	protected $CI;
	protected $session_ttl = 86400; // 24h
	protected $otp_ttl = 600; // 10 min

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Website_model', 'website_model');
		$this->CI->load->library('Website_otp_service', NULL, 'website_otp');
	}

	/**
	 * Start OTP using shop ناپ number OR registered phone number.
	 *
	 * @return array{ok:bool, challenge_id?:int, phone_masked?:string, customer_name?:string, man_napno?:string, error?:string, debug_otp?:string}
	 */
	public function start_otp($identifier, $ip = '')
	{
		if ( ! $this->CI->website_model->has_tables())
		{
			return array('ok' => FALSE, 'error' => 'website_schema_missing');
		}

		$identifier = trim((string) $identifier);
		if ($identifier === '' || strlen($identifier) > 64)
		{
			return array('ok' => FALSE, 'error' => 'invalid_identifier');
		}

		if ( ! $this->CI->website_model->rate_limit_hit('otp_ip:'.$ip, 3600, 20))
		{
			return array('ok' => FALSE, 'error' => 'rate_limited');
		}

		$customer = $this->CI->website_model->find_customer_by_identifier($identifier);
		if ( ! $customer)
		{
			// Generic response; do not reveal whether ناپ or phone exists
			return array('ok' => FALSE, 'error' => 'not_found');
		}

		$cooldown = 60;
		$remaining = $this->CI->website_model->otp_cooldown_remaining((int) $customer['code'], $cooldown);
		if ($remaining > 0)
		{
			return array(
				'ok' => FALSE,
				'error' => 'otp_cooldown',
				'retry_after' => $remaining,
			);
		}

		$phone = $this->CI->website_otp->normalize_phone(isset($customer['Phone']) ? $customer['Phone'] : '');
		if ($phone === '' || strlen($phone) < 11)
		{
			return array('ok' => FALSE, 'error' => 'phone_missing');
		}

		$otp = (string) random_int(100000, 999999);
		$challenge_id = $this->CI->website_model->create_otp_challenge(array(
			'customer_code' => (int) $customer['code'],
			'man_napno' => (string) $customer['man_napno'],
			'phone_masked' => $this->mask_phone($phone),
			'phone_normalized' => $phone,
			'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
			'attempts' => 0,
			'max_attempts' => 5,
			'expires_at' => date('Y-m-d H:i:s', time() + $this->otp_ttl),
			'ip_address' => substr((string) $ip, 0, 45),
			'created_at' => date('Y-m-d H:i:s'),
		));

		$send = $this->CI->website_otp->send_otp($phone, $otp, isset($customer['name']) ? $customer['name'] : '');
		$result = array(
			'ok' => TRUE,
			'challenge_id' => $challenge_id,
			'phone_masked' => $this->mask_phone($phone),
			'customer_name' => $this->public_name(isset($customer['name']) ? $customer['name'] : ''),
			'man_napno' => (string) $customer['man_napno'],
		);

		if ( ! empty($send['debug_otp']))
		{
			$result['debug_otp'] = $send['debug_otp'];
		}
		if (empty($send['ok']))
		{
			$result['delivery'] = isset($send['error']) ? $send['error'] : 'send_failed';
		}
		else
		{
			$result['delivery'] = 'sent';
		}

		return $result;
	}

	/**
	 * @return array{ok:bool, token?:string, customer?:array, error?:string}
	 */
	public function verify_otp($challenge_id, $otp, $ip = '', $user_agent = '')
	{
		$challenge = $this->CI->website_model->get_otp_challenge((int) $challenge_id);
		if ( ! $challenge)
		{
			return array('ok' => FALSE, 'error' => 'invalid_challenge');
		}
		if ( ! empty($challenge['consumed_at']))
		{
			return array('ok' => FALSE, 'error' => 'already_used');
		}
		if (strtotime($challenge['expires_at']) < time())
		{
			return array('ok' => FALSE, 'error' => 'expired');
		}
		if ((int) $challenge['attempts'] >= (int) $challenge['max_attempts'])
		{
			return array('ok' => FALSE, 'error' => 'too_many_attempts');
		}

		$otp = preg_replace('/\D+/', '', (string) $otp);
		if (strlen($otp) !== 6 || ! password_verify($otp, $challenge['otp_hash']))
		{
			$this->CI->website_model->update_otp_challenge((int) $challenge['id'], array(
				'attempts' => (int) $challenge['attempts'] + 1,
			));
			return array('ok' => FALSE, 'error' => 'invalid_otp');
		}

		$this->CI->website_model->update_otp_challenge((int) $challenge['id'], array(
			'consumed_at' => date('Y-m-d H:i:s'),
		));

		$token = bin2hex(random_bytes(32));
		$token_hash = hash('sha256', $token);
		$this->CI->website_model->create_session(array(
			'customer_code' => (int) $challenge['customer_code'],
			'token_hash' => $token_hash,
			'expires_at' => date('Y-m-d H:i:s', time() + $this->session_ttl),
			'ip_address' => substr((string) $ip, 0, 45),
			'user_agent' => substr((string) $user_agent, 0, 255),
			'created_at' => date('Y-m-d H:i:s'),
			'last_seen_at' => date('Y-m-d H:i:s'),
		));

		$customer = $this->CI->website_model->get_customer_by_code((int) $challenge['customer_code']);
		return array(
			'ok' => TRUE,
			'token' => $token,
			'expires_in' => $this->session_ttl,
			'customer' => $this->public_customer($customer, TRUE),
		);
	}

	/**
	 * @return array|null customer public payload
	 */
	public function customer_from_token($token)
	{
		$token = trim((string) $token);
		if ($token === '' || strlen($token) < 32)
		{
			return NULL;
		}
		$session = $this->CI->website_model->get_session_by_token_hash(hash('sha256', $token));
		if ( ! $session)
		{
			return NULL;
		}
		if (strtotime($session['expires_at']) < time())
		{
			return NULL;
		}
		$this->CI->website_model->touch_session((int) $session['id']);
		$customer = $this->CI->website_model->get_customer_by_code((int) $session['customer_code']);
		if ( ! $customer)
		{
			return NULL;
		}
		$customer['_session_id'] = (int) $session['id'];
		return $customer;
	}

	public function logout($token)
	{
		$token = trim((string) $token);
		if ($token === '')
		{
			return;
		}
		$this->CI->website_model->revoke_session_by_token_hash(hash('sha256', $token));
	}

	public function public_customer($customer, $full = FALSE)
	{
		if ( ! $customer)
		{
			return NULL;
		}
		$name = isset($customer['name']) ? trim((string) $customer['name']) : '';
		return array(
			'code' => (int) $customer['code'],
			'name' => $full ? $name : $this->public_name($name),
			'man_napno' => (string) $customer['man_napno'],
			'phone_masked' => $this->mask_phone($this->CI->website_otp->normalize_phone(isset($customer['Phone']) ? $customer['Phone'] : '')),
			'phone' => $full ? (string) (isset($customer['Phone']) ? $customer['Phone'] : '') : '',
			'city' => isset($customer['city']) ? (string) $customer['city'] : '',
			'address' => $full && isset($customer['Address']) ? (string) $customer['Address'] : '',
		);
	}

	protected function mask_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		$len = strlen($digits);
		if ($len < 4)
		{
			return '****';
		}
		return str_repeat('*', max(0, $len - 4)).substr($digits, -4);
	}

	protected function public_name($name)
	{
		$name = trim((string) $name);
		if ($name === '')
		{
			return '';
		}
		$parts = preg_split('/\s+/', $name);
		if (count($parts) === 1)
		{
			return $parts[0];
		}
		return $parts[0].' '.mb_substr($parts[count($parts) - 1], 0, 1).'.';
	}
}
