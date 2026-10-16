<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'core/Api_Controller.php';

/**
 * Customer portal API — view orders, status, and balances.
 * Measurement updates from the website are disabled.
 * Routes: /api/measurements/{method}
 */
class Measurements extends Api_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->ensure_schema();
		$this->load->library('Website_measurement_service', NULL, 'website_measurement');
	}

	public function catalog()
	{
		$guides = $this->website_model->get_guides(TRUE);
		$guide_map = array();
		foreach ($guides as $g)
		{
			$guide_map[$g['field_key']] = $g;
		}

		$catalog = $this->website_measurement->field_catalog();
		foreach ($catalog as &$group)
		{
			foreach ($group['fields'] as &$field)
			{
				if (isset($guide_map[$field['key']]))
				{
					$g = $guide_map[$field['key']];
					if ( ! empty($g['instruction_en']))
					{
						$field['hint_en'] = $g['instruction_en'];
					}
					if ( ! empty($g['instruction_ur']))
					{
						$field['hint_ur'] = $g['instruction_ur'];
					}
					if ( ! empty($g['video_url']))
					{
						$field['video_url'] = $g['video_url'];
					}
					if ( ! empty($g['example_value']))
					{
						$field['example_value'] = $g['example_value'];
					}
				}
			}
			unset($field);
		}
		unset($group);

		return $this->ok(array('catalog' => $catalog));
	}

	public function me()
	{
		$this->require_customer_auth();
		$code = (int) $this->customer['code'];
		$primary = $this->website_model->get_primary_measurement($code);
		$list = $this->website_model->list_measurements_for_customer($code);
		$subs = $this->website_model->list_customer_submissions($code, 10);
		$history = $this->website_model->get_customer_tailoring_history($code, 400);
		$current_count = 0;
		$history_count = 0;
		$total_paid = 0.0;
		$outstanding = 0.0;
		foreach ($history as $order)
		{
			if ( ! empty($order['is_current']))
			{
				$current_count++;
			}
			else
			{
				$history_count++;
			}
			$total_paid += isset($order['paid']) ? (float) $order['paid'] : 0;
			$outstanding += isset($order['balance']) ? (float) $order['balance'] : 0;
		}
		$context = array();
		$this->load->model('Order_query_model', 'order_query');
		if (method_exists($this->order_query, 'get_customer_order_context'))
		{
			$context = $this->order_query->get_customer_order_context($code);
		}

		$submissions = array();
		foreach ($subs as $s)
		{
			$submissions[] = array(
				'id' => (int) $s['id'],
				'status' => $s['status'],
				'submission_type' => $s['submission_type'],
				'created_at' => $s['created_at'],
				'reviewed_at' => $s['reviewed_at'],
				'review_note' => $s['review_note'],
			);
		}

		return $this->ok(array(
			'customer' => $this->website_auth->public_customer($this->customer, TRUE),
			'measurement' => $this->website_measurement->public_measurement_payload($primary),
			'measurement_list' => $list,
			'submissions' => $submissions,
			'tailoring_history' => $history,
			'stats' => array(
				'order_count' => count($history),
				'current_count' => $current_count,
				'history_count' => $history_count,
				'total_paid' => round($total_paid, 2),
				'outstanding' => round($outstanding, 2),
				'pending_balance' => isset($context['pending_balance']) ? (float) $context['pending_balance'] : round($outstanding, 2),
			),
		));
	}

	public function submit()
	{
		return $this->fail('updates_disabled', 403);
	}
}
