<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * HTTP client for local whatsapp-bridge (Node + whatsapp-web.js).
 */
class Whatsapp_bridge
{
	protected $base_url;
	protected $secret;
	protected $enabled;

	public function __construct()
	{
		$this->enabled = function_exists('dz_env_bool')
			? dz_env_bool('WHATSAPP_BRIDGE_ENABLED', FALSE)
			: FALSE;
		$this->base_url = function_exists('dz_env')
			? rtrim((string) dz_env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3099'), '/')
			: 'http://127.0.0.1:3099';
		$this->secret = function_exists('dz_env')
			? trim((string) dz_env('WHATSAPP_BRIDGE_SECRET', ''))
			: '';
	}

	public function is_enabled()
	{
		return $this->enabled && $this->base_url !== '';
	}

	/**
	 * Simple circuit breaker: after repeated bridge failures, skip calls for a cool-down.
	 */
	public function is_circuit_open()
	{
		$state = $this->circuit_state();
		if (empty($state['open_until']))
		{
			return FALSE;
		}
		return time() < (int) $state['open_until'];
	}

	protected function circuit_file()
	{
		return APPPATH.'cache/whatsapp_bridge_circuit.json';
	}

	protected function circuit_state()
	{
		$file = $this->circuit_file();
		if ( ! is_file($file))
		{
			return array('failures' => 0, 'open_until' => 0);
		}
		$data = json_decode((string) @file_get_contents($file), TRUE);
		return is_array($data) ? $data : array('failures' => 0, 'open_until' => 0);
	}

	protected function circuit_record_success()
	{
		@file_put_contents($this->circuit_file(), json_encode(array(
			'failures' => 0,
			'open_until' => 0,
			'updated_at' => time(),
		)));
	}

	protected function circuit_record_failure($error = '')
	{
		$state = $this->circuit_state();
		$failures = isset($state['failures']) ? ((int) $state['failures'] + 1) : 1;
		$open_until = 0;
		// Need several misses before cool-down — a single slow Chromium tick
		// must not make the whole shop see "Bridge down".
		if ($failures >= 4)
		{
			$open_until = time() + 20;
			$failures = 0;
		}
		@file_put_contents($this->circuit_file(), json_encode(array(
			'failures' => $failures,
			'open_until' => $open_until,
			'last_error' => (string) $error,
			'updated_at' => time(),
		)));
	}

	/**
	 * @return array{ok:bool, ready?:bool, state?:string, has_qr?:bool, error?:string, raw?:array}
	 */
	public function status()
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}
		if ($this->is_circuit_open())
		{
			return array(
				'ok' => FALSE,
				'ready' => FALSE,
				'state' => 'recovering',
				'error' => 'circuit_open',
			);
		}

		$cache_file = APPPATH.'cache/whatsapp_bridge_status.json';
		// Cache healthy status a bit longer to ride out brief Chromium stalls.
		if (is_file($cache_file) && (time() - (int) filemtime($cache_file)) < 20)
		{
			$cached = json_decode((string) @file_get_contents($cache_file), TRUE);
			if (is_array($cached) && ! empty($cached['ready']))
			{
				return $cached;
			}
		}

		if ( ! $this->bridge_port_open())
		{
			$this->circuit_record_failure('bridge_unreachable');
			@unlink($cache_file);
			return array('ok' => FALSE, 'ready' => FALSE, 'error' => 'bridge_unreachable');
		}

		// 3s — 1s was too tight when Chromium is busy (false "Bridge down").
		$response = $this->request('GET', '/status', array(), 3);
		if (!$response['ok'])
		{
			$this->circuit_record_failure(isset($response['error']) ? (string) $response['error'] : 'bridge_unreachable');
			@unlink($cache_file);
			return $response;
		}

		$data = isset($response['data']) ? $response['data'] : array();
		$result = array(
			'ok' => TRUE,
			'ready' => !empty($data['ready']),
			'state' => isset($data['state']) ? (string) $data['state'] : '',
			'has_qr' => !empty($data['has_qr']),
			'error' => isset($data['error']) ? (string) $data['error'] : '',
			'raw' => $data,
		);
		$this->circuit_record_success();
		if ( ! empty($result['ready']))
		{
			@file_put_contents($cache_file, json_encode($result));
		}
		else
		{
			@unlink($cache_file);
		}
		return $result;
	}

	/**
	 * Fast TCP probe so a dead bridge does not burn curl timeouts on every poll.
	 */
	protected function bridge_port_open()
	{
		$host = '127.0.0.1';
		$port = 3099;
		if (preg_match('#^https?://([^/:]+)#i', (string) $this->base_url, $m))
		{
			$host = $m[1];
		}
		if (preg_match('#:(\d+)\s*$#', (string) $this->base_url, $m))
		{
			$port = (int) $m[1];
		}
		$errno = 0;
		$errstr = '';
		$fp = @fsockopen($host, $port, $errno, $errstr, 0.2);
		if ($fp)
		{
			fclose($fp);
			return TRUE;
		}
		return FALSE;
	}

	public function clear_status_cache()
	{
		$cache_file = APPPATH.'cache/whatsapp_bridge_status.json';
		if (is_file($cache_file))
		{
			@unlink($cache_file);
		}
	}

	/** Reset circuit breaker so UI can retry after transient failures. */
	public function reset_circuit()
	{
		$this->circuit_record_success();
	}

	/**
	 * Latest QR as data-URL for in-app display.
	 *
	 * @return array{ok:bool, ready?:bool, qr?:string, state?:string, error?:string}
	 */
	public function qr()
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}
		if ($this->is_circuit_open())
		{
			return array('ok' => FALSE, 'error' => 'circuit_open');
		}
		if ( ! $this->bridge_port_open())
		{
			return array('ok' => FALSE, 'error' => 'bridge_unreachable');
		}

		$response = $this->request('GET', '/qr', array(), 1);
		if (!$response['ok'])
		{
			return $response;
		}

		$data = isset($response['data']) ? $response['data'] : array();
		return array(
			'ok' => TRUE,
			'ready' => !empty($data['ready']),
			'state' => isset($data['state']) ? (string) $data['state'] : '',
			'qr' => isset($data['qr']) ? (string) $data['qr'] : '',
			'qr_at' => isset($data['qr_at']) ? (string) $data['qr_at'] : '',
			'error' => isset($data['error']) ? (string) $data['error'] : '',
		);
	}

	/**
	 * Ask running bridge to reconnect. clear_session=true forces a fresh QR.
	 *
	 * @return array{ok:bool, error?:string, message?:string, qr?:string, status?:array}
	 */
	public function restart($clear_session = TRUE)
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}

		$this->reset_circuit();
		$this->clear_status_cache();
		$response = $this->request('POST', '/restart', array(
			'clear_session' => $clear_session ? TRUE : FALSE,
		), 15);

		if (!$response['ok'])
		{
			return $response;
		}

		$data = isset($response['data']) ? $response['data'] : array();
		if (empty($data['success']))
		{
			return array(
				'ok' => FALSE,
				'error' => isset($data['message']) ? (string) $data['message'] : 'restart_failed',
			);
		}

		// Return immediately — UI fast-polls /status+/qr (avoids blocking PHP built-in server).
		return $this->normalize_bridge_boot_payload($data, $clear_session ? 'restarting_fresh_qr' : 'restarting');
	}

	/**
	 * Hard wipe session/auth and wait for a brand-new QR (dedicated Reset button).
	 *
	 * @return array{ok:bool, error?:string, message?:string, qr?:string, status?:array}
	 */
	public function reset_qr()
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}

		$this->reset_circuit();
		$this->clear_status_cache();

		$alive = $this->status();
		if (empty($alive['ok']))
		{
			$started = $this->start_process();
			if (empty($started['ok']))
			{
				return $started;
			}
			// Give the fresh process a moment, then wipe + wait for QR.
			usleep(800000);
		}

		$response = $this->request('POST', '/reset-qr', array(), 15);
		if (!$response['ok'])
		{
			// Fallback: classic restart with session clear.
			return $this->restart(TRUE);
		}

		$data = isset($response['data']) ? $response['data'] : array();
		if (empty($data['success']))
		{
			return array(
				'ok' => FALSE,
				'error' => isset($data['message']) ? (string) $data['message'] : 'reset_qr_failed',
			);
		}

		return $this->normalize_bridge_boot_payload($data, 'reset_qr');
	}

	/**
	 * @param array $data
	 * @param string $default_message
	 * @return array
	 */
	protected function normalize_bridge_boot_payload(array $data, $default_message = 'ok')
	{
		$ready = !empty($data['ready']);
		$qr = isset($data['qr']) ? (string) $data['qr'] : '';
		$has_qr = !empty($data['has_qr']) || $qr !== '';
		$state = isset($data['state']) ? (string) $data['state'] : '';
		if ($state === '' && $has_qr)
		{
			$state = 'qr';
		}

		return array(
			'ok' => TRUE,
			'message' => isset($data['message']) ? (string) $data['message'] : $default_message,
			'qr' => $qr,
			'qr_at' => isset($data['qr_at']) ? (string) $data['qr_at'] : '',
			'status' => array(
				'ok' => TRUE,
				'ready' => $ready,
				'state' => $state,
				'has_qr' => $has_qr,
				'error' => isset($data['error']) ? (string) $data['error'] : '',
			),
		);
	}

	/**
	 * Start Node bridge process when it is not listening (localhost only).
	 *
	 * @return array{ok:bool, error?:string, message?:string, pid?:int}
	 */
	public function start_process()
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}

		// User clicked Start — always allow a fresh probe.
		$this->reset_circuit();
		$this->clear_status_cache();

		$alive = $this->status();
		if (!empty($alive['ok']))
		{
			return array('ok' => TRUE, 'message' => 'already_running', 'status' => $alive);
		}

		$bridge_dir = FCPATH.'bridge/whatsapp-bridge';
		$server_js = $bridge_dir.DIRECTORY_SEPARATOR.'server.js';
		if (!is_dir($bridge_dir) || !is_file($server_js))
		{
			return array('ok' => FALSE, 'error' => 'bridge_folder_missing');
		}

		$node = $this->find_node_binary();
		if ($node === '')
		{
			return array('ok' => FALSE, 'error' => 'node_not_found');
		}

		$port = '3099';
		if (preg_match('#:(\d+)\s*$#', $this->base_url, $m))
		{
			$port = $m[1];
		}

		$log_file = $bridge_dir.DIRECTORY_SEPARATOR.'bridge.log';
		$pid_file = $bridge_dir.DIRECTORY_SEPARATOR.'bridge.pid';
		$env_parts = array(
			'WHATSAPP_BRIDGE_PORT='.escapeshellarg($port),
		);
		if ($this->secret !== '')
		{
			$env_parts[] = 'WHATSAPP_BRIDGE_SECRET='.escapeshellarg($this->secret);
		}

		// Close inherited FDs (critical for PHP built-in server — otherwise Node
		// steals :8080 and Chromium restarts fight over the session lock).
		$cmd = 'cd '.escapeshellarg($bridge_dir).' && ('
			.' for fd in /dev/fd/*; do'
			.' n="${fd##*/}"; case "$n" in *[!0-9]*|0|1|2) continue ;; esac;'
			.' eval "exec $n>&-" 2>/dev/null || true;'
			.' done;'
			.' '.implode(' ', $env_parts)
			.' exec '.escapeshellarg($node).' server.js'
			.' ) >> '.escapeshellarg($log_file).' 2>&1 < /dev/null & echo $!';

		$pid_out = trim((string) @shell_exec($cmd));
		$pid = (int) $pid_out;
		if ($pid <= 0)
		{
			return array('ok' => FALSE, 'error' => 'start_failed');
		}

		@file_put_contents($pid_file, (string) $pid);

		// Give Chromium a moment to bind the port
		usleep(900000);
		$check = $this->status();
		if (!empty($check['ok']))
		{
			return array('ok' => TRUE, 'message' => 'started', 'pid' => $pid);
		}

		return array(
			'ok' => TRUE,
			'message' => 'started_waiting',
			'pid' => $pid,
			'hint' => 'Bridge process started; wait for QR in panel.',
		);
	}

	/**
	 * Stop + start process, or restart session inside running bridge.
	 *
	 * @return array{ok:bool, error?:string, message?:string}
	 */
	public function restart_or_start($clear_session = TRUE)
	{
		$status = $this->status();
		if (!empty($status['ok']))
		{
			return $this->restart($clear_session);
		}
		return $this->start_process();
	}

	protected function find_node_binary()
	{
		$candidates = array(
			'/usr/local/bin/node',
			'/opt/homebrew/bin/node',
			'/usr/bin/node',
		);
		foreach ($candidates as $bin)
		{
			if (is_file($bin) && is_executable($bin))
			{
				return $bin;
			}
		}
		$which = trim((string) @shell_exec('command -v node 2>/dev/null'));
		if ($which !== '' && is_file($which))
		{
			return $which;
		}
		return '';
	}

	/**
	 * @return array{ok:bool, error?:string, message_id?:string}
	 */
	public function send($phone, $message)
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}
		if ($this->is_circuit_open())
		{
			return array('ok' => FALSE, 'error' => 'circuit_open');
		}

		$response = $this->request('POST', '/send', array(
			'phone' => (string) $phone,
			'message' => (string) $message,
		));

		if (!$response['ok'])
		{
			$err = isset($response['error']) ? (string) $response['error'] : 'send_failed';
			if ($err === 'bridge_unreachable' || $err === 'curl_init_failed')
			{
				$this->circuit_record_failure($err);
			}
			return $response;
		}

		$data = isset($response['data']) ? $response['data'] : array();
		if (empty($data['success']))
		{
			return array(
				'ok' => FALSE,
				'error' => isset($data['message']) ? (string) $data['message'] : 'send_failed',
			);
		}

		$this->circuit_record_success();
		return array(
			'ok' => TRUE,
			'message_id' => isset($data['message_id']) ? (string) $data['message_id'] : '',
		);
	}

	/**
	 * Send a local PDF/document via WhatsApp bridge.
	 *
	 * @return array{ok:bool, error?:string, message_id?:string}
	 */
	public function send_document($phone, $file_path, $caption = '')
	{
		if (!$this->is_enabled())
		{
			return array('ok' => FALSE, 'error' => 'bridge_disabled');
		}
		if ($this->is_circuit_open())
		{
			return array('ok' => FALSE, 'error' => 'circuit_open');
		}
		$file_path = (string) $file_path;
		if ($file_path === '' || !is_file($file_path))
		{
			return array('ok' => FALSE, 'error' => 'missing_file');
		}

		if ( ! $this->bridge_port_open())
		{
			$this->circuit_record_failure('bridge_unreachable');
			return array('ok' => FALSE, 'error' => 'bridge_unreachable');
		}
		// Hard cap: single-threaded PHP must never sit on a dead/healing bridge.
		$response = $this->request('POST', '/send-document', array(
			'phone' => (string) $phone,
			'file_path' => $file_path,
			'caption' => (string) $caption,
		), 8);

		$data = isset($response['data']) && is_array($response['data']) ? $response['data'] : array();

		if (!$response['ok'] || empty($data['success']))
		{
			$detail = isset($response['error']) ? (string) $response['error'] : 'send_failed';
			if (isset($data['error']) && is_string($data['error']) && $data['error'] !== '')
			{
				$detail = (string) $data['error'];
			}
			elseif (isset($data['message']) && (string) $data['message'] !== '')
			{
				$detail = (string) $data['message'];
			}
			if ((string) (isset($data['message']) ? $data['message'] : '') === 'number_not_on_whatsapp')
			{
				$detail = 'number_not_on_whatsapp';
			}
			if ($detail === 'bridge_unreachable' || $detail === 'curl_init_failed' || strpos($detail, 'http_') === 0)
			{
				$this->circuit_record_failure($detail);
			}
			$out = array('ok' => FALSE, 'error' => $detail);
			if ( ! empty($data['caption_sent']) || strpos($detail, 'caption_sent_document_failed') === 0)
			{
				$out['caption_sent'] = TRUE;
			}
			return $out;
		}

		$this->circuit_record_success();
		return array(
			'ok' => TRUE,
			'message_id' => isset($data['message_id']) ? (string) $data['message_id'] : '',
		);
	}

	protected function request($method, $path, array $body = array(), $timeout = 20)
	{
		$url = $this->base_url.$path;
		$ch = curl_init($url);
		if ($ch === FALSE)
		{
			return array('ok' => FALSE, 'error' => 'curl_init_failed');
		}

		$headers = array('Accept: application/json');
		if ($this->secret !== '')
		{
			$headers[] = 'X-Bridge-Secret: '.$this->secret;
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 800);
		curl_setopt($ch, CURLOPT_TIMEOUT, max(1, (int) $timeout));
		// Helps CURLOPT_TIMEOUT actually fire if the bridge event-loop stalls.
		if (defined('CURLOPT_NOSIGNAL'))
		{
			curl_setopt($ch, CURLOPT_NOSIGNAL, TRUE);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		if ($method === 'POST')
		{
			$payload = $body;
			if ($this->secret !== '')
			{
				$payload['secret'] = $this->secret;
			}
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$raw = curl_exec($ch);
		$errno = curl_errno($ch);
		$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno !== 0)
		{
			return array('ok' => FALSE, 'error' => 'bridge_unreachable');
		}

		$data = json_decode((string) $raw, TRUE);
		if (!is_array($data))
		{
			return array('ok' => FALSE, 'error' => 'invalid_response', 'http' => $http);
		}

		if ($http >= 400)
		{
			return array(
				'ok' => FALSE,
				'error' => isset($data['message']) ? (string) $data['message'] : 'http_'.$http,
				'data' => $data,
			);
		}

		return array('ok' => TRUE, 'data' => $data);
	}
}
