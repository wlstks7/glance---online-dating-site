<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require('../log-inc.php');
require('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$postStart = trim($_POST['postPageIndex']);
$profile_id = sanitize($_POST['profile_id']);

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

$offset = $postStart * 5;

$_parameterArray = array(
	':profile_id' => $profile_id,
	':deleted' => "0"
);

$_query = <<<EOT
		
	SELECT count(post_id) as totalPosts FROM posts WHERE deleted=:deleted AND profile_id = :profile_id

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

//get total images
$_query = <<<EOT
		
	SELECT sum(imageCount) as imageCount FROM posts WHERE deleted=:deleted AND profile_id = :profile_id

EOT;

try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$imageCount = $results[0]["imageCount"];

}
catch(PDOException $e) {

	$imageCount = 0;
}

$_parameterArray = array(
	':profile_id' => $profile_id,
	':visitor_profile_id' => $_SESSION["loggedUser"]['profile_id'],
	':deleted' => "0"
);

$_query = <<<EOT
		
	SELECT 
		post_id,
		profile_id,
		(
			select count(id) from likes WHERE visitor_profile_id = :visitor_profile_id AND post_id = posts.post_id
		) as likepost,
		post,
		images,
		links,
		pinned,
		DATE_FORMAT(timestamp,'%M %D, %Y') as postedDate
	FROM posts
	WHERE profile_id = :profile_id AND deleted=:deleted
	ORDER BY pinned desc, id desc
	LIMIT 5
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