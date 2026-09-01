<?php
/**
 * Minimal .env loader (no Composer dependency).
 * Loaded from index.php before CodeIgniter boots — do not add BASEPATH guard here.
 */
if ( ! function_exists('dz_env'))
{
	function dz_env($key, $default = NULL)
	{
		$key = (string) $key;
		if ($key === '')
		{
			return $default;
		}

		if (array_key_exists($key, $_ENV))
		{
			$value = $_ENV[$key];
			return ($value === '' && $default !== NULL) ? $default : $value;
		}

		$value = getenv($key);
		if ($value !== FALSE && $value !== '')
		{
			return $value;
		}

		return $default;
	}
}

if ( ! function_exists('dz_env_bool'))
{
	function dz_env_bool($key, $default = FALSE)
	{
		$value = dz_env($key, NULL);
		if ($value === NULL)
		{
			return (bool) $default;
		}
		$value = strtolower(trim((string) $value));
		return in_array($value, array('1', 'true', 'yes', 'on'), TRUE);
	}
}

if ( ! function_exists('dz_load_dotenv'))
{
	function dz_load_dotenv($path)
	{
		if ( ! is_readable($path))
		{
			return;
		}

		$lines = file($path, FILE_IGNORE_NEW_LINES);
		if ($lines === FALSE)
		{
			return;
		}

		foreach ($lines as $line)
		{
			$line = trim((string) $line);
			if ($line === '' || strpos($line, '#') === 0)
			{
				continue;
			}
			if (strpos($line, '=') === FALSE)
			{
				continue;
			}

			list($name, $value) = explode('=', $line, 2);
			$name = trim($name);
			$value = trim($value);
			if ($name === '')
			{
				continue;
			}

			if (strlen($value) >= 2)
			{
				$quote = $value[0];
				if (($quote === '"' || $quote === "'") && substr($value, -1) === $quote)
				{
					$value = substr($value, 1, -1);
				}
			}

			if ( ! array_key_exists($name, $_ENV))
			{
				$_ENV[$name] = $value;
				putenv($name.'='.$value);
			}
		}
	}
}
