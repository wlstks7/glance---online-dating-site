<?php  

//this is the site definition file

error_reporting(E_ALL ^ E_NOTICE);

date_default_timezone_set('UTC');

//the name of your site
const APPNAME = "Glance";

//physical path to the website root
const SITEPATH = "/var/www/html";

//the website's URL
const SITEURL = "https://www.glancedate.com";

//API name
const apiName = 'glancedate';
const apiVersion = '1.0';

//global navigation variables
$home_url = SITEURL . "/home/";
$profile_url = SITEURL . "/profile/";
$messages_url = SITEURL . "/messages/";
$not_available_url = SITEURL . "/not-available/";
$error_url = SITEURL . "/not-found/";
$this_site = SITEURL . "/";
$http_base = SITEURL . "/?";
$this_signup = SITEURL . "/signup/";

//navigation resets
$homeActive = "";
$messageActive = "";
$searchActive = "";
$marketActive = "";

//update activity every X request
$pollingActivityLimit = array(5,6,8);

$maxFastCountInt = 10;
$sleepPunisherInt = 18;
$howManySecondsFromLastAccessLimit = 5;

/*

	SITE DEFINITIONS
	These arrays define strings that are acceptable from user input throughout the site.
	They match all possible strings in dropboxes thoughout the site and allow you to evaluate the
	data POSTED for authenticity.

	It is VERY important that arrays match any changes you make to drop boxes on the site.

 */

$define_relationshipStatus = array(
	"1" => "Single", 
	"2" => "Single and taking a break", 
	"3" => "In a relationship", 
	"4" => "It's complicated", 
	"5" => "Here for friends only", 
	"6" => "I'm in love", 
	"7" => "No longer available", 
	"8" => "Married",
	"9" => "Separated",
	"10" => "In an open relationship"
);

$define_acceptableReports = array(
	"SCAM SOLICITATION" => "Scam Solicitation", 
	"MESSAGE WITH ADVERTISEMENTS" => "I received a message with advertisements",
	"INAPPROPRIATE BEHAVIOR" => "Inappropriate Behavior",
	"THREATENING MESSAGE" => "Threatening Message",
	"HARASSMENT/STALKING" => "Harassment/Stalking",
	"PRESSURE TO COMMUNICATE OFFLINE" => "Pressure to communicate offline",
	"I FEEL BULLIED" => "I feel bullied",
	"PROFILE CONTAINS ADVERTISEMENTS" => "Profile contains advertisements",
	"FAKE PROFILE" => "Fake Profile",
	"PROFILE IS HATEFUL" => "Profile is hateful",
	"PROFILE IS RACIST" => "Profile is racist",
	"OBSCENE PHOTOS" => "Obscene Photos",
	"VULGAR LANGUAGE" => "Vulgar Language"
);

$define_adultViewPref = array(
	"noNudity" => "I DO NOT want to see nudity.", 
	"yesNudity" => "Profiles with nudity is OK." 
);

$define_adultProfileRating = array(
	"noNudity" => "Blue", 
	"yesNudity" => "Blue"
);

$define_zodiacPref = array(
	"YES" => "Show my sign", 
	"NO" => "Do not show my sign"
);

$define_zodiacList = array(
	"Capricorn" => "Capricorn", 
	"Aquarius" => "Aquarius", 
	"Pisces" => "Pisces", 
	"Aries" => "Aries", 
	"Taurus" => "Taurus", 
	"Gemini" => "Gemini", 
	"Cancer" => "Cancer", 
	"Leo" => "Leo", 
	"Virgo" => "Virgo", 
	"Libra" => "Libra", 
	"Scorpio" => "Scorpio", 
	"Sagittarius" => "Sagittarius"
);

$define_gender = array(
	"guy" => "Man", 
	"gal" => "Woman"
);

$define_searchGender = array(
	"guygal" => "Man looking for women", 
	"galguy" => "Woman looking for men",
	"guyguy" => "Man looking for men",
	"galgal" => "Woman looking for women"
);

$define_seekingGender = array(
	"guy" => "men", 
	"gal" => "women",
	"guyGal" => "men and women"
);

$define_children = array( 
	"nope" => "No, I don't have kids", 
	"noNo" => "No and I don't want any", 
	"NoYes" => "No and I want some", 
	"NoOk" => "No and it's ok if you have kids", 
	"yes" => "Yes, I have kids", 
	"yesNo" => "Yes and I don't want more", 
	"yesMore" => "Yes and I want more", 
	"yesOk" => "Yes and it's ok if you have kids", 
	"nothing" => "nothing"
);

$define_drinkingPref = array(
	"no" => "No, I don't drink.", 
	"noOk" => "No, but it's ok if you do.", 
	"noNo" => "No, and I'd rather not be around it.", 
	"yesPlease" => "Yes please!", 
	"yesSocially" => "Yes, socially", 
	"yesWeekend" => "Yes, just on the weekends", 
	"yesEveryday" => "Yes, everyday", 
	"yesBeerPong" => "Yes! Beer pong anybody?", 
	"nothing" => "Prefer not to say"
);

$define_smokingPref = array(
	"cigars" => "Cigars are cool", 
	"420" => "420 occasionally", 
	"420Nothing" => "420 friendly but nothing else", 
	"noWay" => "No Way!", 
	"noNo" => "No and I prefer if you didn't", 
	"noYes" => "No but you can", 
	"yesAllTime" => "Yes! All the time", 
	"yesQuitting" => "Yes but I'm trying to quit", 
	"yesDiscreetly" => "Yes, discreetly", 
	"yesWhile" => "Yes, once in a while", 
	"yesDrink" => "Yes, only when I drink", 
	"nothing" => "Prefer not to say"
);

$define_income = array(
	"25" => "Less than $25,000", 
	"2540" => "$25,000 to $40,000", 
	"4060" => "$40,000 to $60,000", 
	"6080" => "$60,000 to $80,000", 
	"80100" => "$80,000 to $100,000", 
	"100more" => "More than $100,000", 
	"nothing" => "Prefer not to say"
);

$define_religious = array(
	"agnostic" => "Agnostic", 
	"atheist" => "Atheist", 
	"buddhist" => "Buddhist", 
	"catholic" => "Catholic", 
	"christian" => "Christian", 
	"hindu" => "Hindu", 
	"jewish" => "Jewish", 
	"lds" => "LDS", 
	"muslim" => "Muslim", 
	"notReligious" => "Not religious", 
	"other" => "Other", 
	"spiritual" => "Spiritual but not religious", 
	"nothing" => "Prefer not to say"

);

$define_ethnicity = array(
	"asian" => "Asian", 
	"black" => "Black", 
	"indian" => "Indian", 
	"latino" => "Latino / Hispanic", 
	"middleEast" => "Middle Eastern", 
	"mixed" => "Mixed Race", 
	"native" => "Native American", 
	"other" => "Other", 
	"pacificIslander" => "Pacific Islander", 
	"white" => "White", 
	"nothing" => "Prefer not to say"
);

$define_bodyType = array(
	"athletic" => "Athletic", 
	"average" => "Average", 
	"beerGut" => "Beer gut", 
	"bigStrong" => "Big but really strong", 
	"curvy" => "Curvy in all the right places", 
	"fatHappy" => "Fat and happy", 
	"funSize" => "Fun size", 
	"healthyFit" => "Healthy and fit", 
	"someAbs" => "I can see some of my abs", 
	"jacked" => "Jacked", 
	"longLean" => "Long and lean", 
	"overweightWorking" => "Overweight but I'm working on it", 
	"sixPack" => "Six pack abs", 
	"slightlyOverweight" => "Slightly overweight but that's ok", 
	"stocky" => "Stocky", 
	"thin" => "Thin", 
	"voluptuous" => "Voluptuous", 
	"nothing" => "Prefer not to say"

);

$define_hair = array(
	"auburn" => "Auburn", 
	"balding" => "Balding", 
	"black" => "Black", 
	"blond" => "Blond", 
	"brown" => "Brown", 
	"brunette" => "Brunette", 
	"ginger" => "Ginger", 
	"fireyRed" => "Firey Red", 
	"full" => "Full and Lush", 
	"mohawk" => "Mohawk", 
	"multi" => "Multi-color", 
	"saltPepper" => "Salt and Pepper", 
	"sandy" => "Sandy", 
	"shaved" => "Shaved", 
	"silver" => "Silver Fox",  
	"nothing" => "Prefer not to say"
);

$define_eyes = array(
	"blue" => "Blue", 
	"brown" => "Brown",
	"gray" => "Gray",
	"green" => "Green",
	"hazel" => "Hazel",
	"nothing" => "Prefer not to say"
);

$define_height = array(
	"1" => "Less than 5'", 
	"2" => "5'0",
	"3" => "5'1",
	"4" => "5'2",
	"5" => "5'3",
	"6" => "5'4",
	"7" => "5'5",
	"8" => "5'6",
	"9" => "5'7",
	"10" => "5'8",
	"11" => "5'9",
	"12" => "5'10",
	"13" => "5'11",
	"14" => "6'0",
	"15" => "6'1",
	"16" => "6'2",
	"17" => "6'3",
	"18" => "6'4",
	"19" => "6'5",
	"20" => "6'6",
	"21" => "6'7",
	"22" => "6'8",
	"23" => "6'9",
	"24" => "6'10",
	"25" => "6'11",
	"26" => "7'0",
	"27" => "7'1",
	"28" => "7'2",
	"29" => "7'3",
	"30" => "7'4",
	"31" => "7'5",
	"32" => "7'6",
	"33" => "7'7",
	"34" => "7'8",
	"35" => "Really Tall"
);

$default_profileDescription = "I'm still working on this section. Please take a look around and I promise I will update this soon.";

/*

*/

function noHTML($input){
	
	$encoding = 'UTF-8';
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, $encoding);
}

function respond($output) {

	/*
		standard response:
	
			apiName
			version
			status
			error
			msg 
			results

	*/

	echo json_encode($output);
}

function sanitize( $e ){

	//place any sanitizing code here
	$cleaned = strip_tags($e);
	$cleaned = noHTML($cleaned);
	$cleaned = remove_emoji($cleaned);
	$cleaned = trim($cleaned);

	return $cleaned;
}

function remove_emoji($text){
  return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
}

?>