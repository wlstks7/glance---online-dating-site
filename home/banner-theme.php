<?php  
require('../def-inc.php');

?>
<html>
<head>
	<title>Theme Banners</title>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<style type="text/css">
		html, body {
		    background-color: #2e2e2e;
		    margin: 0;
		    overflow-x: hidden;
		    padding: 5px;
		    text-align: center;
		}

		.clickable {
			cursor: pointer;
		}
	</style>
</head>
<body>

<div class="ui small images">
 
	<?php  
		$dir = SITEPATH . "/assets/thumbs/";

		$thumbs = array();

		if (is_dir($dir)) {
		    if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
		        	if ($file != ".." && $file != "." && $file != "index.php") {
		        		array_push($thumbs, $file);
		        	}
		        }
		        closedir($dh);
		    }
		}

		sort($thumbs);

		foreach ($thumbs as $key => $val) {

    		$a = '<img class="clickable" data-url="../assets/banners/' . $val . '" src="../assets/thumbs/' . $val . '">';
    		echo $a;	
		}

	?>
</div>





<script src='../assets/jquery.min.js?=11115'></script>
<script src="../assets/semantic/semantic.min.js?=11115"></script>
<script src='../assets/banner-theme.js'></script>
</body>
</html>