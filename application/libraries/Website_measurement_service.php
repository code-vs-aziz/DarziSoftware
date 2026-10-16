<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Measurement field catalog, validation, submissions, and ERP merge on approval.
 */
class Website_measurement_service
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Website_model', 'website_model');
		$this->CI->load->model('Order_query_model', 'order_query');
	}

	public function allowed_field_keys()
	{
		return $this->CI->order_query->measurement_snap_field_names();
	}

	/**
	 * Public field catalog grouped by garment (labels for website UI).
	 */
	public function field_catalog()
	{
		return array(
			array(
				'garment' => 'kurta',
				'label_en' => 'Kurta / Kameez',
				'label_ur' => 'کرتہ / قمیص',
				'fields' => array(
					array('key' => 'KL', 'label_en' => 'Kameez length', 'label_ur' => 'قمیض لمبائی', 'hint_en' => 'From high point of shoulder to desired hem.', 'unit' => 'inch', 'min' => 28, 'max' => 52),
					array('key' => 'BL', 'label_en' => 'Sleeve', 'label_ur' => 'بازو', 'hint_en' => 'From shoulder tip to wrist.', 'unit' => 'inch', 'min' => 16, 'max' => 32),
					array('key' => 'Teera', 'label_en' => 'Shoulder', 'label_ur' => 'تیرا', 'hint_en' => 'Shoulder tip to shoulder tip across the back.', 'unit' => 'inch', 'min' => 12, 'max' => 26),
					array('key' => 'geera', 'label_en' => 'Armhole', 'label_ur' => 'گیرا', 'hint_en' => 'Around the armhole.', 'unit' => 'inch', 'min' => 12, 'max' => 28),
					array('key' => 'Chaati', 'label_en' => 'Chest', 'label_ur' => 'چھاتی', 'hint_en' => 'Around the fullest part of the chest.', 'unit' => 'inch', 'min' => 28, 'max' => 60),
					array('key' => 'pait', 'label_en' => 'Stomach', 'label_ur' => 'پیٹ', 'hint_en' => 'Around the stomach at fullest point.', 'unit' => 'inch', 'min' => 26, 'max' => 60),
					array('key' => 'Gala', 'label_en' => 'Neck / Collar', 'label_ur' => 'گلا', 'hint_en' => 'Around the base of the neck.', 'unit' => 'inch', 'min' => 10, 'max' => 24),
					array('key' => 'Shalwar_Lenght', 'label_en' => 'Shalwar length', 'label_ur' => 'شلوار لمبائی', 'hint_en' => 'Waist to ankle along the outer leg.', 'unit' => 'inch', 'min' => 30, 'max' => 50),
					array('key' => 'Poncha_Choraic', 'label_en' => 'Poncha', 'label_ur' => 'پانچہ', 'hint_en' => 'Around the ankle opening.', 'unit' => 'inch', 'min' => 10, 'max' => 24),
					array('key' => 'SC', 'label_en' => 'Shalwar waist', 'label_ur' => 'شلوار کمر', 'hint_en' => 'Around the waist where the shalwar sits.', 'unit' => 'inch', 'min' => 24, 'max' => 55),
				),
			),
			array(
				'garment' => 'pajama',
				'label_en' => 'Pajama',
				'label_ur' => 'پاجامہ',
				'fields' => array(
					array('key' => 'PLambai', 'label_en' => 'Pajama length', 'label_ur' => 'لمبائی', 'unit' => 'inch', 'min' => 30, 'max' => 50),
					array('key' => 'PAsan', 'label_en' => 'Pajama seat', 'label_ur' => 'آسن', 'unit' => 'inch', 'min' => 20, 'max' => 50),
					array('key' => 'PPoncha', 'label_en' => 'Pajama bottom', 'label_ur' => 'پنچہ', 'unit' => 'inch', 'min' => 10, 'max' => 24),
					array('key' => 'PPeeir', 'label_en' => 'Pajama thigh', 'label_ur' => 'پیڑ', 'unit' => 'inch', 'min' => 14, 'max' => 36),
				),
			),
			array(
				'garment' => 'pent',
				'label_en' => 'Trouser / Pant',
				'label_ur' => 'پینٹ',
				'fields' => array(
					array('key' => 'PentLambai', 'label_en' => 'Trouser length', 'label_ur' => 'لمبائی', 'unit' => 'inch', 'min' => 30, 'max' => 50),
					array('key' => 'pentWaiste', 'label_en' => 'Waist', 'label_ur' => 'کمر', 'unit' => 'inch', 'min' => 24, 'max' => 55),
					array('key' => 'HIP', 'label_en' => 'Hip', 'label_ur' => 'ہپ', 'unit' => 'inch', 'min' => 28, 'max' => 60),
					array('key' => 'PentThai', 'label_en' => 'Thigh', 'label_ur' => 'تھائی', 'unit' => 'inch', 'min' => 14, 'max' => 36),
					array('key' => 'PentPoncha', 'label_en' => 'Bottom', 'label_ur' => 'پنچہ', 'unit' => 'inch', 'min' => 10, 'max' => 24),
				),
			),
			array(
				'garment' => 'shirt',
				'label_en' => 'Shirt',
				'label_ur' => 'شرٹ',
				'fields' => array(
					array('key' => 'SGaala', 'label_en' => 'Collar', 'label_ur' => 'گلا', 'unit' => 'inch', 'min' => 12, 'max' => 22),
					array('key' => 'STeera', 'label_en' => 'Shoulder', 'label_ur' => 'تیرا', 'unit' => 'inch', 'min' => 14, 'max' => 26),
					array('key' => 'SBazoo', 'label_en' => 'Sleeve', 'label_ur' => 'بازو', 'unit' => 'inch', 'min' => 18, 'max' => 32),
					array('key' => 'SLambai', 'label_en' => 'Shirt length', 'label_ur' => 'لمبائی', 'unit' => 'inch', 'min' => 24, 'max' => 40),
					array('key' => 'SChaati', 'label_en' => 'Chest', 'label_ur' => 'چھاتی', 'unit' => 'inch', 'min' => 28, 'max' => 60),
					array('key' => 'SKamar', 'label_en' => 'Waist', 'label_ur' => 'کمر', 'unit' => 'inch', 'min' => 24, 'max' => 55),
					array('key' => 'SGeera', 'label_en' => 'Armhole', 'label_ur' => 'گیرہ', 'unit' => 'inch', 'min' => 12, 'max' => 28),
				),
			),
			array(
				'garment' => 'coat',
				'label_en' => 'Coat',
				'label_ur' => 'کوٹ',
				'fields' => array(
					array('key' => 'CoatGaala', 'label_en' => 'Collar', 'label_ur' => 'گلا', 'unit' => 'inch', 'min' => 12, 'max' => 22),
					array('key' => 'CoatTeera', 'label_en' => 'Shoulder', 'label_ur' => 'تیرا', 'unit' => 'inch', 'min' => 14, 'max' => 26),
					array('key' => 'CoatBazoo', 'label_en' => 'Sleeve', 'label_ur' => 'بازو', 'unit' => 'inch', 'min' => 18, 'max' => 32),
					array('key' => 'CoatLambai', 'label_en' => 'Coat length', 'label_ur' => 'لمبائی', 'unit' => 'inch', 'min' => 24, 'max' => 45),
					array('key' => 'CoatChaati', 'label_en' => 'Chest', 'label_ur' => 'چھاتی', 'unit' => 'inch', 'min' => 30, 'max' => 60),
					array('key' => 'CoatKamar', 'label_en' => 'Waist', 'label_ur' => 'کمر', 'unit' => 'inch', 'min' => 26, 'max' => 55),
					array('key' => 'CoatHIP', 'label_en' => 'Hip', 'label_ur' => 'ہپ', 'unit' => 'inch', 'min' => 30, 'max' => 60),
					array('key' => 'crossback', 'label_en' => 'Cross back', 'label_ur' => 'کراس بیک', 'unit' => 'inch', 'min' => 12, 'max' => 24),
				),
			),
			array(
				'garment' => 'waistcoat',
				'label_en' => 'Waistcoat',
				'label_ur' => 'ویسٹ کوٹ',
				'fields' => array(
					array('key' => 'wastGaala', 'label_en' => 'Collar', 'label_ur' => 'گلا', 'unit' => 'inch', 'min' => 12, 'max' => 22),
					array('key' => 'wastTeera', 'label_en' => 'Shoulder', 'label_ur' => 'تیرا', 'unit' => 'inch', 'min' => 14, 'max' => 26),
					array('key' => 'wastLambai', 'label_en' => 'Length', 'label_ur' => 'لمبائی', 'unit' => 'inch', 'min' => 18, 'max' => 36),
					array('key' => 'wastChaati', 'label_en' => 'Chest', 'label_ur' => 'چھاتی', 'unit' => 'inch', 'min' => 30, 'max' => 60),
					array('key' => 'wastKamar', 'label_en' => 'Waist', 'label_ur' => 'کمر', 'unit' => 'inch', 'min' => 26, 'max' => 55),
					array('key' => 'wastHIP', 'label_en' => 'Hip', 'label_ur' => 'ہپ', 'unit' => 'inch', 'min' => 30, 'max' => 60),
				),
			),
		);
	}

	public function public_measurement_payload($row)
	{
		if ( ! $row)
		{
			return NULL;
		}
		$allowed = array_flip($this->allowed_field_keys());
		$values = array();
		foreach ($row as $key => $value)
		{
			if (isset($allowed[$key]))
			{
				$values[$key] = ($value === NULL || $value === '') ? '' : (string) $value;
			}
		}
		return array(
			'code' => isset($row['code']) ? (int) $row['code'] : 0,
			'customer_code' => isset($row['customer_code']) ? (int) $row['customer_code'] : 0,
			'date' => isset($row['EDATE']) ? (string) $row['EDATE'] : '',
			'remarks' => isset($row['remarks']) ? (string) $row['remarks'] : '',
			'values' => $values,
			'source' => 'erp',
		);
	}

	/**
	 * @return array{ok:bool, submission_id?:int, error?:string, errors?:array}
	 */
	public function submit($customer_code, array $input, $ip = '')
	{
		$customer_code = (int) $customer_code;
		$existing = $this->CI->website_model->get_primary_measurement($customer_code);
		$type = $existing ? 'update' : 'create';

		$validated = $this->validate_payload(isset($input['values']) && is_array($input['values']) ? $input['values'] : array());
		if ( ! $validated['ok'])
		{
			return $validated;
		}

		$payload = array(
			'values' => $validated['values'],
			'remarks' => isset($input['remarks']) ? substr(trim((string) $input['remarks']), 0, 500) : '',
			'garments' => isset($input['garments']) && is_array($input['garments']) ? array_values($input['garments']) : array(),
		);

		$id = $this->CI->website_model->create_measurement_submission(array(
			'customer_code' => $customer_code,
			'measurement_code' => $existing ? (int) $existing['code'] : NULL,
			'submission_type' => $type,
			'status' => 'pending',
			'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
			'remarks' => $payload['remarks'],
			'source' => 'website',
			'ip_address' => substr((string) $ip, 0, 45),
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));

		return array('ok' => TRUE, 'submission_id' => $id, 'status' => 'pending', 'submission_type' => $type);
	}

	/**
	 * Approve pending submission and write into live measurement table.
	 *
	 * @return array{ok:bool, measurement_code?:int, error?:string}
	 */
	public function approve($submission_id, $reviewed_by, $review_note = '')
	{
		$submission = $this->CI->website_model->get_measurement_submission((int) $submission_id);
		if ( ! $submission)
		{
			return array('ok' => FALSE, 'error' => 'not_found');
		}
		if ($submission['status'] !== 'pending')
		{
			return array('ok' => FALSE, 'error' => 'not_pending');
		}

		$payload = json_decode($submission['payload_json'], TRUE);
		if ( ! is_array($payload) || empty($payload['values']) || ! is_array($payload['values']))
		{
			return array('ok' => FALSE, 'error' => 'invalid_payload');
		}

		$validated = $this->validate_payload($payload['values']);
		if ( ! $validated['ok'])
		{
			return array('ok' => FALSE, 'error' => 'validation_failed', 'errors' => $validated['errors']);
		}

		$row = $validated['values'];
		$row['remarks'] = isset($payload['remarks']) ? (string) $payload['remarks'] : '';
		$row['EDATE'] = date('Y-m-d');
		$row['customer_code'] = (int) $submission['customer_code'];

		$measurement_code = 0;
		if ($submission['submission_type'] === 'update' && ! empty($submission['measurement_code']))
		{
			$measurement_code = (int) $submission['measurement_code'];
			$owned = $this->CI->website_model->get_measurement_by_code($measurement_code, (int) $submission['customer_code']);
			if ( ! $owned)
			{
				return array('ok' => FALSE, 'error' => 'measurement_mismatch');
			}
			$this->CI->website_model->update_measurement_row($measurement_code, $row);
		}
		else
		{
			$measurement_code = $this->CI->website_model->next_measurement_code();
			$row['code'] = $measurement_code;
			$this->CI->website_model->insert_measurement_row($row);
		}

		$this->CI->website_model->update_measurement_submission((int) $submission_id, array(
			'status' => 'approved',
			'measurement_code' => $measurement_code,
			'reviewed_by' => substr((string) $reviewed_by, 0, 100),
			'review_note' => substr((string) $review_note, 0, 1000),
			'reviewed_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));

		return array('ok' => TRUE, 'measurement_code' => $measurement_code);
	}

	public function reject($submission_id, $reviewed_by, $review_note = '')
	{
		$submission = $this->CI->website_model->get_measurement_submission((int) $submission_id);
		if ( ! $submission || $submission['status'] !== 'pending')
		{
			return array('ok' => FALSE, 'error' => 'not_pending');
		}
		$this->CI->website_model->update_measurement_submission((int) $submission_id, array(
			'status' => 'rejected',
			'reviewed_by' => substr((string) $reviewed_by, 0, 100),
			'review_note' => substr((string) $review_note, 0, 1000),
			'reviewed_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
		return array('ok' => TRUE);
	}

	protected function validate_payload(array $values)
	{
		$allowed = array_flip($this->allowed_field_keys());
		$clean = array();
		$errors = array();
		$non_empty = 0;

		foreach ($values as $key => $raw)
		{
			$key = (string) $key;
			if ( ! isset($allowed[$key]))
			{
				continue;
			}
			// Keep shop-style values (e.g. "27.5/26", "p15 s16") — same as ERP measurement table.
			$val = trim(preg_replace('/\s+/u', ' ', (string) $raw));
			if ($val === '')
			{
				$clean[$key] = '';
				continue;
			}
			if (mb_strlen($val) > 40)
			{
				$errors[$key] = 'too_long';
				continue;
			}
			if ( ! preg_match('/^[\p{L}\p{N}\.\/\-\+\s]+$/u', $val))
			{
				$errors[$key] = 'invalid_format';
				continue;
			}
			$clean[$key] = $val;
			$non_empty++;
		}

		if ($non_empty < 1)
		{
			return array('ok' => FALSE, 'error' => 'empty_values', 'errors' => array('_form' => 'at_least_one_required'));
		}
		if ($errors)
		{
			return array('ok' => FALSE, 'error' => 'validation_failed', 'errors' => $errors);
		}
		return array('ok' => TRUE, 'values' => $clean);
	}

	protected function field_limits_map()
	{
		$map = array();
		foreach ($this->field_catalog() as $group)
		{
			foreach ($group['fields'] as $field)
			{
				$map[$field['key']] = array(
					'min' => isset($field['min']) ? (float) $field['min'] : 1,
					'max' => isset($field['max']) ? (float) $field['max'] : 120,
				);
			}
		}
		return $map;
	}
}
