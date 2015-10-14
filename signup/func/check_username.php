<?php  

require("../config-inc.php");
require("../siteinfo.php");

//get the posted data
$token = trim($_POST['i']);
$username = trim(sanitize($_POST['u']));
$password = trim($_POST['p']);

//check to see if form token exists
if ( !isset( $_SESSION["form_token"] ) || $token != $_SESSION["form_token"]){
  
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

if ($username == "") {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Please choose a valid username.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

//replace all spaces
$password = str_replace(" ", "", $password);

if (strlen($password) < 6) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Your password should be 6 characters or more.', 
		'results' => 'none'
	);
	
	respond($response);

	die;
}

$_parameterArray = array(
	':username' => $username
);

$_query = <<<EOT
	SELECT 
		username
	FROM 
		customers 
	WHERE 
		username = :username
	LIMIT 1
EOT;

include_once 'data_controller.php';

$_SESSION["form_token"] = uniqid();

respond($response);

?>