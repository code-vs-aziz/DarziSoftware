<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
if (isset($body_class)) {
	echo ui_page_open($body_class);
} else {
	echo ui_page_open('erp-app');
}
