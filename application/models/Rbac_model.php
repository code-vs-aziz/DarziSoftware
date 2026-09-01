<?php
class Rbac_model extends CI_Model
{
  public function permission_definitions()
  {
    return array(
      'dashboard.view' => array('label' => 'Dashboard', 'legacy_page' => NULL, 'category' => 'Dashboard'),
      'rbac.admin' => array('label' => 'RBAC Administration', 'legacy_page' => NULL, 'category' => 'Administration'),
      'configuration.manage' => array('label' => 'Business Configuration', 'legacy_page' => NULL, 'category' => 'Administration'),
      'customer.manage' => array('label' => 'Customer', 'legacy_page' => 'customer', 'category' => 'Master Data'),
      'measurement.manage' => array('label' => 'Measurement', 'legacy_page' => NULL, 'category' => 'Master Data'),
      'order.create' => array('label' => 'Order Create', 'legacy_page' => NULL, 'category' => 'Orders'),
      'order.status' => array('label' => 'Order Status', 'legacy_page' => 'Order Status', 'category' => 'Orders'),
      'order.detail' => array('label' => 'Order Detail', 'legacy_page' => 'Order Detail', 'category' => 'Orders'),
      'order.ready_delivered' => array('label' => 'Ready for Delivered', 'legacy_page' => 'Ready for Delivered', 'category' => 'Orders'),
      'order.blank_print' => array('label' => 'Blank Print', 'legacy_page' => 'Blank Print', 'category' => 'Orders'),
      'transfer.booking' => array('label' => 'Pending Booking', 'legacy_page' => 'Pending Booking', 'category' => 'Transfers'),
      'transfer.cutting' => array('label' => 'Pending Cutting', 'legacy_page' => 'Pending Cutting', 'category' => 'Transfers'),
      'transfer.ho2' => array('label' => 'Pending HO2', 'legacy_page' => 'Pending HO2', 'category' => 'Transfers'),
      'transfer.stitching' => array('label' => 'Pending Stitching', 'legacy_page' => 'Pending Stitching', 'category' => 'Transfers'),
      'transfer.manage' => array('label' => 'Transfer Orders', 'legacy_page' => NULL, 'category' => 'Transfers'),
      'cash.receipt' => array('label' => 'Cash Payment/Receipt', 'legacy_page' => 'Cash Payment/Receipt', 'category' => 'Cash'),
      'reports.menu' => array('label' => 'Reports', 'legacy_page' => 'Reports', 'category' => 'Reports'),
      'report.employee_ledger' => array('label' => 'Employee Ledger', 'legacy_page' => 'Employee Ledger', 'category' => 'Reports'),
      'report.customer_ledger' => array('label' => 'Customer Ledger', 'legacy_page' => 'Customer Ledger', 'category' => 'Reports'),
      'report.cash_ledger' => array('label' => 'Cash Ledger', 'legacy_page' => 'Cash Ledger', 'category' => 'Reports'),
      'report.expenses' => array('label' => 'Expenses Sheet', 'legacy_page' => 'Expenses Sheet', 'category' => 'Reports'),
      'report.pending_payment' => array('label' => 'Pending Payment Sheet', 'legacy_page' => 'Pending Payment Sheet', 'category' => 'Reports'),
      'report.all_sale' => array('label' => 'All Sale Report', 'legacy_page' => 'All Sale Report', 'category' => 'Reports'),
      'report.delivery_order' => array('label' => 'Delivery Order Sheet', 'legacy_page' => 'Backup', 'category' => 'Reports'),
      'report.customer_total_sale' => array('label' => 'Customer Total Sale', 'legacy_page' => 'Backup', 'category' => 'Reports'),
      'report.employee_report' => array('label' => 'Employee Report', 'legacy_page' => 'Backup', 'category' => 'Reports'),
      'report.booking' => array('label' => 'Booking', 'legacy_page' => 'Backup', 'category' => 'Reports'),
      'report.financial_dashboard' => array('label' => 'Financial Dashboard', 'legacy_page' => NULL, 'category' => 'Reports'),
      'report.sales_expense' => array('label' => 'Sales Vs Expense', 'legacy_page' => NULL, 'category' => 'Reports'),
      'report.cash_flow' => array('label' => 'Cash Flow Report', 'legacy_page' => NULL, 'category' => 'Reports'),
      'employee.manage' => array('label' => 'Employee', 'legacy_page' => 'Employee', 'category' => 'Master Data'),
      'suit.manage' => array('label' => 'Suit Type', 'legacy_page' => 'Suit Type', 'category' => 'Master Data'),
      'style.manage' => array('label' => 'Style', 'legacy_page' => 'Style', 'category' => 'Master Data'),
      'embroidery.manage' => array('label' => 'Embroidery', 'legacy_page' => 'Embroidery', 'category' => 'Master Data'),
      'button.manage' => array('label' => 'Button', 'legacy_page' => NULL, 'category' => 'Master Data'),
    );
  }

  public function ensure_seed_permissions()
  {
    if (!$this->db->table_exists('rbac_permissions'))
    {
      return;
    }
    foreach ($this->permission_definitions() as $key => $def)
    {
      $exists = $this->db->where('permission_key', $key)->count_all_results('rbac_permissions');
      if (!$exists)
      {
        $this->db->insert('rbac_permissions', array(
          'permission_key' => $key,
          'permission_label' => $def['label'],
          'legacy_page' => $def['legacy_page'],
          'category' => $def['category'],
          'is_active' => 1,
        ));
      }
    }
  }

  public function get_permissions()
  {
    $defs = $this->permission_definitions();
    if (!$this->db->table_exists('rbac_permissions'))
    {
      $out = array();
      foreach ($defs as $key => $def)
      {
        $def['permission_key'] = $key;
        $def['permission_label'] = $def['label'];
        $out[] = (object) $def;
      }
      return $out;
    }
    $this->ensure_seed_permissions();
    $this->db->order_by('category', 'ASC');
    $this->db->order_by('permission_label', 'ASC');
    return $this->db->get('rbac_permissions')->result();
  }

  public function get_permission($permission_key)
  {
    $defs = $this->permission_definitions();
    if (isset($defs[$permission_key]))
    {
      return $defs[$permission_key];
    }
    if ($this->db->table_exists('rbac_permissions'))
    {
      $row = $this->db->where('permission_key', $permission_key)->get('rbac_permissions')->row();
      if ($row)
      {
        return array('label' => $row->permission_label, 'legacy_page' => $row->legacy_page, 'category' => $row->category);
      }
    }
    return NULL;
  }

  public function get_users()
  {
    $this->db->select('UNAME');
    $this->db->order_by('UNAME', 'ASC');
    return $this->db->get('users')->result();
  }

  public function get_roles($active_only = FALSE)
  {
    if (!$this->db->table_exists('rbac_roles'))
    {
      return array();
    }
    if ($active_only)
    {
      $this->db->where('is_active', 1);
    }
    $this->db->order_by('role_name', 'ASC');
    return $this->db->get('rbac_roles')->result();
  }

  public function get_role($role_id)
  {
    if (!$this->db->table_exists('rbac_roles'))
    {
      return NULL;
    }
    return $this->db->where('id', (int) $role_id)->get('rbac_roles')->row();
  }

  public function save_role($data, $role_id = NULL)
  {
    if (!$this->db->table_exists('rbac_roles'))
    {
      return 0;
    }
    if ($role_id)
    {
      $this->db->where('id', (int) $role_id)->update('rbac_roles', $data);
      return (int) $role_id;
    }
    $this->db->insert('rbac_roles', $data);
    return (int) $this->db->insert_id();
  }

  public function set_role_permissions($role_id, array $permissions)
  {
    if (!$this->db->table_exists('rbac_role_permissions'))
    {
      return;
    }
    $role_id = (int) $role_id;
    $this->db->where('role_id', $role_id)->delete('rbac_role_permissions');
    foreach (array_unique($permissions) as $permission)
    {
      if ($permission !== '')
      {
        $this->db->insert('rbac_role_permissions', array('role_id' => $role_id, 'permission_key' => $permission));
      }
    }
  }

  public function get_role_permissions($role_id)
  {
    if (!$this->db->table_exists('rbac_role_permissions'))
    {
      return array();
    }
    $q = $this->db->select('permission_key')->where('role_id', (int) $role_id)->get('rbac_role_permissions');
    $out = array();
    foreach ($q->result() as $row)
    {
      $out[] = $row->permission_key;
    }
    return $out;
  }

  public function get_user_roles($username)
  {
    if (!$this->db->table_exists('rbac_user_roles') || !$this->db->table_exists('rbac_roles'))
    {
      return array();
    }
    $this->db->select('rbac_roles.*');
    $this->db->from('rbac_user_roles');
    $this->db->join('rbac_roles', 'rbac_roles.id = rbac_user_roles.role_id');
    $this->db->where('rbac_user_roles.username', $username);
    $this->db->order_by('rbac_roles.role_name', 'ASC');
    return $this->db->get()->result();
  }

  public function set_user_roles($username, array $role_ids)
  {
    if (!$this->db->table_exists('rbac_user_roles'))
    {
      return;
    }
    $this->db->where('username', $username)->delete('rbac_user_roles');
    foreach (array_unique(array_map('intval', $role_ids)) as $role_id)
    {
      if ($role_id > 0)
      {
        $this->db->insert('rbac_user_roles', array('username' => $username, 'role_id' => $role_id));
      }
    }
  }

  public function save_user($username, $password = '')
  {
    $username = trim((string) $username);
    $password = (string) $password;
    if ($username === '')
    {
      return array('ok' => FALSE, 'message' => 'Username is required.', 'created' => FALSE);
    }
    if ( ! preg_match('/^[A-Za-z0-9._-]{2,50}$/', $username))
    {
      return array('ok' => FALSE, 'message' => 'Username may only contain letters, numbers, dot, dash, or underscore (2–50 chars).', 'created' => FALSE);
    }

    $exists = $this->db->where('UNAME', $username)->count_all_results('users') > 0;
    if ($exists)
    {
      return array('ok' => FALSE, 'message' => 'Username already exists. Use Reset password on that user.', 'created' => FALSE);
    }

    if ($password === '')
    {
      return array('ok' => FALSE, 'message' => 'Password is required for a new user.', 'created' => FALSE);
    }

    $this->load->helper('password');
    $row = array('UNAME' => $username, 'UPASS' => app_password_hash($password));
    if ($this->db->field_exists('UEMAIL', 'users'))
    {
      $row['UEMAIL'] = $username.'@local';
    }
    $this->db->insert('users', $row);
    return array('ok' => TRUE, 'message' => 'User created.', 'created' => TRUE);
  }

  public function reset_password($username, $password)
  {
    $username = trim((string) $username);
    $password = (string) $password;
    if ($username === '')
    {
      return array('ok' => FALSE, 'message' => 'Username is required.');
    }
    if ($password === '')
    {
      return array('ok' => FALSE, 'message' => 'New password is required.');
    }
    if ( ! $this->db->where('UNAME', $username)->count_all_results('users'))
    {
      return array('ok' => FALSE, 'message' => 'User not found.');
    }
    $this->load->helper('password');
    $this->db->where('UNAME', $username)->update('users', array('UPASS' => app_password_hash($password)));
    return array('ok' => TRUE, 'message' => 'Password updated.');
  }

  /**
   * Remove login account and related role / legacy rights rows.
   */
  public function delete_user($username)
  {
    $username = trim((string) $username);
    if ($username === '')
    {
      return array('ok' => FALSE, 'message' => 'Username is required.');
    }
    if (strtolower($username) === 'alibaba')
    {
      return array('ok' => FALSE, 'message' => 'The default admin user cannot be deleted.');
    }
    if ( ! $this->db->where('UNAME', $username)->count_all_results('users'))
    {
      return array('ok' => FALSE, 'message' => 'User not found.');
    }

    if ($this->db->table_exists('rbac_user_roles'))
    {
      $this->db->where('username', $username)->delete('rbac_user_roles');
    }
    if ($this->db->table_exists('rightss'))
    {
      $this->db->where('USER', $username)->delete('rightss');
    }
    if ($this->db->table_exists('rights'))
    {
      $this->db->where('UNAME', $username)->delete('rights');
    }
    $this->db->where('UNAME', $username)->delete('users');
    return array('ok' => TRUE, 'message' => 'User deleted.');
  }

  public function count_super_admin_role_assignments()
  {
    $count = 0;
    if ($this->db->table_exists('users') && $this->db->where('UNAME', 'alibaba')->count_all_results('users'))
    {
      $count++;
    }
    if ( ! $this->db->table_exists('rbac_user_roles') || ! $this->db->table_exists('rbac_roles'))
    {
      return $count;
    }
    $this->db->from('rbac_user_roles');
    $this->db->join('rbac_roles', 'rbac_roles.id = rbac_user_roles.role_id');
    $this->db->where('LOWER(rbac_roles.role_name)', 'super admin');
    $this->db->where('rbac_roles.is_active', 1);
    return $count + (int) $this->db->count_all_results();
  }

  public function can_delete_user($username, $current_user)
  {
    $username = trim((string) $username);
    $current_user = trim((string) $current_user);
    if ($username === '')
    {
      return array('ok' => FALSE, 'message' => 'Username is required.');
    }
    if ($username === $current_user)
    {
      return array('ok' => FALSE, 'message' => 'You cannot delete your own account while logged in.');
    }
    if (strtolower($username) === 'alibaba')
    {
      return array('ok' => FALSE, 'message' => 'The default admin user cannot be deleted.');
    }
    if ( ! $this->db->where('UNAME', $username)->count_all_results('users'))
    {
      return array('ok' => FALSE, 'message' => 'User not found.');
    }
    if ($this->user_has_super_admin_role($username) && $this->count_super_admin_role_assignments() <= 1)
    {
      return array('ok' => FALSE, 'message' => 'Cannot delete the last Super Admin user.');
    }
    return array('ok' => TRUE, 'message' => '');
  }

  public function user_has_super_admin_role($username)
  {
    if (strtolower((string) $username) === 'alibaba')
    {
      return TRUE;
    }
    if ( ! $this->db->table_exists('rbac_user_roles') || ! $this->db->table_exists('rbac_roles'))
    {
      return FALSE;
    }
    $this->db->from('rbac_user_roles');
    $this->db->join('rbac_roles', 'rbac_roles.id = rbac_user_roles.role_id');
    $this->db->where('rbac_user_roles.username', $username);
    $this->db->where('LOWER(rbac_roles.role_name)', 'super admin');
    $this->db->where('rbac_roles.is_active', 1);
    return $this->db->count_all_results() > 0;
  }

  public function is_super_admin($username)
  {
    if (strtolower((string) $username) === 'alibaba')
    {
      return TRUE;
    }
    if (!$this->db->table_exists('rbac_user_roles') || !$this->db->table_exists('rbac_roles'))
    {
      return FALSE;
    }
    $this->db->from('rbac_user_roles');
    $this->db->join('rbac_roles', 'rbac_roles.id = rbac_user_roles.role_id');
    $this->db->where('rbac_user_roles.username', $username);
    $this->db->where('LOWER(rbac_roles.role_name)', 'super admin');
    $this->db->where('rbac_roles.is_active', 1);
    return $this->db->count_all_results() > 0;
  }

  public function user_has_permission($username, $permission_key)
  {
    $username = (string) $username;
    $permission_key = (string) $permission_key;
    if ($username === '')
    {
      return FALSE;
    }
    if ($permission_key === 'dashboard.view')
    {
      return TRUE;
    }
    if ($this->is_super_admin($username))
    {
      return TRUE;
    }
    if ($this->db->table_exists('rbac_user_roles') && $this->db->table_exists('rbac_role_permissions') && $this->db->table_exists('rbac_roles'))
    {
      $this->db->from('rbac_user_roles');
      $this->db->join('rbac_roles', 'rbac_roles.id = rbac_user_roles.role_id');
      $this->db->join('rbac_role_permissions', 'rbac_role_permissions.role_id = rbac_roles.id');
      $this->db->where('rbac_user_roles.username', $username);
      $this->db->where('rbac_roles.is_active', 1);
      $this->db->where('rbac_role_permissions.permission_key', $permission_key);
      if ($this->db->count_all_results() > 0)
      {
        return TRUE;
      }
    }
    return $this->legacy_user_has_permission($username, $permission_key);
  }

  public function legacy_user_has_permission($username, $permission_key)
  {
    if (!$this->db->table_exists('rightss'))
    {
      return FALSE;
    }
    $permission = $this->get_permission($permission_key);
    if (!$permission || empty($permission['legacy_page']))
    {
      return FALSE;
    }
    $this->db->where('USER', $username);
    $this->db->where('PAGE', $permission['legacy_page']);
    $this->db->where('ALLOW', 'X');
    return $this->db->count_all_results('rightss') > 0;
  }
}
?>
