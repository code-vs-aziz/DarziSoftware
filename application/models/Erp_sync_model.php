<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Persistence for offline-first staff sync (tokens, ops, id map, conflicts).
 */
class Erp_sync_model extends CI_Model
{
	public function ensure_schema()
	{
		static $done = FALSE;
		if ($done)
		{
			return;
		}
		$done = TRUE;

		$this->_exec_sql_file(APPPATH.'sql/offline_sync_schema.sql');
		$this->_ensure_column('customer', 'client_uuid', "CHAR(36) NULL DEFAULT NULL");
		$this->_ensure_column('customer', 'row_version', "INT UNSIGNED NOT NULL DEFAULT 1");
		$this->_ensure_column('customer', 'updated_at', "DATETIME NULL DEFAULT NULL");
		$this->_ensure_unique('customer', 'uq_customer_client_uuid', 'client_uuid');

		$this->_ensure_column('measurement', 'client_uuid', "CHAR(36) NULL DEFAULT NULL");
		$this->_ensure_column('measurement', 'row_version', "INT UNSIGNED NOT NULL DEFAULT 1");
		$this->_ensure_column('measurement', 'updated_at', "DATETIME NULL DEFAULT NULL");

		$this->_ensure_column('order_mst', 'client_uuid', "CHAR(36) NULL DEFAULT NULL");
		$this->_ensure_column('order_mst', 'row_version', "INT UNSIGNED NOT NULL DEFAULT 1");
		$this->_ensure_column('order_mst', 'updated_at', "DATETIME NULL DEFAULT NULL");
		$this->_ensure_unique('order_mst', 'uq_order_mst_client_uuid', 'client_uuid');
	}

	private function _exec_sql_file($path)
	{
		if ( ! is_readable($path))
		{
			return;
		}
		$sql = file_get_contents($path);
		if ($sql === FALSE || $sql === '')
		{
			return;
		}
		// Strip line comments and split on semicolons for CREATE TABLE blocks.
		$lines = preg_split("/\r\n|\n|\r/", $sql);
		$buf = '';
		foreach ($lines as $line)
		{
			$trim = ltrim($line);
			if ($trim === '' || strpos($trim, '--') === 0)
			{
				continue;
			}
			$buf .= $line."\n";
			if (substr(rtrim($line), -1) === ';')
			{
				$stmt = trim($buf);
				$buf = '';
				if ($stmt !== '' && stripos($stmt, 'CREATE TABLE') === 0)
				{
					@$this->db->query($stmt);
				}
			}
		}
	}

	private function _ensure_column($table, $column, $definition)
	{
		if ( ! $this->db->table_exists($table))
		{
			return;
		}
		if ($this->db->field_exists($column, $table))
		{
			return;
		}
		@$this->db->query('ALTER TABLE `'.$table.'` ADD COLUMN `'.$column.'` '.$definition);
	}

	private function _ensure_unique($table, $index_name, $column)
	{
		if ( ! $this->db->table_exists($table) || ! $this->db->field_exists($column, $table))
		{
			return;
		}
		$db = $this->db->database;
		$q = $this->db->query(
			'SELECT 1 FROM information_schema.statistics
			 WHERE table_schema = '.$this->db->escape($db).'
			   AND table_name = '.$this->db->escape($table).'
			   AND index_name = '.$this->db->escape($index_name).'
			 LIMIT 1'
		);
		if ($q && $q->num_rows() > 0)
		{
			return;
		}
		@$this->db->query('ALTER TABLE `'.$table.'` ADD UNIQUE KEY `'.$index_name.'` (`'.$column.'`)');
	}

	public function find_token_by_hash($hash)
	{
		return $this->db->where('token_hash', $hash)
			->where('revoked_at IS NULL', NULL, FALSE)
			->order_by('id', 'DESC')
			->limit(1)
			->get('erp_sync_tokens')
			->row_array();
	}

	public function upsert_device($device_id, $user_name, $label, $ua)
	{
		$now = date('Y-m-d H:i:s');
		$row = $this->db->where('device_id', $device_id)->get('erp_sync_devices')->row_array();
		if ($row)
		{
			$this->db->where('id', (int) $row['id'])->update('erp_sync_devices', array(
				'user_name' => $user_name,
				'label' => $label,
				'user_agent' => substr((string) $ua, 0, 255),
				'last_seen_at' => $now,
				'revoked_at' => NULL,
			));
			return;
		}
		$this->db->insert('erp_sync_devices', array(
			'device_id' => $device_id,
			'user_name' => $user_name,
			'label' => $label,
			'user_agent' => substr((string) $ua, 0, 255),
			'created_at' => $now,
			'last_seen_at' => $now,
		));
	}

	public function insert_token($device_id, $user_name, $token_hash, $expires_at)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->insert('erp_sync_tokens', array(
			'device_id' => $device_id,
			'user_name' => $user_name,
			'token_hash' => $token_hash,
			'issued_at' => $now,
			'expires_at' => $expires_at,
		));
	}

	public function revoke_device_tokens($device_id, $user_name = NULL)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->where('device_id', $device_id);
		if ($user_name !== NULL)
		{
			$this->db->where('user_name', $user_name);
		}
		$this->db->where('revoked_at IS NULL', NULL, FALSE);
		$this->db->update('erp_sync_tokens', array('revoked_at' => $now));
	}

	public function touch_token($id)
	{
		$this->db->where('id', (int) $id)->update('erp_sync_tokens', array(
			'last_used_at' => date('Y-m-d H:i:s'),
		));
	}

	public function find_op($device_id, $idempotency_key)
	{
		return $this->db->where('device_id', $device_id)
			->where('idempotency_key', $idempotency_key)
			->limit(1)
			->get('erp_sync_ops')
			->row_array();
	}

	public function save_op($data)
	{
		$this->db->insert('erp_sync_ops', $data);
		return (int) $this->db->insert_id();
	}

	public function map_get($entity_type, $client_uuid)
	{
		return $this->db->where('entity_type', $entity_type)
			->where('client_uuid', $client_uuid)
			->limit(1)
			->get('erp_sync_id_map')
			->row_array();
	}

	public function map_put($entity_type, $client_uuid, $server_id, $device_id = '')
	{
		$existing = $this->map_get($entity_type, $client_uuid);
		if ($existing)
		{
			$this->db->where('id', (int) $existing['id'])->update('erp_sync_id_map', array(
				'server_id' => (string) $server_id,
				'device_id' => (string) $device_id,
			));
			return;
		}
		$this->db->insert('erp_sync_id_map', array(
			'entity_type' => $entity_type,
			'client_uuid' => $client_uuid,
			'server_id' => (string) $server_id,
			'device_id' => (string) $device_id,
			'created_at' => date('Y-m-d H:i:s'),
		));
	}

	public function save_conflict($data)
	{
		$this->db->insert('erp_sync_conflicts', $data);
		return (int) $this->db->insert_id();
	}

	public function customers_since($since, $limit = 500)
	{
		$this->db->from('customer');
		if ($since)
		{
			$this->db->group_start();
			$this->db->where('updated_at >', $since);
			$this->db->or_where('updated_at IS NULL', NULL, FALSE);
			$this->db->group_end();
		}
		$this->db->order_by('code', 'DESC');
		$this->db->limit((int) $limit);
		return $this->db->get()->result_array();
	}

	public function recent_customers($limit = 300)
	{
		return $this->db->order_by('code', 'DESC')
			->limit((int) $limit)
			->get('customer')
			->result_array();
	}

	public function measurements_for_customers(array $codes, $limit = 500)
	{
		if (empty($codes))
		{
			return array();
		}
		return $this->db->where_in('customer_code', $codes)
			->order_by('code', 'DESC')
			->limit((int) $limit)
			->get('measurement')
			->result_array();
	}

	public function open_orders($limit = 200)
	{
		// Prefer recent orders for offline read cache.
		$sql = 'SELECT om.trnno, om.naap_no, om.EDATE AS edate, om.RDATE AS ddate,
				om.total, om.advance, om.client_uuid, om.row_version, om.updated_at,
				c.name AS customer_name, c.Phone AS customer_phone, c.code AS customer_code,
				c.man_napno
			FROM order_mst om
			LEFT JOIN measurement m ON m.code = om.naap_no
			LEFT JOIN customer c ON c.code = m.customer_code
			ORDER BY om.trnno DESC
			LIMIT '.(int) $limit;
		$q = @$this->db->query($sql);
		if ( ! $q)
		{
			// Fallback without optional sync columns / join quirks
			$sql2 = 'SELECT om.trnno, om.naap_no, om.total, om.advance
				FROM order_mst om
				ORDER BY om.trnno DESC
				LIMIT '.(int) $limit;
			$q = $this->db->query($sql2);
		}
		return $q ? $q->result_array() : array();
	}

	public function find_customer_by_code($code)
	{
		return $this->db->where('code', (int) $code)->limit(1)->get('customer')->row_array();
	}

	public function find_customer_by_uuid($uuid)
	{
		return $this->db->where('client_uuid', $uuid)->limit(1)->get('customer')->row_array();
	}

	public function find_customer_by_man_napno($man_napno)
	{
		return $this->db->where('man_napno', $man_napno)->limit(1)->get('customer')->row_array();
	}
}
