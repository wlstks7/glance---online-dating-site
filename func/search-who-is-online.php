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

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

$offset = $postStart * 40;

$distance = '50';

//whos online
/*

100 mile radius
gender: seekingGender
not me
not in notInterested
not deleted

/get the seconds from last request
$secondsFromLastRequest =  time() - $last_online;

*/

$timeNow = time();
$timeNow_Sub = $timeNow - 300;

$_parameterArray = array(
	':profile_id' => $profile_id,
	':seekingGender' => $_SESSION["loggedUser"]['seekingGender'],
	':thislatitude' => $_SESSION["loggedUser"]['latitude'],
	':thislongitude' => $_SESSION["loggedUser"]['longitude'],
	':distance' => $distance,
	':timeLastOnline' => $timeNow_Sub,
	':profileVisible' => "0",
	':active' => "ACTIVE"
);

$adultViewPref = $_SESSION["loggedUser"]['adultViewPref'];
$adultProfileRating = $_SESSION["loggedUser"]['adultProfileRating'];
$adultClause = "";

if ( $adultViewPref == "noNudity" ) {
	//this user does not want to see any nudity or adult content
	$_parameterArray[":adultProfileRating"] = "noNudity";
	$adultClause = " AND adultProfileRating = :adultProfileRating ";
}

$_query = <<<EOT
	
	SELECT
		profile_id,
		userName,
		city,
		state,
		latitude,
		longitude,
		birthDate,
		firstName,
		gender,
		seekingGender,
		profileImage,
		last_online,
		adultProfileRating,
		active,
		profileVisible,
		pointsInt,
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
	$adultClause
	AND
		active = :active
	AND
		milesAway < :distance
	ORDER BY 
		IF(last_online > :timeLastOnline, 0, 1) asc, 
		pointsInt desc,
		milesAway
	LIMIT 40
	OFFSET $offset

EOT;

//get the data
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
		//unset($results[$key]["longitude"]);
		//unset($results[$key]["latitude"]);
		unset($results[$key]["active"]);
	}

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'count' => count($results), 
		'msg' => "", 
		//'q' => $_query, 
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

?>