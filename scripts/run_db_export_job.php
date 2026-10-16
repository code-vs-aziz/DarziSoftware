<?php
/**
 * Background worker for Configuration → Database Backup export jobs.
 *
 * Usage: php scripts/run_db_export_job.php <job_id>
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');
@set_time_limit(0);

$job_id = isset($argv[1]) ? preg_replace('/[^a-f0-9]/', '', strtolower((string) $argv[1])) : '';
if (strlen($job_id) < 16)
{
	fwrite(STDERR, "Usage: php scripts/run_db_export_job.php <job_id>\n");
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
		return Export_Job_CI::$instance;
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

class Export_Job_CI {
	public static $instance;
	public $db;
	public $load;

	public function __construct($db)
	{
		$this->db = $db;
		$this->load = new Export_Job_Loader($this);
	}
}

class Export_Job_Loader {
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
Export_Job_CI::$instance = new Export_Job_CI($db);

require_once APPPATH.'models/Database_backup_model.php';
$model = new Database_backup_model();
$ok = $model->run_export_job($job_id);
exit($ok ? 0 : 1);
