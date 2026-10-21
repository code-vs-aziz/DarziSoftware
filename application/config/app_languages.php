<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Application languages (single source of truth)
|--------------------------------------------------------------------------
| Each code maps to UI metadata. Translation strings live in:
|   application/language/{code}/*_lang.php
|
| Use t('key') in views/controllers. Never hardcode user-facing text.
*/
$config['app_default_language'] = 'english';

$config['app_language_codes'] = array('english', 'urdu', 'arabic');

$config['app_languages'] = array(
	'english' => array(
		'label'      => 'EN',
		'native'     => 'English',
		'html_lang'  => 'en',
		'dir'        => 'ltr',
		'css_class'  => 'app-lang-en',
		'rtl'        => FALSE,
	),
	/* Keep layout LTR like English — only strings/fonts change with language. */
	'urdu' => array(
		'label'      => 'اردو',
		'native'     => 'Urdu',
		'html_lang'  => 'ur',
		'dir'        => 'ltr',
		'css_class'  => 'app-lang-ur',
		'rtl'        => FALSE,
	),
	'arabic' => array(
		'label'      => 'العربية',
		'native'     => 'Arabic',
		'html_lang'  => 'ar',
		'dir'        => 'ltr',
		'css_class'  => 'app-lang-ar',
		'rtl'        => FALSE,
	),
);

$config['app_lang_files'] = array(
	'app',
	'menu',
	'auth',
	'customer',
	'measurement',
	'order',
	'transfer',
	'cash',
	'report',
	'master',
	'config',
	'rbac',
	'print',
	'js',
	'notification',
	'offline',
);
