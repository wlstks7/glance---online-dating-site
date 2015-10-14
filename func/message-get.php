<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];
$message_id = trim($_POST['message_id']);
$postStart = trim($_POST['conversationPageIndex']);

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

$offset = $postStart * 20;

//we need to get both of the profile IDs for this message 
$_parameterArray = array(
	':dest_profile_id' => $profile_id,
	':message_id' => $message_id,
	':deleted' => "0"
);

//get the message and find out who it came from
$_query = <<<EOT
		
	SELECT 
		messages.visitor_profile_id,
		messages.msg
	FROM 
		messages
	WHERE 
		messages.dest_profile_id = :dest_profile_id
	AND
		messages.id = :message_id
	AND
		messages.deleted = :deleted
	limit 1

EOT;

try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ( count($results) == 0 ) {
		
		//nothing returned... most likely someone trying to see another profile's message by sending a rogue message ID
		//or a message that has been deleted

		$response = array(
			'api' => apiName, 
			'version' => apiVersion, 
			'status' => 'success', 
			'error' => 'false', 
			'msg' => 'This message is not longer available.', 
			//'q' => $_query, 
			'results' => ""
		);

		respond($response);

		die;

	} else {

		$visitor_profile_id = $results[0]["visitor_profile_id"];
		$thisMessage = $results[0]["msg"];
	}
}
catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'I am having trouble getting that message. Please try again.', 
		'results' => 'none'
	);

	respond($response);

	die;
}

$_parameterArray = array(
	':dest_profile_id' => $profile_id,
	':message_id' => $message_id,
	':msg_read' => "1"
);

//mark this message as read
$_query = <<<EOT
		
		UPDATE messages SET
			msg_read = :msg_read
		WHERE 
			messages.dest_profile_id = :dest_profile_id
		AND
			messages.id = :message_id

EOT;

try {

	$db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
	$stmt = $db->prepare($_query); 
	$stmt->execute($_parameterArray);

} catch(PDOException $e) {

	
}

$_parameterArray = array(
	':visitor_profile_id' => $visitor_profile_id,
	':dest_profile_id' => $profile_id,
	':message_id' => $message_id,
	':hidden' => "1",
	':deleted' => "0"
);

//get the conversation
$_query = <<<EOT
		
	SELECT 
		messages.id,
		messages.visitor_profile_id, 
		messages.dest_profile_id, 
		messages.msg_read, 
		messages.msg, 
		CASE messages.dest_profile_id
		WHEN :visitor_profile_id THEN
			"canDelete"
		ELSE
			"canHide"
		END AS canDelete,
		CASE DATE_FORMAT(messages.datestamp, '%m/%d/%Y')
		WHEN DATE_FORMAT(NOW(), '%m/%d/%Y') THEN
			"today"
		WHEN DATE_FORMAT(subdate(NOW(), 1), '%m/%d/%Y') THEN
			"yesterday"
		ELSE
			DATE_FORMAT(messages.datestamp, '%m/%d/%Y')
		END AS activity_date,
		`profile`.userName, 
		`profile`.firstName,
		`profile`.gender,
		`profile`.profileImage

	FROM `profile` INNER JOIN messages ON `profile`.profile_id = messages.visitor_profile_id
	
	WHERE 
	
	NOT 
		(messages.hidden = :hidden AND messages.dest_profile_id = :dest_profile_id)

	AND NOT 
		(messages.hidden_sender = :hidden AND messages.visitor_profile_id = :dest_profile_id)
		
	AND
		(
			(messages.visitor_profile_id = :visitor_profile_id AND messages.dest_profile_id = :dest_profile_id)
			OR
			(messages.dest_profile_id = :visitor_profile_id AND messages.visitor_profile_id = :dest_profile_id)
		)
	AND
		messages.deleted = :deleted


	ORDER BY messages.id desc
	LIMIT 20
	OFFSET $offset


EOT;

//get the data
try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

	$stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

	$stmt->execute($_parameterArray);

	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($results as $key => $value) {
		
		//check for no picture
		$img = $value["profileImage"];

		if (trim($img) == "") {
			
			//get gender
			$gender = $value["gender"];

			if ($gender == "guy") {
				
				$profileNoPic = "../assets/nopic_guy.png";

			} else {

				$profileNoPic = "../assets/nopic_gal.png";
			}

			$results[$key]["profileImage"] = $profileNoPic;
		}
	}
	
	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'success', 
		'error' => 'false', 
		'count' => count($results), 
		'pagetoken' => uniqid() . "-" . $visitor_profile_id, 
		'msg' => $thisMessage, 
		//'q' => $_query, 
		'results' => $results
	);
}
catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'I am having trouble getting this message. Please try again.', 
		'results' => 'none'
	);

	$msg = "MESSAGE GET FAIL - Module: func/message-get.php - Had an issue with the DB throwing an exception - while attempting to get message for profile_id: " . $profile_id;
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;

?>