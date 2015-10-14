<?php  
/*

	/MESSAGES/
	
*/
require_once('../no-cache-inc.php');
require_once('../session-inc.php');
require_once('../data-inc.php');
require_once('../def-inc.php');
require_once('../account-inc.php');
require_once('../mailgun-inc.php');
require_once('../activity-inc.php');
require_once(SITEPATH . '/func/auth.php');
require_once('../connectivity-inc.php'); 

	$messageActive = "active";

	$profile_id = $loggedUser["profile_id"];

	//build new token for the new form
	$_SESSION["form_token_account_edit_" . $profile_id] = md5("ion") . uniqid() . md5( uniqid() );
	$form_token = $_SESSION["form_token_account_edit_" . $profile_id];

	$_SESSION["post_seed"] = uniqid() . $profile_user["profile_id"];

?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Your Messages: <?php echo $loggedUser["firstName"]; ?></title>
	<link href="<?php echo SITEURL; ?>/favicon.png" type="image/png" rel="shortcut icon" id="favicon">
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> -->
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> -->
	<meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    
	<script src="../assets/modernizr-2.6.2.min.js"></script>

	<link rel="stylesheet" href="../assets/semantic/semantic.min.css">
	<link rel="stylesheet" href="../assets/colorbox.css">
	<link rel="stylesheet" href="../assets/messages.css">
	<link rel="stylesheet" href="../assets/sticky.min.css">
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

		<!-- content -->
		<div id="contentContainer">
			<div class="ui grid centered header-top">
		            
					<!-- left container -->
		            <div id="contentLeftCol" class="contentLeftCol ui four wide column ">

		            	<!-- left content area -->
		            	<div id="__message_contentInnerLeft" class="contentInnerLeft">
		            		<div id="profileContent_leftSection">

		            			<!-- left content box -->
			            		<div id="inboxMessages" class="contentContainer ui sticky">
									<div class="inboxMessagesHeader">
										<h3>Messages</h3>
									</div>
									<div id="inboxMessagesInner" class="contentInner">
										<div class="contentHeader">
											<div id="messages_list" class="ui feed recent_activity_list"></div>
										</div>
				            		</div>
				            	</div>
		            		</div>
		            	</div>
		            </div>

		            <!-- center content area -->
		            <div id="contentCenterCol" class="contentCenterCol ui eight wide column ">

		            	<div class="__messageViewBlank">
		            		<i class="icon large mail"></i><br>
		            		<span>messages</span>
		            	</div>

			            <div style="display:none" class="__messageView">
			            	<div class="ui menu small blue inverted">
							  <a id="btn_notInterested" class="item">
							    <i class="icon thumbs down"></i>Not Interested
							  </a>
							  <a id="btn_deleteConversation" class="item">
							    <i class="icon remove"></i>Delete Conversation
							  </a>
							  <a id="btn_blockUser" class="item">
							    <i class="icon ban"></i>Block User
							  </a>
							  <a id="btn_reportUser" class="item">
							    <i class="icon announcement"></i>Report
							  </a>
							</div>

							<div id="statusArea">

							</div>
			            	<!-- message content and reply -->
			            	<div class="contentContainer">
			 					<div class="messageReplyContainer">
			 						<table>
			 							<tbody>
			 								<tr>
			 									<td class="postAvatarContainer">
			 										<img style="width:51px;" class="ui tiny circular image profile_image_" src="<?php echo $loggedUser["profileImage"]; ?>">
			 										<div style="width:65px;height:1px;"></div>
			 									</td>
			 									<td class="postContentContainer">
			 										<textarea id="messageReply" placeholder="Reply"></textarea>
			 										<div id="messageButtonArea" class="messageButtonArea">
			 											<div id="btn_post" class="ui blue mini button">
															Send Message
														</div>
														<?php  

															if ($_SESSION["loggedUser"]['profileVisible'] == "0") {
																
																echo '<div id="btn_attachLink" class="ui mini button"> Insert Private Profile Link </div>';
															}

														?>

														<div id="btn_working" class="ui blue mini button">
															<i class="spinner loading icon"></i>Working...
														</div>
			 										</div>
			 									</td>
			 								</tr>
			 							</tbody>
			 						</table>
			 					</div>	
			 					<div class="messageContainer">
			 						<table>
			 							<tbody>
			 								<tr>
			 									<td class="postAvatarContainer">
			 										<img id="fromProfileImage" style="width:51px;" class="ui tiny circular image hidden_image_img" src="">
			 										<div style="width:65px;height:1px;"></div>
			 									</td>
			 									<td class="postContentContainer">
			 										<h4 id="messageWhenWho"><!-- author here --></h4>
			 										<div id="__messageContent" class="messageContent">
			 											<!-- message goes here -->
			 										</div>
			 									</td>
			 								</tr>
			 							</tbody>
			 						</table>
			 						<div style="height:40px;"></div>
			 					</div>	
			            	</div>
			            	<div class="_messageConversationLabel"><i class="icon chat"></i>Message Conversation</div>
			            	<!-- message conversation -->
			            	<div id="_messageConversation"></div>
		            	</div>
		            </div>
		            <!-- right content area -->
		            <div id="contentRightCol" class="ui four wide column contentRightCol"></div>
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


	<script src='../assets/jquery.min.js?=11115'></script>
	<script src="../assets/semantic/semantic.min.js?=11115"></script>
	<script src="../assets/jquery.finger.min.js?=11115"></script>
	<script src="../assets/messages.js?=<?php echo uniqid(); ?>"></script>
	<script src="../assets/sticky.min.js"></script>

	<?php  

		$_script = <<<EOT
				
			<script type="text/javascript">			
				var navMessageCount = $navMessageCount;
			</script>

EOT;

	echo $_script;

	?>

	<!-- additional scripting for page load -->
	<script type="text/javascript">

		var click = "click";

		$(function(){

			var h 
			var w

			$(window).resize(function() {

				resizeWindow();
		    });
			
			resizeWindow();
			
			setTimeout(function(){

				$("#loading").hide();

			},500);

		    function resizeWindow(){

		    	h = $(window).height();
				w = $(window).width();

				$("#loading")
					.height(h)
					.width(w)

				$("#inboxMessagesInner")
					.height(h-200)

				$(".messagePreview")
					.width( $("#inboxMessagesInner").width() -80);

		    }
		});
	</script>
</body>
</html>


