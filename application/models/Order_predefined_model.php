<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_predefined_model extends CI_Model
{
	const TABLE = 'order_predefined';

	public function has_table()
	{
		return $this->db->table_exists(self::TABLE);
	}

	public function get_all_active()
	{
		if (!$this->has_table())
		{
			return $this->default_items();
		}

		$rows = $this->db
			->where('is_active', 1)
			->order_by('row_group', 'ASC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get(self::TABLE)
			->result_array();

		return !empty($rows) ? $rows : $this->default_items();
	}

	public function get_grouped_active()
	{
		$grouped = array(1 => array(), 2 => array());
		foreach ($this->get_all_active() as $row)
		{
			$group = isset($row['row_group']) ? (int) $row['row_group'] : 1;
			if (!isset($grouped[$group]))
			{
				$grouped[$group] = array();
			}
			$grouped[$group][] = $row;
		}
		return $grouped;
	}

	public function get_all_for_admin()
	{
		if (!$this->has_table())
		{
			return $this->default_items();
		}

		return $this->db
			->order_by('row_group', 'ASC')
			->order_by('sort_order', 'ASC')
			->order_by('id', 'ASC')
			->get(self::TABLE)
			->result_array();
	}

	public function add_item($label_text, $insert_text = '', $row_group = 1)
	{
		if (!$this->has_table())
		{
			return FALSE;
		}

		$label_text = trim((string) $label_text);
		if ($label_text === '')
		{
			return FALSE;
		}

		$insert_text = trim((string) $insert_text);
		if ($insert_text === '')
		{
			$insert_text = $label_text.' -';
		}

		$row_group = (int) $row_group;
		if ($row_group < 1 || $row_group > 2)
		{
			$row_group = 1;
		}

		$max_sort = $this->db
			->select_max('sort_order')
			->where('row_group', $row_group)
			->get(self::TABLE)
			->row_array();
		$sort_order = isset($max_sort['sort_order']) ? ((int) $max_sort['sort_order'] + 10) : 10;

		$this->db->insert(self::TABLE, array(
			'label_text' => $label_text,
			'insert_text' => $insert_text,
			'row_group' => $row_group,
			'sort_order' => $sort_order,
			'is_active' => 1,
		));

		return (bool) $this->db->insert_id();
	}

	public function get_item($id)
	{
		if (!$this->has_table())
		{
			return NULL;
		}

		$id = (int) $id;
		if ($id < 1)
		{
			return NULL;
		}

		$row = $this->db->where('id', $id)->get(self::TABLE)->row_array();
		return !empty($row) ? $row : NULL;
	}

	public function update_item($id, $label_text, $insert_text = '', $row_group = 1)
	{
		if (!$this->has_table())
		{
			return FALSE;
		}

		$id = (int) $id;
		if ($id < 1 || !$this->get_item($id))
		{
			return FALSE;
		}

		$label_text = trim((string) $label_text);
		if ($label_text === '')
		{
			return FALSE;
		}

		$insert_text = trim((string) $insert_text);
		if ($insert_text === '')
		{
			$insert_text = $label_text.' -';
		}

		$row_group = (int) $row_group;
		if ($row_group < 1 || $row_group > 2)
		{
			$row_group = 1;
		}

		$this->db->where('id', $id)->update(self::TABLE, array(
			'label_text' => $label_text,
			'insert_text' => $insert_text,
			'row_group' => $row_group,
		));

		return TRUE;
	}

	public function delete_item($id)
	{
		if (!$this->has_table())
		{
			return FALSE;
		}

		$id = (int) $id;
		if ($id < 1)
		{
			return FALSE;
		}

		$this->db->where('id', $id)->delete(self::TABLE);
		return $this->db->affected_rows() > 0;
	}

	/**
	 * Replace all rows with built-in defaults (fixes broken Urdu encoding).
	 */
	public function reseed_defaults()
	{
		if (!$this->has_table())
		{
			return FALSE;
		}

		$this->db->query('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
		$this->db->truncate(self::TABLE);

		foreach ($this->default_items() as $item)
		{
			$this->db->insert(self::TABLE, array(
				'label_text' => $item['label_text'],
				'insert_text' => $item['insert_text'],
				'row_group' => $item['row_group'],
				'sort_order' => $item['sort_order'],
				'is_active' => 1,
			));
		}

		return $this->db->count_all(self::TABLE) > 0;
	}

	protected function default_items()
	{
		$items = array();
		$sort = 10;
		$row1 = array(
			array('گیرا چورس', 'گیرا چورس -'),
			array('گیرا گول', 'گیرا گول -'),
			array('کالر کف', 'کالر کف -'),
			array('کف', 'کف -'),
			array('ہاف بین گول', 'ہاف بین گول -'),
			array('ہاف بین چورس', 'ہاف بین چورس -'),
			array('بین پٹی ایک اینچ', 'بین پٹی ایک اینچ -'),
			array('چاک پٹی پر کاج بٹن', 'چاک پٹی پر کاج بٹن -'),
			array('بین پٹی سوا', 'بین پٹی سوا -'),
			array('باذٰٰو گول', 'باذٰٰو گول -'),
		);
		foreach ($row1 as $pair)
		{
			$items[] = array(
				'id' => 0,
				'label_text' => $pair[0],
				'insert_text' => $pair[1],
				'row_group' => 1,
				'sort_order' => $sort,
				'is_active' => 1,
			);
			$sort += 10;
		}

		$sort = 10;
		$row2 = array(
			array('اوف وائٹ', 'اوف وائٹ -'),
			array('بلیک', 'بلیک -'),
			array('گرے', 'گرے -'),
			array('گرین', 'گرین -'),
			array('سفید', 'سفید -'),
			array('ڈزائن والا', 'ڈزائن والا -'),
			array('زنک', 'زنک -'),
			array('براون', ' براون -'),
			array('میرون', ' میرون -'),
			array('نیوی', 'نیوی  -'),
			array('لائٹ گرے', 'لائٹ گرے -'),
		);
		foreach ($row2 as $pair)
		{
			$items[] = array(
				'id' => 0,
				'label_text' => $pair[0],
				'insert_text' => $pair[1],
				'row_group' => 2,
				'sort_order' => $sort,
				'is_active' => 1,
			);
			$sort += 10;
		}

		return $items;
	}
}
