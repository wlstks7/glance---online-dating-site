<?php  
/*

	/PROFILE/
	
*/
require_once('../no-cache-inc.php');
require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../account-inc.php');
require_once('../mailgun-inc.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../interact-inc.php');
require_once('../activity-inc.php');
require_once('../connectivity-inc.php'); 

	/*
	*****************************
	*****************************
	evaluate the get parms to figure out
	which profile to show
	*****************************
	*****************************
	*/

	$x = 0;
	$screenName = "";

	//run through all the keys in the GET
	foreach ($_GET as $key => $value) {
		
		if ($x==0) {
			
			//this is the first key found
			$screenName = sanitize($key);
			$x++;
		}
	}

	//check if the screenName variable is in the GET
	if ($screenName != "") {

		//try to get the profeil ID from this screen name
		$profile_id = findProfile_id($screenName);

		if ($profile_id == "fail") {
			
			//leave blank... can't locate a profile.. probably doesn't exist or deleted
			$profile_id = "";
		}

	} else {

		$profile_id = "";
	}

	//check for null ID
	if ($profile_id == "" || $profile_id == "fail") {
		
		//no id was passed... are they logged in?
		if ( isset($loggedUser["profile_id"]) && $loggedUser["profile_id"] != "" ) {
			
			//show this user's profile 
			$profile_id = $loggedUser["profile_id"];

		} else {

			//no id and they aren't logged in... send them to the login
			header('Location: ' . $this_site);
			die;
		}
	}

	//build the recent activity
	require_once('../recent-activity-inc.php');
	require_once('../recent-friend-activity.php');
	
	//build the profile array
	$profile_user = buildProfileUser($profile_id);

	if ($profile_user[0]["error"] == "false") {
		
		$profile_user = $profile_user[0];

	} else {

		echo $profile_user[0]["error"];
		die;
	}

	//check to see if this profile is hidden
	$isProfileHidden = isProfileHidden($profile_id);

	if ($isProfileHidden == "0") {

		//we couldn't get this info... this page is not available 
		header('Location: ' . $not_available_url);
		die;
	}

	if ($isProfileHidden["profileVisible"] == "0") {
		
		//this is a hidden profile
		//check to see if this person has a private URL

		if ($isProfileHidden["privateURL"] != trim($_GET["u"])) {
			
			//this user does not have a private url... this page is not available 
			header('Location: ' . $not_available_url);
			die;
		}
	}
	
	/*
	*****************************
	*****************************
	Glance this user's profile
	*****************************
	*****************************
	*/
	//check to see if the visitor is the same as the destination profile 
	if ($loggedUser["profile_id"] != $profile_id) {
		
		//check to see if I'm blocked from this page
		$ret = interact_amIBlocked($loggedUser["profile_id"], $profile_id);

		if ($ret != "0") {
			
			//I'm blocked from seeing this user's page...send me to the profile not available page
			header('Location: ' . $not_available_url);
			die;
		}

		//check to see if I'm banned on the site
		$ret = interact_amIBanned($loggedUser["profile_id"]);

		if ($ret != "0") {
			
			//I'm banned from seeing this user's page...send me to the profile not available page
			header('Location: ' . $error_url);
			die;
		}

		//have I glanced at this profile in the last 24 hours?
		$ret = interact_canIGlance($loggedUser["profile_id"], $profile_id);

		//if not...log this glance
		if ($ret == "0") {
			
			//log this activity
			interact_addActivity($loggedUser["profile_id"], $profile_id, "Glance", "Viewed this user's profile");

			//log this glance
			interact_glanceProfile($loggedUser["profile_id"], $profile_id, "Glance", "Viewed this user's profile");	
		}
	}

	//string definitions
	$relationshipStatus = $define_relationshipStatus[$profile_user["relationshipStatus"]];

	$zodiacLabel = "Sign:";

	if ($profile_user["zodiacShow"] == "YES") {

		$zodiac = $profile_user["zodiac"];

	} else {

		$zodiac = "Prefer not to say";
	}

	$gender = $define_gender[$profile_user["gender"]];

	if ($profile_user["gender"] == "guy") {
		
		$genderGreeting = "his";
		$genderGreeting_2 = "him";
		$seekingGenderGreeting = "he";
		$profileNoPic = "../assets/nopic_guy.png";

		//build random messages for messaging placeholder
		$messagesForPlaceholder = array(
			"Say something witty that makes him smile...",
			"Ask him something about one of his posts...",
			"Tell him about the time you did....",
			"Ask him what he did this past weekend...",
			"Ask him what he likes to do to relax...",
			"Ask him what he likes to do in his spare time...",
		);

		$messagePlaceholder = $messagesForPlaceholder[array_rand($messagesForPlaceholder)];

	} else {

		$genderGreeting = "her";
		$genderGreeting_2 = "her";
		$seekingGenderGreeting = "she";
		$profileNoPic = "../assets/nopic_gal.png";

		$messagesForPlaceholder = array(
			"Say something witty that makes her smile...",
			"Ask her something about one of her posts...",
			"Tell her about the time you did....",
			"Ask her what she did this past weekend...",
			"Ask her what she likes to do to relax...",
			"Ask her what she likes to do in her spare time...",
		);
		
		$messagePlaceholder = $messagesForPlaceholder[array_rand($messagesForPlaceholder)];
	}

	$seekingGender = $define_seekingGender[$profile_user["seekingGender"]];
	$height = $define_height[$profile_user["height"]];
	$eyes = $define_eyes[$profile_user["eyeDesc"]];
	$hair = $define_hair[$profile_user["hairDesc"]];
	$bodyType = $define_bodyType[$profile_user["bodyType"]];
	$ethnicity = $define_ethnicity[$profile_user["ethnicity"]];
	$faith = $define_religious[$profile_user["religious"]];
	$income = $define_income[$profile_user["income"]];
	$smokerPref = $define_smokingPref[$profile_user["smokerPref"]];
	$drinkingPref = $define_drinkingPref[$profile_user["drinkingPref"]];
	$children = $define_children[$profile_user["children"]];
	$profileDescription = $profile_user["profileDesc"];

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

	$profileBannerImage = $profile_user["profileBannerImage"];

	if ( substr($profileBannerImage, 0, 3) != "../" ) {
		$profileBannerImage = "../assets/banners/blue_ocean_refresher.jpg";
	}

	$profileImage = $profile_user["profileImage"];

	if ( trim($profileImage) == "" ) {
		$profileImage = $profileNoPic;
	}

	//have you interacted with this user before?
	$haveWeMet = interact_haveWeMet($loggedUser["profile_id"], $profile_id);

	$profile_user["platinumStatus"] = "";

	if (is_numeric($profile_user["pointsInt"])) {
		
		$profile_user["pointsInt"] = intval($profile_user["pointsInt"]);
			
		if ($profile_user["pointsInt"] >= 500) {
			$profile_user["platinumStatus"] = '<span style="color:#6AB9F2;font-size: 12px; padding-left: 3px;"><i class="icon diamond"></i></span>';
		}
	}


	/*

Array
(
    [theyGlanced] => 1
    [youLike] => 1
    [theyLike] => 1
    [youMessage] => 3
    [theyMessage] => 0
)

	*/
	
	if ($haveWeMet["theyGlanced"] != "0") {
		
		$glanceStatement = "<i class='icon check circle outline'></i>" . ucfirst($seekingGenderGreeting) . "'s seen you. &nbsp;&nbsp;";

	} else {
		
		$glanceStatement = "";
	}

	if ($haveWeMet["youLike"] != "0" && $haveWeMet["theyLike"] != "0") {
		
		$likeStatement = "<i class='icon heart'></i>You like each other. &nbsp;&nbsp;";

	} else if ($haveWeMet["youLike"] != "0" && $haveWeMet["theyLike"] == "0") {
		
		$likeStatement = "<i class='icon heart'></i>You like " . $genderGreeting_2 . ". &nbsp;&nbsp;";

	} else if ($haveWeMet["youLike"] == "0" && $haveWeMet["theyLike"] != "0") {
		
		$likeStatement =  "<i class='icon heart'></i>" . ucfirst($seekingGenderGreeting) . " likes you. &nbsp;&nbsp;";

	} else {
		
		$likeStatement = "";
	}

	if ($haveWeMet["youMessage"] != "0" && $haveWeMet["theyMessage"] != "0") {
		
		$msgStatement = "<i class='icon comment'></i>You both have sent messages. &nbsp;&nbsp;";

	} else if ($haveWeMet["youMessage"] != "0" && $haveWeMet["theyMessage"] == "0") {
		
		$msgStatement = "<i class='icon comment'></i>You have messaged. &nbsp;&nbsp;";

	} else if ($haveWeMet["youMessage"] == "0" && $haveWeMet["theyMessage"] != "0") {
		
		$msgStatement = "<i class='icon comment'></i>" . ucfirst($seekingGenderGreeting) . " has messaged. &nbsp;&nbsp;";

	} else {
		
		$msgStatement = "";
	}

	$interaction_hint = $glanceStatement . $likeStatement . $msgStatement;

	if ($haveWeMet["youLike"] != "0") {

		$likeButtonClass="btn_likedProfile";
		$likeButtonText='<i class="icon heart"></i>Liked!';

	} else {

		$likeButtonClass="btn_likeProfile";
		$likeButtonText='<i class="icon heart"></i>Like ' . $genderGreeting_2;
	}

	//build new token for the new form
	$_SESSION["form_token_account_edit_" . $loggedUser["profile_id"]] = md5("ion") . uniqid() . md5( uniqid() );
	$form_token = $_SESSION["form_token_account_edit_" . $loggedUser["profile_id"]];

	$_SESSION["post_seed"] = uniqid() . $profile_user["profile_id"];

?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Profile View: <?php echo $profile_user["firstName"]; ?></title>

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
	<link rel="stylesheet" href="../assets/profile.css">
	<link rel="stylesheet" href="../assets/jquery-inline-affirm.css">
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

	<?php include("../nav-inc.php"); ?>

	<div id="topgap"></div>

	<!-- page -->
	<div id="pageBody">

		<!-- banner -->
		<div id="banner">
			<img id="__bannerImage" class="bannerImage" src="<?php echo $profileBannerImage; ?>" alt="" style="transform: none;">
		</div>

		<!-- banner nav bar -->
		<div id="bannerNavBar">
			<div class="ui grid centered header-top">
	            <div class="contentLeftCol ui four wide column ">

	            	<!-- profile image -->
	            	<div id="mainProfileImageContainer">
	            		<div id="mainProfileImage" class="popup" data-variation="inverted" data-content="<?php echo $profile_user["firstName"]; ?>">
						    <img id="__mainProfileImage" alt="<?php echo $profile_user["firstName"]; ?>" src="<?php echo $profileImage; ?>" class="profile-image profile_image_">
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

	            	<div id="btn_likeUserCenter" style="float:right;" data-mode="run" class="ui button blue <?php echo $likeButtonClass; ?>"><?php echo $likeButtonText; ?></div>
	            	<!-- <div id="btn_cancelProfileCenterCol" style="float:right;" class="ui button btn_cancelProfile profileHide">Cancel</div> -->
	            </div>
	            <div class="ui four wide column contentRightCol">

	            	<div id="btn_likeUserRight" style="float:right;" data-mode="run" class="ui button blue <?php echo $likeButtonClass; ?>"><?php echo $likeButtonText; ?></div>
	            	<!-- <div id="btn_cancelProfileRightCol" style="float:right;" class="ui button btn_cancelProfile profileHide">Cancel</div> -->

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
		            		<h1 class="profileFirstName"><?php echo $profile_user["firstName"]; ?></h1>

		            		<!-- screen name -->
		            		<h2 class="profileScreenName">@<?php echo $profile_user["userName"] . $profile_user["platinumStatus"]; ?></h2>
		            		
		            		<div class="ui simple divider"></div>
		            		
		            		<div id="profileContent_leftSection">

			            		<!-- location link -->
			            		<p>
			            			<i class="marker icon"></i><?php echo ucfirst(strtolower($profile_user["city"])) . ", " . $profile_user["state"]; ?>
			            		</p>

			            		<!-- status link -->
			            		<p>
			            			<i class="user icon"></i><?php echo $relationshipStatus; ?>
			            		</p>

			            		<!-- profile about me -->
			            		<div id="profileAboutMe">
			            			<p>
			            				<span>ABOUT <?php echo $profile_user["firstName"]; ?></span> <br>
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
		            	
		            	<!-- profile message status -->
		            	<div id="status_area" style="display:block;">
		            	</div>

		            	<!-- editor container -->
		            	<div class="contentContainer post_editContainer">
		            		<div class="ui ribbon green label"><i class="chat icon"></i>Say Something to <?php echo $profile_user["firstName"]; ?></div>

		            		<!-- editor -->
		            		<div class="editorContainer">
		            			<textarea id="postEditor" class="editor" placeholder="<?php echo $messagePlaceholder; ?>"></textarea>
		            			
		            			<!-- uploaded images container -->
		            			<div id="post_imagesContainer" data-ui="posts"></div>
		            				<div style="clear:both;"></div>
		            			<!-- editor buttons -->
		            			<div class="editor_btns">
									<div id="btn_post" class="ui blue tiny button">
										Send your message
									</div>
									<div id="btn_working" class="ui blue tiny button">
										<i class="spinner loading icon"></i>Working...
									</div>

									<?php  

										if ($_SESSION["loggedUser"]['profileVisible'] == "0") {
											
											echo '<div id="btn_attachLink" title="Insert Private Profile Link" class="ui tiny button"> <i class="lock icon"></i> Insert Private Link </div>';
										}

									?>
									<div class="interaction_hint"><?php echo $interaction_hint; ?></div>
		            			</div>
		            		</div>
		            	</div>

		            	<!-- posts -->
		            	<div id="postsContainer">
		            		
		            	</div>
		            </div>

		            <!-- right content area -->
		            <div id="contentRightCol" class="ui four wide column contentRightCol">

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
	<div id="page_token" data-id="<?php echo uniqid() . "-" . $profile_id; ?>"></div>
	<div id="post_seed" data-id="<?php echo $_SESSION["post_seed"]; ?>"></div>


	<?php 

		//build client side array for profile prefs
		$_zipcode = $profile_user['zipcode'];
		$_city = $profile_user['city'];
		$_state = $profile_user['state'];
		$_birthMonth = $profile_user['birthMonth'];
		$_birthDay = $profile_user['birthDay'];
		$_birthYear = $profile_user['birthYear'];
		$_birthDate = $profile_user['birthDate'];
		$_zodiac = $profile_user['zodiac'];
		$_zodiacShow = $profile_user['zodiacShow'];
		$_firstName = $profile_user['firstName'];
		$_relationshipStatus = $profile_user['relationshipStatus'];
		$_gender = $profile_user['gender'];
		$_seekingGender = $profile_user['seekingGender'];
		$_height = $profile_user['height'];
		$_eyeDesc = $profile_user['eyeDesc'];
		$_bodyType = $profile_user['bodyType'];
		$_hairDesc = $profile_user['hairDesc'];
		$_religious = $profile_user['religious'];
		$_ethnicity = $profile_user['ethnicity'];
		$_income = $profile_user['income'];
		$_smokerPref = $profile_user['smokerPref'];
		$_drinkingPref = $profile_user['drinkingPref'];
		$_children = $profile_user['children'];
		$_profileDesc = nl2br($profile_user['profileDesc']);
		$_profileImage = $profile_user['profileImage'];
		$_profileBannerImage = $profile_user['profileBannerImage'];
		$_adultProfileRating = $profile_user['adultProfileRating'];
		$_adultViewPref = $profile_user['adultViewPref'];

		$script = <<<EOT
			
			<script type="text/javascript">
				
				var myProfile = {

					profile_id : "$profile_id",
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
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/jquery.finger.min.js?=11115"></script>
	<script src="../assets/jquery.photoset-grid.min.js?=11115"></script>
	<script src="../assets/jquery-inline-affirm.js?=11115"></script>
	<script src="../assets/jquery.colorbox.js?=11115"></script>
	<script src="../assets/profile.js?=<?php echo uniqid(); ?>"></script>
	<script src="../assets/activity.js?=<?php echo uniqid(); ?>"></script>

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
			
			$("#pageBody").show();
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
</body>
</html>


