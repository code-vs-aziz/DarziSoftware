<?php
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');
$submission = isset($submission) ? $submission : array();
$customer = isset($customer) ? $customer : array();
$payload = isset($payload) ? $payload : array();
$values = isset($payload['values']) && is_array($payload['values']) ? $payload['values'] : array();
?>
<body class="animsition erp-app erp-form-page">
	<div class="page-wrapper">
		<div class="main-content">
			<div class="section__content section__content--p30">
				<div class="container-fluid">
					<div class="col-md-12">
						<div class="dz-card erp-module-card data-entry-card">
							<div class="erp-card-head d-flex justify-content-between align-items-center">
								<h2 class="erp-module-title mb-0">Review submission #<?php echo (int) $submission['id']; ?></h2>
								<a href="<?php echo esc_attr(site_url('Website/submissions')); ?>">Back</a>
							</div>
							<div class="card-body erp-card-body">
								<p>
									<strong>Customer:</strong> <?php echo esc_html(isset($customer['name']) ? $customer['name'] : ''); ?>
									· <strong>Naap:</strong> <?php echo esc_html(isset($customer['man_napno']) ? $customer['man_napno'] : ''); ?>
									· <strong>Type:</strong> <?php echo esc_html($submission['submission_type']); ?>
									· <strong>Status:</strong> <?php echo esc_html($submission['status']); ?>
								</p>
								<div class="table-responsive mb-3">
									<table class="table table-sm table-bordered">
										<thead><tr><th>Field</th><th>Value</th></tr></thead>
										<tbody>
											<?php foreach ($values as $k => $v) {
												if ($v === '' || $v === null) continue;
											?>
												<tr><td><?php echo esc_html($k); ?></td><td><?php echo esc_html((string) $v); ?></td></tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
								<?php if ($submission['status'] === 'pending') { ?>
									<form method="post" action="<?php echo esc_attr(site_url('Website/submission_approve/'.$submission['id'])); ?>" class="mb-2">
										<?php $this->load->view('partials/csrf_field'); ?>
										<textarea class="form-control mb-2" name="review_note" rows="2" placeholder="Optional note"></textarea>
										<button class="btn btn-success" type="submit">Approve &amp; save to ERP measurement</button>
									</form>
									<form method="post" action="<?php echo esc_attr(site_url('Website/submission_reject/'.$submission['id'])); ?>">
										<?php $this->load->view('partials/csrf_field'); ?>
										<textarea class="form-control mb-2" name="review_note" rows="2" placeholder="Rejection reason"></textarea>
										<button class="btn btn-danger" type="submit">Reject</button>
									</form>
								<?php } else { ?>
									<p class="text-muted">Reviewed by <?php echo esc_html((string) $submission['reviewed_by']); ?> at <?php echo esc_html((string) $submission['reviewed_at']); ?></p>
									<?php if ( ! empty($submission['review_note'])) { ?>
										<p><?php echo esc_html($submission['review_note']); ?></p>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
