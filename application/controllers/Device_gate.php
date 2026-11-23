<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shown when device lockdown is on and this browser is not approved.
 * Allows enrollment via one-time code from an admin on an approved device.
 */
class Device_gate extends CI_Controller
{
	public function index()
	{
		$this->load->helper(array('url', 'ui', 'app_lang'));
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		if ( ! $this->Device_auth_model->is_lock_enabled())
		{
			redirect('Welcome');
			return;
		}

		if ($this->Device_auth_model->current_device())
		{
			redirect('Welcome');
			return;
		}

		$data['error'] = $this->session->flashdata('device_gate_error');
		$this->load->view('device_gate', $data);
	}

	public function enroll()
	{
		$this->load->helper(array('url', 'app_lang'));
		$this->load->library('session');
		$this->load->model('Device_auth_model');

		if (strtoupper((string) $this->input->method(TRUE)) !== 'POST')
		{
			redirect('Device_gate');
			return;
		}

		if ( ! $this->session->userdata('user'))
		{
			redirect('Login');
			return;
		}

		if ( ! $this->Device_auth_model->is_lock_enabled())
		{
			redirect('Welcome');
			return;
		}

		if ($this->Device_auth_model->current_device())
		{
			redirect('Welcome');
			return;
		}

		$code = (string) $this->input->post('enrollment_code', TRUE);
		$device_name = trim((string) $this->input->post('device_name', TRUE));
		$code_row = $this->Device_auth_model->consume_enrollment_code($code);
		if ( ! $code_row)
		{
			$this->session->set_flashdata('device_gate_error', t('device_enroll_invalid'));
			redirect('Device_gate');
			return;
		}

		if ($device_name === '')
		{
			$device_name = ! empty($code_row->label) ? (string) $code_row->label : ('Device '.date('Y-m-d H:i'));
		}

		$result = $this->Device_auth_model->register_device(
			$device_name,
			(string) $this->input->user_agent(TRUE),
			$this->Device_auth_model->client_ip(),
			(string) $this->session->userdata('user')
		);

		if (empty($result['ok']))
		{
			$this->session->set_flashdata('device_gate_error', t('device_enroll_failed'));
			redirect('Device_gate');
			return;
		}

		$this->Device_auth_model->set_device_cookie($result['raw_token']);
		$this->session->set_flashdata('suss', 'device_enrolled');
		redirect('Welcome');
	}
}
