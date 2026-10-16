<?php
/**
 * CLI smoke test for Database_backup_model (export → import round-trip).
 *
 * Usage:
 *   php -d upload_max_filesize=2048M -d post_max_size=2048M -d memory_limit=512M scripts/test_db_backup.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
chdir($root);

require $root.'/application/config/env_bootstrap.php';
dz_load_dotenv($root.'/.env');

define('BASEPATH', $root.'/system/');
define('APPPATH', $root.'/application/');
define('FCPATH', $root.'/');
define('ENVIRONMENT', dz_env('CI_ENV', 'development'));

if ( ! function_exists('log_message'))
{
	function log_message($level, $message) {}
}
if ( ! function_exists('get_instance'))
{
	function &get_instance()
	{
		return Test_CI_Stub::$instance;
	}
}
if ( ! class_exists('CI_Model', FALSE))
{
	class CI_Model {
		public function __get($key)
		{
			$CI =& get_instance();
			return $CI->$key;
		}
	}
}

class Test_CI_Stub {
	public static $instance;
	public $db;
	public $load;

	public function __construct($db)
	{
		$this->db = $db;
		$this->load = new Test_Loader($this);
	}
}

class Test_Loader {
	protected $CI;
	public function __construct($CI) { $this->CI = $CI; }
	public function dbutil()
	{
		require_once BASEPATH.'database/DB_utility.php';
		require_once BASEPATH.'database/drivers/mysqli/mysqli_utility.php';
		$this->CI->dbutil = new CI_DB_mysqli_utility($this->CI->db);
	}
}

require_once BASEPATH.'database/DB.php';
$db =& DB('', TRUE);

Test_CI_Stub::$instance = new Test_CI_Stub($db);

require_once APPPATH.'models/Database_backup_model.php';
$model = new Database_backup_model();

echo "Limits:\n";
print_r($model->upload_limits());

$marker = 'dz_backup_test_'.time();
$db->query('CREATE TABLE IF NOT EXISTS `'.$marker.'` (`id` INT PRIMARY KEY)');
if ( ! $db->table_exists($marker))
{
	fwrite(STDERR, "Could not create marker table.\n");
	exit(1);
}
echo "Marker created: {$marker}\n";

echo "\n=== EXPORT (includes marker) ===\n";
$export = $model->create_dump('test_export');
if (empty($export['ok']))
{
	@$db->query('DROP TABLE IF EXISTS `'.$marker.'`');
	fwrite(STDERR, 'EXPORT FAILED: '.(isset($export['error']) ? $export['error'] : '?').PHP_EOL);
	exit(1);
}
echo 'OK method='.$export['method'].' file='.$export['filename'].' bytes='.$export['bytes'].PHP_EOL;

$db->query('DROP TABLE IF EXISTS `'.$marker.'`');
echo 'Marker dropped before import: '.($db->table_exists($marker) ? 'still there (BAD)' : 'gone').PHP_EOL;

echo "\n=== IMPORT (should restore marker) ===\n";
$import = $model->import_uploaded_file($export['path'], $export['filename']);
@unlink($export['path']);

if (empty($import['ok']))
{
	fwrite(STDERR, 'IMPORT FAILED: '.(isset($import['error']) ? $import['error'] : '?').PHP_EOL);
	exit(1);
}

$restored = $db->table_exists($marker);
echo 'OK method='.$import['method'].' safety='.(isset($import['safety_filename']) ? $import['safety_filename'] : '').PHP_EOL;
echo 'Marker restored after import: '.($restored ? 'yes (good)' : 'NO (BAD)').PHP_EOL;

@$db->query('DROP TABLE IF EXISTS `'.$marker.'`');

if ( ! $restored)
{
	fwrite(STDERR, "Round-trip failed: marker table was not restored.\n");
	exit(1);
}

echo "\nAll backup smoke tests passed.\n";
exit(0);
