<?php  

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];
$search = trim($_POST["search"]);
$defaultSearch = trim($_POST["defaultSearch"]);

updateProfileSearch($search, $profile_id);

if ($defaultSearch != "") {
	
	updateProfileDeafultSearch($defaultSearch, $profile_id);
}

?>