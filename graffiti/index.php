<?php  
/*

	/GRAFFITI/
	
*/
require_once('../no-cache-inc.php'); 
require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../account-inc.php');
require_once('../mailgun-inc.php');
require_once('../activity-inc.php');	
require_once('../recent-friend-activity.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../connectivity-inc.php'); 
	
	$graffitiActive = "active";

	$profile_id = $loggedUser["profile_id"];

	//build new token for the new form
	$_SESSION["form_token_account_edit_" . $profile_id] = md5("ion") . uniqid() . md5( uniqid() );
	$form_token = $_SESSION["form_token_account_edit_" . $profile_id];

	$_SESSION["post_seed"] = uniqid() . $profile_user["profile_id"];

?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Graffiti: <?php echo $loggedUser["firstName"]; ?></title>
    <link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> -->
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    
	<script src="../assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/semantic/dropdown.min.css">
	<link rel="stylesheet" href="../assets/semantic/icon.min.css">
	<link rel="stylesheet" href="../assets/colorbox.css">
	<link rel="stylesheet" href="../assets/search-content.css">
	<link rel="stylesheet" href="../assets/sticky.min.css">
	<link rel="stylesheet" href="../assets/fonts.css">
	<link rel="stylesheet" href="../assets/glance_logo_app.css">
	
	<style type="text/css">

		body, html{
			margin: 0;
			padding: 0;
		  	background: #FFFFFF;
		}

		#loading {
			position: fixed;
			left: 0;
    		height: 100%;
		    right: 0;
		    top: 0;
		    width: 100%;
			top: 0;
			z-index: 10000000000;
		  	background: #458AC6;
		  	color: #FFFFFF;
		  	padding: 30px 0 0 50px;
		  	margin: 0;
		  	overflow: hidden;
		  	font-family: arial, helvetica !important;
		}

		#loading h1 {
		    font-family: arial,helvetica !important;
		    font-size: 40px !important;
		    font-weight: normal;
		    letter-spacing: -2px;
		    line-height: 35px !important;
		    margin: 5px !important;
		    padding: 0 !important;
		}

		#topHide {
		    background-color: #f5f8fa;
		    height: 23px;
		    left: 0;
		    position: fixed;
		    top: 38px;
		    width: 100%;
		    z-index: 10000;
		}

	</style>
	
</head>
<body>

	<div id="loading" style="font-family: arial; font-weight: 100; background: #458AC6; width: 100%; height: 2000px;">
		<h1 style="font-family: arial; font-weight: 100;">Loading....</h1>
	</div>

	<?php include("../nav-inc.php"); ?>

	<div id="topgap"></div>
	<div id="topHide"></div>

	<!-- page -->
	<div id="pageBody">

		<!-- content -->
		<div id="contentContainer">
			<div class="ui grid centered header-top">
		            
					<!-- left container -->
		            <div id="contentLeftCol" class="contentLeftCol ui four wide column ">

		            	<!-- left content area -->
		            	<div id="__searchLeftCol" class="contentInnerLeft">
		            		<div id="profileContent_leftSection">

		            			<!-- left content box -->
			            		<div id="searchVertical" class="contentContainer ui sticky">
									<div class="searchVerticalHeader">
										<h3>Latest Stuff</h3>
									</div>
									<div id="searchVerticalInner" class="contentInner">
										<div class="contentHeader">
											<div id="searchInner" class="">

												<div class="comboHeaderTop">GRAFFITI SEARCH</div>
												
												<div class="ui fluid action input">
													<input id="txt_search" placeholder="Search..." type="text">
													<div id="btn_search" class="ui blue icon button"><i class="search icon"></i></div>
												</div>
												<div class="searchInnterContent">

													<div class="ui blue message">
														Search posts to easily find tags, words or phrases. It's fun! 
														<br><br>
														Tags can be searched like this: <strong>#yourtag</strong>
													</div>
												</div>

												<!-- options: gender, distance, sort by distance or post date -->
											</div>
										</div>
				            		</div>
				            	</div>
		            		</div>
		            	</div>
		            </div>

		            <!-- center content area -->
		            <div id="contentCenterCol" class="contentCenterCol ui eight wide column ">




		            	<div id="__searchViewBlank">
		            		<i class="icon large list layout"></i><br>
		            		<div id="__searchViewBlankTitle">graffiti</div>
		            	</div>

		            	<!-- results view -->
			            <div id="__searchView">

				            <!-- search content -->
			            	<div id="postsContainer" class="contentCenterColInner">

			            	</div>
		            	</div>
		            </div>
		            <!-- right content area -->
		            <div id="contentRightCol" class="ui four wide column contentRightCol">

		            	<div class="contentContainer containerSharedRight contentContainerWithHeader">
		            		<div class="contentInner">
								<div class="contentHeader">
									<h3>Recent Friend Activity</h3>
									<!-- <span>·</span>
									<a href="#">See More</a> -->
									<div class="ui simple divider"></div>
									<div class="ui feed recent_activity_list">
										<?php echo  $site_recentFriendActivity_html; ?>
									</div>
								</div>
		            		</div>
		            	</div>

		            </div>
		    	</div>
			</div>
		</div>
	</div>

	<!-- alert dialog -->
	<div id="alert" class="ui small modal">
		<i class="close icon"></i>
		<div id="alertTitle" class="header"></div>
		<div class="content">
			<div class="image">
				<i class="icon info circle"></i>
			</div>
			<div id="alertDescription" class="description"></div>
		</div>
		<div class="actions">
			<div class="ui positive right labeled icon button">
				Ok
				<i class="checkmark icon"></i>
			</div>
		</div>
	</div>

	<!-- are you sure? dialog -->
    <div id="affirm" class="ui modal">
        <div id="affirm_title" class="header">
            Are you sure?
        </div>
        <div class="content">
	    	<div class="ui medium image">
	        	<i class="help icon"></i>
	      	</div>
	      	<div class="description">
	        	<p id="affirm_question">Are you sure?</p>
	      	</div>
	    </div>
        <div class="actions">
            <div class="two fluid ui buttons">
                <div class="ui negative labeled icon button">
                    <i class="remove icon"></i>
                    No
                </div>
                <div class="ui positive right labeled icon button">
                    Yes
                    <i class="checkmark icon"></i>
                </div>
            </div>
        </div>
    </div>
    
	<!-- site info for scripts -->
	<div id="this_site" data-id="<?php echo $this_site; ?>" style="display:none;"></div>
	<div id="form_token" data-id="<?php echo $form_token; ?>"></div>
	<div id="page_token" data-id=""></div>
	<div id="post_seed" data-id="<?php echo $_SESSION["post_seed"]; ?>"></div>

	<?php 

		$tag = $_GET["t"];
		$_zipcode = $loggedUser['zipcode'];
		$_city = $loggedUser['city'];
		$_age = $loggedUser['age'];
		$_state = $loggedUser['state'];
		$_gender = $loggedUser['gender'];
		$_seekingGender = $loggedUser['seekingGender'];
		$_adultViewPref = $loggedUser['adultViewPref'];

		$script = <<<EOT
			
			<script type="text/javascript">
				
				var myProfile = {

					tag : "$tag",
					profileNoPicGuy : "../assets/nopic_guy.png",
					profileNoPicGal : "../assets/nopic_gal.png",
					defaultSearch : "$defaultSearch",
					zipcode : "$_zipcode",
					city : "$_city",
					age : "$_age",
					state : "$_state",
					gender : "$_gender",
					seekingGender : "$_seekingGender",
					adultViewPref : "$_adultViewPref",
				}
			</script>

EOT;

		echo $script;

	?>

	<script src='../assets/jquery.min.js?=11115'></script>
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/semantic/dropdown.min.js?=11115"></script>
	<script src="../assets/jquery.photoset-grid.min.js?=11115"></script>
	<script src="../assets/jquery.colorbox.js?=11115"></script>
	<script src="../assets/jquery-inline-affirm.js?=11115"></script>
	<script src="../assets/search-content.js?=<?php echo uniqid(); ?>"></script>
	<script src="../assets/sticky.min.js"></script>
	<script src="../assets/activity.js"></script>

	<!-- additional scripting for page load -->
	<script type="text/javascript">

		var click = "click";

		$(function(){

			var h 
			var w

			resizeWindow();

			$(window).resize(function() {

				resizeWindow();
		    });
			
			$("#loading").hide();

		    function resizeWindow(){

		    	h = $(window).height();
				w = $(window).width();

				$("#loading")
					.height(h)
					.width(w)

				$("#searchVerticalInner")
					.height(h-200)

				$(".searchPreview")
					.width( $("#searchVerticalInner").width() -80);

		    }
		});
	</script>
</body>
</html>


