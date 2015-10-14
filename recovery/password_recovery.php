<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');

//get the token
$seed_token = $_GET["q"];

//check to see if the token has expired
//check to see if the username/email address is taken
$tokenStatus = getTokenStatus($seed_token);

/*echo $seed_token;
print_r($tokenStatus);
die;*/

/*

Array
(
    [expired] => no
    [email] => jburgess@esynaptic.com
    [error] => no
    [errorcode] => none
    [msg] => success
)

*/

//if so...send to that page
if ( $tokenStatus["errorcode"] == "expired" ) {
	
	header('Location: ' . $this_site . 'recovery/password_reset.php');
	die;
}

//if so...send to that page
if ( $tokenStatus["errorcode"] == "not exist" ) {
	
	header('Location: ' . $this_site . 'recovery/error.php');
	die;
}

//build new token for the new form
$_SESSION["form_token_recovery"] = md5("ion") . uniqid() . md5( uniqid() );

//this is the users email
$_SESSION["email_address"] = strtolower($tokenStatus["email"]);
$_SESSION["profile_id"] = $tokenStatus["profile_id"];

/*

*********************
*********************
*********************
*********************
FUNCTIONS
*********************
*********************
*********************
*********************
*/

function getTokenStatus($seed_token){

	$timestamp = time();

	//is there non expired, instance of this token?
	$_parameterArray = array(
		':seed' => $seed_token
	);

	$_query = <<<EOT

	SELECT 
	  email_address, expire_int
	FROM 
	  password_reset 
	WHERE 
		seed = :seed
	limit 1

EOT;

	try {

	    $db = new PDO(conn . dbName, dbUser, dbPass);
	    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
	    $stmt->execute($_parameterArray);
	    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	    $emailAddress = $results[0]["email_address"];
	    $expire_int = $results[0]["expire_int"];
	    
	    if ( is_numeric($expire_int) ) {
	    	$expire_int = intval($expire_int);	
	    } else {
	    	$expire_int = 0;
	    }

	    //is this expired?
	    if ( $timestamp > $expire_int ) {
	    	
	    	//expired
	    	$response = array(
				'expired' => 'yes', 
				'email' => 'none', 
				'error' => 'yes',
				'errorcode' => 'expired',
				'msg' => "I'm sorry, but this invitation has expired. Please try again."
			);

			return $response;

	    } else {

	    	//not expired... continue

	    	//is there a user with this email in the system?
	    	$profile_id = checkForAccount($emailAddress);

	    	if ( $profile_id != "0" ) {

		    	$response = array(
					'expired' => 'no', 
					'email' => $emailAddress, 
					'profile_id' => $profile_id, 
					'error' => 'no',
					'errorcode' => 'exists',
					'msg' => "success"
				);

	    	} else {

	    		$response = array(
					'expired' => 'no', 
					'profile_id' => '', 
					'email' => $emailAddress, 
					'error' => 'yes',
					'errorcode' => 'not exist',
					'msg' => "I cannot find an account with that email address."
				);
	    	}

	    	//print_r($response) . "<p>";

	    	return $response;
	    }
	}
	catch(PDOException $e) {

		return -1;
	}
}

/*
****************
****************
****************
****************
*/

function checkForAccount($emailAddress){

	$_parameterArray = array(
		':emailAddress' => $emailAddress
	);

	$_query = <<<EOT

	SELECT 
	  profile_id
	FROM 
	  profile 
	WHERE 
		emailAddress = :emailAddress
	limit 1

EOT;

	try {
		$db = new PDO(conn . dbName, dbUser, dbPass);
		$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
		$stmt->execute($_parameterArray);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (count($results) != 0) {

			$profile_id = $results[0]["profile_id"];

		} else {

			$profile_id = "0";
		}
		
		return $profile_id;
	
	} catch(PDOException $e) {

		return "0";
	}
}

?><html id="html">
<head>
	<title><?php echo APPNAME; ?> - Password Recovery</title>
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
		  	background: #458AC6;
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

	            	<h2>Password Reset</h2>
					<p id="p_info">Please choose another password.</p>
					<!-- <p id="p_info">To get started, please enter your email address. We will send a signup invitation to you.</p> -->
					<div style="height:6px;"></div>


					<!-- user/password -->
					<div id="page_login" class="ui form segment">
						<div class="field">
							<label>Password</label>
							<input type="password" data-content="Password should be at least 6 characters." data-position="bottom center" placeholder="Choose a password" id="password">
						</div>
						<div class="field">
							<div id="togglePassword" class="ui tpasswd toggle checkbox">
								<input type="checkbox">
								<label>Show Password</label>
							</div>
						</div>
						<div class="ui blue small button" id="btn_create_login">Reset Password</div>
					</div>
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

	<div id="form_token" data-id="<?php echo $_SESSION["form_token_recovery"]; ?>"></div>

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

		    $("#userName").val("").focus();
		});

	</script>
	<script src="assets/signup.js?=<?php echo uniqid(); ?>"></script>
</body>
</html>