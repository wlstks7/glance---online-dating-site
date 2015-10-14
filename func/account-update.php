<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../connectivity-inc.php');


$token = trim($_POST['i']);
$profile_id = $_SESSION["loggedUser"]['profile_id'];

/*//check to see if form token exists

REPLACE WITH CONNECTIVITY ENGINE

if ( !isset( $_SESSION["form_token_account_edit_" . $profile_id] ) || $token != $_SESSION["form_token_account_edit_" . $profile_id]){
  
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		//'token' => $token, 
		//'form_token_account_edit' => $_SESSION["form_token_account_edit_" . $profile_id], 
		'msg' => 'Please try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}*/

$zipcode = sanitize($_POST["zipcode"]);
$firstName = sanitize($_POST["firstName"]);
$birthMonth = sanitize($_POST["birthMonth"]);
$birthDay = sanitize($_POST["birthDay"]);
$birthYear = sanitize($_POST["birthYear"]);
$relationshipStatus = sanitize($_POST["relationshipStatus"]);
$gender = sanitize($_POST["gender"]);
$seekingGender = sanitize($_POST["seekingGender"]);

$height = sanitize($_POST["height"]);
$eyeDesc = sanitize($_POST["eyeDesc"]);
$bodyType = sanitize($_POST["bodyType"]);
$hairDesc = sanitize($_POST["hairDesc"]);
$ethnicity = sanitize($_POST["ethnicity"]);
$religious = sanitize($_POST["religious"]);
$children = sanitize($_POST["children"]);
$income = sanitize($_POST["income"]);
$smokerPref = sanitize($_POST["smokerPref"]);
$drinkingPref = sanitize($_POST["drinkingPref"]);

$zodiacPref = sanitize($_POST["zodiacPref"]);
$profileRating = sanitize($_POST["profileRating"]);
$adultPreference = sanitize($_POST["adultPreference"]);

$profileDesc = sanitize($_POST["profileDesc"]);

if ($profileDesc == "") {
	$profileDesc = $default_profileDescription;
}

$profileDesc = nl2br($profileDesc);

$breaks = array("\r\n", "\n", "\r");
$profileDesc = str_replace($breaks, "", $profileDesc);


$profileBannerImage = sanitize($_POST["bannerImage"]);

if (substr($profileBannerImage, 0, 3) != "../") {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'This is not a proper URL.', 
		'results' => 'none'
	);
	
	respond($response);

	die;	
}
//validate input data

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
checkStringLength($profileDesc, "Profile Description", 590);

checkStringLength($zodiacPref, "Zodiac Preference", 9);
checkStringLength($profileRating, "Your Profile Rating", 19);
checkStringLength($adultPreference, "Adult View Preference", 19);
checkStringLength($profileBannerImage, "Banner Image URL", 199);

checkNumericLength($birthMonth, "Birth Month", 1, 12);
checkNumericLength($birthDay, "Birth Day", 1, 31);
checkNumericLength($birthYear, "Birth Year", 1900, 2035);

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
validateInputData($zodiacPref, "Zodiac Preference", $define_zodiacPref);
validateInputData($profileRating, "Your Profile Rating", $define_adultProfileRating);
validateInputData($adultPreference, "Adult View Preference", $define_adultViewPref);

//create birthdate
$birthDate = $birthYear . "-" . $birthMonth . "-" . $birthDay; 

//calculate the user's age
$bithdayDate = $birthDate;
$date = new DateTime($bithdayDate);
$now = new DateTime();
$interval = $now->diff($date);
$currentAge = $interval->y;

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

$_parameterArray = array(
	':profile_id' => $profile_id,
	':zipcode' => $zipcode,
	':latitude' => $latitude,
	':longitude' => $longitude,
	':city' => $city,
	':state' => $state,
	':birthMonth' => $birthMonth,
	':birthDay' => $birthDay,
	':birthYear' => $birthYear,
	':birthDate' => $birthDate,
	':currentAge' => $currentAge,
	':zodiac' => $zodiac,
	':zodiacShow' => $zodiacPref,
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
	':adultViewPref' => $adultPreference,
	':adultProfileRating' => $profileRating,
	':profileBannerImage' => $profileBannerImage,
	':profileDesc' => $profileDesc
);

//update the line with the user data
$_query = <<<EOT
		
		UPDATE profile SET
			zipcode = :zipcode,
			latitude = :latitude,
			longitude = :longitude,
			city = :city,
			state = :state,
			birthMonth = :birthMonth,
			birthDay = :birthDay,
			birthYear = :birthYear,
			birthDate = :birthDate,
			currentAge = :currentAge,
			zodiac = :zodiac,
			zodiacShow = :zodiacShow,
			firstName = :firstName,
			relationshipStatus = :relationshipStatus,
			gender = :gender,
			seekingGender = :seekingGender,
			height = :height,
			eyeDesc = :eyeDesc,
			bodyType = :bodyType,
			hairDesc = :hairDesc,
			religious = :religious,
			ethnicity = :ethnicity,
			income = :income,
			smokerPref = :smokerPref,
			drinkingPref = :drinkingPref,
			children = :children,
			adultViewPref = :adultViewPref,
			adultProfileRating = :adultProfileRating,
			profileBannerImage = :profileBannerImage,
			profileDesc = :profileDesc
		WHERE
			profile_id = :profile_id

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

		logThis("Updated Account - Old settings: " . print_r($loggedUser, true), $profile_id, "Updated Account");
		interact_addFriendUpdate($profile_id, "Updated Profile", "Just updated my profile.");

		//we can remove this once it gets popular
		//_emailAlert("Updated Account -  User: " . $loggedUser["firstName"] . " - [userName: " . $loggedUser["userName"] . "]", ALERT_EMAIL_ADDRESS);

	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $msg_error . $stmt->errorCode(),  
			'results' => 'none'
		);

		$msg = "Update Account fail - Module: func/account_update.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode() . " - while attempting to update user [userName: " . $loggedUser["userName"] . "]";
		
		logThis($msg, $profile_id, "Update Account Fail");
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);
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

	$msg = "Update Account fail - Module: func/account_update.php - Had an issue with the DB throwing an exception - while attempting to sign up user [userName: " . $loggedUser["userName"] . "]";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

//build the logged in user session
$ret = buildLoggedUser($profile_id);

if ($ret != "SUCCESS") {
	
	//report the fail to the script to force re-login
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Your profile was updated and we need you to login again for these changes to take effect.', 
		'results' => "RET: " . $ret
	);

	//force log out... 
	$_SESSION['ion_user_authenticated'] = '';
	$_SESSION['ion_cookie_timestamp'] = '';

	unset( $_SESSION["ion_user_authenticated"]);
	unset( $_SESSION["ion_cookie_timestamp"]);
	unset( $_SESSION["loggedUser"]);
}

//reset the form token 
$_SESSION["form_token_account_edit_" . $profile_id] = uniqid();

respond($response);

die;

?>