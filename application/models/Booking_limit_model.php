<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Daily booking limits by return date (global + per-date overrides).
 */
class Booking_limit_model extends CI_Model
{
	const DATE_TABLE = 'booking_date_limits';
	const CONFIG_TABLE = 'business_configuration';

	public function has_date_table()
	{
		return $this->db->table_exists(self::DATE_TABLE);
	}

	/**
	 * Effective max qty for a return date. NULL = unlimited.
	 */
	public function get_effective_limit($return_date)
	{
		$return_date = $this->normalize_date($return_date);
		if ($return_date === '')
		{
			return NULL;
		}

		if ($this->has_date_table())
		{
			$row = $this->db->select('limit_qty')
				->from(self::DATE_TABLE)
				->where('return_date', $return_date)
				->limit(1)
				->get()
				->row_array();
			if ($row)
			{
				return (int) $row['limit_qty'];
			}
		}

		if ( ! $this->db->table_exists(self::CONFIG_TABLE) || ! $this->db->field_exists('daily_booking_limit', self::CONFIG_TABLE))
		{
			return NULL;
		}

		$cfg = $this->db->select('daily_booking_limit')
			->from(self::CONFIG_TABLE)
			->where('id', 1)
			->limit(1)
			->get()
			->row_array();
		if ( ! $cfg || $cfg['daily_booking_limit'] === NULL || $cfg['daily_booking_limit'] === '')
		{
			return NULL;
		}
		$limit = (int) $cfg['daily_booking_limit'];
		return $limit > 0 ? $limit : NULL;
	}

	/**
	 * Sum of active order qty for a return date (optionally excluding one order).
	 */
	public function booked_qty_for_date($return_date, $exclude_order_trnno = 0)
	{
		$return_date = $this->normalize_date($return_date);
		if ($return_date === '' || ! $this->db->table_exists('order_mst'))
		{
			return 0;
		}

		$this->db->select_sum('qty', 'total_qty');
		$this->db->from('order_mst');
		$this->db->where('RDATE', $return_date);
		$this->db->where('(ostatus IS NULL OR ostatus = 0 OR ostatus = \'0\')', NULL, FALSE);
		$exclude_order_trnno = (int) $exclude_order_trnno;
		if ($exclude_order_trnno > 0)
		{
			$this->db->where('trnno !=', $exclude_order_trnno);
		}
		$row = $this->db->get()->row_array();
		return isset($row['total_qty']) ? (int) $row['total_qty'] : 0;
	}

	/**
	 * @return array{ok:bool,message?:string,limit?:int,booked?:int,remaining?:int}
	 */
	public function check_can_book($return_date, $new_qty, $exclude_order_trnno = 0)
	{
		$return_date = $this->normalize_date($return_date);
		$new_qty = max(0, (int) $new_qty);
		if ($return_date === '')
		{
			return array('ok' => TRUE);
		}

		$limit = $this->get_effective_limit($return_date);
		if ($limit === NULL)
		{
			return array('ok' => TRUE);
		}

		$booked = $this->booked_qty_for_date($return_date, $exclude_order_trnno);
		$remaining = $limit - $booked;
		if ($new_qty > $remaining)
		{
			return array(
				'ok' => FALSE,
				'limit' => $limit,
				'booked' => $booked,
				'remaining' => max(0, $remaining),
				'message' => 'booking_limit_exceeded',
			);
		}

		return array(
			'ok' => TRUE,
			'limit' => $limit,
			'booked' => $booked,
			'remaining' => $remaining,
		);
	}

	public function list_date_limits($limit = 200)
	{
		if ( ! $this->has_date_table())
		{
			return array();
		}
		$limit = max(1, min(500, (int) $limit));
		return $this->db->order_by('return_date', 'ASC')
			->limit($limit)
			->get(self::DATE_TABLE)
			->result_array();
	}

	/**
	 * Upsert by return_date.
	 */
	public function save_date_limit($return_date, $limit_qty, $note = '')
	{
		if ( ! $this->has_date_table())
		{
			return array('ok' => FALSE, 'message' => 'missing_table');
		}
		$return_date = $this->normalize_date($return_date);
		if ($return_date === '')
		{
			return array('ok' => FALSE, 'message' => 'invalid_date');
		}
		$limit_qty = max(0, (int) $limit_qty);
		$note = trim((string) $note);
		if (mb_strlen($note) > 255)
		{
			$note = mb_substr($note, 0, 255);
		}

		$existing = $this->db->select('id')
			->from(self::DATE_TABLE)
			->where('return_date', $return_date)
			->limit(1)
			->get()
			->row_array();

		$row = array(
			'return_date' => $return_date,
			'limit_qty' => $limit_qty,
			'note' => ($note !== '' ? $note : NULL),
		);

		if ($existing)
		{
			$this->db->where('id', (int) $existing['id'])->update(self::DATE_TABLE, $row);
		}
		else
		{
			$this->db->insert(self::DATE_TABLE, $row);
		}
		return array('ok' => TRUE);
	}

	public function delete_date_limit($id)
	{
		if ( ! $this->has_date_table())
		{
			return FALSE;
		}
		$id = (int) $id;
		if ($id <= 0)
		{
			return FALSE;
		}
		$this->db->where('id', $id)->delete(self::DATE_TABLE);
		return TRUE;
	}

	protected function normalize_date($value)
	{
		$value = trim((string) $value);
		if ($value === '')
		{
			return '';
		}
		$ts = strtotime($value);
		if ($ts === FALSE)
		{
			return '';
		}
		return date('Y-m-d', $ts);
	}
}
