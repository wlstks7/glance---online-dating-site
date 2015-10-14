<?php  

require('../../session-inc.php');
require('../../data-inc.php');
require('../../def-inc.php');
require('../../mailgun-inc.php');
require('../../log-inc.php');
require('../../account-inc.php');
require('../../func/passwordLib.php');

$token = trim($_POST['i']);
 
$emailAddress = sanitize($_SESSION["email_address"]);

$userName = trim(sanitize($_POST['userName']));
$password = trim($_POST['password']);

$zipcode = sanitize($_POST["zipcode"]);

$birthMonth = sanitize($_POST["birthMonth"]);
$birthDay = sanitize($_POST["birthDay"]);
$birthYear = sanitize($_POST["birthYear"]);

$firstName = sanitize($_POST["firstName"]);
$relationshipStatus = sanitize($_POST["relationshipStatus"]);
$gender = sanitize($_POST["gender"]);
$seekingGender = sanitize($_POST["seekingGender"]);
$height = sanitize($_POST["height"]);
$eyeDesc = sanitize($_POST["eyeDesc"]);
$bodyType = sanitize($_POST["bodyType"]);
$hairDesc = sanitize($_POST["hairDesc"]);
$religious = sanitize($_POST["religious"]);
$ethnicity = sanitize($_POST["ethnicity"]);
$income = sanitize($_POST["income"]);
$smokerPref = sanitize($_POST["smokerPref"]);
$drinkingPref = sanitize($_POST["drinkingPref"]);
$children = sanitize($_POST["children"]);

//$profileDesc = sanitize($_POST["profileDesc"]);
//$profession = sanitize($_POST["profession"]);

//check to see if form token exists
if ( !isset( $_SESSION["form_token_accountsetup"] ) || $token != $_SESSION["form_token_accountsetup"]){
  
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please refresh your browser and try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//check username for use
$ret = checkUserName($userName);

	if ($ret != "0") {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $ret, 
			'results' => 'none'
		);
		
		respond($response);

		die;
	}

//check password for use
$ret = checkPassword($password);

	if ($ret != "0") {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $ret, 
			'results' => 'none'
		);
		
		respond($response);

		die;
	}

//check for empty email
if ($emailAddress == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => "All fields need to be completed and the email address cannot be an empty value. Please check this and try again.", 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

$ret = validateEmail($emailAddress);

	//check for email address
	if ( $ret != "0" ) {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $ret, 
			'results' => 'none'
		);
		
		respond($response);

		die;

	}

checkStringLength($userName, "User Name", 25);
checkStringLength($emailAddress, "Email Address", 99);
checkStringLength($zipcode, "Zipcode", 20);
checkStringLength($firstName, "First Name", 15);
checkStringLength($relationshipStatus, "Relationship Status", 99);
checkStringLength($gender, "Gender", 99);
checkStringLength($seekingGender, "Gender I'm Seeking", 99);
checkStringLength($height, "Height", 49);
checkStringLength($eyeDesc, "Eye Color", 49);
checkStringLength($bodyType, "Body Type", 99);
checkStringLength($hairDesc, "Hair", 99);
checkStringLength($religious, "Faith", 99);
checkStringLength($ethnicity, "Ethnicity", 99);
checkStringLength($income, "Income", 99);
checkStringLength($smokerPref, "Smoking Preference", 99);
checkStringLength($drinkingPref, "Drinking Preference", 99);
checkStringLength($children, "Children", 99);

validateInputData($relationshipStatus, "Relationship Status", $define_relationshipStatus);
validateInputData($gender, "Gender", $define_gender);
validateInputData($seekingGender, "Gender I'm Seeking", $define_seekingGender);
validateInputData($height, "Height", $define_height);
validateInputData($eyeDesc, "Eye Color", $define_eyes);
validateInputData($bodyType, "Body Type", $define_bodyType);
validateInputData($hairDesc, "Hair", $define_hair);
validateInputData($religious, "Faith", $define_religious);
validateInputData($ethnicity, "Ethnicity", $define_ethnicity);
validateInputData($income, "Income", $define_income);
validateInputData($smokerPref, "Smoking Preference", $define_smokingPref);
validateInputData($drinkingPref, "Drinking Preference", $define_drinkingPref);
validateInputData($children, "Children", $define_children);

checkNumericLength($birthMonth, "Birth Month", 1, 12);
checkNumericLength($birthDay, "Birth Day", 1, 31);
checkNumericLength($birthYear, "Birth Year", 1900, 2035);

//create birthdate
$birthDate = $birthYear . "-" . $birthMonth . "-" . $birthDay; 

//calculate the user's age
$bithdayDate = $birthDate;
$date = new DateTime($bithdayDate);
$now = new DateTime();
$interval = $now->diff($date);
$currentAge = $interval->y;

//no one under 18
if (intval($currentAge) < 18) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'You must be at least 18 to create a profile.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

$zodiac = getZodiac($birthDate);

if (trim($zipcode) == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please enter your zip code.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

$ret = getZipcodeData($zipcode);

	if ($ret["error"] == "true") {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $ret["msg"], 
			'results' => 'none'
		);
		
		respond($response);

		die;
	}

$latitude = $ret["latitude"];				
$longitude = $ret["longitude"];				
$city = $ret["city"];				
$state = $ret["state"];				

//create hashed password
$c_password = create_hash($password);

$profile_id = uniqid();

$_parameterArray = array(
	':profile_id' => $profile_id,
	':userName' => $userName,
	':password' => $c_password,
	':emailAddress' => $emailAddress,
	':zipcode' => $zipcode,
	':latitude' => $latitude,
	':longitude' => $longitude,
	':city' => $city,
	':state' => $state,
	':birthMonth' => $birthMonth,
	':birthDay' => $birthDay,
	':birthYear' => $birthYear,
	':currentAge' => $currentAge,
	':birthDate' => $birthDate,
	':zodiac' => $zodiac,
	':firstName' => $firstName,
	':relationshipStatus' => $relationshipStatus,
	':gender' => $gender,
	':seekingGender' => $seekingGender,
	':height' => $height,
	':eyeDesc' => $eyeDesc,
	':bodyType' => $bodyType,
	':hairDesc' => $hairDesc,
	':religious' => $religious,
	':ethnicity' => $ethnicity,
	':income' => $income,
	':smokerPref' => $smokerPref,
	':drinkingPref' => $drinkingPref,
	':children' => $children,
	':profileVisible' => "1",
	':privateURL' => substr(md5(uniqid()),0,10),
	':activated' => "ACTIVE",
	':active' => "ACTIVE"

);

//update the line with the user data
$_query = <<<EOT
		
		INSERT INTO profile
		(
			profile_id,
			userName,
			password,
			emailAddress,
			zipcode,
			latitude,
			longitude,
			city,
			state,
			birthMonth,
			birthDay,
			birthYear,
			currentAge,
			birthDate,
			zodiac,
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
			privateURL,
			profileVisible,
			activated,
			active
		)
		VALUES
		(
			:profile_id,
			:userName,
			:password,
			:emailAddress,
			:zipcode,
			:latitude,
			:longitude,
			:city,
			:state,
			:birthMonth,
			:birthDay,
			:birthYear,
			:currentAge,
			:birthDate,
			:zodiac,
			:firstName,
			:relationshipStatus,
			:gender,
			:seekingGender,
			:height,
			:eyeDesc,
			:bodyType,
			:hairDesc,
			:religious,
			:ethnicity,
			:income,
			:smokerPref,
			:drinkingPref,
			:children,
			:privateURL,
			:profileVisible,
			:activated,
			:active
		)
EOT;

try {

	$db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
	$stmt = $db->prepare($_query); 
	$stmt->execute($_parameterArray);
	$count = $stmt->rowCount();

	if($count != 0) {
	
		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'error' => 'false', 
			'msg' => "complete", 
			'results' => ""
		);

		$_SESSION["account_created"] = "TRUE";

		logThis("New Account - User signup: [userName: " . $userName . "]", $profile_id, "New Account");
		
		//we can remove this once it gets popular
		__emailAlert("New Account - new user: " . $firstName . " - [userName: " . $userName . "]", ALERT_EMAIL_ADDRESS);

	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $msg_error . $stmt->errorCode(),  
			'results' => 'none'
		);

		$msg = "New Account fail - Module: signup/func/account_add.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode() . " - while attempting to sign up user [userName: " . $userName . "]";
		
		logThis($msg, $profile_id, "New Account Fail");
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);

		$_SESSION["account_created"] = "FALSE";
	}

} catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Error 100220. Please seek support.', 
		'results' => 'none'
	);

	$msg = "New Account fail - Module: signup/func/account_add.php - Had an issue with the DB throwing an exception - while attempting to sign up user [userName: " . $userName . "]";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);

	$_SESSION["account_created"] = "FALSE";
}

$_SESSION["form_token_accountsetup"] = uniqid();

respond($response);

?>