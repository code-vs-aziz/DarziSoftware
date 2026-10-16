<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for public website JSON APIs (customer-facing).
 * Does not require ERP staff login; uses Bearer token for customer routes.
 */
class Api_Controller extends CI_Controller
{
	/** @var array|null */
	protected $customer = NULL;

	/** @var bool */
	protected $require_customer = FALSE;

	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('X-Content-Type-Options: nosniff');
		$this->output->set_header('X-Robots-Tag: noindex, nofollow');
		$this->handle_cors();
		if ($this->input->method(TRUE) === 'OPTIONS')
		{
			$this->output->set_status_header(204);
			$this->output->_display();
			exit;
		}
		$this->load->model('Website_model', 'website_model');
		$this->load->library('Website_auth_service', NULL, 'website_auth');
		if ($this->require_customer)
		{
			$this->require_customer_auth();
		}
	}

	protected function handle_cors()
	{
		$origin = (string) $this->input->get_request_header('Origin', TRUE);
		$allowed = $this->allowed_origins();
		if ($origin !== '' && in_array($origin, $allowed, TRUE))
		{
			$this->output->set_header('Access-Control-Allow-Origin: '.$origin);
			$this->output->set_header('Vary: Origin');
			$this->output->set_header('Access-Control-Allow-Credentials: true');
		}
		$this->output->set_header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept, X-Requested-With');
		$this->output->set_header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
	}

	protected function allowed_origins()
	{
		$raw = function_exists('dz_env') ? (string) dz_env('WEBSITE_CORS_ORIGINS', 'http://localhost:3000,http://127.0.0.1:3000') : 'http://localhost:3000';
		$parts = preg_split('/\s*,\s*/', $raw);
		$out = array();
		foreach ($parts as $p)
		{
			$p = rtrim($p, '/');
			if ($p !== '')
			{
				$out[] = $p;
			}
		}
		return $out;
	}

	protected function json_input()
	{
		$raw = $this->input->raw_input_stream;
		if ($raw === NULL || $raw === '')
		{
			return array();
		}
		$data = json_decode($raw, TRUE);
		return is_array($data) ? $data : array();
	}

	protected function respond($data, $status = 200)
	{
		$this->output
			->set_status_header((int) $status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	protected function fail($error, $status = 400, $extra = array())
	{
		$payload = array_merge(array('ok' => FALSE, 'error' => $error), $extra);
		$this->respond($payload, $status);
	}

	protected function ok($data = array(), $status = 200)
	{
		$this->respond(array_merge(array('ok' => TRUE), $data), $status);
	}

	protected function bearer_token()
	{
		$header = (string) $this->input->get_request_header('Authorization', TRUE);
		if (stripos($header, 'Bearer ') === 0)
		{
			return trim(substr($header, 7));
		}
		$token = (string) $this->input->get_request_header('X-Customer-Token', TRUE);
		return trim($token);
	}

	protected function require_customer_auth()
	{
		$customer = $this->CI_website_customer();
		if ( ! $customer)
		{
			$this->fail('unauthorized', 401);
			$this->output->_display();
			exit;
		}
		$this->customer = $customer;
	}

	protected function CI_website_customer()
	{
		return $this->website_auth->customer_from_token($this->bearer_token());
	}

	protected function client_ip()
	{
		$ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
		if ($ip === '' || $ip === '::1')
		{
			return $ip === '::1' ? '127.0.0.1' : '0.0.0.0';
		}
		return substr($ip, 0, 45);
	}

	protected function ensure_schema()
	{
		if ( ! $this->website_model->has_tables())
		{
			$this->fail('website_schema_missing', 503, array(
				'message' => 'Run application/sql/website_schema.sql on the ERP database.',
			));
			$this->output->_display();
			exit;
		}
	}
}
