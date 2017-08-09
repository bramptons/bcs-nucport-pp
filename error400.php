<?php
require_once("initialise.inc");
?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="UTF-8">    
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<title>MyPortal: 400 Bad Request</title>
		<meta name="description" content="My Portal">
		<meta name="author" content="Guido Gybels">
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

		<link rel="icon" href="/img/favicon.ico">
		<link rel="apple-touch-icon" href="/img/icon57.png" sizes="57x57">
		<link rel="apple-touch-icon" href="/img/icon72.png" sizes="72x72">
		<link rel="apple-touch-icon" href="/img/icon76.png" sizes="76x76">
		<link rel="apple-touch-icon" href="/img/icon114.png" sizes="114x114">
		<link rel="apple-touch-icon" href="/img/icon120.png" sizes="120x120">
		<link rel="apple-touch-icon" href="/img/icon144.png" sizes="144x144">
		<link rel="apple-touch-icon" href="/img/icon152.png" sizes="152x152">

		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/plugins.css">
		<link rel="stylesheet" href="/css/main.css">
		<link rel="stylesheet" href="/css/themes.css">
		<link rel="stylesheet" href="/css/<?php echo $SYSTEM_SETTINGS["General"]["StyleSheet"]; ?>">

		<script src="/js/vendor/modernizr-2.7.1-respond-1.4.2.min.js"></script>
		<script src="/js/moment.min.js"></script>

<?php if(file_exists(CONSTLocalStorageRoot."googleanalytics.txt")){ echo file_get_contents(CONSTLocalStorageRoot."googleanalytics.txt"); } ?>
<?php if(file_exists(CONSTLocalStorageRoot."googletagmanager.txt")){ echo file_get_contents(CONSTLocalStorageRoot."googletagmanager.txt"); } ?>
	</head>
	<body>
		<!-- Error Container -->
		<div id="error-container">
			<div class="error-options">
				<h3><i class="fa fa-chevron-circle-left text-muted"></i> <a href="javascript:void(0)" onclick="history.back();">Go Back</a></h3>
			</div>
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2 text-center">


                    <h1><i class="fa fa-exclamation-triangle text-info animation-pulse"></i> 400</h1>
                    <h2 class="h3">Oops, we are sorry but there was an error processing your request. Please contact the system administrator..</h2>


				</div>
			</div>
		</div><!-- END Error Container -->
        <!-- Modal Terms -->
<?php TermsAndConditions(2); ?>
        <!-- END Modal Terms -->

	</body>
</html>
<?php ob_end_flush(); ?>