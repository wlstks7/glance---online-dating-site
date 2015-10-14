<?php  

//Include to monitor use/abuse of system.

/*

	This module is unfinished and still a concept.

	It evaluates 2 types of access...
		- whitelisted : pages that have specific requirements (what posted, frequency, etc...)

		- everything else : monitoring page access to see if it is within the thresholds of reasonable usage. (people.. not bots)
							(still concept... unfinished but it's close)
 * */

//remove line to use
return false;
//remove line to use








require_once('def-inc.php');
require_once('data-inc.php');
require_once('mailgun-inc.php');

/*
********************************************
determine if this page is on the white list
********************************************
*/

	$thisPage = $_SERVER['REQUEST_URI'];

	//pages that require additional checks
	$pageWhiteList = array(
		'/func/account-update.php' => '_funcAccountUpdate'
	);

	$functionName = "";

	//test to see if this page is on the whitelist
	try {
		
		$functionName = $pageWhiteList[$thisPage];
		
	} catch (Exception $e) {}

	//if whitelisted... then run the function
	if (trim($functionName) != "") {

		call_user_func($functionName);

	} else {

		//$_GET connectivity... handle the request
		doGet();
	}

/*
**************************************************
END - determine if this page is on the white list
**************************************************
*/

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function doGet(){

	//time between requests... on average.. 30 seconds
	$numberOfRequestsPerHour = 100; 
	$reasonableNumberOfSecondsBetweenRequests = 15; //15 seconds between requests
	$reasonableTotalSeconds = $reasonableNumberOfSecondsBetweenRequests * $numberOfRequestsPerHour; //30 seconds in 100 requests
	$timeToExpire = 3600; //one hour

	$profile_id = $_SESSION["loggedUser"]['profile_id'];

	/*
	echo $_SESSION["loggedUser"]['get_request'];
	
	$_SESSION["loggedUser"]['get_request'] = "";
	die($_SESSION["loggedUser"]['get_request']);
	*/
	

	//check to see if this is the first request this session... if so.. create the get_request session tag
	if (!isset($_SESSION["loggedUser"]['get_request']) || trim($_SESSION["loggedUser"]['get_request']) == "") {
		
		//check to see if there is a value in lastGetRequest... if so.. it was placed there
		//during abuse.. so we want to eval it and see if it was in the last hour
		//this is here in case the abuser kills the session and trys again.

		//

		//get value from lastGetRequest in profile
		$lastGetRequest = doGet_getLastRequest($profile_id);
		$lastGetRequest = $lastGetRequest['results'];
		$lastGetRequest = $lastGetRequest[0]["lastGetRequest"];

		if ( trim($lastGetRequest) != "" ) {
			
			//there is data from a previous abuse here
			//let's take a look and see if it's within the last hour.

			$timeStamps = "";

			try {
				$timeStamps = explode(",", $lastGetRequest);
			} catch (Exception $e) {}

			$time_now = time();

			//calc a value 60 minutes ago
			$timestamp_sixtyMinutesAgo = $time_now - $timeToExpire;

			//check if this is an array
			if (is_array($timeStamps)) {
			
				//how many values do we have?
				$arrayCount = count($timeStamps);

				if ($arrayCount == 0) {
					
					//nothing there
					//reset value with current time
					$_SESSION["loggedUser"]['get_request'] = time();
					doGet_getLastRequest_reset($profile_id);
				
				} else {
		
					//we have an array which means there is data from a previous abuse. 
					//let's check the first element for valid data

					//check to see if this is a number
					if (is_numeric($timeStamps[0])) {
						
						//create an integer value to compare
						$lastTimestamp = intval($timeStamps[0]);

						//check to see if this was in the last 60 minutes
						if ($timestamp_sixtyMinutesAgo > $lastTimestamp) {

							//not within the last 60 minutes
							//reset value with current time
							$_SESSION["loggedUser"]['get_request'] = time();
							doGet_getLastRequest_reset($profile_id);

						} else {

							//this is within the last 60 minutes and because we are here... this is because
							//of abuse... we need to act

							die("<h1>Service Unavailable</h1>");
						}

					} else {
						
						//not numeric... reset
						$_SESSION["loggedUser"]['get_request'] = time();
						doGet_getLastRequest_reset($profile_id);
					}
				}
				
			} else {

				//timestamps is not an array
				//reset value with current time
				$_SESSION["loggedUser"]['get_request'] = time();
				doGet_getLastRequest_reset($profile_id);
			}

		} else {

			//got nothing from the DB which means there is no previous abuse
			//reset value with current time
			$_SESSION["loggedUser"]['get_request'] = time();
			doGet_getLastRequest_reset($profile_id);
		}

	} else {

		//awesome... we have a session marker.. let's check it for abuse

		$lastGetRequest = $_SESSION["loggedUser"]['get_request'];

		/*//try to create array from data
		try {
			$timeStamps = explode(",", $lastGetRequest);
		} catch (Exception $e) {}

		$time_now = time();

		//calc a value 60 minutes ago
		$timestamp_sixtyMinutesAgo = $time_now - $timeToExpire;*/

		//check for valid value
		if (trim($lastGetRequest) != "") {
			
			$timeStamps = "";

			//try to create array from data
			try {
				$timeStamps = explode(",", $lastGetRequest);
			} catch (Exception $e) {}

			$time_now = time();

			//calc a value 60 minutes ago
			$timestamp_sixtyMinutesAgo = $time_now - $timeToExpire;

			//check if this is an array
			if (is_array($timeStamps)) {
			
				//how many values do we have?
				$arrayCount = count($timeStamps);

				if ($arrayCount == 0) {
					
					//nothing... this isn't a valid array
					//reset
					$_SESSION["loggedUser"]['get_request'] = time();
				
				} else if ($arrayCount <= $numberOfRequestsPerHour) {
					
					//update the DB each interval of 10... in case the attacker logs off/on and continues 
					if ($arrayCount == 10 || $arrayCount == 20 || $arrayCount == 30 || $arrayCount == 40) {
						
						doGet_addLastRequest($profile_id, $_SESSION["loggedUser"]['get_request']);
					}

					//there is less than X number array elements... let's check the first element for valid data

					//check to see if this is a number
					if (is_numeric($timeStamps[0])) {
						
						//create an integer value to compare
						$lastTimestamp = intval($timeStamps[0]);

						//check to see if this was in the last 60 minutes
						if ($timestamp_sixtyMinutesAgo > $lastTimestamp) {

							//this user hasn't requested in a while... reset 
							$_SESSION["loggedUser"]['get_request'] = time();

						} else {

							//this is within 60 minutes... let's just add our value to the array and post this update
							array_push($timeStamps, $time_now);

							$timeList = "";

							foreach ($timeStamps as $k => $v) {
								
								$timeList = $timeList . $v . ",";
							}

							$timeList = rtrim($timeList, ",");
							
							//update the session marker
							$_SESSION["loggedUser"]['get_request'] = $timeList;
						}

					} else {
						
						//not numeric... reset
						$_SESSION["loggedUser"]['get_request'] = time();
					}

				} else {

					//more than X number in the last 60 minutes... let's calculate the diff in seconds between each time
					$timeDiff = 0;
					$timeLastInt = $timeStamps[0];

					foreach ($timeStamps as $k => $v) {
						
						$_timeDiff = _funcAccountUpdateCalcSeconds($timeLastInt, $v);

						$timeLastInt = $v;

						$timeDiff = $timeDiff + $_timeDiff;
					}
					
					if ($timeDiff < $reasonableTotalSeconds) {
						
						//we have a problem

						//send a message... 
						if ($_SESSION["loggedUser"]["lastGetRequest"] != "notified") {
							
							$msg = "This user is requesting pages way too quickly. <pre>" . print_r($_SESSION["loggedUser"], true) . '</pre>';
							__emailAlert($msg, ALERT_EMAIL_ADDRESS);

							//set the notification marker
							$_SESSION["loggedUser"]["lastGetRequest"] = "notified";
						}

						doGet_addLastRequest($profile_id, $_SESSION["loggedUser"]['get_request']);

						//sleep the request for 60 seconds
						//make it suck to use the use the service
						//sleep(60);

						//or... just don't complete the request at all.
						//this isn't a user... it's a bot
						die("<h1>Service Unavailable</h1>");

						//more ideas... 

						//if it happens again... 
						//write an entry to the banned table for 30 minutes
						//add to trouble queue column on profile in DB
						//that way if they kill the cookie it's still there... 
						
						//if this happens 3 times with the trouble marker... we auto ban the account for 1 day

					} else {

						//this user is operating within reasonable thresholds
						//reset
						$_SESSION["loggedUser"]['get_request'] = time();
					}
				}
				
			} else {

				//reset
				$_SESSION["loggedUser"]['get_request'] = time();
			}

		} else {

			//reset
			$_SESSION["loggedUser"]['get_request'] = time();
		}
	}
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function doGet_getLastRequest($profile_id){

	$_parameterArray = array(
		':profile_id' => $profile_id
	);

	$_query = <<<EOT
			
		SELECT 
			lastGetRequest 
		FROM
			profile
		WHERE 
			profile_id = :profile_id
		LIMIT 1
EOT;

	$ret = connectivity_query_select($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function doGet_getLastRequest_reset($profile_id){

	//reset notification marker
	$_SESSION["loggedUser"]["lastAccountUpdate"] = "";

	$profile_id = $_SESSION["loggedUser"]['profile_id'];
	$time_now = time();

	$_parameterArray = array(
		':timeList' => '',
		':profile_id' => $profile_id,
	);

	$_query = <<<EOT
		
		UPDATE profile 
			SET lastGetRequest = :timeList
		WHERE 
			profile_id = :profile_id
EOT;

	$ret = connectivity_query_insert($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function doGet_addLastRequest($profile_id, $requestList){

	$_parameterArray = array(
		':timeList' => $requestList,
		':profile_id' => $profile_id,
	);

	$_query = <<<EOT
		
		UPDATE profile 
			SET lastGetRequest = :timeList
		WHERE 
			profile_id = :profile_id
EOT;

	$ret = connectivity_query_insert($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************

This is run before the account is updated
*/

function _funcAccountUpdate(){

	//time between updates... on average.. 90 seconds
	$reasonableTotalSeconds = 900;

	$profile_id = $_SESSION["loggedUser"]['profile_id'];

	//attempt to get the lastAccountUpdate array
	$lastAccountUpdate = connectivity_getLastWrite('lastAccountUpdate', $profile_id);
	$lastAccountUpdate = $lastAccountUpdate["results"];
	$lastAccountUpdate = $lastAccountUpdate[0]["lastAccountUpdate"];

	//check for valid value
	if (trim($lastAccountUpdate) != "") {
		
		$firstTimeStamp = "";
		$timeStamps = "";

		//try to create array from data
		try {
			$timeStamps = explode(",", $lastAccountUpdate);
		} catch (Exception $e) {}

		$time_now = time();

		//calc a value 60 minutes ago
		$timestamp_thirtyMinutesAgo = $time_now - 3600;

		//check if this is an array
		if (is_array($timeStamps)) {
		
			//how many values do we have?
			$arrayCount = count($timeStamps);

			if ($arrayCount == 0) {
				
				//reset
				_funcAccountUpdate_reset();
			
			} else if ($arrayCount <= 10) {

				//there is less than 10 array elements... let's check the first element for valid data

				//check to see if this is a number
				if (is_numeric($timeStamps[0])) {
					
					//create an integer value to compare
					$lastTimestamp = intval($timeStamps[0]);

					//check to see if this was in the last 60 minutes
					if ($timestamp_thirtyMinutesAgo > $lastTimestamp) {

						//this user hasn't updated in a while... reset 
						_funcAccountUpdate_reset();

					} else {

						//this is within 60 minutes... let's just add our value to the array and post this update
						array_push($timeStamps, $time_now);

						$timeList = "";

						foreach ($timeStamps as $k => $v) {
							
							$timeList = $timeList . $v . ",";
						}

						$timeList = rtrim($timeList, ",");
						
						_funcAccountUpdate_addValue($timeList, $profile_id, 'lastAccountUpdate');
					}

				} else {
					
					//not numeric... reset
					_funcAccountUpdate_reset();
				}

			} else {

				//more than 10 in the last 60 minutes... let's calculate the diff in seconds between each time
				$timeDiff = 0;
				$timeLastInt = $timeStamps[0];

				foreach ($timeStamps as $k => $v) {
					
					$_timeDiff = _funcAccountUpdateCalcSeconds($timeLastInt, $v);

					$timeLastInt = $v;

					$timeDiff = $timeDiff + $_timeDiff;
				}
				
				if ($timeDiff < $reasonableTotalSeconds) {
					
					//we have a problem

					//send a message... 
					if ($_SESSION["loggedUser"]["lastAccountUpdate"] != "notified") {
						
						$msg = "This user has updated their profile way too many times in the last hour. Sleeping. <pre>" . print_r($_SESSION["loggedUser"], true) . '</pre>';
						__emailAlert($msg, ALERT_EMAIL_ADDRESS);

						//set the notification marker
						$_SESSION["loggedUser"]["lastAccountUpdate"] = "notified";
					}

					//sleep the request for 30 seconds
					//sleep(30);

					//or... just don't complete the request at all.

					$response = array(
						'api' => apiName, 
						'version' => apiVersion, 
						'status' => 'fail', 
						'error' => 'true', 
						'msg' => 'Too many requests for update. Please try later.', 
						'results' => 'none'
					);
					
					respond($response);

					die;
					

					//more ideas... 

					//if it happens again... 
					//write an entry to the banned table for 30 minutes
					//add to trouble queue column on profile in DB
					//that way if they kill the cookie it's still there... 
					
					//if this happens 3 times with the trouble marker... we auto ban the account for 1 day

				} else {

					//this user is operating within reasonable thresholds
					//reset
					_funcAccountUpdate_reset();
				}
			}
			
		} else {

			//reset
			_funcAccountUpdate_reset();
		}

	} else {

		//reset
		_funcAccountUpdate_reset();
	}

	/*
		has this user updated account in last 30 minutes
			no.... reset
				place timestamp as starting array in lastUpdate field
			yes
				how many array items?
					less than 10
						add timestamp to array and update
					more than 10
						calculate seconds diff between updates and 
						compare to what is acceptable
							is more... 
								REPORT PUNISH RESET
							is less
								this user is probably operating within acceptable parms
									reset... allow to continue
	*/
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function _funcAccountUpdate_addValue($timeList, $profile_id, $columnName){

	$_parameterArray = array(
		':timeList' => $timeList,
		':profile_id' => $profile_id,
	);

	$_query = <<<EOT
		
		UPDATE profile 
			SET $columnName = :timeList
		WHERE 
			profile_id = :profile_id
EOT;

	$ret = connectivity_query_insert($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function _funcAccountUpdateCalcSeconds($firstTimestamp, $secondTimestamp){

	$val = 0;

	try {
		
		$val = $secondTimestamp - $firstTimestamp;

	} catch (Exception $e) {}

	return $val;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function _funcAccountUpdate_reset(){

	//reset notification marker
	$_SESSION["loggedUser"]["lastAccountUpdate"] = "";

	$profile_id = $_SESSION["loggedUser"]['profile_id'];
	$time_now = time();

	$_parameterArray = array(
		':timeList' => $time_now,
		':profile_id' => $profile_id,
	);

	$_query = <<<EOT
		
		UPDATE profile 
			SET lastAccountUpdate = :timeList
		WHERE 
			profile_id = :profile_id
EOT;

	$ret = connectivity_query_insert($_query, $_parameterArray);
	return $ret;
}
/*

	HELPER FUNCTIONS

*/

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function connectivity_getLastWrite($columnName, $profile_id){

	$_parameterArray = array(
		':profile_id' => $profile_id
	);

	$_query = <<<EOT
			
		SELECT 
			$columnName
		FROM
			profile
		WHERE 
			profile_id = :profile_id
		LIMIT 1
EOT;

	$ret = connectivity_query_select($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function connectivity_template(){

	//UPDATE-----------------------------------
	$_parameterArray = array(
		':DDDDD' => $DDDDDD
	);

	$_query = <<<EOT
			

EOT;

	$ret = connectivity_query_insert($_query, $_parameterArray);
	return $ret;

	//SELECT------------------------------------
	$_parameterArray = array(
		':DDDDD' => $DDDDD
	);

	$_query = <<<EOT
			
		

EOT;

	$ret = connectivity_query_select($_query, $_parameterArray);
	return $ret;
}

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function connectivity_query_select($_query, $_parameterArray){

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

/*
****************************************************
****************************************************
****************************************************
****************************************************
********************FUNCTION************************
****************************************************
****************************************************
****************************************************
****************************************************
****************************************************
*/

function connectivity_query_insert($_query, $_parameterArray){

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

			$msg = "Add failed - Module: connectivity_query_insert() - Had an issue with the DB and this was the error: " . $msg_error . $stmt->errorCode() . " query " . $_query;

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

		$msg = "Add failed - Module: connectivity_query_insert() - Had an issue with the DB throwing an exception -  query " . $_query;
		__emailAlert($msg, ALERT_EMAIL_ADDRESS);
	}

	return $response;
}

?>