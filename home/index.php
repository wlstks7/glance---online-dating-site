<?php  
/*

	/HOME/
	
*/
require_once('../no-cache-inc.php');
require_once('../session-inc.php');
require_once('../def-inc.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../activity-inc.php');
require_once('../recent-activity-inc.php');
require_once('../recent-friend-activity.php');
require_once('../connectivity-inc.php'); 
	
	$statusCounts = activity_likesGlancesCount($loggedUser["profile_id"]);

	$loggedUser["platinumStatus"] = "";

	if (is_numeric($loggedUser["pointsInt"])) {
		
		$loggedUser["pointsInt"] = intval($loggedUser["pointsInt"]);
			
		if ($loggedUser["pointsInt"] >= 500) {
			$loggedUser["platinumStatus"] = '<span style="color:#6AB9F2;font-size: 12px; padding-left: 3px;"><i class="icon diamond"></i></span>';
		}
	}

	//string definitions
	$seekingGender = $loggedUser["seekingGender"];

	if ($seekingGender == "guy") {
		$seekingGenderGreeting = "he";
	} else if ($seekingGender == "gal") {
		$seekingGenderGreeting = "she";
	} else if ($seekingGender == "guyGal") {
		$seekingGenderGreeting = "they";
	}

	$relationshipStatus = $define_relationshipStatus[$loggedUser["relationshipStatus"]];

	$zodiacLabel = "Sign:";

	if ($loggedUser["zodiacShow"] == "YES") {
		$zodiac = $loggedUser["zodiac"];
	} else {
		$zodiac = "Prefer not to say";
	}

	$gender = $define_gender[$loggedUser["gender"]];

	if ($loggedUser["gender"] == "guy") {
		$genderGreeting = "his";
		$profileNoPic = "../assets/nopic_guy.png";
	} else {
		$genderGreeting = "her";
		$profileNoPic = "../assets/nopic_gal.png";
	}

	$seekingGender = $define_seekingGender[$loggedUser["seekingGender"]];
	$height = $define_height[$loggedUser["height"]];
	$eyes = $define_eyes[$loggedUser["eyeDesc"]];
	$hair = $define_hair[$loggedUser["hairDesc"]];
	$bodyType = $define_bodyType[$loggedUser["bodyType"]];
	$ethnicity = $define_ethnicity[$loggedUser["ethnicity"]];
	$faith = $define_religious[$loggedUser["religious"]];

	$income = $define_income[$loggedUser["income"]];
	$smokerPref = $define_smokingPref[$loggedUser["smokerPref"]];
	$drinkingPref = $define_drinkingPref[$loggedUser["drinkingPref"]];
	$children = $define_children[$loggedUser["children"]];

	$profileVisible = $_SESSION["loggedUser"]['profileVisible'];
	$visibility = '<i class="wizard icon"></i><span id="glanceProfileVisibility">Your profile is visible</span> <span id="glanceProfileVisibilityLink"><a id="glanceProfileVisibilityChange" data-status="0" href="#">(Hide Profile)</a></span>';

	if ($profileVisible == "0") {
		$visibility = '<i class="wizard icon"></i><span id="glanceProfileVisibility">Your profile is hidden</span> <span id="glanceProfileVisibilityLink"><a id="glanceProfileVisibilityChange" data-status="1" href="#">(Show Profile)</a></span>';
	}

	$profileDescription = $loggedUser["profileDesc"];

	if (trim($profileDescription) == "") {
		$profileDescription = $default_profileDescription;
	}

	$profileDescription = str_replace(':-)', '<i class="icon-emo-happy"></i>', $profileDescription);
	$profileDescription = str_replace(':)', '<i class="icon-emo-happy"></i>', $profileDescription);
	$profileDescription = str_replace(';-)', '<i class="icon-emo-wink"></i>', $profileDescription);
	$profileDescription = str_replace(';)', '<i class="icon-emo-wink2"></i>', $profileDescription);
	$profileDescription = str_replace(':-(', '<i class="icon-emo-unhappy"></i>', $profileDescription);
	$profileDescription = str_replace(':(', '<i class="icon-emo-unhappy"></i>', $profileDescription);
	$profileDescription = str_replace('(Y)', '<i class="icon-emo-thumbsup"></i>', $profileDescription);

	$profileBannerImage = $loggedUser["profileBannerImage"];

	if ( substr($profileBannerImage, 0, 3) != "../" ) {
		$profileBannerImage = "../assets/banners/blue_ocean_refresher.jpg";
	}

	$profileImage = $loggedUser["profileImage"];

	if ( trim($profileImage) == "" ) {
		$profileImage = $profileNoPic;
	}

	$profile_id = $loggedUser["profile_id"];

	//build new token for the new form
	$_SESSION["form_token_account_edit_" . $profile_id] = md5("ion") . uniqid() . md5( uniqid() );

	$form_token = $_SESSION["form_token_account_edit_" . $profile_id];

	$_SESSION["post_seed"] = uniqid() . $loggedUser["profile_id"];

	$homeActive = "active";
?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Welcome <?php echo $_customer_firstName; ?></title>
    <link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">

	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> -->
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    
	<script src="../assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/semantic/icon.min.css">
	<link rel="stylesheet" href="../assets/colorbox.css">
	<link rel="stylesheet" href="../assets/main.css">
	<link rel="stylesheet" href="../assets/activity.css">
	<link rel="stylesheet" href="../assets/smilz.css">
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

	</style>
	
</head>
<body>

	<div id="loading" style="font-family: arial; font-weight: 100; background: #458AC6; width: 100%; height: 2000px;">
		<h1 style="font-family: arial; font-weight: 100;">Loading....</h1>
	</div>

	<!-- top menu -->
	<?php include("../nav-inc.php"); ?>

	<div id="topgap"></div>

	<!-- page -->
	<div id="pageBody">

		<!-- banner -->
		<div id="banner">
			<img id="__bannerImage" class="bannerImage" src="<?php echo $profileBannerImage; ?>" alt="" style="transform: none;">
			<div id="edit_banner_overlay" class="edit_banner_overlay_view edit_mode_overlay">
				<div class="banner_overlay_msg edit_banner_overlay_view edit_mode_overlay"><i class="icon large arrow circle outline up"></i><br>Banner Backgrounds</div>
			</div>
			<div id="edit_banner" class="edit_banner_overlay_view edit_mode_overlay">
				<iframe src="banner-theme.php?=<?php echo uniqid(); ?>"></iframe>
			</div>
		</div>

		<!-- banner nav bar -->
		<div id="bannerNavBar">
			<div class="ui grid centered header-top">
	            <div class="contentLeftCol ui four wide column ">

	            	<!-- profile image -->
	            	<div id="mainProfileImageContainer">
	            		<div id="mainProfileImage" class="popup" data-variation="inverted" data-content="<?php echo $loggedUser["firstName"]; ?>">
						    <img id="__mainProfileImage" alt="<?php echo $loggedUser["firstName"]; ?>" src="<?php echo $profileImage; ?>" class="profile-image profile_image_">
	            		</div>
	            		<div id="edit_profile_image" class="edit_mode_overlay">
	            			<i class="icon photo big"></i><br>
	            			<span>New Photo</span>
	            		</div>
	            		<div id="crop_profile_photo" class="">
							<iframe id="frame_crop_profile_photo" src="crop_profile_image.php?=<?php echo uniqid(); ?>"></iframe>
						</div>

	            	</div>
	            </div>
	            <div class="contentCenterCol ui eight wide column">
	            	<div class="ui mini statistic">
	            		<div class="label">
	            			Posts
	            		</div>
	            		<div id="counter_posts" class="value">
	            			0
	            		</div>
	            	</div>
	            	<div class="ui mini statistic">
	            		<div class="label">
	            			Images
	            		</div>
	            		<div id="counter_images" class="value">
	            			0
	            		</div>
	            	</div>
	            	<div class="ui mini statistic">
	            		<div class="label">
	            			Likes
	            		</div>
	            		<div id="likePeopleCount" data-likePeopleCount="<?php echo $statusCounts["results"][0]["likePeopleCount"]; ?>" data-likeCount="<?php echo $statusCounts["results"][0]["likeCount"]; ?>" class="likePeopleCount value">
	            			<?php echo $statusCounts["results"][0]["likeCount"]; ?>
	            		</div>
	            	</div>
	            	<div class="ui mini statistic">
	            		<div class="label">
	            			Glances
	            		</div>
	            		<div id="glanceCount" data-glancePeopleCount="<?php echo $statusCounts["results"][0]["glancePeopleCount"]; ?>" data-glanceCount="<?php echo $statusCounts["results"][0]["glanceCount"]; ?>" class="glanceCount value">
	            			<?php echo $statusCounts["results"][0]["glanceCount"]; ?>
	            		</div>
	            	</div>
	            	<div id="btn_editProfileCenterCol" style="float:right;" data-mode="run" class="ui button blue btn_editProfile">Edit Profile</div>
	            	<div id="btn_cancelProfileCenterCol" style="float:right;" class="ui button btn_cancelProfile profileHide">Cancel</div>
	            </div>
	            <div class="ui four wide column contentRightCol">
	            	<div id="btn_editProfileRightCol" style="float:right;" data-mode="run" class="ui button blue btn_editProfile">Edit Profile</div>
	            	<div id="btn_cancelProfileRightCol" style="float:right;" class="ui button btn_cancelProfile profileHide">Cancel</div>
	            </div>
        	</div>
		</div>

		<!-- content -->
		<div id="contentContainer">
			<div class="ui grid centered header-top">
		            
					<!-- left container -->
		            <div id="contentLeftCol" class="contentLeftCol ui four wide column ">

		            	<!-- left content area -->
		            	<div class="contentInnerLeft">
		            		
		            		<!-- user name -->
		            		<h1 class="profileFirstName"><?php echo $loggedUser["firstName"]; ?></h1>

		            		<!-- screen name -->
		            		<h2 class="profileScreenName">@<?php echo $loggedUser["userName"] . $loggedUser["platinumStatus"]; ?></h2>
		            		
		            		<div class="ui simple divider"></div>
		            		
		            		<div id="profileContent_leftSection">

			            		<!-- location link -->
			            		<p>
			            			<i class="marker icon"></i><?php echo ucfirst(strtolower($loggedUser["city"])) . ", " . $loggedUser["state"]; ?>
			            		</p>

			            		<!-- status link -->
			            		<p>
			            			<i class="user icon"></i><?php echo $relationshipStatus; ?>
			            		</p>

			            		<!-- points link -->
			            		<p>
			            			<i class="trophy icon"></i>Profile Points: <span id="profilePoints"><?php echo number_format($loggedUser["pointsInt"]); ?></span>
			            		</p>

			            		<!-- visible link -->
			            		<p>
			            			<?php echo $visibility; ?>
			            		</p>


			            		<!-- profile about me -->
			            		<div id="profileAboutMe">
			            			<p>
			            				<span>ABOUT <?php echo $loggedUser["firstName"]; ?></span> <br>
			            				<?php echo $profileDescription; ?>
			            			</p> 
			            		</div>

			            		<!-- about me section -->
			            		<?php include_once("__about-me.php"); ?>

		            		</div>

			            	<!-- left side recent activity list -->
		            		<div class="contentContainer containerSharedLeft contentContainerWithHeader">
			            		<div class="contentInner">
									<div class="contentHeader">
										<h3>Recent Activity</h3>
										<!-- <span>·</span>
										<a href="#">See More</a> -->
										<div class="ui simple divider"></div>
										<div class="ui feed recent_activity_list">
											<?php echo $site_recentActivity_html; ?>
										</div>
									</div>
			            		</div>
			            	</div>

			            	<div class="contentContainer containerSharedLeft contentContainerWithHeader">
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

		            <!-- center content area -->
		            <div id="contentCenterCol" class="contentCenterCol ui eight wide column ">
		            	
		            	<!-- profile editor container -->
		            	<div class="contentContainer editProfile" style="display:none;">
		 					<div class="ui ribbon blue label"><i class="edit icon"></i>Edit Profile</div>
		 					<div class="contentInner ui form">
		 						<?php include_once("__account-edit.php"); ?>
		 					</div>
		            	</div>

		            	<!-- editor container -->
		            	<div class="contentContainer post_editContainer">
		            		<div class="ui ribbon blue label"><i class="share square icon"></i>Post Something</div>

		            		<!-- editor -->
		            		<div class="editorContainer">
		            			<textarea id="postEditor" class="editor" placeholder="Go ahead... <?php echo $seekingGenderGreeting; ?> can't wait to hear about it ;-)"></textarea>
		            			
		            			<!-- uploaded images container -->
		            			<div id="post_imagesContainer" data-ui="posts">
		            				<!-- <div class="post_imageThumb">
		            					<div class="remove_postImage"><i class="remove icon"></i></div>
		            					<img style="height: 70px; position: absolute; left: -40px;" alt="" src="../assets/shutterstock_97243034.jpg" class="">
		            				</div>
		            				<div class="post_imageThumb">
		            					<div class="remove_postImage"><i class="remove icon"></i></div>
		            					<img style="height: 70px; position: absolute;" alt="" src="https://scontent.fphx1-1.fna.fbcdn.net/hphotos-xpf1/v/t1.0-9/q86/c200.0.200.200/p200x200/10432116_1455646354752931_5761466257657950284_n.jpg?oh=1c3ec6a27d2965899de7ce5c72880613&amp;oe=56304C63" class="">
		            				</div> -->
		            			</div>
		            				<div style="clear:both;"></div>
		            			<!-- editor buttons -->
		            			<div class="editor_btns">
									<div id="__btn_addPhoto" class="ui basic tiny button">
										<i class="icon photo"></i>
										Add Photo
									</div>
									<div id="btn_post" class="ui blue tiny button">
										Post
									</div>
									<div id="btn_working" class="ui blue tiny button">
										<i class="spinner loading icon"></i>Working...
									</div>


									<div>
										<div id="__progressOuter" class="progress progress-striped active" style="display:none;">
										<div id="__progressBar" class="progress-bar progress-bar-success"  role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
										</div>
										<div id="__msgBox"></div>
									</div>

		            			</div>
		            		</div>
		            	</div>

		            	<!-- posts -->
		            	<div id="postsContainer">
		            		
		            	</div>
		            </div>

		            <!-- right content area -->
		            <div id="contentRightCol" class="ui four wide column contentRightCol">

		            	<!-- right side glances list -->
		            	<!-- <div class="contentContainer containerSharedRight contentContainerWithHeader">
		            		<div class="contentInner">
								<div class="contentHeader">
									<h3>Recent Glances</h3>
									<span>·</span>
									<a href="#">See More</a>
									<div class="ui simple divider"></div>
								</div>
		            		</div>
		            	</div> -->

		            	<!-- right side activity list -->
		            	<div class="contentContainer containerSharedRight contentContainerWithHeader">
		            		<div class="contentInner">
								<div class="contentHeader">
									<h3>Recent Activity</h3>
									<!-- <span>·</span>
									<a href="#">See More</a> -->
									<div class="ui simple divider"></div>
									<div class="ui feed recent_activity_list">
										<?php echo $site_recentActivity_html; ?>
									</div>
								</div>
		            		</div>
		            	</div>

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
	<div id="post_seed" data-id="<?php echo $_SESSION["post_seed"]; ?>"></div>


	<?php 

		//build client side array for profile prefs
		$_zipcode = $loggedUser['zipcode'];
		$_city = $loggedUser['city'];
		$_state = $loggedUser['state'];
		$_birthMonth = $loggedUser['birthMonth'];
		$_birthDay = $loggedUser['birthDay'];
		$_birthYear = $loggedUser['birthYear'];
		$_birthDate = $loggedUser['birthDate'];
		$_zodiac = $loggedUser['zodiac'];
		$_zodiacShow = $loggedUser['zodiacShow'];
		$_firstName = $loggedUser['firstName'];
		$_relationshipStatus = $loggedUser['relationshipStatus'];
		$_gender = $loggedUser['gender'];
		$_seekingGender = $loggedUser['seekingGender'];
		$_height = $loggedUser['height'];
		$_eyeDesc = $loggedUser['eyeDesc'];
		$_bodyType = $loggedUser['bodyType'];
		$_hairDesc = $loggedUser['hairDesc'];
		$_religious = $loggedUser['religious'];
		$_ethnicity = $loggedUser['ethnicity'];
		$_income = $loggedUser['income'];
		$_smokerPref = $loggedUser['smokerPref'];
		$_drinkingPref = $loggedUser['drinkingPref'];
		$_children = $loggedUser['children'];
		
		$_profileDesc = nl2br($loggedUser['profileDesc']);
		
		$_profileImage = $loggedUser['profileImage'];
		$_profileBannerImage = $loggedUser['profileBannerImage'];
		$_adultProfileRating = $loggedUser['adultProfileRating'];
		$_adultViewPref = $loggedUser['adultViewPref'];

		$script = <<<EOT
			
			<script type="text/javascript">
				
				var myProfile = {

					profileNoPicGuy : "../assets/nopic_guy.png",
					profileNoPicGal : "../assets/nopic_gal.png",
					zipcode : "$_zipcode",
					city : "$_city",
					state : "$_state",
					birthMonth : "$_birthMonth",
					birthDay : "$_birthDay",
					birthYear : "$_birthYear",
					zodiac : "$_zodiac",
					zodiacShow : "$_zodiacShow",
					firstName : "$_firstName",
					relationshipStatus : "$_relationshipStatus",
					gender : "$_gender",
					seekingGender : "$_seekingGender",
					height : "$_height",
					eyeDesc : "$_eyeDesc",
					bodyType : "$_bodyType",
					hairDesc : "$_hairDesc",
					religious : "$_religious",
					ethnicity : "$_ethnicity",
					income : "$_income",
					smokerPref : "$_smokerPref",
					drinkingPref : "$_drinkingPref",
					children : "$_children",
					adultProfileRating : "$_adultProfileRating",
					adultViewPref : "$_adultViewPref",
					profileDesc : "$_profileDesc",
					profileImage : "$_profileImage",
					profileBannerImage : "$_profileBannerImage"
				}
			</script>

EOT;

		echo $script;

	?>

	<script src='../assets/jquery.min.js?=11115'></script>
	<script src="../assets/jquery-ui.min.js?=11115"></script>
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/jquery.jscroll.min.js?=11115"></script>
	<script src="../assets/jquery.finger.min.js?=11115"></script>
	<script src="../assets/jquery.photoset-grid.min.js?=11115"></script>
	<script src="../assets/jquery.colorbox.js?=11115"></script>
	
	<!-- additional scripting for page load -->
	<script type="text/javascript">

		var click = "click";

		$(function(){

			//$("#pageBody").hide();
			
			$('.photoset-grid-custom').photosetGrid({
				highresLinks: true,
				rel: 'withhearts-gallery',
				gutter: '5px',

				onComplete: function(){
					$('.photoset-grid-custom').attr('style', '');
					$('.photoset-grid-custom a').colorbox({
						photo: true,
						scalePhotos: true,
						maxHeight:'90%',
						maxWidth:'90%'
					});
				}
			});

			var h 
			var w

			$(window).resize(function() {

				resizeWindow();
		    });
			
			resizeWindow();
			
			$("#loading").hide();
			
			setTimeout(function(){


			},500);

		    function resizeWindow(){

		    	h = $(window).height();
				w = $(window).width();

				$("#loading")
					.height(h)
					.width(w)
		    }
		});

	</script>

	<script src="../assets/main.js?=<?php echo uniqid(); ?>"></script>
	<script src="../assets/activity.js?=<?php echo uniqid(); ?>"></script>
	
	<script type="text/javascript" src="../assets/SimpleAjaxUploader.js"></script>
	<script type="text/javascript">

		function escapeTags( str ) {
		  return String( str )
		           .replace( /&/g, '&amp;' )
		           .replace( /"/g, '&quot;' )
		           .replace( /'/g, '&#39;' )
		           .replace( /</g, '&lt;' )
		           .replace( />/g, '&gt;' );
		}

	</script>	

</body>
</html>


