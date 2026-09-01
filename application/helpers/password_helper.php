<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('app_password_hash'))
{
	function app_password_hash($plain)
	{
		return password_hash((string) $plain, PASSWORD_DEFAULT);
	}
}

if ( ! function_exists('app_password_verify'))
{
	function app_password_verify($plain, $stored)
	{
		$plain = (string) $plain;
		$stored = (string) $stored;

		if ($plain === '' || $stored === '')
		{
			return FALSE;
		}

		if (app_password_is_hashed($stored))
		{
			return password_verify($plain, $stored);
		}

		return hash_equals($stored, $plain);
	}
}

if ( ! function_exists('app_password_is_hashed'))
{
	function app_password_is_hashed($stored)
	{
		$stored = (string) $stored;

		return (bool) preg_match('/^\$2[ayb]\$.{56}$/', $stored)
			|| strpos($stored, '$argon2') === 0;
	}
}

if ( ! function_exists('app_password_needs_rehash'))
{
	function app_password_needs_rehash($stored)
	{
		$stored = (string) $stored;

		if ( ! app_password_is_hashed($stored))
		{
			return TRUE;
		}

		return password_needs_rehash($stored, PASSWORD_DEFAULT);
	}
}
