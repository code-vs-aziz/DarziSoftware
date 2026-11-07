<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('measurement_triple_num'))
{
	/**
	 * @param mixed $v
	 * @return float|null
	 */
	function measurement_triple_num($v)
	{
		$v = trim((string) $v);
		if ($v === '' || ! is_numeric($v))
		{
			return NULL;
		}
		return (float) $v;
	}
}

if ( ! function_exists('measurement_split_triple'))
{
	/**
	 * Split stored geera into اوپر / درمیان / نیچے.
	 *
	 * Canonical (new) store order: اوپر/درمیان/نیچے (top → bottom).
	 * Legacy old-ERP often stored نیچے/اوپر or نیچے/درمیان/اوپر
	 * (larger hem first). Detect and remap so نیچے gets the larger value.
	 *
	 * @param mixed $raw
	 * @return array{0:string,1:string,2:string}  [upar, darmiyan, neeche]
	 */
	function measurement_split_triple($raw)
	{
		$raw = trim((string) $raw);
		if ($raw === '')
		{
			return array('', '', '');
		}

		$parts = preg_split('/\s*[\/|,]\s*/u', $raw);
		if ( ! is_array($parts))
		{
			return array($raw, $raw, $raw);
		}

		$clean = array();
		foreach ($parts as $p)
		{
			$clean[] = trim((string) $p);
		}

		// Drop trailing empties from "23/" style, but keep mid empties in "23//24".
		while (count($clean) > 1 && end($clean) === '')
		{
			array_pop($clean);
		}

		$n = count($clean);
		if ($n <= 1)
		{
			$v = isset($clean[0]) ? $clean[0] : $raw;
			return array($v, $v, $v);
		}

		if ($n === 2)
		{
			$a = $clean[0];
			$b = $clean[1];
			$na = measurement_triple_num($a);
			$nb = measurement_triple_num($b);
			// Legacy: larger first = نیچے / اوپر
			if ($na !== NULL && $nb !== NULL && $na > $nb)
			{
				return array($b, '', $a);
			}
			// New / equal: اوپر / نیچے
			return array($a, '', $b);
		}

		// 3+ parts — use first three.
		$a = isset($clean[0]) ? $clean[0] : '';
		$b = isset($clean[1]) ? $clean[1] : '';
		$c = isset($clean[2]) ? $clean[2] : '';
		$na = measurement_triple_num($a);
		$nc = measurement_triple_num($c);

		// Legacy reverse: نیچے/درمیان/اوپر when first > last (hem larger first).
		if ($na !== NULL && $nc !== NULL && $na > $nc)
		{
			return array($c, $b, $a);
		}

		// Canonical: اوپر/درمیان/نیچے
		return array($a, $b, $c);
	}
}

if ( ! function_exists('measurement_join_triple'))
{
	/**
	 * Join as canonical اوپر/درمیان/نیچے.
	 *
	 * @param mixed $top
	 * @param mixed $mid
	 * @param mixed $bottom
	 * @return string
	 */
	function measurement_join_triple($top, $mid, $bottom)
	{
		$a = trim((string) $top);
		$b = trim((string) $mid);
		$c = trim((string) $bottom);
		if ($a === '' && $b === '' && $c === '')
		{
			return '';
		}
		if ($b === '' && $c === '')
		{
			return $a;
		}
		if ($a === $b && $b === $c)
		{
			return $a;
		}
		return $a.'/'.$b.'/'.$c;
	}
}

if ( ! function_exists('measurement_join_triple_from_post'))
{
	/**
	 * @param object $CI
	 * @param string $prefix  e.g. geera or SGeera
	 * @return string
	 */
	function measurement_join_triple_from_post($CI, $prefix)
	{
		$prefix = (string) $prefix;
		$top = $CI->input->post($prefix.'_upar', TRUE);
		$mid = $CI->input->post($prefix.'_darmiyan', TRUE);
		$bot = $CI->input->post($prefix.'_neeche', TRUE);
		if ($top === NULL && $mid === NULL && $bot === NULL)
		{
			$legacy = $CI->input->post($prefix, TRUE);
			return $legacy === NULL ? '' : trim((string) $legacy);
		}
		return measurement_join_triple($top, $mid, $bot);
	}
}
