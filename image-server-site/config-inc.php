<?php

error_reporting(E_ALL ^ E_NOTICE);

$upload_directory = '/var/upload/';
$max_upload_size = 10 * 1024 * 1024;
$max_upload_size_str = "10 MB";

$image_size = $_POST['image_size'];
$profile_id = $_POST['profile_id'];
$post_secureKey = $_POST['securekey'];

//SECURE KEY - this is a key that is passed to image server during upload to authenticate this server
//BE SURE you change this to something unique and complex IF you choose to place this server
//somewhere public AND you will need to place the SAME key in the Glance web server's uploader-inc.php file
$secureKey = ""; //1234566

?>