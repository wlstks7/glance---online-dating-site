<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');

$id = trim($_POST['id']);

if ( !is_numeric($id) ) {
	$id = "0";
}

//find the users for this message
$_parameterArray = array(
	':id' => $id
);

$_query = <<<EOT
		
	SELECT
		messages.visitor_profile_id,
		messages.dest_profile_id
	FROM
		messages
	WHERE 
		messages.id = :id
	LIMIT 1

EOT;

$ret = interact_query_select($_query, $_parameterArray);
$ret = $ret["results"][0];

$profile_id = $_SESSION["loggedUser"]['profile_id'];

//is one of the users me?
if ($ret["visitor_profile_id"] == $profile_id || $ret["dest_profile_id"] == $profile_id) {
	
	if ($ret["dest_profile_id"] == $profile_id){
		
		$profile_to_block_id = $ret["visitor_profile_id"];

		//check to see if this user is already blocked 
		$ret = interact_amIBlocked($profile_to_block_id, $profile_id);

		if ($ret == "0") {
			
			//add to our blocked list
			interact_addBlocked($profile_to_block_id, $profile_id);
		}

		//check to see if either of us is listed in not interested
		$notInterested = interact_amINotInterested($profile_to_block_id, $profile_id);

		if ($notInterested == "0") {
			
			//add to not interested
			interact_addNotInterested($profile_to_block_id, $profile_id);
		}
	}
}

$response = array(
	'api' => apiName, 
	'version' => apiVersion, 
	'status' => 'success', 
	'error' => 'false', 
	'msg' => 'This user is now blocked.', 
	'results' => 'none'
);

respond($response);

die;
?>