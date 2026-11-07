<?php
	$business_config = isset($business_config) && is_array($business_config) ? $business_config : array();
	$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';
	$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';

	$print_mode = isset($print_mode) ? strtolower(trim((string) $print_mode)) : '';
	if ($print_mode !== 'letterhead' && $print_mode !== 'blank')
	{
		$q = isset($_GET['mode']) ? strtolower(trim((string) $_GET['mode'])) : '';
		$print_mode = ($q === 'letterhead') ? 'letterhead' : 'blank';
	}
	$is_letterhead = ($print_mode === 'letterhead');

	if (!isset($order) || empty($order))
	{
		$order = array((object) array(
			'TRNNO' => '', 'man_napno' => '', 'name' => '', 'RDATE' => '', 'ORDERDATE' => '',
			'total' => '', 'advance' => '', 'discount' => '', 'qty' => '',
		));
	}

	$erp_light_page = TRUE;
	ob_start();
	$this->load->view('Head', array('erp_light_page' => TRUE));
?>
<style>
	.urd {
		font-weight: bold;
		font-family: 'Noto Nastaliq Urdu Draft', 'Noto Naskh Urdu', lateef, serif;
	}
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', lateef, sans-serif;
	}
	@media print {
		.page-break { display: block; page-break-before: always; }
		@page {
			size: A4 portrait;
			margin: 0;
		}
	}
</style>
<body class="animsition erp-app blank-print-page print-size-a4<?php echo $is_letterhead ? ' blank-print-page--letterhead' : ''; ?>">
<?php
	foreach ($order as $orders) {
		break;
	}
	$order_number = isset($orders->TRNNO) ? trim((string) $orders->TRNNO) : '';
	$has_order_number = ($order_number !== '' && $order_number !== '0');
	$rdate_fmt = (isset($orders->RDATE) && $orders->RDATE !== null && $orders->RDATE !== '') ? date('d-m-Y', strtotime($orders->RDATE)) : '';
	$odate_fmt = (isset($orders->ORDERDATE) && $orders->ORDERDATE !== null && $orders->ORDERDATE !== '') ? date('d-m-Y', strtotime($orders->ORDERDATE)) : '';
	$blank_total = (!isset($orders->total) || $orders->total === '' || $orders->total === null) ? null : (float) $orders->total;
	$blank_advance = (!isset($orders->advance) || $orders->advance === '' || $orders->advance === null) ? null : (float) $orders->advance;
	$blank_discount = (!isset($orders->discount) || $orders->discount === '' || $orders->discount === null) ? null : (float) $orders->discount;
	$blank_balance = '';
	if ($blank_total !== null || $blank_advance !== null || $blank_discount !== null) {
		$blank_balance = (float) $blank_total - (float) $blank_advance - (float) $blank_discount;
	}
	$blank_url = site_url('Order/print_blank');
	$letterhead_url = site_url('Order/print_blank').'?mode=letterhead';
?>
<div class="page-wrapper top">
	<div class="order-print-toolbar print">
		<div class="order-print-toolbar__inner">
			<div class="order-print-toolbar__left">
				<a href="<?php echo site_url('Welcome'); ?>" class="btn btn-lg btn-secondary" data-no-ajax="true"><?php echo t('print_back'); ?></a>
				<div class="blank-print-mode" role="group" aria-label="<?php echo esc_attr(t('print_blank_mode')); ?>">
					<a href="<?php echo esc_html($blank_url); ?>" class="btn btn-lg <?php echo $is_letterhead ? 'btn-outline-secondary' : 'btn-info'; ?>" data-no-ajax="true"><?php echo t('print_blank_slip'); ?></a>
					<a href="<?php echo esc_html($letterhead_url); ?>" class="btn btn-lg <?php echo $is_letterhead ? 'btn-info' : 'btn-outline-secondary'; ?>" data-no-ajax="true"><?php echo t('print_letterhead'); ?></a>
				</div>
			</div>
			<div class="order-print-toolbar__right">
<?php $this->load->view('partials/print_size_selector', array('default_size' => 'a4')); ?>
				<button type="button" class="btn btn-lg btn-primary" onclick="window.print()"><?php echo t('app_print'); ?></button>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="print-slip-sheet" data-print-sheet>
			<img src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=2" alt="" class="print-slip-watermark" aria-hidden="true">

			<header class="print-slip-brand">
				<img src="<?php echo base_url('images/darzi-baba-logo.png'); ?>?v=2" alt="Darzi Baba" class="print-slip-brand__logo" width="280" height="176">
				<div class="print-slip-brand__meta">
					<span class="print-slip-brand__phone">Ph: <?php echo esc_html($print_phone); ?></span>
					<span class="print-slip-brand__badge print-slip-brand__badge--single ur">پینٹ کوٹ، ویسکوٹ، شلوار قمیض، سپیشلسٹ</span>
					<span class="print-slip-brand__address ur urd"><?php echo esc_html($print_address); ?></span>
				</div>
				<?php if ($has_order_number && ! $is_letterhead) { ?>
				<div class="print-slip-brand__trn"><?php echo (int) $order_number; ?></div>
				<?php } ?>
			</header>

			<main class="print-slip-content">
<?php if ($is_letterhead) { ?>
				<section class="print-letterhead-body" aria-label="<?php echo esc_attr(t('print_letterhead')); ?>">
					<!-- Empty writing area under letterhead only -->
				</section>
<?php } else { ?>
				<section class="print-slip-info-block">
					<table class="print-blank-info-table">
						<tr>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html(isset($orders->man_napno) ? $orders->man_napno : ''); ?></td>
							<th class="print-blank-info-table__label ur urd">ناپ نمبر</th>
							<td class="print-blank-info-table__value ur urd print-blank-info-table__value--wide"><?php echo esc_html(isset($orders->name) ? $orders->name : ''); ?></td>
							<th class="print-blank-info-table__label ur urd">نام</th>
						</tr>
						<tr>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html($rdate_fmt); ?></td>
							<th class="print-blank-info-table__label ur urd">تاریخ واپسی</th>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html($odate_fmt); ?></td>
							<th class="print-blank-info-table__label ur urd">تاریخ</th>
						</tr>
						<tr>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html((string) $blank_balance); ?></td>
							<th class="print-blank-info-table__label ur urd">بقایا</th>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html(isset($orders->advance) ? $orders->advance : ''); ?></td>
							<th class="print-blank-info-table__label ur urd">ایڈوانس</th>
							<td class="print-blank-info-table__value ur urd"><?php echo esc_html(isset($orders->qty) ? $orders->qty : ''); ?></td>
							<th class="print-blank-info-table__label ur urd">سوٹ تعداد</th>
						</tr>
					</table>
				</section>

				<section class="print-slip-body-block" aria-label="Order details writing area">
					<div class="print-blank-suit-lines">
						<?php for ($line_no = 1; $line_no <= 5; $line_no++) { ?>
						<div class="print-blank-suit-line">
							<span class="print-blank-suit-line__no">-<?php echo $line_no; ?></span>
							<span class="print-blank-suit-line__rule"></span>
						</div>
						<div class="print-blank-suit-line print-blank-suit-line--sub">
							<span class="print-blank-suit-line__no"></span>
							<span class="print-blank-suit-line__rule"></span>
						</div>
						<?php } ?>
					</div>
					<div class="print-blank-write-area" aria-hidden="true"></div>
				</section>
<?php } ?>
			</main>

<?php if ( ! $is_letterhead) { ?>
			<footer class="print-slip-footer">
				<div class="print-slip-footer__title ur urd">ناغہ بروز جمعتہ المبارک</div>
				<div class="print-slip-footer__notes ur urd">
					<span>بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</span>
					<span>دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</span>
				</div>
			</footer>
			<div class="print-slip-credit">
				<span class="print-slip-credit__rule" aria-hidden="true"></span>
				<p class="print-slip-credit__text ur urd">یہ سافٹ ویئر <strong>عزیز الرحمن</strong> نے تیار کیا ہے — ایسا سافٹ ویئر یا کسی بھی قسم کا سافٹ ویئر بنوانا ہو تو رابطہ کریں: <a href="tel:03060407042" class="print-slip-credit__phone" dir="ltr">0306-0407042</a></p>
				<span class="print-slip-credit__rule" aria-hidden="true"></span>
			</div>
<?php } ?>
		</div>
	</div>
</div>
</body>
</html>
