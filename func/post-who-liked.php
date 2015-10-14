<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require('../log-inc.php');
require('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];
$post_id = trim($_POST['post_id']);

if ($post_id == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please pass a valid post id.', 
		'results' => ''
	);

	respond($response);

	die;
}

$_parameterArray = array(
	':profile_id' => $profile_id,
	':post_id' => $post_id
);

//check to see if this post belongs to me
$_query = <<<EOT
		
	SELECT count(id) as totalPosts FROM posts WHERE post_id=:post_id AND profile_id = :profile_id

EOT;

try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$totalPosts = $results[0]["totalPosts"];

}
catch(PDOException $e) {

	$totalPosts = 0;
}

if ($totalPosts == "0") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Nothing found.', 
		'results' => ''
	);

	respond($response);

	die;
}

$_query = <<<EOT
		
	SELECT 
		`profile`.userName, 
		`profile`.gender, 
		`profile`.firstName, 
		`profile`.profileImage, 
		likes.post_id
	FROM likes INNER JOIN `profile` ON likes.visitor_profile_id = `profile`.profile_id
	WHERE
		likes.post_id = :post_id
	AND
		likes.dest_profile_id = :profile_id

EOT;

//get the data
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
		'totalPosts' => $totalPosts, 
		'imageCount' => $imageCount, 
		'msg' => count($results), 
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
		'msg' => 'Error 100220. Please seek support.', 
		'results' => 'none'
	);

	$msg = "POSTS GET FAIL - Module: func/post-get.php - Had an issue with the DB throwing an exception - while attempting to get posts for profile_id: " . $profile_id;
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>