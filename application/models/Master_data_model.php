<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_data_model extends CI_Model

{
  /**
   * Linux MySQL (lower_case_table_names=0) is case-sensitive.
   * Legacy code often passes TitleCase table names.
   */
  function normalize_table_name($tab_name)
  {
    if (is_array($tab_name))
    {
      $tab_name = reset($tab_name);
    }
    $tab_name = trim((string) $tab_name);
    if ($tab_name === '')
    {
      return $tab_name;
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

    $key = strtolower($tab_name);
    return isset($map[$key]) ? $map[$key] : $tab_name;
  }

  function get_dat($tab_name, $active_only = FALSE)
  {
    if (is_array($tab_name))
    {
      $tab_name = reset($tab_name);
    }
    $tab_name = $this->normalize_table_name($tab_name);
    $active_only = (bool) $active_only;
    $status_tables = array('embroidery', 'style', 'suit');

    $cache_ttl = NULL;
    $cache_tables = array('embroidery' => 300, 'style' => 300, 'suit' => 300, 'employee' => 120);
    if (isset($cache_tables[$tab_name]))
    {
      $cache_ttl = $cache_tables[$tab_name];
      $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
      $key = 'gatedata_getdat_'.$tab_name.($active_only ? '_active' : '_all');
      $cached = $this->cache->get($key);
      if ($cached !== FALSE)
      {
        return $cached;
      }
    }

    $this->db->select('*');
    if ($active_only && in_array($tab_name, $status_tables, TRUE) && $this->db->field_exists('is_active', $tab_name))
    {
      $this->db->where('(is_active = 1 OR is_active IS NULL)', NULL, FALSE);
    }
    $q = $this->db->get($tab_name);

    $data = array();
    if ($q->num_rows() > 0)
    {
      foreach ($q->result() as $row)
      {
        $data[] = $row;
      }
    }

    if ($cache_ttl !== NULL)
    {
      $this->cache->save($key, $data, $cache_ttl);
    }

    return $data;
  }

  /**
   * Clear file cache for master tables after add/edit (suit, style, etc.).
   */
  function clear_dat_cache($tab_name)
  {
    $tab_name = $this->normalize_table_name($tab_name);
    $cache_tables = array('embroidery', 'style', 'suit', 'employee');
    if ( ! in_array($tab_name, $cache_tables, TRUE))
    {
      return;
    }
    $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
    $this->cache->delete('gatedata_getdat_'.$tab_name);
    $this->cache->delete('gatedata_getdat_'.$tab_name.'_all');
    $this->cache->delete('gatedata_getdat_'.$tab_name.'_active');
  }

  /**
   * Active staff employees for order booking dropdown.
   */
 function get_dat_style()
  {
    $this->db->select('vol_name,CAST(vol_no AS UNSIGNED) as vol_no,CAST(design AS UNSIGNED) as design,DNO ');
	$this->db->from('style');
    if ($this->db->field_exists('is_active', 'style'))
    {
      $this->db->where('(is_active = 1 OR is_active IS NULL)', NULL, FALSE);
    }
    $this->db->order_by("vol_name,vol_no,design");
	$q = $this->db->get();
    $data = array();
    if($q->num_rows() > 0)
    {
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
    }
    return $data;
  }
 
   function get_dat_embroidery()
  {
    $this->db->select('LTRIM(RTRIM(embroidery_name)) AS embroidery_name,CAST(vol_no AS UNSIGNED) as vol_no,CAST(design AS UNSIGNED) as design,ENO');
	$this->db->from('embroidery');
    if ($this->db->field_exists('is_active', 'embroidery'))
    {
      $this->db->where('(is_active = 1 OR is_active IS NULL)', NULL, FALSE);
    }
    $this->db->order_by("LTRIM(RTRIM(embroidery_name)),vol_no,design");
	$q = $this->db->get();
    $data = array();
    if($q->num_rows() > 0)
    {
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
    }
    return $data;
  }
 
 
 
  function get_dat_where($tab_name,$where)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
    $tab_name = $this->normalize_table_name($tab_name);
    $this->db->select('*');
	$this->db->from($tab_name);
	$this->db->where($where);
	$q = $this->db->get();
	
    /* after we've made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {
      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data[] = $row;
      }
	
      return $data;
    }

    return array();
  }

  function get_max_id($field,$tab)
  {
    /* all the queries relating to the data we want to retrieve will go in here. */
 
    //$this->db->where($where_arr);
	$tab = $this->normalize_table_name($tab);
	$this->db->select_max($field);
	$q = $this->db->get($tab);

	
    /* after weve made the queries from the database, we will store them inside a variable called $data, and return the variable to the controller */
    if($q->num_rows() > 0)
    {

      // we will store the results in the form of class methods by using $q->result()
      // if you want to store them as an array you can use $q->result_array()
      foreach ($q->result() as $row)
      {
         $data = $row;
      }

      return $data->$field;
    }
  }

  
}
