<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/Api_Controller.php';

/**
 * Staff offline-first sync API.
 * Routes under /api/erp_sync/*
 *
 * Auth: ERP session cookie (when online) OR Bearer sync token (issued while online).
 */
class Erp_sync extends Api_Controller
{
	/** @var array|null */
	protected $sync_user = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->library('Erp_sync_service', NULL, 'erp_sync');
	}

	public function health()
	{
		return $this->respond(array(
			'ok' => TRUE,
			'service' => 'erp_sync',
			'server_time' => date('c'),
			'version' => 1,
		));
	}

	public function capabilities()
	{
		return $this->respond(array(
			'ok' => TRUE,
			'capabilities' => $this->erp_sync->capabilities(),
		));
	}

	/**
	 * Issue / refresh offline sync token. Requires active ERP session.
	 */
	public function issue_token()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$user = $this->session->userdata('user');
		if ( ! $user)
		{
			return $this->fail('login_required', 401);
		}
		$input = $this->json_input();
		$device_id = isset($input['device_id']) ? trim((string) $input['device_id']) : '';
		if ($device_id === '' || ! $this->_valid_uuid($device_id))
		{
			return $this->fail('invalid_device_id', 400);
		}
		$label = isset($input['label']) ? substr(trim((string) $input['label']), 0, 160) : '';
		$ua = (string) $this->input->user_agent(TRUE);
		$result = $this->erp_sync->issue_token((string) $user, $device_id, $label, $ua);
		if (empty($result['ok']))
		{
			return $this->fail(isset($result['error']) ? $result['error'] : 'issue_failed', 400);
		}
		return $this->respond($result);
	}

	public function revoke_token()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$auth = $this->_require_sync_auth(TRUE);
		if ($auth === NULL)
		{
			return;
		}
		$input = $this->json_input();
		$device_id = isset($input['device_id']) ? trim((string) $input['device_id']) : $auth['device_id'];
		$this->erp_sync->revoke($device_id, $auth['user_name']);
		return $this->respond(array('ok' => TRUE));
	}

	public function bootstrap()
	{
		$auth = $this->_require_sync_auth(FALSE);
		if ($auth === NULL)
		{
			return;
		}
		$data = $this->erp_sync->bootstrap(1500);
		$data['auth'] = array(
			'user_name' => $auth['user_name'],
			'device_id' => $auth['device_id'],
		);
		return $this->respond($data);
	}

	public function pull()
	{
		$auth = $this->_require_sync_auth(FALSE);
		if ($auth === NULL)
		{
			return;
		}
		$since = $this->input->get('since', TRUE);
		if ($since === NULL || $since === '')
		{
			$input = $this->json_input();
			$since = isset($input['since']) ? $input['since'] : NULL;
		}
		return $this->respond($this->erp_sync->pull($since));
	}

	/**
	 * Transfer queues + Order Status rows for offline tables/search.
	 */
	public function lists()
	{
		$auth = $this->_require_sync_auth(FALSE);
		if ($auth === NULL)
		{
			return;
		}
		return $this->respond($this->erp_sync->offline_lists());
	}

	public function push()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$auth = $this->_require_sync_auth(FALSE);
		if ($auth === NULL)
		{
			return;
		}
		$input = $this->json_input();
		$ops = isset($input['ops']) && is_array($input['ops']) ? $input['ops'] : array();
		if (empty($ops))
		{
			return $this->fail('no_ops', 400);
		}
		if (count($ops) > 100)
		{
			return $this->fail('batch_too_large', 400);
		}
		$result = $this->erp_sync->push_ops($auth['device_id'], $auth['user_name'], $ops);
		return $this->respond($result);
	}

	/**
	 * Staff chose Keep Local or Keep Server for an offline conflict.
	 */
	public function resolve_conflict()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			return $this->fail('method_not_allowed', 405);
		}
		$auth = $this->_require_sync_auth(FALSE);
		if ($auth === NULL)
		{
			return;
		}
		$input = $this->json_input();
		$result = $this->erp_sync->resolve_conflict($auth['device_id'], $auth['user_name'], $input);
		if (empty($result['ok']))
		{
			return $this->fail(isset($result['error']) ? $result['error'] : 'resolve_failed', 400, $result);
		}
		return $this->respond($result);
	}

	/**
	 * Verify additive offline sync schema (tables + row_version columns).
	 */
	public function schema_status()
	{
		$auth = $this->_require_sync_auth(TRUE);
		if ($auth === NULL)
		{
			return;
		}
		return $this->respond($this->erp_sync->schema_status());
	}

	/**
	 * @param bool $allow_session_only
	 * @return array|null
	 */
	private function _require_sync_auth($allow_session_only = FALSE)
	{
		$header = (string) $this->input->get_request_header('Authorization', TRUE);
		$bearer = $this->erp_sync->authenticate_bearer($header);
		if ($bearer)
		{
			$this->sync_user = $bearer;
			return $bearer;
		}

		$user = $this->session->userdata('user');
		if ($user)
		{
			$input = $this->json_input();
			$device_id = isset($input['device_id']) ? trim((string) $input['device_id']) : '';
			if ($device_id === '')
			{
				$device_id = (string) $this->input->get_request_header('X-Device-Id', TRUE);
			}
			if ($device_id === '' && $allow_session_only)
			{
				$device_id = 'session';
			}
			if ($device_id === '')
			{
				$this->fail('device_id_required', 400);
				return NULL;
			}
			$this->sync_user = array(
				'user_name' => (string) $user,
				'device_id' => $device_id,
				'token_id' => 0,
			);
			return $this->sync_user;
		}

		$this->fail('unauthorized', 401);
		return NULL;
	}

	private function _valid_uuid($value)
	{
		return (bool) preg_match(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$value
		);
	}
}
