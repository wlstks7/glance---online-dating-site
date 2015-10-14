<?php  

//Include providing session parms

//YOUR DOMAIN GOES HERE
$session_domain = ''; //i.e. www.glancedate.com

ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.cookie_secure', false);
ini_set('session.use_only_cookies', true);

//set the session for one day, only this domain, secure only and only available on https
session_set_cookie_params(86400, '/', $session_domain, true, true);
session_start();

if (!isset($_SESSION['last_regeneration'])) {
	$_SESSION['last_regeneration'] = 1;
}

if (++$_SESSION['last_regeneration'] >= 20) {
    $_SESSION['last_regeneration'] = 0;
    session_regenerate_id(true);
}

?>