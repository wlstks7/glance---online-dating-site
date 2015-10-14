<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');

//is the account created?
if ( $_SESSION["account_created"] == "TRUE" ){
  
	header('Location: ' . $this_signup . 'account-created.php');
	die;
}

//get the token
$seed_token = $_GET["q"];

//check to see if the token has expired
//check to see if the username/email address is taken
$tokenStatus = getTokenStatus($seed_token);

// echo $seed_token;
// print_r($tokenStatus);
// die;

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
	
	header('Location: ' . $this_signup . 'expired-invitation.php');
	die;
}

//if so...send to that page
if ( $tokenStatus["errorcode"] == "exists" ) {
	
	header('Location: ' . $this_signup . 'username-not-available.php');
	die;
}

//build new token for the new form
$_SESSION["form_token_accountsetup"] = md5("ion") . uniqid() . md5( uniqid() );

//this is the users email
$_SESSION["email_address"] = strtolower($tokenStatus["email"]);

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
	  invitations 
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
	    	$doesEmailExist = checkForAccount($emailAddress);

	    	//check for DB error
	    	if ( $doesEmailExist == -1) {
		    	$response = array(
					'expired' => 'no', 
					'email' => 'none', 
					'error' => 'yes',
					'errorcode' => 'system',
					'msg' => 'We are having a little trouble finding that. Please try again.'
				);
	    	}

	    	//check to see if there is another account using this email
	    	else if ( $doesEmailExist > 0 ) {

		    	$response = array(
					'expired' => 'no', 
					'email' => 'none', 
					'error' => 'yes',
					'errorcode' => 'exists',
					'msg' => "I'm sorry, that email address is being used by another account. Please try a different email address."
				);

	    	} else {

	    		$response = array(
					'expired' => 'no', 
					'email' => $emailAddress, 
					'error' => 'no',
					'errorcode' => 'none',
					'msg' => "success"
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
		':username' => $emailAddress
	);

	$_query = <<<EOT

	SELECT 
	  COUNT(username) as howManyAccounts
	FROM 
	  customers 
	WHERE 
		username = :username

EOT;

	try {
		$db = new PDO(conn . dbName, dbUser, dbPass);
		$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
		$stmt->execute($_parameterArray);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$howManyAccounts = $results[0]["howManyAccounts"];
		
		return $howManyAccounts;
	
	} catch(PDOException $e) {

		return -1;
	}
}

?>
<html id="html">
<head>
	<title>Welcome to <?php echo APPNAME; ?>!</title>
	<link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

	<script src='assets/jquery.min.js?=11115'></script>
	<script src="assets/semantic/semantic.min.js?=11115"></script>
	<script src="assets/jquery.finger.min.js?=11115"></script>
	<script src="assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="assets/main.css">
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
		._segment_header_ {
  			font-family: "Roboto Thin" !important;
		    color: #3f70a3;
		    margin: 5px 0 15px !important;
		    padding: 0 !important;
		    font-size: 36px !important;
		}

		._segment_header {
		    color: #3f70a3;
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

	            	<h2>Sign Up</h2>
					<p id="p_info">To get started, please choose a password.</p>
					<!-- <p id="p_info">To get started, please enter your email address. We will send a signup invitation to you.</p> -->
					<div style="height:6px;"></div>


					<!-- user/password -->
					<div id="page_login" class="ui form segment">
						<h2 class="_segment_header">My Password</h2>
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
						<div class="ui blue small button" id="btn_create_login">Create Login</div>
					</div>

					
					<!-- account -->
					<div id="page_account" class="ui form segment" style="display:none;">
						<h2 class="_segment_header">About You</h2>
						<div class="two fields">
							<div class="field">
								<label>User Name</label>
								<input type="text" placeholder="Example: ashtastic<?php echo date("Y"); ?>" id="userName">
							</div>
							<div class="field">
								<label>First Name</label>
								<input type="text" placeholder="First Name" id="firstName">
							</div>
						</div>
						<div class="ui simple divider"></div>
						<div class="two fields">
							<div class="field">
								<label>Zip Code</label>
								<input type="text" placeholder="Your Zip Code" id="zipcode">
							</div>
							<div class="field"></div>
						</div>
						<div class="ui simple divider"></div>
						<div class="field">
							<label>Birthday</label>
							<div class="three fields">
								<div class="field">
									<div class="ui selection dropdown">
										<input id="birthMonth" name="birthMonth" type="hidden">
										<div class="default text">Month</div>
										<i class="dropdown icon"></i>
										<div class="menu">
											<div class="item" data-value="1">January</div>
											<div class="item" data-value="2">February</div>
											<div class="item" data-value="3">March</div>
											<div class="item" data-value="4">April</div>
											<div class="item" data-value="5">May</div>
											<div class="item" data-value="6">June</div>
											<div class="item" data-value="7">July</div>
											<div class="item" data-value="8">August</div>
											<div class="item" data-value="9">September</div>
											<div class="item" data-value="10">October</div>
											<div class="item" data-value="11">November</div>
											<div class="item" data-value="12">December</div>
										</div>
									</div>
								</div>
								<div class="field">
									<div class="ui selection dropdown">
										<input id="birthDay" name="birthDay" type="hidden">
										<div class="default text">Day</div>
										<i class="dropdown icon"></i>
										<div class="menu">
											<?php  

												for ($i=1; $i < 32 ; $i++) { 
													
													echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';

												}
											?>
										</div>
									</div>
								</div>
								<div class="field">
									<div class="ui selection dropdown">
										<input id="birthYear" name="birthYear" type="hidden">
										<div class="default text">Year</div>
										<i class="dropdown icon"></i>
										<div class="menu">
											<?php  

												$y = date("Y");
												$y = intval($y);
												$max_bday = $y - 17;
												$min_bday = $y - 90;

												for ($i=$min_bday; $i < $max_bday ; $i++) { 
													
													echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';

												}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ui simple divider"></div>
						<div class="three fields">
							<div class="field">
								<label>Relationship Status</label>
								<div class="ui selection dropdown">
									<input id="relationshipStatus" name="relationshipStatus" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="1">Single</div>
										<div class="item" data-value="2">Single and taking a break from dating</div>
										<div class="item" data-value="3">In a relationship</div>
										<div class="item" data-value="4">It's complicated</div>
										<div class="item" data-value="5">Here for friends only</div>
										<div class="item" data-value="6">I'm in love</div>
										<div class="item" data-value="7">No longer available</div>
										<div class="item" data-value="8">Married</div>
										<div class="item" data-value="9">Separated</div>
										<div class="item" data-value="10">In an open relationship</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>My Gender</label>
								<div class="ui selection dropdown">
									<input id="gender" name="gender" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="guy">Guy</div>
										<div class="item" data-value="gal">Gal</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>I'm Looking For</label>
								<div class="ui selection dropdown">
									<input id="seekingGender" name="seekingGender" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="guy">Guy</div>
										<div class="item" data-value="gal">Gal</div>
										<div class="item" data-value="guyGal">Guys and Gals</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ui simple divider"></div>
						<div class="three fields">
							<div class="field">
								<label>Height</label>
								<div class="ui selection dropdown">
									<input id="height" name="height" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="1">Less than 5'</div>
										<div class="item" data-value="2">5'0"</div>
										<div class="item" data-value="3">5'1"</div>
										<div class="item" data-value="4">5'2"</div>
										<div class="item" data-value="5">5'3"</div>
										<div class="item" data-value="6">5'4"</div>
										<div class="item" data-value="7">5'5"</div>
										<div class="item" data-value="8">5'6"</div>
										<div class="item" data-value="9">5'7"</div>
										<div class="item" data-value="10">5'8"</div>
										<div class="item" data-value="11">5'9"</div>
										<div class="item" data-value="12">5'10"</div>
										<div class="item" data-value="13">5'11"</div>
										<div class="item" data-value="14">6'0"</div>
										<div class="item" data-value="15">6'1"</div>
										<div class="item" data-value="16">6'2"</div>
										<div class="item" data-value="17">6'3"</div>
										<div class="item" data-value="18">6'4"</div>
										<div class="item" data-value="19">6'5"</div>
										<div class="item" data-value="20">6'6"</div>
										<div class="item" data-value="21">6'7"</div>
										<div class="item" data-value="22">6'8"</div>
										<div class="item" data-value="23">6'9"</div>
										<div class="item" data-value="24">6'10"</div>
										<div class="item" data-value="25">6'11"</div>
										<div class="item" data-value="26">7'0"</div>
										<div class="item" data-value="27">7'1"</div>
										<div class="item" data-value="28">7'2"</div>
										<div class="item" data-value="29">7'3"</div>
										<div class="item" data-value="30">7'4"</div>
										<div class="item" data-value="31">7'5"</div>
										<div class="item" data-value="32">7'6"</div>
										<div class="item" data-value="33">7'7"</div>
										<div class="item" data-value="34">7'8"</div>
										<div class="item" data-value="35">Really tall - more than 7'8"</div>

									</div>
								</div>
							</div>
							<div class="field">
								<label>Eye Color</label>
								<div class="ui selection dropdown">
									<input id="eyeDesc" name="eyeDesc" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="blue">Blue</div>
										<div class="item" data-value="brown">Brown</div>
										<div class="item" data-value="gray">Gray</div>
										<div class="item" data-value="green">Green</div>
										<div class="item" data-value="hazel">Hazel</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>Body Type</label>
								<div class="ui selection dropdown">
									<input id="bodyType" name="bodyType" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="athletic">Athletic</div>
										<div class="item" data-value="average">Average</div>
										<div class="item" data-value="beerGut">Beer gut</div>
										<div class="item" data-value="bigStrong">Big but really strong</div>
										<div class="item" data-value="curvy">Curvy in all the right places</div>
										<div class="item" data-value="fatHappy">Fat and happy</div>
										<div class="item" data-value="funSize">Fun size</div>
										<div class="item" data-value="healthyFit">Healthy and fit</div>
										<div class="item" data-value="someAbs">I can see some of my abs</div>
										<div class="item" data-value="jacked">Jacked</div>
										<div class="item" data-value="longLean">Long and lean</div>
										<div class="item" data-value="overweightWorking">Overweight but I'm working on it</div>
										<div class="item" data-value="sixPack">Six pack abs</div>
										<div class="item" data-value="slightlyOverweight">Slightly overweight but that's ok</div>
										<div class="item" data-value="stocky">Stocky</div>
										<div class="item" data-value="thin">Thin</div>
										<div class="item" data-value="voluptuous">Voluptuous</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
						</div>
						<div class="two fields">
							<div class="field">
								<label>Hair</label>
								<div class="ui selection dropdown">
									<input id="hairDesc" name="hairDesc" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="auburn">Auburn</div>
										<div class="item" data-value="balding">Balding</div>
										<div class="item" data-value="black">Black</div>
										<div class="item" data-value="blond">Blond</div>
										<div class="item" data-value="brown">Brown</div>
										<div class="item" data-value="brunette">Brunette</div>
										<div class="item" data-value="ginger">Ginger</div>
										<div class="item" data-value="fireyRed">Firey Red</div>
										<div class="item" data-value="full">Full and Lush</div>
										<div class="item" data-value="mohawk">Mohawk</div>
										<div class="item" data-value="multi">Multi-color</div>
										<div class="item" data-value="saltPepper">Salt and Pepper</div>
										<div class="item" data-value="sandy">Sandy</div>
										<div class="item" data-value="shaved">Shaved</div>
										<div class="item" data-value="silver">Silver Fox</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>Ethnicity</label>
								<div class="ui selection dropdown">
									<input id="ethnicity" name="ethnicity" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="asian">Asian</div>
										<div class="item" data-value="black">Black</div>
										<div class="item" data-value="indian">Indian</div>
										<div class="item" data-value="latino">Latino/Hispanic</div>
										<div class="item" data-value="middleEast">Middle Eastern</div>
										<div class="item" data-value="mixed">Mixed Race</div>
										<div class="item" data-value="native">Native American</div>
										<div class="item" data-value="other">Other</div>
										<div class="item" data-value="pacificIslander">Pacific Islander</div>
										<div class="item" data-value="white">White</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ui simple divider"></div>
						<div class="three fields">
							<div class="field">
								<label>Faith</label>
								<div class="ui selection dropdown">
									<input id="religious" name="religious" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="agnostic">Agnostic</div>
										<div class="item" data-value="atheist">Atheist</div>
										<div class="item" data-value="buddhist">Buddhist</div>
										<div class="item" data-value="catholic">Catholic</div>
										<div class="item" data-value="christian">Christian</div>
										<div class="item" data-value="hindu">Hindu</div>
										<div class="item" data-value="jewish">Jewish</div>
										<div class="item" data-value="lds">LDS</div>
										<div class="item" data-value="muslim">Muslim</div>
										<div class="item" data-value="notReligious">Not religious</div>
										<div class="item" data-value="other">Other</div>
										<div class="item" data-value="spiritual">Spiritual but not religious</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>Children</label>
								<div class="ui selection dropdown">
									<input id="children" name="children" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										
										<div class="item" data-value="nope">No, I don't have kids</div>
										<div class="item" data-value="noNo">No and I don't want any</div>
										<div class="item" data-value="NoYes">No and I want some</div>
										<div class="item" data-value="NoOk">No and it's ok if you have kids</div>
										<div class="item" data-value="yes">Yes, I have kids</div>
										<div class="item" data-value="yesNo">Yes and I don't want more</div>
										<div class="item" data-value="yesMore">Yes and I want more</div>
										<div class="item" data-value="yesOk">Yes and it's ok if you have kids</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
							<div class="field">
								<label>Income</label>
								<div class="ui selection dropdown">
									<input id="income" name="income" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="25">Less than $25,000</div>
										<div class="item" data-value="2540">$25,000 to $40,000</div>
										<div class="item" data-value="4060">$40,000 to $60,000</div>
										<div class="item" data-value="6080">$60,000 to $80,000</div>
										<div class="item" data-value="80100">$80,000 to $100,000</div>
										<div class="item" data-value="100more">More than $100,000</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
										
									</div>
								</div>
							</div>
						</div>
						<div class="ui simple divider"></div>
						<div class="two fields">
							<div class="field">
								<label>Smoking</label>
								<div class="ui selection dropdown">
									<input id="smokerPref" name="smokerPref" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										<div class="item" data-value="cigars">Cigars are cool</div>
										<div class="item" data-value="420">420 occasionally</div>
										<div class="item" data-value="420Nothing">420 friendly but nothing else</div>
										<div class="item" data-value="noWay">No Way!</div>
										<div class="item" data-value="noNo">No and I prefer if you didn't</div>
										<div class="item" data-value="noYes">No but you can</div>
										<div class="item" data-value="yesAllTime">Yes! All the time</div>
										<div class="item" data-value="yesQuitting">Yes but I'm trying to quit</div>
										<div class="item" data-value="yesDiscreetly">Yes, discreetly</div>
										<div class="item" data-value="yesWhile">Yes, once in a while</div>
										<div class="item" data-value="yesDrink">Yes, only when I drink</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div> 
							<div class="field">
								<label>Drinking</label>
								<div class="ui selection dropdown">
									<input id="drinkingPref" name="drinkingPref" type="hidden">
									<div class="default text">Please Choose</div>
									<i class="dropdown icon"></i>
									<div class="menu">
										
										<div class="item" data-value="no">No, I don't drink.</div>
										<div class="item" data-value="noOk">No, but it's ok if you do.</div>
										<div class="item" data-value="noNo">No, and I'd rather not be around it.</div>
										<div class="item" data-value="yesPlease">Yes please!</div>
										<div class="item" data-value="yesSocially">Yes, socially</div>
										<div class="item" data-value="yesWeekend">Yes, just on the weekends</div>
										<div class="item" data-value="yesEveryday">Yes, everyday</div>
										<div class="item" data-value="yesBeerPong">Yes! Beer pong anybody?</div>
										<div class="item" data-value="nothing">Prefer not to say</div>
									</div>
								</div>
							</div>
						</div>
						<div class="field">
							<div class="ui blue fluid button" id="btn_create_account">Create My Account</div>
						</div>
					</div>
					<div style="height:130px"></div>
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

	<div id="form_token" data-id="<?php echo $_SESSION["form_token_accountsetup"]; ?>"></div>

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