<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../interact-inc.php');

$id = trim($_POST['id']);
$reported = sanitize($_POST['report']);

if ( !is_numeric($id) ) {
	$id = "0";
}

//get the report definition
$report = $define_acceptableReports[$reported];

if ($report == "") {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => 'We received your report and will start an investigation.', 
		'results' => 'none'
	);

	respond($response);

	die;
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

		//check to see if I can report this user... 1 report per 24 hours
		$canIReport = interact_canIReportUser($profile_to_block_id, $profile_id);

		if ($canIReport == "0") {
			
			interact_reportProfile($profile_to_block_id, $profile_id, "Reported User", $report);
		}
	}
}

$response = array(
	'api' => apiName, 
	'version' => apiVersion, 
	'status' => 'success', 
	'error' => 'false', 
	'msg' => 'We received your report and will start an investigation within 24 hours.', 
	'results' => 'none'
);

respond($response);

die;
?>