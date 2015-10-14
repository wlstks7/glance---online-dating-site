<?php
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$headers = apache_request_headers();
if(isset($headers['If-Modified-Since'])) {
  if(strtotime($headers['If-Modified-Since']) < time() - 600) {
    header('HTTP/1.1 304 Not Modified');
    exit;
  }
}
?>