<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');
require_once(SITEPATH . '/func/auth.php');

$post = sanitize($_POST["post"]);
$post_id = sanitize($_POST["id"]);
$profile_id = $_SESSION["loggedUser"]['profile_id'];
$pointsInt = $_SESSION["loggedUser"]['pointsInt'];
$post_points = 0;

if (trim($post_id) == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please refresh the page and try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

if (trim($post) == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Post cannot be empty.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

if (!is_numeric($pointsInt)) {
	$pointsInt = 10;
}

$pointsInt = intval($pointsInt);

$postPointsWorth = getPostPointWorth($post_id);
$imageCount = getPostImagesCount($post_id);

//calculating points for this post
$post_points = 10; //10 points for post
$totalPoints = $post_points;

if (!is_numeric($imageCount)) {
	$imageCount = 0;
}

$imageCount = intval($imageCount);

$imagePoints = 0;

//10 points for each image up to 5
if ($imageCount < 6) {
	$imagePoints = $imageCount * 10;	
} else {
	//more than 5 images
	$imagePoints = 50;
}

$totalPoints = $totalPoints + $imagePoints;

$post = nl2br($post);

$pos = strpos($post, "<br />");

if ($pos !== false) { 
	$totalPoints = $totalPoints + 10;
}

if ( strlen($post)>200 && strlen($post)<600 ) {
	$totalPoints = $totalPoints + 10;
}

//is this greater
$breaks = array("\r\n", "\n", "\r");
$post = str_replace($breaks, "", $post);

//validate input data
checkStringLength($post, "Post Text Length", 999);

$_parameterArray = array(
	':profile_id' => $profile_id,
	':post_id' => $post_id,
	':pointsInt' => $totalPoints,
	':post' => $post
);

$_query = <<<EOT
		
		UPDATE posts SET 
			post = :post,
			pointsInt = :pointsInt
		WHERE
			profile_id = :profile_id AND
			post_id = :post_id

EOT;

try {

	$db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
	$stmt = $db->prepare($_query); 
	$stmt->execute($_parameterArray);
	$count = $stmt->rowCount();

	if($count != 0) {
	
		//update your profile
		updateProfilePointsDelete($postPointsWorth, $profile_id);
		updateProfilePoints($totalPoints, $profile_id);
		
		$pointsInt = getProfilePointWorth($profile_id);
		
		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'post' => $post, 
			'points' => $totalPoints,
			'profilePoints' => number_format($pointsInt), 
			'error' => 'false', 
			'msg' => "complete"
		);

		//place the new points on the user's account
		$_SESSION["loggedUser"]['pointsInt'] = $pointsInt;

		logThis("Updated post: " . $post_id, $profile_id, "Updated Post");
		
	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => "Had an issue with the DB and this was the error " . $stmt->errorCode() . " - This was reported to our support team. Please try again.",  
			'results' => 'none'
		);

		$msg = "Updated Post fail - Module: func/post-update.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();
		
		logThis($msg, $profile_id, "Updated Post fail");
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

	$msg = "Updated Post fail - Module: func/post-update.php - Had an issue with the DB throwing an exception";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>