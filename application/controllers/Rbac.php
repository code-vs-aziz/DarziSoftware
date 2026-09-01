<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac extends MY_Controller
{
	public function index()
	{
		redirect('Rbac/users');
	}

	public function users()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Rbac_model');
		$users = $this->Rbac_model->get_users();
		foreach ($users as $user)
		{
			$user_roles = $this->Rbac_model->get_user_roles($user->UNAME);
			$labels = array();
			foreach ($user_roles as $role)
			{
				$labels[] = $role->role_name;
			}
			$user->role_labels = implode(', ', $labels);
		}
		$data['users'] = $users;
		$data['roles'] = $this->Rbac_model->get_roles(TRUE);
		$data['current_user'] = (string) $this->session->userdata('user');
		$this->load->view('rbac_users', $data);
	}

	public function save_user()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Rbac_model');
		$username = trim((string) $this->input->post('username', TRUE));
		$password = trim((string) $this->input->post('password', TRUE));
		if ($username === '')
		{
			$this->session->set_flashdata('error', t('rbac_username_required'));
			redirect('Rbac/users');
			return;
		}
		$result = $this->Rbac_model->save_user($username, $password);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : t('rbac_save_failed'));
			redirect('Rbac/users');
			return;
		}
		if ( ! empty($result['created']))
		{
			$this->Rbac_model->set_user_roles($username, $this->input->post('roles') ? $this->input->post('roles') : array());
		}
		$this->session->set_flashdata('suss', 'yes');
		redirect('Rbac/users');
	}

	public function reset_password()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Rbac_model');
		$username = trim((string) $this->input->post('username', TRUE));
		$password = trim((string) $this->input->post('password', TRUE));
		$confirm = trim((string) $this->input->post('password_confirm', TRUE));
		if ($username === '')
		{
			$this->session->set_flashdata('error', t('rbac_username_required'));
			redirect('Rbac/users');
			return;
		}
		if ($password === '' || $password !== $confirm)
		{
			$this->session->set_flashdata('error', t('rbac_password_mismatch'));
			redirect('Rbac/users');
			return;
		}
		$result = $this->Rbac_model->reset_password($username, $password);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : t('rbac_save_failed'));
			redirect('Rbac/users');
			return;
		}
		$this->session->set_flashdata('suss', 'yes');
		redirect('Rbac/users');
	}

	public function delete_user()
	{
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Rbac_model');
		$username = trim((string) $this->input->post('username', TRUE));
		$current = trim((string) $this->session->userdata('user'));
		if ($username === '')
		{
			$this->session->set_flashdata('error', t('rbac_username_required'));
			redirect('Rbac/users');
			return;
		}
		$allowed = $this->Rbac_model->can_delete_user($username, $current);
		if (empty($allowed['ok']))
		{
			$this->session->set_flashdata('error', isset($allowed['message']) ? $allowed['message'] : t('rbac_delete_failed'));
			redirect('Rbac/users');
			return;
		}
		$result = $this->Rbac_model->delete_user($username);
		if (empty($result['ok']))
		{
			$this->session->set_flashdata('error', isset($result['message']) ? $result['message'] : t('rbac_delete_failed'));
			redirect('Rbac/users');
			return;
		}
		$this->session->set_flashdata('suss', 'yes');
		redirect('Rbac/users');
	}

	public function user_roles()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$username = urldecode((string) $this->uri->segment(3));
		$data['username'] = $username;
		$data['roles'] = $this->Rbac_model->get_roles(TRUE);
		$data['user_roles'] = $this->Rbac_model->get_user_roles($username);
		$this->load->view('rbac_user_roles', $data);
	}

	public function save_user_roles()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$username = trim((string) $this->input->post('username', TRUE));
		if ($username !== '')
		{
			$this->Rbac_model->set_user_roles($username, $this->input->post('roles') ? $this->input->post('roles') : array());
			$this->session->set_flashdata('suss', 'yes');
		}
		redirect('Rbac/users');
	}

	public function roles()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$data['roles'] = $this->Rbac_model->get_roles(FALSE);
		$this->load->view('rbac_roles', $data);
	}

	public function save_role()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$role_id = (int) $this->input->post('role_id');
		$data = array(
			'role_name' => trim((string) $this->input->post('role_name', TRUE)),
			'description' => trim((string) $this->input->post('description', TRUE)),
			'is_active' => $this->input->post('is_active') === '0' ? 0 : 1,
		);
		if ($data['role_name'] !== '')
		{
			$this->Rbac_model->save_role($data, $role_id > 0 ? $role_id : NULL);
			$this->session->set_flashdata('suss', 'yes');
		}
		redirect('Rbac/roles');
	}

	public function permissions()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$role_id = (int) $this->uri->segment(3);
		$data['role'] = $this->Rbac_model->get_role($role_id);
		$data['permissions'] = $this->Rbac_model->get_permissions();
		$data['role_permissions'] = $this->Rbac_model->get_role_permissions($role_id);
		$this->load->view('rbac_permissions', $data);
	}

	public function save_permissions()
	{
		$this->load->helper('url');
		$this->load->model('Rbac_model');
		$role_id = (int) $this->input->post('role_id');
		$this->Rbac_model->set_role_permissions($role_id, $this->input->post('permissions') ? $this->input->post('permissions') : array());
		$this->session->set_flashdata('suss', 'yes');
		redirect('Rbac/permissions/'.$role_id);
	}
}
?>
