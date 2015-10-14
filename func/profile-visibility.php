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
$profileVisible = sanitize($_POST["v"]);

if ($profileVisible != "0" && $profileVisible != "1") {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => "Not a valid value.",  
		'results' => 'none'
	);	

	respond($response);

	die;

}
$_parameterArray = array(
	':profile_id' => $profile_id,
	':profileVisible' => $profileVisible
);

//update the line with the user data
$_query = <<<EOT
		
		UPDATE profile SET 
			profileVisible = :profileVisible
		WHERE
			profile_id = :profile_id

EOT;

try {

	$db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
	$stmt = $db->prepare($_query); 
	$stmt->execute($_parameterArray);
	$count = $stmt->rowCount();

	if($count != 0) {
	
		if ($profileVisible == "1") {
			$loggedUser["profileVisible"] = "1";
			$_SESSION["loggedUser"]['profileVisible'] = "1";
		} else {	
			$loggedUser["profileVisible"] = "0";
			$_SESSION["loggedUser"]['profileVisible'] = "0";
		}

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'profileVisible' => $_SESSION["loggedUser"]['profileVisible'], 
			'error' => 'false', 
			'msg' => "complete"
		);

		
	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => "Had an issue with the DB and this was the error " . $stmt->errorCode() . " - This was reported to our support team. Please try again.",  
			'results' => 'none'
		);

		$msg = "Profile visibility change fail - Module: func/profile-visibility.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();
		
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

	$msg = "Profile visibility change fail - Module: func/profile-visibility.php - Had an issue with the DB throwing an exception";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>