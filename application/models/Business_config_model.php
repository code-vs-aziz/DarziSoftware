<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Business_config_model extends CI_Model
{
	const TABLE = 'business_configuration';

	public function get_config()
	{
		if (!$this->db->table_exists(self::TABLE))
		{
			return $this->default_config();
		}

		$row = $this->db->where('id', 1)->get(self::TABLE)->row_array();
		return $row ? array_merge($this->default_config(), $row) : $this->default_config();
	}

	public function save_config(array $data)
	{
		if (!$this->db->table_exists(self::TABLE))
		{
			return FALSE;
		}

		$config = array(
			'id' => 1,
			'location_name' => isset($data['location_name']) ? trim((string) $data['location_name']) : '',
			'phone' => isset($data['phone']) ? trim((string) $data['phone']) : '',
			'address_urdu' => isset($data['address_urdu']) ? trim((string) $data['address_urdu']) : '',
		);

		if ($this->db->field_exists('naap_number_prefix', self::TABLE))
		{
			$prefix = isset($data['naap_number_prefix']) ? trim((string) $data['naap_number_prefix']) : '';
			$config['naap_number_prefix'] = mb_substr($prefix, 0, 20);
		}

		if ($this->db->field_exists('daily_booking_limit', self::TABLE))
		{
			$raw = isset($data['daily_booking_limit']) ? trim((string) $data['daily_booking_limit']) : '';
			if ($raw === '')
			{
				$config['daily_booking_limit'] = NULL;
			}
			else
			{
				$config['daily_booking_limit'] = max(0, (int) $raw);
			}
		}

		$exists = $this->db->where('id', 1)->count_all_results(self::TABLE);
		if ($exists)
		{
			$this->db->where('id', 1)->update(self::TABLE, $config);
			return TRUE;
		}

		$this->db->insert(self::TABLE, $config);
		return TRUE;
	}

	public function has_table()
	{
		return $this->db->table_exists(self::TABLE);
	}

	protected function default_config()
	{
		return array(
			'id' => 1,
			'location_name' => '',
			'phone' => '',
			'address_urdu' => '',
			'naap_number_prefix' => '',
			'daily_booking_limit' => NULL,
		);
	}
}
