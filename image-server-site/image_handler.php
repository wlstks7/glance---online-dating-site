<?php  
header("Content-Type: application/json");
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once("config-inc.php");

//check if form submitted
if (isset($_POST['upload'])) {

    if (!empty($_FILES['my_file'])) {

			//check for image submitted
    		if ($_FILES['my_file']['error'] > 0) {

            $response = array(
				'status' => 'fail', 
				'msg' => "Hmmm... I am getting an error. Please try again later. - " . $_FILES["my_file"]["error"]
			);

			echo json_encode($response);

			die;
        
        } else {

			//move temp file to our server
			move_uploaded_file($_FILES['my_file']['tmp_name'], $upload_directory . $_FILES['my_file']['name']);
			
			//create file name from file
			$fileName = $_FILES['my_file']['name'];
        }

    } else {
	    
	        $response = array(
				'status' => 'fail', 
				'msg' => "File not uploaded."
			);

			echo json_encode($response);

			die;
    }
}

//get the name of the file location
$workingFile = $upload_directory . $fileName;

//check for max file size
$uploaded_filesize = filesize($workingFile);

if ($uploaded_filesize > $max_upload_size) {
	
	unlink($workingFile);
	$f = format_size($uploaded_filesize);
	
	$msg = "The image size cannot be greater than " . $max_upload_size_str . " - this file is: " . $f;

	$response = array(
		'status' => 'fail', 
		'msg' => $msg
	);

	echo json_encode($response);

	die;
}

//get the size of the file
$size = getimagesize($workingFile);

//post image
$minWidth = 700;

$file_process_width = 700;

$file_th_process_width = 200;

//max file size for posts is 125k
$max_file__size = 100 * 1024;

//max size for post thumbs is 25k
$max_file__size_th = 25 * 1024;

//get the width of the image
$imageWidth = $size[0];

//eval for proper mime type
$mime = $size["mime"];

if ($mime != "image/jpeg" && $mime != "image/png") {
	
	//unsupported filetype
	//kill the file
	unlink($workingFile);

	$msg = "This image is not the right file type. It needs to be JPG or PNG.";

	$response = array(
		'status' => 'fail', 
		'msg' => $msg
	);

	echo json_encode($response);

	die;
}

//eval if this image meets the minimum requirements
if ($imageWidth < $minWidth) {

	//kill the file
	unlink($workingFile);

	$msg = "This image must be at least " . $minWidth . " pixels wide.";

	$response = array(
		'status' => 'fail', 
		'msg' => $msg
	);

	echo json_encode($response);

	die;
}

//once again check for the correct file type
if ($mime == "image/jpeg") {
	
	$file_ext = ".jpg";
	
} elseif ($mime == "image/png") {
	
	$file_ext = ".png";
}

//create new file name
$fileuniq = uniqid();

$output_filename = $profile_id . $fileuniq . $file_ext;

//create the full links to the public folder
$file_to_process = $upload_directory . $output_filename;

//$file_to_processth = $public_folder . $output_filename_thumb;

//process post image
resizeImage($workingFile, $file_process_width, $max_file__size, $file_to_process, $mime, 100);

//check to see if the file size exceeds our max file size
$conversion_log = checkImage($workingFile, $file_process_width, $max_file__size, $file_to_process, $mime, 100);

//get the height of the 
$size = getimagesize($file_to_process);

$image_type = "Post Image";

require_once("s3-uploader-inc.php");

//kill working orig files
unlink($workingFile);
unlink($file_to_process);

$response = array(
	'status' => 'SUCCESS', 
	'conversion_log' => $conversion_log, 
	'file_size_height' => $size[1], 
	'msg' => $output_filename
);

echo json_encode($response);

die; 

/*


FUNCTIONS


*/
function format_size($size) {
      $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
      if ($size == 0) { return('n/a'); } else {
      return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
}

function checkImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime){

	//calculate size of the new image file 
	$the_size = filesize($output_filename);

	//is the new file size meet our max size requirements?
	if ($the_size <= $max_filesize) {
		
		////perfect... return the filename
		return "100% - the size: " . $the_size . " max-size: " . $max_filesize;
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 92);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 92%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "93% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 90);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 90%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "90% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 86);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 86%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "86% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 85);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 85%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "85% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 84);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 84%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "84% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 83);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 83%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "83% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 82);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 82%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "82% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 80);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 80%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "80% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 79);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 79%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "79% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 78);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 78%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "78% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	//the size is still too big
	if ($the_size > $max_filesize) {
		
		unlink($output_filename);

		resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 77);

		//calculate size of the new image file 
		$the_size = filesize($output_filename);

		error_log("File size: " . $the_size . " - Checking at 77%");

		//is the new file size meet our max size requirements?
		if ($the_size <= $max_filesize) {
			
			////perfect... return the filename
			return "77% - the size: " . $the_size . " max-size: " . $max_filesize;
		}
	}

	unlink($output_filename);

	//just return it with 75... anything less and the image will be wasted
	resizeImage($workingFile, $file_process_width, $max_filesize, $output_filename, $mime, 75);

	error_log("File size: " . $the_size . " - giving up at 75%");

	return "75% - the size: " . $the_size . " max-size: " . $max_filesize;

}

function resizeImage($filename, $desired_width, $max_filesize, $output_filename, $mime, $compRatio){

	// content type
	header('Content-Type: ' . $mime);

	list($width_orig, $height_orig) = getimagesize($filename);

	$ratio_orig = $width_orig/$height_orig;
	$height = $desired_width/$ratio_orig;

	//magic
	$imagick = new Imagick($filename);

	// resize the original image to size of editor
	$imagick->resizeImage(700, 0, imagick::FILTER_LANCZOS, .7, false);

	try {	
		//read EXIF header from uploaded file
		$exif = exif_read_data($filename);

		//fix the Orientation if EXIF data exist
		if(!empty($exif['Orientation'])) {
			
		    switch($exif['Orientation']) {
		        case 8:
		            //$image_p = imagerotate($image_p,90,0);
		            $imagick->rotateImage(new ImagickPixel('#00000000'), -90);
		            break;
		        case 3:
		            //$image_p = imagerotate($image_p,180,0);
		            $imagick->rotateImage(new ImagickPixel('#00000000'), 180);
		            break;
		        case 6:
		            $imagick->rotateImage(new ImagickPixel('#00000000'), 90);
		            //$image_p = imagerotate($image_p,-90,0);
		            break;
		    }
		}
	} catch (Exception $e) {
		
	}

	$imagick->stripImage();

	$imagick->setImageCompressionQuality($compRatio);

	$imagick->setImageFormat ("jpeg");

	$imagick->writeImage($output_filename);
}

?>