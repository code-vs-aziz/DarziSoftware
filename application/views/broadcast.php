<?php
ob_start();
$this->load->helper('ui');
$this->load->view('Head');
$this->load->view('Header');

$table_ready = ! empty($table_ready);
$campaigns = isset($campaigns) ? $campaigns : array();
$safety = isset($safety) ? $safety : array();
$phone_customer_count = isset($phone_customer_count) ? (int) $phone_customer_count : 0;
$bridge = isset($bridge_status) ? $bridge_status : array();
$bridge_ready = ! empty($bridge['ready']);
$max_recipients = isset($safety['max_recipients']) ? (int) $safety['max_recipients'] : 500;
?>
<style>
.bc-page { max-width: 1200px; }
.bc-head {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 1rem;
	flex-wrap: wrap;
	margin-bottom: 1.25rem;
}
.bc-head h2 {
	margin: 0 0 0.25rem;
	font-size: 1.5rem;
	font-weight: 700;
	color: #0f172a;
}
.bc-head p {
	margin: 0;
	color: #64748b;
	font-size: 0.95rem;
}
.bc-status {
	display: flex;
	align-items: center;
	gap: 0.75rem;
	flex-wrap: wrap;
	margin-bottom: 1rem;
}
.bc-grid {
	display: grid;
	grid-template-columns: 1.35fr 0.65fr;
	gap: 1rem;
	align-items: start;
}
@media (max-width: 992px) {
	.bc-grid { grid-template-columns: 1fr; }
}
.bc-panel {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 12px;
	padding: 1.15rem 1.25rem;
	box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.bc-panel h5 {
	margin: 0 0 1rem;
	font-size: 1.05rem;
	font-weight: 700;
	color: #0f172a;
}
.bc-section-label {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.75rem;
	flex-wrap: wrap;
	margin-bottom: 0.65rem;
}
.bc-selected-count {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	font-weight: 700;
	color: #0369a1;
	background: #e0f2fe;
	border-radius: 999px;
	padding: 0.2rem 0.7rem;
	font-size: 0.9rem;
}
.bc-toolbar {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
	align-items: center;
	margin-bottom: 0.65rem;
}
.bc-toolbar .btn {
	white-space: nowrap;
}
.bc-list {
	max-height: 380px;
	overflow: auto;
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	background: #f8fafc;
}
.bc-list table {
	margin: 0;
	background: #fff;
}
.bc-list thead th {
	position: sticky;
	top: 0;
	background: #f1f5f9;
	z-index: 1;
	border-bottom: 1px solid #e2e8f0;
	font-size: 0.8rem;
	text-transform: uppercase;
	letter-spacing: 0.02em;
	color: #64748b;
}
.bc-list tbody tr {
	cursor: pointer;
	transition: background 0.12s ease;
}
.bc-list tbody tr.is-selected {
	background: #e0f2fe;
}
.bc-list tbody tr.is-selected td {
	color: #0c4a6e;
	font-weight: 600;
}
.bc-list tbody tr:hover {
	background: #f1f5f9;
}
.bc-list tbody tr.is-selected:hover {
	background: #bae6fd;
}
.bc-limits .form-group {
	margin-bottom: 0.75rem;
}
.bc-limits small {
	display: block;
	color: #64748b;
	margin-top: 0.2rem;
}
.bc-actions {
	display: flex;
	flex-wrap: wrap;
	gap: 0.65rem;
	align-items: center;
	margin-top: 0.5rem;
	padding-top: 0.85rem;
	border-top: 1px solid #e2e8f0;
}
.bc-all-banner {
	display: none;
	margin-bottom: 0.65rem;
	padding: 0.55rem 0.75rem;
	border-radius: 8px;
	background: #ecfeff;
	border: 1px solid #a5f3fc;
	color: #155e75;
	font-size: 0.9rem;
}
.bc-all-banner.is-visible {
	display: block;
}
</style>
<body class="erp-app">
<div class="page-wrapper">
<div class="main-content">
<div class="container-fluid py-3 bc-page">

	<div class="bc-head">
		<div>
			<h2><?php echo t('broadcast_title'); ?></h2>
			<p><?php echo t('broadcast_subtitle'); ?></p>
		</div>
		<a class="btn btn-outline-secondary dz-btn dz-btn--outlined" href="<?php echo site_url('Notifications'); ?>"><?php echo t('menu_customer_messages'); ?></a>
	</div>

	<?php if ( ! $table_ready) { ?>
		<div class="alert alert-warning">
			<strong><?php echo t('broadcast_table_missing'); ?></strong>
			<p class="mb-0 mt-2"><?php echo t('broadcast_table_missing_help'); ?> <code>application/sql/wa_broadcast_schema.sql</code></p>
		</div>
	<?php } else { ?>

	<?php if ($this->session->flashdata('broadcast_error')) {
		$err = (string) $this->session->flashdata('broadcast_error');
	?>
		<div class="alert alert-danger"><?php echo esc_html(t('broadcast_error_'.$err) !== 'broadcast_error_'.$err ? t('broadcast_error_'.$err) : $err); ?></div>
	<?php } ?>
	<?php if ($this->session->flashdata('broadcast_ok')) { ?>
		<div class="alert alert-success"><?php echo esc_html((string) $this->session->flashdata('broadcast_ok')); ?></div>
	<?php } ?>

	<div class="bc-status">
		<span class="badge badge-<?php echo $bridge_ready ? 'success' : 'warning'; ?>">
			<?php echo $bridge_ready ? t('notify_bridge_connected') : t('notify_bridge_waiting'); ?>
		</span>
		<span class="text-muted"><?php echo sprintf(t('broadcast_customers_with_phone'), $phone_customer_count); ?></span>
	</div>

	<div class="bc-grid mb-4">
		<div class="bc-panel">
			<h5><?php echo t('broadcast_compose'); ?></h5>
			<form method="post" action="<?php echo site_url('Broadcast/create'); ?>" id="bcForm" data-no-ajax="true">
				<?php $this->load->view('partials/csrf_field'); ?>
				<input type="hidden" name="risk_ack" value="1">
				<input type="hidden" name="select_all_phones" id="bcSelectAllInput" value="0">

				<div class="form-group">
					<label for="bcTitle"><?php echo t('broadcast_title_label'); ?></label>
					<input id="bcTitle" class="form-control" name="title" maxlength="160" placeholder="Eid offer / Eid Mubarak">
				</div>
				<div class="form-group">
					<label for="bcMessage"><?php echo t('broadcast_message'); ?></label>
					<textarea id="bcMessage" class="form-control" name="message" rows="5" required maxlength="3500" placeholder="<?php echo esc_attr(t('broadcast_message_placeholder')); ?>"></textarea>
					<small class="text-muted"><?php echo t('broadcast_message_help'); ?></small>
				</div>

				<div class="form-row bc-limits">
					<div class="form-group col-6 col-md-4">
						<label for="bcDailyCap"><?php echo t('broadcast_daily_cap'); ?></label>
						<input id="bcDailyCap" class="form-control" type="number" name="daily_cap" min="1" max="<?php echo (int) $safety['hard_daily']; ?>" value="<?php echo (int) $safety['daily_cap']; ?>">
						<small><?php echo t('broadcast_hard_cap'); ?>: <?php echo (int) $safety['hard_daily']; ?>/day</small>
					</div>
					<div class="form-group col-6 col-md-4">
						<label for="bcHourCap"><?php echo t('broadcast_hour_cap'); ?></label>
						<input id="bcHourCap" class="form-control" type="number" name="hour_cap" min="1" max="30" value="<?php echo (int) $safety['hour_cap']; ?>">
					</div>
					<div class="form-group col-6 col-md-4">
						<label for="bcDelayMin"><?php echo t('broadcast_delay_min'); ?></label>
						<input id="bcDelayMin" class="form-control" type="number" name="delay_min_sec" min="40" max="300" value="<?php echo (int) $safety['delay_min']; ?>">
					</div>
					<div class="form-group col-6 col-md-4">
						<label for="bcDelayMax"><?php echo t('broadcast_delay_max'); ?></label>
						<input id="bcDelayMax" class="form-control" type="number" name="delay_max_sec" min="40" max="600" value="<?php echo (int) $safety['delay_max']; ?>">
					</div>
					<div class="form-group col-6 col-md-4">
						<label for="bcWindowStart"><?php echo t('broadcast_window_start'); ?></label>
						<input id="bcWindowStart" class="form-control" type="number" name="send_window_start" min="0" max="23" value="10">
					</div>
					<div class="form-group col-6 col-md-4">
						<label for="bcWindowEnd"><?php echo t('broadcast_window_end'); ?></label>
						<input id="bcWindowEnd" class="form-control" type="number" name="send_window_end" min="1" max="24" value="20">
					</div>
				</div>

				<div class="form-group mb-0">
					<div class="bc-section-label">
						<label class="mb-0"><?php echo t('broadcast_recipients'); ?></label>
						<span class="bc-selected-count" id="bcSelectedCountWrap">
							<span id="bcSelectedCount">0</span>
							<span><?php echo t('broadcast_selected_label'); ?></span>
						</span>
					</div>

					<div class="bc-toolbar">
						<div class="input-group" style="flex:1; min-width:220px;">
							<input type="text" class="form-control" id="bcSearch" placeholder="<?php echo esc_attr(t('broadcast_search_placeholder')); ?>">
							<div class="input-group-append">
								<button type="button" class="btn btn-outline-secondary" id="bcSearchBtn"><?php echo t('app_search'); ?></button>
							</div>
						</div>
						<button type="button" class="btn btn-outline-primary dz-btn dz-btn--outlined dz-btn--sm" id="bcSelectAllBtn"><?php echo t('broadcast_select_all'); ?></button>
						<button type="button" class="btn btn-outline-secondary dz-btn dz-btn--outlined dz-btn--sm" id="bcSelectVisibleBtn"><?php echo t('broadcast_select_visible'); ?></button>
						<button type="button" class="btn btn-outline-danger dz-btn dz-btn--outlined dz-btn--sm" id="bcClearBtn"><?php echo t('broadcast_clear_selection'); ?></button>
					</div>

					<div id="bcAllBanner" class="bc-all-banner">
						<?php echo sprintf(t('broadcast_all_selected_banner'), min($phone_customer_count, $max_recipients)); ?>
					</div>

					<div class="bc-list">
						<table class="table table-sm mb-0">
							<thead>
								<tr>
									<th style="width:42px;"></th>
									<th><?php echo t('broadcast_col_code'); ?></th>
									<th><?php echo t('broadcast_col_name'); ?></th>
									<th><?php echo t('broadcast_col_phone'); ?></th>
								</tr>
							</thead>
							<tbody id="bcCustomerBody">
								<tr><td colspan="4" class="text-muted p-3"><?php echo t('broadcast_search_hint'); ?></td></tr>
							</tbody>
						</table>
					</div>
					<div id="bcHiddenCodes"></div>
				</div>

				<div class="bc-actions">
					<button type="submit" class="btn btn-primary dz-btn dz-btn--filled" id="bcSubmitBtn" onclick="return confirm('<?php echo esc_attr(t('broadcast_confirm_queue')); ?>');">
						<?php echo t('broadcast_queue_start'); ?>
					</button>
				</div>
			</form>
		</div>

		<div class="bc-panel">
			<h5><?php echo t('broadcast_recent'); ?></h5>
			<?php if ( ! $campaigns) { ?>
				<p class="text-muted mb-0"><?php echo t('broadcast_none'); ?></p>
			<?php } else { ?>
				<div class="table-responsive">
					<table class="table table-sm mb-0">
						<thead>
							<tr>
								<th><?php echo t('broadcast_title_label'); ?></th>
								<th><?php echo t('broadcast_col_status'); ?></th>
								<th><?php echo t('broadcast_col_sent'); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($campaigns as $c) { ?>
							<tr>
								<td><?php echo esc_html($c['title']); ?></td>
								<td><?php echo esc_html($c['status']); ?></td>
								<td><?php echo (int) $c['sent_count']; ?>/<?php echo (int) $c['total_recipients']; ?></td>
								<td><a href="<?php echo site_url('Broadcast/view/'.$c['id']); ?>"><?php echo t('app_view'); ?></a></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>

</div>
</div>
</div>
<script>
(function () {
	'use strict';

	var searchUrl = <?php echo json_encode(site_url('Broadcast/search_customers')); ?>;
	var phoneTotal = <?php echo (int) $phone_customer_count; ?>;
	var maxRecipients = <?php echo (int) $max_recipients; ?>;
	var body = document.getElementById('bcCustomerBody');
	var hidden = document.getElementById('bcHiddenCodes');
	var countEl = document.getElementById('bcSelectedCount');
	var selectAllInput = document.getElementById('bcSelectAllInput');
	var allBanner = document.getElementById('bcAllBanner');
	var selected = {};
	var selectAllMode = false;

	if (!body || !hidden || !countEl) {
		return;
	}

	function selectedCount() {
		if (selectAllMode) {
			return Math.min(phoneTotal, maxRecipients);
		}
		return Object.keys(selected).length;
	}

	function renderSelected() {
		hidden.innerHTML = '';
		if (!selectAllMode) {
			Object.keys(selected).forEach(function (code) {
				var input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'customer_codes[]';
				input.value = code;
				hidden.appendChild(input);
			});
		}
		if (selectAllInput) {
			selectAllInput.value = selectAllMode ? '1' : '0';
		}
		countEl.textContent = String(selectedCount());
		if (allBanner) {
			allBanner.classList.toggle('is-visible', selectAllMode);
		}
		syncRowStyles();
	}

	function syncRowStyles() {
		body.querySelectorAll('tr[data-code]').forEach(function (tr) {
			var code = String(tr.getAttribute('data-code'));
			var on = selectAllMode || !!selected[code];
			tr.classList.toggle('is-selected', on);
			var cb = tr.querySelector('.bc-row');
			if (cb) {
				cb.checked = on;
			}
		});
	}

	function setSelectAll(on) {
		selectAllMode = !!on;
		if (selectAllMode) {
			selected = {};
		}
		renderSelected();
	}

	function clearSelection() {
		selected = {};
		selectAllMode = false;
		renderSelected();
	}

	function setRowSelected(code, on) {
		code = String(code);
		if (selectAllMode) {
			selectAllMode = false;
			// Leaving "all" mode: start from empty, then apply this toggle as individual.
			selected = {};
		}
		if (on) {
			selected[code] = true;
		} else {
			delete selected[code];
		}
		renderSelected();
	}

	function loadCustomers() {
		var q = (document.getElementById('bcSearch').value || '').trim();
		body.innerHTML = '<tr><td colspan="4" class="p-3 text-muted">Loading…</td></tr>';
		fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&limit=120', { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (data) {
				var rows = (data && data.customers) || [];
				if (!rows.length) {
					body.innerHTML = '<tr><td colspan="4" class="p-3 text-muted">'+<?php echo json_encode(t('broadcast_no_customers')); ?>+'</td></tr>';
					return;
				}
				body.innerHTML = '';
				rows.forEach(function (c) {
					var code = String(c.code);
					var tr = document.createElement('tr');
					tr.setAttribute('data-code', code);
					var on = selectAllMode || !!selected[code];
					if (on) {
						tr.classList.add('is-selected');
					}
					tr.innerHTML = '<td><input type="checkbox" class="bc-row" data-code="'+code+'"'+(on ? ' checked' : '')+'></td>'
						+ '<td>'+code+'</td><td></td><td></td>';
					tr.children[2].textContent = c.name || '';
					tr.children[3].textContent = c.phone || '';
					body.appendChild(tr);
				});
			})
			.catch(function () {
				body.innerHTML = '<tr><td colspan="4" class="p-3 text-danger">'+<?php echo json_encode(t('broadcast_search_failed')); ?>+'</td></tr>';
			});
	}

	document.getElementById('bcSearchBtn').addEventListener('click', loadCustomers);
	document.getElementById('bcSearch').addEventListener('keydown', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			loadCustomers();
		}
	});

	body.addEventListener('change', function (e) {
		var t = e.target;
		if (!t.classList.contains('bc-row')) {
			return;
		}
		setRowSelected(t.getAttribute('data-code'), t.checked);
	});

	body.addEventListener('click', function (e) {
		var tr = e.target.closest('tr[data-code]');
		if (!tr || e.target.classList.contains('bc-row')) {
			return;
		}
		var cb = tr.querySelector('.bc-row');
		if (!cb) {
			return;
		}
		cb.checked = !cb.checked;
		setRowSelected(cb.getAttribute('data-code'), cb.checked);
	});

	document.getElementById('bcSelectAllBtn').addEventListener('click', function () {
		setSelectAll(true);
	});

	document.getElementById('bcSelectVisibleBtn').addEventListener('click', function () {
		selectAllMode = false;
		body.querySelectorAll('.bc-row').forEach(function (cb) {
			cb.checked = true;
			selected[String(cb.getAttribute('data-code'))] = true;
		});
		renderSelected();
	});

	document.getElementById('bcClearBtn').addEventListener('click', clearSelection);

	document.getElementById('bcForm').addEventListener('submit', function (e) {
		if (selectedCount() < 1) {
			e.preventDefault();
			alert(<?php echo json_encode(t('broadcast_need_recipients')); ?>);
		}
	});

	loadCustomers();
	renderSelected();
})();
</script>
</body>
</html>
