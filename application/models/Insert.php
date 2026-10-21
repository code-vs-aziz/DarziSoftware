<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Insert extends CI_Model
{
	protected function _normalize_table($tab)
	{
		$CI =& get_instance();
		if (isset($CI->master_data) && is_object($CI->master_data) && method_exists($CI->master_data, 'normalize_table_name'))
		{
			return $CI->master_data->normalize_table_name($tab);
		}

		static $map = array(
			'measurement' => 'measurement',
			'customer' => 'customer',
			'employee' => 'employee',
			'embroidery' => 'embroidery',
			'style' => 'style',
			'suit' => 'suit',
			'button' => 'button',
			'order_mst' => 'order_mst',
			'order_dtl' => 'order_dtl',
			'transfer_mst' => 'transfer_mst',
			'users' => 'users',
			'jmst' => 'jmst',
			'jdtl' => 'jdtl',
		);
		$key = strtolower(trim((string) $tab));
		return isset($map[$key]) ? $map[$key] : (string) $tab;
	}

	function insert_dat($data, $tab, $PAGE)
	{
		$tab = $this->_normalize_table($tab);
		$this->db->insert($tab, $data);
		$this->_clear_master_dat_cache($tab);
		$this->load->helper('url');
		$this->session->set_flashdata('suss', 'yes');
		redirect($PAGE);
	}

	function insert_dat1($data, $tab)
	{
		$tab = $this->_normalize_table($tab);
		$this->db->insert($tab, $data);
		$this->session->set_flashdata('suss', 'yes');
	}

	public function entry_update1($field, $id, $data, $table)
	{
		$table = $this->_normalize_table($table);
		$this->db->where($field, $id);
		$this->db->update($table, $data);
		$this->_clear_master_dat_cache($table);
		$this->session->set_flashdata('suss', 'yes');
	}

	protected function _clear_master_dat_cache($table)
	{
		$cache_tables = array('embroidery', 'style', 'suit', 'employee');
		if ( ! in_array($table, $cache_tables, TRUE))
		{
			return;
		}
		$CI =& get_instance();
		$CI->load->model('Master_data_model', 'master_data');
		$CI->master_data->clear_dat_cache($table);
	}

	public function entry_update1_transfer($id, $data, $table)
	{
		$table = $this->_normalize_table($table);
		$this->db->where('trnno', $id);
		$this->db->update($table, $data);
		$this->session->set_flashdata('suss', 'yes');
	}

	public function entry_update3_transfer($id, $id1, $data, $table)
	{
		$table = $this->_normalize_table($table);
		$this->db->where('order_id', $id);
		$this->db->where('sr', $id1);
		$this->db->update($table, $data);
		$this->session->set_flashdata('suss', 'yes');
	}

	function row_delete($field, $id, $table)
	{
		$this->db->delete('order_dtl');
	}
}
