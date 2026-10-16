<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Builds normalized report payloads (title, columns, rows, totals) for PDF/share.
 * Reuses existing Cash_ledger_model / Dashboard_data_model / Order_query_model.
 */
class Report_data_service
{
	/** @var CI_Controller */
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * @return array{ok:bool,error?:string,title?:string,columns?:array,rows?:array,totals?:array,summary?:array,meta?:array}
	 */
	public function build($key, $from, $to, array $options = array())
	{
		$key = trim((string) $key);
		$method = 'build_'.$key;
		if (!method_exists($this, $method))
		{
			// Map registry builder names that differ from key
			$map = array(
				'financial_dashboard' => 'build_financial_dashboard',
				'sales_expense' => 'build_sales_expense',
				'cash_flow' => 'build_cash_flow',
				'cash_ledger' => 'build_cash_ledger',
				'expenses' => 'build_expenses',
				'pending_payment' => 'build_pending_payment',
				'all_sale' => 'build_all_sale',
				'delivery' => 'build_delivery',
				'customer_sale' => 'build_customer_sale',
				'customer_total_sale' => 'build_customer_sale',
				'employee_report' => 'build_employee_report',
				'booking' => 'build_booking',
			);
			$method = isset($map[$key]) ? $map[$key] : '';
		}
		if ($method === '' || !method_exists($this, $method))
		{
			return array('ok' => FALSE, 'error' => 'unsupported_report');
		}
		return $this->{$method}($from, $to, $options);
	}

	protected function empty_payload($title, $from, $to)
	{
		return array(
			'ok' => TRUE,
			'title' => $title,
			'columns' => array(),
			'rows' => array(),
			'totals' => array(),
			'summary' => array(
				array('label' => t('report_date'), 'value' => $from.' → '.$to),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => TRUE),
		);
	}

	protected function build_delivery($from, $to, array $options = array())
	{
		$rows_raw = $this->CI->cash_ledger->get_data_report_del_order($from, $to);
		$rows = array();
		$total_suit = 0;
		$total_amount = 0;
		$sr = 0;
		foreach ($rows_raw as $p)
		{
			$sr++;
			$suit = isset($p->COunt_suit) ? (float) $p->COunt_suit : 0;
			$amt = isset($p->PAID) ? (float) $p->PAID : 0;
			$total_suit += $suit;
			$total_amount += $amt;
			$narration = 'Order #'.(isset($p->ORDERNO) ? $p->ORDERNO : '')
				.' · Naap '.(isset($p->man_napno) ? $p->man_napno : '')
				.(isset($p->narration) && $p->narration !== '' ? ' · '.$p->narration : '');
			$rows[] = array(
				$sr,
				isset($p->ENDATE) ? date('d-m-Y', strtotime($p->ENDATE)) : '',
				isset($p->CUSTOMERNAME) ? $p->CUSTOMERNAME : '',
				$narration,
				number_format($suit),
				number_format($amt, 2),
				isset($p->status) ? $p->status : 'Delivered',
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_delivery_order'),
			'columns' => array('SR', t('report_date'), t('report_customer'), t('report_narration'), 'Suit', t('report_amount'), 'Status'),
			'rows' => $rows,
			'totals' => array(
				array('label' => 'Suits', 'value' => number_format($total_suit)),
				array('label' => t('report_total_amount'), 'value' => number_format($total_amount, 2)),
				array('label' => 'Rows', 'value' => (string) count($rows)),
			),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_expenses($from, $to, array $options = array())
	{
		$type = isset($options['type']) ? (string) $options['type'] : 'All';
		$rows_raw = $this->CI->cash_ledger->get_data_exp($from, $to, $type === 'All' ? '' : $type);
		if (!is_array($rows_raw) && !is_object($rows_raw))
		{
			$rows_raw = array();
		}
		$type_labels = array('' => 'ALL', '3' => 'Shop Expense', '4' => 'House Expense', '5' => 'Nasir sb Expense', '6' => 'Extra Expense');
		$rows = array();
		$total = 0;
		$sr = 0;
		foreach ($rows_raw as $r)
		{
			$sr++;
			$amt = isset($r->amount) ? (float) $r->amount : 0;
			$total += abs($amt);
			$row_type = isset($r->type) ? (string) $r->type : '';
			$rows[] = array(
				$sr,
				isset($r->EDATE) ? date('d-m-Y', strtotime($r->EDATE)) : '',
				isset($r->narration) ? $r->narration : '',
				number_format(abs($amt), 2),
				isset($type_labels[$row_type]) ? $type_labels[$row_type] : $row_type,
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_expenses_sheet'),
			'columns' => array('SR', t('report_date'), t('report_narration'), t('report_amount'), 'Type'),
			'rows' => $rows,
			'totals' => array(
				array('label' => t('report_total'), 'value' => number_format($total, 2)),
			),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_all_sale($from, $to, array $options = array())
	{
		$rtype = isset($options['rtype']) ? (string) $options['rtype'] : '';
		$rows_raw = $this->CI->cash_ledger->get_data_report_all_month($from, $to, '', $rtype);
		$rows = array();
		$total = 0;
		$sr = 0;
		foreach ($rows_raw as $p)
		{
			$sr++;
			$line = (isset($p->suitrate) ? (float) $p->suitrate : 0)
				+ (isset($p->drate) ? (float) $p->drate : 0)
				+ (isset($p->erate) ? (float) $p->erate : 0);
			$total += $line;
			$rows[] = array(
				$sr,
				isset($p->ENDATE) ? date('d-m-Y', strtotime($p->ENDATE)) : '',
				isset($p->CUSTOMERNAME) ? $p->CUSTOMERNAME : '',
				isset($p->name) ? $p->name : '',
				isset($p->ORDERNO) ? $p->ORDERNO : '',
				isset($p->status) ? $p->status : '',
				number_format($line, 2),
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_all_sale'),
			'columns' => array('SR', t('report_date'), t('report_customer'), t('report_employee'), 'Order', 'Status', t('report_amount')),
			'rows' => $rows,
			'totals' => array(
				array('label' => t('report_total'), 'value' => number_format($total, 2)),
				array('label' => 'Rows', 'value' => (string) count($rows)),
			),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_pending_payment($from, $to, array $options = array())
	{
		$rows_raw = $this->CI->cash_ledger->get_pending_cash($from, $to);
		if (!is_array($rows_raw))
		{
			$rows_raw = array();
		}
		$rows = array();
		$total = 0;
		$sr = 0;
		foreach ($rows_raw as $p)
		{
			$sr++;
			$amt = 0;
			if (isset($p->pending))
			{
				$amt = (float) $p->pending;
			}
			elseif (isset($p->PENDING))
			{
				$amt = (float) $p->PENDING;
			}
			elseif (isset($p->balance))
			{
				$amt = (float) $p->balance;
			}
			$total += $amt;
			$rows[] = array(
				$sr,
				isset($p->name) ? $p->name : (isset($p->CUSTOMERNAME) ? $p->CUSTOMERNAME : ''),
				isset($p->man_napno) ? $p->man_napno : '',
				isset($p->ORDERNO) ? $p->ORDERNO : (isset($p->order_no) ? $p->order_no : ''),
				isset($p->NPAID_dATE) ? $p->NPAID_dATE : (isset($p->EDATE) ? $p->EDATE : ''),
				number_format($amt, 2),
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_pending_payment_sheet'),
			'columns' => array('SR', t('report_customer'), 'Naap', 'Order', t('report_date'), t('report_amount')),
			'rows' => $rows,
			'totals' => array(
				array('label' => t('report_total_pending_rows'), 'value' => (string) count($rows)),
				array('label' => t('report_amount'), 'value' => number_format($total, 2)),
			),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_cash_ledger($from, $to, array $options = array())
	{
		if (method_exists($this->CI->cash_ledger, 'get_cash_ledger_summary'))
		{
			$summary = $this->CI->cash_ledger->get_cash_ledger_summary($from, $to);
		}
		else
		{
			$summary = array();
		}
		$opening = method_exists($this->CI->cash_ledger, 'get_opeing_cash')
			? (float) $this->CI->cash_ledger->get_opeing_cash($from)
			: 0;
		$rows = array();
		if (is_array($summary))
		{
			foreach ($summary as $day)
			{
				$d = is_object($day) ? $day : (object) $day;
				$rows[] = array(
					isset($d->day) ? $d->day : (isset($d->EDATE) ? $d->EDATE : ''),
					isset($d->receipts) ? number_format((float) $d->receipts, 2) : '0.00',
					isset($d->payments) ? number_format((float) $d->payments, 2) : '0.00',
					isset($d->balance) ? number_format((float) $d->balance, 2) : '',
				);
			}
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_cash_ledger'),
			'columns' => array(t('report_date'), t('report_total_received'), t('report_payment'), 'Balance'),
			'rows' => $rows,
			'totals' => array(
				array('label' => 'Opening', 'value' => number_format($opening, 2)),
				array('label' => 'Days', 'value' => (string) count($rows)),
			),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_financial_dashboard($from, $to, array $options = array())
	{
		$this->CI->load->model('dashboard_data');
		$group = isset($options['group']) ? $options['group'] : 'daily';
		$summary = $this->CI->dashboard_data->get_financial_dashboard_summary($from, $to);
		$cmp = $this->CI->dashboard_data->get_financial_grouped_comparison($from, $to, $group);
		$totals = array();
		if (is_array($summary) || is_object($summary))
		{
			foreach ((array) $summary as $k => $v)
			{
				if (is_scalar($v))
				{
					$totals[] = array(
						'label' => (string) $k,
						'value' => is_numeric($v) ? number_format((float) $v, 2) : (string) $v,
					);
				}
			}
		}
		$rows = array();
		foreach ((array) $cmp as $row)
		{
			$row = is_object($row) ? (array) $row : $row;
			$rows[] = array_values($row);
		}
		$cols = array('Period');
		if (!empty($cmp))
		{
			$first = is_object($cmp[0]) ? (array) $cmp[0] : (array) $cmp[0];
			$cols = array_keys($first);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_financial'),
			'columns' => array_map('strval', $cols),
			'rows' => $rows,
			'totals' => $totals,
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows) && empty($totals)),
		);
	}

	protected function build_sales_expense($from, $to, array $options = array())
	{
		$this->CI->load->model('dashboard_data');
		$group = isset($options['group']) ? $options['group'] : 'daily';
		$data = $this->CI->dashboard_data->get_financial_grouped_comparison($from, $to, $group);
		$rows = array();
		$cols = array('Period', 'Sales', 'Expense', 'Net');
		foreach ((array) $data as $row)
		{
			$row = is_object($row) ? (array) $row : $row;
			if ($cols === array('Period', 'Sales', 'Expense', 'Net') && !empty($row))
			{
				$cols = array_keys($row);
			}
			$rows[] = array_values($row);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_sales_vs_expense'),
			'columns' => array_map('strval', $cols),
			'rows' => $rows,
			'totals' => array(),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_cash_flow($from, $to, array $options = array())
	{
		$this->CI->load->model('dashboard_data');
		$group = isset($options['group']) ? $options['group'] : 'daily';
		$data = $this->CI->dashboard_data->get_financial_cash_flow($from, $to, $group);
		$rows = array();
		$cols = array('Period', 'In', 'Out', 'Net');
		foreach ((array) $data as $row)
		{
			$row = is_object($row) ? (array) $row : $row;
			if (!empty($row))
			{
				$cols = array_keys($row);
			}
			$rows[] = array_values($row);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_cash_flow'),
			'columns' => array_map('strval', $cols),
			'rows' => $rows,
			'totals' => array(),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_customer_sale($from, $to, array $options = array())
	{
		$this->CI->load->model('order_query');
		$rows_raw = array();
		if (method_exists($this->CI->order_query, 'customer_wise_sale'))
		{
			$rows_raw = $this->CI->order_query->customer_wise_sale($from, $to);
		}
		elseif (method_exists($this->CI->cash_ledger, 'get_customer_wise_sale'))
		{
			$rows_raw = $this->CI->cash_ledger->get_customer_wise_sale($from, $to);
		}
		$rows = array();
		$total = 0;
		$sr = 0;
		foreach ((array) $rows_raw as $r)
		{
			$sr++;
			$r = is_object($r) ? $r : (object) $r;
			$amt = isset($r->total) ? (float) $r->total : (isset($r->TOTAL) ? (float) $r->TOTAL : 0);
			$total += $amt;
			$rows[] = array(
				$sr,
				isset($r->name) ? $r->name : '',
				isset($r->man_napno) ? $r->man_napno : '',
				number_format($amt, 2),
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_customer_total_sale'),
			'columns' => array('SR', t('report_customer'), 'Naap', t('report_amount')),
			'rows' => $rows,
			'totals' => array(array('label' => t('report_total'), 'value' => number_format($total, 2))),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_booking($from, $to, array $options = array())
	{
		$this->CI->load->model('order_query');
		$rows_raw = array();
		if (method_exists($this->CI->order_query, 'booking_report'))
		{
			$rows_raw = $this->CI->order_query->booking_report($from, $to);
		}
		$rows = array();
		$sr = 0;
		$total = 0;
		foreach ((array) $rows_raw as $r)
		{
			$sr++;
			$r = is_object($r) ? $r : (object) $r;
			$amt = isset($r->total) ? (float) $r->total : 0;
			$total += $amt;
			$rows[] = array(
				$sr,
				isset($r->EDATE) ? date('d-m-Y', strtotime($r->EDATE)) : '',
				isset($r->name) ? $r->name : '',
				isset($r->trnno) ? $r->trnno : '',
				number_format($amt, 2),
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_booking'),
			'columns' => array('SR', t('report_date'), t('report_customer'), 'Order', t('report_amount')),
			'rows' => $rows,
			'totals' => array(array('label' => t('report_total'), 'value' => number_format($total, 2))),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}

	protected function build_employee_report($from, $to, array $options = array())
	{
		$rows_raw = array();
		if (method_exists($this->CI->cash_ledger, 'get_data_employee_summary'))
		{
			$rows_raw = $this->CI->cash_ledger->get_data_employee_summary($from, $to);
		}
		$rows = array();
		$sr = 0;
		foreach ((array) $rows_raw as $r)
		{
			$sr++;
			$r = is_object($r) ? $r : (object) $r;
			$rows[] = array(
				$sr,
				isset($r->name) ? $r->name : '',
				isset($r->suits) ? $r->suits : (isset($r->qty) ? $r->qty : ''),
				isset($r->amount) ? number_format((float) $r->amount, 2) : '0.00',
			);
		}
		return array(
			'ok' => TRUE,
			'title' => t('report_employee_report'),
			'columns' => array('SR', t('report_employee'), 'Suits', t('report_amount')),
			'rows' => $rows,
			'totals' => array(array('label' => 'Employees', 'value' => (string) count($rows))),
			'summary' => array(
				array('label' => 'From', 'value' => date('d-m-Y', strtotime($from))),
				array('label' => 'To', 'value' => date('d-m-Y', strtotime($to))),
			),
			'meta' => array('from' => $from, 'to' => $to, 'empty' => empty($rows)),
		);
	}
}
