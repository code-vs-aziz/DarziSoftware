<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Resolves report catalog entries, date presets, and result URLs.
 */
class Report_registry
{
	/** @var CI_Controller */
	protected $CI;
	/** @var array */
	protected $catalog = array();
	/** @var array */
	protected $presets = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->config->load('reports', TRUE);
		$catalog = $this->CI->config->item('report_catalog', 'reports');
		$presets = $this->CI->config->item('report_date_presets', 'reports');
		$this->catalog = is_array($catalog) ? $catalog : array();
		$this->presets = is_array($presets) ? $presets : array();
	}

	public function all()
	{
		return $this->catalog;
	}

	public function get($key)
	{
		$key = trim((string) $key);
		return isset($this->catalog[$key]) ? $this->catalog[$key] : NULL;
	}

	public function presets()
	{
		return $this->presets;
	}

	/**
	 * @return array{from:string,to:string}|null
	 */
	public function resolve_preset($preset_key)
	{
		$preset_key = trim((string) $preset_key);
		if ($preset_key === '' || !isset($this->presets[$preset_key]))
		{
			return NULL;
		}
		$p = $this->presets[$preset_key];
		$today = new DateTime('today');

		if (isset($p['mode']) && $p['mode'] === 'this_month')
		{
			$from = (clone $today)->modify('first day of this month');
			return array('from' => $from->format('Y-m-d'), 'to' => $today->format('Y-m-d'));
		}
		if (isset($p['mode']) && $p['mode'] === 'last_month')
		{
			$from = (clone $today)->modify('first day of last month');
			$to = (clone $today)->modify('last day of last month');
			return array('from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'));
		}
		if (!empty($p['span']))
		{
			$day = (clone $today)->modify('-'.(int) $p['days'].' days');
			$d = $day->format('Y-m-d');
			return array('from' => $d, 'to' => $d);
		}
		$days = isset($p['days']) ? (int) $p['days'] : 0;
		$from = (clone $today)->modify('-'.$days.' days');
		return array('from' => $from->format('Y-m-d'), 'to' => $today->format('Y-m-d'));
	}

	public function can_access($key)
	{
		$item = $this->get($key);
		if (!$item)
		{
			return FALSE;
		}
		$perm = isset($item['permission']) ? $item['permission'] : '';
		if ($perm === '')
		{
			return TRUE;
		}
		return isset($this->CI->rbac) && is_object($this->CI->rbac) && $this->CI->rbac->can($perm);
	}

	/**
	 * Build absolute result or form URL for a report + dates.
	 */
	public function build_url($key, $from = '', $to = '', $extra = '')
	{
		$item = $this->get($key);
		if (!$item)
		{
			return '';
		}
		$needs = isset($item['needs']) ? $item['needs'] : array();
		if (in_array('form', $needs, TRUE) || $from === '' || $to === '')
		{
			$path = isset($item['form_url']) ? $item['form_url'] : '';
			return $path !== '' ? site_url($path) : '';
		}
		$path = isset($item['result_url']) ? $item['result_url'] : '';
		$path = str_replace(
			array('{from}', '{to}', '{extra}'),
			array(rawurlencode($from), rawurlencode($to), rawurlencode((string) $extra)),
			$path
		);
		return site_url($path);
	}

	public function validate_dates($from, $to)
	{
		$from = trim((string) $from);
		$to = trim((string) $to);
		if ($from === '' || $to === '')
		{
			return array('ok' => FALSE, 'error' => 'missing_dates');
		}
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))
		{
			return array('ok' => FALSE, 'error' => 'invalid_dates');
		}
		if ($from > $to)
		{
			return array('ok' => FALSE, 'error' => 'from_after_to');
		}
		$fromTs = strtotime($from);
		$toTs = strtotime($to);
		if ($fromTs === FALSE || $toTs === FALSE)
		{
			return array('ok' => FALSE, 'error' => 'invalid_dates');
		}
		// Guard runaway ranges (1 year for interactive reports — protects memory/timeouts)
		if (($toTs - $fromTs) > (86400 * 366))
		{
			return array('ok' => FALSE, 'error' => 'range_too_large');
		}
		return array('ok' => TRUE, 'from' => $from, 'to' => $to);
	}
}
