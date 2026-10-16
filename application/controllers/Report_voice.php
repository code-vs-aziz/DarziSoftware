<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_voice extends MY_Controller
{
	public function parse()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		if (!$this->session->userdata('user'))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'session'));
			return;
		}

		if (!$this->rbac->can('reports.menu'))
		{
			echo json_encode(array('ok' => FALSE, 'error' => 'permission'));
			return;
		}

		$text = trim((string) $this->input->post('text', FALSE));
		if ($text === '')
		{
			$text = trim((string) $this->input->post('command', FALSE));
		}

		$this->load->library('report_voice_service');
		$result = $this->report_voice_service->resolve($text);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function status()
	{
		$this->load->library('session');
		header('Content-Type: application/json; charset=UTF-8');

		$ollama = function_exists('dz_env_bool') && dz_env_bool('OLLAMA_ENABLED', FALSE);
		echo json_encode(array(
			'ok' => TRUE,
			'rules' => TRUE,
			'ollama' => $ollama,
			'speech' => TRUE,
		));
	}
}
