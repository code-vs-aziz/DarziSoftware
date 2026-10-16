<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Full MySQL/MariaDB dump and restore for the configured ERP database.
 *
 * Prefers mysqldump/mysql CLI (same approach as ops docs); falls back to
 * CodeIgniter dbutil + mysqli multi_query when CLI tools are unavailable.
 */
class Database_backup_model extends CI_Model
{
	const REL_DIR = 'backups/database';
	const MAX_UPLOAD_BYTES = 2147483648; // 2 GiB app-level cap
	const SAFETY_KEEP = 5;

	/** @var string|null Last human-readable error */
	protected $last_error = '';

	public function last_error()
	{
		return $this->last_error;
	}

	/**
	 * Absolute path to the secure backup directory (creates if needed).
	 *
	 * @return string|false
	 */
	public function backup_directory()
	{
		$dir = rtrim(APPPATH, '/\\').DIRECTORY_SEPARATOR
			.str_replace('/', DIRECTORY_SEPARATOR, self::REL_DIR);

		if ( ! is_dir($dir))
		{
			if ( ! @mkdir($dir, 0750, TRUE))
			{
				$this->last_error = 'Could not create backup directory.';
				return FALSE;
			}
		}

		$htaccess = $dir.DIRECTORY_SEPARATOR.'.htaccess';
		if ( ! is_file($htaccess))
		{
			@file_put_contents($htaccess, "Deny from all\n");
		}

		$index = $dir.DIRECTORY_SEPARATOR.'index.html';
		if ( ! is_file($index))
		{
			@file_put_contents($index, '');
		}

		if ( ! is_writable($dir))
		{
			$this->last_error = 'Backup directory is not writable.';
			return FALSE;
		}

		return $dir;
	}

	/**
	 * PHP upload/post limits that affect import (bytes).
	 *
	 * @return array
	 */
	public function upload_limits()
	{
		$upload = $this->ini_bytes(ini_get('upload_max_filesize'));
		$post = $this->ini_bytes(ini_get('post_max_size'));
		$effective = min(
			self::MAX_UPLOAD_BYTES,
			$upload > 0 ? $upload : self::MAX_UPLOAD_BYTES,
			$post > 0 ? $post : self::MAX_UPLOAD_BYTES
		);

		return array(
			'upload_max_filesize' => $upload,
			'post_max_size' => $post,
			'effective_max' => $effective,
			'cli_mysqldump' => $this->find_binary('mysqldump') !== '',
			'cli_mysql' => $this->find_binary('mysql') !== '',
		);
	}

	/**
	 * Create a gzipped SQL dump and return file metadata.
	 *
	 * @param string   $prefix      Filename prefix (e.g. export, safety)
	 * @param callable|null $on_progress function ($percent, $phase, $message)
	 * @return array{ok:bool,path?:string,filename?:string,method?:string,error?:string,bytes?:int}
	 */
	public function create_dump($prefix = 'export', $on_progress = NULL)
	{
		$this->last_error = '';
		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return array('ok' => FALSE, 'error' => $this->last_error);
		}

		$this->report_progress($on_progress, 2, 'preparing', 'Preparing export…');

		$stamp = date('Y-m-d_H-i-s');
		$db_name = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->db->database);
		if ($db_name === '')
		{
			$db_name = 'database';
		}
		$base = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $prefix);
		if ($base === '')
		{
			$base = 'export';
		}
		$filename = $base.'_'.$db_name.'_'.$stamp.'.sql.gz';
		$path = $dir.DIRECTORY_SEPARATOR.$filename;
		$sql_path = $dir.DIRECTORY_SEPARATOR.$base.'_'.$db_name.'_'.$stamp.'.sql';

		$method = 'mysqldump';
		$ok = $this->dump_via_mysqldump_progressive($sql_path, $on_progress);
		if ( ! $ok)
		{
			$method = 'dbutil';
			$this->report_progress($on_progress, 8, 'dumping', 'Using PHP fallback export…');
			$ok = $this->dump_via_dbutil($sql_path);
			if ($ok)
			{
				$this->report_progress($on_progress, 82, 'dumping', 'SQL dump finished');
			}
		}

		if ( ! $ok || ! is_file($sql_path) || filesize($sql_path) < 20)
		{
			@unlink($sql_path);
			$error = $this->last_error !== '' ? $this->last_error : 'Database export failed.';
			return array('ok' => FALSE, 'error' => $error);
		}

		$this->report_progress($on_progress, 88, 'compressing', 'Compressing backup file…');
		$gz = $this->gzip_file($sql_path, $path, $on_progress);
		@unlink($sql_path);
		if ( ! $gz)
		{
			@unlink($path);
			return array('ok' => FALSE, 'error' => $this->last_error !== '' ? $this->last_error : 'Could not compress backup.');
		}

		$this->report_progress($on_progress, 100, 'ready', 'Backup ready to download');

		return array(
			'ok' => TRUE,
			'path' => $path,
			'filename' => $filename,
			'method' => $method,
			'bytes' => (int) filesize($path),
		);
	}

	/**
	 * Start a background export job (so the UI can poll progress).
	 *
	 * @return array{ok:bool,job_id?:string,error?:string}
	 */
	public function start_export_job()
	{
		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return array('ok' => FALSE, 'error' => $this->last_error);
		}

		$job_id = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
		$token = bin2hex(function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16));
		$now = time();
		$job = array(
			'id' => $job_id,
			'token' => $token,
			'status' => 'queued',
			'percent' => 0,
			'phase' => 'queued',
			'message' => 'Starting export…',
			'filename' => '',
			'path' => '',
			'bytes' => 0,
			'error' => '',
			'created_at' => $now,
			'updated_at' => $now,
		);
		if ( ! $this->write_export_job($job_id, $job))
		{
			return array('ok' => FALSE, 'error' => 'Could not create export job.');
		}

		$php = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
		$script = rtrim(FCPATH, '/\\').DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'run_db_export_job.php';
		if ( ! is_file($script))
		{
			$this->update_export_job($job_id, array(
				'status' => 'error',
				'phase' => 'error',
				'percent' => 0,
				'error' => 'Export worker script missing.',
				'message' => 'Export worker script missing.',
			));
			return array('ok' => FALSE, 'error' => 'Export worker script missing.');
		}

		$cmd = escapeshellarg($php)
			.' '.escapeshellarg($script)
			.' '.escapeshellarg($job_id)
			.' > /dev/null 2>&1 &';
		@exec($cmd);

		return array('ok' => TRUE, 'job_id' => $job_id);
	}

	/**
	 * @param string $job_id
	 * @return array|null
	 */
	public function get_export_job($job_id)
	{
		$job_id = preg_replace('/[^a-f0-9]/', '', strtolower((string) $job_id));
		if (strlen($job_id) < 16)
		{
			return NULL;
		}
		$path = $this->export_job_path($job_id);
		if ($path === FALSE || ! is_file($path))
		{
			return NULL;
		}
		$raw = @file_get_contents($path);
		$data = json_decode((string) $raw, TRUE);
		return is_array($data) ? $data : NULL;
	}

	/**
	 * Public status payload for AJAX (no filesystem paths).
	 *
	 * @param string $job_id
	 * @return array
	 */
	public function export_job_status($job_id)
	{
		$job = $this->get_export_job($job_id);
		if ( ! $job)
		{
			return array('ok' => FALSE, 'error' => 'Export job not found.');
		}

		return array(
			'ok' => TRUE,
			'job_id' => isset($job['id']) ? $job['id'] : $job_id,
			'status' => isset($job['status']) ? $job['status'] : 'unknown',
			'percent' => isset($job['percent']) ? (int) $job['percent'] : 0,
			'phase' => isset($job['phase']) ? $job['phase'] : '',
			'message' => isset($job['message']) ? $job['message'] : '',
			'filename' => isset($job['filename']) ? $job['filename'] : '',
			'bytes' => isset($job['bytes']) ? (int) $job['bytes'] : 0,
			'bytes_label' => $this->format_bytes(isset($job['bytes']) ? (int) $job['bytes'] : 0),
			'error' => isset($job['error']) ? $job['error'] : '',
			'token' => (isset($job['status']) && $job['status'] === 'ready' && ! empty($job['token']))
				? $job['token'] : '',
		);
	}

	/**
	 * Resolve download path when job is ready and token matches.
	 *
	 * @param string $job_id
	 * @param string $token
	 * @return array{ok:bool,path?:string,filename?:string,error?:string}
	 */
	public function claim_export_download($job_id, $token)
	{
		$job = $this->get_export_job($job_id);
		if ( ! $job)
		{
			return array('ok' => FALSE, 'error' => 'Export job not found.');
		}
		if (empty($job['status']) || $job['status'] !== 'ready')
		{
			return array('ok' => FALSE, 'error' => 'Export is not ready yet.');
		}
		if (empty($job['token']) || ! hash_equals((string) $job['token'], (string) $token))
		{
			return array('ok' => FALSE, 'error' => 'Invalid download token.');
		}
		if (empty($job['path']) || ! is_file($job['path']))
		{
			return array('ok' => FALSE, 'error' => 'Export file missing.');
		}

		return array(
			'ok' => TRUE,
			'path' => $job['path'],
			'filename' => ! empty($job['filename']) ? $job['filename'] : 'database_export.sql.gz',
		);
	}

	/**
	 * Run export job (CLI worker).
	 *
	 * @param string $job_id
	 * @return bool
	 */
	public function run_export_job($job_id)
	{
		@set_time_limit(0);
		$job = $this->get_export_job($job_id);
		if ( ! $job)
		{
			return FALSE;
		}

		$this->update_export_job($job_id, array(
			'status' => 'running',
			'phase' => 'preparing',
			'percent' => 1,
			'message' => 'Preparing export…',
			'error' => '',
		));

		$self = $this;
		$result = $this->create_dump('export', function ($percent, $phase, $message) use ($self, $job_id) {
			$self->update_export_job($job_id, array(
				'status' => 'running',
				'percent' => (int) $percent,
				'phase' => (string) $phase,
				'message' => (string) $message,
			));
		});

		if (empty($result['ok']))
		{
			$this->update_export_job($job_id, array(
				'status' => 'error',
				'phase' => 'error',
				'percent' => 0,
				'message' => isset($result['error']) ? $result['error'] : 'Export failed.',
				'error' => isset($result['error']) ? $result['error'] : 'Export failed.',
			));
			return FALSE;
		}

		$this->update_export_job($job_id, array(
			'status' => 'ready',
			'phase' => 'ready',
			'percent' => 100,
			'message' => 'Backup ready — starting download…',
			'filename' => isset($result['filename']) ? $result['filename'] : '',
			'path' => isset($result['path']) ? $result['path'] : '',
			'bytes' => isset($result['bytes']) ? (int) $result['bytes'] : 0,
			'error' => '',
		));
		return TRUE;
	}

	/**
	 * Mark job consumed and remove job metadata (file may already be unlinked by download).
	 *
	 * @param string $job_id
	 */
	public function finish_export_job($job_id)
	{
		$path = $this->export_job_path($job_id);
		if ($path !== FALSE && is_file($path))
		{
			@unlink($path);
		}
	}

	/**
	 * Restore database from an uploaded .sql or .sql.gz file.
	 * Creates a safety dump first; rolls back to it if restore fails.
	 *
	 * @param string $tmp_path Uploaded temp path
	 * @param string $original_name Client filename
	 * @return array{ok:bool,error?:string,safety_filename?:string,method?:string}
	 */
	public function import_uploaded_file($tmp_path, $original_name)
	{
		$this->last_error = '';
		@set_time_limit(0);

		$validation = $this->validate_upload($tmp_path, $original_name);
		if ( ! $validation['ok'])
		{
			return $validation;
		}

		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return array('ok' => FALSE, 'error' => $this->last_error);
		}

		$staging = $dir.DIRECTORY_SEPARATOR.'import_staging_'.uniqid('', TRUE);
		if ( ! @copy($tmp_path, $staging))
		{
			$this->last_error = 'Could not stage uploaded backup file.';
			return array('ok' => FALSE, 'error' => $this->last_error);
		}

		$sql_path = $staging;
		$cleanup = array($staging);

		if ($validation['gzip'])
		{
			$ungz = $staging.'.sql';
			if ( ! $this->gunzip_file($staging, $ungz))
			{
				$this->cleanup_paths($cleanup);
				return array('ok' => FALSE, 'error' => $this->last_error !== '' ? $this->last_error : 'Could not decompress backup.');
			}
			$sql_path = $ungz;
			$cleanup[] = $ungz;
		}

		$probe = $this->probe_sql_file($sql_path);
		if ( ! $probe['ok'])
		{
			$this->cleanup_paths($cleanup);
			return $probe;
		}

		$safety = $this->create_dump('safety');
		if (empty($safety['ok']))
		{
			$this->cleanup_paths($cleanup);
			return array(
				'ok' => FALSE,
				'error' => 'Import aborted: could not create safety backup before restore. '
					.(isset($safety['error']) ? $safety['error'] : ''),
			);
		}

		if ( ! $this->wipe_database_objects())
		{
			$rollback_error = $this->last_error !== '' ? $this->last_error : 'Could not clear database before import.';
			$rolled = $this->rollback_from_safety(isset($safety['path']) ? $safety['path'] : '');
			$this->cleanup_paths($cleanup);
			$this->prune_old_safety_files();
			$msg = 'Import aborted: '.$rollback_error;
			if ($rolled)
			{
				$msg .= ' Previous database was restored from the automatic safety backup.';
			}
			return array(
				'ok' => FALSE,
				'error' => $msg,
				'safety_filename' => isset($safety['filename']) ? $safety['filename'] : '',
			);
		}

		$method = 'mysql';
		$restored = $this->restore_via_mysql($sql_path);
		if ( ! $restored)
		{
			$method = 'mysqli';
			$restored = $this->restore_via_mysqli($sql_path);
		}

		if ( ! $restored)
		{
			$rollback_error = $this->last_error;
			$rolled = $this->rollback_from_safety(isset($safety['path']) ? $safety['path'] : '');
			$this->cleanup_paths($cleanup);
			$this->prune_old_safety_files();

			$msg = 'Import failed: '.$rollback_error;
			if ($rolled)
			{
				$msg .= ' Previous database was restored from the automatic safety backup.';
			}
			else
			{
				$msg .= ' Automatic rollback also failed. Safety file: '
					.(isset($safety['filename']) ? $safety['filename'] : 'unknown')
					.'. Contact an administrator before using the system.';
			}

			return array(
				'ok' => FALSE,
				'error' => $msg,
				'safety_filename' => isset($safety['filename']) ? $safety['filename'] : '',
			);
		}

		$this->cleanup_paths($cleanup);
		$this->prune_old_safety_files();
		$this->clear_app_caches();

		return array(
			'ok' => TRUE,
			'method' => $method,
			'safety_filename' => isset($safety['filename']) ? $safety['filename'] : '',
		);
	}

	/**
	 * @param string $tmp_path
	 * @param string $original_name
	 * @return array
	 */
	protected function validate_upload($tmp_path, $original_name)
	{
		if ($tmp_path === '' || ! is_file($tmp_path) || ! is_readable($tmp_path))
		{
			return array('ok' => FALSE, 'error' => 'Uploaded file is missing or unreadable.');
		}

		$size = (int) filesize($tmp_path);
		if ($size < 20)
		{
			return array('ok' => FALSE, 'error' => 'Uploaded file is empty or too small to be a valid backup.');
		}

		$limits = $this->upload_limits();
		if ($size > $limits['effective_max'])
		{
			return array(
				'ok' => FALSE,
				'error' => 'Backup file exceeds the maximum allowed size of '
					.$this->format_bytes($limits['effective_max']).'.',
			);
		}

		$name = strtolower((string) $original_name);
		$gzip = (bool) preg_match('/\.(sql\.gz|gz)$/', $name);
		$sql = (bool) preg_match('/\.sql$/', $name);

		if ( ! $gzip && ! $sql)
		{
			return array('ok' => FALSE, 'error' => 'Only .sql or .sql.gz backup files are allowed.');
		}

		// Reject obvious non-SQL binaries (PNG/PDF/ZIP signatures).
		$fh = @fopen($tmp_path, 'rb');
		if ($fh)
		{
			$head = fread($fh, 8);
			fclose($fh);
			if ($head !== FALSE)
			{
				$hex = bin2hex(substr($head, 0, 4));
				if (in_array($hex, array('89504e47', '25504446', '504b0304'), TRUE))
				{
					return array('ok' => FALSE, 'error' => 'File does not look like a SQL database backup.');
				}
				if ($gzip && substr($head, 0, 2) !== "\x1f\x8b")
				{
					return array('ok' => FALSE, 'error' => 'File extension is .gz but content is not gzip-compressed.');
				}
			}
		}

		return array('ok' => TRUE, 'gzip' => $gzip);
	}

	/**
	 * Light content checks before executing SQL.
	 *
	 * @param string $sql_path
	 * @return array
	 */
	protected function probe_sql_file($sql_path)
	{
		$fh = @fopen($sql_path, 'rb');
		if ( ! $fh)
		{
			return array('ok' => FALSE, 'error' => 'Could not read SQL backup for validation.');
		}

		$chunk = fread($fh, 65536);
		fclose($fh);
		if ($chunk === FALSE || trim($chunk) === '')
		{
			return array('ok' => FALSE, 'error' => 'SQL backup appears empty.');
		}

		$upper = strtoupper($chunk);
		if (strpos($upper, 'DROP DATABASE') !== FALSE)
		{
			return array('ok' => FALSE, 'error' => 'Backup contains DROP DATABASE and was rejected for safety.');
		}

		// Accept mysqldump / CI dbutil style dumps.
		$looks_sql = (
			strpos($upper, 'CREATE TABLE') !== FALSE
			|| strpos($upper, 'INSERT INTO') !== FALSE
			|| strpos($upper, 'DROP TABLE') !== FALSE
			|| strpos($chunk, '--') !== FALSE
			|| strpos($chunk, '/*') !== FALSE
		);
		if ( ! $looks_sql)
		{
			return array('ok' => FALSE, 'error' => 'File does not appear to be a valid SQL dump.');
		}

		return array('ok' => TRUE);
	}

	/**
	 * Full-database mysqldump (used when progress is not required).
	 *
	 * @param string $sql_path Destination .sql path
	 * @return bool
	 */
	protected function dump_via_mysqldump($sql_path)
	{
		return $this->dump_via_mysqldump_progressive($sql_path, NULL);
	}

	/**
	 * Table-by-table mysqldump so export UI can show real percentages.
	 *
	 * @param string        $sql_path
	 * @param callable|null $on_progress
	 * @return bool
	 */
	protected function dump_via_mysqldump_progressive($sql_path, $on_progress = NULL)
	{
		$bin = $this->find_binary('mysqldump');
		if ($bin === '')
		{
			$this->last_error = 'mysqldump not found.';
			return FALSE;
		}

		$cnf = $this->write_client_cnf();
		if ($cnf === FALSE)
		{
			return FALSE;
		}

		$tables = $this->db->list_tables();
		if ( ! is_array($tables) || count($tables) === 0)
		{
			@unlink($cnf);
			$this->last_error = 'No tables found to export.';
			return FALSE;
		}

		$header = "-- Darzibaba ERP backup\n"
			."-- Generated: ".date('c')."\n"
			."-- Database: ".$this->db->database."\n\n"
			."SET NAMES utf8mb4;\n"
			."SET FOREIGN_KEY_CHECKS=0;\n\n";
		if (@file_put_contents($sql_path, $header) === FALSE)
		{
			@unlink($cnf);
			$this->last_error = 'Could not write SQL dump file.';
			return FALSE;
		}

		$this->report_progress($on_progress, 5, 'dumping', 'Starting table export…');

		$defaults = escapeshellarg($cnf);
		$db = escapeshellarg($this->db->database);
		$out = escapeshellarg($sql_path);
		$total = count($tables);
		$base_flags = $bin
			.' --defaults-extra-file='.$defaults
			.' --single-transaction'
			.' --hex-blob'
			.' --skip-lock-tables'
			.' --default-character-set=utf8mb4'
			.' --add-drop-table'
			.' --comments';

		foreach ($tables as $i => $table)
		{
			$table = (string) $table;
			$cmd = $base_flags
				.' --triggers'
				.' '.$db.' '.escapeshellarg($table)
				.' >> '.$out
				.' 2>&1';
			$output = array();
			$code = 1;
			@exec($cmd, $output, $code);
			if ((int) $code !== 0)
			{
				@unlink($cnf);
				@unlink($sql_path);
				$this->last_error = 'mysqldump failed on table '.$table
					.($output ? (': '.implode(' ', $output)) : '.');
				return FALSE;
			}

			$pct = 5 + (int) floor((($i + 1) / $total) * 72);
			$this->report_progress(
				$on_progress,
				$pct,
				'dumping',
				'Exporting table '.($i + 1).' of '.$total.': '.$table
			);
		}

		// Routines / events (once for the whole schema)
		$this->report_progress($on_progress, 80, 'dumping', 'Exporting routines and events…');
		$routines_cmd = $bin
			.' --defaults-extra-file='.$defaults
			.' --routines'
			.' --events'
			.' --no-create-info'
			.' --no-data'
			.' --no-create-db'
			.' --skip-triggers'
			.' --default-character-set=utf8mb4'
			.' '.$db
			.' >> '.$out
			.' 2>&1';
		$rout_out = array();
		$rout_code = 1;
		@exec($routines_cmd, $rout_out, $rout_code);
		// Non-fatal if routines dump fails on older MySQL
		if ((int) $rout_code !== 0 && $rout_out)
		{
			@file_put_contents(
				$sql_path,
				"\n-- routines/events dump skipped: ".implode(' ', $rout_out)."\n",
				FILE_APPEND
			);
		}

		@file_put_contents($sql_path, "\nSET FOREIGN_KEY_CHECKS=1;\n", FILE_APPEND);
		@unlink($cnf);

		if ( ! is_file($sql_path) || filesize($sql_path) < 20)
		{
			$this->last_error = 'mysqldump produced an empty file.';
			@unlink($sql_path);
			return FALSE;
		}

		$this->report_progress($on_progress, 85, 'dumping', 'SQL dump finished');
		return TRUE;
	}

	/**
	 * @param callable|null $on_progress
	 * @param int           $percent
	 * @param string        $phase
	 * @param string        $message
	 */
	protected function report_progress($on_progress, $percent, $phase, $message)
	{
		if (is_callable($on_progress))
		{
			call_user_func($on_progress, (int) $percent, (string) $phase, (string) $message);
		}
	}

	/**
	 * @param string $job_id
	 * @return string|false
	 */
	protected function export_job_path($job_id)
	{
		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return FALSE;
		}
		$job_id = preg_replace('/[^a-f0-9]/', '', strtolower((string) $job_id));
		return $dir.DIRECTORY_SEPARATOR.'job_'.$job_id.'.json';
	}

	/**
	 * @param string $job_id
	 * @param array  $job
	 * @return bool
	 */
	protected function write_export_job($job_id, array $job)
	{
		$path = $this->export_job_path($job_id);
		if ($path === FALSE)
		{
			return FALSE;
		}
		$job['updated_at'] = time();
		$json = json_encode($job);
		return $json !== FALSE && @file_put_contents($path, $json, LOCK_EX) !== FALSE;
	}

	/**
	 * @param string $job_id
	 * @param array  $patch
	 * @return bool
	 */
	public function update_export_job($job_id, array $patch)
	{
		$job = $this->get_export_job($job_id);
		if ( ! $job)
		{
			return FALSE;
		}
		foreach ($patch as $key => $value)
		{
			$job[$key] = $value;
		}
		return $this->write_export_job($job_id, $job);
	}

	/**
	 * @param string $sql_path
	 * @return bool
	 */
	protected function dump_via_dbutil($sql_path)
	{
		$this->load->dbutil();
		$backup = $this->dbutil->backup(array(
			'format' => 'txt',
			'add_drop' => TRUE,
			'add_insert' => TRUE,
			'foreign_key_checks' => FALSE,
			'newline' => "\n",
		));

		if ($backup === FALSE || $backup === '')
		{
			$this->last_error = 'CodeIgniter database utility backup failed.';
			return FALSE;
		}

		$header = "-- Darzibaba ERP backup (dbutil)\n"
			."-- Generated: ".date('c')."\n"
			."-- Database: ".$this->db->database."\n\n"
			."SET NAMES utf8mb4;\n"
			."SET FOREIGN_KEY_CHECKS=0;\n\n";

		$footer = "\nSET FOREIGN_KEY_CHECKS=1;\n";
		if (@file_put_contents($sql_path, $header.$backup.$footer) === FALSE)
		{
			$this->last_error = 'Could not write SQL dump file.';
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Drop all base tables and views so import fully replaces the schema.
	 * Routines/events are replaced by mysqldump DROP+CREATE when present.
	 *
	 * @return bool
	 */
	protected function wipe_database_objects()
	{
		$conn = $this->mysqli_connect();
		if ( ! $conn)
		{
			return FALSE;
		}

		@mysqli_set_charset($conn, 'utf8mb4');
		@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=0');
		@mysqli_query($conn, 'SET UNIQUE_CHECKS=0');

		$db_name = $this->db->database;
		$escaped_db = mysqli_real_escape_string($conn, $db_name);

		$views = array();
		$tables = array();
		$result = mysqli_query(
			$conn,
			"SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$escaped_db}'"
		);
		if ($result)
		{
			while ($row = mysqli_fetch_assoc($result))
			{
				$name = $row['TABLE_NAME'];
				if (isset($row['TABLE_TYPE']) && strtoupper($row['TABLE_TYPE']) === 'VIEW')
				{
					$views[] = $name;
				}
				else
				{
					$tables[] = $name;
				}
			}
			mysqli_free_result($result);
		}

		foreach ($views as $view)
		{
			$q = 'DROP VIEW IF EXISTS `'.str_replace('`', '``', $view).'`';
			if ( ! mysqli_query($conn, $q))
			{
				$this->last_error = 'Could not drop view '.$view.': '.mysqli_error($conn);
				@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
				@mysqli_close($conn);
				return FALSE;
			}
		}

		foreach ($tables as $table)
		{
			$q = 'DROP TABLE IF EXISTS `'.str_replace('`', '``', $table).'`';
			if ( ! mysqli_query($conn, $q))
			{
				$this->last_error = 'Could not drop table '.$table.': '.mysqli_error($conn);
				@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
				@mysqli_close($conn);
				return FALSE;
			}
		}

		@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
		@mysqli_query($conn, 'SET UNIQUE_CHECKS=1');
		@mysqli_close($conn);
		return TRUE;
	}

	/**
	 * @param string $sql_path
	 * @return bool
	 */
	protected function restore_via_mysql($sql_path)
	{
		$bin = $this->find_binary('mysql');
		if ($bin === '')
		{
			$this->last_error = 'mysql client not found.';
			return FALSE;
		}

		$cnf = $this->write_client_cnf();
		if ($cnf === FALSE)
		{
			return FALSE;
		}

		$db = escapeshellarg($this->db->database);
		$in = escapeshellarg($sql_path);
		$defaults = escapeshellarg($cnf);
		$cmd = $bin
			.' --defaults-extra-file='.$defaults
			.' --default-character-set=utf8mb4'
			.' '.$db
			.' < '.$in
			.' 2>&1';

		$output = array();
		$code = 1;
		@exec($cmd, $output, $code);
		@unlink($cnf);

		if ((int) $code !== 0)
		{
			$this->last_error = 'mysql restore failed'
				.($output ? (': '.implode(' ', $output)) : '.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * PHP fallback restore using mysqli multi_query in chunks.
	 *
	 * @param string $sql_path
	 * @return bool
	 */
	protected function restore_via_mysqli($sql_path)
	{
		$conn = $this->mysqli_connect();
		if ( ! $conn)
		{
			return FALSE;
		}

		@mysqli_set_charset($conn, 'utf8mb4');
		@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=0');
		@mysqli_query($conn, 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');

		$fh = @fopen($sql_path, 'rb');
		if ( ! $fh)
		{
			$this->last_error = 'Could not open SQL file for restore.';
			@mysqli_close($conn);
			return FALSE;
		}

		$buffer = '';
		$line_no = 0;
		$ok = TRUE;

		while ( ! feof($fh))
		{
			$line = fgets($fh);
			if ($line === FALSE)
			{
				break;
			}
			$line_no++;

			$trim = ltrim($line);
			if ($trim === '' || strpos($trim, '--') === 0 || strpos($trim, '#') === 0)
			{
				continue;
			}

			// Skip DELIMITER changes (mysqldump routines) — rare in PHP path;
			// CLI path is preferred when routines exist.
			if (stripos($trim, 'DELIMITER ') === 0)
			{
				continue;
			}

			$buffer .= $line;
			if (substr(rtrim($line), -1) !== ';')
			{
				continue;
			}

			$sql = trim($buffer);
			$buffer = '';
			if ($sql === '' || $sql === ';')
			{
				continue;
			}

			if ( ! $this->mysqli_run_statement($conn, $sql, $line_no))
			{
				$ok = FALSE;
				break;
			}
		}

		if ($ok && trim($buffer) !== '')
		{
			$ok = $this->mysqli_run_statement($conn, trim($buffer), $line_no);
		}

		@mysqli_query($conn, 'SET FOREIGN_KEY_CHECKS=1');
		@mysqli_close($conn);
		fclose($fh);

		return $ok;
	}

	/**
	 * @param mysqli $conn
	 * @param string $sql
	 * @param int $line_no
	 * @return bool
	 */
	protected function mysqli_run_statement($conn, $sql, $line_no)
	{
		$upper = strtoupper(ltrim($sql));
		if (strpos($upper, 'DROP DATABASE') === 0
			|| strpos($upper, 'CREATE DATABASE') === 0)
		{
			return TRUE; // skip dangerous / irrelevant statements
		}

		if ( ! @mysqli_query($conn, $sql))
		{
			$this->last_error = 'SQL error near line '.$line_no.': '.mysqli_error($conn);
			return FALSE;
		}

		// Drain multi results if any
		while (mysqli_more_results($conn) && mysqli_next_result($conn))
		{
			$result = mysqli_store_result($conn);
			if ($result)
			{
				mysqli_free_result($result);
			}
		}

		return TRUE;
	}

	/**
	 * @return mysqli|false
	 */
	protected function mysqli_connect()
	{
		$host = $this->db->hostname;
		$port = ! empty($this->db->port) ? (int) $this->db->port : 3306;
		$user = $this->db->username;
		$pass = $this->db->password;
		$name = $this->db->database;

		$conn = @mysqli_connect($host, $user, $pass, $name, $port);
		if ( ! $conn)
		{
			$this->last_error = 'Could not open MySQL connection for restore: '.mysqli_connect_error();
			return FALSE;
		}

		return $conn;
	}

	/**
	 * @param string $gzip_path
	 * @return bool
	 */
	protected function rollback_from_safety($gzip_path)
	{
		if ($gzip_path === '' || ! is_file($gzip_path))
		{
			return FALSE;
		}

		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return FALSE;
		}

		$sql_path = $dir.DIRECTORY_SEPARATOR.'rollback_'.uniqid('', TRUE).'.sql';
		if ( ! $this->gunzip_file($gzip_path, $sql_path))
		{
			return FALSE;
		}

		$ok = $this->restore_via_mysql($sql_path);
		if ( ! $ok)
		{
			$ok = $this->restore_via_mysqli($sql_path);
		}
		@unlink($sql_path);

		return $ok;
	}

	/**
	 * Keep only the newest SAFETY_KEEP safety_*.sql.gz files.
	 */
	protected function prune_old_safety_files()
	{
		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return;
		}

		$files = glob($dir.DIRECTORY_SEPARATOR.'safety_*.sql.gz');
		if ( ! is_array($files) || count($files) <= self::SAFETY_KEEP)
		{
			return;
		}

		usort($files, function ($a, $b) {
			return filemtime($b) - filemtime($a);
		});

		foreach (array_slice($files, self::SAFETY_KEEP) as $old)
		{
			@unlink($old);
		}
	}

	protected function clear_app_caches()
	{
		$cache_dir = APPPATH.'cache';
		if ( ! is_dir($cache_dir))
		{
			return;
		}

		$iterator = new DirectoryIterator($cache_dir);
		foreach ($iterator as $file)
		{
			if ($file->isFile() && strpos($file->getFilename(), '.') !== 0)
			{
				@unlink($file->getPathname());
			}
		}
	}

	/**
	 * Temporary mysql client defaults file (avoids password on argv).
	 *
	 * @return string|false
	 */
	protected function write_client_cnf()
	{
		$dir = $this->backup_directory();
		if ($dir === FALSE)
		{
			return FALSE;
		}

		$path = $dir.DIRECTORY_SEPARATOR.'client_'.uniqid('', TRUE).'.cnf';
		$host = (string) $this->db->hostname;
		$port = ! empty($this->db->port) ? (int) $this->db->port : 3306;
		$user = (string) $this->db->username;
		$pass = (string) $this->db->password;

		// Escape for my.cnf double-quoted values
		$pass_esc = str_replace(array('\\', '"'), array('\\\\', '\\"'), $pass);

		$content = "[client]\n"
			."host=\"{$host}\"\n"
			."port={$port}\n"
			."user=\"{$user}\"\n"
			."password=\"{$pass_esc}\"\n";

		if (@file_put_contents($path, $content) === FALSE)
		{
			$this->last_error = 'Could not write temporary MySQL client config.';
			return FALSE;
		}
		@chmod($path, 0600);

		return $path;
	}

	/**
	 * @param string $name
	 * @return string Absolute path or empty
	 */
	protected function find_binary($name)
	{
		static $cache = array();
		if (isset($cache[$name]))
		{
			return $cache[$name];
		}

		$candidates = array(
			'/usr/local/bin/'.$name,
			'/opt/homebrew/bin/'.$name,
			'/usr/bin/'.$name,
			$name,
		);

		foreach ($candidates as $bin)
		{
			if ($bin === $name)
			{
				$which = array();
				@exec('command -v '.escapeshellarg($name).' 2>/dev/null', $which, $code);
				if ((int) $code === 0 && ! empty($which[0]) && is_executable($which[0]))
				{
					return $cache[$name] = $which[0];
				}
				continue;
			}
			if (is_executable($bin))
			{
				return $cache[$name] = $bin;
			}
		}

		return $cache[$name] = '';
	}

	/**
	 * @param string        $src
	 * @param string        $dest
	 * @param callable|null $on_progress
	 * @return bool
	 */
	protected function gzip_file($src, $dest, $on_progress = NULL)
	{
		$in = @fopen($src, 'rb');
		$out = @gzopen($dest, 'wb9');
		if ( ! $in || ! $out)
		{
			$this->last_error = 'Could not open streams for gzip compression.';
			if ($in)
			{
				fclose($in);
			}
			if ($out)
			{
				gzclose($out);
			}
			return FALSE;
		}

		$total = @filesize($src);
		$total = $total > 0 ? (int) $total : 0;
		$done = 0;
		$last_pct = 88;

		while ( ! feof($in))
		{
			$chunk = fread($in, 1048576);
			if ($chunk === FALSE)
			{
				break;
			}
			gzwrite($out, $chunk);
			$done += strlen($chunk);
			if ($on_progress && $total > 0)
			{
				$pct = 88 + (int) floor(($done / $total) * 10);
				if ($pct > 98)
				{
					$pct = 98;
				}
				if ($pct !== $last_pct)
				{
					$last_pct = $pct;
					$this->report_progress(
						$on_progress,
						$pct,
						'compressing',
						'Compressing backup… '.$this->format_bytes($done).' / '.$this->format_bytes($total)
					);
				}
			}
		}
		fclose($in);
		gzclose($out);

		return is_file($dest) && filesize($dest) > 0;
	}

	/**
	 * @param string $src
	 * @param string $dest
	 * @return bool
	 */
	protected function gunzip_file($src, $dest)
	{
		$in = @gzopen($src, 'rb');
		$out = @fopen($dest, 'wb');
		if ( ! $in || ! $out)
		{
			$this->last_error = 'Could not open streams for gzip decompression.';
			if ($in)
			{
				gzclose($in);
			}
			if ($out)
			{
				fclose($out);
			}
			return FALSE;
		}

		while ( ! gzeof($in))
		{
			$chunk = gzread($in, 1048576);
			if ($chunk === FALSE)
			{
				break;
			}
			fwrite($out, $chunk);
		}
		gzclose($in);
		fclose($out);

		return is_file($dest) && filesize($dest) > 0;
	}

	/**
	 * @param array $paths
	 */
	protected function cleanup_paths(array $paths)
	{
		foreach ($paths as $path)
		{
			if (is_string($path) && $path !== '' && is_file($path))
			{
				@unlink($path);
			}
		}
	}

	/**
	 * @param string $val
	 * @return int
	 */
	protected function ini_bytes($val)
	{
		$val = trim((string) $val);
		if ($val === '' || $val === '0')
		{
			return 0;
		}

		$unit = strtolower(substr($val, -1));
		$num = (float) $val;
		switch ($unit)
		{
			case 'g':
				$num *= 1024;
				// no break
			case 'm':
				$num *= 1024;
				// no break
			case 'k':
				$num *= 1024;
		}

		return (int) $num;
	}

	/**
	 * @param int $bytes
	 * @return string
	 */
	public function format_bytes($bytes)
	{
		$bytes = (int) $bytes;
		if ($bytes < 1024)
		{
			return $bytes.' B';
		}
		if ($bytes < 1048576)
		{
			return round($bytes / 1024, 1).' KB';
		}
		return round($bytes / 1048576, 1).' MB';
	}
}
