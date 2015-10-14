<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');
require_once(SITEPATH . '/func/auth.php');

$profile_id = trim($_POST['id']);
$request_token = sanitize($_POST['page_token']);

//validate profile ID
if ($profile_id == ""){
  
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => "complete"
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
		'status' => 'success', 
		'error' => 'false', 
		'msg' => "complete"
	);
	
	respond($response);

	die;
}*/

interact_addNotInterested($loggedUser["profile_id"], $profile_id);

$response = array(
	'api' => apiName, 
	'version' => apiVersion, 
	'status' => 'success', 
	'error' => 'false', 
	'msg' => "complete"
);

//reset the form token 
$_SESSION["form_token_account_edit_" . $profile_id] = uniqid();

respond($response);

die;
	
?>