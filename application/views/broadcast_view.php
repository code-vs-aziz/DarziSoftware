<?php
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');

$campaign = isset($campaign) ? $campaign : array();
$recipients = isset($recipients) ? $recipients : array();
$bridge = isset($bridge_status) ? $bridge_status : array();
$id = (int) $campaign['id'];
$status = (string) $campaign['status'];
?>
<body class="erp-app">
<div class="page-wrapper">
<div class="main-content">
<div class="container-fluid py-3">
	<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
		<div>
			<h2 class="mb-1">#<?php echo $id; ?> <?php echo esc_html($campaign['title']); ?></h2>
			<p class="text-muted mb-0"><?php echo esc_html($status); ?> ·
				<?php echo (int) $campaign['sent_count']; ?> sent /
				<?php echo (int) $campaign['fail_count']; ?> failed /
				<?php echo (int) $campaign['skip_count']; ?> skipped /
				<?php echo (int) $campaign['total_recipients']; ?> total
			</p>
		</div>
		<a class="btn btn-outline-secondary" href="<?php echo site_url('Broadcast'); ?>"><?php echo t('app_back'); ?></a>
	</div>

	<?php if ($this->session->flashdata('broadcast_ok')) { ?>
		<div class="alert alert-success"><?php echo esc_html((string) $this->session->flashdata('broadcast_ok')); ?></div>
	<?php } ?>

	<div class="mb-3">
		<span class="badge badge-<?php echo !empty($bridge['ready']) ? 'success' : 'warning'; ?>">
			<?php echo !empty($bridge['ready']) ? t('notify_bridge_connected') : t('notify_bridge_waiting'); ?>
		</span>
		<span class="text-muted ml-2">
			Delay <?php echo (int) $campaign['delay_min_sec']; ?>–<?php echo (int) $campaign['delay_max_sec']; ?>s ·
			Cap <?php echo (int) $campaign['daily_cap']; ?>/day ·
			Window <?php echo (int) $campaign['send_window_start']; ?>:00–<?php echo (int) $campaign['send_window_end']; ?>:00
		</span>
	</div>

	<div class="card mb-3">
		<div class="card-body">
			<pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;"><?php echo esc_html($campaign['message']); ?></pre>
		</div>
	</div>

	<div class="mb-3">
		<?php if (in_array($status, array('queued', 'running'), TRUE)) { ?>
			<a class="btn btn-warning" href="<?php echo site_url('Broadcast/pause/'.$id); ?>"><?php echo t('broadcast_pause'); ?></a>
		<?php } ?>
		<?php if (in_array($status, array('paused', 'queued'), TRUE)) { ?>
			<a class="btn btn-success" href="<?php echo site_url('Broadcast/resume/'.$id); ?>"><?php echo t('broadcast_resume'); ?></a>
		<?php } ?>
		<?php if ( ! in_array($status, array('completed', 'cancelled'), TRUE)) { ?>
			<a class="btn btn-outline-danger" href="<?php echo site_url('Broadcast/cancel/'.$id); ?>" onclick="return confirm('Cancel remaining sends?');"><?php echo t('broadcast_cancel'); ?></a>
			<a class="btn btn-outline-primary" href="<?php echo site_url('Broadcast/process_now/'.$id); ?>"><?php echo t('broadcast_send_one'); ?></a>
		<?php } ?>
	</div>

	<div class="table-responsive">
		<table class="table table-sm table-striped">
			<thead>
				<tr>
					<th>Customer</th>
					<th>Phone</th>
					<th>Status</th>
					<th>Error</th>
					<th>Sent at</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($recipients as $r) { ?>
				<tr>
					<td><?php echo esc_html($r['customer_name']); ?> <small class="text-muted">#<?php echo (int) $r['customer_code']; ?></small></td>
					<td><bdi><?php echo esc_html($r['phone_normalized']); ?></bdi></td>
					<td><?php echo esc_html($r['status']); ?></td>
					<td><?php echo esc_html($r['error_message']); ?></td>
					<td><?php echo esc_html((string) $r['sent_at']); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
</div>
</div>
</body>
</html>
