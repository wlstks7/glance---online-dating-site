<?php

//site navigation include

require_once(SITEPATH . '/activity-inc.php');

$link_home = SITEURL . '/home/';
$link_messages = SITEURL . '/messages/';
$link_search = SITEURL . '/search/';
$link_marketplace = SITEURL . '/marketplace/';
$link_logout = SITEURL . '/logout/';
$link_wall = SITEURL . '/graffiti/';

$ret = activity_messageCount($loggedUser["profile_id"]);
$messageCount = $ret["results"][0]["messageCount"];

$navMessageCount = $messageCount;

if ($ret["results"][0]["messageCount"] != "0") {
	$messageCount = '<div class="ui green circular label __messages-count">' . $messageCount . '</div>';
} else {
	$messageCount = "";
}
?>
<!-- top menu -->
<div id="topMenuContainer" class="ui inverted secondary pointing menu blue">
	<div id="topMenuContainerInner">
		<div id="topmenu" class="ui secondary pointing menu blue">
			<a href="<?php echo $link_home; ?>" class="<?php echo $homeActive; ?> item">
				<i class="user icon"></i>Home
			</a>
			<a href="<?php echo $link_messages; ?>" class="<?php echo $messageActive; ?> item">
				<i class="mail icon"></i>Messages
				<?php echo $messageCount; ?>
			</a>
			<a href="<?php echo $link_search; ?>" class="<?php echo $searchActive; ?> item">
				<i class="search icon"></i>Search
			</a>
			<a href="<?php echo $link_wall; ?>" class="<?php echo $graffitiActive; ?> item">
				<i class="list layout icon"></i>Graffiti
			</a>
			<!-- <a href="<?php echo $link_marketplace; ?>" class="<?php echo $marketActive; ?> item">
				<i class="ticket icon"></i>Marketplace
			</a> -->
			<div class="right menu">
				<a href="<?php echo $link_logout; ?>" class="ui item">
					Logout <i class="sign out icon logout-icon"></i>
				</a>
			</div>
			<div class="top-logo-container">
				<div class="glance-icon-glance_webfont2 glance-logo"></div>
			</div>

		</div>
	</div>
</div>