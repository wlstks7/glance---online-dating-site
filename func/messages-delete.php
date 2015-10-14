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
		
		//if I am not the author... then hide
		$_parameterArray = array(
			':hidden' => "1",
			':dest_profile_id' => $profile_id,
			':visitor_profile_id' => $ret["visitor_profile_id"]
		);

		//hide messages from them
		$_query = <<<EOT
				
			UPDATE messages set
				messages.hidden = :hidden
			WHERE 
				dest_profile_id = :dest_profile_id
			AND visitor_profile_id = :visitor_profile_id

EOT;
		interact_query_insert($_query, $_parameterArray);

		//hide messages from me
		$_query = <<<EOT
				
			UPDATE messages set
				messages.hidden_sender = :hidden
			WHERE 
				dest_profile_id = :visitor_profile_id
			AND visitor_profile_id = :dest_profile_id

EOT;
		interact_query_insert($_query, $_parameterArray);

	}
}

$response = array(
	'api' => apiName, 
	'version' => apiVersion, 
	'status' => 'success', 
	'error' => 'false', 
	'results' => ""
);

respond($response);

die;
?>