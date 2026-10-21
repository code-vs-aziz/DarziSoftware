<?php
 /*
 session_start();
error_reporting(0);
 
   $page_name = basename($_SERVER['PHP_SELF']);
 $UNAME = $_SESSION['UNAME'];
 
if($_SESSION['email'] == '' and $_SESSION['UNAME']  = '')
{
		
	header("location: index.php?err=101"); 
}	
else
{
		 echo $UNAME;
	$query_right= "SELECT * from rights WHERE UNAME = '$UNAME' AND   applink = '$page_name'   ";
	$result_right = mysql_query($query_right);
 
	if(mysql_num_rows($result_right) < 1)
	{	
		if($page_name != 'defualt.php') 
		{
		header("location: defualt.php?err=102"); 
		}
	}
}
*/
?> 
<!DOCTYPE html>
<html lang="<?php echo esc_html(app_html_lang()); ?>" dir="<?php echo app_is_rtl() ? 'rtl' : 'ltr'; ?>" class="<?php echo esc_html(app_lang_css_class()); ?>">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
    <meta name="description" content="au theme template">
    <meta name="author" content="Hau Nguyen">
    <meta name="keywords" content="au theme template">

    <!-- Title Page-->
    <title><?php echo esc_html(t('app_page_title')); ?></title>
	<link rel="icon" href="<?php echo base_url('images/favicon-32.png'); ?>?v=1" type="image/png" sizes="32x32">
	<link rel="icon" href="<?php echo base_url('images/favicon-16.png'); ?>?v=1" type="image/png" sizes="16x16">
	<link rel="shortcut icon" href="<?php echo base_url('favicon.ico'); ?>?v=1">
	<link rel="apple-touch-icon" href="<?php echo base_url('images/apple-touch-icon.png'); ?>?v=2" sizes="180x180">
	<meta name="theme-color" content="#0ea5e9">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="apple-mobile-web-app-title" content="DarziERP">
	<link rel="manifest" href="<?php echo base_url('manifest.webmanifest'); ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Noto+Sans+Arabic:wght@400;500;600;700&family=Noto+Naskh+Urdu:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php $erp_light_page = !empty($erp_light_page); ?>
<?php if (!$erp_light_page) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>/css/jquery.dataTables.css">
<?php } ?>
	<meta charset="utf-8">
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>js/jquery-3.3.1.js"></script>
<?php if (!$erp_light_page) { ?>
	<script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>/js/jquery.dataTables.js"></script>
<?php } ?>
    <!-- Fontfaces CSS-->
    <link href="<?php echo base_url(); ?>css/font-face.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/font-awesome-5/css/fontawesome-all.min.css?v=5154" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="<?php echo base_url(); ?>vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="<?php echo base_url(); ?>css/theme.css" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/dz-design-system.css?v=58" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/app-unified-theme.css?v=10" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/erp-form-enhancements.css?v=5" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/dz-responsive.css?v=6" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/dz-mobile-app.css?v=5" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/app-ajax-loader.css?v=2" rel="stylesheet" media="all">
    <link href="<?php echo base_url(); ?>css/offline-erp.css?v=1" rel="stylesheet" media="all">
<?php if (!$erp_light_page) { ?>
	<script type="text/javascript" src="<?php echo base_url(); ?>/js/select2.full.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>/js/cam.js"></script>
	<script src="<?php echo base_url(); ?>js/yauk.min.js"></script>
<?php } ?>
	<style>
		body.animsition,
		.animsition,
		.animsition.fade-in,
		.animsition.fade-out {
			opacity: 1 !important;
			animation: none !important;
			-webkit-animation: none !important;
		}
		.page-loader,
		.animsition-loading,
		.animsition-overlay-slide {
			display: none !important;
			opacity: 0 !important;
			pointer-events: none !important;
		}
	</style>
	<script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/popper.min.js"></script>
	<script src="<?php echo base_url(); ?>vendor/bootstrap-4.1/bootstrap.min.js"></script>
	<script src="<?php echo base_url(); ?>js/app-modal-fix.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/app-confirm.js?v=1"></script>
	<?php $this->load->view('partials/dz_confirm_modal'); ?>
<?php if (!$erp_light_page) { ?>
	<script>window.ORDER_LIFECYCLE_CFG = <?php echo json_encode(array(
		'historyUrl' => site_url('Order/order_lifecycle_history'),
		'modalTitle' => t('lifecycle_title'),
		'loadError' => t('lifecycle_load_error'),
		'loadingHtml' => '<p class="text-center text-muted py-3 mb-0">'.t('app_loading').'</p>',
	), JSON_UNESCAPED_UNICODE); ?>;</script>
	<script src="<?php echo base_url(); ?>js/order-lifecycle-modal.js?v=1"></script>
	<script src="<?php echo base_url(); ?>js/order-detail-datatable.js?v=2"></script>
	<script src="<?php echo base_url(); ?>js/master-list-datatable.js?v=1"></script>
	<script src="<?php echo base_url(); ?>js/customer-list-datatable.js?v=1"></script>
	<script src="<?php echo base_url(); ?>js/cash-entry.js?v=2"></script>
	<script src="<?php echo base_url(); ?>js/config-predefined.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/transfer-bulk-table.js?v=5"></script>
	<script src="<?php echo base_url(); ?>js/app-datatables-init.js?v=1"></script>
<?php } ?>
	<script src="<?php echo base_url(); ?>js/app-ajax-shell.js?v=25"></script>
	<script src="<?php echo base_url(); ?>js/dz-responsive-shell.js?v=1"></script>
	<script src="<?php echo base_url(); ?>js/app-notifications.js?v=1"></script>
	<script>window.APP_LANG = <?php echo json_encode(app_lang_js_strings(), JSON_UNESCAPED_UNICODE); ?>;</script>
	<script src="<?php echo base_url(); ?>js/bridge-status-indicator.js?v=2"></script>
	<?php if ($this->session->userdata('user')) { ?>
	<script>
	window.DARZI_OFFLINE_CFG = <?php echo json_encode(array(
		'enabled' => TRUE,
		'apiBase' => site_url('api/erp_sync'),
		'healthUrl' => site_url('api/erp_sync/health'),
		'swUrl' => base_url('sw.js'),
		'swScope' => base_url(),
		'homeUrl' => site_url('Welcome'),
		'loginUrl' => site_url('Login'),
		'offlineAppUrl' => base_url('offline-app.html'),
		'customerUrl' => site_url('Customer'),
		'measurementUrl' => site_url('Measurement'),
		'cashUrl' => site_url('Cash'),
		'deliveryUrl' => site_url('Order/order_issue'),
		'transferUrl' => site_url('Transfer'),
		'deviceLabel' => (string) $this->session->userdata('user'),
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo base_url(); ?>js/offline/db.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/connectivity.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/sync-engine.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/forms.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/desk-ops.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/ui.js?v=3"></script>
	<script src="<?php echo base_url(); ?>js/offline/bootstrap.js?v=3"></script>
	<?php } ?>
	<?php $this->load->view('partials/csrf_bootstrap'); ?>
	<?php $this->load->view('partials/notify_flash'); ?>
</head>