<?php  

header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require('../session-inc.php');
require("../json-inc.php");
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require("../log-inc.php");
require("../account-inc.php");
require('../func/passwordLib.php');

if($_SESSION["user_login_count"] > 10){

    $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'It appears that you are having trouble. This account is now temporarily locked. Please try again in a few hours. <br><br>Note: this login failure has been logged and reported.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//get the posted data
$token = trim($_POST['i']);
$emailAddress = trim(sanitize($_POST['e']));
$password = trim($_POST['p']);

//check to see if form token exists
if ( !isset( $_SESSION["form_token"] ) || $token != $_SESSION["form_token"]){
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please refresh your browser and try login again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

$id = sanitize($_POST["id"]);

if ($token == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please pass a valid token', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

if ($emailAddress == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please pass a valid email address', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

if ($password == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please pass a valid password', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//check for the attempt count and increment
if ( !isset( $_SESSION["user_login_count"] )){
    
    //not set... init
  $_SESSION["user_login_count"] = 0;
}

//increment attempt
$_SESSION["user_login_count"]++;

//what to do with excessive login attempts
if($_SESSION["user_login_count"] > 6){

    if ($_SESSION["user_login_count"] < 8){
            logThis("Failed Login Max - This user has tried to log in too many times" . " - User logging in [userName: " . $emailAddress . " password: " . $password . "]", "", "Failed Login Max");


    } else if ($_SESSION["user_login_count"] > 8){
            logThis("Failed Login Max - This user has tried to log in too many times" . " - User logging in [userName: " . $emailAddress . " password: " . $password . "]", "", "Failed Login Max");
            emailAdmin("Failed Login Max - This user has tried to log in too many times" . " - User logging in [userName: " . $emailAddress . "]");
    }

    $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'It appears that you are having trouble. This account is now temporarily locked. Please try again in a few hours.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//get password from account using email/username
$passwordHashed = checkCredientials($emailAddress);

$ret = validate_password($password, $passwordHashed);

if ($ret === false) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => "I'm sorry that wasn't correct. Please try again.", 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//THIS USER HAS BEEN AUTHENTICATED

$_parameterArray = array(
	':emailAddress' => $emailAddress
);

$_query = <<<EOT
	
	SELECT 
		profile_id,
		userName,
		emailAddress,
		zipcode,
		latitude,
		longitude,
		city,
		state,
		birthMonth,
		birthDay,
		birthYear,
		birthDate,
		zodiac,
		zodiacShow,
		firstName,
		relationshipStatus,
		gender,
		seekingGender,
		height,
		eyeDesc,
		bodyType,
		hairDesc,
		religious,
		ethnicity,
		income,
		smokerPref,
		drinkingPref,
		children,
		adultProfileRating,
		adultViewPref,
		profileDesc,
		profileImage,
		profileBannerImage,
		pointsInt,
		profileVisible,
      	privateURL
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

	if (count($results) == 0) {
		
		$results = "";

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'false', 
			'msg' => "I'm having trouble locating your account. Please contact support if this continues.", 
			'results' => $results
		);

		$_SESSION['ion_user_authenticated'] = '';	
		$_SESSION['ion_user_authenticated'] = '';
		$_SESSION['ion_cookie_timestamp'] = '';

		unset( $_SESSION["ion_user_authenticated"]);
		unset( $_SESSION["ion_cookie_timestamp"]);
		unset( $_SESSION["loggedUser"]);

		respond($response);

		$msg = "Login fail - Module: func/login.php - I cannot find the account for [userName: " . $emailAddress . "] [emailaddress: " . $emailAddress . "]";
		
		logThis($msg, $profile_id, "New Account Fail");
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);

		die;

	} else {

		//reset any sessions
		$_SESSION["ion_user_authenticated"] = "";
		$_SESSION["ion_cookie_timestamp"] = "";
		$_SESSION["loggedUser"] = array();

		unset( $_SESSION["loggedUser"]);
		unset( $_SESSION["ion_user_authenticated"]);
		unset( $_SESSION["ion_cookie_timestamp"]);

		$_SESSION = array();

		//update this users profile age
		$results[0]["age"]= updateProfileAge($results[0]["birthDate"], $results[0]["profile_id"]);

		$loggedUser = $results[0];

		//build the array for the logged in user
		$_SESSION["loggedUser"] = $loggedUser;
		
		//remove any old activity sessions
		try {
			unset( $_SESSION["polling_activity_" . $loggedUser["profile_id"]]);
			unset( $_SESSION["polling_latest_activity_array" . $loggedUser["profile_id"]]);
		} catch (Exception $e) {}

		$_SESSION['ion_user_authenticated'] = 'true';

		//create the inital timestamp
		$ion_cookie_timestamp_now = date_create();
		$_SESSION['ion_cookie_timestamp'] =  date_timestamp_get($ion_cookie_timestamp_now);

		//reset counter
		$_SESSION["user_login_count"] = 0;
		unset( $_SESSION["form_token"]);

		logThis("User Login - User logging in [userName: " . $emailAddress . "]", $loggedUser["profile_id"], "Login");
	}

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => count($results), 
		'results' => $results
	);

}
catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Error 100220. Please seek support.', 
		'results' => 'none'
	);

	$msg = "Login fail - Module: func/login.php - Had an issue with the DB throwing an exception - while attempting to login user: [userName: " . $emailAddress . "] [emailaddress: " . $emailAddress . "]";
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

/*

FUNCTIONS

*/

function checkCredientials($emailAddress){

	$_parameterArray = array(
		//':userName' => $emailAddress,
		':emailAddress' => $emailAddress
	);

$_query = <<<EOT
	
	SELECT 
		password
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

		if (count($results) == 0) {
			
			//there is no account with that email/user combo
			$response = array(
				'api' => apiName, 
				'version' => apiVersion, 
				'status' => 'fail', 
				'error' => 'true', 
				'msg' => "I'm sorry that wasn't correct. Please try again.", 
				'results' => 'none'
			);
			
			respond($response);

			die;

		} else {

			return $results[0]["password"];
		}
	}
	catch(PDOException $e) {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => 'Error 1002202. Please seek support.', 
			'results' => 'none'
		);
	}
}

function emailAdmin($msg){

	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

?>