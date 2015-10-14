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
$request_token = sanitize($_POST['request_token']);

//pass the profile_id as 'page token' as to not broadcast a profile id
$profile_id = trim($_POST['page_token']);
$profile_arr = explode("-", $profile_id);

//check to see if the proper structure was passed
if (isset($profile_arr[1])) {

	$profile_id = $profile_arr[1];

}else{

	$profile_id = "";
}

//validate profile ID
if ($profile_id == ""){
  
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Could not send this message. Please refresh the page and try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

/*//check to see if form token exists

REPLACE WITH CONNECTIVITY ENGINE

if ( !isset( $_SESSION["form_token_account_edit_" . $loggedUser["profile_id"]] ) || $token != $_SESSION["form_token_account_edit_" . $loggedUser["profile_id"]]){
  
  $response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		//'token' => $token, 
		//'form_token_account_edit' => $_SESSION["form_token_account_edit_" . $profile_id], 
		'msg' => 'Please try again.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}*/

//check to see if I'm blocked from this user
$ret = interact_amIBlocked($loggedUser["profile_id"], $profile_id);

if ($ret != "0") {
	
	//I'm blocked from seeing this user's page...send me to the profile not available page
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'This user is not available any longer.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}


//check to see if either of us is listed in not interested
$notInterested = interact_amINotInterested($loggedUser["profile_id"], $profile_id);

if ($notInterested != "0") {
	
	//One of us is in the not interested list...send me to the profile not available page
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'This user is not available any longer.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//check to see if I'm banned on the site
$ret = interact_amIBanned($loggedUser["profile_id"]);

if ($ret != "0") {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'This user is not available any longer.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//check to see if this is a request to list as NOT INTERESTED
if ($request_token == "10589-102-3123A") {

	interact_addNotInterested($loggedUser["profile_id"], $profile_id);
}

//handle the post string
$post = sanitize($_POST["message"]);
$post = nl2br($post);
$breaks = array("\r\n", "\n", "\r");
$post = str_replace($breaks, "", $post);

$privateURL_placeholder = '[Your private link will automatically go here]';
$privateURL = $profile_url . "?" . $_SESSION["loggedUser"]['userName'] . "&u=" . $_SESSION["loggedUser"]['privateURL'];
$link = '<a target="_blank" href="' . $privateURL . '">Here is my private profile link</a>';
$post = str_replace($privateURL_placeholder, $link, $post);

$post_id = uniqid() . $profile_id;

//validate input data
checkStringLength($post, "Message Text Length", 1999);

$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
$ip_address = $_SERVER['REMOTE_ADDR'];

$activity_timestamp = time();

//this logs the glance to the profile
$_parameterArray = array(
	':visitor_profile_id' => $loggedUser["profile_id"],
	':dest_profile_id' => $profile_id,
	':did_what' => "New Message",
	':msg' => $post,
	':session_info' => $session_info,
	':ip_address' => $ip_address,
	':activity_timestamp' => $activity_timestamp
);

$_query = <<<EOT
			
		INSERT INTO messages
		(
			visitor_profile_id,
			dest_profile_id,
			did_what,
			msg,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:did_what,
			:msg,
			:session_info,
			:ip_address,
			:activity_timestamp
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
			'msg' => "complete"
		);

		//log this activity
		interact_addActivity($loggedUser["profile_id"], $profile_id, "New Message", "Sent a new message");

		//reset the form token 
		$_SESSION["form_token_account_edit_" . $profile_id] = uniqid();

	} else {

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'fail', 
			'error' => 'true', 
			'msg' => "Had an issue with the DB and this was the error " . $stmt->errorCode() . " - This was reported to our support team. Please try again.",  
			'results' => 'none'
		);

		$msg = "New message fail - Module: func/message-add.php - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();
		
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

	$msg = "New message fail - Module: func/message-add.php - Had an issue with the DB throwing an exception";
		
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>