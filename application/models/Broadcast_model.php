<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broadcast_model extends CI_Model
{
	const CAMPAIGNS = 'wa_broadcast_campaigns';
	const RECIPIENTS = 'wa_broadcast_recipients';

	public function has_tables()
	{
		return $this->db->table_exists(self::CAMPAIGNS) && $this->db->table_exists(self::RECIPIENTS);
	}

	public function list_campaigns($limit = 40)
	{
		if ( ! $this->has_tables())
		{
			return array();
		}
		return $this->db
			->order_by('id', 'DESC')
			->limit((int) $limit)
			->get(self::CAMPAIGNS)
			->result_array();
	}

	public function get_campaign($id)
	{
		$id = (int) $id;
		if ($id < 1 || ! $this->has_tables())
		{
			return NULL;
		}
		return $this->db->where('id', $id)->get(self::CAMPAIGNS)->row_array();
	}

	public function create_campaign(array $data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = $data['created_at'];
		$this->db->insert(self::CAMPAIGNS, $data);
		return (int) $this->db->insert_id();
	}

	public function update_campaign($id, array $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', (int) $id)->update(self::CAMPAIGNS, $data);
		return $this->db->affected_rows() >= 0;
	}

	public function add_recipients($campaign_id, array $rows)
	{
		$campaign_id = (int) $campaign_id;
		if ($campaign_id < 1 || ! $rows)
		{
			return 0;
		}
		$now = date('Y-m-d H:i:s');
		$n = 0;
		foreach ($rows as $row)
		{
			$row['campaign_id'] = $campaign_id;
			$row['created_at'] = $now;
			$row['status'] = 'pending';
			// Ignore duplicates on unique phone key.
			$this->db->query(
				'INSERT IGNORE INTO `'.self::RECIPIENTS.'` (`campaign_id`,`customer_code`,`customer_name`,`phone`,`phone_normalized`,`personalized_message`,`status`,`created_at`) VALUES (?,?,?,?,?,?,?,?)',
				array(
					$campaign_id,
					(int) $row['customer_code'],
					(string) $row['customer_name'],
					(string) $row['phone'],
					(string) $row['phone_normalized'],
					isset($row['personalized_message']) ? (string) $row['personalized_message'] : NULL,
					'pending',
					$now,
				)
			);
			if ($this->db->affected_rows() > 0)
			{
				$n++;
			}
		}
		$this->refresh_campaign_counts($campaign_id);
		return $n;
	}

	public function refresh_campaign_counts($campaign_id)
	{
		$campaign_id = (int) $campaign_id;
		$row = $this->db->query(
			'SELECT
				COUNT(*) AS total_recipients,
				SUM(CASE WHEN status = \'sent\' THEN 1 ELSE 0 END) AS sent_count,
				SUM(CASE WHEN status = \'failed\' THEN 1 ELSE 0 END) AS fail_count,
				SUM(CASE WHEN status = \'skipped\' THEN 1 ELSE 0 END) AS skip_count
			 FROM `'.self::RECIPIENTS.'` WHERE campaign_id = ?',
			array($campaign_id)
		)->row_array();
		$this->update_campaign($campaign_id, array(
			'total_recipients' => (int) (isset($row['total_recipients']) ? $row['total_recipients'] : 0),
			'sent_count' => (int) (isset($row['sent_count']) ? $row['sent_count'] : 0),
			'fail_count' => (int) (isset($row['fail_count']) ? $row['fail_count'] : 0),
			'skip_count' => (int) (isset($row['skip_count']) ? $row['skip_count'] : 0),
		));
	}

	public function list_recipients($campaign_id, $limit = 200, $offset = 0)
	{
		return $this->db
			->where('campaign_id', (int) $campaign_id)
			->order_by('id', 'ASC')
			->limit((int) $limit, (int) $offset)
			->get(self::RECIPIENTS)
			->result_array();
	}

	public function next_pending_recipient($campaign_id)
	{
		return $this->db
			->where('campaign_id', (int) $campaign_id)
			->where('status', 'pending')
			->order_by('id', 'ASC')
			->limit(1)
			->get(self::RECIPIENTS)
			->row_array();
	}

	public function update_recipient($id, array $data)
	{
		$this->db->where('id', (int) $id)->update(self::RECIPIENTS, $data);
	}

	public function count_sent_since($since_datetime)
	{
		$row = $this->db->query(
			'SELECT COUNT(*) AS c FROM `'.self::RECIPIENTS.'` WHERE status = \'sent\' AND sent_at >= ?',
			array($since_datetime)
		)->row_array();
		return (int) (isset($row['c']) ? $row['c'] : 0);
	}

	public function active_campaigns()
	{
		if ( ! $this->has_tables())
		{
			return array();
		}
		return $this->db
			->where_in('status', array('queued', 'running'))
			->order_by('id', 'ASC')
			->get(self::CAMPAIGNS)
			->result_array();
	}

	/**
	 * Customers with usable phone numbers for broadcast picker.
	 */
	public function search_customers($q = '', $limit = 80, $offset = 0)
	{
		$limit = max(1, min(200, (int) $limit));
		$offset = max(0, (int) $offset);
		$this->db->from('customer');
		$this->db->select('code, name, Phone');
		$this->db->where('Phone IS NOT NULL', NULL, FALSE);
		$this->db->where("TRIM(Phone) <> ''", NULL, FALSE);
		$this->db->where("TRIM(Phone) <> '0'", NULL, FALSE);
		$this->db->where("TRIM(Phone) <> '00'", NULL, FALSE);
		$q = trim((string) $q);
		if ($q !== '')
		{
			$like = $this->db->escape_like_str($q);
			$this->db->group_start()
				->like('name', $like)
				->or_like('Phone', $like)
				->or_like('code', $like)
				->group_end();
		}
		$this->db->order_by('code', 'DESC');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result_array();
	}

	public function count_customers_with_phone($q = '')
	{
		$this->db->from('customer');
		$this->db->where('Phone IS NOT NULL', NULL, FALSE);
		$this->db->where("TRIM(Phone) <> ''", NULL, FALSE);
		$this->db->where("TRIM(Phone) <> '0'", NULL, FALSE);
		$this->db->where("TRIM(Phone) <> '00'", NULL, FALSE);
		$q = trim((string) $q);
		if ($q !== '')
		{
			$like = $this->db->escape_like_str($q);
			$this->db->group_start()
				->like('name', $like)
				->or_like('Phone', $like)
				->or_like('code', $like)
				->group_end();
		}
		return (int) $this->db->count_all_results();
	}

	public function customers_by_codes(array $codes)
	{
		$codes = array_values(array_unique(array_filter(array_map('intval', $codes))));
		if ( ! $codes)
		{
			return array();
		}
		return $this->db
			->select('code, name, Phone')
			->where_in('code', $codes)
			->get('customer')
			->result_array();
	}

	/**
	 * Fetch up to $limit customers that look like they have a phone (for select-all).
	 */
	public function list_phone_customers($limit = 500)
	{
		$limit = max(1, min(500, (int) $limit));
		return $this->db
			->select('code, name, Phone')
			->from('customer')
			->where('Phone IS NOT NULL', NULL, FALSE)
			->where("TRIM(Phone) <> ''", NULL, FALSE)
			->where("TRIM(Phone) <> '0'", NULL, FALSE)
			->where("TRIM(Phone) <> '00'", NULL, FALSE)
			->where('LENGTH(REPLACE(REPLACE(REPLACE(Phone, " ", ""), "-", ""), "+", "")) >= 10', NULL, FALSE)
			->order_by('code', 'DESC')
			->limit($limit)
			->get()
			->result_array();
	}
}
