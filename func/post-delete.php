<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require('../log-inc.php');
require('../account-inc.php');
require(SITEPATH . '/func/auth.php');

//this is the user profile_id
$profile_id = $_SESSION["loggedUser"]['profile_id'];
$post_id = sanitize($_POST["post_id"]);

$postPoints = getPostPointWorth($post_id);

$_parameterArray = array(
	':profile_id' => $profile_id,
	':post_id' => $post_id,
	':deleted' => "1",
);

//update the line with the user data
$_query = <<<EOT
		
		UPDATE posts SET 
			deleted = :deleted
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
	
		//remove the points from thus post from the user profile
		updateProfilePointsDelete($postPoints, $profile_id);

		$pointsInt = $_SESSION["loggedUser"]['pointsInt'];

		$totalProfilePoints = $pointsInt - $postPoints;

		//place the new points on the user's account
		$_SESSION["loggedUser"]['pointsInt'] = $totalProfilePoints;
		
		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'pointsRemoved' => $postPoints, 
			'profilePoints' => number_format($totalProfilePoints), 
			'error' => 'false', 
			'msg' => "complete"
		);

		logThis("Deleted post: " . $post_id, $profile_id, "Deleted Post");
		
	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => "Had an issue with the DB and this was the error " . $stmt->errorCode() . " - This was reported to our support team. Please try again.",  
			'results' => 'none'
		);

		$msg = "Deleted Post fail - Module: func/post-delete.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();
		
		logThis($msg, $profile_id, "Deleted Post fail");
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

	$msg = "Deleted Post fail - Module: func/post-deleted.php - Had an issue with the DB throwing an exception";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>