<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Free report voice/text commands — rules first, optional local Ollama AI.
 */
class Report_voice_service
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper('url');
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public function resolve($text)
	{
		$text = trim((string) $text);
		if ($text === '')
		{
			return array('ok' => FALSE, 'error' => 'empty');
		}

		$parsed = $this->parse_with_rules($text);
		if ($parsed !== NULL)
		{
			$parsed['source'] = 'rules';
			return $this->build_result($parsed);
		}

		if ($this->ollama_enabled())
		{
			$ai = $this->parse_with_ollama($text);
			if ($ai !== NULL)
			{
				$ai['source'] = 'ollama';
				return $this->build_result($ai);
			}
		}

		return array(
			'ok' => FALSE,
			'error' => 'unrecognized',
			'hint' => t('report_voice_hint'),
		);
	}

	protected function parse_with_rules($text)
	{
		$lower = $this->normalize_command_text($text);

		$days = $this->extract_days($lower);
		$range = $this->date_range_from_days($days, $lower);

		$report = '';
		// More specific ledgers first
		if ($this->contains_any($lower, array(
			'customer ledger', 'customer report', 'customer ka', 'customer ki',
			'کسٹمر لیجر', 'کسٹمر لیجڑ', 'کسٹمر رپورٹ', 'کسٹمر کا لیجر', 'گاہک لیجر', 'گاہک رپورٹ',
		)) || (strpos($lower, 'customer') !== FALSE && strpos($lower, 'ledger') !== FALSE)
			|| ((strpos($lower, 'کسٹمر') !== FALSE || strpos($lower, 'گاہک') !== FALSE)
				&& (strpos($lower, 'لیجر') !== FALSE || strpos($lower, 'رپورٹ') !== FALSE)))
		{
			$report = 'customer_ledger';
		}
		elseif ($this->contains_any($lower, array(
			'employee ledger', 'employee report', 'mulazim', 'staff ledger',
			'ملازم لیجر', 'ایمپلائی لیجر', 'سٹاف لیجر', 'ملازم رپورٹ',
		)))
		{
			$report = 'employee_ledger';
		}
		elseif ($this->contains_any($lower, array(
			'cash ledger', 'cash report', 'rozana cash', 'daily cash', 'cash ka', 'cash ki',
			'system ledger', 'sistem ledger', 'system report', 'shop ledger', 'cashbook', 'cash book',
			'کیش لیجر', 'کیش رپورٹ', 'روزنامچہ', 'سسٹم لیجر', 'سسٹم رپورٹ', 'کیش بک', 'کیس لیجر',
		)))
		{
			$report = 'cash_ledger';
		}
		elseif ($this->contains_any($lower, array(
			'delivery', 'deliver', 'delivered order', 'delivery order',
			'ڈیلیوری', 'ڈلیوری', 'ڈیلیور', 'سپلائی آرڈر',
		)))
		{
			$report = 'delivery';
		}
		elseif ($this->contains_any($lower, array(
			'expense', 'kharcha', 'expenses sheet', 'exp sheet',
			'خرچہ', 'خرچے', 'اخراجات', 'ایکسپینس',
		)))
		{
			$report = 'expense';
		}
		elseif ($this->contains_any($lower, array(
			'pending', 'udaar', 'pending payment', 'bqi', 'bakia', 'bqi payment',
			'زیر التوا', 'بقایا', 'ادھار', 'پینڈنگ',
		)))
		{
			$report = 'pending';
		}
		elseif ($this->contains_any($lower, array(
			'all sale', 'employee sale', 'sale report', 'sales report', 'sale employee',
			'سیل رپورٹ', 'سیلز رپورٹ', 'کل سیل',
		)))
		{
			$report = 'all_sale';
		}
		elseif ($this->contains_any($lower, array(
			'financial', 'finance dashboard', 'financial dashboard', 'dashboard report',
			'فنانشل', 'مالی ڈیش', 'مالی رپورٹ',
		)))
		{
			$report = 'financial';
		}
		elseif ($this->contains_any($lower, array(
			'sales expense', 'sale vs expense', 'sales vs expense',
			'سیل بمقابلہ', 'سیلز بمقابلہ',
		)))
		{
			$report = 'sales_expense';
		}
		elseif ($this->contains_any($lower, array('cash flow', 'receivable', 'کیش فلو', 'وصولی')))
		{
			$report = 'cash_flow';
		}
		elseif ($this->contains_any($lower, array('booking', 'booking report', 'order book', 'بکنگ')))
		{
			$report = 'booking';
		}
		elseif ($this->contains_any($lower, array('لیجر', 'ledger', 'cash', 'کیش', 'rozana', 'روزنامچہ', 'سسٹم'))
			&& !$this->contains_any($lower, array('customer', 'کسٹمر', 'گاہک', 'employee', 'ملازم', 'flow', 'فلو')))
		{
			// Generic “ledger / system report” → cash ledger
			$report = 'cash_ledger';
		}

		if ($report === '')
		{
			return NULL;
		}

		return array(
			'report' => $report,
			'from' => $range['from'],
			'to' => $range['to'],
			'days' => $days,
		);
	}

	/**
	 * Normalize Urdu/Roman text for keyword matching.
	 */
	protected function normalize_command_text($text)
	{
		$text = trim((string) $text);
		$text = str_replace(
			array('۔', '،', '؟', '!', 'ـ', "\xC2\xA0"),
			array(' ', ',', ' ', ' ', '', ' '),
			$text
		);
		// Arabic-Indic digits → ASCII
		$text = strtr($text, array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
			'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
			'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
		));
		// Common Urdu spelling variants / Alef forms
		$text = str_replace(array('ي', 'ك', 'ھ'), array('ی', 'ک', 'ہ'), $text);
		$text = preg_replace('/\s+/u', ' ', $text);
		return mb_strtolower($text, 'UTF-8');
	}

	protected function extract_days($lower)
	{
		// Urdu: پچھلے ایک ہفتے / پچھلی ایک ہفتے / گزشتہ ہفتہ
		if (preg_match('/(?:پچھل[ےئی]?|گزشتہ|آخری)\s*(?:ایک|1)?\s*ہفت/u', $lower)
			|| preg_match('/(?:ایک|1)\s*ہفت/u', $lower)
			|| $this->contains_any($lower, array('is hafte', 'this week', 'last week', 'pichle hafte', 'pichlay hafte', 'pichli haftay', 'ek hafte', 'aik hafte', 'hafta', 'ہفتہ', 'ہفتے')))
		{
			return 7;
		}

		// Urdu word numbers + دن
		$urdu_nums = array(
			'ایک' => 1, 'دو' => 2, 'تین' => 3, 'چار' => 4, 'پانچ' => 5,
			'چھ' => 6, 'چھے' => 6, 'سات' => 7, 'آٹھ' => 8, 'نو' => 9, 'دس' => 10,
			'پندرہ' => 15, 'بیس' => 20, 'تیس' => 30,
		);
		if (preg_match('/(?:پچھل[ےئی]?|گزشتہ|آخری)?\s*('.implode('|', array_keys($urdu_nums)).')\s*دن/u', $lower, $m))
		{
			$word = $m[1];
			return isset($urdu_nums[$word]) ? max(1, (int) $urdu_nums[$word]) : 7;
		}

		if (preg_match('/(?:last|pichle|pichlay|pichli|previous|پچھل[ےئی]?)\s+(\d{1,3})\s*(?:din|day|days|دن)/u', $lower, $m))
		{
			return max(1, (int) $m[1]);
		}
		if (preg_match('/(\d{1,3})\s*(?:din|day|days|دن)\s*(?:ka|ki|ke|کے|کی|کا|record|report|data|ledger|لیجر|رپورٹ)?/u', $lower, $m))
		{
			return max(1, (int) $m[1]);
		}
		if ($this->contains_any($lower, array('aaj', 'today', 'roz ka', 'daily', 'آج')))
		{
			return 0;
		}
		if ($this->contains_any($lower, array('kal', 'yesterday', 'کل')))
		{
			return -1;
		}
		if ($this->contains_any($lower, array('is mahine', 'this month', 'mahina', 'month', 'اس مہینے', 'اس مہینہ', 'مہینہ')))
		{
			return 30;
		}

		return 7;
	}

	protected function date_range_from_days($days, $lower)
	{
		$today = date('Y-m-d');

		if ($this->contains_any($lower, array('kal', 'yesterday', 'کل')) || $days === -1)
		{
			$y = date('Y-m-d', strtotime('-1 day'));
			return array('from' => $y, 'to' => $y);
		}

		if ($days === 0 || $this->contains_any($lower, array('aaj ka', 'today', 'aaj ki', 'roz ka', 'آج')))
		{
			return array('from' => $today, 'to' => $today);
		}

		if ($this->contains_any($lower, array('is mahine', 'this month', 'اس مہینے', 'اس مہینہ')))
		{
			return array('from' => date('Y-m-01'), 'to' => $today);
		}

		// "last N days/week" → inclusive window of N days ending today
		$span = max(1, (int) $days);
		$from = date('Y-m-d', strtotime('-'.($span - 1).' days'));
		return array('from' => $from, 'to' => $today);
	}

	protected function build_result(array $parsed)
	{
		$report = isset($parsed['report']) ? (string) $parsed['report'] : '';
		$from = isset($parsed['from']) ? (string) $parsed['from'] : date('Y-m-d', strtotime('-7 days'));
		$to = isset($parsed['to']) ? (string) $parsed['to'] : date('Y-m-d');

		if (!$this->can_report($report))
		{
			return array('ok' => FALSE, 'error' => 'permission');
		}

		$url = $this->report_url($report, $from, $to);
		if ($url === '')
		{
			return array('ok' => FALSE, 'error' => 'unrecognized');
		}

		return array(
			'ok' => TRUE,
			'report' => $report,
			'from' => $from,
			'to' => $to,
			'url' => $url,
			'label' => $this->report_label($report),
			'message' => sprintf(t('report_voice_opening'), $this->report_label($report), date('d M Y', strtotime($from)), date('d M Y', strtotime($to))),
			'source' => isset($parsed['source']) ? $parsed['source'] : 'rules',
			'needs_form' => ($report === 'employee_ledger' || $report === 'customer_ledger'),
		);
	}

	protected function report_url($report, $from, $to)
	{
		switch ($report)
		{
			case 'cash_ledger':
				return site_url('Cash/cash_ledger/'.$from.'/'.$to);
			case 'delivery':
				return site_url('Cash/all_delivery_order/'.$from.'/'.$to.'/0');
			case 'expense':
				return site_url('Cash/exp_ledger/'.$from.'/'.$to.'/');
			case 'pending':
				return site_url('Cash/customer_pending_ledger/'.$from.'/'.$to);
			case 'all_sale':
				return site_url('Cash/all_Sale_employee_wise/'.$from.'/'.$to.'/0');
			case 'financial':
				return site_url('Cash/financial_dashboard/'.$from.'/'.$to.'/daily');
			case 'sales_expense':
				return site_url('Cash/sales_expense_compare/'.$from.'/'.$to.'/daily');
			case 'cash_flow':
				return site_url('Cash/cash_flow_receivable/'.$from.'/'.$to.'/daily');
			case 'employee_ledger':
				return site_url('Cash/reprtemployee_ledger');
			case 'customer_ledger':
				return site_url('Cash/reprtcustomer_ledger');
			case 'booking':
				return site_url('employee_rep/booking_ledreport/'.$from.'/'.$to);
			default:
				return '';
		}
	}

	protected function report_label($report)
	{
		$map = array(
			'cash_ledger' => t('report_voice_cash_ledger'),
			'delivery' => t('report_voice_delivery'),
			'expense' => t('report_voice_expense'),
			'pending' => t('report_voice_pending'),
			'all_sale' => t('report_voice_all_sale'),
			'financial' => t('report_voice_financial'),
			'sales_expense' => t('report_voice_sales_expense'),
			'cash_flow' => t('report_voice_cash_flow'),
			'employee_ledger' => t('report_employee_ledger'),
			'customer_ledger' => t('report_customer_ledger'),
			'booking' => t('report_booking'),
		);
		return isset($map[$report]) ? $map[$report] : $report;
	}

	protected function can_report($report)
	{
		if (!isset($this->CI->rbac) || !is_object($this->CI->rbac))
		{
			return TRUE;
		}

		$perm_map = array(
			'cash_ledger' => 'report.cash_ledger',
			'delivery' => 'report.delivery_order',
			'expense' => 'report.expenses',
			'pending' => 'report.pending_payment',
			'all_sale' => 'report.all_sale',
			'financial' => 'report.financial_dashboard',
			'sales_expense' => 'report.sales_expense',
			'cash_flow' => 'report.cash_flow',
			'employee_ledger' => 'report.employee_ledger',
			'customer_ledger' => 'report.customer_ledger',
			'booking' => 'report.booking',
		);

		if (!isset($perm_map[$report]))
		{
			return FALSE;
		}

		return $this->CI->rbac->can($perm_map[$report]);
	}

	protected function ollama_enabled()
	{
		return function_exists('dz_env_bool') && dz_env_bool('OLLAMA_ENABLED', FALSE);
	}

	protected function parse_with_ollama($text)
	{
		$base = function_exists('dz_env') ? rtrim((string) dz_env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/') : 'http://127.0.0.1:11434';
		$model = function_exists('dz_env') ? (string) dz_env('OLLAMA_MODEL', 'llama3.2') : 'llama3.2';

		$prompt = 'You parse tailoring shop report requests. Reply with ONLY valid JSON, no markdown. '
			.'Fields: report (one of: cash_ledger, delivery, expense, pending, all_sale, financial, sales_expense, cash_flow, employee_ledger, customer_ledger, booking), '
			.'from (YYYY-MM-DD), to (YYYY-MM-DD). '
			.'Today is '.date('Y-m-d').". User said: ".$text;

		$ch = curl_init($base.'/api/generate');
		if ($ch === FALSE)
		{
			return NULL;
		}

		$body = json_encode(array(
			'model' => $model,
			'prompt' => $prompt,
			'stream' => FALSE,
			'options' => array('temperature' => 0.1),
		));

		curl_setopt_array($ch, array(
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
			CURLOPT_CONNECTTIMEOUT => 3,
			CURLOPT_TIMEOUT => 45,
		));

		$raw = curl_exec($ch);
		curl_close($ch);

		if ($raw === FALSE || $raw === '')
		{
			return NULL;
		}

		$outer = json_decode($raw, TRUE);
		if (!is_array($outer) || !isset($outer['response']))
		{
			return NULL;
		}

		$json_text = trim((string) $outer['response']);
		$json_text = preg_replace('/^```json\s*|\s*```$/', '', $json_text);
		$data = json_decode($json_text, TRUE);
		if (!is_array($data) || !isset($data['report']))
		{
			return NULL;
		}

		return array(
			'report' => (string) $data['report'],
			'from' => isset($data['from']) ? (string) $data['from'] : date('Y-m-d', strtotime('-7 days')),
			'to' => isset($data['to']) ? (string) $data['to'] : date('Y-m-d'),
		);
	}

	protected function contains_any($text, array $needles)
	{
		foreach ($needles as $needle)
		{
			if ($needle !== '' && strpos($text, $needle) !== FALSE)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
}
