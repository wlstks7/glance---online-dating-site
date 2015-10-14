<?php 
/*

	/SEARCH/
	
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

	$searchActive = "active";

	$profile_id = $loggedUser["profile_id"];

	//build new token for the new form
	$_SESSION["form_token_account_edit_" . $profile_id] = md5("ion") . uniqid() . md5( uniqid() );
	$form_token = $_SESSION["form_token_account_edit_" . $profile_id];

	$_SESSION["post_seed"] = uniqid() . $profile_user["profile_id"];

?>
<html id="html">
<head>
	<title><?php echo APPNAME; ?> - Search: <?php echo $loggedUser["firstName"]; ?></title>
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
	<link rel="stylesheet" href="../assets/search.css">
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
		    height: 36px;
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
										<h3>Search</h3>
									</div>
									<div id="searchVerticalInner" class="contentInner">
										<div class="contentHeader">
											<div id="searchInner" class="">

												<div class="comboHeaderTop">CURRENT SEARCH</div>
												<div id="comboHeaderTitle" class="comboHeaderTitle">Default</div>

												<div class="searchDetails">										
													Women: 28 - 58 years old <br>
													50 mi. from 85248
												
												</div>
												
												<div id="btn_editSearch" data-action="closed" class="ui button mini blue">Edit Search <i class="icon arrow right"></i></div>
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
		            		<i class="icon large search"></i><br>
		            		<span>search</span>
		            	</div>

		            	<div id="__searchEditFavList" class="contentContainer">
		            		<div class="contentContainerInner">
		            			<h1>Edit Favorites</h1>
		            			<div class="rightEditorButtons">
		            				<div id="btn_closeSearchFavEdit"><i class="icon remove"></i>Close</div>
		            			</div>
		            		</div>
		            		<div id="searchEditcontentContainerInner" class="searchEditcontentContainerInner"></div>
		            	</div>

		            	<div id="__searchEdit" class="contentContainer">
		            		<div class="contentContainerInner">
		            			<h1>Edit Search</h1>

		            			<div class="rightEditorButtons">
		            				<div id="btn_newSearch"><i class="icon wizard"></i>New Seach</div>
		            				<div id="btn_closeSearchEdit"><i class="icon remove"></i>Close</div>

		            			</div>
		            			
		            			<div style="height:20px;clear:both;"></div>

		            			<div class="contentSection">
		            				<div class="ui blue ribbon label">
								    	<i class="user icon"></i> Basic
								    </div>
								    <div class="contentSectionInner">
								    	<div class="ui form">
								    		<div class="two fields">
								    			<div class="field">
								    				<label>I am a...</label>
								    				<div id="_searchingGender" class="ui selection dropdown searchForm">
								    					<input id="searchingGender" name="searchingGender" type="hidden">
								    					<div class="default text">Choose</div>
								    					<i class="dropdown icon"></i>
								    					<div class="menu">
								    						<div class="item" data-value="guygal">Man looking for women</div>
								    						<div class="item" data-value="galguy">Woman looking for men</div>
								    						<div class="item" data-value="guyguy">Man looking for men</div>
								    						<div class="item" data-value="galgal">Woman looking for women</div>
								    					</div>
								    				</div>
								    			</div>
								    			<div class="field">
								    				<label>Between ages...</label>
								    				<div class="two fields">
								    					<div class="field">
								    						<div id="_searchingAgeFrom" class="ui selection dropdown searchForm">
								    							<input id="searchingAgeFrom" name="searchingAgeFrom" type="hidden">
								    							<div class="default text">Min</div>
								    							<i class="dropdown icon"></i>
								    							<div class="menu">
								    								<?php  
								    								for ($i=18; $i < 100 ; $i++) { 
								    									echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';
								    								}
								    								?>
								    							</div>
								    						</div>
								    					</div>
								    					<div class="field">
								    						<div id="_searchingAgeTo" class="ui selection dropdown searchForm">
								    							<input id="searchingAgeTo" name="searchingAgeTo" type="hidden">
								    							<div class="default text">Max</div>
								    							<i class="dropdown icon"></i>
								    							<div class="menu">
								    								<?php  
								    								for ($i=18; $i < 100 ; $i++) { 
								    									echo '<div class="item" data-value="' . $i . '">' . $i . '</div>';
								    								}
								    								?>
								    							</div>
								    						</div>
								    					</div>
								    				</div>
								    			</div>
								    		</div>
								    		
								    		<div class="ui divider"></div>

								    		<div class="two fields">
						    					<div class="field">
						    						<label>Distance</label>
						    						<input id="input_distance" placeholder="How many miles" type="text">
						    					</div>
						    					<div class="field">
						    						<label>Zipcode</label>
						    						<input id="input_zipcode" placeholder="From this zipcode" type="text">
						    					</div>
								    		</div>

								    		<div class="ui divider"></div>

								    		<div class="inline fields">
									    		<div class="field">
									    			<div id="chk_profileImage" class="ui checkbox">
									    				<input class="hidden" tabindex="0" type="checkbox">
									    				<label>With Profile Image</label>
									    			</div>
									    		</div>
									    		<div class="field">
									    			<div id="chk_online" class="ui checkbox">
									    				<input class="hidden" tabindex="0" type="checkbox">
									    				<label>Online</label>
									    			</div>
									    		</div>
								    		</div>
								    	</div>
		            				</div>
		            			</div>
		            		</div>
		            	</div>

            			<div class="contentContainer __editSearchContainers">
            				<div class="contentContainerInner">
		            			<div class="contentSection">
		            				<div class="ui blue ribbon label">
								    	<i class="adjust icon"></i> Appearance
								    </div>
								    <div id="sectionAreaInnerAppearance" class="contentSectionInner sectionClosed">
								    	<div id="sectionAreaAppearance" class="sectionContent sectionClosed">
								    		<div class="ui form">
									    		<div class="field">
													<label>Body Type</label>
													<div id="_bodyType" class="ui selection multiple dropdown searchForm" multiple="">
														<input id="bodyType" name="bodyType" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="athletic">Athletic</div>
															<div class="item" data-value="average">Average</div>
															<div class="item" data-value="beerGut">Beer gut</div>
															<div class="item" data-value="bigStrong">Big but really strong</div>
															<div class="item" data-value="curvy">Curvy in all the right places</div>
															<div class="item" data-value="fatHappy">Fat and happy</div>
															<div class="item" data-value="funSize">Fun size</div>
															<div class="item" data-value="healthyFit">Healthy and fit</div>
															<div class="item" data-value="someAbs">I can see some of my abs</div>
															<div class="item" data-value="jacked">Jacked</div>
															<div class="item" data-value="longLean">Long and lean</div>
															<div class="item" data-value="overweightWorking">Overweight but I'm working on it</div>
															<div class="item" data-value="sixPack">Six pack abs</div>
															<div class="item" data-value="slightlyOverweight">Slightly overweight but that's ok</div>
															<div class="item" data-value="stocky">Stocky</div>
															<div class="item" data-value="thin">Thin</div>
															<div class="item" data-value="voluptuous">Voluptuous</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>

												<div class="field">
													<label>Hair</label>
													<div id="_hairDesc" class="ui selection multiple dropdown searchForm">
														<input id="hairDesc" name="hairDesc" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="auburn">Auburn</div>
															<div class="item" data-value="balding">Balding</div>
															<div class="item" data-value="black">Black</div>
															<div class="item" data-value="blond">Blond</div>
															<div class="item" data-value="brown">Brown</div>
															<div class="item" data-value="brunette">Brunette</div>
															<div class="item" data-value="ginger">Ginger</div>
															<div class="item" data-value="fireyRed">Firey Red</div>
															<div class="item" data-value="full">Full and Lush</div>
															<div class="item" data-value="mohawk">Mohawk</div>
															<div class="item" data-value="multi">Multi-color</div>
															<div class="item" data-value="saltPepper">Salt and Pepper</div>
															<div class="item" data-value="sandy">Sandy</div>
															<div class="item" data-value="shaved">Shaved</div>
															<div class="item" data-value="silver">Silver Fox</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>

												<div class="field">
													<label>Ethnicity</label>
													<div id="_ethnicity" class="ui selection multiple dropdown searchForm">
														<input id="ethnicity" name="ethnicity" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="asian">Asian</div>
															<div class="item" data-value="black">Black</div>
															<div class="item" data-value="indian">Indian</div>
															<div class="item" data-value="latino">Latino/Hispanic</div>
															<div class="item" data-value="middleEast">Middle Eastern</div>
															<div class="item" data-value="mixed">Mixed Race</div>
															<div class="item" data-value="native">Native American</div>
															<div class="item" data-value="other">Other</div>
															<div class="item" data-value="pacificIslander">Pacific Islander</div>
															<div class="item" data-value="white">White</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>

												<div class="field">
													<label>Eye Color</label>
													<div id="_eyeDesc" class="ui selection multiple dropdown searchForm">
														<input id="eyeDesc" name="eyeDesc" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="blue">Blue</div>
															<div class="item" data-value="brown">Brown</div>
															<div class="item" data-value="gray">Gray</div>
															<div class="item" data-value="green">Green</div>
															<div class="item" data-value="hazel">Hazel</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>

												<div class="two fields">
													<div class="field">
														<label>Min Height</label>
														<div id="_minHeight" class="ui selection dropdown searchForm">
															<input id="minHeight" name="minHeight" type="hidden">
															<div class="default text">Please Choose</div>
															<i class="dropdown icon"></i>
															<div class="menu">
																<div class="item" data-value="1">Less than 5'</div>
																<div class="item" data-value="2">5'0"</div>
																<div class="item" data-value="3">5'1"</div>
																<div class="item" data-value="4">5'2"</div>
																<div class="item" data-value="5">5'3"</div>
																<div class="item" data-value="6">5'4"</div>
																<div class="item" data-value="7">5'5"</div>
																<div class="item" data-value="8">5'6"</div>
																<div class="item" data-value="9">5'7"</div>
																<div class="item" data-value="10">5'8"</div>
																<div class="item" data-value="11">5'9"</div>
																<div class="item" data-value="12">5'10"</div>
																<div class="item" data-value="13">5'11"</div>
																<div class="item" data-value="14">6'0"</div>
																<div class="item" data-value="15">6'1"</div>
																<div class="item" data-value="16">6'2"</div>
																<div class="item" data-value="17">6'3"</div>
																<div class="item" data-value="18">6'4"</div>
																<div class="item" data-value="19">6'5"</div>
																<div class="item" data-value="20">6'6"</div>
																<div class="item" data-value="21">6'7"</div>
																<div class="item" data-value="22">6'8"</div>
																<div class="item" data-value="23">6'9"</div>
																<div class="item" data-value="24">6'10"</div>
																<div class="item" data-value="25">6'11"</div>
																<div class="item" data-value="26">7'0"</div>
																<div class="item" data-value="27">7'1"</div>
																<div class="item" data-value="28">7'2"</div>
																<div class="item" data-value="29">7'3"</div>
																<div class="item" data-value="30">7'4"</div>
																<div class="item" data-value="31">7'5"</div>
																<div class="item" data-value="32">7'6"</div>
																<div class="item" data-value="33">7'7"</div>
																<div class="item" data-value="34">7'8"</div>
																<div class="item" data-value="35">Really tall - more than 7'8"</div>
															</div>
														</div>
													</div>												
													<div class="field">
														<label>Max Height</label>
														<div id="_maxHeight" class="ui selection dropdown searchForm">
															<input id="maxHeight" name="maxHeight" type="hidden">
															<div class="default text">Please Choose</div>
															<i class="dropdown icon"></i>
															<div class="menu">
																<div class="item" data-value="1">Less than 5'</div>
																<div class="item" data-value="2">5'0"</div>
																<div class="item" data-value="3">5'1"</div>
																<div class="item" data-value="4">5'2"</div>
																<div class="item" data-value="5">5'3"</div>
																<div class="item" data-value="6">5'4"</div>
																<div class="item" data-value="7">5'5"</div>
																<div class="item" data-value="8">5'6"</div>
																<div class="item" data-value="9">5'7"</div>
																<div class="item" data-value="10">5'8"</div>
																<div class="item" data-value="11">5'9"</div>
																<div class="item" data-value="12">5'10"</div>
																<div class="item" data-value="13">5'11"</div>
																<div class="item" data-value="14">6'0"</div>
																<div class="item" data-value="15">6'1"</div>
																<div class="item" data-value="16">6'2"</div>
																<div class="item" data-value="17">6'3"</div>
																<div class="item" data-value="18">6'4"</div>
																<div class="item" data-value="19">6'5"</div>
																<div class="item" data-value="20">6'6"</div>
																<div class="item" data-value="21">6'7"</div>
																<div class="item" data-value="22">6'8"</div>
																<div class="item" data-value="23">6'9"</div>
																<div class="item" data-value="24">6'10"</div>
																<div class="item" data-value="25">6'11"</div>
																<div class="item" data-value="26">7'0"</div>
																<div class="item" data-value="27">7'1"</div>
																<div class="item" data-value="28">7'2"</div>
																<div class="item" data-value="29">7'3"</div>
																<div class="item" data-value="30">7'4"</div>
																<div class="item" data-value="31">7'5"</div>
																<div class="item" data-value="32">7'6"</div>
																<div class="item" data-value="33">7'7"</div>
																<div class="item" data-value="34">7'8"</div>
																<div class="item" data-value="35">Really tall - more than 7'8"</div>
															</div>
														</div>
													</div>

												</div>
											</div>
								    	</div>
								    </div>
		            			</div>
		            		</div>
		            	</div>

		            	<div class="contentContainer __editSearchContainers">
            				<div class="contentContainerInner">
		            			<div class="contentSection">
		            				<div class="ui blue ribbon label">
								    	<i class="cocktail icon"></i> Lifestyle
								    </div>
								    <div id="sectionAreaInnerLifestyle" class="contentSectionInner sectionClosed">
								    	<div id="sectionAreaLifestyle" class="sectionContent sectionClosed">
								    		<div class="ui form">	

									    		<div class="field">
													<label>Relationship Status</label>
													<div id="_relationshipStatus" class="ui selection multiple dropdown searchForm">
														<input id="relationshipStatus" name="relationshipStatus" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="1">Single</div>
															<div class="item" data-value="2">Single and taking a break from dating</div>
															<div class="item" data-value="3">In a relationship</div>
															<div class="item" data-value="4">It's complicated</div>
															<div class="item" data-value="5">Here for friends only</div>
															<div class="item" data-value="6">I'm in love</div>
															<div class="item" data-value="7">No longer available</div>
															<div class="item" data-value="8">Married</div>
															<div class="item" data-value="9">Separated</div>
															<div class="item" data-value="10">In an open relationship</div>
														</div>
													</div>
												</div>





												<div class="field">
													<label>Faith</label>
													<div id="_religious" class="ui selection multiple dropdown searchForm">
														<input id="religious" name="religious" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="agnostic">Agnostic</div>
															<div class="item" data-value="atheist">Atheist</div>
															<div class="item" data-value="buddhist">Buddhist</div>
															<div class="item" data-value="catholic">Catholic</div>
															<div class="item" data-value="christian">Christian</div>
															<div class="item" data-value="hindu">Hindu</div>
															<div class="item" data-value="jewish">Jewish</div>
															<div class="item" data-value="lds">LDS</div>
															<div class="item" data-value="muslim">Muslim</div>
															<div class="item" data-value="notReligious">Not religious</div>
															<div class="item" data-value="other">Other</div>
															<div class="item" data-value="spiritual">Spiritual but not religious</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>
												<div class="field">
													<label>Children</label>
													<div id="_children" class="ui selection multiple dropdown searchForm">
														<input id="children" name="children" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															
															<div class="item" data-value="nope">No, I don't have kids</div>
															<div class="item" data-value="noNo">No and I don't want any</div>
															<div class="item" data-value="NoYes">No and I want some</div>
															<div class="item" data-value="NoOk">No and it's ok if you have kids</div>
															<div class="item" data-value="yes">Yes, I have kids</div>
															<div class="item" data-value="yesNo">Yes and I don't want more</div>
															<div class="item" data-value="yesMore">Yes and I want more</div>
															<div class="item" data-value="yesOk">Yes and it's ok if you have kids</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>
												<div class="field">
													<label>Income</label>
													<div id="_income" class="ui selection multiple dropdown searchForm">
														<input id="income" name="income" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="25">Less than $25,000</div>
															<div class="item" data-value="2540">$25,000 to $40,000</div>
															<div class="item" data-value="4060">$40,000 to $60,000</div>
															<div class="item" data-value="6080">$60,000 to $80,000</div>
															<div class="item" data-value="80100">$80,000 to $100,000</div>
															<div class="item" data-value="100more">More than $100,000</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
															
														</div>
													</div>
												</div>

												<div class="field">
													<label>Smoking</label>
													<div id="_smokerPref" class="ui selection multiple dropdown searchForm">
														<input id="smokerPref" name="smokerPref" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="cigars">Cigars are cool</div>
															<div class="item" data-value="420">420 occasionally</div>
															<div class="item" data-value="420Nothing">420 friendly but nothing else</div>
															<div class="item" data-value="noWay">No Way!</div>
															<div class="item" data-value="noNo">No and I prefer if you didn't</div>
															<div class="item" data-value="noYes">No but you can</div>
															<div class="item" data-value="yesAllTime">Yes! All the time</div>
															<div class="item" data-value="yesQuitting">Yes but I'm trying to quit</div>
															<div class="item" data-value="yesDiscreetly">Yes, discreetly</div>
															<div class="item" data-value="yesWhile">Yes, once in a while</div>
															<div class="item" data-value="yesDrink">Yes, only when I drink</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div> 
												<div class="field">
													<label>Drinking</label>
													<div id="_drinkingPref" class="ui selection multiple dropdown searchForm">
														<input id="drinkingPref" name="drinkingPref" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															
															<div class="item" data-value="no">No, I don't drink.</div>
															<div class="item" data-value="noOk">No, but it's ok if you do.</div>
															<div class="item" data-value="noNo">No, and I'd rather not be around it.</div>
															<div class="item" data-value="yesPlease">Yes please!</div>
															<div class="item" data-value="yesSocially">Yes, socially</div>
															<div class="item" data-value="yesWeekend">Yes, just on the weekends</div>
															<div class="item" data-value="yesEveryday">Yes, everyday</div>
															<div class="item" data-value="yesBeerPong">Yes! Beer pong anybody?</div>
															<div class="item" data-value="nothing">Prefer not to say</div>
														</div>
													</div>
												</div>
												<div class="field">
													<label>Astrological Sign</label>
													<div id="_zodiacPref" class="ui selection multiple dropdown searchForm">
														<input id="zodiacPref" name="zodiacPref" type="hidden">
														<div class="default text">Please Choose</div>
														<i class="dropdown icon"></i>
														<div class="menu">
															<div class="item" data-value="Capricorn">Capricorn</div>
															<div class="item" data-value="Aquarius">Aquarius</div>
															<div class="item" data-value="Pisces">Pisces</div>
															<div class="item" data-value="Aries">Aries</div>
															<div class="item" data-value="Taurus">Taurus</div>
															<div class="item" data-value="Gemini">Gemini</div>
															<div class="item" data-value="Cancer">Cancer</div>
															<div class="item" data-value="Leo">Leo</div>
															<div class="item" data-value="Virgo">Virgo</div>
															<div class="item" data-value="Libra">Libra</div>
															<div class="item" data-value="Scorpio">Scorpio</div>
															<div class="item" data-value="Sagittarius">Sagittarius</div>
														</div>
													</div>
												</div>
												<div class="option_adultSearch option_adultSearchHeader">
													<h3>Adult Option</h3>
													<p>Your preferences indicate that you wish to see adult content. If you wish to change this option, you can make this change in your profile settings.</p>
												</div>
							    				<div class="inline field option_adultSearch">
									    			<div id="chk_adultSearch" class="ui checkbox">
									    				<input class="hidden" tabindex="0" type="checkbox">
									    				<label>Show only profiles with adult content</label>
									    			</div>
									    		</div>

											</div>
								    	</div>
								    </div>
		            			</div>
	            			</div>
	            		</div>
		            	<div class="contentContainer __editSearchContainers">
		            		<div class="contentContainerInner">
		            			<div class="contentSection">
								    <div class="contentSectionInner">
								    	<div class="sectionContent">
								    		<div class="ui form">
								    			<div class="two fields">
								    				<div class="field">
								    					<label>Save as a favorite search</label>
								    					<input id="input_searchName" placeholder="Name this search" type="text">
								    				</div>
								    				<div class=" field">
								    					<label>&nbsp;</label>
										    			<div id="btn_applySearch" class="ui button blue"><i class="icon circle checkmark"></i> Apply Search</div>
										    		</div>
								    			</div>
							    				<div class="inline field">
									    			<div id="chk_defaultSearch" class="ui checkbox">
									    				<input class="hidden" tabindex="0" type="checkbox">
									    				<label>Make this my default search</label>
									    			</div>
									    		</div>
								    		</div>
								    	</div>
								    </div>
		            			</div>
		            		</div>
		            	</div>

		            	<!-- results view -->
			            <div id="__searchView">
			            	
			            	<div id="searchMenuContainer" class="">
				            	<div id="searchMenu" class="ui menu small blue inverted">
								  <a id="btn_whoOnline" class="item">
								    <i class="icon users"></i>Who's Online?
								  </a>
								  <!-- <a id="btn_mySearch" class="item">
								    <i class="icon bookmark"></i>My Search
								  </a> -->
								  <div class="ui blue buttons">
								  	<div id="btn_mySearch" class="ui button"><i class="icon bookmark"></i>My Search</div>
								  	<div id="btn_mySearchDrop" class="ui floating scrolling dropdown icon button btn_mySearchDrop">
								  		<i class="dropdown icon"></i>
								  		<div id="favoriteSearchesDrop" class="menu">
								  			<div class="header">
								  				My Favorite Searches
								  			</div>
								  		</div>
								  	</div>
								  </div>
								</div>
							</div>
							
							<div style="height:53px;"></div>
							<!-- status area for messages -->
							<div id="statusArea"></div>

				            <!-- search content -->
			            	<div id="searchResults" class="contentCenterColInner"></div>
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

	<?php 

		$s = getFavoriteSearches($loggedUser['profile_id']);

		//build client side array for profile prefs
		$searches = $s[0]["fav_searches"];
		$defaultSearch = $s[0]["defaultSearch"];

		if (trim($searches) == "") {
			$searches = '""';
		}

		if (trim($defaultSearch) == "") {
			$defaultSearch = '';
		}

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

					searches : $searches,
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
	<script src="../assets/jquery-inline-affirm.js?=11115"></script>
	<script src="../assets/search.js?=<?php echo uniqid(); ?>"></script>
	<script src="../assets/sticky.min.js"></script>

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


