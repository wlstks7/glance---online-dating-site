<?php  

//Include to manage all site interaction between users

require_once(SITEPATH . '/session-inc.php');
require_once(SITEPATH . '/data-inc.php');
require_once(SITEPATH . '/mailgun-inc.php');
require_once(SITEPATH . '/log-inc.php');
require_once(SITEPATH . '/func/auth.php');


/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_addActivity($visitor_profile_id, $dest_profile_id, $did_what, $activity_description){

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address
	);

	$_query = <<<EOT
			
		INSERT INTO activity
		(
			visitor_profile_id,
			dest_profile_id,
			did_what,
			activity_description,
			session_info,
			ip_address
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address
		)
EOT;

	interact_query_insert($_query, $_parameterArray);
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_addBlocked($visitor_profile_id, $dest_profile_id){

	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id
	);

	$_query = <<<EOT
			
		INSERT INTO blocked_users
		(
			visitor_profile_id,
			dest_profile_id
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id
		)
EOT;

	interact_query_insert($_query, $_parameterArray);
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_addFriendUpdate($profile_id, $did_what, $activity_description){

	$ret = interact_canIFriendUpdate($profile_id);
	
	if ($ret != 0) {
		return false;
	}

	$time_now = time();

	//calc a value 24 hours ago
	$activity_timestamp = $time_now;

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$_parameterArray = array(
		':profile_id' => $profile_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address,
		':activity_timestamp' => $activity_timestamp

	);

	$_query = <<<EOT
			
		INSERT INTO friend_updates
		(
			profile_id,
			did_what,
			activity_description,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:profile_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address,
			:activity_timestamp
		)
EOT;

	interact_query_insert($_query, $_parameterArray);
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_addNotInterested($visitor_profile_id, $dest_profile_id){

	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id
	);

	$_query = <<<EOT
			
		INSERT INTO not_interested
		(
			visitor_profile_id,
			dest_profile_id
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id
		)
EOT;

	interact_query_insert($_query, $_parameterArray);
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_canIFriendUpdate($profile_id){

	//check to see if we have logged any friendly updates in the last 5 hours
	$time_now = time();

	//calc a value 24 hours ago
	$activity_timestamp = $time_now - 18000;

	$_parameterArray = array(
		':profile_id' => $profile_id,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		SELECT count(id) as haveIUpdated
			FROM friend_updates
		WHERE 
			profile_id = :profile_id
		AND activity_timestamp > :activity_timestamp
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["haveIUpdated"];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_canIReportUser($visitor_profile_id, $dest_profile_id){

	//check to see if we have logged any friendly updates in the last 5 hours
	$time_now = time();

	//calc a value 24 hours ago
	$activity_timestamp = $time_now - 86400;

	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		SELECT count(id) as reported
			FROM report_user
		WHERE 
			visitor_profile_id = :visitor_profile_id
		AND	dest_profile_id = :dest_profile_id
		AND activity_timestamp > :activity_timestamp
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["reported"];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_amINotInterested($visitor_profile_id, $profile_id){

	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $profile_id
	);

	$_query = <<<EOT
			
		SELECT count(id) as amINotInterested
			FROM not_interested
		WHERE 
		(
			(not_interested.visitor_profile_id = :visitor_profile_id AND not_interested.dest_profile_id = :dest_profile_id)
				OR
			(not_interested.dest_profile_id = :visitor_profile_id AND not_interested.visitor_profile_id = :dest_profile_id)
		)
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["amINotInterested"];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_canIGlance($visitor_profile_id, $dest_profile_id){

	//check to see if we should log this glance

	$time_now = time();

	//calc a value 24 hours ago
	$activity_timestamp = $time_now - 86400;

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		SELECT count(id) as haveIGlanced
			FROM glances
		WHERE 
			visitor_profile_id = :visitor_profile_id
		AND	dest_profile_id = :dest_profile_id
		AND activity_timestamp > :activity_timestamp
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["haveIGlanced"];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_canILikeProfile($visitor_profile_id, $dest_profile_id){

	//check to see if we should log this like

	$time_now = time();

	//calc a value 24 hours ago
	$activity_timestamp = $time_now - 86400;

	//this logs the like to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		SELECT count(id) as haveILiked
			FROM likes
		WHERE 
			visitor_profile_id = :visitor_profile_id
		AND	dest_profile_id = :dest_profile_id
		AND activity_timestamp > :activity_timestamp
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["haveILiked"];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_glanceProfile($visitor_profile_id, $dest_profile_id, $did_what, $activity_description){

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$activity_timestamp = time();

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		INSERT INTO glances
		(
			visitor_profile_id,
			dest_profile_id,
			did_what,
			activity_description,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address,
			:activity_timestamp
		)
EOT;

	interact_query_insert($_query, $_parameterArray);

}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_haveWeMet($visitor_profile_id, $dest_profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id
	);

	/*

	have you ever liked this user
	have they liked you?
	messages? from to?
	has this user seen you?

	*/

	$_query = <<<EOT
		
		SELECT 

			count(id) as theyGlanced,

			(
				SELECT count(id)
				FROM likes
				WHERE 
					visitor_profile_id = :visitor_profile_id
				AND	dest_profile_id = :dest_profile_id
			) as youLike,
			
			(
				SELECT count(id)
				FROM likes
				WHERE 
					visitor_profile_id = :dest_profile_id
				AND	dest_profile_id = :visitor_profile_id
			) as theyLike,

			(
				SELECT count(id)
				FROM messages
				WHERE 
					visitor_profile_id = :visitor_profile_id
				AND	dest_profile_id = :dest_profile_id
			) as youMessage,
			
			(
				SELECT count(id)
				FROM messages
				WHERE 
					visitor_profile_id = :dest_profile_id
				AND	dest_profile_id = :visitor_profile_id
			) as theyMessage

		FROM glances
		WHERE 
			visitor_profile_id = :dest_profile_id
		AND	dest_profile_id = :visitor_profile_id
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0];

	} else {

		//error...don't log this 
		$ret = "1";
	}

	return $ret;
}

function interact_likeProfile($visitor_profile_id, $dest_profile_id, $did_what, $activity_description){

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$activity_timestamp = time();

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		INSERT INTO likes
		(
			visitor_profile_id,
			dest_profile_id,
			did_what,
			activity_description,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address,
			:activity_timestamp
		)
EOT;

	interact_query_insert($_query, $_parameterArray);

}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_likePost($visitor_profile_id, $dest_profile_id, $did_what, $activity_description, $post_id){

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$activity_timestamp = time();

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':post_id' => $post_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		INSERT INTO likes
		(
			visitor_profile_id,
			dest_profile_id,
			post_id,
			did_what,
			activity_description,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:post_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address,
			:activity_timestamp
		)
EOT;

	interact_query_insert($_query, $_parameterArray);

}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_amIBlocked($visitor_profile_id, $dest_profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id
	);

	$_query = <<<EOT
			
		SELECT count(id) as blockCount
			FROM blocked_users
		WHERE 
			visitor_profile_id = :visitor_profile_id
		AND	dest_profile_id = :dest_profile_id
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["blockCount"];

	} else {

		//error... show blocked status as a safeguard
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/



function interact_amIBanned($profile_id){

	$activity_timestamp = time();

	//this logs the glance to the profile
	$_parameterArray = array(
		':profile_id' => $profile_id,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		SELECT count(id) as bannedCount
			FROM banned
		WHERE 
			profile_id = :profile_id
		AND	activity_timestamp > :activity_timestamp
		
EOT;

	$ret = interact_query_select($_query, $_parameterArray);

	if ($ret["error"] == "false") {
		
		$ret = $ret["results"];
		$ret = $ret[0]["bannedCount"];

	} else {

		//error... show blocked status as a safeguard
		$ret = "1";
	}

	return $ret;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_query_select($_query, $_parameterArray){

	try {

		$db = new PDO(conn . dbName, dbUser, dbPass);
		$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
		$stmt->execute($_parameterArray);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$response["error"] = "false";
		$response["msg"] = "";
		$response["count"] = count($results);
		$response["results"] = $results;
		return $response;

	} catch(PDOException $e) {

		$ret = "Could not finish the task. Error 100220. Please contact our support team.";
		$response["error"] = "true";
		$response["msg"] = $ret;
		$response["count"] = 0;
		$response["results"] = "";
		return $response;
	}
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_query_insert($_query, $_parameterArray){

	try {

		$db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
		$stmt = $db->prepare($_query); 
		$stmt->execute($_parameterArray);
		$count = $stmt->rowCount();

		if($count != 0) {
			
			//good... return success
			$response = array(
				'api' => apiName, 
				'version' => apiVersion, 
				'status' => 'success', 
				'error' => 'false', 
				'msg' => "complete", 
				'results' => ""
			);

		} else {

			$msg = "Add failed - Module: interact_query_insert() - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode();

			$response = array(
				'api' => apiName, 
				'version' => apiVersion, 
				'status' => 'fail', 
				'error' => 'true', 
				'msg' => $msg,  
				'results' => 'none'
			);
			
			logThis($msg, "", "Add failed");
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

		$msg = "Add failed - Module: interact_query_insert() - Had an issue with the DB throwing an exception";
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);
	}

	return $response;
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
******************************************
*****************************************/

function interact_reportProfile($visitor_profile_id, $dest_profile_id, $did_what, $activity_description){

	$session_info = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
	$ip_address = $_SERVER['REMOTE_ADDR'];

	$activity_timestamp = time();

	//this logs the glance to the profile
	$_parameterArray = array(
		':visitor_profile_id' => $visitor_profile_id,
		':dest_profile_id' => $dest_profile_id,
		':did_what' => $did_what,
		':activity_description' => $activity_description,
		':session_info' => $session_info,
		':ip_address' => $ip_address,
		':activity_timestamp' => $activity_timestamp
	);

	$_query = <<<EOT
			
		INSERT INTO report_user
		(
			visitor_profile_id,
			dest_profile_id,
			did_what,
			activity_description,
			session_info,
			ip_address,
			activity_timestamp
		)
		VALUES
		(
			:visitor_profile_id,
			:dest_profile_id,
			:did_what,
			:activity_description,
			:session_info,
			:ip_address,
			:activity_timestamp
		)
EOT;

	interact_query_insert($_query, $_parameterArray);

}

/*

//throttling concept

/*$sessionName = "interation_site_usage_speed_" . $loggedUser["profile_id"];

echo "fast count: " . $_SESSION[$sessionName]["fastCount"] . "<p>";

//check to see what the activity speed is for this user
if (!isset($_SESSION[$sessionName])) {
	
	//the session doesn't exist yet
	$site_usage["startTime"] = time();
	$site_usage["lastTime"] = time();
	$site_usage["fastCount"] = 1;

	//create the session
	$_SESSION[$sessionName] = $site_usage;	

	echo "CREATED";
} 

//
$rightNow = time();
$lastAccess = $_SESSION[$sessionName]["lastTime"];

$howManySecondsFromLastAccess = $rightNow - $lastAccess;

echo "how many: ".$howManySecondsFromLastAccess;

//check how long from the last page access for this user
//if less than X seconds
if ($howManySecondsFromLastAccess < $howManySecondsFromLastAccessLimit) {
	
	//increment the access counter for aggressive behavior
	$_SESSION[$sessionName]["fastCount"]++;
	$_SESSION[$sessionName]["lastTime"] = time();

	if ($_SESSION[$sessionName]["fastCount"] > $maxFastCountInt) {
		
		//make them wait a little bit...
		sleep($sleepPunisherInt);
		
		//reset the count 
		$_SESSION[$sessionName]["fastCount"] = 1;
	}
} else {

	//reset the access time
	$_SESSION[$sessionName]["lastTime"] = time();
}
die;

*/

?>