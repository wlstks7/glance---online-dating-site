<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');

/*//is the account created?
if ( $_SESSION["account_created"] != "TRUE" ){
  
	header('Location: ./');
	die;
}

*/

?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Password Reset</title>
	<link href="../fav.png" type="image/png" rel="shortcut icon" id="favicon">

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

	<script src='../assets/jquery.min.js?=11115'></script>
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/jquery.finger.min.js?=11115"></script>
	<script src="../assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/main.css">
	<link rel="stylesheet" href="style.css">

	<style type="text/css">

		body, html{
			margin: 0;
			padding: 0;
		  	background: #FFFFFF;
		}

		#loading {
			position: absolute;
			left: 0;
			top: 0;
			z-index: 10000000000;
		  	background: #1EA8FF;
		  	color: #FFFFFF;
		  	padding: 30px 0 0 50px;
		  	margin: 0;
		  	overflow: hidden;
		  	font-family: arial, helvetica !important;
		}

		#loading h1 {
		  	font-family: arial, helvetica !important;
		    font-size: 30px !important;
		    letter-spacing: -1px;
		    padding: 0 !important;
		    margin: 0 !important; 
		    line-height: 35px !important;
		}
		._segment_header_ {
  			font-family: "Roboto Thin" !important;
		    color: #458AC6;
		    margin: 5px 0 15px !important;
		    padding: 0 !important;
		    font-size: 36px !important;
		}

		._segment_header {
		    color: #888888;
		    font-family: "Roboto Thin" !important;
		    font-size: 33px !important;
		    line-height: 38px;
		    margin: 8px 0 20px !important;
		    padding: 0 !important;
		}

		#ion__logo {
			font-size: 60px;
			line-height: 60px;
			padding: 35px 0 10px;
			cursor: default;
		}
	</style>
	
</head>
<body class="pushable">

	<div id="loading" style="font-family: arial; font-weight: 100; background: #458AC6; width: 100%; height: 2000px;">
		<h1 style="font-family: arial; font-weight: 100;">Loading....</h1>
	</div>

	<div class="pusher">


        <!-- page: loading -->
		<div id="pageLoading" style="display:none;padding-top:20px;padding-bottom:20px;">
			<div class="ui grid header-top">
	            <div class="ui one wide column aligned left"></div>
	            <div class="ui fourteen wide column aligned left">
	            	<div class="ui icon message">
	            		<i class="notched circle loading icon"></i>
	            		<div class="content">
	            			<div class="header">
	            				Just a moment...
	            			</div>
	            			<p>Working on the request.</p>
	            		</div>
	            	</div>
	            </div>
            	<div class="ui one wide column aligned right"></div>
        	</div>
        </div>

		<!-- page: account -->
		<div id="pageAccountInfo" style="padding-bottom:20px;">
			<div class="ui grid header-top">
	            <div class="ui two wide column aligned left"></div>
	            <div class="ui twelve wide column aligned left">
	            	
	            	<div id="ion__logo">
		            	<?php include('../logo-inc.php'); ?>
	            	</div>

	            	<h2 class="_segment_header">Password was changed</h2>
	            	<p><a href="<?php echo $this_site; ?>">Click here to log in</a></p>
	            </div>
	        	<div class="ui two wide column aligned right"></div>
	    	</div>
	    </div>
	</div>

	<div id="http_base" data-id="<?php echo $http_base; ?>" style="display:none;"></div>
	<div id="this_site" data-id="<?php echo $this_site; ?>" style="display:none;"></div>


	<script type="text/javascript">

		var click = "click";

		$(function(){

			/*var isMobile = { 
				Android: function() { return navigator.userAgent.match(/Android/i); }, 
				BlackBerry: function() { return navigator.userAgent.match(/BlackBerry/i); }, 
				iOS: function() { return navigator.userAgent.match(/iPhone|iPad|iPod/i); }, 
				Opera: function() { return navigator.userAgent.match(/Opera Mini/i); }, 
				Windows: function() { return navigator.userAgent.match(/IEMobile/i); }, 
				any: function() { return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows()); } };
			
			if (isMobile.Android()) {

				click = "click";
			}*/

			var h 
			var w

			$(window).resize(function() {

				resizeWindow();
		    });
			
			resizeWindow();
			
			setTimeout(function(){

				$("#loading").fadeOut("slow");
				$("#username").select().focus();

			},500);

		    function resizeWindow(){

		    	h = $(window).height();
				w = $(window).width();

				$("#loading")
					.height(h)
					.width(w)
		    }
		});

	</script>
</body>
</html>