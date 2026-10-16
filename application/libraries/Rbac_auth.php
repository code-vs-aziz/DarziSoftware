<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_auth
{
  protected $CI;
  protected $public_routes = array('login/index', 'login/process', 'login/logout', 'language/set');

  public function __construct()
  {
    $this->CI =& get_instance();
    $this->CI->load->helper('url');
    $this->CI->load->library('session');
    $this->CI->load->model('Rbac_model');
  }

  public function user()
  {
    return (string) $this->CI->session->userdata('user');
  }

  public function is_logged_in()
  {
    return $this->user() !== '';
  }

  public function can($permission_key)
  {
    return $this->has_permission($permission_key);
  }

  public function has_permission($permission_key)
  {
    return $this->CI->Rbac_model->user_has_permission($this->user(), $permission_key);
  }

  public function require_login()
  {
    if ($this->is_logged_in())
    {
      return TRUE;
    }
    redirect('Login');
    return FALSE;
  }

  public function require_permission($permission_key)
  {
    if (!$this->require_login())
    {
      return FALSE;
    }
    if ($permission_key === '' || $this->has_permission($permission_key))
    {
      return TRUE;
    }
    $this->deny();
    return FALSE;
  }

  public function deny()
  {
    $this->CI->output->set_status_header(403);
    $this->CI->load->view('access_denied');
    exit;
  }

  public function enforce_current_route()
  {
    if (is_cli())
    {
      return TRUE;
    }
    $class = strtolower((string) $this->CI->router->class);
    $method = strtolower((string) $this->CI->router->method);
    $route = $class.'/'.$method;
    if ($class === 'cron')
    {
      return TRUE;
    }
    // Public website JSON API uses customer Bearer tokens (see Api_Controller).
    // erp_sync uses session or staff sync Bearer token (see api/Erp_sync.php).
    if ($class === 'auth' || $class === 'measurements' || $class === 'content' || $class === 'shop' || $class === 'erp_sync')
    {
      $directory = strtolower((string) $this->CI->router->directory);
      if ($directory === 'api/' || $directory === 'api')
      {
        return TRUE;
      }
    }
    if (in_array($route, $this->public_routes, TRUE))
    {
      return TRUE;
    }
    if (!$this->is_logged_in())
    {
      redirect('Login');
      return FALSE;
    }
    $permission = $this->route_permission($class, $method);
    if ($permission !== '' && !$this->has_permission($permission))
    {
      $this->deny();
      return FALSE;
    }
    return TRUE;
  }

  public function route_permission($class, $method)
  {
    if ($class === 'welcome')
    {
      return $method === 'report' ? 'reports.menu' : 'dashboard.view';
    }
    if ($class === 'rbac')
    {
      return 'rbac.admin';
    }
    if ($class === 'configuration')
    {
      return 'configuration.manage';
    }
    if ($class === 'notifications')
    {
      return 'configuration.manage';
    }
    if ($class === 'broadcast')
    {
      // Same operators as Customer Messages; optional finer key broadcast.manage also exists.
      return 'configuration.manage';
    }
    if ($class === 'website')
    {
      if (strpos($method, 'product') !== FALSE || strpos($method, 'categor') !== FALSE)
      {
        return 'website.products';
      }
      if (strpos($method, 'order') !== FALSE)
      {
        return 'website.orders';
      }
      if (strpos($method, 'submission') !== FALSE || strpos($method, 'measurement') !== FALSE)
      {
        return 'website.measurements';
      }
      if (strpos($method, 'content') !== FALSE || strpos($method, 'guide') !== FALSE || strpos($method, 'faq') !== FALSE || strpos($method, 'settings') !== FALSE)
      {
        return 'website.content';
      }
      return 'website.manage';
    }
    if ($class === 'report_voice')
    {
      return 'reports.menu';
    }
    if ($class === 'customer')
    {
      return 'customer.manage';
    }
    if ($class === 'measurement')
    {
      return 'measurement.manage';
    }
    if ($class === 'employee')
    {
      return 'employee.manage';
    }
    if ($class === 'suit')
    {
      return 'suit.manage';
    }
    if ($class === 'style' || $class === 'style_edit')
    {
      return 'style.manage';
    }
    if ($class === 'embroidery')
    {
      return 'embroidery.manage';
    }
    if ($class === 'button')
    {
      return 'button.manage';
    }
    if ($class === 'transfer')
    {
      if ($method === 'transfer_table' || $method === 'transfer_table_data')
      {
        $stage = strtoupper((string) $this->CI->uri->segment($method === 'transfer_table_data' ? 4 : 3));
        if ($stage === '' || !in_array($stage, array('HO', 'CUT', 'HO2', 'STIT'), TRUE))
        {
          $stage = strtoupper((string) $this->CI->uri->segment(4));
        }
        $map = array(
          'HO' => 'transfer.booking',
          'CUT' => 'transfer.cutting',
          'HO2' => 'transfer.ho2',
          'STIT' => 'transfer.stitching',
        );
        return isset($map[$stage]) ? $map[$stage] : 'transfer.manage';
      }
      if ($method === 'order_statusd')
      {
        return 'order.ready_delivered';
      }
      if (strpos($method, 'order_status') !== FALSE)
      {
        return 'order.status';
      }
      return 'transfer.manage';
    }
    if ($class === 'order')
    {
      if (strpos($method, 'order_view') !== FALSE)
      {
        return 'order.detail';
      }
      if (strpos($method, 'order_issue') !== FALSE || strpos($method, 'order_deliver') !== FALSE || $method === 'process_del')
      {
        return 'order.ready_delivered';
      }
      if (strpos($method, 'print_blank') !== FALSE)
      {
        return 'order.blank_print';
      }
      return 'order.create';
    }
    if ($class === 'cash')
    {
      return $this->cash_permission($method);
    }
    if ($class === 'employee_rep')
    {
      return $method === 'booking_rep' || strpos($method, 'booking') !== FALSE ? 'report.booking' : 'report.employee_report';
    }
    return '';
  }

  protected function cash_permission($method)
  {
    $map = array(
      'index' => 'cash.receipt',
      'process' => 'cash.receipt',
      'reprtemployee_ledger' => 'report.employee_ledger',
      'employee_ledger' => 'report.employee_ledger',
      'employee_ledger_pro' => 'report.employee_ledger',
      'reprtcustomer_ledger' => 'report.customer_ledger',
      'customer_ledger' => 'report.customer_ledger',
      'customer_ledger_pro' => 'report.customer_ledger',
      'reprtcash_ledger' => 'report.cash_ledger',
      'cash_ledger' => 'report.cash_ledger',
      'cash_ledger_pro' => 'report.cash_ledger',
      'cash_ledger_data' => 'report.cash_ledger',
      'reprtexp_ledger' => 'report.expenses',
      'exp_ledger' => 'report.expenses',
      'exp_ledger_pro' => 'report.expenses',
      'reprtcustomer_pending' => 'report.pending_payment',
      'customer_pending_ledger' => 'report.pending_payment',
      'customer_pending_ledger_pr' => 'report.pending_payment',
      'process_status' => 'report.pending_payment',
      'reprtall_sale_employee_wise' => 'report.all_sale',
      'all_sale_employee_wise' => 'report.all_sale',
      'all_sale_employee_sie_pro' => 'report.all_sale',
      'reprtall_delivery_order' => 'report.delivery_order',
      'all_delivery_order' => 'report.delivery_order',
      'all_del_ord_pro' => 'report.delivery_order',
      'rep_customer_sale' => 'report.customer_total_sale',
      'customer_sale_pro' => 'report.customer_total_sale',
      'reprtfinancial_dashboard' => 'report.financial_dashboard',
      'financial_dashboard' => 'report.financial_dashboard',
      'financial_dashboard_pro' => 'report.financial_dashboard',
      'reprtsales_expense_compare' => 'report.sales_expense',
      'sales_expense_compare' => 'report.sales_expense',
      'sales_expense_compare_pro' => 'report.sales_expense',
      'reprtcash_flow_receivable' => 'report.cash_flow',
      'cash_flow_receivable' => 'report.cash_flow',
      'cash_flow_receivable_pro' => 'report.cash_flow',
    );
    return isset($map[$method]) ? $map[$method] : 'cash.receipt';
  }
}
?>
