<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('app_take_flash'))
{
	/**
	 * Read flash once and remove it immediately so AJAX/parallel requests
	 * cannot keep showing the same green success banner on every page.
	 *
	 * @param string $key
	 * @return mixed
	 */
	function app_take_flash($key)
	{
		$key = trim((string) $key);
		if ($key === '' || session_status() !== PHP_SESSION_ACTIVE)
		{
			return NULL;
		}

		$value = NULL;
		if (isset($_SESSION['__ci_vars'][$key], $_SESSION[$key]) && ! is_int($_SESSION['__ci_vars'][$key]))
		{
			$value = $_SESSION[$key];
		}
		elseif (isset($_SESSION[$key]) && ! isset($_SESSION['__ci_vars'][$key]))
		{
			// Orphan userdata left by older bugs — treat as one-shot then delete.
			$value = $_SESSION[$key];
		}

		unset($_SESSION[$key]);
		if (isset($_SESSION['__ci_vars'][$key]))
		{
			unset($_SESSION['__ci_vars'][$key]);
			if (empty($_SESSION['__ci_vars']))
			{
				unset($_SESSION['__ci_vars']);
			}
		}

		return $value;
	}
}

if ( ! function_exists('app_set_success_flash'))
{
	/**
	 * @param string $message
	 * @return void
	 */
	function app_set_success_flash($message)
	{
		$CI =& get_instance();
		$CI->session->set_flashdata('success_msg', (string) $message);
		$CI->session->set_flashdata('suss', 'yes');
	}
}
