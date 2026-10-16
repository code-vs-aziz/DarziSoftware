
<?php
	$pdf_mode = !empty($pdf_mode);
	$business_config = isset($business_config) && is_array($business_config) ? $business_config : array();
	$print_phone = !empty($business_config['phone']) ? $business_config['phone'] : '041-8751922';
	$print_address = !empty($business_config['address_urdu']) ? $business_config['address_urdu'] : 'شیخوپورہ روڈ چوک نشاط آباد فیصل آباد';
	$web_img_base = rtrim(base_url(), '/').'/';
	$fs_img_base = (isset($print_img_base) && $print_img_base !== '')
		? rtrim(str_replace('\\', '/', (string) $print_img_base), '/').'/'
		: rtrim(str_replace('\\', '/', FCPATH), '/').'/';
	$pdf_img = function ($rel) use ($pdf_mode, $web_img_base, $fs_img_base) {
		$rel = ltrim(str_replace('\\', '/', (string) $rel), '/');
		if ($pdf_mode)
		{
			return str_replace('\\', '/', $fs_img_base.$rel);
		}
		$parts = array();
		foreach (explode('/', $rel) as $part)
		{
			if ($part !== '')
			{
				$parts[] = rawurlencode($part);
			}
		}
		return $web_img_base.implode('/', $parts);
	};
	if ( ! isset($SR))
	{
		$SR = 0;
	}

	if ( ! $pdf_mode)
	{
		ob_Start();
		$this->load->view('Head');
	}
?>
<?php if ( ! $pdf_mode) { ?>
<head>
    <!-- Required meta tags-->
  

    <!-- Title Page-->
    <title> </title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/jquery.dataTables.css">
 
	<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/jquery.dataTables.js"></script>
	  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:300i" rel="stylesheet">
    <!-- Fontfaces CSS-->
<?php } ?>
   <style media="print">
   .top{
		margin-top:50px;
   }
	.print{
			display:none;
	}
   @page {
  size: landscape;
}
   </style>
   <style>
   <?php if ($pdf_mode) { ?>
   .print { display: none !important; }
   .page-break { display: none !important; height: 0; }
   body, table, td, th, b, span, div, article {
     font-family: xbriyaz, lateef, sans-serif;
     font-size: 20pt;
     line-height: 1.45;
   }
   .ur, .urd { font-family: xbriyaz, lateef, sans-serif; font-weight: bold; direction: rtl; text-align: right; }
   body { margin: 0 !important; }
   .top { margin-top: 0 !important; }
   .pdf-slip, .pdf-slip .container-fluid, .pdf-slip .col-md-14 { width: 100% !important; max-width: 100% !important; }
   .print-slip-panel { border: 2px solid #475569; padding: 6px 8px; margin-bottom: 8px; page-break-inside: auto; }
   .print-slip-panel__head {
     text-align: center; font-weight: bold; font-size: 22pt;
     background: #e2e8f0; padding: 6px 4px; margin-bottom: 6px;
     border-bottom: 2px solid #475569;
   }
   .print-slip-meas__heading {
     text-align: center; font-size: 18pt; font-weight: bold;
     margin: 4px 0 6px; border-bottom: 1.5px solid #64748b; padding-bottom: 3px;
   }
   /* Two equal columns; label+value kept together in each cell (no wide gap) */
   .pdf-meas-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 4px; }
   .pdf-meas-table td.pdf-meas-cell {
     width: 50%; border-bottom: 1.5px solid #1e293b; padding: 9px 12px;
     vertical-align: middle; font-size: 22pt; line-height: 1.45; font-weight: bold;
     direction: rtl; text-align: right;
   }
   .pdf-meas-table .pdf-meas-num {
     font-size: 24pt; font-weight: bold; direction: ltr; unicode-bidi: embed;
   }
   .pdf-meas-note-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
   .pdf-meas-note-table td {
     border-bottom: 1.5px solid #1e293b; padding: 7px 8px; font-size: 16pt; font-weight: bold;
     direction: rtl; text-align: right; vertical-align: top;
   }
   .pdf-meas-note-table .m-note-lbl { width: 22%; white-space: nowrap; }
   .pdf-meas-note-table .m-note-val { width: 78%; direction: rtl; text-align: right; }
   .print-suit-card { border-bottom: 1.5px solid #94a3b8; padding: 6px 2px 8px; margin: 0; page-break-inside: avoid; }
   .print-suit-card__table { width: 100%; border-collapse: collapse; }
   .print-suit-card__price { font-weight: bold; font-size: 18pt; text-align: left; white-space: nowrap; vertical-align: top; }
   .print-suit-card__fabric img { height: 44px; width: 56px; border: 1px solid #94a3b8; }
   .print-suit-card__titles { text-align: right; direction: rtl; vertical-align: top; }
   .print-suit-card__name { font-weight: bold; font-size: 17pt; display: block; margin-bottom: 3px; }
   .print-suit-card__pockets { font-size: 14pt; display: block; margin-bottom: 2px; font-weight: bold; }
   .print-suit-card__extra { font-size: 13pt; color: #1e293b; display: block; font-weight: bold; }
   .print-suit-card__badge {
     display: inline-block; min-width: 24px; padding: 3px 8px; border-radius: 999px;
     background: #0f172a; color: #fff; font-weight: bold; font-size: 14pt; text-align: center;
   }
   .pdf-brand-badge {
     background: #0f172a; color: #fff; font-weight: bold; font-size: 14pt;
     padding: 8px 12px; display: inline-block; border-radius: 6px; line-height: 1.4;
     font-family: lateef, xbriyaz, sans-serif;
   }
   .pdf-footer-holiday { font-size: 18pt; font-weight: bold; background: #cbd5e1; padding: 8px; text-align: center; }
   .pdf-footer-terms { font-size: 14pt; font-weight: bold; text-align: right; direction: rtl; }
   <?php } else { ?>
  @media print {
	.page-break	{ display: block; page-break-before: always; }
	
	 @page { margin: 0; }
  body { margin: 5.6cm; }
}
   <?php } ?>
  
	<?php if ($pdf_mode) { ?>
	.urd, .ur {
		font-family: lateef, xbriyaz, sans-serif;
		font-weight: bold;
	}
	<?php } else { ?>
	.urd{
			font-weight:bold;
			font-family: 'Noto Nastaliq Urdu Draft', lateef, xbriyaz, serif;
				 
	}
 
	.ur {
		font-family: 'Jameel Noori Nastaleeq', 'Noto Naskh Arabic', lateef, xbriyaz, sans-serif;
	}
	<?php } ?>
	 #Remarks  {
		direction: rtl;
		border: 1px solid #ccc;
		vertical-align: middle;
		direction: rtl;
		padding: 2px;
		font-size: 16px;
	}
	#kb {
		width:500px;
	}
	a {
		color:#4ea0e8;
		text-decoration:none;
	}
	</style>

<?php if ( ! $pdf_mode) { ?>
</head>
	<script type="text/javascript" language="javascript" class="init">
	
$(document).ready(function() {
	$('#example').DataTable( {
		"pagingType": "full_numbers"
	} );
} );

	</script>
<body class="animsition erp-app order-print-page print-size-small">
<?php } ?>
<?php
	if ( ! isset($types) || ! is_array($types))
	{
		$types = (isset($type) && $type !== '') ? array($type) : array();
	}
	$print_has_type = function ($needle) use ($types) {
		return in_array($needle, $types, TRUE);
	};
?>
									<div class="page-wrapper top<?php echo $pdf_mode ? ' pdf-slip' : ''; ?>">
        <!-- HEADER MOBILE-->
            <!-- MAIN CONTENT-->

            <div class=" ">
                <div  >
                    <div class="container-fluid">
						<?php
			 $orders = reset($order);
			 $order_number = isset($orders->TRNNO) ? $orders->TRNNO : 0;
			 $notify_auto_dispatch = 0;
			 $order_whatsapp_prompt = (int) $this->session->flashdata('order_whatsapp_prompt');
			 $customer_phone = isset($orders->Phone) ? trim((string) $orders->Phone) : '';
			 $has_whatsapp_phone = ($customer_phone !== '');
			 $wa_phone = '';
			 if ($has_whatsapp_phone) {
				 $digits = preg_replace('/\D+/', '', $customer_phone);
				 if ($digits !== '') {
					 if (strpos($digits, '92') === 0 && strlen($digits) >= 12) {
						 $wa_phone = $digits;
					 } elseif ($digits[0] === '0' && strlen($digits) >= 11) {
						 $wa_phone = '92'.substr($digits, 1);
					 } elseif (strlen($digits) === 10 && $digits[0] === '3') {
						 $wa_phone = '92'.$digits;
					 } else {
						 $wa_phone = $digits;
					 }
				 }
			 }
						?>
                     	<?php if ( ! $pdf_mode) { ?>
                                   <div class="order-print-toolbar print">
										<div class="order-print-toolbar__inner">
											<div class="order-print-toolbar__left">
												<a href="<?php echo site_url('Welcome'); ?>" class="btn btn-lg btn-secondary" data-no-ajax="true"><?php echo t('print_back'); ?></a>
												<a href="<?php echo site_url('Order/Order_edit/'.$order_number.''); ?>" class="btn btn-lg btn-primary" data-no-ajax="true"><?php echo t('print_order_edit'); ?></a>
											</div>
											<div class="order-print-toolbar__right">
<?php $this->load->view('partials/print_size_selector', array('default_size' => 'small')); ?>
												<button type="button" class="btn btn-lg btn-success btn-order-whatsapp" data-trnno="<?php echo (int) $order_number; ?>" data-has-phone="<?php echo $has_whatsapp_phone ? '1' : '0'; ?>" data-wa-phone="<?php echo esc_html($wa_phone); ?>" data-saved-phone="<?php echo esc_html($customer_phone); ?>" data-auto-prompt="<?php echo ($order_whatsapp_prompt > 0 && (int) $order_number === $order_whatsapp_prompt) ? '1' : '0'; ?>" data-endpoint="<?php echo site_url('Order/order_whatsapp_pdf'); ?>"><?php echo t('print_whatsapp'); ?></button>
												<button type="button" class="btn btn-lg btn-primary" onclick="window.print()"><?php echo t('app_print'); ?></button>
											</div>
										</div>
                                   </div>
                     	<?php } ?>
                     	<div class="<?php echo $pdf_mode ? 'col-md-14' : 'print-slip-sheet'; ?>" style="<?php echo $pdf_mode ? 'width:100%;' : ''; ?>">
                                	  	<?php if ( ! $pdf_mode) { ?>
			<img src="<?php echo $pdf_img('images/darzi-baba-logo.png'); ?>" alt="" class="print-slip-watermark" aria-hidden="true">
			<?php } ?>
									<section>
 				
			<?php if ($pdf_mode) { ?>
			<table class="pdf-struct pdf-header" width="100%" cellpadding="4" cellspacing="0" style="margin-bottom:6px;">
				<tr>
					<td width="16%" valign="middle" align="left">
						<span style="display:inline-block;border:2px solid #0f172a;border-radius:999px;padding:6px 12px;font-weight:bold;font-size:18pt;"><?php echo (int) $orders->TRNNO; ?></span>
					</td>
					<td width="48%" align="center" valign="middle">
						<img src="<?php echo $pdf_img('images/darzi-baba-logo.png'); ?>" style="height:64px; width:auto; max-width:190px;" alt="Darzi Baba"><br>
						<span class="ur urd" style="font-size:12pt;display:block;margin-top:1px;"><?php echo esc_html($print_address); ?></span>
						<span style="font-weight:bold;font-size:12pt;">Ph: <?php echo esc_html($print_phone); ?></span>
					</td>
					<td width="36%" align="right" valign="middle" class="ur urd">
						<span class="pdf-brand-badge">پینٹ کوٹ، ویسکوٹ، شلوار قمیض، سپیشلسٹ</span>
					</td>
				</tr>
			</table>
			<?php } else { ?>
			<header class="print-slip-brand print-slip-brand--classic">
				<div class="print-slip-brand__trn"><?php echo (int) $orders->TRNNO; ?></div>
				<div class="print-slip-brand__center">
					<img src="<?php echo $pdf_img('images/darzi-baba-logo.png'); ?>" alt="Darzi Baba" class="print-slip-brand__logo" width="280" height="176">
					<span class="print-slip-brand__address ur urd"><?php echo esc_html($print_address); ?></span>
					<span class="print-slip-brand__phone">Ph: <?php echo esc_html($print_phone); ?></span>
				</div>
				<div class="print-slip-brand__meta">
					<span class="print-slip-brand__badge print-slip-brand__badge--single ur">پینٹ کوٹ، ویسکوٹ، شلوار قمیض، سپیشلسٹ</span>
				</div>
			</header>
			<?php } ?>
			<?php
			$customer_photo = isset($orders->image) ? trim((string) $orders->image) : '';
			$has_customer_photo = ($customer_photo !== '');
			$rdate_fmt = !empty($orders->RDATE) ? date('d-m-Y', strtotime($orders->RDATE)) : '';
			$odate_fmt = !empty($orders->ORDERDATE) ? date('d-m-Y', strtotime($orders->ORDERDATE)) : '';
			$balance_amt = (float) $orders->total - (float) $orders->advance - (float) $orders->discount;
			$prev_balance = isset($orders->prev_balance) ? (float) $orders->prev_balance : 0;
			$fmt_money = function ($n) {
				return number_format((float) $n, 2, '.', ',');
			};
			?>
			<?php if ($pdf_mode) { ?>
			<table class="pdf-struct pdf-info-table" width="100%" cellpadding="5" cellspacing="0" style="width:100%;margin:4px 0 10px;border-bottom:2px solid #111;font-size:16pt;" dir="rtl">
				<tr>
					<?php if ($has_customer_photo) { ?>
					<td width="14%" rowspan="3" valign="top" align="center" style="padding:2px 0 6px 8px;">
						<img src="<?php echo esc_html($customer_photo); ?>" style="height:90px;width:90px;object-fit:cover;border:1px solid #111;" alt="">
					</td>
					<?php } ?>
					<td width="43%" style="padding:5px 6px;vertical-align:bottom;font-size:16pt;">
						<span class="ur urd" style="white-space:nowrap;font-weight:bold;font-size:16pt;">نام</span>
						<span class="ur urd" style="display:inline-block;min-width:55%;border-bottom:1px dotted #111;margin-right:6px;padding:0 4px;font-weight:bold;font-size:18pt;"><?php echo esc_html($orders->name); ?></span>
					</td>
					<td width="43%" style="padding:5px 6px;vertical-align:bottom;font-size:16pt;">
						<span class="ur urd" style="white-space:nowrap;font-weight:bold;font-size:16pt;">ناپ نمبر</span>
						<span class="ur urd" style="display:inline-block;min-width:45%;border-bottom:1px dotted #111;margin-right:6px;padding:0 4px;font-weight:bold;font-size:18pt;"><?php echo esc_html($orders->man_napno); ?></span>
					</td>
				</tr>
				<tr>
					<td style="padding:5px 6px;vertical-align:bottom;font-size:16pt;">
						<span class="ur urd" style="white-space:nowrap;font-weight:bold;font-size:16pt;">تاریخ</span>
						<span class="ur urd" style="display:inline-block;min-width:50%;border-bottom:1px dotted #111;margin-right:6px;padding:0 4px;font-weight:bold;font-size:16pt;"><?php echo $odate_fmt; ?></span>
					</td>
					<td style="padding:5px 6px;vertical-align:bottom;font-size:16pt;">
						<span class="ur urd" style="white-space:nowrap;font-weight:bold;font-size:16pt;">تاریخ واپسی</span>
						<span class="ur urd" style="display:inline-block;min-width:35%;border-bottom:1px dotted #111;margin-right:6px;padding:0 4px;font-weight:bold;font-size:16pt;"><?php echo $rdate_fmt; ?></span>
						<span class="ur urd" style="white-space:nowrap;margin-right:8px;font-weight:bold;font-size:15pt;">رات 08:00 بجے</span>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center" class="ur urd" style="padding:10px 4px 6px;font-size:16pt;">
						<span style="display:inline-block;margin:0 10px;white-space:nowrap;"><b>سوٹ تعداد</b> <?php echo $fmt_money($orders->qty); ?></span>
						<span style="display:inline-block;margin:0 10px;white-space:nowrap;"><b>ایڈوانس</b> <?php echo $fmt_money($orders->advance); ?></span>
						<span style="display:inline-block;margin:0 10px;white-space:nowrap;"><b>بقایا</b> <?php echo $fmt_money($balance_amt); ?></span>
						<span style="display:inline-block;margin:0 10px;white-space:nowrap;"><b>سابقہ بقایا</b> <?php echo $fmt_money($prev_balance); ?></span>
					</td>
				</tr>
			</table>
			<?php
				// Suits first on WhatsApp PDF so order lines stay on page 1 with customer info.
				if ($pdf_mode)
				{
					$this->load->view('partials/order_print_suit_items', array(
						'order' => $order,
						'suit' => $suit,
						'style' => $style,
						'embroidery' => $embroidery,
						'pdf_img' => $pdf_img,
						'pdf_mode' => TRUE,
					));
				}
			?>
			<?php } else { ?>
			<div class="print-slip-info<?php echo $has_customer_photo ? ' has-photo' : ''; ?>">
				<?php if ($has_customer_photo) { ?>
				<div class="print-slip-info__photo">
					<img src="<?php echo esc_html($customer_photo); ?>" alt="Customer photo">
				</div>
				<?php } ?>
				<div class="print-slip-info-grid" dir="rtl">
					<div class="print-slip-pair">
						<span class="print-slip-pair__label ur urd">نام</span>
						<span class="print-slip-pair__value ur urd"><?php echo esc_html($orders->name); ?></span>
					</div>
					<div class="print-slip-pair">
						<span class="print-slip-pair__label ur urd">ناپ نمبر</span>
						<span class="print-slip-pair__value ur urd"><?php echo esc_html($orders->man_napno); ?></span>
					</div>
					<div class="print-slip-pair">
						<span class="print-slip-pair__label ur urd">تاریخ</span>
						<span class="print-slip-pair__value ur urd"><?php echo $odate_fmt; ?></span>
					</div>
					<div class="print-slip-pair">
						<span class="print-slip-pair__label ur urd">تاریخ واپسی</span>
						<span class="print-slip-pair__value ur urd"><?php echo $rdate_fmt; ?> <small>رات 08:00 بجے</small></span>
					</div>
					<div class="print-slip-money">
						<span class="ur urd"><b>سوٹ تعداد</b> <?php echo $fmt_money($orders->qty); ?></span>
						<span class="ur urd"><b>ایڈوانس</b> <?php echo $fmt_money($orders->advance); ?></span>
						<span class="ur urd"><b>بقایا</b> <?php echo $fmt_money($balance_amt); ?></span>
						<span class="ur urd"><b>سابقہ بقایا</b> <?php echo $fmt_money($prev_balance); ?></span>
					</div>
				</div>
			</div>
			<?php } ?>
<?php if ($pdf_mode) { ?><!-- measurements full-width below suits --><?php } else { ?><div class="print-slip-body"><?php } ?>
			<div class="print-slip-meas-col">
				<div class="print-slip-panel print-slip-panel--meas">
					<div class="print-slip-panel__head ur urd">ناپ تفصیل</div>
		<?php
			$SR = 0;
			$orders = reset($order);
			$meas_trim = function ($key) use ($orders) {
				return isset($orders->$key) ? trim((string) $orders->$key) : '';
			};
			$meas_field = function ($value, $label) {
				$value = trim((string) $value);
				$label = trim((string) $label);
				if ($value === '' && $label === '')
				{
					return '<div class="print-slip-meas__field print-slip-meas__field--empty" aria-hidden="true"></div>';
				}
				return '<div class="print-slip-meas__field">'
					.'<span class="print-slip-meas__label">'.esc_html($label).'</span>'
					.'<span class="print-slip-meas__val">'.esc_html($value).'</span>'
					.'</div>';
			};
			$meas_pair_cell = function ($value, $label) {
				$value = trim((string) $value);
				$label = trim((string) $label);
				// WhatsApp PDF: skip blanks so filled rows stay large & clear.
				if ($value === '')
				{
					return '';
				}
				// Label + value together (RTL): "قمیض لمبائی  43" — no wide orphan gap.
				return '<td class="pdf-meas-cell ur urd" width="50%" style="width:50%;font-size:22pt;font-weight:bold;font-family:xbriyaz,lateef,sans-serif;direction:rtl;text-align:right;padding:9px 12px;border-bottom:1.5px solid #1e293b;vertical-align:middle;">'
					.esc_html($label)
					.'&nbsp;&nbsp;<span class="pdf-meas-num" dir="ltr" style="font-size:24pt;font-weight:bold;direction:ltr;">'
					.esc_html($value).'</span></td>';
			};
			$meas_section = function ($title, $rows) use ($meas_field, $meas_pair_cell, $pdf_mode) {
				$html = '<section class="print-slip-meas__section">';
				$html .= '<h3 class="print-slip-meas__heading ur urd">'.esc_html($title).'</h3>';
				if ($pdf_mode)
				{
					$pairs = array();
					foreach ($rows as $row)
					{
						$a = $meas_pair_cell(isset($row[0]) ? $row[0] : '', isset($row[1]) ? $row[1] : '');
						$b = $meas_pair_cell(isset($row[2]) ? $row[2] : '', isset($row[3]) ? $row[3] : '');
						if ($a !== '') { $pairs[] = $a; }
						if ($b !== '') { $pairs[] = $b; }
					}
					if (empty($pairs))
					{
						return '';
					}
					$html .= '<table class="pdf-meas-table" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;table-layout:fixed;">';
					for ($i = 0, $n = count($pairs); $i < $n; $i += 2)
					{
						$left = $pairs[$i];
						$right = isset($pairs[$i + 1])
							? $pairs[$i + 1]
							: '<td width="50%" style="width:50%;border-bottom:1.5px solid #1e293b;"></td>';
						$html .= '<tr>'.$left.$right.'</tr>';
					}
					$html .= '</table>';
				}
				else
				{
					$html .= '<div class="print-slip-meas__grid">';
					foreach ($rows as $row)
					{
						$html .= $meas_field(isset($row[0]) ? $row[0] : '', isset($row[1]) ? $row[1] : '');
						$html .= $meas_field(isset($row[2]) ? $row[2] : '', isset($row[3]) ? $row[3] : '');
					}
					$html .= '</div>';
				}
				$html .= '</section>';
				return $html;
			};
			$has_kurta_nap = (
				$meas_trim('KL') !== '' || $meas_trim('BL') !== '' || $meas_trim('Teera') !== '' ||
				$meas_trim('Chaati') !== '' || $meas_trim('Gala') !== '' || $meas_trim('Shalwar_Lenght') !== '' ||
				$meas_trim('Asan') !== '' || $meas_trim('geera') !== '' || $meas_trim('pait') !== '' ||
				$meas_trim('WASIT') !== '' || $meas_trim('Poncha_Choraic') !== '' || $meas_trim('FPL') !== ''
			);
			$show_kurta_block = ($print_has_type('Kurta') || $has_kurta_nap);
			if ($show_kurta_block)
			{
				echo $meas_section('قمیض شلوار', array(
					array($orders->KL, 'قمیض لمبائی', $orders->BL, 'باذو'),
					array($orders->Teera, 'تیرا', $orders->geera, 'گیرا'),
					array($orders->Chaati, 'چھاتی', $orders->pait, 'پیٹ'),
					array($orders->Gala, 'گلا', $orders->Shalwar_Lenght, 'شلوار لمبائی'),
					array($orders->Poncha_Choraic, 'پانچہ', $orders->SC, 'شلوار پھیر'),
					array($orders->Asan, 'آسن', $orders->FPL, 'فرنٹ پٹی'),
					array($orders->BP, 'باذو پٹی', $orders->CAKLAMBAI, 'چاک لمبائی'),
					array($orders->CL, 'کف لمبائی', $orders->KAAFCURAI, 'کف چوڑائی'),
					array($orders->MODA_CURAI, 'موڈاچوڑائی', $orders->Poncha_Chorai, 'پانچہ چوڑائی'),
					array($orders->WASIT, 'کمر (ویسٹ)', $orders->FRONT_Pocket_Size, 'فرنٹ پاکٹ سائز'),
					array($orders->BG, 'باذو گول', $orders->Side_Pocket_Size, 'سائیڈ پاکٹ سائز'),
				));
			}
			if ($print_has_type('Pajama'))
			{
				echo $meas_section('پاجامہ', array(
					array($orders->PPoncha, 'پانچہ', $orders->PLambai, 'لمبائی'),
					array($orders->PPeeir, 'پپھیر', $orders->PAsan, 'آسن'),
				));
			}
			if ($print_has_type('Pent'))
			{
				echo $meas_section('پینٹ', array(
					array($orders->pentWaiste, 'کمر', $orders->PentLambai, 'لمبائی'),
					array($orders->PentPoncha, 'پانچہ', $orders->HIP, 'ہپ'),
					array('', '', $orders->PentThai, 'Thai'),
				));
			}
			if ($print_has_type('Shirt'))
			{
				echo $meas_section('شرٹ', array(
					array($orders->SBazoo, 'باذو', $orders->SLambai, 'لمبائی'),
					array($orders->SGeera, 'گیرا', $orders->STeera, 'تیرا'),
					array($orders->SKamar, 'کمر', $orders->SChaati, 'چھاتی'),
					array($orders->SGaala, 'گلا', '', ''),
				));
			}
			if ($print_has_type('Coat'))
			{
				echo $meas_section('شروانی/کوٹ', array(
					array($orders->CoatBazoo, 'باذو', $orders->CoatLambai, 'لمبائی'),
					array($orders->CoatChaati, 'چھاتی', $orders->CoatTeera, 'تیرا'),
					array($orders->CoatKamar, 'کمر', $orders->CoatHIP, 'ہپ'),
					array($orders->CoatGaala, 'گلا', $orders->crossback, 'Cross Back'),
				));
			}
			if ($print_has_type('Waistcoat'))
			{
				echo $meas_section('ویسکوٹ', array(
					array($orders->wastTeera, 'تیرا', $orders->wastLambai, 'لمبائی'),
					array($orders->wastChaati, 'چھاتی', $orders->wastHIP, 'ہپ'),
					array($orders->wastKamar, 'کمر', '', ''),
					array($orders->wastGaala, 'گلا', '', ''),
				));
			}
			?>
					<?php if ($pdf_mode) { ?>
					<table class="pdf-meas-note-table" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td class="m-note-lbl ur urd">نوٹ لائن</td>
							<td class="m-note-val ur urd"><?php echo esc_html($orders->MREMARKS); ?></td>
						</tr>
					</table>
					<?php } else { ?>
					<div class="print-slip-meas-note">
						<div class="print-slip-meas__field print-slip-meas__field--note">
							<span class="print-slip-meas__label">نوٹ لائن</span>
							<span class="print-slip-meas__val"><?php echo esc_html($orders->MREMARKS); ?></span>
						</div>
					</div>
					<?php } ?>
				</div>
					</div>
<?php if ( ! $pdf_mode) { ?>
			<div class="print-slip-suits-col">
<?php
	$this->load->view('partials/order_print_suit_items', array(
		'order' => $order,
		'suit' => $suit,
		'style' => $style,
		'embroidery' => $embroidery,
		'pdf_img' => $pdf_img,
		'pdf_mode' => FALSE,
	));
?>
			</div>
</div>
<?php } ?>
					
	</section>
<?php if ( ! $pdf_mode) { ?>
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
<?php if ($pdf_mode) { ?>
<table class="pdf-struct pdf-footer" width="100%" cellpadding="6" cellspacing="0" style="margin-top:6px;">
	<tr>
		<td width="42%" class="pdf-footer-holiday ur urd">ناغہ بروز جمعتہ المبارک</td>
		<td width="58%" class="pdf-footer-terms ur urd">بجلی نہ ہونے کی صورت میں وعدہ کی پابندی لازمی نہیں ہوگی</td>
	</tr>
	<tr>
		<td></td>
		<td class="pdf-footer-terms ur urd">دو ماہ کے بعد دوکاندار کپڑے کا ذمہ دار نہ ہو گا</td>
	</tr>
</table>
<table width="100%" cellpadding="4" cellspacing="0" style="margin-top:6px;">
	<tr>
		<td align="center" class="ur urd" style="font-size:12pt; color:#334155; border-top:1px solid #cbd5e1; padding-top:6px; font-weight:bold;">
			یہ سافٹ ویئر <b>عزیز الرحمن</b> نے تیار کیا ہے — ایسا سافٹ ویئر یا کسی بھی قسم کا سافٹ ویئر بنوانا ہو تو رابطہ کریں: <span dir="ltr">0306-0407042</span>
		</td>
	</tr>
</table>
<?php } ?>
                     
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>
		
		
    <!-- Jquery JS-->
    
    <!-- Bootstrap JS-->

 
    
    
    </div>

 

<?php if ( ! $pdf_mode) { ?>
<style>
	.dz-print-notify-toast {
		position: fixed;
		bottom: 24px;
		right: 24px;
		z-index: 9999;
		max-width: 360px;
		padding: 12px 16px;
		border-radius: 10px;
		font-size: 14px;
		color: #fff;
		background: #334155;
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
		display: none;
	}
	.dz-print-notify-toast.is-ok { background: #059669; }
	.dz-print-notify-toast.is-error { background: #dc2626; }
</style>
<script>
window.ORDER_PRINT_NOTIFY = <?php echo json_encode(array(
	'orderId' => (int) $order_number,
	'autoDispatch' => FALSE,
	'endpoint' => site_url('Notifications/dispatch_for_order'),
), JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="<?php echo base_url('js/order-print-notify.js?v=2'); ?>"></script>
<div id="dz-wa-modal" class="dz-wa-modal" hidden>
	<div class="dz-wa-modal__panel" role="dialog" aria-modal="true" aria-labelledby="dz-wa-modal-title">
		<h3 id="dz-wa-modal-title" class="dz-wa-modal__title"><?php echo esc_html(t('print_wa_title')); ?></h3>
		<p class="dz-wa-modal__help ur urd"><?php echo esc_html(t('print_wa_help')); ?></p>
		<label class="dz-wa-modal__option">
			<input type="radio" name="dz_wa_choice" value="saved" id="dz-wa-choice-saved">
			<span><?php echo esc_html(t('print_wa_use_saved')); ?> <strong id="dz-wa-saved-display">—</strong></span>
		</label>
		<label class="dz-wa-modal__option">
			<input type="radio" name="dz_wa_choice" value="other" id="dz-wa-choice-other">
			<span><?php echo esc_html(t('print_wa_use_other')); ?></span>
		</label>
		<input type="tel" id="dz-wa-phone-input" class="form-control dz-wa-modal__input" inputmode="tel" placeholder="<?php echo esc_attr(t('print_wa_phone_placeholder')); ?>" dir="ltr">
		<div id="dz-wa-bridge" class="dz-wa-bridge" hidden>
			<p class="dz-wa-bridge__state" id="dz-wa-bridge-state"></p>
			<div class="dz-wa-bridge__qr-wrap" id="dz-wa-bridge-qr-wrap" hidden>
				<p class="dz-wa-bridge__qr-title ur urd"><strong><?php echo esc_html(t('notify_bridge_scan_qr')); ?></strong></p>
				<img id="dz-wa-bridge-qr" class="dz-wa-bridge__qr-img" width="240" height="240" alt="WhatsApp QR">
				<p class="dz-wa-bridge__hint ur urd"><?php echo esc_html(t('notify_bridge_scan_help')); ?></p>
			</div>
			<p class="dz-wa-bridge__hint ur urd" id="dz-wa-bridge-no-qr" hidden><?php echo esc_html(t('print_wa_qr_waiting')); ?></p>
			<div class="dz-wa-bridge__actions">
				<button type="button" class="btn btn-sm btn-outline-secondary" id="dz-wa-bridge-refresh"><?php echo esc_html(t('notify_bridge_btn_refresh')); ?></button>
				<button type="button" class="btn btn-sm btn-primary" id="dz-wa-bridge-start"><?php echo esc_html(t('notify_bridge_btn_start')); ?></button>
				<button type="button" class="btn btn-sm btn-warning" id="dz-wa-bridge-qr"><?php echo esc_html(t('print_wa_show_qr')); ?></button>
			</div>
		</div>
		<p id="dz-wa-modal-error" class="dz-wa-modal__error" hidden></p>
		<div class="dz-wa-modal__actions">
			<button type="button" class="btn btn-secondary" id="dz-wa-cancel"><?php echo esc_html(t('print_wa_cancel')); ?></button>
			<button type="button" class="btn btn-success" id="dz-wa-send"><?php echo esc_html(t('print_wa_send_pdf')); ?></button>
		</div>
	</div>
</div>
<style>
.dz-wa-modal {
	position: fixed;
	inset: 0;
	z-index: 12000;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 1rem;
	background: rgba(15, 23, 42, 0.45);
}
.dz-wa-modal[hidden] { display: none !important; }
.dz-wa-modal__panel {
	width: min(460px, 100%);
	max-height: min(92vh, 720px);
	overflow-y: auto;
	background: #fff;
	border-radius: 12px;
	padding: 1.15rem 1.25rem 1.1rem;
	box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
}
.dz-wa-bridge {
	margin: 0.5rem 0 0.65rem;
	padding: 0.75rem 0.85rem;
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	background: #f8fafc;
}
.dz-wa-bridge__state {
	margin: 0 0 0.45rem;
	font-size: 0.88rem;
	font-weight: 600;
	color: #334155;
}
.dz-wa-bridge__state.is-ok { color: #059669; }
.dz-wa-bridge__state.is-wait { color: #d97706; }
.dz-wa-bridge__state.is-off { color: #dc2626; }
.dz-wa-bridge__qr-wrap {
	text-align: center;
	margin: 0.35rem 0 0.55rem;
}
.dz-wa-bridge__qr-title {
	margin: 0 0 0.35rem;
	font-size: 0.88rem;
	line-height: 1.5;
}
.dz-wa-bridge__qr-img {
	display: block;
	margin: 0 auto 0.35rem;
	border: 1px solid #cbd5e1;
	border-radius: 8px;
	background: #fff;
}
.dz-wa-bridge__hint {
	margin: 0;
	font-size: 0.78rem;
	color: #64748b;
	line-height: 1.45;
}
.dz-wa-bridge__actions {
	display: flex;
	flex-wrap: wrap;
	gap: 0.45rem;
	justify-content: center;
}
.dz-wa-modal__title {
	margin: 0 0 0.45rem;
	font-size: 1.15rem;
	font-weight: 700;
	color: #0f172a;
}
.dz-wa-modal__help {
	margin: 0 0 0.85rem;
	font-size: 0.92rem;
	line-height: 1.55;
	color: #334155;
}
.dz-wa-modal__option {
	display: flex;
	align-items: flex-start;
	gap: 0.5rem;
	margin: 0 0 0.55rem;
	font-size: 0.92rem;
	color: #0f172a;
	cursor: pointer;
}
.dz-wa-modal__option input { margin-top: 0.2rem; }
.dz-wa-modal__input {
	margin: 0.35rem 0 0.65rem;
	direction: ltr;
	text-align: left;
}
.dz-wa-modal__input:disabled {
	opacity: 0.55;
	background: #f1f5f9;
}
.dz-wa-modal__error {
	margin: 0 0 0.65rem;
	color: #dc2626;
	font-size: 0.86rem;
}
.dz-wa-modal__actions {
	display: flex;
	justify-content: flex-end;
	gap: 0.5rem;
	margin-top: 0.35rem;
}
</style>
<script>
window.ORDER_PRINT_WA_BRIDGE = <?php echo json_encode(array(
	'statusUrl' => site_url('Notifications/bridge_status_json'),
	'startUrl' => site_url('Notifications/bridge_start'),
	'restartUrl' => site_url('Notifications/bridge_restart'),
	'labels' => array(
		'connected' => t('notify_bridge_connected'),
		'waiting' => t('notify_bridge_waiting'),
		'off' => t('notify_bridge_off'),
		'starting' => t('notify_bridge_starting'),
		'restarting' => t('notify_bridge_restarting'),
		'scanHere' => t('print_wa_scan_here'),
		'qrWaiting' => t('print_wa_qr_waiting'),
	),
), JSON_UNESCAPED_UNICODE); ?>;
</script>
<script>
(function () {
	if (window.__orderWhatsappInit) { return; }
	window.__orderWhatsappInit = true;

	var modal = document.getElementById('dz-wa-modal');
	var savedRadio = document.getElementById('dz-wa-choice-saved');
	var otherRadio = document.getElementById('dz-wa-choice-other');
	var phoneInput = document.getElementById('dz-wa-phone-input');
	var savedDisplay = document.getElementById('dz-wa-saved-display');
	var errEl = document.getElementById('dz-wa-modal-error');
	var sendBtn = document.getElementById('dz-wa-send');
	var cancelBtn = document.getElementById('dz-wa-cancel');
	var bridgeBox = document.getElementById('dz-wa-bridge');
	var bridgeState = document.getElementById('dz-wa-bridge-state');
	var bridgeQrWrap = document.getElementById('dz-wa-bridge-qr-wrap');
	var bridgeQrImg = document.getElementById('dz-wa-bridge-qr');
	var bridgeRefreshBtn = document.getElementById('dz-wa-bridge-refresh');
	var bridgeStartBtn = document.getElementById('dz-wa-bridge-start');
	var bridgeQrBtn = document.getElementById('dz-wa-bridge-qr');
	var bridgeNoQr = document.getElementById('dz-wa-bridge-no-qr');
	var bridgeCfg = window.ORDER_PRINT_WA_BRIDGE || {};
	var bridgeLabels = bridgeCfg.labels || {};
	var bridgePoll = null;
	var bridgeReady = false;
	var bridgeBusy = false;
	var openSince = 0;
	var activeBtn = null;
	var LANG = window.APP_LANG || {};

	function t(key, fallback) {
		return LANG[key] || fallback;
	}

	function csrfAppend(body) {
		var nameMeta = document.querySelector('meta[name="csrf-token-name"]');
		var valMeta = document.querySelector('meta[name="csrf-token-value"]');
		if (nameMeta && valMeta && nameMeta.content && valMeta.content) {
			body.append(nameMeta.content, valMeta.content);
		}
	}

	function showError(msg) {
		if (!errEl) { return; }
		errEl.hidden = !msg;
		errEl.textContent = msg || '';
	}

	function syncChoice() {
		var useOther = otherRadio && otherRadio.checked;
		if (phoneInput) {
			phoneInput.disabled = !useOther;
			if (useOther) { phoneInput.focus(); }
		}
		showError('');
	}

	function stopBridgePoll() {
		if (bridgePoll) {
			clearInterval(bridgePoll);
			bridgePoll = null;
		}
	}

	function startBridgePoll() {
		if (bridgePoll) { return; }
		bridgePoll = setInterval(refreshBridgeStatus, 2500);
	}

	function setBridgeState(kind, text) {
		if (!bridgeState) { return; }
		bridgeState.className = 'dz-wa-bridge__state' + (kind ? (' is-' + kind) : '');
		bridgeState.textContent = text || '';
	}

	function showBridgeQr(dataUrl) {
		if (!bridgeQrWrap || !bridgeQrImg) { return; }
		if (dataUrl) {
			bridgeQrImg.src = dataUrl;
			bridgeQrWrap.hidden = false;
		} else {
			bridgeQrWrap.hidden = true;
			bridgeQrImg.removeAttribute('src');
		}
	}

	function applyBridgePayload(data) {
		var st = (data && data.status) ? data.status : {};
		bridgeReady = !!(st.ready);
		if (bridgeBox) {
			bridgeBox.hidden = bridgeReady;
		}
		if (bridgeReady) {
			setBridgeState('ok', bridgeLabels.connected || 'WhatsApp connected');
			showBridgeQr('');
			if (bridgeNoQr) { bridgeNoQr.hidden = true; }
			stopBridgePoll();
			showError('');
			return true;
		}
		if (bridgeBox) {
			bridgeBox.hidden = false;
		}
		var hasQr = !!(data && data.qr);
		var waiting = !!(st.ok || hasQr || st.has_qr
			|| st.state === 'qr' || st.state === 'starting'
			|| st.state === 'authenticated' || st.state === 'restarting');
		if (waiting) {
			setBridgeState('wait', hasQr
				? (bridgeLabels.waiting || 'Scan QR to connect')
				: (bridgeLabels.scanHere || 'Waiting for QR…'));
			if (hasQr) {
				showBridgeQr(data.qr || '');
			}
			if (bridgeNoQr) {
				bridgeNoQr.hidden = hasQr || !!st.has_qr;
			}
			startBridgePoll();
			// Auto soft-refresh only if stuck starting with no QR for 20s (never wipe a live QR).
			if (!hasQr && !st.has_qr && !bridgeBusy && st.state === 'starting'
				&& openSince && (Date.now() - openSince) > 20000) {
				bridgeFreshQr(true);
			}
			return false;
		}
		setBridgeState('off', bridgeLabels.off || 'WhatsApp bridge off');
		showBridgeQr('');
		if (bridgeNoQr) { bridgeNoQr.hidden = false; }
		startBridgePoll();
		return false;
	}

	function refreshBridgeStatus() {
		if (!bridgeCfg.statusUrl) {
			return Promise.resolve(false);
		}
		return fetch(bridgeCfg.statusUrl, {
			credentials: 'same-origin',
			headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (res) { return res.text(); })
			.then(function (raw) {
				var data;
				try { data = JSON.parse(raw); } catch (err) { throw new Error('bad_json'); }
				if (!data || !data.success) {
					if (bridgeBox) { bridgeBox.hidden = false; }
					setBridgeState('off', bridgeLabels.off || 'WhatsApp bridge off');
					showBridgeQr('');
					bridgeReady = false;
					return false;
				}
				return applyBridgePayload(data);
			})
			.catch(function () {
				if (bridgeBox) { bridgeBox.hidden = false; }
				setBridgeState('off', bridgeLabels.off || 'WhatsApp bridge off');
				showBridgeQr('');
				bridgeReady = false;
				return false;
			});
	}

	function postBridge(url, body) {
		var fd = new FormData();
		if (body) {
			Object.keys(body).forEach(function (k) { fd.append(k, body[k]); });
		}
		var nameMeta = document.querySelector('meta[name="csrf-token-name"]');
		var valMeta = document.querySelector('meta[name="csrf-token-value"]');
		if (nameMeta && valMeta && nameMeta.content && valMeta.content) {
			fd.append(nameMeta.content, valMeta.content);
		}
		return fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
			body: fd
		}).then(function (res) { return res.text(); })
		.then(function (raw) {
			try { return JSON.parse(raw); }
			catch (err) { throw new Error('bad_json'); }
		});
	}

	function bridgeStart() {
		if (!bridgeCfg.startUrl || bridgeBusy) { return; }
		bridgeBusy = true;
		openSince = Date.now();
		if (bridgeBox) { bridgeBox.hidden = false; }
		setBridgeState('wait', bridgeLabels.starting || 'Starting…');
		// Do not clear an existing QR — status refresh will replace/keep it.
		postBridge(bridgeCfg.startUrl).then(function (data) {
			startBridgePoll();
			// Prefer immediate payload from start endpoint (status + qr).
			if (data && (data.status || data.qr)) {
				applyBridgePayload(data);
			}
			setTimeout(refreshBridgeStatus, 400);
			setTimeout(refreshBridgeStatus, 1500);
			setTimeout(refreshBridgeStatus, 3000);
			setTimeout(refreshBridgeStatus, 5500);
			if (data && data.success === false) {
				showError((data.result && data.result.error)
					? String(data.result.error)
					: (bridgeLabels.off || 'Bridge start failed'));
			}
		}).catch(function () {
			showError(bridgeLabels.off || 'Bridge start failed');
			startBridgePoll();
			refreshBridgeStatus();
		}).finally(function () { bridgeBusy = false; });
	}

	function bridgeFreshQr(silent) {
		if (!bridgeCfg.restartUrl || bridgeBusy) { return; }
		bridgeBusy = true;
		openSince = Date.now();
		if (bridgeBox) { bridgeBox.hidden = false; }
		setBridgeState('wait', bridgeLabels.restarting || 'Generating QR…');
		if (bridgeNoQr) { bridgeNoQr.hidden = false; }
		// Silent auto-retry: soft restart. Manual "Show QR again": clear session.
		var clear = silent ? '0' : '1';
		if (!silent) {
			showBridgeQr('');
		}
		// Poll immediately — restart returns before QR is ready.
		startBridgePoll();
		postBridge(bridgeCfg.restartUrl, { clear_session: clear }).then(function (data) {
			if (data) { applyBridgePayload(data); }
			refreshBridgeStatus();
			setTimeout(refreshBridgeStatus, 1500);
			setTimeout(refreshBridgeStatus, 4000);
		}).catch(function () {
			if (!silent) {
				showError(bridgeLabels.off || 'Could not restart bridge for QR.');
			}
			refreshBridgeStatus();
		}).finally(function () { bridgeBusy = false; });
	}

	function closeModal() {
		if (modal) { modal.hidden = true; }
		activeBtn = null;
		showError('');
		stopBridgePoll();
		if (bridgeBox) { bridgeBox.hidden = true; }
		showBridgeQr('');
	}

	function openModal(btn) {
		activeBtn = btn;
		var saved = (btn.getAttribute('data-saved-phone') || '').trim();
		var waPhone = (btn.getAttribute('data-wa-phone') || '').trim();
		var hasSaved = btn.getAttribute('data-has-phone') === '1' && (saved !== '' || waPhone !== '');
		if (savedDisplay) {
			savedDisplay.textContent = saved || (waPhone ? ('+' + waPhone) : '—');
		}
		if (savedRadio) {
			savedRadio.disabled = !hasSaved;
			savedRadio.checked = !!hasSaved;
		}
		if (otherRadio) {
			otherRadio.checked = !hasSaved;
		}
		if (phoneInput) {
			phoneInput.value = '';
			phoneInput.disabled = hasSaved;
		}
		showError('');
		if (modal) { modal.hidden = false; }
		syncChoice();
		openSince = Date.now();
		refreshBridgeStatus();
	}

	function chosenPhone() {
		if (savedRadio && savedRadio.checked && activeBtn) {
			return (activeBtn.getAttribute('data-wa-phone') || activeBtn.getAttribute('data-saved-phone') || '').trim();
		}
		return phoneInput ? phoneInput.value.trim() : '';
	}

	function sendPdf(btn, phone) {
		var trnno = btn.getAttribute('data-trnno');
		var endpoint = btn.getAttribute('data-endpoint');
		var originalText = btn.textContent;
		btn.disabled = true;
		btn.textContent = t('loading', 'Please wait...');
		if (sendBtn) {
			sendBtn.disabled = true;
			sendBtn.textContent = t('loading', 'Please wait...');
		}

		var body = new URLSearchParams();
		body.append('trnno', trnno);
		body.append('phone', phone);
		csrfAppend(body);

		fetch(endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
			body: body.toString(),
			credentials: 'same-origin'
		})
			.then(function (res) { return res.text(); })
			.then(function (raw) {
				var data;
				try { data = JSON.parse(raw); } catch (err) { throw new Error(t('whatsapp_failed', 'Could not send to WhatsApp.')); }
				return data;
			})
			.then(function (data) {
				if (!data || !data.success) {
					throw new Error((data && data.message) ? data.message : t('whatsapp_failed', 'Could not send to WhatsApp.'));
				}
				closeModal();
				if (data.sent) {
					alert(t('wa_pdf_sent', 'Order PDF WhatsApp par bhej diya.'));
				} else if (data.queued) {
					alert((data.message) ? data.message : t('wa_pdf_queued', 'PDF queue mein aa gaya — WhatsApp thori der mein bhej dega.'));
				} else {
					alert(t('wa_pdf_sent', 'Order PDF WhatsApp par bhej diya.'));
				}
			})
			.catch(function (err) {
				var msg = err.message || t('whatsapp_failed', 'Could not send to WhatsApp.');
				showError(msg);
				if (/bridge is not ready|not_ready|scan QR/i.test(msg)) {
					if (bridgeBox) { bridgeBox.hidden = false; }
					refreshBridgeStatus();
				}
				if (!modal || modal.hidden) {
					alert(msg);
				}
			})
			.finally(function () {
				btn.disabled = false;
				btn.textContent = originalText;
				if (sendBtn) {
					sendBtn.disabled = false;
					sendBtn.textContent = t('wa_send_pdf', 'Send PDF');
				}
			});
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.btn-order-whatsapp');
		if (!btn || btn.disabled) { return; }
		openModal(btn);
	});

	if (savedRadio) { savedRadio.addEventListener('change', syncChoice); }
	if (otherRadio) { otherRadio.addEventListener('change', syncChoice); }
	if (cancelBtn) { cancelBtn.addEventListener('click', closeModal); }
	if (modal) {
		modal.addEventListener('click', function (e) {
			if (e.target === modal) { closeModal(); }
		});
	}
	if (bridgeRefreshBtn) {
		bridgeRefreshBtn.addEventListener('click', function () { refreshBridgeStatus(); });
	}
	if (bridgeStartBtn) {
		bridgeStartBtn.addEventListener('click', function () { bridgeStart(); });
	}
	if (bridgeQrBtn) {
		bridgeQrBtn.addEventListener('click', function () { bridgeFreshQr(false); });
	}
	if (sendBtn) {
		sendBtn.addEventListener('click', function () {
			if (!activeBtn) { return; }
			var phone = chosenPhone();
			if (!phone) {
				showError(t('wa_enter_phone', 'WhatsApp number darj karein.'));
				return;
			}
			refreshBridgeStatus().then(function (ready) {
				// Queue even if bridge is down — cron retries when ready.
				if (!ready && bridgeBox) {
					bridgeBox.hidden = false;
				}
				sendPdf(activeBtn, phone);
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var autoBtn = document.querySelector('.btn-order-whatsapp[data-auto-prompt="1"]');
		if (!autoBtn) {
			return;
		}
		// Always open the chooser so staff can use saved number OR enter another.
		setTimeout(function () { openModal(autoBtn); }, 500);
	});
})();
</script>
</body>
</html>
<?php } ?>
<!-- end document-->
<?php if ($pdf_mode) { return; } ?>
