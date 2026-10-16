<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Configuration extends MY_Controller
{
	public function index()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Business_config_model');
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
		else
		{
			$tab = 'business';
		}

		$data['active_tab'] = $tab;
		$data['config'] = $this->Business_config_model->get_config();
		$data['config_table_ready'] = $this->Business_config_model->has_table();
		$data['predefined_table_ready'] = $this->Order_predefined_model->has_table();
		$data['order_predefined_items'] = $this->Order_predefined_model->get_all_for_admin();
		$data['backup_limits'] = $this->Database_backup_model->upload_limits();
		$data['backup_max_label'] = $this->Database_backup_model->format_bytes(
			isset($data['backup_limits']['effective_max']) ? $data['backup_limits']['effective_max'] : 0
		);
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
		));

		$this->session->set_flashdata($ok ? 'suss' : 'error', $ok ? 'yes' : 'missing_table');
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
}
