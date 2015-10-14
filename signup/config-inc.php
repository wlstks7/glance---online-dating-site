<?php
/*header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate"); //No Caching
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
*/
header("Content-Type: application/json");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start(); //for connection monitoring

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^ E_NOTICE);

$http_base = "http://app.com/i/?";









/* CONFIG******************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
**************************************************************************/

date_default_timezone_set('America/Chicago');

//*********************************

//API identifiers
const apiName = 'Ion';
const apiVersion = '1.0';

//this is the default value (in seconds) for expiring user authentication keys
const keyExpire = 86400;

//db connection info -- you will need to change this 
const dbName = 'ink';
const dbUser = 'aio_user';
const dbPass = 'dxKa6SyTSQzNBMRL';

$_currentUser = array();

//load modules
//require 'log.php';

/*
FUNCTIONS******************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
***************************************************************************
**************************************************************************/

function respond($output) {

	/*
		standard response:
	
			apiName
			version
			status
			error
			msg 
			results

	*/

	echo json_encode($output);
}

function sanitize( $e ){

	//place any sanitizing code here
	$cleaned = strip_tags($e);
	$cleaned = trim($cleaned);

	return $cleaned;
}

?>
