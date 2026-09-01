<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_query_model extends CI_Model

{
  function get_staff_employees()
  {
    $this->db->select('empno, name, phone, type, status');
    $this->db->from('employee');
    $this->db->where('type', 'Staff');
    $this->db->group_start();
    $this->db->where('status', 'Active');
    $this->db->or_where('status IS NULL', NULL, FALSE);
    $this->db->or_where('status', '');
    $this->db->group_end();
    $this->db->order_by('name', 'ASC');
    $q = $this->db->get();
    if ($q->num_rows() < 1)
    {
      return array();
    }
    return $q->result();
  }

  /**
   * Active / Inactive / All — for report employee dropdowns.
   */
  function normalize_employee_status_filter($status_filter)
  {
    $status_filter = trim((string) $status_filter);
    if ($status_filter === 'Active' || $status_filter === 'Inactive')
    {
      return $status_filter;
    }
    return 'All';
  }

  /**
   * Employees for ledger/report pickers, optionally filtered by status.
   */
  function get_employees_for_ledger($status_filter = 'All')
  {
    $status_filter = $this->normalize_employee_status_filter($status_filter);
    $this->db->select('empno, name, phone, type, status');
    $this->db->from('employee');
    if ($status_filter === 'Active')
    {
      $this->db->group_start();
      $this->db->where('status', 'Active');
      $this->db->or_where('status IS NULL', NULL, FALSE);
      $this->db->or_where('status', '');
      $this->db->group_end();
    }
    elseif ($status_filter === 'Inactive')
    {
      $this->db->where('status', 'Inactive');
    }
    $this->db->order_by('name', 'ASC');
    $q = $this->db->get();
    if ($q->num_rows() < 1)
    {
      return array();
    }
    return $q->result();
  }

  function get_employee_by_empno($empno)
  {
    $empno = (int) $empno;
    if ($empno <= 0)
    {
      return NULL;
    }
    $this->db->from('employee');
    $this->db->where('empno', $empno);
    $this->db->limit(1);
    $q = $this->db->get();
    return ($q->num_rows() > 0) ? $q->row() : NULL;
  }

  function is_staff_employee($empno)
  {
    $empno = (int) $empno;
    if ($empno <= 0)
    {
      return FALSE;
    }
    $this->db->from('employee');
    $this->db->where('empno', $empno);
    $this->db->where('type', 'Staff');
    $this->db->group_start();
    $this->db->where('status', 'Active');
    $this->db->or_where('status IS NULL', NULL, FALSE);
    $this->db->or_where('status', '');
    $this->db->group_end();
    return ($this->db->count_all_results() > 0);
  }

  /**
   * Active employees for order transfer assignment (HO -> Cutting, HO2 -> Stitching).
   */
  function get_transfer_employees($from_dept)
  {
    $from_dept = strtoupper(trim((string) $from_dept));
    $type = '';
    if ($from_dept === 'HO')
    {
      $type = 'Cutting';
    }
    elseif ($from_dept === 'HO2')
    {
      $type = 'Stitching';
    }
    else
    {
      return array();
    }

    $this->db->select('empno, name, phone, type, status');
    $this->db->from('employee');
    $this->db->where('type', $type);
    $this->db->group_start();
    $this->db->where('status', 'Active');
    $this->db->or_where('status IS NULL', NULL, FALSE);
    $this->db->or_where('status', '');
    $this->db->group_end();
    $this->db->order_by('name', 'ASC');
    $q = $this->db->get();
    if ($q->num_rows() < 1)
    {
      return array();
    }
    return $q->result();
  }

  function is_transfer_employee($empno, $from_dept)
  {
    $empno = (int) $empno;
    if ($empno <= 0)
    {
      return FALSE;
    }
    $from_dept = strtoupper(trim((string) $from_dept));
    $type = '';
    if ($from_dept === 'HO')
    {
      $type = 'Cutting';
    }
    elseif ($from_dept === 'HO2')
    {
      $type = 'Stitching';
    }
    else
    {
      return FALSE;
    }

    $this->db->from('employee');
    $this->db->where('empno', $empno);
    $this->db->where('type', $type);
    $this->db->group_start();
    $this->db->where('status', 'Active');
    $this->db->or_where('status IS NULL', NULL, FALSE);
    $this->db->or_where('status', '');
    $this->db->group_end();
    return ($this->db->count_all_results() > 0);
  }

}
