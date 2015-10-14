<?php  

require('../session-inc.php');
require('../def-inc.php');

$_SESSION['ion_user_authenticated'] = '';
$_SESSION['ion_cookie_timestamp'] = '';
$_SESSION["loggedUser"] = array();

//detroy user sessions
unset( $_SESSION["polling_activity_" . $loggedUser["profile_id"]]);
unset( $_SESSION["polling_latest_activity_array" . $loggedUser["profile_id"]]);
unset( $_SESSION["ion_user_authenticated"]);
unset( $_SESSION["ion_cookie_timestamp"]);
unset( $_SESSION["loggedUser"]);

$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

header('Location: ' . $this_site . "login/");

?>