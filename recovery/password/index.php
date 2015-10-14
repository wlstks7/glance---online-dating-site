<?php  

require('../../session-inc.php');
require('../../def-inc.php');

//build new token for the new form
$_SESSION["form_token"] = md5("ion") . uniqid() . md5( uniqid() );

?>

<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Password Reset</title>
	<link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

	<script src='../../assets/jquery.min.js?=11115'></script>
	<script src="../../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../../assets/jquery.finger.min.js?=11115"></script>
	<script src="../../assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="../../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/main.css">
	<link rel="stylesheet" href="../../assets/glance.css">
	<style type="text/css">

		body, html{
			margin: 0;
			padding: 0;
		  	background: #FFFFFF;
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
		._segment_header {
		    color: #3F70A3;
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
		.clickable {
			cursor: pointer;
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

		<!-- page: invite -->
		<div id="pageAccountInfo" style="padding-bottom:20px;">
			<div class="ui grid header-top">
	            <div class="ui two wide column aligned left"></div>
	            <div class="ui twelve wide column aligned left">
	            	
	            	<div id="ion__logo">
		            	<?php include('../../logo-inc.php'); ?>
	            	</div>

	            	<h2 class="stepOne elements">Password Reset</h2>
					<p id="p_info" class="stepOne elements">To reset your password, please enter the email address associated with your account.</p>
					<div class="stepOne elements" style="height:6px;"></div>

					<!-- email -->
					<div class="ui form segment" style="display:nones;">
						
						<div class="field stepOne elements">
							<label>Email Address</label>
							<input type="text" placeholder="Your email address" id="email">
						</div>
						<div class="ui blue small button stepOne elements" id="btn_create_invite">Send Reset Email</div>

						<div id="stepTwo" class="field stepTwo elements">
							
							<h2 class="_segment_header stepTwo elements">Are You A Person?</h2>
							<p>Here's a fun little game that helps us be sure you are a <i>real live person</i> ;-)  Please click on the item below that is <strong style="color:red;">not</strong> food.</p>

							<div id="humanTest" class="ui tiny images">

							</div>
						</div>

						<h2 class="_segment_header stepThree elements">Thank You</h2>
						<div class="field stepThree elements">
							<p>Your reset email has been sent to: <span id="myEmail"></span></p>
						</div>
						
					</div>

					<div style="height:20px;"></div>
	            </div>
	        	<div class="ui two wide column aligned right"></div>
	    	</div>
	    </div>
	</div>

	<!-- are you sure? dialog -->
    <div id="affirm" class="ui modal">
        <div id="affirm_title" class="header">
            Are you sure?
        </div>
        <div class="content">
	    	<div class="ui medium image">
	        	<i class="help icon"></i>
	      	</div>
	      	<div class="description">
	        	<p id="affirm_question">Are you sure?</p>
	      	</div>
	    </div>
        <div class="actions">
            <div class="two fluid ui buttons">
                <div class="ui negative labeled icon button">
                    <i class="remove icon"></i>
                    No
                </div>
                <div class="ui positive right labeled icon button">
                    Yes
                    <i class="checkmark icon"></i>
                </div>
            </div>
        </div>
    </div>

	<!-- alert dialog -->
	<div id="alert" class="ui small modal">
		<i class="close icon"></i>
		<div id="alertTitle" class="header"></div>
		<div class="content">
			<div class="image">
				<i class="icon info circle"></i>
			</div>
			<div id="alertDescription" class="description"></div>
		</div>
		<div class="actions">
			<div class="ui positive right labeled icon button">
				Ok
				<i class="checkmark icon"></i>
			</div>
		</div>
	</div>

	<div id="form_token" data-id="<?php echo $_SESSION["form_token"]; ?>" style="display:none;"></div>
	<div id="http_base" data-id="<?php echo $http_base; ?>" style="display:none;"></div>
	<div id="this_site" data-id="<?php echo $this_site; ?>" style="display:none;"></div>

	<script type="text/javascript">

		var click = "click";

		$(function(){

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

		    $("#userName").val("").focus();
		});

	</script>

	<script src="../assets/invite.js?=<?php echo uniqid(); ?>"></script>
</body>
</html>