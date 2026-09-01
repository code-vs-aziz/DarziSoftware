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
		return $row ? $row : $this->default_config();
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
		);
	}
}
