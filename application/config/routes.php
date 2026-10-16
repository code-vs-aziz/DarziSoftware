<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Darzi Baba public website API
|--------------------------------------------------------------------------
*/
$route['api/health'] = 'api/content/health';
$route['api/content/home'] = 'api/content/home';
$route['api/content/page/(:any)'] = 'api/content/page/$1';
$route['api/auth/lookup'] = 'api/auth/lookup';
$route['api/auth/verify'] = 'api/auth/verify';
$route['api/auth/logout'] = 'api/auth/logout';
$route['api/auth/me'] = 'api/auth/me';
$route['api/measurements/catalog'] = 'api/measurements/catalog';
$route['api/measurements/me'] = 'api/measurements/me';
$route['api/measurements/submit'] = 'api/measurements/submit';
$route['api/shop/categories'] = 'api/shop/categories';
$route['api/shop/products'] = 'api/shop/products';
$route['api/shop/product/(:any)'] = 'api/shop/product/$1';
$route['api/shop/checkout'] = 'api/shop/checkout';
$route['api/shop/order/(:any)'] = 'api/shop/order/$1';

/*
|--------------------------------------------------------------------------
| Staff offline-first sync API
|--------------------------------------------------------------------------
*/
$route['api/erp_sync/health'] = 'api/erp_sync/health';
$route['api/erp_sync/capabilities'] = 'api/erp_sync/capabilities';
$route['api/erp_sync/issue_token'] = 'api/erp_sync/issue_token';
$route['api/erp_sync/revoke_token'] = 'api/erp_sync/revoke_token';
$route['api/erp_sync/bootstrap'] = 'api/erp_sync/bootstrap';
$route['api/erp_sync/pull'] = 'api/erp_sync/pull';
$route['api/erp_sync/push'] = 'api/erp_sync/push';
