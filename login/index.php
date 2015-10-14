<?php  
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
/*

	LOGIN

*/
require_once('browser-detection.php');
require_once('../session-inc.php');
require_once('../def-inc.php');

//be sure this page is secure
/*if(!isset($_SERVER['HTTPS'])) {
   header('Location: ' . $this_site . "login/");
	die;
}*/

$browser = new BrowserDetection();
$userBrowserName = $browser->getBrowser();
$userBrowserVer = $browser->getVersion();

$browserFlag = true;

/*echo $userBrowserName . "<p>";
echo $userBrowserVer;
die;*/

//chrome
if ($userBrowserName == BrowserDetection::BROWSER_CHROME && $browser->compareVersions($userBrowserVer, '30.0') === 1) {
	$browserFlag = false;
}

//opera
if ($userBrowserName == BrowserDetection::BROWSER_OPERA && $browser->compareVersions($userBrowserVer, '20.0') === 1) {
	$browserFlag = false;
}

//ie
if ($userBrowserName == BrowserDetection::BROWSER_IE && $browser->compareVersions($userBrowserVer, '10.0') === 1) {
	$browserFlag = false;
}

//safari
/*if ($userBrowserName == BrowserDetection::BROWSER_SAFARI && $browser->compareVersions($userBrowserVer, '7.0.6') === 1) {
	$browserFlag = false;
}*/

//firefox
if ($userBrowserName == BrowserDetection::BROWSER_FIREFOX && $browser->compareVersions($userBrowserVer, '30.0') === 1) {
	$browserFlag = false;
}

if ($browserFlag == false) {
	header('Location: ' . $this_site . "browser/");
	die;	
}

/*opera 25
firefox 30
You are using Internet Explorer 10.0.
You are using Firefox 30.0.
You are using Chrome 30.0.1599.101.
You are using Opera 25.0.1614.50.
You are using Safari 7.0.6.
chrome 30*/

//build new token for the new form
$_SESSION["form_token"] = md5("ion") . uniqid() . md5( uniqid() );

?>
<html id="html"> 
<head>
	<title><?php echo APPNAME; ?> - Please Log In</title>

	<link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> -->
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/login.css">
	<link rel="stylesheet" href="../assets/glance.css">

	<style type="text/css">

		body, html {
		    margin: 0 !important;
		    padding: 0 !important;
		    width: 100%;
		}

		body {
		    background-color: #F5F8FA;
		}
		.image {
		    margin-top: -100px;
		}
		.column {
		    max-width: 450px;
		}

		#loading {
			position: fixed;
			left: 0;
    		height: 100%;
		    right: 0;
		    top: 0;
		    width: 100%;
			top: 0;
			z-index: 10000000000;
		  	background: #458AC6;
		  	color: #FFFFFF;
		  	padding: 30px 0 0 50px;
		  	margin: 0;
		  	overflow: hidden;
		  	font-family: arial, helvetica !important;
		}

		#loading h1 {
		    font-family: arial,helvetica !important;
		    font-size: 40px !important;
		    font-weight: normal;
		    letter-spacing: -2px;
		    line-height: 35px !important;
		    margin: 5px !important;
		    padding: 0 !important;
		}
		#pageLogin {
		    margin: 30px 0 0;
		    padding: 0 !important;
		    width: 100%;
		}
		#error_msg {
			font-size: 12px!important;
		}
	</style>
	
</head>
<body>

	<div id="loading" style="font-family: arial; font-weight: 100; background: #458AC6; width: 100%; height: 2000px;">
		<h1 style="font-family: arial; font-weight: 100;">Loading....</h1>
	</div>

	<!-- login page -->
	<div id="pageLogin" class="ui center aligned grid">
		<div class="column">
			<div id="logo">
            	<?php include('../logo-inc.php'); ?>
        	</div>
        	<div class="logo_msg">Please Login</div>
			<div style="height:12px;"></div>

			<form id="form_login" action="#" method="post" class="ui large form">
				<div class="ui segment">
					<div class="field">
						<div class="ui left icon input">
							<i class="user icon"></i>
							<input id="emailAddress" class="form_input" type="text" placeholder="E-mail address" name="emailAddress">
						</div>
					</div>
					<div class="field">
						<div class="ui left icon input">
							<i class="lock icon"></i>
							<input id="password" class="form_input" type="password" placeholder="Password" name="password">
						</div>
					</div>
					<div id="form_token" style="display:none;" data-id="<?php echo $_SESSION["form_token"]; ?>"></div>
					<input id="btn_login" type="submit" value="Login" class="ui fluid large blue submit button">
				</div>
				<div id="error_msg" class="ui error message"></div>
			</form>

			<p><a href="<?php echo SITEURL; ?>/recovery/password/">I forgot my password</a></p>
			<p><a href="<?php echo SITEURL; ?>/signup/">Sign Up</a></p>
		</div>
	</div>

	<div id="http_base" data-id="<?php echo $http_base; ?>" style="display:none;"></div>
	<div id="this_site" style="display:none;" data-id="<?php echo $this_site; ?>"></div>

	<script src='../assets/jquery.min.js?=11115'></script>
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/modernizr-2.6.2.min.js"></script>
	<script type="text/javascript">

		var click = "click";

		$(function(){

			$('html, body').animate({ scrollTop: 0 }, 0);

			var h 
			var w

			$(window).resize(function() {

				resizeWindow();
		    });
			
			resizeWindow();
			
			setTimeout(function(){

				$("#loading").fadeOut("slow");

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
	<script src="../assets/login.js?=<?php echo uniqid(); ?>"></script>

</body>
</html>