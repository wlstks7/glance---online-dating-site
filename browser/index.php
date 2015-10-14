<?php require_once('../def-inc.php'); ?>
<html>
<head>
	<title>You are using an old browser</title>
	<link rel="stylesheet" href="../assets/glance.css">
	<link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">
	<style type="text/css">

	h1 {
	    color: #444444;
	    font-family: arial;
	    font-size: 24px;
	    line-height: 30px;
	    margin: 0;
	    padding: 0;
	}
	h3 {
	    color: #444444;
	    font-family: arial;
	    font-size: 20px;
	    line-height: 28px;
	    margin: 0;
	    padding: 18px 0 0;
	}
	p {
		font-size: 16px;
		font-family: arial;
		color: #777777;
		line-height: 24px;
	}
	.container {
	    margin: 30px auto;
	    padding: 30px;
	    text-align: center;
	    width: 600px;
	}
	.logo {
	    margin: 0 0 20px;
	}
	.free-dating-site {
	    color: #777777;
	    font-family: arial !important;
	    font-size: 11px;
	    left: 103px;
	    letter-spacing: 1px;
	    line-height: 12px;
	    position: absolute;
	    top: 72px;
	}
	.browser-container {
	    height: 324px;
	    margin: auto;
	    padding: 0;
	    width: 448px;
	}
	.browser-link {
	    display: block;
	    float: left;
	    margin: 0;
	    padding: 0;
	    width: 140px;
	}
	.browser-icon {
		margin: 0;
		padding: 0;
	}
	.browser-icon > img {
		width: 72px;
	}
	.browser-title {
	    font-family: 'helvetica neue',arial;
	    font-size: 18px;
	    line-height: 20px;
	    margin: 0 auto;
	    padding: 0;
	    text-align: center;
	}
	.browser-text {
		font-family: 'helvetica neue',arial;
	    font-size: 14px;
	    line-height: 18px;
	    margin: 0 auto;
	    padding: 0;
	    text-align: center;
	}

	</style>
</head>
<body>
	<div class="container">
		<div class="logo">
        	<?php include('../logo-inc.php'); ?>
    	</div>
		<h1>You are using a browser that can't keep up</h1>
		<p>Glance was built to provide our users a beautiful and fun experience. The browser you are using is too old to take advantage of the awesomeness!</p>
		<p>The good news... you can update that browser right now for <strong>free</strong>.</p>

		<h3>Supported Browsers</h3>
		<p>Glance supports most modern browsers including the latest versions of Chrome, Firefox, Safari and Internet Explorer 10+</p>

		<div class="browser-container">
			<div class="browser-link">
				<div class="browser-icon">
					<img width="72px" height="72px" src="icon-chrome.png"> 
				</div>
				<div class="browser-title">Chrome</div>
				<div class="browser-text"><a href="http://www.google.com/chrome/">Get it here</a></div>
			</div>
			<div class="browser-link">
				<div class="browser-icon">
					<img width="72px" height="72px" src="icon-firefox.png"> 
				</div>
				<div class="browser-title">Firefox</div>
				<div class="browser-text"><a href="http://www.firefox.com/">Get it here</a></div>
			</div>
			<div class="browser-link">
				<div class="browser-icon">
					<img width="72px" height="72px" src="icon-ie.png"> 
				</div>
				<div class="browser-title">Internet Explorer</div>
				<div class="browser-text"><a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">Get it here</a></div>
			</div>
		</div>
	</div>
</body>
</html>