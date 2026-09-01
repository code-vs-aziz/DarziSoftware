<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function index()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->view('login');
	}

	public function process()
	{
		$this->load->helper(array('url', 'password', 'app_lang'));
		$this->load->library('session');

		$locked_until = (int) $this->session->userdata('login_locked_until');
		if ($locked_until > time())
		{
			$this->session->set_flashdata('error', t('auth_too_many_attempts'));
			redirect('Login');
			return;
		}

		$user = trim((string) $this->input->post('email'));
		$pass = (string) $this->input->post('password');

		$this->load->model('login_model');
		$row = $this->login_model->get_user_by_username($user);

		if ($row && app_password_verify($pass, $row->UPASS))
		{
			if (app_password_needs_rehash($row->UPASS))
			{
				$this->login_model->update_password_hash($user, app_password_hash($pass));
			}

			$lang = app_resolve_language();
			$this->session->unset_userdata(array('login_attempts', 'login_locked_until'));
			$this->session->set_userdata('user', $user);
			app_set_language($lang);
			redirect('Welcome');
			return;
		}

		$attempts = (int) $this->session->userdata('login_attempts') + 1;
		$this->session->set_userdata('login_attempts', $attempts);
		if ($attempts >= 5)
		{
			$this->session->set_userdata('login_locked_until', time() + 900);
			$this->session->unset_userdata('login_attempts');
		}

		$this->session->set_flashdata('error', t('auth_invalid_account'));
		redirect('Login');
	}

	public function logout()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->session->unset_userdata('user');
		$this->session->sess_destroy();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->output->set_header('Pragma: no-cache');
		redirect(site_url('Login'), 'location', 302);
	}
}
