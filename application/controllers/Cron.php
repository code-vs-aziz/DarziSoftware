<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI / scheduled jobs (WhatsApp queue, etc.).
 *
 * Examples:
 *   php index.php cron process_notifications
 *   php index.php cron process_notifications 40
 *   php index.php cron process_broadcasts
 *
 * Cron (every minute):
 *   * * * * * cd /path/to/Darzibaba && /usr/bin/php index.php cron process_notifications >> /tmp/darzi-cron.log 2>&1
 *   * * * * * cd /path/to/Darzibaba && /usr/bin/php index.php cron process_broadcasts >> /tmp/darzi-broadcast.log 2>&1
 */
class Cron extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if ( ! is_cli())
		{
			// Allow optional HTTP with shared secret for hosting panels without CLI.
			$secret = function_exists('dz_env') ? trim((string) dz_env('CRON_HTTP_SECRET', '')) : '';
			$given = (string) $this->input->get_post('secret', TRUE);
			if ($secret === '' || $given === '' || !hash_equals($secret, $given))
			{
				show_error('CLI only (or valid CRON_HTTP_SECRET).', 403);
				return;
			}
		}
	}

	/**
	 * Drain WhatsApp / notification pending queue.
	 *
	 * @param int $limit
	 */
	public function process_notifications($limit = 40)
	{
		$limit = (int) $limit;
		if ($limit < 1)
		{
			$limit = 40;
		}
		if ($limit > 100)
		{
			$limit = 100;
		}

		$this->load->library('notification_service');
		$result = $this->notification_service->process_pending($limit);
		$processed = isset($result['processed']) ? (int) $result['processed'] : 0;
		$line = date('c').' notifications processed='.$processed.' limit='.$limit.PHP_EOL;
		if (is_cli())
		{
			echo $line;
		}
		else
		{
			$this->output->set_content_type('text/plain')->set_output($line);
		}
	}

	/**
	 * Drain WhatsApp broadcast queue slowly (safe for unofficial bridge).
	 *
	 * Cron every minute:
	 *   * * * * * cd /path/to/Darzibaba && php index.php cron process_broadcasts
	 */
	public function process_broadcasts($limit = 2)
	{
		$limit = (int) $limit;
		if ($limit < 1)
		{
			$limit = 2;
		}
		if ($limit > 5)
		{
			$limit = 5;
		}
		$this->load->library('broadcast_service');
		$result = $this->broadcast_service->process($limit);
		$processed = isset($result['processed']) ? (int) $result['processed'] : 0;
		$reason = isset($result['reason']) ? (string) $result['reason'] : '';
		$line = date('c').' broadcast processed='.$processed.($reason !== '' ? (' reason='.$reason) : '').PHP_EOL;
		if (is_cli())
		{
			echo $line;
		}
		else
		{
			$this->output->set_content_type('text/plain')->set_output($line);
		}
	}

	/**
	 * Health ping for schedulers.
	 */
	public function ping()
	{
		$msg = date('c').' cron ok'.PHP_EOL;
		if (is_cli())
		{
			echo $msg;
		}
		else
		{
			$this->output->set_content_type('text/plain')->set_output($msg);
		}
	}
}
