<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');
require_once(SITEPATH . '/func/auth.php');

$token = trim($_POST['i']);
$profile_id = $_SESSION["loggedUser"]['profile_id'];
$pointsInt = $_SESSION["loggedUser"]['pointsInt'];
$post_points = 0;

if (!is_numeric($pointsInt)) {
	$pointsInt = 10;
}

$pointsInt = intval($pointsInt);

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
		'msg' => 'Please refresh your browser and try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}*/

//handle the post string
$post = sanitize($_POST["post"]);
$images = sanitize($_POST["images"]);
$imageCount = sanitize($_POST["imageCount"]);

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

//add the points for this post
$pointsInt = $pointsInt + $totalPoints;

//calculate post
/*

points:
	
	10 post
	10 per image up to 5
	10 <br />
	10 if you are over 100 under 400 chrs
*/

$breaks = array("\r\n", "\n", "\r");
$post = str_replace($breaks, "", $post);

$imageNames = "";

$post_id = uniqid() . $profile_id;

//validate input data
checkStringLength($post, "Post Text Length", 999);

$_parameterArray = array(
	':profile_id' => $profile_id,
	':post_id' => $post_id,
	':imageCount' => $imageCount,
	':post' => $post,
	':pointsInt' => $totalPoints,
	':images' => $images
);

//update the line with the user data
$_query = <<<EOT
		
		INSERT INTO posts
		(
			profile_id,
			post_id,
			post,
			images,
			imageCount,
			pointsInt
		)
		VALUES
		(
			:profile_id,
			:post_id,
			:post,
			:images,
			:imageCount,
			:pointsInt
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
			'profilePoints' => number_format($pointsInt), 
			'points' => $totalPoints, 
			'post_seed' => uniqid() . $profile_id, 
			'post_id' => $post_id
		);

		//place the new points on the user's account
		$_SESSION["loggedUser"]['pointsInt'] = $pointsInt;

		updateProfilePoints($totalPoints, $profile_id);

		logThis("New post: " . $post_id, $profile_id, "New Post");
		interact_addFriendUpdate($profile_id, "New Post", "Just added a new post.");
		
	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => "Had an issue with the DB and this was the error " . $stmt->errorCode() . " - This was reported to our support team. Please try again.",  
			'results' => 'none'
		);

		$msg = "New post fail - Module: func/post-add.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();
		
		logThis($msg, $profile_id, "New post fail");
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

	$msg = "New post fail - Module: func/post-add.php - Had an issue with the DB throwing an exception";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}
	
respond($response);

die;

?>