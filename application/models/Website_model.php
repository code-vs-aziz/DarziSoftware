<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data access for Darzi Baba public website tables (same ERP database).
 */
class Website_model extends CI_Model
{
	public function has_tables()
	{
		return $this->db->table_exists('web_settings')
			&& $this->db->table_exists('web_customer_sessions');
	}

	public function get_settings()
	{
		if ( ! $this->db->table_exists('web_settings'))
		{
			return $this->default_settings();
		}
		$row = $this->db->where('id', 1)->get('web_settings')->row_array();
		return $row ? $row : $this->default_settings();
	}

	public function save_settings(array $data)
	{
		if ( ! $this->db->table_exists('web_settings'))
		{
			return FALSE;
		}
		$data['id'] = 1;
		$data['updated_at'] = date('Y-m-d H:i:s');
		$exists = $this->db->where('id', 1)->count_all_results('web_settings');
		if ($exists)
		{
			$this->db->where('id', 1)->update('web_settings', $data);
			return TRUE;
		}
		$this->db->insert('web_settings', $data);
		return TRUE;
	}

	public function get_home_sections($active_only = TRUE)
	{
		if ( ! $this->db->table_exists('web_sections'))
		{
			return array();
		}
		$this->db->from('web_sections')->where('page_key', 'home');
		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('sort_order', 'ASC')->get()->result_array();
	}

	public function get_faqs($active_only = TRUE)
	{
		if ( ! $this->db->table_exists('web_faqs'))
		{
			return array();
		}
		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('sort_order', 'ASC')->get('web_faqs')->result_array();
	}

	public function get_page_by_slug($slug)
	{
		if ( ! $this->db->table_exists('web_pages'))
		{
			return NULL;
		}
		return $this->db->where('slug', $slug)->where('is_published', 1)->get('web_pages')->row_array();
	}

	public function find_customer_by_man_napno($man_napno)
	{
		$man_napno = trim((string) $man_napno);
		if ($man_napno === '')
		{
			return NULL;
		}
		return $this->db
			->select('code, name, Phone, man_napno, city, Address, cnic')
			->from('customer')
			->where('man_napno', $man_napno)
			->limit(1)
			->get()
			->row_array();
	}

	/**
	 * Find by shop ناپ number or registered phone (handles 03… / 92… / spaced formats).
	 *
	 * @return array|null
	 */
	public function find_customer_by_identifier($identifier)
	{
		$identifier = trim((string) $identifier);
		if ($identifier === '')
		{
			return NULL;
		}

		$by_naap = $this->find_customer_by_man_napno($identifier);
		if ($by_naap)
		{
			return $by_naap;
		}

		$digits = preg_replace('/\D+/', '', $identifier);
		if ($digits === '' || strlen($digits) < 10)
		{
			return NULL;
		}

		$variants = $this->phone_match_variants($digits);
		$last10 = substr($digits, -10);
		if ($last10 === '' || strlen($last10) < 10)
		{
			return NULL;
		}

		$phone_expr = "REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(Phone,''), ' ', ''), '-', ''), '+', ''), '(', '')";
		$this->db
			->select('code, name, Phone, man_napno, city, Address, cnic')
			->from('customer')
			->group_start();

		foreach ($variants as $i => $variant)
		{
			if ($i === 0)
			{
				$this->db->where('Phone', $variant);
			}
			else
			{
				$this->db->or_where('Phone', $variant);
			}
			$this->db->or_where($phone_expr.' = '.$this->db->escape($variant), NULL, FALSE);
		}
		$this->db->or_where('RIGHT('.$phone_expr.', 10) = '.$this->db->escape($last10), NULL, FALSE);
		$this->db->group_end();

		$rows = $this->db->order_by('code', 'ASC')->limit(5)->get()->result_array();
		if ( ! $rows)
		{
			return NULL;
		}
		if (count($rows) === 1)
		{
			return $rows[0];
		}

		foreach ($rows as $row)
		{
			$row_digits = preg_replace('/\D+/', '', isset($row['Phone']) ? $row['Phone'] : '');
			if ($row_digits !== '' && substr($row_digits, -10) === $last10)
			{
				return $row;
			}
		}
		return $rows[0];
	}

	/**
	 * @param string $digits digits-only phone
	 * @return string[]
	 */
	protected function phone_match_variants($digits)
	{
		$digits = preg_replace('/\D+/', '', (string) $digits);
		if ($digits === '')
		{
			return array();
		}
		$out = array($digits);
		if (strpos($digits, '92') === 0 && strlen($digits) >= 12)
		{
			$out[] = '0'.substr($digits, 2);
			$out[] = substr($digits, 2);
		}
		elseif (isset($digits[0]) && $digits[0] === '0' && strlen($digits) >= 11)
		{
			$out[] = '92'.substr($digits, 1);
			$out[] = substr($digits, 1);
		}
		elseif (strlen($digits) === 10 && isset($digits[0]) && $digits[0] === '3')
		{
			$out[] = '0'.$digits;
			$out[] = '92'.$digits;
		}
		return array_values(array_unique($out));
	}

	public function get_customer_by_code($code)
	{
		$code = (int) $code;
		if ($code <= 0)
		{
			return NULL;
		}
		return $this->db
			->select('code, name, Phone, man_napno, city, Address, cnic')
			->from('customer')
			->where('code', $code)
			->limit(1)
			->get()
			->row_array();
	}

	public function get_primary_measurement($customer_code)
	{
		$customer_code = (int) $customer_code;
		if ($customer_code <= 0)
		{
			return NULL;
		}
		return $this->db
			->from('measurement')
			->where('customer_code', $customer_code)
			->order_by('code', 'ASC')
			->limit(1)
			->get()
			->row_array();
	}

	public function list_measurements_for_customer($customer_code)
	{
		$customer_code = (int) $customer_code;
		if ($customer_code <= 0)
		{
			return array();
		}
		return $this->db
			->select('code, EDATE, customer_code, remarks')
			->from('measurement')
			->where('customer_code', $customer_code)
			->order_by('code', 'ASC')
			->get()
			->result_array();
	}

	/**
	 * Tailoring orders for a customer (ERP order_mst / order_dtl).
	 * Read-only payload: suit stage, amount paid, and remaining balance.
	 * Cancelled orders (ostatus = 1) are omitted.
	 *
	 * @return array
	 */
	public function get_customer_tailoring_history($customer_code, $limit = 400)
	{
		$customer_code = (int) $customer_code;
		$limit = max(1, min(400, (int) $limit));
		if ($customer_code <= 0)
		{
			return array();
		}

		$this->db->select('order_mst.trnno, order_mst.EDATE, order_mst.RDATE, order_mst.qty, order_mst.total, order_mst.advance, order_mst.discount, order_mst.image');
		$this->db->from('order_mst');
		$this->db->join('measurement', 'measurement.code = order_mst.naap_no');
		$this->db->where('measurement.customer_code', $customer_code);
		$this->db->where('(order_mst.ostatus IS NULL OR order_mst.ostatus != 1)', NULL, FALSE);
		$this->db->order_by('order_mst.trnno', 'DESC');
		$this->db->limit($limit);
		$orders = $this->db->get()->result_array();
		if ( ! $orders)
		{
			return array();
		}

		$ids = array();
		foreach ($orders as $order)
		{
			$ids[] = (int) $order['trnno'];
		}

		$lines_by_order = array();
		// Prefer live suit.name — snap_suit_name is latin1 and often stored as ??? for Urdu.
		$this->db->select('order_dtl.order_trnno, order_dtl.trnno, order_dtl.type, order_dtl.snap_suit_name, order_dtl.SNO, order_dtl.suitrate, order_dtl.addprice, order_dtl.drate, order_dtl.erate, order_dtl.design, order_dtl.order_remarks, suit.name AS suit_name', FALSE);
		$this->db->from('order_dtl');
		$this->db->join('suit', 'suit.SNO = order_dtl.SNO', 'left');
		$this->db->where_in('order_dtl.order_trnno', $ids);
		$this->db->order_by('order_dtl.trnno', 'ASC');
		foreach ($this->db->get()->result_array() as $line)
		{
			$lines_by_order[(int) $line['order_trnno']][] = $line;
		}

		$transfer_by_line = array();
		$this->db->select('order_id, sr, dept, status, trnno');
		$this->db->from('transfer_mst');
		$this->db->where_in('order_id', $ids);
		$this->db->order_by('trnno', 'ASC');
		foreach ($this->db->get()->result_array() as $transfer)
		{
			$key = ((int) $transfer['order_id']).'-'.((int) $transfer['sr']);
			$transfer_by_line[$key] = $transfer;
		}

		$dmst_paid = $this->_sum_amounts_by_order('dmst', 'order_id', 'PAID', $ids);
		$cash_paid = $this->_sum_amounts_by_order('cash', 'order_no', 'amount', $ids);

		$out = array();
		foreach ($orders as $order)
		{
			$trnno = (int) $order['trnno'];
			$articles = array();
			$order_status = 'delivered';
			$lines = isset($lines_by_order[$trnno]) ? $lines_by_order[$trnno] : array();
			foreach ($lines as $line)
			{
				$line_sr = (int) $line['trnno'];
				$transfer = isset($transfer_by_line[$trnno.'-'.$line_sr]) ? $transfer_by_line[$trnno.'-'.$line_sr] : NULL;
				$status = $this->_customer_suit_status(
					$transfer ? $transfer['dept'] : '',
					$transfer ? $transfer['status'] : ''
				);
				if ($this->_customer_status_rank($status) < $this->_customer_status_rank($order_status))
				{
					$order_status = $status;
				}
				$label = $this->_public_suit_label($line);
				$design = trim((string) $line['design']);
				if ($design === '0' || $design === '0.0')
				{
					$design = '';
				}
				$articles[] = array(
					'name' => $label,
					'type' => (string) $line['type'],
					'design' => $design,
					'remarks' => trim((string) $line['order_remarks']),
					'rate' => round(
						(float) $line['suitrate'] + (float) $line['addprice'] + (float) $line['drate'] + (float) $line['erate'],
						2
					),
					'status' => $status,
				);
			}
			if ( ! $articles)
			{
				$order_status = 'booking';
			}

			$total = round((float) $order['total'], 2);
			$advance = round((float) $order['advance'], 2);
			$discount = round((float) $order['discount'], 2);
			$paid = round($advance + (isset($dmst_paid[$trnno]) ? $dmst_paid[$trnno] : 0) + (isset($cash_paid[$trnno]) ? $cash_paid[$trnno] : 0), 2);
			$balance = round($total - $advance - $discount - (isset($dmst_paid[$trnno]) ? $dmst_paid[$trnno] : 0) - (isset($cash_paid[$trnno]) ? $cash_paid[$trnno] : 0), 2);
			if ($balance < 0)
			{
				$balance = 0;
			}

			$out[] = array(
				'order_no' => $trnno,
				'order_date' => (string) $order['EDATE'],
				'delivery_date' => (string) $order['RDATE'],
				'qty' => (int) $order['qty'],
				'total' => $total,
				'discount' => $discount,
				'advance' => $advance,
				'paid' => $paid,
				'balance' => $balance,
				'status' => $order_status,
				'is_current' => ($order_status !== 'delivered'),
				'image_url' => $this->_public_order_image_url(isset($order['image']) ? $order['image'] : ''),
				'articles' => $articles,
			);
		}
		return $out;
	}

	/**
	 * Readable suit title for the customer site.
	 * snap_suit_name is often corrupted (???) because order_dtl uses latin1.
	 *
	 * @param array $line
	 * @return string
	 */
	protected function _public_suit_label(array $line)
	{
		$candidates = array(
			isset($line['suit_name']) ? $line['suit_name'] : '',
			isset($line['snap_suit_name']) ? $line['snap_suit_name'] : '',
			isset($line['type']) ? $line['type'] : '',
		);
		foreach ($candidates as $raw)
		{
			$label = trim((string) $raw);
			if ($label === '')
			{
				continue;
			}
			// Skip values that are mostly replacement/question marks from bad charset writes.
			$letters = preg_replace('/[\s\d\.\-\/_]+/u', '', $label);
			if ($letters === '')
			{
				continue;
			}
			$bad = preg_match_all('/[?\x{FFFD}]/u', $letters);
			if ($bad > 0 && $bad >= (int) ceil(mb_strlen($letters, 'UTF-8') * 0.4))
			{
				continue;
			}
			return $label;
		}
		return 'Suit';
	}

	/**
	 * Public suit stage. Matches the workshop order-status screen.
	 *
	 * @return string
	 */
	protected function _customer_suit_status($dept, $transfer_status)
	{
		$dept = strtoupper(trim((string) $dept));
		$transfer_status = strtoupper(trim((string) $transfer_status));
		if ($transfer_status === 'DELIVER')
		{
			return 'delivered';
		}
		if ($dept === 'STIT' && $transfer_status === 'DEACTIVE')
		{
			return 'ready';
		}
		if ($dept === 'STIT')
		{
			return 'stitching';
		}
		if ($dept === 'HO2')
		{
			return 'after_cutting';
		}
		if ($dept === 'CUT')
		{
			return 'cutting';
		}
		return 'booking';
	}

	protected function _customer_status_rank($status)
	{
		$ranks = array(
			'booking' => 0,
			'cutting' => 1,
			'after_cutting' => 2,
			'stitching' => 3,
			'ready' => 4,
			'delivered' => 5,
		);
		return isset($ranks[$status]) ? $ranks[$status] : 0;
	}

	protected function _sum_amounts_by_order($table, $id_column, $amount_column, array $ids)
	{
		$map = array();
		if ( ! $ids)
		{
			return $map;
		}
		$this->db->select($id_column.' AS oid, SUM('.$amount_column.') AS paid', FALSE);
		$this->db->from($table);
		$this->db->where_in($id_column, $ids);
		$this->db->group_by($id_column);
		$query = $this->db->get();
		if ( ! $query)
		{
			return $map;
		}
		foreach ($query->result_array() as $row)
		{
			$map[(int) $row['oid']] = round((float) $row['paid'], 2);
		}
		return $map;
	}

	protected function _public_order_image_url($image)
	{
		$image = trim((string) $image);
		if ($image === '' || strcasecmp($image, 'NULL') === 0)
		{
			return '';
		}
		// ERP stores camera captures as data:image/...;base64,... — use as-is.
		if (stripos($image, 'data:image/') === 0)
		{
			return $image;
		}
		if (strpos($image, 'http') === 0 || strpos($image, '/') === 0)
		{
			return $image;
		}
		return base_url(ltrim($image, '/'));
	}

	public function get_measurement_by_code($measurement_code, $customer_code)
	{
		$measurement_code = (int) $measurement_code;
		$customer_code = (int) $customer_code;
		if ($measurement_code <= 0 || $customer_code <= 0)
		{
			return NULL;
		}
		return $this->db
			->from('measurement')
			->where('code', $measurement_code)
			->where('customer_code', $customer_code)
			->limit(1)
			->get()
			->row_array();
	}

	public function create_otp_challenge(array $row)
	{
		$this->db->insert('web_otp_challenges', $row);
		return (int) $this->db->insert_id();
	}

	public function get_otp_challenge($id)
	{
		return $this->db->where('id', (int) $id)->get('web_otp_challenges')->row_array();
	}

	public function update_otp_challenge($id, array $data)
	{
		$this->db->where('id', (int) $id)->update('web_otp_challenges', $data);
	}

	public function count_recent_otps($customer_code, $seconds = 60)
	{
		$since = date('Y-m-d H:i:s', time() - (int) $seconds);
		return (int) $this->db
			->where('customer_code', (int) $customer_code)
			->where('created_at >=', $since)
			->count_all_results('web_otp_challenges');
	}

	/**
	 * Seconds remaining before another OTP can be requested (0 = allowed).
	 */
	public function otp_cooldown_remaining($customer_code, $seconds = 60)
	{
		$customer_code = (int) $customer_code;
		$seconds = (int) $seconds;
		if ($customer_code <= 0 || $seconds <= 0)
		{
			return 0;
		}
		$row = $this->db
			->select('created_at')
			->from('web_otp_challenges')
			->where('customer_code', $customer_code)
			->order_by('created_at', 'DESC')
			->limit(1)
			->get()
			->row_array();
		if ( ! $row)
		{
			return 0;
		}
		$created = strtotime($row['created_at']);
		if ($created === FALSE)
		{
			return 0;
		}
		$elapsed = time() - $created;
		if ($elapsed >= $seconds)
		{
			return 0;
		}
		return (int) ($seconds - $elapsed);
	}

	public function create_session(array $row)
	{
		$this->db->insert('web_customer_sessions', $row);
		return (int) $this->db->insert_id();
	}

	public function get_session_by_token_hash($token_hash)
	{
		return $this->db
			->where('token_hash', $token_hash)
			->where('revoked_at IS NULL', NULL, FALSE)
			->get('web_customer_sessions')
			->row_array();
	}

	public function touch_session($id)
	{
		$this->db->where('id', (int) $id)->update('web_customer_sessions', array(
			'last_seen_at' => date('Y-m-d H:i:s'),
		));
	}

	public function revoke_session_by_token_hash($token_hash)
	{
		$this->db->where('token_hash', $token_hash)->update('web_customer_sessions', array(
			'revoked_at' => date('Y-m-d H:i:s'),
		));
	}

	public function rate_limit_hit($bucket_key, $window_seconds, $max_hits)
	{
		$now = time();
		$row = $this->db->where('bucket_key', $bucket_key)->get('web_api_rate_limits')->row_array();
		if ( ! $row)
		{
			$this->db->insert('web_api_rate_limits', array(
				'bucket_key' => $bucket_key,
				'hit_count' => 1,
				'window_start' => date('Y-m-d H:i:s', $now),
			));
			return TRUE;
		}
		$window_start = strtotime($row['window_start']);
		if (($now - $window_start) >= (int) $window_seconds)
		{
			$this->db->where('id', (int) $row['id'])->update('web_api_rate_limits', array(
				'hit_count' => 1,
				'window_start' => date('Y-m-d H:i:s', $now),
			));
			return TRUE;
		}
		$hits = (int) $row['hit_count'] + 1;
		$this->db->where('id', (int) $row['id'])->update('web_api_rate_limits', array(
			'hit_count' => $hits,
		));
		return $hits <= (int) $max_hits;
	}

	/**
	 * Seconds until the rate-limit window resets (0 if no active window).
	 */
	public function rate_limit_retry_after($bucket_key, $window_seconds)
	{
		$row = $this->db->where('bucket_key', $bucket_key)->get('web_api_rate_limits')->row_array();
		if ( ! $row)
		{
			return 0;
		}
		$window_start = strtotime($row['window_start']);
		if ($window_start === FALSE)
		{
			return 0;
		}
		$remaining = (int) $window_seconds - (time() - $window_start);
		return $remaining > 0 ? $remaining : 0;
	}

	public function create_measurement_submission(array $row)
	{
		$this->db->insert('web_measurement_submissions', $row);
		return (int) $this->db->insert_id();
	}

	public function get_measurement_submission($id)
	{
		return $this->db->where('id', (int) $id)->get('web_measurement_submissions')->row_array();
	}

	public function list_measurement_submissions($status = '', $limit = 50, $offset = 0)
	{
		if ($status !== '')
		{
			$this->db->where('status', $status);
		}
		return $this->db
			->order_by('created_at', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get('web_measurement_submissions')
			->result_array();
	}

	public function count_measurement_submissions($status = '')
	{
		if ($status !== '')
		{
			$this->db->where('status', $status);
		}
		return (int) $this->db->count_all_results('web_measurement_submissions');
	}

	public function update_measurement_submission($id, array $data)
	{
		$this->db->where('id', (int) $id)->update('web_measurement_submissions', $data);
	}

	public function list_customer_submissions($customer_code, $limit = 20)
	{
		return $this->db
			->where('customer_code', (int) $customer_code)
			->order_by('created_at', 'DESC')
			->limit((int) $limit)
			->get('web_measurement_submissions')
			->result_array();
	}

	public function get_guides($active_only = TRUE)
	{
		if ( ! $this->db->table_exists('web_measurement_guides'))
		{
			return array();
		}
		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('garment', 'ASC')->order_by('sort_order', 'ASC')->get('web_measurement_guides')->result_array();
	}

	public function list_categories($active_only = TRUE)
	{
		if ( ! $this->db->table_exists('web_categories'))
		{
			return array();
		}
		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('sort_order', 'ASC')->get('web_categories')->result_array();
	}

	public function get_category($id)
	{
		$id = (int) $id;
		if ($id <= 0 || ! $this->db->table_exists('web_categories'))
		{
			return NULL;
		}
		return $this->db->where('id', $id)->get('web_categories')->row_array();
	}

	public function save_category(array $row, $id = 0)
	{
		if ( ! $this->db->table_exists('web_categories'))
		{
			return 0;
		}
		$id = (int) $id;
		$row['updated_at'] = date('Y-m-d H:i:s');
		if ($id > 0)
		{
			$this->db->where('id', $id)->update('web_categories', $row);
			return $id;
		}
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('web_categories', $row);
		return (int) $this->db->insert_id();
	}

	public function count_products_in_category($category_id)
	{
		$category_id = (int) $category_id;
		if ($category_id <= 0 || ! $this->db->table_exists('web_products'))
		{
			return 0;
		}
		return (int) $this->db->where('category_id', $category_id)->count_all_results('web_products');
	}

	public function category_slug_exists($slug, $exclude_id = 0)
	{
		if ( ! $this->db->table_exists('web_categories'))
		{
			return FALSE;
		}
		$this->db->where('slug', $slug);
		$exclude_id = (int) $exclude_id;
		if ($exclude_id > 0)
		{
			$this->db->where('id !=', $exclude_id);
		}
		return (int) $this->db->count_all_results('web_categories') > 0;
	}

	public function get_product($id)
	{
		$id = (int) $id;
		if ($id <= 0 || ! $this->db->table_exists('web_products'))
		{
			return NULL;
		}
		return $this->db->where('id', $id)->get('web_products')->row_array();
	}

	public function list_products($filters = array())
	{
		if ( ! $this->db->table_exists('web_products'))
		{
			return array();
		}
		$this->db->select('web_products.*, web_categories.name_en AS category_name', FALSE);
		$this->db->from('web_products');
		$this->db->join('web_categories', 'web_categories.id = web_products.category_id', 'left');
		if (empty($filters['include_inactive']))
		{
			$this->db->where('web_products.is_active', 1);
		}
		if ( ! empty($filters['category_id']))
		{
			$this->db->where('web_products.category_id', (int) $filters['category_id']);
		}
		if ( ! empty($filters['featured']))
		{
			$this->db->where('web_products.is_featured', 1);
		}
		if ( ! empty($filters['q']))
		{
			$q = $this->db->escape_like_str($filters['q']);
			$this->db->group_start()
				->like('web_products.name_en', $q)
				->or_like('web_products.name_ur', $q)
				->or_like('web_products.sku', $q)
				->group_end();
		}
		$limit = isset($filters['limit']) ? (int) $filters['limit'] : 24;
		$offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;
		return $this->db
			->order_by('web_products.is_featured', 'DESC')
			->order_by('web_products.id', 'DESC')
			->limit($limit, $offset)
			->get()
			->result_array();
	}

	public function get_product_by_slug($slug)
	{
		if ( ! $this->db->table_exists('web_products'))
		{
			return NULL;
		}
		return $this->db->where('slug', $slug)->where('is_active', 1)->get('web_products')->row_array();
	}

	public function get_product_images($product_id)
	{
		if ( ! $this->db->table_exists('web_product_images'))
		{
			return array();
		}
		return $this->db
			->where('product_id', (int) $product_id)
			->order_by('is_primary', 'DESC')
			->order_by('sort_order', 'ASC')
			->get('web_product_images')
			->result_array();
	}

	public function create_order(array $order, array $items)
	{
		$this->db->trans_start();
		$this->db->insert('web_orders', $order);
		$order_id = (int) $this->db->insert_id();
		foreach ($items as $item)
		{
			$item['order_id'] = $order_id;
			$this->db->insert('web_order_items', $item);
		}
		$this->db->trans_complete();
		return $this->db->trans_status() ? $order_id : 0;
	}

	public function get_order_by_number($order_number, $customer_code = 0)
	{
		$this->db->where('order_number', $order_number);
		if ($customer_code > 0)
		{
			$this->db->where('customer_code', (int) $customer_code);
		}
		return $this->db->get('web_orders')->row_array();
	}

	public function get_order_items($order_id)
	{
		return $this->db->where('order_id', (int) $order_id)->get('web_order_items')->result_array();
	}

	public function list_orders($filters = array(), $limit = 50, $offset = 0)
	{
		if ( ! empty($filters['status']))
		{
			$this->db->where('status', $filters['status']);
		}
		if ( ! empty($filters['q']))
		{
			$q = $this->db->escape_like_str($filters['q']);
			$this->db->group_start()
				->like('order_number', $q)
				->or_like('guest_name', $q)
				->or_like('guest_phone', $q)
				->group_end();
		}
		return $this->db->order_by('created_at', 'DESC')->limit((int) $limit, (int) $offset)->get('web_orders')->result_array();
	}

	public function update_order($id, array $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', (int) $id)->update('web_orders', $data);
	}

	public function insert_measurement_row(array $data)
	{
		$this->db->insert('measurement', $data);
		return (int) $this->db->insert_id();
	}

	public function update_measurement_row($code, array $data)
	{
		$this->db->where('code', (int) $code)->update('measurement', $data);
		return $this->db->affected_rows() >= 0;
	}

	public function next_measurement_code()
	{
		$row = $this->db->select_max('code')->get('measurement')->row_array();
		$max = isset($row['code']) ? (int) $row['code'] : 0;
		return $max + 1;
	}

	public function dashboard_counts()
	{
		$out = array(
			'pending_submissions' => 0,
			'web_orders_pending' => 0,
			'products_active' => 0,
		);
		if ($this->db->table_exists('web_measurement_submissions'))
		{
			$out['pending_submissions'] = (int) $this->db->where('status', 'pending')->count_all_results('web_measurement_submissions');
		}
		if ($this->db->table_exists('web_orders'))
		{
			$out['web_orders_pending'] = (int) $this->db->where('status', 'pending')->count_all_results('web_orders');
		}
		if ($this->db->table_exists('web_products'))
		{
			$out['products_active'] = (int) $this->db->where('is_active', 1)->count_all_results('web_products');
		}
		return $out;
	}

	protected function default_settings()
	{
		return array(
			'id' => 1,
			'site_name' => 'Darzi Baba',
			'tagline_en' => 'Premium tailoring & measurements, made simple.',
			'tagline_ur' => '',
			'about_en' => '',
			'about_ur' => '',
			'contact_phone' => '',
			'contact_whatsapp' => '',
			'contact_email' => '',
			'contact_address_en' => '',
			'contact_address_ur' => '',
			'seo_title_default' => 'Darzi Baba',
			'seo_description_default' => '',
			'og_image_url' => '',
			'feature_shop' => 1,
			'feature_measurements' => 1,
		);
	}
}
