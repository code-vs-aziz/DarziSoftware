<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Offline-first sync engine (server side).
 * Idempotent apply of queued client ops; bootstrap + delta pull.
 */
class Erp_sync_service
{
	/** @var CI_Controller */
	protected $CI;

	/** Sensitive customer fields that require conflict queue when both sides change */
	protected $customer_conflict_fields = array('name', 'Phone', 'cnic', 'man_napno');

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Erp_sync_model', 'erp_sync_model');
		$this->CI->erp_sync_model->ensure_schema();
	}

	public function token_ttl_days()
	{
		$days = function_exists('dz_env') ? (int) dz_env('ERP_SYNC_TOKEN_DAYS', 7) : 7;
		return $days > 0 ? $days : 7;
	}

	public function hash_token($plain)
	{
		return hash('sha256', (string) $plain);
	}

	public function issue_token($user_name, $device_id, $label = '', $ua = '')
	{
		$user_name = trim((string) $user_name);
		$device_id = trim((string) $device_id);
		if ($user_name === '' || $device_id === '')
		{
			return array('ok' => FALSE, 'error' => 'missing_user_or_device');
		}

		$this->CI->erp_sync_model->upsert_device($device_id, $user_name, $label, $ua);
		$this->CI->erp_sync_model->revoke_device_tokens($device_id, $user_name);

		$plain = bin2hex(random_bytes(32));
		$hash = $this->hash_token($plain);
		$expires = date('Y-m-d H:i:s', time() + ($this->token_ttl_days() * 86400));
		$this->CI->erp_sync_model->insert_token($device_id, $user_name, $hash, $expires);

		return array(
			'ok' => TRUE,
			'token' => $plain,
			'device_id' => $device_id,
			'user_name' => $user_name,
			'expires_at' => $expires,
			'ttl_days' => $this->token_ttl_days(),
		);
	}

	public function authenticate_bearer($authorization_header)
	{
		$token = '';
		if (preg_match('/^\s*Bearer\s+(\S+)\s*$/i', (string) $authorization_header, $m))
		{
			$token = $m[1];
		}
		if ($token === '')
		{
			return NULL;
		}
		$row = $this->CI->erp_sync_model->find_token_by_hash($this->hash_token($token));
		if ( ! $row)
		{
			return NULL;
		}
		if (strtotime($row['expires_at']) < time())
		{
			return NULL;
		}
		$this->CI->erp_sync_model->touch_token((int) $row['id']);
		$this->CI->erp_sync_model->upsert_device($row['device_id'], $row['user_name'], '', '');
		return array(
			'user_name' => $row['user_name'],
			'device_id' => $row['device_id'],
			'token_id' => (int) $row['id'],
		);
	}

	public function revoke($device_id, $user_name)
	{
		$this->CI->erp_sync_model->revoke_device_tokens($device_id, $user_name);
		return array('ok' => TRUE);
	}

	/**
	 * Mark server conflict rows resolved after staff chooses Keep Local / Keep Server.
	 * Does not delete local or server business rows.
	 */
	public function resolve_conflict($device_id, $user_name, array $input)
	{
		$entity_type = isset($input['entity_type']) ? trim((string) $input['entity_type']) : '';
		$server_entity_id = isset($input['server_entity_id']) ? trim((string) $input['server_entity_id']) : '';
		$resolution = isset($input['resolution']) ? trim((string) $input['resolution']) : '';
		if ($entity_type === '' || ($resolution !== 'keep_local' && $resolution !== 'keep_server'))
		{
			return array('ok' => FALSE, 'error' => 'invalid_resolution');
		}
		if ($resolution === 'keep_local' && in_array($entity_type, array('delivery', 'cash'), TRUE))
		{
			return array('ok' => FALSE, 'error' => 'keep_local_not_allowed', 'conflict' => TRUE);
		}
		$updated = $this->CI->erp_sync_model->resolve_open_conflicts(
			$device_id,
			$entity_type,
			$server_entity_id,
			$resolution,
			$user_name
		);
		return array(
			'ok' => TRUE,
			'resolution' => $resolution,
			'entity_type' => $entity_type,
			'server_entity_id' => $server_entity_id,
			'updated' => $updated,
		);
	}

	/**
	 * Report whether offline sync columns/tables exist (additive ensure already ran).
	 */
	public function schema_status()
	{
		$this->CI->erp_sync_model->ensure_schema();
		$tables = array(
			'erp_sync_devices',
			'erp_sync_tokens',
			'erp_sync_ops',
			'erp_sync_conflicts',
			'erp_sync_id_map',
		);
		$columns = array(
			'customer' => array('client_uuid', 'row_version', 'updated_at'),
			'measurement' => array('client_uuid', 'row_version', 'updated_at'),
			'order_mst' => array('client_uuid', 'row_version', 'updated_at'),
		);
		$table_ok = array();
		foreach ($tables as $t)
		{
			$table_ok[$t] = $this->CI->db->table_exists($t);
		}
		$column_ok = array();
		$missing = array();
		foreach ($columns as $table => $cols)
		{
			$column_ok[$table] = array();
			foreach ($cols as $col)
			{
				$exists = $this->CI->db->table_exists($table) && $this->CI->db->field_exists($col, $table);
				$column_ok[$table][$col] = $exists;
				if ( ! $exists)
				{
					$missing[] = $table.'.'.$col;
				}
			}
		}
		$ready = empty($missing);
		foreach ($table_ok as $ok)
		{
			if ( ! $ok)
			{
				$ready = FALSE;
				break;
			}
		}
		return array(
			'ok' => TRUE,
			'ready' => $ready,
			'tables' => $table_ok,
			'columns' => $column_ok,
			'missing' => $missing,
			'server_time' => date('c'),
		);
	}

	public function bootstrap($limit_customers = 300)
	{
		$customers = $this->CI->erp_sync_model->recent_customers($limit_customers);
		$codes = array();
		foreach ($customers as $c)
		{
			$codes[] = (int) $c['code'];
		}
		$measurements = $this->CI->erp_sync_model->measurements_for_customers($codes, 500);
		$orders = $this->CI->erp_sync_model->open_orders(150);
		$masters = $this->_masters();

		return array(
			'ok' => TRUE,
			'server_time' => date('c'),
			'customers' => $this->_normalize_customers($customers),
			'measurements' => $this->_normalize_measurements($measurements),
			'orders' => $this->_normalize_orders($orders),
			'masters' => $masters,
			'capabilities' => $this->capabilities(),
		);
	}

	public function pull($since = NULL)
	{
		$customers = $this->CI->erp_sync_model->customers_since($since, 500);
		$codes = array();
		foreach ($customers as $c)
		{
			$codes[] = (int) $c['code'];
		}
		// Full snapshot when since is empty (first offline prepare).
		if ($since === NULL || $since === '')
		{
			return $this->bootstrap(1500);
		}
		$measurements = $this->CI->erp_sync_model->measurements_for_customers($codes, 500);
		return array(
			'ok' => TRUE,
			'server_time' => date('c'),
			'cursor' => date('Y-m-d H:i:s'),
			'customers' => $this->_normalize_customers($customers),
			'measurements' => $this->_normalize_measurements($measurements),
			'orders' => $this->_normalize_orders($this->CI->erp_sync_model->open_orders(100)),
			'masters' => $this->_masters(),
		);
	}

	public function capabilities()
	{
		return array(
			'fully_offline' => array(
				'customer.create', 'customer.update',
				'measurement.create', 'measurement.update',
				'order.create',
				'cash.create', 'cash.pending_pay',
				'delivery.create',
				'transfer.process', 'transfer.process_bulk',
			),
			'read_only_offline' => array(
				'customer.list', 'order.list', 'measurement.list', 'masters',
				'reports.cached', 'pdf.local_print',
			),
			'online_only' => array(
				'whatsapp', 'rbac', 'website.manage', 'configuration', 'notifications',
			),
		);
	}

	/**
	 * Apply a batch of ops. Each op: op_id, entity_type, entity_client_id, op_type, payload, idempotency_key.
	 */
	public function push_ops($device_id, $user_name, array $ops)
	{
		$results = array();
		$summary = array(
			'customers' => 0,
			'orders' => 0,
			'measurements' => 0,
			'cash' => 0,
			'deliveries' => 0,
			'transfers' => 0,
			'conflicts' => 0,
			'failed' => 0,
			'skipped' => 0,
		);

		foreach ($ops as $op)
		{
			$one = $this->apply_op($device_id, $user_name, $op);
			$results[] = $one;
			if ( ! empty($one['conflict']))
			{
				$summary['conflicts']++;
			}
			elseif ( ! empty($one['duplicate']))
			{
				$summary['skipped']++;
			}
			elseif ( ! empty($one['ok']))
			{
				$et = isset($op['entity_type']) ? $op['entity_type'] : '';
				if ($et === 'customer')
				{
					$summary['customers']++;
				}
				elseif ($et === 'order')
				{
					$summary['orders']++;
				}
				elseif ($et === 'measurement')
				{
					$summary['measurements']++;
				}
				elseif ($et === 'cash')
				{
					$summary['cash']++;
				}
				elseif ($et === 'delivery')
				{
					$summary['deliveries']++;
				}
				elseif ($et === 'transfer')
				{
					$summary['transfers']++;
				}
			}
			else
			{
				$summary['failed']++;
			}
		}

		return array(
			'ok' => TRUE,
			'results' => $results,
			'summary' => $summary,
			'server_time' => date('c'),
		);
	}

	public function apply_op($device_id, $user_name, array $op)
	{
		$idem = isset($op['idempotency_key']) ? trim((string) $op['idempotency_key']) : '';
		$op_id = isset($op['op_id']) ? trim((string) $op['op_id']) : '';
		$entity_type = isset($op['entity_type']) ? trim((string) $op['entity_type']) : '';
		$op_type = isset($op['op_type']) ? trim((string) $op['op_type']) : '';
		$entity_client_id = isset($op['entity_client_id']) ? trim((string) $op['entity_client_id']) : '';
		$payload = isset($op['payload']) && is_array($op['payload']) ? $op['payload'] : array();

		if ($idem === '' || $op_id === '' || $entity_type === '' || $op_type === '')
		{
			return array('ok' => FALSE, 'error' => 'invalid_op', 'op_id' => $op_id);
		}

		$existing = $this->CI->erp_sync_model->find_op($device_id, $idem);
		if ($existing)
		{
			// Failed ops must remain retriable. Older builds persisted them and the client
			// treated duplicate as success — that silently dropped unsynced work.
			if (isset($existing['status']) && $existing['status'] === 'failed')
			{
				$this->CI->erp_sync_model->delete_failed_op($device_id, $idem);
			}
			else
			{
				$decoded = json_decode((string) $existing['result_json'], TRUE);
				$out = array(
					'ok' => ($existing['status'] === 'applied'),
					'duplicate' => TRUE,
					'op_id' => $op_id,
					'idempotency_key' => $idem,
					'server_entity_id' => $existing['server_entity_id'],
					'result' => is_array($decoded) ? $decoded : array(),
				);
				if ($existing['status'] === 'conflict')
				{
					$out['conflict'] = TRUE;
					$out['error'] = 'conflict';
					if (is_array($decoded) && ! empty($decoded['conflict_fields']))
					{
						$out['conflict_fields'] = $decoded['conflict_fields'];
					}
				}
				return $out;
			}
		}

		$this->CI->db->trans_start();
		try
		{
			$result = $this->_dispatch($device_id, $user_name, $entity_type, $op_type, $entity_client_id, $payload);
			$status = ! empty($result['ok']) ? 'applied' : ( ! empty($result['conflict']) ? 'conflict' : 'failed');
			$server_id = isset($result['server_entity_id']) ? (string) $result['server_entity_id'] : '';
			// Persist only terminal outcomes. Transient failures stay out of erp_sync_ops
			// so the same idempotency key can retry cleanly after deps (id_map) resolve.
			if ($status === 'applied' || $status === 'conflict')
			{
				$this->CI->erp_sync_model->save_op(array(
					'device_id' => $device_id,
					'idempotency_key' => $idem,
					'op_id' => $op_id,
					'entity_type' => $entity_type,
					'entity_client_id' => $entity_client_id,
					'op_type' => $op_type,
					'request_json' => json_encode($op, JSON_UNESCAPED_UNICODE),
					'result_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
					'status' => $status,
					'server_entity_id' => $server_id,
					'error_message' => isset($result['error']) ? substr((string) $result['error'], 0, 500) : '',
					'created_at' => date('Y-m-d H:i:s'),
					'applied_at' => date('Y-m-d H:i:s'),
				));
			}
			$this->CI->db->trans_complete();
			if ($this->CI->db->trans_status() === FALSE)
			{
				return array('ok' => FALSE, 'error' => 'transaction_failed', 'op_id' => $op_id);
			}
			$result['op_id'] = $op_id;
			$result['idempotency_key'] = $idem;
			return $result;
		}
		catch (Exception $e)
		{
			$this->CI->db->trans_rollback();
			return array('ok' => FALSE, 'error' => $e->getMessage(), 'op_id' => $op_id);
		}
	}

	private function _dispatch($device_id, $user_name, $entity_type, $op_type, $entity_client_id, array $payload)
	{
		$key = $entity_type.'.'.$op_type;
		switch ($key)
		{
			case 'customer.create':
				return $this->_customer_create($device_id, $entity_client_id, $payload);
			case 'customer.update':
				return $this->_customer_update($device_id, $entity_client_id, $payload);
			case 'measurement.create':
				return $this->_measurement_create($device_id, $entity_client_id, $payload);
			case 'measurement.update':
				return $this->_measurement_update($device_id, $entity_client_id, $payload);
			case 'order.create':
				return $this->_order_create($device_id, $user_name, $entity_client_id, $payload);
			case 'cash.create':
				return $this->_cash_create($device_id, $entity_client_id, $payload);
			case 'cash.pending_pay':
				return $this->_cash_pending_pay($device_id, $entity_client_id, $payload);
			case 'delivery.create':
				return $this->_delivery_create($device_id, $user_name, $entity_client_id, $payload);
			case 'transfer.process':
				return $this->_transfer_process($device_id, $entity_client_id, $payload);
			case 'transfer.process_bulk':
				return $this->_transfer_process_bulk($device_id, $entity_client_id, $payload);
			default:
				return array('ok' => FALSE, 'error' => 'unsupported_op:'.$key);
		}
	}

	private function _customer_create($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid === '')
		{
			return array('ok' => FALSE, 'error' => 'missing_client_uuid');
		}
		$mapped = $this->CI->erp_sync_model->map_get('customer', $client_uuid);
		if ($mapped)
		{
			return array(
				'ok' => TRUE,
				'server_entity_id' => $mapped['server_id'],
				'duplicate' => TRUE,
			);
		}
		$by_uuid = $this->CI->erp_sync_model->find_customer_by_uuid($client_uuid);
		if ($by_uuid)
		{
			$this->CI->erp_sync_model->map_put('customer', $client_uuid, $by_uuid['code'], $device_id);
			return array('ok' => TRUE, 'server_entity_id' => (string) $by_uuid['code'], 'duplicate' => TRUE);
		}

		$man_napno = isset($payload['man_napno']) ? trim((string) $payload['man_napno']) : '';
		if ($man_napno !== '')
		{
			$clash = $this->CI->erp_sync_model->find_customer_by_man_napno($man_napno);
			if ($clash)
			{
				// Keep server number; assign a provisional unique man_napno suffix.
				$man_napno = $man_napno.'-OF'.substr(preg_replace('/[^A-Z0-9]/i', '', $client_uuid), 0, 6);
			}
		}

		$now = date('Y-m-d H:i:s');
		$row = array(
			'EDATE' => isset($payload['EDATE']) ? $payload['EDATE'] : date('Y-m-d'),
			'name' => isset($payload['name']) ? $payload['name'] : '',
			'city' => isset($payload['city']) ? $payload['city'] : '',
			'country' => isset($payload['country']) ? $payload['country'] : '',
			'Phone' => isset($payload['Phone']) ? $payload['Phone'] : (isset($payload['phone']) ? $payload['phone'] : ''),
			'Address' => isset($payload['Address']) ? $payload['Address'] : (isset($payload['address']) ? $payload['address'] : ''),
			'cus_type' => isset($payload['cus_type']) ? $payload['cus_type'] : (isset($payload['type']) ? $payload['type'] : ''),
			'cnic' => isset($payload['cnic']) ? $payload['cnic'] : '',
			'man_napno' => $man_napno,
			'remarks' => isset($payload['remarks']) ? $payload['remarks'] : '',
			'client_uuid' => $client_uuid,
			'row_version' => 1,
			'updated_at' => $now,
		);
		$this->CI->db->insert('customer', $row);
		$code = (int) $this->CI->db->insert_id();
		if ($code < 1)
		{
			return array('ok' => FALSE, 'error' => 'customer_insert_failed');
		}
		$this->CI->erp_sync_model->map_put('customer', $client_uuid, $code, $device_id);
		return array(
			'ok' => TRUE,
			'server_entity_id' => (string) $code,
			'man_napno' => $man_napno,
			'row_version' => 1,
		);
	}

	private function _customer_update($device_id, $client_uuid, array $payload)
	{
		$server_id = isset($payload['server_id']) ? (int) $payload['server_id'] : 0;
		if ($server_id < 1 && $client_uuid !== '')
		{
			$map = $this->CI->erp_sync_model->map_get('customer', $client_uuid);
			if ($map)
			{
				$server_id = (int) $map['server_id'];
			}
			else
			{
				$by_uuid = $this->CI->erp_sync_model->find_customer_by_uuid($client_uuid);
				if ($by_uuid)
				{
					$server_id = (int) $by_uuid['code'];
				}
			}
		}
		if ($server_id < 1)
		{
			return array('ok' => FALSE, 'error' => 'customer_not_found');
		}

		$current = $this->CI->erp_sync_model->find_customer_by_code($server_id);
		if ( ! $current)
		{
			return array('ok' => FALSE, 'error' => 'customer_not_found');
		}

		$base_version = isset($payload['base_row_version']) ? (int) $payload['base_row_version'] : 0;
		$server_version = isset($current['row_version']) ? (int) $current['row_version'] : 1;
		$incoming = $this->_customer_payload_fields($payload);
		$force_keep_local = ! empty($payload['force_keep_local']);

		// Missing base version is treated as stale when the server row already advanced —
		// otherwise concurrent edits silently last-write-wins.
		$conflict_fields = array();
		$should_check_conflict = ! $force_keep_local && (
			($base_version > 0 && $server_version > $base_version)
			|| ($base_version < 1 && $server_version > 1)
		);
		if ($should_check_conflict)
		{
			foreach ($this->customer_conflict_fields as $field)
			{
				if ( ! array_key_exists($field, $incoming))
				{
					continue;
				}
				$server_val = isset($current[$field]) ? (string) $current[$field] : '';
				$local_val = (string) $incoming[$field];
				if ($server_val !== $local_val)
				{
					$conflict_fields[] = $field;
				}
			}
		}

		if ( ! empty($conflict_fields))
		{
			$this->CI->erp_sync_model->save_conflict(array(
				'device_id' => $device_id,
				'entity_type' => 'customer',
				'entity_client_id' => $client_uuid,
				'server_entity_id' => (string) $server_id,
				'local_json' => json_encode($incoming, JSON_UNESCAPED_UNICODE),
				'server_json' => json_encode($current, JSON_UNESCAPED_UNICODE),
				'conflict_fields' => implode(',', $conflict_fields),
				'status' => 'open',
				'created_at' => date('Y-m-d H:i:s'),
			));
			return array(
				'ok' => FALSE,
				'conflict' => TRUE,
				'server_entity_id' => (string) $server_id,
				'conflict_fields' => $conflict_fields,
				'server_snapshot' => $current,
				'error' => 'conflict',
			);
		}

		// Auto-merge: apply incoming fields; bump version.
		$update = $incoming;
		$update['row_version'] = $server_version + 1;
		$update['updated_at'] = date('Y-m-d H:i:s');
		if ($client_uuid !== '' && empty($current['client_uuid']))
		{
			$update['client_uuid'] = $client_uuid;
		}
		$this->CI->db->where('code', $server_id)->update('customer', $update);
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('customer', $client_uuid, $server_id, $device_id);
		}
		return array(
			'ok' => TRUE,
			'server_entity_id' => (string) $server_id,
			'row_version' => $server_version + 1,
		);
	}

	/**
	 * Build measurement row from offline form payload (same fields as Measurement::process).
	 */
	private function _measurement_row_from_payload(array $payload, $customer_code)
	{
		$pick = function ($keys, $default = '') use ($payload) {
			foreach ((array) $keys as $k)
			{
				if (isset($payload[$k]) && $payload[$k] !== NULL && $payload[$k] !== '')
				{
					return is_string($payload[$k]) ? trim($payload[$k]) : $payload[$k];
				}
			}
			return $default;
		};

		$join_triple = function ($prefix) use ($payload, $pick) {
			$top = $pick(array($prefix.'_upar'));
			$mid = $pick(array($prefix.'_darmiyan'));
			$bot = $pick(array($prefix.'_neeche'));
			if ($top === '' && $mid === '' && $bot === '')
			{
				return $pick(array($prefix));
			}
			if (function_exists('measurement_join_triple'))
			{
				return measurement_join_triple($top, $mid, $bot);
			}
			$parts = array_filter(array($top, $mid, $bot), function ($v) {
				return $v !== '' && $v !== NULL;
			});
			return implode('/', $parts);
		};

		$row = array(
			'EDATE' => date('Y-m-d'),
			'customer_code' => (int) $customer_code,
			'KL' => $pick(array('KL')),
			'FPL' => $pick(array('FPL')),
			'BL' => $pick(array('BL')),
			'Teera' => $pick(array('Teera')),
			'BP' => $pick(array('BP')),
			'CL' => $pick(array('CL')),
			'Chaati' => $pick(array('Chaati')),
			'Gala' => $pick(array('Gala')),
			'Poncha_Chorai' => $pick(array('Poncha_Chorai')),
			'Poncha_Choraic' => $pick(array('Poncha_Choraic')),
			'Shalwar_Lenght' => $pick(array('Shalwar_Lenght')),
			'Asan' => $pick(array('Asan')),
			'Side_Pocket_Size' => $pick(array('Side_Pocket_Size')),
			'SC' => $pick(array('SC')),
			'BG' => $pick(array('BG')),
			'Lambai' => $pick(array('Lambai')),
			'Poncha' => $pick(array('Poncha')),
			'shalwar_Asan' => $pick(array('shalwar_Asan')),
			'Peeir' => $pick(array('Peeir')),
			'PLambai' => $pick(array('PLambai')),
			'PPoncha' => $pick(array('PPoncha')),
			'PAsan' => $pick(array('PAsan')),
			'PPeeir' => $pick(array('PPeeir')),
			'PentLambai' => $pick(array('PentLambai')),
			'pentWaiste' => $pick(array('pentWaiste')),
			'HIP' => $pick(array('HIP')),
			'PentPat' => $pick(array('PentPat')),
			'PentThai' => $pick(array('PentThai', 'PentPat')),
			'PentGhutna' => $pick(array('PentGhutna')),
			'PentPonchaMori' => $pick(array('PentPonchaMori')),
			'PentPoncha' => $pick(array('PentPoncha', 'PentPonchaMori')),
			'SLambai' => $pick(array('SLambai')),
			'SBazoo' => $pick(array('SBazoo')),
			'STeera' => $pick(array('STeera')),
			'SGeera' => $join_triple('SGeera'),
			'geera' => $join_triple('geera'),
			'SChaati' => $pick(array('SChaati')),
			'SKamar' => $pick(array('SKamar')),
			'SGaala' => $pick(array('SGaala')),
			'CoatLambai' => $pick(array('CoatLambai')),
			'CoatBazoo' => $pick(array('CoatBazoo')),
			'CoatTeera' => $pick(array('CoatTeera')),
			'CoatChaati' => $pick(array('CoatChaati')),
			'CoatHIP' => $pick(array('CoatHIP')),
			'CoatKamar' => $pick(array('CoatKamar')),
			'CoatDola' => $pick(array('CoatDola')),
			'CoatBazooMori' => $pick(array('CoatBazooMori')),
			'CoatLambaiBack' => $pick(array('CoatLambaiBack')),
			'crossback' => $pick(array('crossback')),
			'PCCoatLambai' => $pick(array('PCCoatLambai')),
			'PCCoatChaati' => $pick(array('PCCoatChaati')),
			'PCCoatKamar' => $pick(array('PCCoatKamar')),
			'PCCoatTeera' => $pick(array('PCCoatTeera')),
			'PCCoatBazoo' => $pick(array('PCCoatBazoo')),
			'PCCoatGaala' => $pick(array('PCCoatGaala')),
			'PCCoatHIP' => $pick(array('PCCoatHIP')),
			'PCCoatDola' => $pick(array('PCCoatDola')),
			'PCCoatBazooMori' => $pick(array('PCCoatBazooMori')),
			'PCCoatLambaiBack' => $pick(array('PCCoatLambaiBack')),
			'PCCrossback' => $pick(array('PCCrossback')),
			'PCPentLambai' => $pick(array('PCPentLambai')),
			'PCpentWaiste' => $pick(array('PCpentWaiste')),
			'PCHIP' => $pick(array('PCHIP')),
			'PCPentPat' => $pick(array('PCPentPat')),
			'PCPentThai' => $pick(array('PCPentThai', 'PCPentPat')),
			'PCPentGhutna' => $pick(array('PCPentGhutna')),
			'PCPentPonchaMori' => $pick(array('PCPentPonchaMori')),
			'PCPentPoncha' => $pick(array('PCPentPoncha', 'PCPentPonchaMori')),
			'wastLambai' => $pick(array('wastLambai')),
			'wastTeera' => $pick(array('wastTeera')),
			'wastChaati' => $pick(array('wastChaati')),
			'wastHIP' => $pick(array('wastHIP')),
			'wastKamar' => $pick(array('wastKamar')),
			'wastGaala' => $pick(array('wastGaala')),
			'remarks' => $pick(array('RemarksKurta', 'Remarks', 'remarks')),
			'remarks_kurta' => $pick(array('RemarksKurta', 'Remarks', 'remarks_kurta')),
			'remarks_pajama' => $pick(array('RemarksPajama', 'remarks_pajama')),
			'remarks_pent' => $pick(array('RemarksPent', 'remarks_pent')),
			'remarks_shirt' => $pick(array('RemarksShirt', 'remarks_shirt')),
			'remarks_coat' => $pick(array('RemarksCoat', 'remarks_coat')),
			'remarks_pentcoat' => $pick(array('RemarksPentcoat', 'remarks_pentcoat')),
			'remarks_waistcoat' => $pick(array('RemarksWaistcoat', 'remarks_waistcoat')),
			'pait' => $pick(array('pait')),
			'WASIT' => $pick(array('WASIT')),
			'FRONT_Pocket_Size' => $pick(array('FRONT_Pocket_Size')),
			'CAKLAMBAI' => $pick(array('CAKLAMBAI')),
			'KAAFCURAI' => $pick(array('KAAFCURAI')),
			'MODA_CURAI' => $pick(array('MODA_CURAI')),
			'CoatGaala' => $pick(array('CoatGaala')),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		// Drop columns that do not exist on this DB.
		if ($this->CI->db->table_exists('measurement'))
		{
			$fields = $this->CI->db->list_fields('measurement');
			$field_map = array_fill_keys($fields, TRUE);
			foreach (array_keys($row) as $col)
			{
				if ( ! isset($field_map[$col]))
				{
					unset($row[$col]);
				}
			}
		}

		return $row;
	}

	private function _resolve_customer_code_for_measurement(array $payload)
	{
		$code = isset($payload['customer_code']) ? (int) $payload['customer_code'] : 0;
		if ($code < 1)
		{
			$code = isset($payload['customerno1']) ? (int) $payload['customerno1'] : 0;
		}
		if ($code < 1 && ! empty($payload['customer_client_id']))
		{
			$map = $this->CI->erp_sync_model->map_get('customer', $payload['customer_client_id']);
			if ($map)
			{
				$code = (int) $map['server_id'];
			}
		}
		return $code;
	}

	private function _measurement_create($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid === '')
		{
			return array('ok' => FALSE, 'error' => 'missing_client_uuid');
		}
		$mapped = $this->CI->erp_sync_model->map_get('measurement', $client_uuid);
		if ($mapped)
		{
			return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
		}

		$by_uuid = $this->CI->db->where('client_uuid', $client_uuid)->limit(1)->get('measurement')->row_array();
		if ($by_uuid)
		{
			$this->CI->erp_sync_model->map_put('measurement', $client_uuid, $by_uuid['code'], $device_id);
			return array('ok' => TRUE, 'server_entity_id' => (string) $by_uuid['code'], 'duplicate' => TRUE);
		}

		$customer_code = $this->_resolve_customer_code_for_measurement($payload);
		if ($customer_code < 1)
		{
			return array('ok' => FALSE, 'error' => 'customer_required');
		}

		// Always insert a new measurement for create. Overwriting the customer's latest
		// measurement caused silent data loss when an offline "create" synced later.
		$row = $this->_measurement_row_from_payload($payload, $customer_code);
		if ($this->CI->db->field_exists('client_uuid', 'measurement'))
		{
			$row['client_uuid'] = $client_uuid;
		}
		if ($this->CI->db->field_exists('row_version', 'measurement'))
		{
			$row['row_version'] = 1;
		}

		$this->CI->db->insert('measurement', $row);
		$code = (int) $this->CI->db->insert_id();
		if ($code < 1)
		{
			return array('ok' => FALSE, 'error' => 'measurement_insert_failed');
		}
		$this->CI->erp_sync_model->map_put('measurement', $client_uuid, $code, $device_id);
		return array('ok' => TRUE, 'server_entity_id' => (string) $code);
	}

	private function _measurement_update($device_id, $client_uuid, array $payload)
	{
		$server_id = isset($payload['server_id']) ? (int) $payload['server_id'] : (isset($payload['code']) ? (int) $payload['code'] : 0);
		if ($server_id < 1 && $client_uuid !== '')
		{
			$map = $this->CI->erp_sync_model->map_get('measurement', $client_uuid);
			if ($map)
			{
				$server_id = (int) $map['server_id'];
			}
		}
		if ($server_id < 1)
		{
			return array('ok' => FALSE, 'error' => 'measurement_id_required');
		}

		$customer_code = $this->_resolve_customer_code_for_measurement($payload);
		if ($customer_code < 1)
		{
			$current = $this->CI->db->where('code', $server_id)->limit(1)->get('measurement')->row_array();
			$customer_code = $current ? (int) $current['customer_code'] : 0;
		}
		if ($customer_code < 1)
		{
			return array('ok' => FALSE, 'error' => 'customer_required');
		}

		$current = $this->CI->db->where('code', $server_id)->limit(1)->get('measurement')->row_array();
		if ( ! $current)
		{
			return array('ok' => FALSE, 'error' => 'measurement_not_found');
		}

		$base_version = isset($payload['base_row_version']) ? (int) $payload['base_row_version'] : 0;
		$server_version = isset($current['row_version']) ? (int) $current['row_version'] : 1;
		$force_keep_local = ! empty($payload['force_keep_local']);
		if ( ! $force_keep_local && (($base_version > 0 && $server_version > $base_version) || ($base_version < 1 && $server_version > 1)))
		{
			$this->CI->erp_sync_model->save_conflict(array(
				'device_id' => $device_id,
				'entity_type' => 'measurement',
				'entity_client_id' => $client_uuid,
				'server_entity_id' => (string) $server_id,
				'local_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
				'server_json' => json_encode($current, JSON_UNESCAPED_UNICODE),
				'conflict_fields' => 'row_version',
				'status' => 'open',
				'created_at' => date('Y-m-d H:i:s'),
			));
			return array(
				'ok' => FALSE,
				'conflict' => TRUE,
				'server_entity_id' => (string) $server_id,
				'conflict_fields' => array('row_version'),
				'server_snapshot' => $current,
				'error' => 'conflict',
			);
		}

		$row = $this->_measurement_row_from_payload($payload, $customer_code);
		if ($client_uuid !== '' && $this->CI->db->field_exists('client_uuid', 'measurement'))
		{
			$row['client_uuid'] = $client_uuid;
		}
		if ($this->CI->db->field_exists('row_version', 'measurement'))
		{
			$row['row_version'] = $server_version + 1;
		}
		$this->CI->db->where('code', $server_id)->update('measurement', $row);
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('measurement', $client_uuid, $server_id, $device_id);
		}
		return array('ok' => TRUE, 'server_entity_id' => (string) $server_id, 'row_version' => $server_version + 1);
	}

	/**
	 * Offline order create: stores a pending structural order.
	 * Full line reconstruction uses the same tables as Order::process where payload allows.
	 */
	private function _order_create($device_id, $user_name, $client_uuid, array $payload)
	{
		if ($client_uuid === '')
		{
			return array('ok' => FALSE, 'error' => 'missing_client_uuid');
		}
		$mapped = $this->CI->erp_sync_model->map_get('order', $client_uuid);
		if ($mapped)
		{
			return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
		}

		$existing = $this->CI->db->where('client_uuid', $client_uuid)->limit(1)->get('order_mst')->row_array();
		if ($existing)
		{
			$this->CI->erp_sync_model->map_put('order', $client_uuid, $existing['trnno'], $device_id);
			return array('ok' => TRUE, 'server_entity_id' => (string) $existing['trnno'], 'duplicate' => TRUE);
		}

		$naap_no = isset($payload['naap_no']) ? (int) $payload['naap_no'] : (isset($payload['Naapno']) ? (int) $payload['Naapno'] : 0);
		if ($naap_no < 1 && ! empty($payload['measurement_client_id']))
		{
			$map = $this->CI->erp_sync_model->map_get('measurement', $payload['measurement_client_id']);
			if ($map)
			{
				$naap_no = (int) $map['server_id'];
			}
		}
		if ($naap_no < 1)
		{
			return array('ok' => FALSE, 'error' => 'measurement_required');
		}

		$lines = isset($payload['lines']) && is_array($payload['lines']) ? $payload['lines'] : array();
		if (empty($lines) && isset($payload['desigtype']) && is_array($payload['desigtype']))
		{
			$lines = $this->_lines_from_legacy_form($payload);
		}
		elseif (empty($lines) && isset($payload['desigtype']) && ! is_array($payload['desigtype']))
		{
			// Single-value form encoding edge case
			$payload['desigtype'] = array($payload['desigtype']);
			if (isset($payload['suit']) && ! is_array($payload['suit']))
			{
				$payload['suit'] = array($payload['suit']);
			}
			$lines = $this->_lines_from_legacy_form($payload);
		}
		if (empty($lines))
		{
			return array('ok' => FALSE, 'error' => 'order_lines_required');
		}

		$total = isset($payload['total_amount']) ? (float) $payload['total_amount'] : (isset($payload['total']) ? (float) $payload['total'] : 0);
		$advance = isset($payload['Advance']) ? (float) $payload['Advance'] : (isset($payload['advance']) ? (float) $payload['advance'] : 0);
		$discount = isset($payload['Discount']) ? (float) $payload['Discount'] : 0;
		$rdate = isset($payload['retrun_Date']) ? date('Y-m-d', strtotime($payload['retrun_Date'])) : date('Y-m-d');
		$staff_empno = isset($payload['staff_employee']) ? (int) $payload['staff_employee'] : 0;
		$qty = isset($payload['Quantity']) ? $payload['Quantity'] : count($lines);

		$now = date('Y-m-d H:i:s');
		$mst = array(
			'EDATE' => date('Y-m-d'),
			'naap_no' => $naap_no,
			'RDATE' => $rdate,
			'staff_empno' => $staff_empno,
			'qty' => $qty,
			'total' => $total,
			'advance' => $advance,
			'Discount' => $discount,
			'addprice' => isset($payload['addprice']) ? $payload['addprice'] : 0,
			'image' => isset($payload['iamge']) ? $payload['iamge'] : (isset($payload['image']) ? $payload['image'] : ''),
			'client_uuid' => $client_uuid,
			'row_version' => 1,
			'updated_at' => $now,
		);

		$fields = $this->CI->db->list_fields('order_mst');
		$mst_safe = array();
		foreach ($mst as $k => $v)
		{
			if (in_array($k, $fields, TRUE))
			{
				$mst_safe[$k] = $v;
			}
		}

		$this->CI->db->insert('order_mst', $mst_safe);
		$trnno = (int) $this->CI->db->insert_id();
		if ($trnno < 1)
		{
			return array('ok' => FALSE, 'error' => 'order_insert_failed');
		}

		$dtl_fields = $this->CI->db->list_fields('order_dtl');
		$transfer_fields = $this->CI->db->table_exists('transfer_mst') ? $this->CI->db->list_fields('transfer_mst') : array();
		$i = 0;
		foreach ($lines as $line)
		{
			$row = array(
				'order_trnno' => $trnno,
				'design' => 0,
				'type' => isset($line['type']) ? $line['type'] : '',
				'kolor_style' => isset($payload['kolorstyle'.$i]) ? $payload['kolorstyle'.$i] : (isset($line['kolor_style']) ? $line['kolor_style'] : ''),
				'ENO' => 0,
				'SNO' => isset($line['suit']) ? (int) $line['suit'] : 0,
				'suitrate' => isset($payload['ssrate'][$i]) ? $payload['ssrate'][$i] : (isset($line['rate']) ? $line['rate'] : 0),
				'drate' => 0,
				'erate' => 0,
				'addprice' => isset($payload['add'][$i]) ? $payload['add'][$i] : 0,
				'spocket' => isset($payload['spocket'][$i]) ? $payload['spocket'][$i] : '',
				'fpocket' => isset($payload['fpocket'][$i]) ? $payload['fpocket'][$i] : '',
				'Shawlarpocket' => isset($payload['shawpocket'][$i]) ? $payload['shawpocket'][$i] : '',
				'order_remarks' => isset($payload['Remarks'][$i]) ? $payload['Remarks'][$i] : (isset($line['remarks']) ? $line['remarks'] : ''),
			);
			$safe = array();
			foreach ($row as $k => $v)
			{
				if (in_array($k, $dtl_fields, TRUE))
				{
					$safe[$k] = $v;
				}
			}
			$detail_sr = 0;
			if ( ! empty($safe))
			{
				$this->CI->db->insert('order_dtl', $safe);
				$detail_sr = (int) $this->CI->db->insert_id();
			}
			if ($detail_sr > 0 && ! empty($transfer_fields))
			{
				$tr = array(
					'EDATE' => date('Y-m-d'),
					'order_id' => $trnno,
					'sr' => $detail_sr,
					'qty' => 1,
					'dept' => 'HO',
					'status' => 'Active',
				);
				$tr_safe = array();
				foreach ($tr as $k => $v)
				{
					if (in_array($k, $transfer_fields, TRUE))
					{
						$tr_safe[$k] = $v;
					}
				}
				if ( ! empty($tr_safe))
				{
					$this->CI->db->insert('transfer_mst', $tr_safe);
				}
			}
			$i++;
		}

		$this->CI->erp_sync_model->map_put('order', $client_uuid, $trnno, $device_id);
		return array(
			'ok' => TRUE,
			'server_entity_id' => (string) $trnno,
			'row_version' => 1,
		);
	}

	private function _lines_from_legacy_form(array $payload)
	{
		$types = $payload['desigtype'];
		$lines = array();
		$count = count($types);
		for ($i = 0; $i < $count; $i++)
		{
			$lines[] = array(
				'type' => isset($payload['desigtype'][$i]) ? $payload['desigtype'][$i] : '',
				'suit' => isset($payload['suit'][$i]) ? $payload['suit'][$i] : 0,
				'qty' => isset($payload['Quantity'][$i]) ? $payload['Quantity'][$i] : (isset($payload['qty'][$i]) ? $payload['qty'][$i] : 1),
				'rate' => isset($payload['rate'][$i]) ? $payload['rate'][$i] : 0,
				'itotal' => isset($payload['itotal'][$i]) ? $payload['itotal'][$i] : (isset($payload['itotal1'][$i]) ? $payload['itotal1'][$i] : 0),
			);
		}
		return $lines;
	}

	private function _cash_create($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid !== '')
		{
			$mapped = $this->CI->erp_sync_model->map_get('cash', $client_uuid);
			if ($mapped)
			{
				return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
			}
		}

		$type = isset($payload['type']) ? (string) $payload['type'] : '';
		$amount = isset($payload['paid']) ? (float) $payload['paid'] : (isset($payload['amount']) ? (float) $payload['amount'] : 0);
		if ($type === '' || abs($amount) < 0.0001)
		{
			return array('ok' => FALSE, 'error' => 'cash_invalid');
		}

		$row = array(
			'EDATE' => isset($payload['EDATE']) ? date('Y-m-d', strtotime($payload['EDATE'])) : date('Y-m-d'),
			'type' => $type,
			'emp_id' => isset($payload['Employee']) ? $payload['Employee'] : (isset($payload['emp_id']) ? $payload['emp_id'] : ''),
			'customer_id' => isset($payload['Customer']) ? $payload['Customer'] : (isset($payload['customer_id']) ? $payload['customer_id'] : ''),
			'amount' => $amount,
			'narration' => isset($payload['Remarks']) ? $payload['Remarks'] : (isset($payload['narration']) ? $payload['narration'] : ''),
			'order_no' => isset($payload['order_number']) ? $payload['order_number'] : (isset($payload['order_no']) ? $payload['order_no'] : ''),
		);

		$fields = $this->CI->db->list_fields('cash');
		$safe = array();
		foreach ($row as $k => $v)
		{
			if (in_array($k, $fields, TRUE))
			{
				$safe[$k] = $v;
			}
		}
		if (empty($safe))
		{
			return array('ok' => FALSE, 'error' => 'cash_schema');
		}

		$this->CI->db->insert('cash', $safe);
		$id = (int) $this->CI->db->insert_id();
		if ($id < 1)
		{
			return array('ok' => FALSE, 'error' => 'cash_insert_failed');
		}
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('cash', $client_uuid, $id, $device_id);
		}
		if (isset($this->CI->cash_ledger) && is_object($this->CI->cash_ledger))
		{
			$this->CI->cash_ledger->invalidate_cash_ledger_opening_cache($safe['EDATE']);
		}
		else
		{
			$this->CI->load->model('Cash_ledger_model', 'cash_ledger');
			$this->CI->cash_ledger->invalidate_cash_ledger_opening_cache($safe['EDATE']);
		}
		return array('ok' => TRUE, 'server_entity_id' => (string) $id);
	}

	private function _cash_pending_pay($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid !== '')
		{
			$mapped = $this->CI->erp_sync_model->map_get('cash_pending', $client_uuid);
			if ($mapped)
			{
				return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
			}
		}

		$dmst_trnno = isset($payload['dmst_trnno']) ? (int) $payload['dmst_trnno'] : 0;
		$pay_amount = isset($payload['pay_amount']) ? round((float) $payload['pay_amount'], 2) : 0;
		$discount_amount = isset($payload['discount_amount']) ? round((float) $payload['discount_amount'], 2) : 0;
		$customer_id = isset($payload['customer_id']) ? trim((string) $payload['customer_id']) : '';

		if ($dmst_trnno < 1 || ($pay_amount + $discount_amount) <= 0)
		{
			return array('ok' => FALSE, 'error' => 'pending_pay_invalid');
		}

		$this->CI->load->model('Cash_ledger_model', 'cash_ledger');
		$dmst_row = $this->CI->cash_ledger->get_dmst_pending_row($dmst_trnno);
		if ($dmst_row === NULL)
		{
			return array('ok' => FALSE, 'error' => 'pending_not_found', 'conflict' => TRUE);
		}

		$balance = round((float) $dmst_row->balance, 2);
		if ($pay_amount + $discount_amount - $balance > 0.009)
		{
			return array(
				'ok' => FALSE,
				'conflict' => TRUE,
				'error' => 'pending_amount_changed',
				'server_balance' => $balance,
			);
		}
		if ($customer_id === '')
		{
			$customer_id = (string) $dmst_row->customer_id;
		}

		$remaining = round($balance - $pay_amount - $discount_amount, 2);
		$is_settled = ($remaining <= 0.009);
		$paid_today = date('Y-m-d');

		$dmst_update = array('balance' => $is_settled ? 0 : $remaining);
		if ($is_settled)
		{
			$dmst_update['paid_Date'] = $paid_today;
			$dmst_update['paidstatus'] = 'YES';
		}
		$this->CI->db->where('trnno', $dmst_trnno);
		$this->CI->db->update('dmst', $dmst_update);

		$cash_trnno = 0;
		if ($pay_amount > 0)
		{
			$narration = 'Received From Customer (offline sync)';
			if ($discount_amount > 0)
			{
				$narration .= ' (Discount: '.number_format($discount_amount, 2).')';
			}
			if ( ! $is_settled)
			{
				$narration .= ' (Partial, Balance: '.number_format($remaining, 2).')';
			}
			$cash_data = array(
				'EDATE' => $paid_today,
				'type' => '2',
				'customer_id' => $customer_id,
				'amount' => $pay_amount,
				'narration' => $narration,
				'order_no' => (string) $dmst_trnno,
			);
			$this->CI->db->insert('cash', $cash_data);
			$cash_trnno = (int) $this->CI->db->insert_id();
			if ($cash_trnno > 0)
			{
				$this->CI->cash_ledger->invalidate_cash_ledger_opening_cache($paid_today);
			}
		}

		if ($cash_trnno > 0 && $this->CI->cash_ledger->dmst_has_paid_cash_trnno_column())
		{
			$this->CI->db->where('trnno', $dmst_trnno);
			$this->CI->db->update('dmst', array('paid_cash_trnno' => $cash_trnno));
		}

		$server_id = $cash_trnno > 0 ? (string) $cash_trnno : ('dmst:'.$dmst_trnno);
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('cash_pending', $client_uuid, $server_id, $device_id);
		}
		return array('ok' => TRUE, 'server_entity_id' => $server_id, 'remaining' => $remaining);
	}

	private function _delivery_create($device_id, $user_name, $client_uuid, array $payload)
	{
		if ($client_uuid !== '')
		{
			$mapped = $this->CI->erp_sync_model->map_get('delivery', $client_uuid);
			if ($mapped)
			{
				return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
			}
		}

		$order_id = isset($payload['order_id']) ? (int) $payload['order_id'] : 0;
		$customer_id = isset($payload['customer_code']) ? (int) $payload['customer_code'] : (isset($payload['customer_id']) ? (int) $payload['customer_id'] : 0);
		$paid = isset($payload['Balance']) ? (float) $payload['Balance'] : (isset($payload['PAID']) ? (float) $payload['PAID'] : 0);
		$total = isset($payload['total_Amount']) ? (float) $payload['total_Amount'] : 0;
		$advance = isset($payload['Advance']) ? (float) $payload['Advance'] : 0;
		$discount = isset($payload['Discount']) ? (float) $payload['Discount'] : 0;
		$bal = $total - ($advance + $discount + $paid);
		$order_sr_list = isset($payload['order_sr']) ? $payload['order_sr'] : array();
		$returndel_list = isset($payload['returndel']) ? $payload['returndel'] : array();
		if ( ! is_array($order_sr_list))
		{
			$order_sr_list = $order_sr_list === '' || $order_sr_list === NULL ? array() : array($order_sr_list);
		}
		if ( ! is_array($returndel_list))
		{
			$returndel_list = $returndel_list === '' || $returndel_list === NULL ? array() : array($returndel_list);
		}
		if ($order_id < 1 || $customer_id < 1)
		{
			return array('ok' => FALSE, 'error' => 'delivery_invalid');
		}

		// Reject re-delivery of lines already recorded in ddtl (prevents duplicate cash + dmst).
		$already_delivered = array();
		$count_check = count($order_sr_list);
		for ($ci = 0; $ci < $count_check; $ci++)
		{
			$ret_check = isset($returndel_list[$ci]) ? strtolower(trim((string) $returndel_list[$ci])) : '';
			if ($ret_check !== 'yes')
			{
				continue;
			}
			$sr_check = (int) $order_sr_list[$ci];
			if ($sr_check < 1)
			{
				continue;
			}
			$prior = $this->CI->db->where('ORDER_TRNNO', $order_id)
				->where('ORDERSR', $sr_check)
				->limit(1)
				->get('ddtl')
				->row_array();
			if ($prior)
			{
				$already_delivered[] = $sr_check;
			}
		}
		if ( ! empty($already_delivered))
		{
			$this->CI->erp_sync_model->save_conflict(array(
				'device_id' => $device_id,
				'entity_type' => 'delivery',
				'entity_client_id' => $client_uuid,
				'server_entity_id' => (string) $order_id,
				'local_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
				'server_json' => json_encode(array('already_delivered_sr' => $already_delivered), JSON_UNESCAPED_UNICODE),
				'conflict_fields' => 'already_delivered',
				'status' => 'open',
				'created_at' => date('Y-m-d H:i:s'),
			));
			return array(
				'ok' => FALSE,
				'conflict' => TRUE,
				'server_entity_id' => (string) $order_id,
				'conflict_fields' => array('already_delivered'),
				'error' => 'already_delivered',
			);
		}

		$dmst = array(
			'EDATE' => date('Y-m-d'),
			'order_id' => $order_id,
			'PAID' => $paid,
			'NPAID_dATE' => isset($payload['paid_Date']) ? $payload['paid_Date'] : date('Y-m-d'),
			'customer_id' => $customer_id,
			'balance' => $bal,
			'pending' => isset($payload['udaar']) ? $payload['udaar'] : '',
		);
		$this->CI->db->insert('dmst', $dmst);
		$last_dmst = (int) $this->CI->db->insert_id();
		if ($last_dmst < 1)
		{
			return array('ok' => FALSE, 'error' => 'delivery_dmst_failed');
		}

		$narration = 'Recevied Agaisnt Order '.$order_id.' (offline sync)';
		$cash = array(
			'EDATE' => date('Y-m-d'),
			'type' => '2',
			'customer_id' => $customer_id,
			'order_no' => $order_id,
			'amount' => $paid,
			'narration' => $narration,
		);
		$this->CI->db->insert('cash', $cash);

		$delivered_any = FALSE;
		$count = count($order_sr_list);
		for ($i = 0; $i < $count; $i++)
		{
			$ret = isset($returndel_list[$i]) ? strtolower(trim((string) $returndel_list[$i])) : '';
			if ($ret !== 'yes')
			{
				continue;
			}
			$delivered_any = TRUE;
			$sr = (int) $order_sr_list[$i];
			$this->CI->db->insert('ddtl', array(
				'DTRNNO' => $last_dmst,
				'ORDER_TRNNO' => $order_id,
				'ORDERSR' => $sr,
				'ord_dtl_date' => date('Y-m-d'),
			));
			$this->CI->db->where('order_id', $order_id);
			$this->CI->db->where('sr', $sr);
			$this->CI->db->where('status', 'Active');
			$this->CI->db->update('transfer_mst', array('status' => 'DELIVER'));
		}

		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('delivery', $client_uuid, $last_dmst, $device_id);
		}

		return array(
			'ok' => TRUE,
			'server_entity_id' => (string) $last_dmst,
			'delivered_any' => $delivered_any,
			'user_name' => $user_name,
		);
	}

	private function _transfer_next_dept($from_dept)
	{
		if ($from_dept === 'HO')
		{
			return 'CUT';
		}
		if ($from_dept === 'CUT')
		{
			return 'HO2';
		}
		if ($from_dept === 'HO2')
		{
			return 'STIT';
		}
		return '';
	}

	private function _transfer_process($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid !== '')
		{
			$mapped = $this->CI->erp_sync_model->map_get('transfer', $client_uuid);
			if ($mapped)
			{
				return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
			}
		}

		$transfer_trnno = isset($payload['transfer_trnno']) ? (int) $payload['transfer_trnno'] : (isset($payload['trnno']) ? (int) $payload['trnno'] : 0);
		$from_dept = isset($payload['dept']) ? trim((string) $payload['dept']) : '';
		$order_sr = isset($payload['sr']) ? (int) $payload['sr'] : 0;
		$order_id = isset($payload['order_id']) ? (int) $payload['order_id'] : 0;
		$employee = isset($payload['EMPLOYEE']) ? trim((string) $payload['EMPLOYEE']) : (isset($payload['bulk_employee']) ? trim((string) $payload['bulk_employee']) : '');
		$rate = isset($payload['Rate']) ? trim((string) $payload['Rate']) : (isset($payload['bulk_rate']) ? trim((string) $payload['bulk_rate']) : '');
		$rdate = isset($payload['RDATE']) ? $payload['RDATE'] : date('Y-m-d');

		$result = $this->_transfer_apply_one($transfer_trnno, $from_dept, $order_sr, $order_id, $employee, $rate, $rdate);
		if (empty($result['ok']))
		{
			return $result;
		}
		$server_id = $transfer_trnno.'-'.$from_dept.'-'.$order_sr.'-'.(isset($result['dept1']) ? $result['dept1'] : 'STIT');
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('transfer', $client_uuid, $server_id, $device_id);
		}
		$result['server_entity_id'] = $server_id;
		return $result;
	}

	private function _transfer_process_bulk($device_id, $client_uuid, array $payload)
	{
		if ($client_uuid !== '')
		{
			$mapped = $this->CI->erp_sync_model->map_get('transfer_bulk', $client_uuid);
			if ($mapped)
			{
				return array('ok' => TRUE, 'server_entity_id' => $mapped['server_id'], 'duplicate' => TRUE);
			}
		}

		$dept = isset($payload['dept']) ? trim((string) $payload['dept']) : '';
		$employee = isset($payload['bulk_employee']) ? trim((string) $payload['bulk_employee']) : '';
		$rate = isset($payload['bulk_rate']) ? trim((string) $payload['bulk_rate']) : '';
		$rdate = date('Y-m-d');
		$items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : array();
		if (empty($items) && isset($payload['transfer_items']))
		{
			$raw = $payload['transfer_items'];
			if (is_string($raw))
			{
				$decoded = json_decode($raw, TRUE);
				$items = is_array($decoded) ? $decoded : array();
			}
			elseif (is_array($raw))
			{
				$items = $raw;
			}
		}

		$done = 0;
		$failed = 0;
		foreach ($items as $item)
		{
			if ( ! is_array($item))
			{
				continue;
			}
			$trnno = isset($item['trnno']) ? (int) $item['trnno'] : 0;
			$order = isset($item['order']) ? (int) $item['order'] : 0;
			$sr = isset($item['sr']) ? (int) $item['sr'] : 0;
			$one = $this->_transfer_apply_one($trnno, $dept, $sr, $order, $employee, $rate, $rdate);
			if ( ! empty($one['ok']))
			{
				$done++;
			}
			else
			{
				$failed++;
			}
		}

		$server_id = 'bulk:'.$dept.':'.$done.':'.substr(md5($client_uuid), 0, 8);
		if ($client_uuid !== '')
		{
			$this->CI->erp_sync_model->map_put('transfer_bulk', $client_uuid, $server_id, $device_id);
		}
		return array(
			'ok' => $done > 0,
			'server_entity_id' => $server_id,
			'done' => $done,
			'failed' => $failed,
			'error' => $done < 1 ? 'transfer_bulk_none' : '',
		);
	}

	private function _transfer_apply_one($transfer_trnno, $from_dept, $order_sr, $order_id, $employee_account, $rate, $rdate)
	{
		$this->CI->load->model('insert');
		$this->CI->load->model('Employee_query_model', 'employee_query');
		$this->CI->load->model('Master_data_model', 'master_data');

		$transfer_trnno = (int) $transfer_trnno;
		$order_sr = (int) $order_sr;
		$order_id = (int) $order_id;
		$from_dept = trim((string) $from_dept);
		$dept1 = $this->_transfer_next_dept($from_dept);
		$stit_complete = ($from_dept === 'STIT');
		$allowed = array('HO', 'CUT', 'HO2', 'STIT');
		if ($transfer_trnno <= 0 || $order_sr <= 0 || $order_id <= 0 || ! in_array($from_dept, $allowed, TRUE))
		{
			return array('ok' => FALSE, 'error' => 'transfer_invalid');
		}
		if ( ! $stit_complete && $dept1 === '')
		{
			return array('ok' => FALSE, 'error' => 'transfer_invalid');
		}

		$employee_account = trim((string) $employee_account);
		$rate = trim((string) $rate);
		if ( ! $stit_complete && ($dept1 === 'CUT' || $dept1 === 'STIT'))
		{
			if ($employee_account === '')
			{
				return array('ok' => FALSE, 'error' => 'employee_required');
			}
			if (method_exists($this->CI->employee_query, 'is_transfer_employee')
				&& ! $this->CI->employee_query->is_transfer_employee($employee_account, $from_dept))
			{
				return array('ok' => FALSE, 'error' => 'employee_invalid');
			}
		}
		if ($rdate === '')
		{
			$rdate = date('Y-m-d');
		}

		$data = array(
			'EDATE' => date('Y-m-d'),
			'order_id' => $order_id,
			'sr' => $order_sr,
			'empno' => $employee_account,
			'qty' => 1,
			'price' => $rate,
			'RDATE' => $rdate,
			'dept' => $dept1,
			'status' => 'Active',
		);
		$data1 = array(
			'status' => 'Deactive',
			'RDATE' => date('Y-m-d'),
		);
		if ($from_dept === 'STIT' || $from_dept === 'CUT')
		{
			$data1['EDATE'] = date('Y-m-d');
		}

		$deactivated = FALSE;
		if ($transfer_trnno > 0)
		{
			$this->CI->db->where('trnno', $transfer_trnno);
			$this->CI->db->where('dept', $from_dept);
			$this->CI->db->where('status', 'Active');
			$this->CI->db->update('transfer_mst', $data1);
			$deactivated = ($this->CI->db->affected_rows() > 0);
		}
		if ( ! $deactivated)
		{
			$this->CI->db->where('order_id', $order_id);
			$this->CI->db->where('sr', $order_sr);
			$this->CI->db->where('dept', $from_dept);
			$this->CI->db->where('status', 'Active');
			$this->CI->db->update('transfer_mst', $data1);
			$deactivated = ($this->CI->db->affected_rows() > 0);
		}
		if ( ! $deactivated)
		{
			return array('ok' => FALSE, 'conflict' => TRUE, 'error' => 'transfer_not_active');
		}

		if ($dept1 === 'CUT' || $dept1 === 'STIT')
		{
			$jmst = array(
				'TRNDATE' => date('Y-m-d'),
				'ACCOUNT' => $employee_account,
				'ACCOUNT_TYPE' => 'EMPLOYEE',
			);
			$this->CI->insert->insert_dat1($jmst, 'jmst');
			$max_jmst = $this->CI->master_data->get_max_id('TRNNO', 'jmst');
			$max_tr = $this->CI->master_data->get_max_id('trnno', 'transfer_mst');
			$jdtl = array(
				'SR' => $max_jmst,
				'NARRATION' => 'Employee Debit to assign task REF#'.$max_tr.' (offline)',
				'DR' => $rate,
				'CR' => 0,
			);
			$this->CI->insert->insert_dat1($jdtl, 'jdtl');
			$jdtl['NARRATION'] = 'New ALi Baba tailor credited by task asign REF#'.$max_tr.' (offline)';
			$jdtl['DR'] = 0;
			$jdtl['CR'] = $rate;
			$this->CI->insert->insert_dat1($jdtl, 'jdtl');
		}

		if ($stit_complete)
		{
			return array('ok' => TRUE, 'dept1' => '', 'stit_only' => TRUE);
		}

		$this->CI->db->where('sr', $order_sr);
		$this->CI->db->where('dept', $dept1);
		$this->CI->db->where('order_id', $order_id);
		$query = $this->CI->db->get('transfer_mst');
		if ($query->num_rows() < 1)
		{
			$this->CI->insert->insert_dat1($data, 'transfer_mst');
		}
		if ($this->CI->db->table_exists('transfer_mst_new'))
		{
			$this->CI->insert->insert_dat1($data, 'transfer_mst_new');
		}
		return array('ok' => TRUE, 'dept1' => $dept1);
	}

	private function _customer_payload_fields(array $payload)
	{
		$map = array(
			'name' => array('name', 'Name'),
			'city' => array('city', 'City'),
			'country' => array('country', 'Country'),
			'Phone' => array('Phone', 'phone'),
			'Address' => array('Address', 'address'),
			'cnic' => array('cnic', 'CNIC'),
			'cus_type' => array('cus_type', 'type'),
			'man_napno' => array('man_napno'),
			'remarks' => array('remarks', 'Remarks'),
		);
		$out = array();
		foreach ($map as $col => $keys)
		{
			foreach ($keys as $k)
			{
				if (array_key_exists($k, $payload))
				{
					$out[$col] = $payload[$k];
					break;
				}
			}
		}
		return $out;
	}

	/**
	 * Snapshots for offline Transfer queues + Order Status search.
	 */
	public function offline_lists()
	{
		$this->CI->load->model('Order_query_model', 'order_query');
		$this->CI->load->helper('esc');
		$this->CI->load->helper('url');

		$transfer = array();
		foreach (array('HO', 'CUT', 'HO2', 'STIT') as $dept)
		{
			$rows = $this->CI->order_query->get_pending_transfer_rows($dept, 0, 800, '', 'order_mst.trnno', 'DESC');
			$transfer[$dept] = $this->_format_transfer_list_rows($dept, $rows);
		}

		// Large snapshot so offline phone/order search matches online results.
		$current = $this->CI->order_query->order_Status(array('row_limit' => 3000));
		$delivered_filters = $this->CI->order_query->apply_default_order_date_filters(array(), 30, 120, FALSE);
		$delivered_filters['row_limit'] = 1500;
		$delivered = $this->CI->order_query->order_Status_D($delivered_filters);

		return array(
			'ok' => TRUE,
			'server_time' => date('c'),
			'transfer' => $transfer,
			'order_status' => array(
				'current' => $this->_format_order_status_list_rows($current),
				'delivered' => $this->_format_order_status_list_rows($delivered),
			),
			'counts' => array(
				'transfer_ho' => isset($transfer['HO']) ? count($transfer['HO']) : 0,
				'transfer_cut' => isset($transfer['CUT']) ? count($transfer['CUT']) : 0,
				'transfer_ho2' => isset($transfer['HO2']) ? count($transfer['HO2']) : 0,
				'transfer_stit' => isset($transfer['STIT']) ? count($transfer['STIT']) : 0,
				'order_status_current' => is_array($current) ? count($current) : 0,
				'order_status_delivered' => is_array($delivered) ? count($delivered) : 0,
			),
		);
	}

	private function _format_transfer_list_rows($dept, $rows)
	{
		$dept = strtoupper(trim((string) $dept));
		$show_bulk = ($dept === 'CUT' || $dept === 'STIT');
		$list_from = $dept;
		$out = array();
		$sr_by_order = array();
		foreach ($rows as $row)
		{
			$order_no = (int) $row->ORDERNO;
			if ( ! isset($sr_by_order[$order_no]))
			{
				$sr_by_order[$order_no] = 1;
			}
			else
			{
				$sr_by_order[$order_no]++;
			}
			$line_sr = isset($row->ORDERSR) ? (int) $row->ORDERSR : (int) $row->SUITNO;
			$display_sr = $sr_by_order[$order_no];
			$transfer_id = (int) $row->TRANSFERID;
			$emp_name = isset($row->emp_name) ? trim((string) $row->emp_name) : '';
			$emp_name = preg_replace('/\s*0{3,}\s*$/u', '', $emp_name);
			$emp_name = trim((string) $emp_name);
			$entry = $row->ORDERDATE;
			if ($entry === '' || $entry === NULL)
			{
				$entry = $row->TRANSFEREDATE;
			}
			$ret = $row->RORDERDATE;
			if ($ret === '' || $ret === NULL)
			{
				$ret = $row->RETURNDATE;
			}
			$cells = array();
			if ($show_bulk)
			{
				$cells[] = '<input type="checkbox" class="bulk-transfer-cb" data-trnno="'.$transfer_id.'" data-order="'.$order_no.'" data-sr="'.$line_sr.'">';
			}
			$cells[] = esc_html($order_no.'-'.$display_sr);
			$cells[] = esc_html($emp_name);
			$cells[] = ($entry !== '' && $entry !== NULL) ? date('d-m-Y', strtotime($entry)) : '';
			$cells[] = ($ret !== '' && $ret !== NULL) ? date('d-m-Y', strtotime($ret)) : '';
			$cells[] = esc_html((string) $row->Phone);
			$cells[] = esc_html((string) $row->name);
			$cells[] = esc_html((string) $row->man_napno);
			$cells[] = esc_html((string) $row->order_remarks);
			$view = '';
			if ($dept === 'CUT' || $dept === 'STIT')
			{
				$view = '<a class="btn btn-primary dz-btn--filled btn-sm" href="'.site_url('Transfer/transfer_Print/'.$transfer_id.'/'.$dept.'/'.$line_sr).'" target="_blank">View</a>';
			}
			$cells[] = $view;
			$cells[] = '<a class="btn btn-primary dz-btn--filled btn-sm" href="'.site_url('Transfer/transfer_order/'.$transfer_id.'/'.$dept.'/'.$line_sr).'">Transfer</a>';
			$ret_btn = '';
			if ($dept !== 'HO')
			{
				$ret_btn = '<a class="btn btn-primary dz-btn--filled btn-sm" data-no-ajax="true" href="'.site_url('Transfer/transfer_return/'.$transfer_id.'/'.$dept.'/'.$order_no.'/'.$line_sr.'/'.$list_from).'">Return</a>';
			}
			$cells[] = $ret_btn;
			$out[] = $cells;
		}
		return $out;
	}

	private function _format_order_status_list_rows($rows)
	{
		$out = array();
		$employees = array();
		try
		{
			$emp_rows = $this->CI->db->select('empno, name')->get('employee')->result();
			foreach ($emp_rows as $e)
			{
				$employees[(string) $e->empno] = $e->name;
			}
		}
		catch (Exception $e)
		{
			$employees = array();
		}
		$order = 0;
		$sr = 1;
		foreach ($rows as $row)
		{
			if (isset($row->ostatus) && (int) $row->ostatus !== 0)
			{
				continue;
			}
			$orderno = isset($row->ORDERNO) ? (int) $row->ORDERNO : 0;
			if ($orderno !== $order)
			{
				$order = $orderno;
				$sr = 1;
			}
			else
			{
				$sr++;
			}
			$dept = isset($row->dept) ? strtoupper(trim((string) $row->dept)) : '';
			$xfer_status = isset($row->status) ? trim((string) $row->status) : '';
			$is_delivered = ($xfer_status === 'DELIVER');
			$is_ready = ( ! $is_delivered && $dept === 'STIT' && $xfer_status === 'Deactive');
			// Same labels as live order_status.php
			if ($is_delivered)
			{
				$status_label = 'Delivered';
			}
			elseif ($dept === 'HO')
			{
				$status_label = 'Booking';
			}
			elseif ($dept === 'CUT')
			{
				$status_label = 'Cutting';
			}
			elseif ($dept === 'HO2')
			{
				$status_label = 'After Cutting';
			}
			elseif ($dept === 'STIT' && $xfer_status !== 'Deactive')
			{
				$status_label = 'Stitching';
			}
			elseif ($is_ready)
			{
				$status_label = 'Ready 4 Deliver';
			}
			else
			{
				$status_label = $dept !== '' ? $dept : '';
			}
			$empno = isset($row->empno) ? (string) $row->empno : '';
			$entry_raw = ! empty($row->ORDERDATE) ? $row->ORDERDATE : (! empty($row->TRANSFEREDATE) ? $row->TRANSFEREDATE : '');
			$return_raw = ! empty($row->ORDERRDATE) ? $row->ORDERRDATE : (! empty($row->RETURNDATE) ? $row->RETURNDATE : (! empty($row->RDATE) ? $row->RDATE : ''));
			$out[] = array(
				'order_no' => $orderno,
				'order_display' => $orderno.'-'.$sr,
				'emp_name' => isset($employees[$empno]) ? $employees[$empno] : '',
				'entry_date' => $entry_raw !== '' ? date('d-m-Y', strtotime($entry_raw)) : '',
				'order_date' => $entry_raw !== '' ? date('d-m-Y', strtotime($entry_raw)) : '',
				'return_date' => $return_raw !== '' ? date('d-m-Y', strtotime($return_raw)) : '',
				'phone' => isset($row->Phone) ? (string) $row->Phone : '',
				'customer' => isset($row->name) ? (string) $row->name : '',
				'naap_no' => isset($row->man_napno) ? (string) $row->man_napno : '',
				'remarks' => isset($row->order_remarks) ? (string) $row->order_remarks : '',
				'status_label' => $status_label,
				'stage' => $dept,
				'xfer_status' => $xfer_status,
			);
		}
		return $out;
	}

	private function _normalize_customers(array $rows)
	{
		$out = array();
		foreach ($rows as $r)
		{
			$out[] = array(
				'server_id' => (string) $r['code'],
				'client_uuid' => isset($r['client_uuid']) ? $r['client_uuid'] : NULL,
				'man_napno' => isset($r['man_napno']) ? $r['man_napno'] : '',
				'name' => isset($r['name']) ? $r['name'] : '',
				'city' => isset($r['city']) ? $r['city'] : '',
				'country' => isset($r['country']) ? $r['country'] : '',
				'Phone' => isset($r['Phone']) ? $r['Phone'] : '',
				'Address' => isset($r['Address']) ? $r['Address'] : '',
				'cnic' => isset($r['cnic']) ? $r['cnic'] : '',
				'cus_type' => isset($r['cus_type']) ? $r['cus_type'] : '',
				'remarks' => isset($r['remarks']) ? $r['remarks'] : '',
				'row_version' => isset($r['row_version']) ? (int) $r['row_version'] : 1,
				'updated_at' => isset($r['updated_at']) ? $r['updated_at'] : NULL,
				'sync_status' => 'synced',
			);
		}
		return $out;
	}

	private function _normalize_measurements(array $rows)
	{
		$out = array();
		foreach ($rows as $r)
		{
			$out[] = array(
				'server_id' => (string) $r['code'],
				'client_uuid' => isset($r['client_uuid']) ? $r['client_uuid'] : NULL,
				'customer_code' => isset($r['customer_code']) ? (string) $r['customer_code'] : '',
				'remarks' => isset($r['remarks']) ? $r['remarks'] : '',
				'row_version' => isset($r['row_version']) ? (int) $r['row_version'] : 1,
				'updated_at' => isset($r['updated_at']) ? $r['updated_at'] : NULL,
				'sync_status' => 'synced',
				'raw' => $r,
			);
		}
		return $out;
	}

	private function _normalize_orders(array $rows)
	{
		$out = array();
		foreach ($rows as $r)
		{
			$out[] = array(
				'server_id' => (string) $r['trnno'],
				'client_uuid' => isset($r['client_uuid']) ? $r['client_uuid'] : NULL,
				'naap_no' => isset($r['naap_no']) ? (string) $r['naap_no'] : '',
				'customer_name' => isset($r['customer_name']) ? $r['customer_name'] : '',
				'customer_phone' => isset($r['customer_phone']) ? $r['customer_phone'] : '',
				'customer_code' => isset($r['customer_code']) ? (string) $r['customer_code'] : '',
				'man_napno' => isset($r['man_napno']) ? $r['man_napno'] : '',
				'total' => isset($r['total']) ? $r['total'] : 0,
				'advance' => isset($r['advance']) ? $r['advance'] : 0,
				'edate' => isset($r['edate']) ? $r['edate'] : (isset($r['EDATE']) ? $r['EDATE'] : NULL),
				'ddate' => isset($r['ddate']) ? $r['ddate'] : (isset($r['DDATE']) ? $r['DDATE'] : NULL),
				'row_version' => isset($r['row_version']) ? (int) $r['row_version'] : 1,
				'updated_at' => isset($r['updated_at']) ? $r['updated_at'] : NULL,
				'sync_status' => 'synced',
			);
		}
		return $out;
	}

	private function _masters()
	{
		$out = array();
		foreach (array('suit_type', 'style', 'embroidery', 'button') as $table)
		{
			if ($this->CI->db->table_exists($table))
			{
				$out[$table] = $this->CI->db->limit(500)->get($table)->result_array();
			}
			else
			{
				$out[$table] = array();
			}
		}
		return $out;
	}
}
