<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_query_model extends CI_Model

{
  function count_all_customers()
  {
    return (int) $this->db->count_all('customer');
  }

  function get_cash_customer_options()
  {
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    $cache_key = 'cash_customer_options';
    $cached = $this->cache->get($cache_key);
    if ($cached !== FALSE)
    {
      return $cached;
    }

    $this->db->select('code, man_napno, name');
    $this->db->from('customer');
    $this->db->order_by('man_napno', 'ASC');
    $q = $this->db->get();
    $data = $q->num_rows() > 0 ? $q->result() : array();
    $this->cache->save($cache_key, $data, 120);
    return $data;
  }

  function get_customer_list_page($limit, $offset)
  {
    $this->db->select('code, name, Phone, man_napno, DOB');
    $this->db->from('customer');
    $this->db->order_by('code', 'DESC');
    $this->db->limit((int) $limit, (int) $offset);
    $q = $this->db->get();
    if ($q->num_rows() > 0)
    {
      return $q->result();
    }
    return array();
  }

  function get_measurement_flags_for_codes(array $codes)
  {
    $codes = array_values(array_unique(array_filter(array_map('intval', $codes))));
    if ($codes === array())
    {
      return array();
    }
    $this->db->distinct();
    $this->db->select('customer_code');
    $this->db->from('measurement');
    $this->db->where_in('customer_code', $codes);
    $q = $this->db->get();
    $out = array();
    foreach ($q->result() as $row)
    {
      $out[(int) $row->customer_code] = TRUE;
    }
    return $out;
  }

  function get_primary_measurement_codes_for_codes(array $codes)
  {
    $codes = array_values(array_unique(array_filter(array_map('intval', $codes))));
    if ($codes === array())
    {
      return array();
    }
    $this->db->select('customer_code, MIN(code) AS measurement_code', FALSE);
    $this->db->from('measurement');
    $this->db->where_in('customer_code', $codes);
    $this->db->group_by('customer_code');
    $q = $this->db->get();
    $out = array();
    foreach ($q->result() as $row)
    {
      $out[(int) $row->customer_code] = (int) $row->measurement_code;
    }
    return $out;
  }

  function get_measurements_for_order($customer_code = 0, $measurement_code = 0)
  {
    $customer_code = (int) $customer_code;
    $measurement_code = (int) $measurement_code;
    $this->db->select('measurement.*, measurement.remarks AS MREMARKS, measurement.code AS MCODE, customer.code AS Customerno, customer.man_napno AS MANNAAPNO, customer.name AS name, customer.name AS customer_name', FALSE);
    $this->db->from('measurement');
    $this->db->join('customer', 'measurement.customer_code = customer.code');
    if ($measurement_code > 0)
    {
      $this->db->where('measurement.code', $measurement_code);
    }
    elseif ($customer_code > 0)
    {
      $this->db->where('customer.code', $customer_code);
    }
    else
    {
      return array();
    }
    $this->db->order_by('measurement.code', 'ASC');
    $q = $this->db->get();
    if ($q->num_rows() < 1)
    {
      return array();
    }
    return $q->result();
  }

  protected function _apply_customer_search($term_raw)
  {
    $term_raw = trim((string) $term_raw);
    if ($term_raw === '')
    {
      return;
    }
    $digits = preg_replace('/\D+/', '', $term_raw);
    $this->db->group_start();
    $this->db->where('man_napno', $term_raw);
    $this->db->or_where('Phone', $term_raw);
    $this->db->or_where('cnic', $term_raw);
    if ($digits !== '' && $digits !== $term_raw)
    {
      $this->db->or_where('Phone', $digits);
      $this->db->or_where('cnic', $digits);
    }
    $esc = $this->db->escape_like_str($term_raw);
    $this->db->or_like('man_napno', $esc, 'after');
    $this->db->or_like('Phone', $esc, 'after');
    $this->db->or_like('cnic', $esc, 'after');
    $this->db->or_like('name', $esc, 'both');
    $this->db->group_end();
  }

  /**
   * Same rules as customer search, for queries that already JOIN `customer` (alias customer.).
   */
  protected function _apply_joined_customer_search($term_raw)
  {
    $term_raw = trim((string) $term_raw);
    if ($term_raw === '')
    {
      return;
    }
    $digits = preg_replace('/\D+/', '', $term_raw);
    $c = 'customer.';
    $this->db->group_start();
    $this->db->where($c.'man_napno', $term_raw);
    $this->db->or_where($c.'Phone', $term_raw);
    $this->db->or_where($c.'cnic', $term_raw);
    if ($digits !== '' && $digits !== $term_raw)
    {
      $this->db->or_where($c.'Phone', $digits);
      $this->db->or_where($c.'cnic', $digits);
    }
    $esc = $this->db->escape_like_str($term_raw);
    $this->db->or_like($c.'man_napno', $esc, 'after');
    $this->db->or_like($c.'Phone', $esc, 'after');
    $this->db->or_like($c.'cnic', $esc, 'after');
    $this->db->or_like($c.'name', $esc, 'both');
    $this->db->group_end();
  }

  /**
   * Customer-style filter on flat list rows (e.g. view oderdtl): name, phone, naap, optional order #.
   */
  protected function _apply_order_detail_row_search($term_raw)
  {
    $term_raw = trim((string) $term_raw);
    if ($term_raw === '')
    {
      return;
    }
    $digits = preg_replace('/\D+/', '', $term_raw);
    $this->db->group_start();
    $this->db->where('man_napno', $term_raw);
    $this->db->or_where('Phone', $term_raw);
    if ($digits !== '' && $digits !== $term_raw)
    {
      $this->db->or_where('Phone', $digits);
    }
    if ($digits !== '' && ctype_digit($digits))
    {
      $this->db->or_where('ORDERNO', $digits);
    }
    $esc = $this->db->escape_like_str($term_raw);
    $this->db->or_like('man_napno', $esc, 'after');
    $this->db->or_like('Phone', $esc, 'after');
    $this->db->or_like('name', $esc, 'both');
    $this->db->group_end();
  }

  function count_search_customers($term)
  {
    if (trim((string) $term) === '')
    {
      return 0;
    }
    $this->db->from('customer');
    $this->_apply_customer_search($term);
    return (int) $this->db->count_all_results();
  }

  function search_customers_paged($term, $limit, $offset)
  {
    if (trim((string) $term) === '')
    {
      return array();
    }
    $this->db->select('code, name, Phone, man_napno, DOB');
    $this->db->from('customer');
    $this->_apply_customer_search($term);
    $this->db->order_by('code', 'DESC');
    $this->db->limit((int) $limit, (int) $offset);
    $q = $this->db->get();
    if ($q->num_rows() > 0)
    {
      return $q->result();
    }
    return array();
  }

  /**
   * Count open order lines at a transfer stage (same rows as transfer_table list).
   */

}
