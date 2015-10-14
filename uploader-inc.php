<?php  

/*

Uploaded images are passed to an image server (or servers behind load balancer) that does all the work resizing and sending to S3. If you are
using AWS you should keep these private (behind your firewall) and have the web servers connect on your private network (instead of public addressed)

If you choose to make these servers public, you can pass a secure key during POST to authenticate the web server (below)

The image server accepts posted images and returns JSON with the image location on S3

 */

//this is the link to the image server for post uploads
$ds_server = 'http://yourserver/image_handler.php'; //i.e. http://ip-172-1-4-10.us-west-2.compute.internal/image_handler.php

//these links identify the S3 domain you setup on AWS. They MUST be setup like the examples below
$server_link = 'https://images.yoursite.com/'; //i.e. https://images.glancedate.com/
$bucketURL = "https://images.yoursite.com/"; //i.e. https://images.glancedate.com/
$bucket = "images.yoursite.com"; //i.e. images.glancedate.com

//this is the UPLOAD dir on your web server. Be sure the web user/group has write access to that folder
$upload_dir = '/var/upload/';

//This defines the max uploaded file size allowed by your image server
//max upload is 5mb
$max_upload_size = 5 * 1024 * 1024;
$max_upload_size_str = "5 MB";

//SECURE KEY - this is a key that is passed to image server during upload to authenticate this server
//BE SURE you change this to something unique and complex IF you choose to place this server
//somewhere public AND you will need to place the SAME key in the server's config-inc
$secureKey = ""; //1234566

?>