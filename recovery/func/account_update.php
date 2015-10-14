<?php  

require('../../session-inc.php');
require('../../data-inc.php');
require('../../def-inc.php');
require('../../mailgun-inc.php');
require('../../log-inc.php');
require('../../func/passwordLib.php');

$token = trim($_POST['i']);
$password = trim($_POST['password']);
$emailAddress = $_SESSION["email_address"];
$profile_id = $_SESSION["profile_id"];

//check to see if form token exists
if ( !isset( $_SESSION["form_token_recovery"] ) || $token != $_SESSION["form_token_recovery"]){
  
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//check password for use
$ret = checkPassword($password);

	if ($ret != "0") {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $ret, 
			'results' => 'none'
		);
		
		respond($response);

		die;
	}

//create hashed password
$c_password = create_hash($password);

$_parameterArray = array(
	':password' => $c_password,
	':emailAddress' => $emailAddress
);

//update the line with the user data
$_query = <<<EOT
		
		UPDATE profile
		SET
			password = :password
		WHERE 
			emailAddress = :emailAddress

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
			'results' => ""
		);

		logThis("Account Update - User updated password for email address: " . $emailAddress, $profile_id, "Password Update");

	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $msg_error . $stmt->errorCode(),  
			'results' => 'none'
		);
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
}

$_parameterArray = array(
	':expire_int' => '0',
	':email_address' => $emailAddress
);

//expire all pending requests
$_query = <<<EOT
		
		UPDATE password_reset
		SET
			expire_int = :expire_int
		WHERE 
			email_address = :email_address

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
			'results' => ""
		);

	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => $msg_error . $stmt->errorCode(),  
			'results' => 'none'
		);
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
}

$_SESSION["form_token_recovery"] = uniqid();

respond($response);
die;

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function checkPassword($password){

	$ret = "0";
	$password = trim($password);

	//check for spaces
	if (preg_match('/\s/', $password)) {
		$ret = "Password cannot contain spaces";
		return $ret;
	}

	if (strlen($password)<6) {
	  $ret = "Password must be at least 6 characters";
	}

	if (strlen($password)>18) {
	  $ret = "Password can not be more than 18 characters";
	}

	return $ret;
}

?>