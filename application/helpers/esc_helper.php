<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('esc_html'))
{
	/**
	 * Null-safe HTML escape for PHP 8.1+ views and forms.
	 */
	function esc_html($value, $double_encode = TRUE)
	{
		if ($value === NULL)
		{
			return '';
		}

		if (is_array($value))
		{
			foreach (array_keys($value) as $key)
			{
				$value[$key] = esc_html($value[$key], $double_encode);
			}

			return $value;
		}

		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', $double_encode);
	}
}
