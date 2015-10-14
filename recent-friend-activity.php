<?php

//Include to manage all recent recent friend activity throughout the site

require_once('../def-inc.php');

//build the recent activity list
$site_recentActivity = activity_recentFriendActivity($loggedUser["profile_id"]);
$site_recentActivity = $site_recentActivity["results"];

$html_ele_template = '<div class="event"><div class="label"><img class="hidden_image_img" src="[PROFILE_IMG]"></div><div class="content"><div class="date">[WHENDO]</div><div class="summary">[WHATDO]</div></div></div>';
$site_recentFriendActivity_html = "";

$x = 0;

foreach ($site_recentActivity as $key => $value) {
    
    if ($value["gender"] == "guy") {
        
        $profileNoPic = "../assets/nopic_guy.png";
        $gender_ownership = "his";
        
    } else {

        $profileNoPic = "../assets/nopic_gal.png";
        $gender_ownership = "her";
    }   

    if ($value["did_what"] == "New Post") {
        
        $whatdo = '<a href="' . $profile_url . '?' . $value["userName"] . '">' . $value["firstName"]  . " added a new post</a>";

    } else if ($value["did_what"] == "Updated Profile") {
        
        $whatdo = '<a href="' . $profile_url . '?' . $value["userName"] . '">' . $value["firstName"]  . " updated " . $gender_ownership . " profile</a>";
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

    $site_recentFriendActivity_html .= $tempProfile; 

    $x++;
}

if ($x == 0) {
    
    $site_recentFriendActivity_html = '<div class="noActivityMsg">No Activity Yet</div>';
}

?>
