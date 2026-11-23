<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuration extends MY_Controller
{
	public function index()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Business_config_model');
		$this->load->model('Booking_limit_model');
		$this->load->model('Order_predefined_model');
		$this->load->model('Database_backup_model');

		$tab = $this->input->get('tab', TRUE);
		if ($tab === 'order_predefined')
		{
			$tab = 'order_predefined';
		}
		elseif ($tab === 'database')
		{
			$tab = 'database';
		}
		elseif ($tab === 'devices')
		{
			$tab = 'devices';
		}
		else
		{
			$tab = 'business';
		}

		$data['active_tab'] = $tab;
		$data['config'] = $this->Business_config_model->get_config();
		$data['config_table_ready'] = $this->Business_config_model->has_table();
		$data['booking_date_limits'] = $this->Booking_limit_model->list_date_limits();
		$data['booking_limits_table_ready'] = $this->Booking_limit_model->has_date_table();
		$data['predefined_table_ready'] = $this->Order_predefined_model->has_table();
		$data['order_predefined_items'] = $this->Order_predefined_model->get_all_for_admin();
		$data['backup_limits'] = $this->Database_backup_model->upload_limits();
		$data['backup_max_label'] = $this->Database_backup_model->format_bytes(
			isset($data['backup_limits']['effective_max']) ? $data['backup_limits']['effective_max'] : 0
		);

		$this->load->model('Device_auth_model');
		$data['device_tables_ready'] = $this->Device_auth_model->tables_ready();
		$data['device_lock_enabled'] = $this->Device_auth_model->is_lock_enabled();
		$data['authorized_devices'] = $this->Device_auth_model->list_devices();
		$data['this_device'] = $this->Device_auth_model->current_device();
		$data['device_client_ip'] = $this->Device_auth_model->client_ip();
		$data['device_enrollment_code'] = $this->session->flashdata('device_enrollment_code');
		$data['device_enrollment_expires'] = $this->session->flashdata('device_enrollment_expires');

		$this->load->view('business_configuration', $data);
	}

	/**
	 * Start background export job (JSON). UI polls export_status then downloads.
	 */
	public function export_start()
	{
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			return $this->backup_json(array('ok' => FALSE, 'error' => 'POST required.'), 405);
		}

		$result = $this->Database_backup_model->start_export_job();
		if (empty($result['ok']))
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => isset($result['error']) ? $result['error'] : 'Could not start export.',
			), 500);
		}

		return $this->backup_json(array(
			'ok' => TRUE,
			'job_id' => $result['job_id'],
		));
	}

	/**
	 * Poll export job progress (JSON).
	 */
	public function export_status($job_id = '')
	{
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		if ($job_id === '')
		{
			$job_id = (string) $this->input->get('job_id', TRUE);
		}

		$status = $this->Database_backup_model->export_job_status($job_id);
		$code = ! empty($status['ok']) ? 200 : 404;
		return $this->backup_json($status, $code);
	}

	/**
	 * Stream a completed export file (token required).
	 */
	public function export_download($job_id = '')
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		if ($job_id === '')
		{
			$job_id = (string) $this->input->get('job_id', TRUE);
		}
		$token = (string) $this->input->get('token', TRUE);

		$result = $this->Database_backup_model->claim_export_download($job_id, $token);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata(
				'backup_error',
				isset($result['error']) ? $result['error'] : 'Download failed.'
			);
			redirect('Configuration?tab=database');
			return;
		}

		$filename = $result['filename'];
		$path = $result['path'];

		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		$filesize = (int) filesize($path);
		header('Content-Type: application/gzip');
		header('Content-Disposition: attachment; filename="'.str_replace(array('"', "\r", "\n"), '', $filename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$filesize);
		header('Cache-Control: private, no-transform, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		$fp = fopen($path, 'rb');
		if ($fp)
		{
			while ( ! feof($fp))
			{
				echo fread($fp, 1048576);
				flush();
			}
			fclose($fp);
		}
		@unlink($path);
		$this->Database_backup_model->finish_export_job($job_id);
		exit;
	}

	/**
	 * Legacy synchronous export (fallback link). Prefer export_start + progress UI.
	 */
	public function export_database()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		@set_time_limit(0);
		$result = $this->Database_backup_model->create_dump('export');
		if (empty($result['ok']) || empty($result['path']) || ! is_file($result['path']))
		{
			$this->session->set_flashdata(
				'backup_error',
				isset($result['error']) ? $result['error'] : 'Database export failed.'
			);
			redirect('Configuration?tab=database');
			return;
		}

		$filename = isset($result['filename']) ? $result['filename'] : 'database_export.sql.gz';
		$path = $result['path'];

		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		$filesize = (int) filesize($path);
		header('Content-Type: application/gzip');
		header('Content-Disposition: attachment; filename="'.str_replace(array('"', "\r", "\n"), '', $filename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$filesize);
		header('Cache-Control: private, no-transform, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		$fp = fopen($path, 'rb');
		if ($fp)
		{
			while ( ! feof($fp))
			{
				echo fread($fp, 1048576);
				flush();
			}
			fclose($fp);
		}
		@unlink($path);
		exit;
	}

	/**
	 * @param array $payload
	 * @param int   $status
	 */
	protected function backup_json(array $payload, $status = 200)
	{
		return $this->output
			->set_status_header((int) $status)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($payload));
	}

	/**
	 * Replace the live database from an uploaded .sql / .sql.gz backup.
	 * A safety dump is created first; failed imports attempt automatic rollback.
	 */
	/**
	 * Send a one-time WhatsApp code required before Clear Database.
	 */
	public function clear_send_otp()
	{
		$this->load->library('session');
		$this->load->model('Business_config_model');
		$this->load->library('Whatsapp_bridge');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			return $this->backup_json(array('ok' => FALSE, 'error' => 'POST required.'), 405);
		}

		$last = (int) $this->session->userdata('db_clear_otp_sent_at');
		if ($last > 0 && (time() - $last) < 60)
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => 'Wait 60 seconds before requesting another code.',
			), 429);
		}

		$config = $this->Business_config_model->get_config();
		$phone_raw = isset($config['phone']) ? trim((string) $config['phone']) : '';
		$phone = $this->normalize_wa_phone($phone_raw);
		if ($phone === '')
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => 'Set the business phone number in Configuration → Business Details first.',
			), 400);
		}

		if ( ! $this->whatsapp_bridge->is_enabled())
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => 'WhatsApp bridge is not connected. Open Notifications and scan the QR first.',
			), 503);
		}

		$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
		$message = "Darzi Baba ERP\n\nDatabase Clear verification code: {$otp}\nValid for 10 minutes.\nIf you did not request this, ignore the message.";

		$send = $this->whatsapp_bridge->send($phone, $message);
		if (empty($send['ok']))
		{
			$err = isset($send['error']) ? (string) $send['error'] : 'send_failed';
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => 'Could not send WhatsApp OTP ('.$err.').',
			), 502);
		}

		$this->session->set_userdata(array(
			'db_clear_otp_hash' => hash('sha256', $otp),
			'db_clear_otp_expires' => time() + 600,
			'db_clear_otp_sent_at' => time(),
			'db_clear_otp_phone' => $phone,
		));

		$masked = $this->mask_phone($phone);
		return $this->backup_json(array(
			'ok' => TRUE,
			'message' => 'OTP sent to WhatsApp '.$masked,
			'phone_masked' => $masked,
		));
	}

	/**
	 * Clear all ERP data tables after typing CLEAR + valid WhatsApp OTP.
	 */
	public function clear_database()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		$confirm = trim((string) $this->input->post('confirm_clear', TRUE));
		$otp = preg_replace('/\D+/', '', (string) $this->input->post('clear_otp', TRUE));

		if (strtoupper($confirm) !== 'CLEAR')
		{
			$this->session->set_flashdata('backup_error', 'Type CLEAR to confirm database wipe.');
			redirect('Configuration?tab=database');
			return;
		}

		$hash = (string) $this->session->userdata('db_clear_otp_hash');
		$expires = (int) $this->session->userdata('db_clear_otp_expires');
		if ($hash === '' || $expires < time())
		{
			$this->session->set_flashdata('backup_error', 'WhatsApp OTP missing or expired. Send a new code first.');
			redirect('Configuration?tab=database');
			return;
		}
		if (strlen($otp) !== 6 || ! hash_equals($hash, hash('sha256', $otp)))
		{
			$this->session->set_flashdata('backup_error', 'Invalid WhatsApp OTP. Check the code and try again.');
			redirect('Configuration?tab=database');
			return;
		}

		@set_time_limit(0);
		$result = $this->Database_backup_model->clear_all_data();

		$this->session->unset_userdata(array(
			'db_clear_otp_hash',
			'db_clear_otp_expires',
			'db_clear_otp_sent_at',
			'db_clear_otp_phone',
		));

		if (empty($result['ok']))
		{
			$this->session->set_flashdata(
				'backup_error',
				isset($result['error']) ? $result['error'] : 'Database clear failed.'
			);
			redirect('Configuration?tab=database');
			return;
		}

		$this->session->set_flashdata('backup_suss', 'cleared');
		if ( ! empty($result['safety_filename']))
		{
			$this->session->set_flashdata('backup_safety', $result['safety_filename']);
		}
		redirect('Configuration?tab=database');
	}

	/**
	 * @param string $phone
	 * @return string
	 */
	protected function normalize_wa_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		if ($digits === '')
		{
			return '';
		}
		if (strpos($digits, '92') === 0 && strlen($digits) >= 12)
		{
			return $digits;
		}
		if (isset($digits[0]) && $digits[0] === '0' && strlen($digits) >= 11)
		{
			return '92'.substr($digits, 1);
		}
		if (strlen($digits) === 10 && isset($digits[0]) && $digits[0] === '3')
		{
			return '92'.$digits;
		}
		return $digits;
	}

	/**
	 * @param string $phone
	 * @return string
	 */
	protected function mask_phone($phone)
	{
		$digits = preg_replace('/\D+/', '', (string) $phone);
		$len = strlen($digits);
		if ($len < 6)
		{
			return '****';
		}
		return substr($digits, 0, 4).str_repeat('*', max(0, $len - 6)).substr($digits, -2);
	}

	/**
	 * Start background import job after file upload (JSON). UI polls import_status.
	 */
	public function import_start()
	{
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			return $this->backup_json(array('ok' => FALSE, 'error' => 'POST required.'), 405);
		}

		$confirm = trim((string) $this->input->post('confirm_restore', TRUE));
		if (strtoupper($confirm) !== 'RESTORE')
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => 'Type RESTORE to confirm database import.',
			), 400);
		}

		if (empty($_FILES['backup_file']['tmp_name']))
		{
			$err = isset($_FILES['backup_file']['error']) ? (int) $_FILES['backup_file']['error'] : UPLOAD_ERR_NO_FILE;
			$msg = 'Please choose a .sql or .sql.gz backup file.';
			if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE)
			{
				$limits = $this->Database_backup_model->upload_limits();
				$msg = 'Uploaded file is too large for PHP limits (max '
					.$this->Database_backup_model->format_bytes($limits['effective_max']).').';
			}
			elseif ($err === UPLOAD_ERR_PARTIAL)
			{
				$msg = 'Upload was incomplete. Try again.';
			}
			return $this->backup_json(array('ok' => FALSE, 'error' => $msg), 400);
		}

		@set_time_limit(0);
		$result = $this->Database_backup_model->start_import_job(
			$_FILES['backup_file']['tmp_name'],
			isset($_FILES['backup_file']['name']) ? $_FILES['backup_file']['name'] : ''
		);

		if (empty($result['ok']))
		{
			return $this->backup_json(array(
				'ok' => FALSE,
				'error' => isset($result['error']) ? $result['error'] : 'Could not start import.',
			), 500);
		}

		return $this->backup_json(array(
			'ok' => TRUE,
			'job_id' => $result['job_id'],
		));
	}

	/**
	 * Poll import job progress (JSON).
	 */
	public function import_status($job_id = '')
	{
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		if ($job_id === '')
		{
			$job_id = (string) $this->input->get('job_id', TRUE);
		}

		$status = $this->Database_backup_model->import_job_status($job_id);
		$code = ! empty($status['ok']) ? 200 : 404;
		return $this->backup_json($status, $code);
	}

	/**
	 * Legacy synchronous import (fallback). Prefer import_start + progress UI.
	 */
	public function import_database()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Database_backup_model');

		$confirm = trim((string) $this->input->post('confirm_restore', TRUE));
		if (strtoupper($confirm) !== 'RESTORE')
		{
			$this->session->set_flashdata('backup_error', 'Type RESTORE to confirm database import.');
			redirect('Configuration?tab=database');
			return;
		}

		if (empty($_FILES['backup_file']['tmp_name']))
		{
			$err = isset($_FILES['backup_file']['error']) ? (int) $_FILES['backup_file']['error'] : UPLOAD_ERR_NO_FILE;
			$msg = 'Please choose a .sql or .sql.gz backup file.';
			if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE)
			{
				$limits = $this->Database_backup_model->upload_limits();
				$msg = 'Uploaded file is too large for PHP limits (max '
					.$this->Database_backup_model->format_bytes($limits['effective_max']).').';
			}
			$this->session->set_flashdata('backup_error', $msg);
			redirect('Configuration?tab=database');
			return;
		}

		@set_time_limit(0);
		$result = $this->Database_backup_model->import_uploaded_file(
			$_FILES['backup_file']['tmp_name'],
			isset($_FILES['backup_file']['name']) ? $_FILES['backup_file']['name'] : ''
		);

		if (empty($result['ok']))
		{
			$this->session->set_flashdata(
				'backup_error',
				isset($result['error']) ? $result['error'] : 'Database import failed.'
			);
			redirect('Configuration?tab=database');
			return;
		}

		$this->session->set_flashdata('backup_suss', 'imported');
		if ( ! empty($result['summary']))
		{
			$this->session->set_flashdata('backup_summary', $result['summary']);
		}
		if ( ! empty($result['safety_filename']))
		{
			$this->session->set_flashdata('backup_safety', $result['safety_filename']);
		}
		redirect('Configuration?tab=database');
	}

	public function save()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Business_config_model');

		$ok = $this->Business_config_model->save_config(array(
			'location_name' => $this->input->post('location_name', TRUE),
			'phone' => $this->input->post('phone', TRUE),
			'address_urdu' => $this->input->post('address_urdu', FALSE),
			'naap_number_prefix' => $this->input->post('naap_number_prefix', TRUE),
			'daily_booking_limit' => $this->input->post('daily_booking_limit', TRUE),
		));

		$this->session->set_flashdata($ok ? 'suss' : 'error', $ok ? 'yes' : 'missing_table');
		redirect('Configuration');
	}

	public function booking_limit_save()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Booking_limit_model');

		$result = $this->Booking_limit_model->save_date_limit(
			$this->input->post('return_date', TRUE),
			$this->input->post('limit_qty', TRUE),
			$this->input->post('note', TRUE)
		);

		if ( ! empty($result['ok']))
		{
			$this->session->set_flashdata('booking_limit_suss', 'saved');
		}
		else
		{
			$msg = isset($result['message']) ? $result['message'] : 'failed';
			$this->session->set_flashdata('booking_limit_error', $msg);
		}
		redirect('Configuration');
	}

	public function booking_limit_delete($id = 0)
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Booking_limit_model');

		$ok = $this->Booking_limit_model->delete_date_limit($id);
		$this->session->set_flashdata($ok ? 'booking_limit_suss' : 'booking_limit_error', $ok ? 'deleted' : 'failed');
		redirect('Configuration');
	}

	public function predefined_add()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->add_item(
			$this->input->post('label_text', FALSE),
			$this->input->post('insert_text', FALSE),
			$this->input->post('row_group', TRUE)
		);

		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'yes' : ($this->Order_predefined_model->has_table() ? 'invalid' : 'missing_table'));
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_update()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$id = (int) $this->input->post('id');
		$ok = $this->Order_predefined_model->update_item(
			$id,
			$this->input->post('label_text', FALSE),
			$this->input->post('insert_text', FALSE),
			$this->input->post('row_group', TRUE)
		);

		$this->session->set_flashdata(
			$ok ? 'predefined_suss' : 'predefined_error',
			$ok ? 'updated' : ($this->Order_predefined_model->has_table() ? 'invalid' : 'missing_table')
		);
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_delete($id = 0)
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->delete_item($id);
		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'deleted' : 'failed');
		redirect('Configuration?tab=order_predefined');
	}

	public function predefined_reseed()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Order_predefined_model');

		$ok = $this->Order_predefined_model->reseed_defaults();
		$this->session->set_flashdata($ok ? 'predefined_suss' : 'predefined_error', $ok ? 'reseeded' : 'missing_table');
		redirect('Configuration?tab=order_predefined');
	}

	public function device_lock_toggle()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			redirect('Configuration?tab=devices');
			return;
		}
		if ( ! $this->Device_auth_model->tables_ready())
		{
			$this->session->set_flashdata('device_error', 'missing_table');
			redirect('Configuration?tab=devices');
			return;
		}

		$enable = (int) $this->input->post('lock_enabled') === 1;
		if ($enable)
		{
			// Must have this browser registered before turning lock on.
			if ( ! $this->Device_auth_model->current_device())
			{
				$name = trim((string) $this->input->post('device_name', TRUE));
				if ($name === '')
				{
					$name = 'Admin device '.date('Y-m-d');
				}
				$result = $this->Device_auth_model->register_device(
					$name,
					(string) $this->input->user_agent(TRUE),
					$this->Device_auth_model->client_ip(),
					(string) $this->session->userdata('user')
				);
				if (empty($result['ok']))
				{
					$this->session->set_flashdata('device_error', 'register_failed');
					redirect('Configuration?tab=devices');
					return;
				}
				$this->Device_auth_model->set_device_cookie($result['raw_token']);
			}
		}

		$this->Device_auth_model->set_lock_enabled($enable);
		$this->session->set_flashdata('device_suss', $enable ? 'lock_on' : 'lock_off');
		redirect('Configuration?tab=devices');
	}

	public function device_register()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			redirect('Configuration?tab=devices');
			return;
		}
		if ( ! $this->Device_auth_model->tables_ready())
		{
			$this->session->set_flashdata('device_error', 'missing_table');
			redirect('Configuration?tab=devices');
			return;
		}

		$name = trim((string) $this->input->post('device_name', TRUE));
		$result = $this->Device_auth_model->register_device(
			$name !== '' ? $name : ('Device '.date('Y-m-d H:i')),
			(string) $this->input->user_agent(TRUE),
			$this->Device_auth_model->client_ip(),
			(string) $this->session->userdata('user')
		);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata('device_error', 'register_failed');
			redirect('Configuration?tab=devices');
			return;
		}
		$this->Device_auth_model->set_device_cookie($result['raw_token']);
		$this->session->set_flashdata('device_suss', 'registered');
		redirect('Configuration?tab=devices');
	}

	public function device_revoke($id = 0)
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		$id = (int) $id;
		$current = $this->Device_auth_model->current_device();
		$ok = $this->Device_auth_model->revoke_device($id);
		if ($ok && $current && (int) $current->id === $id)
		{
			$this->Device_auth_model->clear_device_cookie();
		}
		$this->session->set_flashdata($ok ? 'device_suss' : 'device_error', $ok ? 'revoked' : 'revoke_failed');
		redirect('Configuration?tab=devices');
	}

	public function device_enrollment_code()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			redirect('Configuration?tab=devices');
			return;
		}
		if ( ! $this->Device_auth_model->tables_ready())
		{
			$this->session->set_flashdata('device_error', 'missing_table');
			redirect('Configuration?tab=devices');
			return;
		}
		if ($this->Device_auth_model->is_lock_enabled() && ! $this->Device_auth_model->current_device())
		{
			$this->session->set_flashdata('device_error', 'not_this_device');
			redirect('Configuration?tab=devices');
			return;
		}

		$label = trim((string) $this->input->post('label', TRUE));
		$result = $this->Device_auth_model->create_enrollment_code(
			$label,
			(string) $this->session->userdata('user'),
			30
		);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata('device_error', 'code_failed');
			redirect('Configuration?tab=devices');
			return;
		}
		$this->session->set_flashdata('device_suss', 'code_created');
		$this->session->set_flashdata('device_enrollment_code', $result['code']);
		$this->session->set_flashdata('device_enrollment_expires', $result['expires_at']);
		redirect('Configuration?tab=devices');
	}
}
