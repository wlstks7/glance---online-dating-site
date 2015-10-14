<?php
header("Content-Type: application/json");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../mailgun-inc.php');
require_once('../log-inc.php');
require_once('../account-inc.php');
require_once('../uploader-inc.php');
require_once(SITEPATH . '/func/auth.php');

$profile_id = $_SESSION["loggedUser"]['profile_id'];

require_once(dirname(__FILE__) . '/uploader.php');

$headers = apache_request_headers();

$image_size = "0";

foreach ($headers as $header => $value) {

	if ($header == "image_sizes") {
		$image_size = $value;
	}
}

if ($image_size == "0") {
	//could not get the image size requested from header.
	
	echo json_encode(array('success' => false, 'msg' => "Hi. 1001"));
	die;
}
	
//define file types allowed
$valid_extensions = array('png', 'jpg', 'jpeg');

//store this file temporarily
$uploader = new FileUpload('uploadfile');

	$ext = $uploader->getExtension(); // Get the extension of the uploaded file

	$filename =  uniqid() . '.' . $ext;
	$filename = strtolower($filename);

	$uploader->newFileName = $filename;

	// Handle the upload
	$result = $uploader->handleUpload($upload_dir, $valid_extensions);

if (!$result) {
 
  exit(json_encode(array('success' => false, 'msg' => $uploader->getErrorMsg())));  
  die;
}

/*

validate this image size 

*/

//get the name of the file location
$workingFile = $upload_dir . $filename;

//check for max file size
$uploaded_filesize = filesize($workingFile);

if ($uploaded_filesize > $max_upload_size) {
	
	unlink($workingFile);
	$f = format_size($uploaded_filesize);
	
	$msg = "The image size cannot be greater than " . $max_upload_size_str . " - this file is: " . $f;
	echo json_encode(array('success' => false, 'msg' => $msg));
	die;
}

$minWidth = 700;

//get the size of the file
$size = getimagesize($workingFile);

//get the width of the image
$imageWidth = $size[0];

//eval for proper mime type
$mime = $size["mime"];

if ($mime != "image/jpeg" && $mime != "image/png") {
//if ($mime != "image/jpeg") {
	
	//unsupported filetype
	//kill the file
	unlink($workingFile);
	$msg = "This image must be a JPG or PNG.";
	echo json_encode(array('success' => false, 'msg' => $msg));
	die;
}

//eval this image meets the minimum requirements
if ($imageWidth < $minWidth) {

	//kill the file
	unlink($workingFile);
	$msg = "This image must be at least " . $minWidth . " pixels wide.";
	echo json_encode(array('success' => false, 'msg' => $msg));
	die;
}

//send the file to the DS server
//TODO: GET LOGIN FROM HAPPY CLUB UPDATER SETUP LOGIN

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, $ds_server);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600);
curl_setopt($ch, CURLOPT_TIMEOUT, 600);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$fn = $upload_dir . $filename;
$size = getimagesize($fn);
$mime = $size["mime"];

$post_array = array(
    //"my_file"=>"@". $upload_dir . $filename,
    "my_file"=> new CURLFile($fn, $mime, basename($fn)),
    "upload"=>"Upload",
    "image_size"=>$image_size,
    "profile_id"=>$profile_id,
    "securekey"=>$secureKey
);

curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
$response = curl_exec($ch);

//{"status":"SUCCESS","msg":"12345559455ca1b5a2.jpg"}
$response = json_decode($response);

try {

	//delete the file
	unlink($upload_dir . $filename);

} catch (Exception $e) {}

if ($response->status !="SUCCESS") {
	
	echo json_encode(array('success' => false, 'msg' => $response->msg));

} else {

	echo json_encode(array('success' => true, 'filename' => $server_link . $response->msg, 'conversion_log' => $response->conversion_log, 'file_size_height' => $response->file_size_height));
}


function format_size($size) {
      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      if ($size == 0) { return('n/a'); } else {
      return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
}

?>