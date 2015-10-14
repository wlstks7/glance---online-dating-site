<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
//require(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];
$postStart = trim($_POST['postPageIndex']);

if ( !is_numeric($postStart) ) {
	$postStart = "0";
}

$offset = $postStart * 20;

$_parameterArray = array(
	':dest_profile_id' => $profile_id,
	':deleted' => "0"
);

$_query = <<<EOT
		
	SELECT
		messages.id,
		messages.msg_read,
		messages.visitor_profile_id,
		LEFT(messages.msg, 50) AS msg_excerpt,
		CASE DATE_FORMAT(messages.datestamp, '%m/%d/%Y')
	WHEN DATE_FORMAT(NOW(), '%m/%d/%Y') THEN
		"today"
	WHEN DATE_FORMAT(subdate(NOW(), 1), '%m/%d/%Y') THEN
		"yesterday"
	ELSE
		DATE_FORMAT(messages.datestamp, '%m/%d/%Y')
	END AS activity_date,
	 profile.firstName,
	 profile.profileImage,
	 profile.gender,
	 profile.userName
	FROM
		profile
	INNER JOIN messages ON profile.profile_id = messages.visitor_profile_id
	WHERE 
		messages.dest_profile_id = :dest_profile_id
	AND messages.deleted = :deleted
	
	AND (messages.hidden = :deleted AND messages.dest_profile_id = :dest_profile_id)
	ORDER BY messages.id desc
	LIMIT 20
	OFFSET $offset

EOT;

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
		'msg' => count($results),
		'results' => $results
	);

} catch(PDOException $e) {

	$response = array(
		'api' => apiName, 
		'version' => apiVersion, 
		'status' => 'fail', 
		'error' => 'true', 
		'msg' => 'Error 100220. Please seek support.', 
		'results' => 'none'
	);

	$msg = "MESSAGE GET FAIL - Module: func/messages-get.php - Had an issue with the DB throwing an exception - while attempting to get messages for profile_id: " . $profile_id;
	__emailAlert($msg, ALERT_EMAIL_ADDRESS);
}

respond($response);

die;


function cb($content){

  if(!mb_check_encoding($content, 'UTF-8')
   OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {

    $content = mb_convert_encoding($content, 'UTF-8');

  }
  return $content;
}
?>