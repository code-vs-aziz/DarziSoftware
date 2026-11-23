<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model
{
	const SETTINGS_TABLE = 'notification_settings';
	const QUEUE_TABLE = 'notification_queue';

	public function has_tables()
	{
		return $this->db->table_exists(self::SETTINGS_TABLE)
			&& $this->db->table_exists(self::QUEUE_TABLE);
	}

	public function get_settings()
	{
		if (!$this->db->table_exists(self::SETTINGS_TABLE))
		{
			return $this->default_settings();
		}

		$row = $this->db->where('id', 1)->get(self::SETTINGS_TABLE)->row_array();
		return $row ? $this->merge_defaults($row) : $this->default_settings();
	}

	public function save_settings(array $data)
	{
		if (!$this->db->table_exists(self::SETTINGS_TABLE))
		{
			return FALSE;
		}

		$payload = array(
			'id' => 1,
			'enabled_order_booked' => !empty($data['enabled_order_booked']) ? 1 : 0,
			'enabled_order_ready' => !empty($data['enabled_order_ready']) ? 1 : 0,
			'enabled_payment_reminder' => !empty($data['enabled_payment_reminder']) ? 1 : 0,
			'enabled_delivery_confirm' => !empty($data['enabled_delivery_confirm']) ? 1 : 0,
			'auto_open_whatsapp' => !empty($data['auto_open_whatsapp']) ? 1 : 0,
			'dispatch_mode' => $this->sanitize_dispatch_mode($data['dispatch_mode']),
			'payment_reminder_days_before' => max(0, min(30, (int) $data['payment_reminder_days_before'])),
			'template_order_booked' => trim((string) $data['template_order_booked']),
			'template_order_ready' => trim((string) $data['template_order_ready']),
			'template_payment_reminder' => trim((string) $data['template_payment_reminder']),
			'template_delivery_confirm' => trim((string) $data['template_delivery_confirm']),
		);

		$exists = (int) $this->db->where('id', 1)->count_all_results(self::SETTINGS_TABLE);
		if ($exists > 0)
		{
			$this->db->where('id', 1)->update(self::SETTINGS_TABLE, $payload);
			return TRUE;
		}

		$this->db->insert(self::SETTINGS_TABLE, $payload);
		return TRUE;
	}

	public function queue_insert(array $row)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return 0;
		}

		$this->ensure_attachment_column();

		$now = date('Y-m-d H:i:s');
		$data = array(
			'event_type' => (string) $row['event_type'],
			'order_id' => (int) $row['order_id'],
			'customer_id' => (int) $row['customer_id'],
			'phone' => isset($row['phone']) ? (string) $row['phone'] : '',
			'message' => isset($row['message']) ? (string) $row['message'] : '',
			'channel' => isset($row['channel']) ? (string) $row['channel'] : 'whatsapp',
			'status' => isset($row['status']) ? (string) $row['status'] : 'pending',
			'whatsapp_url' => isset($row['whatsapp_url']) ? (string) $row['whatsapp_url'] : '',
			'error_message' => isset($row['error_message']) ? (string) $row['error_message'] : '',
			'trigger_source' => isset($row['trigger_source']) ? (string) $row['trigger_source'] : '',
			'created_at' => $now,
			'sent_at' => isset($row['sent_at']) ? (string) $row['sent_at'] : NULL,
		);
		if ($this->has_attachment_column())
		{
			$data['attachment_path'] = isset($row['attachment_path']) ? (string) $row['attachment_path'] : NULL;
		}

		$this->db->insert(self::QUEUE_TABLE, $data);
		return (int) $this->db->insert_id();
	}

	/**
	 * Ensure PDF/document queue column exists (safe for older installs).
	 */
	public function ensure_attachment_column()
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return FALSE;
		}
		if ($this->has_attachment_column(TRUE))
		{
			return TRUE;
		}
		$this->db->query('ALTER TABLE `'.self::QUEUE_TABLE.'` ADD COLUMN `attachment_path` varchar(512) DEFAULT NULL AFTER `whatsapp_url`');
		// CI field-name cache + our static flag must refresh after ALTER.
		if (isset($this->db->data_cache['field_names'][self::QUEUE_TABLE]))
		{
			unset($this->db->data_cache['field_names'][self::QUEUE_TABLE]);
		}
		return $this->has_attachment_column(TRUE);
	}

	public function has_attachment_column($refresh = FALSE)
	{
		static $has = NULL;
		if ($refresh)
		{
			$has = NULL;
		}
		if ($has !== NULL)
		{
			return $has;
		}
		$has = $this->db->table_exists(self::QUEUE_TABLE)
			&& $this->db->field_exists('attachment_path', self::QUEUE_TABLE);
		return $has;
	}

	public function update_queue_row($id, array $data)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return FALSE;
		}

		$this->db->where('id', (int) $id)->update(self::QUEUE_TABLE, $data);
		return TRUE;
	}

	public function get_queue_row($id)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return NULL;
		}

		return $this->db->where('id', (int) $id)->get(self::QUEUE_TABLE)->row();
	}

	public function recent_duplicate_exists($event_type, $order_id, $minutes = 360)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return FALSE;
		}

		$minutes = max(1, (int) $minutes);
		$since = date('Y-m-d H:i:s', time() - ($minutes * 60));
		$this->db->where('event_type', (string) $event_type);
		$this->db->where('order_id', (int) $order_id);
		$this->db->where('created_at >=', $since);
		$this->db->where_in('status', array('pending', 'sent'));
		return $this->db->count_all_results(self::QUEUE_TABLE) > 0;
	}

	public function get_recent_queue($limit = 50)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return array();
		}

		return $this->db
			->order_by('id', 'DESC')
			->limit(max(1, (int) $limit))
			->get(self::QUEUE_TABLE)
			->result();
	}

	public function get_pending_queue($limit = 20)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return array();
		}

		return $this->db
			->where('status', 'pending')
			->order_by('id', 'ASC')
			->limit(max(1, (int) $limit))
			->get(self::QUEUE_TABLE)
			->result();
	}

	public function get_stats()
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return array('total' => 0, 'sent' => 0, 'pending' => 0, 'failed' => 0, 'skipped' => 0);
		}

		$rows = $this->db
			->select('status, COUNT(*) AS cnt', FALSE)
			->group_by('status')
			->get(self::QUEUE_TABLE)
			->result();

		$stats = array('total' => 0, 'sent' => 0, 'pending' => 0, 'failed' => 0, 'skipped' => 0);
		foreach ($rows as $row)
		{
			$key = (string) $row->status;
			$cnt = (int) $row->cnt;
			$stats['total'] += $cnt;
			if (isset($stats[$key]))
			{
				$stats[$key] = $cnt;
			}
		}

		return $stats;
	}

	public function get_pending_for_order($order_id, $event_type)
	{
		if (!$this->db->table_exists(self::QUEUE_TABLE))
		{
			return NULL;
		}

		return $this->db
			->where('order_id', (int) $order_id)
			->where('event_type', (string) $event_type)
			->where('status', 'pending')
			->order_by('id', 'DESC')
			->limit(1)
			->get(self::QUEUE_TABLE)
			->row();
	}

	public function restore_professional_templates()
	{
		if (!$this->db->table_exists(self::SETTINGS_TABLE))
		{
			return FALSE;
		}

		$defaults = $this->default_settings();
		$this->db->where('id', 1)->update(self::SETTINGS_TABLE, array(
			'template_order_booked' => $defaults['template_order_booked'],
			'template_order_ready' => $defaults['template_order_ready'],
			'template_payment_reminder' => $defaults['template_payment_reminder'],
			'template_delivery_confirm' => $defaults['template_delivery_confirm'],
		));

		return TRUE;
	}

	protected function sanitize_dispatch_mode($mode)
	{
		$mode = strtolower(trim((string) $mode));
		$allowed = array('whatsapp_bridge', 'whatsapp_link', 'log');
		return in_array($mode, $allowed, TRUE) ? $mode : 'whatsapp_bridge';
	}

	protected function merge_defaults(array $row)
	{
		$defaults = $this->default_settings();
		foreach ($defaults as $key => $value)
		{
			if (!isset($row[$key]) || $row[$key] === '')
			{
				$row[$key] = $value;
			}
		}
		return $row;
	}

	protected function default_settings()
	{
		return array(
			'id' => 1,
			'enabled_order_booked' => 1,
			'enabled_order_ready' => 1,
			'enabled_payment_reminder' => 1,
			'enabled_delivery_confirm' => 1,
			'auto_open_whatsapp' => 1,
			'dispatch_mode' => 'whatsapp_bridge',
			'payment_reminder_days_before' => 1,
			'template_order_booked' => "*{shop_name}*\n━━━━━━━━━━━━━━\nالسلام علیکم {customer_name},\n\nآپ کا آرڈر کامیابی سے رجسٹر ہو گیا۔\n\n📋 *Order Details*\nOrder No: #{order_no}\nNap #: {naap_no}\n{order_items}\nTotal Bill: Rs {bill_total}\nAdvance Paid: Rs {advance}\nBalance: Rs {balance}\nReturn Date: {return_date}\n\n📍 {shop_address}\n📞 {shop_phone}\n🌐 {shop_website}\n\n{copyright}",
			'template_order_ready' => "*{shop_name}*\n━━━━━━━━━━━━━━\nالسلام علیکم {customer_name},\n\nآپ کا آرڈر *تیار* ہے — وصول کر لیں۔\n\nOrder #: {order_no}\nNap #: {naap_no}\n{order_items} | Bill: Rs {bill_total}\nTotal price: Rs {bill_total}\n\n📍 {shop_address}\n📞 {shop_phone}\n🌐 {shop_website}\n\n{copyright}",
			'template_payment_reminder' => "*{shop_name}*\n━━━━━━━━━━━━━━\nالسلام علیکم {customer_name},\n\nیاد دہانی: Order #{order_no} پر بقایا رقم باقی ہے۔\n\nNap #: {naap_no}\nPending Balance: Rs {balance}\nDue Date: {due_date}\n{order_items} | Total Bill: Rs {bill_total}\n\n📍 {shop_address}\n📞 {shop_phone}\n🌐 {shop_website}\n\n{copyright}",
			'template_delivery_confirm' => "*{shop_name}*\n━━━━━━━━━━━━━━\nالسلام علیکم {customer_name},\n\nشکریہ! Order #{order_no} کامیابی سے deliver ہو گیا۔\n\nNap #: {naap_no}\n{order_items} | Bill: Rs {bill_total}\nAdvance: Rs {advance} | Balance: Rs {balance}\n\nدوبارہ تشریف لائیں — خوشی ہوگی۔\n\n📍 {shop_address}\n📞 {shop_phone}\n🌐 {shop_website}\n\n{copyright}",
		);
	}
}
