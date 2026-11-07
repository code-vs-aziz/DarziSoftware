<?php
/**
 * Start a DB import job from a file already on the server (bypasses browser/Cloudflare upload).
 *
 * Usage: php scripts/start_db_import_file.php /path/to/backup.sql.gz
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
@set_time_limit(0);

$path = isset($argv[1]) ? (string) $argv[1] : '';
if ($path === '' || ! is_file($path))
{
	fwrite(STDERR, "Usage: php scripts/start_db_import_file.php /path/to/backup.sql.gz\n");
	exit(1);
}

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
		return Import_Start_CI::$instance;
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

class Import_Start_CI {
	public static $instance;
	public $db;
	public $load;
	public function __construct($db)
	{
		$this->db = $db;
		$this->load = new Import_Start_Loader($this);
	}
}

class Import_Start_Loader {
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
Import_Start_CI::$instance = new Import_Start_CI($db);

require_once APPPATH.'models/Database_backup_model.php';
$model = new Database_backup_model();
$result = $model->start_import_job($path, basename($path));
if (empty($result['ok']))
{
	fwrite(STDERR, (isset($result['error']) ? $result['error'] : 'Failed to start import')."\n");
	exit(1);
}

echo 'JOB_ID='.$result['job_id']."\n";
exit(0);
