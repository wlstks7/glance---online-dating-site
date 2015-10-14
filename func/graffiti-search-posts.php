<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require('../log-inc.php');
require('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$postStart = trim($_POST['postPageIndex']);
$profile_id = $_SESSION["loggedUser"]['profile_id'];
$search = trim($_POST['search']);
$offset = $postStart * 40;
$distance = '300';

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

//check for proper length
if ( strlen($search) > 20 || strlen($search) < 1) {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Search phrases need to be 20 characters or less.', 
		'results' => 'none'
	);

	respond($response);

	die;
}

$search = sanitize($search);
$search = "%" . $search . "%";

$_parameterArray = array(
	':profile_id' => $profile_id,
	':profileVisible' => "0",
	':search' => $search,
	':seekingGender' => $_SESSION["loggedUser"]['seekingGender'],
	':thislatitude' => $_SESSION["loggedUser"]['latitude'],
	':thislongitude' => $_SESSION["loggedUser"]['longitude'],
	':distance' => $distance,
	':active' => "ACTIVE",
	':deleted' => "0"
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
		profile.profile_id,
		profile.profileVisible,
		profile.firstName,
		profile.userName,
		profile.profileImage,
		profile.adultProfileRating,
		( 
			3959 * acos( cos( radians(:thislatitude) ) * cos( radians( profile.latitude ) ) * cos( radians( profile.longitude ) - radians(:thislongitude) ) + sin( radians(:thislatitude) ) * sin( radians( profile.latitude ) ) ) 
		) AS milesAway,
		DATE_FORMAT(posts.timestamp,'%M %D, %Y') as postedDate,
		posts.id,
		posts.post_id,
		posts.post,
		(
			select count(id) from likes WHERE visitor_profile_id = :profile_id AND post_id = posts.post_id
		) as likepost,
		posts.images,
		posts.links,
		posts.pinned,
		profile.active,
		profile.gender,
		posts.deleted,
		posts.post
	FROM
		profile

	INNER JOIN posts ON profile.profile_id = posts.profile_id

	HAVING
		profile.profile_id != :profile_id
	AND 
		profile.gender = :seekingGender
	AND 
		profileVisible != :profileVisible
	AND 
		posts.deleted=:deleted
	AND 
		posts.post like :search

	AND profile.profile_id NOT IN (
		SELECT
			not_interested.dest_profile_id
		FROM
			not_interested
		WHERE
			not_interested.visitor_profile_id = :profile_id
	)
	$adultClause
	AND
		profile.active = :active
	AND
		milesAway < :distance

	ORDER BY 
		#milesAway, posts.id desc
		posts.id desc
	LIMIT 40
	OFFSET $offset

EOT;

try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => count($results), 
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
		'msg' => 'Error 100220. Please seek support.', 
		'results' => 'none'
	);

	$msg = "POSTS GET FAIL - Module: func/search-posts.php - Had an issue with the DB throwing an exception - while attempting to get posts";
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>