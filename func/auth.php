<?php 

/*

	AUTH
*/
	
require_once(SITEPATH . '/session-inc.php');

// header("Expires: on, 01 Jan 1970 00:00:00 GMT");
// header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// header("Cache-Control: no-store, no-cache, must-revalidate");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");

//check for standing authentication from earlier login
if ( !isset( $_SESSION['ion_user_authenticated'] ) ) {
	
	header('Location: ' . $this_site . "login/");
	die;
}

//check for standing authentication from earlier login
if ( $_SESSION['ion_user_authenticated'] != 'true' ) {
		
	header('Location: ' . $this_site . "login/");
	die;
}

//check when the last user activity was recorded
$ion_cookie_timestamp_now = date_create();
$ion_cookie_timestamp_now =  date_timestamp_get($ion_cookie_timestamp_now);

//look at last session timestamp
$ion_cookie_timestamp = $_SESSION['ion_cookie_timestamp'];

//get the last time there was activity on this session
$lastActivity = $ion_cookie_timestamp_now - $ion_cookie_timestamp;

//if last activity on this session is more than 1200 seconds OR 20 minutes ago... make the user login
if (   $lastActivity > 3600   ) {
	
	//the user needs to log in again
	header('Location: ' . $this_site . "login/");
	die;
}

//refresh session activity
$ion_cookie_timestamp = date_create();
$_SESSION['ion_cookie_timestamp'] =  date_timestamp_get($ion_cookie_timestamp);

//build the array for the current user
$loggedUser = $_SESSION["loggedUser"];

$customer_id = $loggedUser["profile_id"];  //testing TODO remove
$_customer_firstName = $loggedUser["firstName"];
$_customer_email = $loggedUser["emailAddress"];

?>