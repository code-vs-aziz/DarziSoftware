<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('ui_component'))
{
	function ui_component($name, array $data = array())
	{
		$CI =& get_instance();
		return $CI->load->view('components/'.$name, $data, TRUE);
	}
}

if ( ! function_exists('ui_btn'))
{
	function ui_btn(array $opts = array())
	{
		return ui_component('button', $opts);
	}
}

if ( ! function_exists('ui_stat'))
{
	function ui_stat(array $opts = array())
	{
		return ui_component('stat_card', $opts);
	}
}

if ( ! function_exists('ui_alert'))
{
	function ui_alert($message, $type = 'error')
	{
		return ui_component('alert', array('message' => $message, 'type' => $type));
	}
}

if ( ! function_exists('ui_module_open'))
{
	function ui_module_open($title, $actions = '', $class = '')
	{
		return ui_component('card_open', array(
			'title' => $title,
			'actions' => $actions,
			'class' => $class,
		));
	}
}

if ( ! function_exists('ui_module_close'))
{
	function ui_module_close()
	{
		return ui_component('card_close');
	}
}

if ( ! function_exists('ui_field'))
{
	function ui_field(array $opts = array())
	{
		return ui_component('field', $opts);
	}
}

if ( ! function_exists('ui_icon'))
{
	/**
	 * Render a Font Awesome icon with consistent classes.
	 *
	 * @param string $name  Icon name, with or without fa- prefix.
	 * @param string $style Font Awesome style: fas, far, fab.
	 */
	function ui_icon($name, $style = 'fas')
	{
		$name = trim((string) $name);
		if ($name === '') {
			return '';
		}
		if (strpos($name, 'fa-') !== 0) {
			$name = 'fa-'.$name;
		}
		$style = in_array($style, array('fas', 'far', 'fab', 'fal'), TRUE) ? $style : 'fas';
		return '<i class="'.esc_html($style).' '.esc_html($name).'" aria-hidden="true"></i>';
	}
}

if ( ! function_exists('ui_page_open'))
{
	function ui_page_open($body_class = 'erp-app')
	{
		$CI =& get_instance();
		return $CI->load->view('layout/page_open', array('body_class' => $body_class), TRUE);
	}
}

if ( ! function_exists('ui_page_close'))
{
	function ui_page_close()
	{
		$CI =& get_instance();
		return $CI->load->view('layout/page_close', TRUE);
	}
}
