<?php  

//Include to manage all recent user activity throughout the site

require_once('../def-inc.php');

//build the recent activity list
$site_recentActivity = activity_recentActivity($loggedUser["profile_id"]);
$site_recentActivity = $site_recentActivity["results"];

$html_ele_template = '<div class="event"><div class="label"><img class="hidden_image_img" src="[PROFILE_IMG]"></div><div class="content"><div class="date">[WHENDO]</div><div class="summary">[WHATDO]</div></div></div>';
$site_recentActivity_html = "";

$x = 0;

foreach ($site_recentActivity as $key => $value) {

	if ($value["did_what"] == "Glance") {
		
		$whatdo = '<a href="' . $profile_url . '?' . $value["userName"] . '">' . $value["firstName"] . " viewed your profile</a>";

	} else if ($value["did_what"] == "Like Profile") {
        
        $whatdo = '<a href="' . $profile_url . '?' . $value["userName"] . '">' . $value["firstName"] . " liked your profile</a>";
    
    } else if ($value["did_what"] == "Like Post") {
        
        $whatdo = '<a href="' . $profile_url . '?' . $value["userName"] . '">' . $value["firstName"] . " liked your post</a>";
    
    } else if ($value["did_what"] == "New Message") {
		
		$whatdo = '<a href="' . $messages_url . '">' . $value["firstName"] . " sent you a message</a>";
	}
	
    if ($value["gender"] == "guy") {
        
        $profileNoPic = "../assets/nopic_guy.png";
    
    } else {

        $profileNoPic = "../assets/nopic_gal.png";
    }   

    $profileImage = $value["profileImage"];

    if ( trim($profileImage) == "" ) {
        $profileImage = $profileNoPic;
    }

	//echo $value["did_what"];
	$tempProfile = $html_ele_template;
	$tempProfile = str_replace("[PROFILE_IMG]", $profileImage, $tempProfile);
	$tempProfile = str_replace("[WHATDO]", $whatdo, $tempProfile);
	$tempProfile = str_replace("[WHENDO]", $value["activity_date"], $tempProfile);

	$site_recentActivity_html .= $tempProfile; 

    $x++;
}

if ($x == 0) {
    
    $site_recentActivity_html = '<div class="noActivityMsg">No Activity Yet</div>';
}

/*

Array
(
    [error] => false
    [msg] => 
    [count] => 4
    [results] => Array
        (
            [0] => Array
                (
                    [did_what] => Glance
                    [activity_date] => today
                    [firstName] => Ash
                    [profileImage] => https://images.firmpos.net/public_assets/imgs/558efe41c1006559ea767c52ea.jpg
                    [visitor_profile_id] => 558efe41c1006
                    [userName] => ash2
                )

            [1] => Array
                (
                    [did_what] => Glance
                    [datestamp] => 2015-07-09 13:39:02
                    [firstName] => Ash
                    [profileImage] => https://images.firmpos.net/public_assets/imgs/558efe41c1006559ea767c52ea.jpg
                    [visitor_profile_id] => 558efe41c1006
                    [userName] => ash2
                )

            [2] => Array
                (
                    [did_what] => Glance
                    [datestamp] => 2015-07-09 13:39:02
                    [firstName] => Ash
                    [profileImage] => https://images.firmpos.net/public_assets/imgs/558efe41c1006559ea767c52ea.jpg
                    [visitor_profile_id] => 558efe41c1006
                    [userName] => ash2
                )

            [3] => Array
                (
                    [did_what] => Glance
                    [datestamp] => 2015-07-09 13:39:02
                    [firstName] => Ash
                    [profileImage] => https://images.firmpos.net/public_assets/imgs/558efe41c1006559ea767c52ea.jpg
                    [visitor_profile_id] => 558efe41c1006
                    [userName] => ash2
                )

        )

)



*/

?>
