<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];
$postStart = trim($_POST['postPageIndex']);
$filter = trim($_POST['filter']);

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

try {

	//try to create an array from filter JSON
	$filter = json_decode($filter);
	$filter = json_decode(json_encode($filter), true);

	if (!is_array($filter)) {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => 'I am having trouble processing that request. Please refresh your browser and try this search again. Thank you :-)', 
			'results' => 'none'
		);

		respond($response);

		die;

	}

} catch (Exception $e) {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'I am having trouble processing that request. Please refresh your browser and try this search again.', 
		'results' => 'none'
	);

	respond($response);

	die;
}

//validate the values 
validateFilterData($filter["searchingGender"], "Gender seeking gender", $define_searchGender);
validateFilterData($filter["bodyType"], "Body Type", $define_bodyType);
validateFilterData($filter["hairDesc"], "Hair", $define_hair);
validateFilterData($filter["ethnicity"], "Ethnicity", $define_ethnicity);
validateFilterData($filter["eyeDesc"], "Eyes", $define_eyes);
validateFilterData($filter["minHeight"], "Minimum Height", $define_height);
validateFilterData($filter["maxHeight"], "Maximum Height", $define_height);
validateFilterData($filter["relationshipStatus"], "Relationship Status", $define_relationshipStatus);
validateFilterData($filter["religious"], "Faith", $define_religious);
validateFilterData($filter["children"], "Children", $define_children);
validateFilterData($filter["income"], "Income", $define_income);
validateFilterData($filter["smokerPref"], "Smoking Preference", $define_smokingPref);
validateFilterData($filter["drinkingPref"], "Drinking Preference", $define_drinkingPref);
validateFilterData($filter["zodiacPref"], "Astrological Sign", $define_zodiacList);

checkNumericLength($filter["searchingAgeFrom"], "Minimum Age", 18, 100);
checkNumericLength($filter["searchingAgeTo"], "Maximum Age", 18, 100);
checkNumericLength($filter["searchDistance"], "Search Distance", 1, 200);
checkNumericLength($filter["searchZipcode"], "Zipcode", 0, 99999);

if (trim($filter["onlyWithProfileImage"]) != "checked" && trim($filter["onlyWithProfileImage"]) != "") {
	
	$response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
	  'msg' => 'I am having trouble processing that request. Please refresh your browser and try this search again.', 
      'results' => ""
    );
    
    respond($response);

    die;	
}

if (trim($filter["onlyOnline"]) != "checked" && trim($filter["onlyOnline"]) != "") {
	
	$response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
	  'msg' => 'I am having trouble processing that request. Please refresh your browser and try this search again.', 
      'results' => ""
    );
    
    respond($response);

    die;	
}

if (trim($filter["adultSearch"]) != "checked" && trim($filter["adultSearch"]) != "") {
	
	$response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
	  'msg' => 'I am having trouble processing that request. Please refresh your browser and try this search again.', 
      'results' => ""
    );
    
    respond($response);

    die;	
}

//tokenize our parms for the search statement

if ( $filter["searchingGender"] == "guygal" || $filter["searchingGender"] == "galgal") {
	
	$seekingGender = "gal";

} else {

	$seekingGender = "guy";
}

//get geo location data for this zipcode
$ret = getZipcodeData($filter["searchZipcode"]);

if ($ret["error"] == 'true') {

	$response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
	  'msg' => 'I cannot locate that zipcode. Please try another zipcode close to the area you wish to search.', 
      'results' => ""
    );
    
    respond($response);

    die;
}

$latitude = $ret["latitude"];
$longitude = $ret["longitude"];
$city = $ret["city"];
$state = $ret["state"];

$timeNow = time();
$timeNow_Sub = $timeNow - 300;


//init the parm array
$_parameterArray = array(
	':profile_id' => $profile_id,
	':profileVisible' => "0",
	':seekingGender' => $seekingGender,
	':thislatitude' => $latitude,
	':thislongitude' => $longitude,
	':distance' => $filter["searchDistance"],
	':timeLastOnline' => $timeNow_Sub,
	':active' => "ACTIVE"
);

//init your statement
$selectStatement = 'profile_id, profileVisible, userName, city, state, latitude, longitude, birthDate, firstName, gender, seekingGender, profileImage, last_online, active, adultProfileRating, ';
$additionalParms = "";

buildParmArr("bodyType", $filter["bodyType"]);
buildParmArr("hairDesc", $filter["hairDesc"]);
buildParmArr("ethnicity", $filter["ethnicity"]);
buildParmArr("eyeDesc", $filter["eyeDesc"]);
buildParmArr("relationshipStatus", $filter["relationshipStatus"]);
buildParmArr("religious", $filter["religious"]);
buildParmArr("children", $filter["children"]);
buildParmArr("income", $filter["income"]);
buildParmArr("smokerPref", $filter["smokerPref"]);
buildParmArr("drinkingPref", $filter["drinkingPref"]);
buildParmArr("zodiacPref", $filter["zodiacPref"]);

$adultViewPref = $_SESSION["loggedUser"]['adultViewPref'];
$adultProfileRating = $_SESSION["loggedUser"]['adultProfileRating'];
$adultClause = "";

if ( $adultViewPref == "noNudity" ) {
	//this user does not want to see any nudity or adult content
	$_parameterArray[":adultProfileRating"] = "noNudity";
	$adultClause = " AND adultProfileRating = :adultProfileRating ";
}

if (trim($filter["minHeight"]) != "" && trim($filter["maxHeight"]) != "") {
	
	$_parameterArray[":minHeight"] = $filter["minHeight"];
	$_parameterArray[":maxHeight"] = $filter["maxHeight"];
		
	$selectStatement = $selectStatement . " height, ";
	$additionalParms = $additionalParms . " AND height >= :minHeight AND height <= :maxHeight ";
}

$_parameterArray[":searchingAgeFrom"] = $filter["searchingAgeFrom"];
$_parameterArray[":searchingAgeTo"] = $filter["searchingAgeTo"];
	
$selectStatement = $selectStatement . " currentAge, ";
$additionalParms = $additionalParms . " AND currentAge >= :searchingAgeFrom AND currentAge <= :searchingAgeTo ";

if (trim($filter["onlyWithProfileImage"]) == "checked") {
	
	$_parameterArray[":profileImage"] = "";
	$selectStatement = $selectStatement . " profileImage, ";
	$additionalParms = $additionalParms . " AND profileImage != :profileImage ";
}

if (trim($filter["onlyOnline"]) == "checked") {
	
	$additionalParms = $additionalParms . " AND last_online > :timeLastOnline ";
}

if (trim($filter["adultSearch"]) == "checked") {
	
	$_parameterArray[":adultProfileRating"] = "yesNudity";
	$selectStatement = $selectStatement . " adultProfileRating, ";
	$additionalParms = $additionalParms . " AND adultProfileRating = :adultProfileRating ";
}

$offset = $postStart * 40;

//get the conversation
$_query = <<<EOT
	
	SELECT
		[SELECT STATEMENT]
		( 
			3959 * acos( cos( radians(:thislatitude) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:thislongitude) ) + sin( radians(:thislatitude) ) * sin( radians( latitude ) ) ) 
		) AS milesAway
	FROM
		profile
	HAVING
		profile_id != :profile_id
	AND gender = :seekingGender
	AND profileVisible != :profileVisible
	AND profile_id NOT IN (
		SELECT
			not_interested.dest_profile_id
		FROM
			not_interested
		WHERE
			not_interested.visitor_profile_id = :profile_id
	)
	AND
		active = :active
	AND
		milesAway < :distance

	$adultClause
	[ADDITIONAL PARMS]

	ORDER BY 
		IF(last_online > :timeLastOnline, 0, 1) asc,
		milesAway
	LIMIT 40
	OFFSET $offset

EOT;

$_query = str_replace("[SELECT STATEMENT]", $selectStatement, $_query);
$_query = str_replace("[ADDITIONAL PARMS]", $additionalParms, $_query);

try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//insert some cacluated data
	foreach ($results as $key => $value) {
		
		//calculate the user's age
		$bithdayDate = $results[$key]["birthDate"];
		$date = new DateTime($bithdayDate);
		$now = new DateTime();
		$interval = $now->diff($date);

		$results[$key]["age"] = $interval->y;
		$results[$key]["milesAway"] = intval($results[$key]["milesAway"]);

		//profile image
		if ($results[$key]["gender"] == "guy") {
			$profileNoPic = "../assets/nopic_guy.png";
		} else {
			$profileNoPic = "../assets/nopic_gal.png";
		}

		$profileImage = $results[$key]["profileImage"];

		if ( trim($profileImage) == "" ) {
			$profileImage = $profileNoPic;
		}

		$results[$key]["profileImage"] = $profileImage;

		$last_online = $results[$key]["last_online"];

		//get the seconds from last request
		$secondsFromLastRequest =  $timeNow - $last_online;

		$last_online = "";

		if ( $secondsFromLastRequest < 300 ) {
			
			$last_online = "Online Now";
		}

		$results[$key]["last_online"] = $last_online;

		//get rid of some of the revealing data
		unset($results[$key]["birthDate"]);
		unset($results[$key]["active"]);
	}

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'count' => count($results), 
		'msg' => "", 
		'q' => $_query, 
		'results' => $results
	);
}
catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'I am having trouble getting this message. Please try again.', 
		'results' => 'none'
	);

	$msg = "MESSAGE GET FAIL - Module: func/message-get.php - Had an issue with the DB throwing an exception - while attempting to get message for profile_id: " . $profile_id;
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

function buildParmArr($baseName, $parm){
	global $_parameterArray,
			$selectStatement,
			$additionalParms;

	$parm = trim($parm);

	if ( $parm != "" ) {
		
		$tokens = explode(",", $parm);
		$tokenCount = count($tokens);

		$parmWrapper = " AND (";
		$parmTerminate = ") ";
		
		$selectStatement = $selectStatement . $baseName . ", ";

		$x=0;
		$parmConst = "";

		foreach ($tokens as $key => $value) {
			
			$token = $baseName . "_" . $x;
			$_parameterArray[":" . $token] = $value;
			
			if ($x == 0) {
	
				$parmConst = $parmConst . ' ' . $baseName . ' = :' . $token . ' ';

			} else {

				$parmConst = $parmConst . ' OR ' . $baseName . ' = :' . $token . ' ';
			}

			$x++;
		}

		$additionalParms = $additionalParms . $parmWrapper . $parmConst . $parmTerminate;
	}
}

function validateFilterData($str, $name, $definition){

  $str = trim($str);

  //check for no data
  if ($str == "") {
  	return false;
  }

  $filter_data = explode(",", $str);

  $flag = false;

  foreach ($filter_data as $key => $value) {

  	$definedValue = $definition[$value];

  	 if ( $definedValue == "" ) {

  	 	$flag = true;
  	 }
  }

  if ( $flag == true ) {
    
    $ret =  $str . " - This is not a valid option for " . $name . ". Please check this and try again.";

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => ""
    );
    
    respond($response);

    die;
  }
}



?>