<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../interact-inc.php');

//pass the profile_id as 'page token' as to not broadcast a profile id
$profile_id = trim($_POST['page_token']);
$profile_arr = explode("-", $profile_id);

//check to see if the proper structure was passed
if (isset($profile_arr[1])) {
	$profile_id = $profile_arr[1];
}else{
	$profile_id = "";
}

//evaluate id
if ($profile_id == "") {
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => "Got it! Awesome job :-)", 
		'results' => ""
	);

	respond($response);

	die;
}

//check to see if the visitor is the same as the destination profile (you cannot like your own profile)
if ($loggedUser["profile_id"] != $profile_id) {
	
	//check to see if I'm blocked from this page
	$ret = interact_amIBlocked($loggedUser["profile_id"], $profile_id);

	if ($ret != "0") {
		
		//I'm blocked from seeing this user's page...can't like it
		//but we'll still tell you it was good
		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'error' => 'false', 
			'msg' => "Got it! You are awesome :-)", 
			'results' => ""
		);
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

	//have I glanced at this profile in the last 24 hours?
	$ret = interact_canILikeProfile($loggedUser["profile_id"], $profile_id);

	//if not...log this glance
	if ($ret == "0") {
		
		//log this activity
		interact_addActivity($loggedUser["profile_id"], $profile_id, "Like Profile", "Liked this user's profile");

		//log this like
		interact_likeProfile($loggedUser["profile_id"], $profile_id, "Like Profile", "Liked this user's profile");	
	}

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => "Got it! You are awesome :-)", 
		'results' => ""
	);

} else {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'msg' => "Great job! If you like you... then other's will too!", 
		'results' => ""
	);
}

respond($response);

?>