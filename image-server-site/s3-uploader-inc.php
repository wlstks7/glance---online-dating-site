<?php  

require_once("aws-inc.php");
require_once 'aws/vendor/autoload.php';

use Aws\S3\S3Client;

// Instantiate the S3 client with your AWS credentials
$client = S3Client::factory(array(
	'region'  => 'us-west-2',
    'version' => 'latest',
    'credentials' => array(
        'key'    => aws_key,
        'secret' => aws_secret
    )
));

$result = $client->putObject(array(
    'Bucket'     => $bucket,
    'Key'        => $output_filename,
    'SourceFile' => $file_to_process,
    'Metadata'   => array(
        'profile_id' => $profile_id,
        'image_type' => $image_type
    )
));

// We can poll the object until it is accessible
$client->waitUntil('ObjectExists', array(
    'Bucket' => $bucket,
    'Key'    => $output_filename
));

?>