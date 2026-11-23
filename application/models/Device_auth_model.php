<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Approved-device lockdown for ERP browsers.
 * Identifier = cryptographically random cookie token (not MAC — browsers cannot expose MAC).
 */
class Device_auth_model extends CI_Model
{
	const SETTINGS_TABLE = 'device_auth_settings';
	const DEVICES_TABLE = 'authorized_devices';
	const CODES_TABLE = 'device_enrollment_codes';
	const COOKIE_NAME = 'dz_device_token';
	const COOKIE_DAYS = 730;

	public function tables_ready()
	{
		return $this->db->table_exists(self::SETTINGS_TABLE)
			&& $this->db->table_exists(self::DEVICES_TABLE)
			&& $this->db->table_exists(self::CODES_TABLE);
	}

	public function is_lock_enabled()
	{
		if ( ! $this->tables_ready())
		{
			return FALSE;
		}
		$row = $this->db->select('lock_enabled')->where('id', 1)->get(self::SETTINGS_TABLE)->row();
		return $row && (int) $row->lock_enabled === 1;
	}

	public function set_lock_enabled($enabled)
	{
		if ( ! $this->tables_ready())
		{
			return FALSE;
		}
		$this->db->where('id', 1)->update(self::SETTINGS_TABLE, array(
			'lock_enabled' => $enabled ? 1 : 0,
			'updated_at' => date('Y-m-d H:i:s'),
		));
		return TRUE;
	}

	public function list_devices()
	{
		if ( ! $this->db->table_exists(self::DEVICES_TABLE))
		{
			return array();
		}
		return $this->db
			->order_by('is_active', 'DESC')
			->order_by('last_seen_at', 'DESC')
			->order_by('id', 'DESC')
			->get(self::DEVICES_TABLE)
			->result();
	}

	public function count_active_devices()
	{
		if ( ! $this->db->table_exists(self::DEVICES_TABLE))
		{
			return 0;
		}
		return (int) $this->db->where('is_active', 1)->count_all_results(self::DEVICES_TABLE);
	}

	public function hash_token($raw_token)
	{
		return hash('sha256', (string) $raw_token);
	}

	public function find_active_by_token($raw_token)
	{
		$raw_token = trim((string) $raw_token);
		if ($raw_token === '' || ! $this->db->table_exists(self::DEVICES_TABLE))
		{
			return NULL;
		}
		$hash = $this->hash_token($raw_token);
		return $this->db
			->where('token_hash', $hash)
			->where('is_active', 1)
			->limit(1)
			->get(self::DEVICES_TABLE)
			->row();
	}

	public function touch_device($device_id, $ip)
	{
		$this->db->where('id', (int) $device_id)->update(self::DEVICES_TABLE, array(
			'last_ip' => mb_substr((string) $ip, 0, 45),
			'last_seen_at' => date('Y-m-d H:i:s'),
		));
	}

	public function register_device($device_name, $user_agent, $ip, $created_by)
	{
		if ( ! $this->tables_ready())
		{
			return array('ok' => FALSE, 'error' => 'missing_table');
		}
		$raw = bin2hex(random_bytes(32));
		$name = trim((string) $device_name);
		if ($name === '')
		{
			$name = 'Device '.date('Y-m-d H:i');
		}
		$row = array(
			'device_name' => mb_substr($name, 0, 120),
			'token_hash' => $this->hash_token($raw),
			'user_agent' => mb_substr((string) $user_agent, 0, 512),
			'register_ip' => mb_substr((string) $ip, 0, 45),
			'last_ip' => mb_substr((string) $ip, 0, 45),
			'last_seen_at' => date('Y-m-d H:i:s'),
			'is_active' => 1,
			'created_by' => mb_substr((string) $created_by, 0, 100),
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->db->insert(self::DEVICES_TABLE, $row);
		$id = (int) $this->db->insert_id();
		return array('ok' => TRUE, 'id' => $id, 'raw_token' => $raw);
	}

	public function revoke_device($id)
	{
		if ( ! $this->db->table_exists(self::DEVICES_TABLE))
		{
			return FALSE;
		}
		$this->db->where('id', (int) $id)->update(self::DEVICES_TABLE, array(
			'is_active' => 0,
		));
		return $this->db->affected_rows() >= 0;
	}

	public function create_enrollment_code($label, $created_by, $ttl_minutes = 30)
	{
		if ( ! $this->tables_ready())
		{
			return array('ok' => FALSE, 'error' => 'missing_table');
		}
		$ttl_minutes = max(5, min(1440, (int) $ttl_minutes));
		// 8-digit numeric code — easy to type on a phone.
		$code = (string) random_int(10000000, 99999999);
		$this->db->insert(self::CODES_TABLE, array(
			'code_hash' => $this->hash_token($code),
			'label' => mb_substr(trim((string) $label), 0, 120),
			'expires_at' => date('Y-m-d H:i:s', time() + ($ttl_minutes * 60)),
			'used_at' => NULL,
			'created_by' => mb_substr((string) $created_by, 0, 100),
			'created_at' => date('Y-m-d H:i:s'),
		));
		return array(
			'ok' => TRUE,
			'code' => $code,
			'expires_at' => date('Y-m-d H:i:s', time() + ($ttl_minutes * 60)),
			'ttl_minutes' => $ttl_minutes,
		);
	}

	public function consume_enrollment_code($code)
	{
		$code = preg_replace('/\D+/', '', (string) $code);
		if (strlen($code) < 6 || ! $this->tables_ready())
		{
			return FALSE;
		}
		$hash = $this->hash_token($code);
		$row = $this->db
			->where('code_hash', $hash)
			->where('used_at IS NULL', NULL, FALSE)
			->where('expires_at >=', date('Y-m-d H:i:s'))
			->limit(1)
			->get(self::CODES_TABLE)
			->row();
		if ( ! $row)
		{
			return FALSE;
		}
		$this->db->where('id', (int) $row->id)->update(self::CODES_TABLE, array(
			'used_at' => date('Y-m-d H:i:s'),
		));
		return $row;
	}

	public function client_ip()
	{
		$ip = $this->input->ip_address();
		return is_string($ip) ? $ip : '';
	}

	public function set_device_cookie($raw_token)
	{
		$expire = time() + (self::COOKIE_DAYS * 86400);
		$secure = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
		$path = config_item('cookie_path');
		if ( ! is_string($path) || $path === '')
		{
			$path = '/';
		}
		$params = array(
			'expires' => $expire,
			'path' => $path,
			'domain' => (string) config_item('cookie_domain'),
			'secure' => $secure || (bool) config_item('cookie_secure'),
			'httponly' => TRUE,
			'samesite' => 'Lax',
		);
		setcookie(self::COOKIE_NAME, $raw_token, $params);
		$_COOKIE[self::COOKIE_NAME] = $raw_token;
	}

	public function clear_device_cookie()
	{
		$path = config_item('cookie_path');
		if ( ! is_string($path) || $path === '')
		{
			$path = '/';
		}
		$params = array(
			'expires' => time() - 3600,
			'path' => $path,
			'domain' => (string) config_item('cookie_domain'),
			'secure' => (bool) config_item('cookie_secure'),
			'httponly' => TRUE,
			'samesite' => 'Lax',
		);
		setcookie(self::COOKIE_NAME, '', $params);
		unset($_COOKIE[self::COOKIE_NAME]);
	}

	public function cookie_token()
	{
		return isset($_COOKIE[self::COOKIE_NAME]) ? trim((string) $_COOKIE[self::COOKIE_NAME]) : '';
	}

	/**
	 * @return object|null active device row if cookie is valid
	 */
	public function current_device()
	{
		$token = $this->cookie_token();
		if ($token === '')
		{
			return NULL;
		}
		$device = $this->find_active_by_token($token);
		if ($device)
		{
			$this->touch_device($device->id, $this->client_ip());
		}
		return $device;
	}
}
