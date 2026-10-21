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

  /**
   * Flexible customer match: name/naap/cnic contains; phone matches anywhere
   * (partial digits without network code, spaces/dashes ignored).
   */
  protected function _apply_customer_search($term_raw)
  {
    $this->_apply_phone_flexible_search($term_raw, '', TRUE);
  }

  /**
   * Same rules as customer search, for queries that already JOIN `customer` (alias customer.).
   */
  protected function _apply_joined_customer_search($term_raw)
  {
    $this->_apply_phone_flexible_search($term_raw, 'customer.', FALSE);
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
    $this->_apply_phone_flexible_search($term_raw, '', FALSE, FALSE);
    if ($digits !== '' && ctype_digit($digits))
    {
      $this->db->or_where('ORDERNO', $digits);
      $this->db->or_like('ORDERNO', $this->db->escape_like_str($digits), 'both');
    }
    $this->db->group_end();
  }

  /**
   * Shared flexible match for customer / joined customer tables.
   *
   * @param string $term_raw
   * @param string $prefix table prefix e.g. "customer."
   * @param bool   $wrap_group wrap in group_start/group_end
   * @param bool   $include_cnic
   */
  protected function _apply_phone_flexible_search($term_raw, $prefix = '', $wrap_group = TRUE, $include_cnic = TRUE)
  {
    $term_raw = trim((string) $term_raw);
    if ($term_raw === '')
    {
      return;
    }
    $p = (string) $prefix;
    $digits = preg_replace('/\D+/', '', $term_raw);
    $esc = $this->db->escape_like_str($term_raw);

    if ($wrap_group)
    {
      $this->db->group_start();
    }

    $this->db->or_where($p.'man_napno', $term_raw);
    $this->db->or_where($p.'Phone', $term_raw);
    if ($include_cnic)
    {
      $this->db->or_where($p.'cnic', $term_raw);
    }

    // Contains match on raw text (name + ids + phone as typed)
    $this->db->or_like($p.'man_napno', $esc, 'both');
    $this->db->or_like($p.'Phone', $esc, 'both');
    $this->db->or_like($p.'name', $esc, 'both');
    if ($include_cnic)
    {
      $this->db->or_like($p.'cnic', $esc, 'both');
    }

    if ($digits !== '')
    {
      $esc_d = $this->db->escape_like_str($digits);
      $this->db->or_where($p.'Phone', $digits);
      $this->db->or_where($p.'man_napno', $digits);
      if ($include_cnic)
      {
        $this->db->or_where($p.'cnic', $digits);
      }
      $this->db->or_like($p.'Phone', $esc_d, 'both');
      $this->db->or_like($p.'man_napno', $esc_d, 'both');
      if ($include_cnic)
      {
        $this->db->or_like($p.'cnic', $esc_d, 'both');
      }

      // Digits-only phone: ignore spaces/dashes/+ and match partial (e.g. 0407042 → 03060407042)
      if (strlen($digits) >= 4)
      {
        $phone_digits = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(".$p."Phone,''),'-',''),' ',''),'+',''),'.',''),'(','')";
        $phone_digits = "REPLACE(".$phone_digits.",')','')";
        $variants = array($digits);
        // leading 0 dropped / added
        if ($digits[0] === '0' && strlen($digits) > 4)
        {
          $variants[] = substr($digits, 1);
        }
        else
        {
          $variants[] = '0'.$digits;
        }
        // Pakistan country code 92
        if (strpos($digits, '92') === 0 && strlen($digits) >= 11)
        {
          $rest = substr($digits, 2);
          $variants[] = $rest;
          $variants[] = '0'.$rest;
        }
        elseif (strlen($digits) >= 10)
        {
          $variants[] = '92'.$digits;
          if ($digits[0] === '0')
          {
            $variants[] = '92'.substr($digits, 1);
          }
        }
        $variants = array_unique(array_filter($variants, function ($v) {
          return is_string($v) && $v !== '';
        }));
        foreach ($variants as $v)
        {
          $this->db->or_where($phone_digits.' LIKE '.$this->db->escape('%'.$v.'%'), NULL, FALSE);
        }
      }
    }

    if ($wrap_group)
    {
      $this->db->group_end();
    }
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
