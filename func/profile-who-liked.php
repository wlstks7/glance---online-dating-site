<?php  

require('../session-inc.php');
require('../data-inc.php');
require('../def-inc.php');
require('../mailgun-inc.php');
require('../log-inc.php');
require('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];

$_parameterArray = array(
	':profile_id' => $profile_id
);

$_query = <<<EOT
	
	SELECT DISTINCT
		(`profile`.profile_id),
		`profile`.userName,
		`profile`.profileImage,
		`profile`.gender,
		`profile`.firstName
	FROM
		likes
	INNER JOIN `profile` ON likes.visitor_profile_id = `profile`.profile_id
	WHERE 
		likes.dest_profile_id = :profile_id
	ORDER BY likes.id DESC
	LIMIT 20

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