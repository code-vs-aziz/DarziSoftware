<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/Api_Controller.php';

/**
 * Public website auth: OTP lookup / verify / logout / me
 * Routes: /api/auth/{method}
 */
class Auth extends Api_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
	}

	public function lookup()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$input = $this->json_input();
		$identifier = '';
		if (isset($input['phone']) && trim((string) $input['phone']) !== '')
		{
			$identifier = $input['phone'];
		}
		elseif (isset($input['identifier']) && trim((string) $input['identifier']) !== '')
		{
			$identifier = $input['identifier'];
		}
		elseif (isset($input['man_napno']))
		{
			$identifier = $input['man_napno'];
		}
		elseif (isset($input['naap_no']))
		{
			$identifier = $input['naap_no'];
		}
		$result = $this->website_auth->start_otp($identifier, $this->client_ip());
		if (empty($result['ok']))
		{
			$status = ($result['error'] === 'rate_limited' || $result['error'] === 'otp_cooldown') ? 429 : 400;
			$extra = array();
			if (isset($result['retry_after']))
			{
				$extra['retry_after'] = (int) $result['retry_after'];
			}
			return $this->fail($result['error'], $status, $extra);
		}
		return $this->ok($result);
	}

	public function verify()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$input = $this->json_input();
		$result = $this->website_auth->verify_otp(
			isset($input['challenge_id']) ? $input['challenge_id'] : 0,
			isset($input['otp']) ? $input['otp'] : '',
			$this->client_ip(),
			(string) $this->input->user_agent()
		);
		if (empty($result['ok']))
		{
			return $this->fail($result['error'], 400);
		}
		return $this->ok($result);
	}

	public function logout()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$this->website_auth->logout($this->bearer_token());
		return $this->ok();
	}

	public function me()
	{
		$this->require_customer_auth();
		return $this->ok(array(
			'customer' => $this->website_auth->public_customer($this->customer),
		));
	}
}
