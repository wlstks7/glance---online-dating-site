<?php  

// Include that manages all site activity during navigation

require_once(SITEPATH . '/session-inc.php');
require_once(SITEPATH . '/data-inc.php');
require_once(SITEPATH . '/mailgun-inc.php');
require_once(SITEPATH . '/log-inc.php');
require_once(SITEPATH . '/func/auth.php');

//update last online time
try {
	
		//attempt to get the last login
		$last_login = $_SESSION["loggedUser"]["last_online"];

		//echo $last_login . " last login <p>";

		if (is_numeric($last_login)) {
			
			//get the seconds from last request
			$secondsFromLastRequest =  time() - $last_login;

			//echo $secondsFromLastRequest . " secondsFromLastRequest <p>";

			//if we are more than 4 minutes since last update... change the profile
			if ( $secondsFromLastRequest > 240 ) {
				
				//echo "renewed <p>";

				//renew the activity		
				activity_updateLastOnline($_SESSION["loggedUser"]["profile_id"]);
			}
		} else {

			//echo "NULL - renewed <p>";

			activity_updateLastOnline($_SESSION["loggedUser"]["profile_id"]);
		}

} catch (Exception $e) {
	
	activity_updateLastOnline($_SESSION["loggedUser"]["profile_id"]);
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

function activity_canIPoll_pagecount($profile_id){
	global	$loggedUser,
			$pollingActivityLimit;

	///only poll for recent activity every X request

	//create the session name based on the user profile id		
	$sessionName = "polling_activity_" . $loggedUser["profile_id"];
	$sessionNamePolledArray = "polling_latest_activity_array" . $loggedUser["profile_id"];

	//has the session been created yet?
	if (!isset($_SESSION[$sessionName])) {
		
		//get a random number so the polling interval isn't static and predictable 
		$pollcount = $pollingActivityLimit[array_rand($pollingActivityLimit)];
		
		//the session doesn't exist yet
		$polling_activity["polling_count"] = 1;
		$polling_activity["pollingActivityLimit"] = $pollcount;

		//create the session
		$_SESSION[$sessionName] = $polling_activity;	

		//poll for latest activity
		$_SESSION[$sessionNamePolledArray] = activity_recentActivity($profile_id);

		return $_SESSION[$sessionNamePolledArray];
	} 

	if ( $_SESSION[$sessionName]["polling_count"] > $_SESSION[$sessionName]["pollingActivityLimit"] ) {
		
		//destroy the session
		unset($_SESSION[$sessionName]);
		return $_SESSION[$sessionNamePolledArray];

	} else {

		$_SESSION[$sessionName]["polling_count"]++;
		return $_SESSION[$sessionNamePolledArray];
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

function activity_canIPoll($profile_id){
	global	$loggedUser,
			$pollingActivityLimit;

	//create the session name based on the user profile id		
	$sessionName = "polling_activity_" . $loggedUser["profile_id"];
	$sessionNamePolledArray = "polling_latest_activity_array" . $loggedUser["profile_id"];

	//has the session been created yet?
	if (!isset($_SESSION[$sessionName])) {
		
		//set the start time for this request
		$pollStartTime = time();
		
		//add to array for session
		$polling_activity["pollStartTime"] = $pollStartTime;

		//create the session
		$_SESSION[$sessionName] = $polling_activity;	

		//poll for latest activity
		$_SESSION[$sessionNamePolledArray] = activity_recentActivity($profile_id);

		return $_SESSION[$sessionNamePolledArray];
	} 

	$secondsFromLastRequest =  time() - $_SESSION[$sessionName]["pollStartTime"];

	if ( $secondsFromLastRequest > 120 ) {
		
		//destroy the session
		unset($_SESSION[$sessionName]);
		return $_SESSION[$sessionNamePolledArray];

	} else {

		return $_SESSION[$sessionNamePolledArray];
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

function activity_likesGlancesCount($profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':dest_profile_id' => $profile_id
	);

	//update the line with the user data
	$_query = <<<EOT
			
		SELECT
			COUNT(id) as glanceCount,

			(SELECT
				COUNT(DISTINCT visitor_profile_id)
			FROM
				glances
			WHERE 
				dest_profile_id = :dest_profile_id
			) as glancePeopleCount,


			(SELECT
				COUNT(id)
			FROM
				likes
			WHERE 
				dest_profile_id = :dest_profile_id
			) as likeCount,
			(SELECT
				COUNT(DISTINCT visitor_profile_id)
			FROM
				likes
			WHERE 
				dest_profile_id = :dest_profile_id
			) as likePeopleCount

		FROM
			glances
		WHERE 
			dest_profile_id = :dest_profile_id

EOT;

	$ret = activity_query_select($_query, $_parameterArray);
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

function activity_messageCount($profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':dest_profile_id' => $profile_id,
		':deleted' => "0",
		':msg_read' => "0"
	);

	//update the line with the user data
	$_query = <<<EOT
			
		SELECT
			COUNT(id) as messageCount
		FROM
			messages
		WHERE 
			dest_profile_id = :dest_profile_id
		AND 
			deleted = :deleted

		AND 
			(messages.hidden = :deleted AND messages.dest_profile_id = :dest_profile_id)

		AND 
			msg_read = :msg_read

EOT;

	$ret = activity_query_select($_query, $_parameterArray);
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

function activity_recentActivity($profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':dest_profile_id' => $profile_id
	);

	//update the line with the user data
	$_query = <<<EOT
			
		SELECT
			activity.did_what,
			activity.datestamp,
			CASE DATE_FORMAT(activity.datestamp, '%m/%d/%Y')
		WHEN DATE_FORMAT(NOW(), '%m/%d/%Y') THEN
			"today"
		WHEN DATE_FORMAT(subdate(NOW(), 1), '%m/%d/%Y') THEN
			"yesterday"
		ELSE
			DATE_FORMAT(activity.datestamp, '%m/%d/%Y')
		END AS activity_date,
		 profile.firstName,
		 profile.profileImage,
		 activity.visitor_profile_id,
		 profile.gender,
		 profile.userName
		FROM
			profile
		INNER JOIN activity ON profile.profile_id = activity.visitor_profile_id
		WHERE 
			dest_profile_id = :dest_profile_id
		ORDER BY activity.id desc
		LIMIT 8

EOT;

	$ret = activity_query_select($_query, $_parameterArray);
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

function activity_recentFriendActivity($profile_id){

	//this logs the glance to the profile
	$_parameterArray = array(
		':dest_profile_id' => $profile_id
	);

	//update the line with the user data
	$_query = <<<EOT
		
		SELECT DISTINCT
			CASE DATE_FORMAT(friend_updates.datestamp, '%m/%d/%Y')
			WHEN DATE_FORMAT(NOW(), '%m/%d/%Y') THEN
				"today"
			WHEN DATE_FORMAT(subdate(NOW(), 1), '%m/%d/%Y') THEN
				"yesterday"
			ELSE
				DATE_FORMAT(friend_updates.datestamp, '%m/%d/%Y')
			END AS activity_date,
			`profile`.id,
			`profile`.profile_id,
			`profile`.firstName,
			`profile`.userName,
			`profile`.profileImage,
			`profile`.gender,
			friend_updates.did_what
		FROM
			likes
		INNER JOIN friend_updates ON likes.dest_profile_id = friend_updates.profile_id
		INNER JOIN `profile` ON `profile`.profile_id = friend_updates.profile_id
		WHERE
			likes.visitor_profile_id = :dest_profile_id
		ORDER BY
			friend_updates.id DESC
		limit 15

EOT;

	$ret = activity_query_select($_query, $_parameterArray);
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

function activity_updateLastOnline($profile_id){

	$last_online = time();

	$_SESSION["loggedUser"]["last_online"] = $last_online;

	$_parameterArray = array(
		':profile_id' => $profile_id,
		':last_online' => $last_online
	);

	$_query = <<<EOT
			
		UPDATE profile 
		SET
			last_online = :last_online
		WHERE 
			profile_id = :profile_id

EOT;

	activity_query_insert($_query, $_parameterArray);
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

function activity_insertPLACEHOLDER($visitor_profile_id, $dest_profile_id, $did_what, $activity_description){

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

	//update the line with the user data
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

	activity_query_insert($_query, $_parameterArray);

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

function activity_query_select($_query, $_parameterArray){

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

function activity_query_insert($_query, $_parameterArray){

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

			$msg = "Add failed - Module: activity_query_insert() - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode() . " query " . $_query;

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

		$msg = "Add failed - Module: activity_query_insert() - Had an issue with the DB throwing an exception. - query: " . $_query ;
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);
	}

	return $response;
}

?>