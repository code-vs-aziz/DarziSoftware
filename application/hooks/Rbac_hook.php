<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_hook
{
  public function authorize()
  {
    $CI =& get_instance();
    $CI->load->library('rbac_auth', NULL, 'rbac');
    $CI->rbac->enforce_current_route();
  }
}
?>
