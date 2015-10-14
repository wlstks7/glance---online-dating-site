<?php  

//Include for global methods dealing with member accounts

function buildLoggedUser($profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id
  );

  $_query = <<<EOT
    
    SELECT 
      profile_id,
      userName,
      emailAddress,
      zipcode,
      latitude,
      longitude,
      city,
      state,
      birthMonth,
      birthDay,
      birthYear,
      birthDate,
      zodiac,
      zodiacShow,
      firstName,
      relationshipStatus,
      gender,
      seekingGender,
      height,
      eyeDesc,
      bodyType,
      hairDesc,
      religious,
      ethnicity,
      income,
      smokerPref,
      drinkingPref,
      children,
      adultProfileRating,
      adultViewPref,
      profileDesc,
      profileImage,
      profileBannerImage,
      pointsInt,
      profileVisible,
      privateURL
    FROM 
      profile 
    WHERE 
      profile_id = :profile_id
    limit 1

EOT;

  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
      
      //something really bad just happened... couldn't get the data from this profile ID
      $msg = "Failure when trying to create LOGGEDUSER session. PROFILE ID: " . $profile_id;

      logThis($msg, $profile_id, "Logged USER Fail");
      __emailAlert($msg, ALERT_EMAIL_ADDRESS);

      return "FAIL";

    } else {

      //calculate the user's age
      $bithdayDate = $results[0]["birthDate"];
      $date = new DateTime($bithdayDate);
      $now = new DateTime();
      $interval = $now->diff($date);

      $results[0]["age"] = $interval->y;

      $loggedUser = $results[0];

      //build the array for the logged in user
      $_SESSION["loggedUser"] = $loggedUser;

      return "SUCCESS";
    }
  }
  catch(PDOException $e) {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => 'Error 100220. Please seek support.', 
      'results' => 'none'
    );

    $msg = "Failure when trying to create LOGGEDUSER session. PROFILE ID: " . $profile_id . " - Module: account-inc.php - Had an issue with the DB throwing an exception";
    __emailAlert($msg, ALERT_EMAIL_ADDRESS);

    return "FAIL";
  }
}


/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function buildProfileUser($profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id
  );

  $_query = <<<EOT
    
    SELECT 
      profile_id,
      userName,
      emailAddress,
      zipcode,
      latitude,
      longitude,
      city,
      state,
      birthMonth,
      birthDay,
      birthYear,
      birthDate,
      zodiac,
      zodiacShow,
      firstName,
      relationshipStatus,
      gender,
      seekingGender,
      height,
      eyeDesc,
      bodyType,
      hairDesc,
      religious,
      ethnicity,
      income,
      smokerPref,
      drinkingPref,
      children,
      adultProfileRating,
      adultViewPref,
      profileDesc,
      profileImage,
      profileBannerImage,
      pointsInt
    FROM 
      profile 
    WHERE 
      profile_id = :profile_id
    limit 1

EOT;

  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
      
      $results[0]["error"] = "Nothing Returned";

      return $results;

    } else {

      //calculate the user's age
      $bithdayDate = $results[0]["birthDate"];
      $date = new DateTime($bithdayDate);
      $now = new DateTime();
      $interval = $now->diff($date);

      $results[0]["age"] = $interval->y;
      $results[0]["error"] = "false";

      $userArr = $results;

      return $userArr;
    }
  }
  catch(PDOException $e) {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => 'Error 100220. Please seek support.', 
      'results' => 'none'
    );

    $msg = "Failure when trying to pull this user profile. PROFILE ID: " . $profile_id . " - Module: account-inc.php - Had an issue with the DB throwing an exception";
    __emailAlert($msg, ALERT_EMAIL_ADDRESS);

    $results[0]["error"] = $msg;
    return $results;
  }
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function checkNumericLength($val, $name, $min, $max){

  if (!is_numeric($val)) {
    
    $ret = $name . " must be a numeric value."; 
    
    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }

  if ($val > $max) {
    
    $ret = $name . " cannot be more than " . $max; 
    
    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }

  if ($val < $min) {
    
    $ret = $name . " cannot be less than " . $max; 
    
    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function checkPassword($password){

  $ret = "0";
  $password = trim($password);

  //check for spaces
  if (preg_match('/\s/', $password)) {
    $ret = "Password cannot contain spaces";
    return $ret;
  }

  if (strlen($password)<6) {
    $ret = "Password must be at least 6 characters";
  }

  if (strlen($password)>18) {
    $ret = "Password can not be more than 18 characters";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function checkStringLength($str, $name, $len){

  $str = trim($str);

  if (strlen($str) > $len) {
    
    $ret = $name . " cannot have more than " . $len . " characters";

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }

  if ($str == "") {
    
    $ret = "All fields need to be completed and " . $name . " cannot be an empty value. Please check this and try again.";

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function checkUserName($userName){

  $ret = "0";
  $userName = trim($userName);
  
  if ($userName == "") {
    $ret = "Please choose a user name that is not blank.";
    return $ret;
  } 

  //check for spaces
  if (preg_match('/\s/', $userName)) {
    $ret = "User name cannot contain spaces";
    return $ret;
  }

  //check for unauthorized chars
  if (preg_match("/[^A-Za-z0-9\-]/", $userName)) {
      $ret = "User name can only contain letters and numbers with no spaces";
      return $ret;
  }

  //check for duplicate
  $_parameterArray = array(
    ':userName' => $userName
  );

  $_query = <<<EOT
    SELECT 
      userName
    FROM 
      profile 
    WHERE 
      userName = :userName
    AND active != 'DELETED'
    limit 1

EOT;

  try {

      $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
      
      //nothing found... ok to use

    } else {

      $ret = "This username is being used by another member. Please try another name.";
    }
  }
  catch(PDOException $e) {

    $ret = "Could not finish the task. Error 100220. Please contact our support team.";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function findProfile_id($userName){

  //check for spaces
  if (preg_match('/\s/', $userName)) {
    $ret = "fail";
    return $ret;
  }

  //check for unauthorized chars
  if (preg_match("/[^A-Za-z0-9\-]/", $userName)) {
      $ret = "fail";
      return $ret;
  }

  //check for duplicate
  $_parameterArray = array(
    ':userName' => $userName,
    ':active' => "DELETED"
  );

  $_query = <<<EOT
    SELECT 
      profile_id
    FROM 
      profile 
    WHERE 
      userName = :userName
    AND active != :active
    limit 1

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) != 0) {
      
      //nothing found... ok to use
      $ret = $results[0]["profile_id"];

    } else {

      $ret = "fail";
    }
  }
  catch(PDOException $e) {

    $ret = "file";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getPostImagesCount($post_id){

  $_parameterArray = array(
    ':post_id' => $post_id
  );

  $_query = <<<EOT
    SELECT 
      imageCount
    FROM 
      posts 
    WHERE 
      post_id = :post_id
    limit 1

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) != 0) {
      
      $ret = $results[0]["imageCount"];

    } else {

      $ret = "0";
    }
  }
  catch(PDOException $e) {

    $ret = "0";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getPostPointWorth($post_id){

  $_parameterArray = array(
    ':post_id' => $post_id
  );

  $_query = <<<EOT
    SELECT 
      pointsInt
    FROM 
      posts 
    WHERE 
      post_id = :post_id
    limit 1

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) != 0) {
      
      $ret = $results[0]["pointsInt"];

    } else {

      $ret = "0";
    }
  }
  catch(PDOException $e) {

    $ret = "0";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getProfilePointWorth($profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id
  );

  $_query = <<<EOT
    SELECT 
      pointsInt
    FROM 
      profile 
    WHERE 
      profile_id = :profile_id
    limit 1

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) != 0) {
      
      $ret = $results[0]["pointsInt"];

    } else {

      $ret = "0";
    }
  }
  catch(PDOException $e) {

    $ret = "0";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getFavoriteSearches($profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id
  );

  $_query = <<<EOT
    
    SELECT 
      fav_searches, defaultSearch
    FROM 
      profile 
    WHERE 
      profile_id = :profile_id
    limit 1

EOT;

  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
      
      $results[0]["fav_searches"] = "";
      $results[0]["defaultSearch"] = "";
      return $results;

    } else {

      return $results;
    }
  }
  catch(PDOException $e) {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => 'Error 100220. Please seek support.', 
      'results' => 'none'
    );

    $msg = "Failure when trying to pull this user search. PROFILE ID: " . $profile_id . " - Module: account-inc.php - Had an issue with the DB throwing an exception";
    __emailAlert($msg, ALERT_EMAIL_ADDRESS);

    $results[0]["error"] = $msg;
    return $results;
  }
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getZipcodeData($zipcode){

  $zipcode = trim($zipcode);

  //check for duplicate
  $_parameterArray = array(
    ':zipcode' => $zipcode
  );

  $_query = <<<EOT
    SELECT 
      latitude, longitude, city, state
    FROM 
      zipcodes 
    WHERE 
      zipcode = :zipcode
    limit 1

EOT;

  try {

      $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) == 0) {
      
      $response = array(
        'error' => 'true', 
        'msg' => 'No data was found for that zip code', 
        'latitude' => '',  
        'longitude' => '',  
        'city' => '',  
        'state' => ''
      );

    } else {

      //return result
      $response = array(
        'error' => 'false', 
        'msg' => '',
        'latitude' => $results[0]["latitude"],  
        'longitude' => $results[0]["longitude"],  
        'city' => $results[0]["city"],  
        'state' => $results[0]["state"]
      );
    }
  }
  catch(PDOException $e) {

    $response = array(
      'error' => 'true', 
      'msg' => 'Could not finish the task. Error 10022220. Please contact our support team.',
      'latitude' => '',  
      'longitude' => '',  
      'city' => '',  
      'state' => ''
    );
  }

  return $response;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function getZodiac ( $birthdate ) {

   $zodiac = "";
        
   list ( $year, $month, $day ) = explode ( "-", $birthdate );
        
   if     ( ( $month == 3 && $day > 20 ) || ( $month == 4 && $day < 20 ) ) { $zodiac = "Aries"; }
   elseif ( ( $month == 4 && $day > 19 ) || ( $month == 5 && $day < 21 ) ) { $zodiac = "Taurus"; }
   elseif ( ( $month == 5 && $day > 20 ) || ( $month == 6 && $day < 21 ) ) { $zodiac = "Gemini"; }
   elseif ( ( $month == 6 && $day > 20 ) || ( $month == 7 && $day < 23 ) ) { $zodiac = "Cancer"; }
   elseif ( ( $month == 7 && $day > 22 ) || ( $month == 8 && $day < 23 ) ) { $zodiac = "Leo"; }
   elseif ( ( $month == 8 && $day > 22 ) || ( $month == 9 && $day < 23 ) ) { $zodiac = "Virgo"; }
   elseif ( ( $month == 9 && $day > 22 ) || ( $month == 10 && $day < 23 ) ) { $zodiac = "Libra"; }
   elseif ( ( $month == 10 && $day > 22 ) || ( $month == 11 && $day < 22 ) ) { $zodiac = "Scorpio"; }
   elseif ( ( $month == 11 && $day > 21 ) || ( $month == 12 && $day < 22 ) ) { $zodiac = "Sagittarius"; }
   elseif ( ( $month == 12 && $day > 21 ) || ( $month == 1 && $day < 20 ) ) { $zodiac = "Capricorn"; }
   elseif ( ( $month == 1 && $day > 19 ) || ( $month == 2 && $day < 19 ) ) { $zodiac = "Aquarius"; }
   elseif ( ( $month == 2 && $day > 18 ) || ( $month == 3 && $day < 21 ) ) { $zodiac = "Pisces"; }

   return $zodiac;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function isProfileHidden($profile_id){

  //check for duplicate
  $_parameterArray = array(
    ':profile_id' => $profile_id
  );

  $_query = <<<EOT
    SELECT 
      profileVisible,
      privateURL
    FROM 
      profile 
    WHERE 
      profile_id = :profile_id
    limit 1

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);
    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 
    $stmt->execute($_parameterArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) != 0) {
      
      $ret = $results[0];

    } else {

      $ret = "0";
    }
  }
  catch(PDOException $e) {

    $ret = "0";
  }

  return $ret;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfilePoints($points, $profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':pointsInt' => $points
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        pointsInt = (pointsInt + :pointsInt)
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}

  return $currentAge;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfilePointsDelete($points, $profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':pointsInt' => $points
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        pointsInt = (pointsInt - :pointsInt)
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}

  return $currentAge;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfileAge($birthDate, $profile_id){

  //calculate the user's age
  $date = new DateTime($birthDate);
  $now = new DateTime();
  $interval = $now->diff($date);
  $currentAge = $interval->y;

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':currentAge' => $currentAge
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        currentAge = :currentAge
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}

  return $currentAge;
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfileSearch($search, $profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':fav_searches' => $search
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        fav_searches = :fav_searches
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}

}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfileDeafultSearch($search, $profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':defaultSearch' => $search
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        defaultSearch = :defaultSearch
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}

}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function updateProfileImage($profileImage, $profile_id){

  $_parameterArray = array(
    ':profile_id' => $profile_id,
    ':profileImage' => $profileImage
  );

  //update the line with the user data
  $_query = <<<EOT
      
      UPDATE profile SET
        profileImage = :profileImage
      WHERE
        profile_id = :profile_id

EOT;

  try {

    $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    $stmt = $db->prepare($_query); 
    $stmt->execute($_parameterArray);
    $count = $stmt->rowCount();

  } catch(PDOException $e) {}
}

/******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
*******************************************
******************************************/

function validateInputData($str, $name, $definition){

  $str = trim($str);
  $definedValue = $definition[$str];

  if ( $definedValue == "" ) {
    
    $ret = "This is not a valid option for " . $name . ". Please check this and try again.";

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $ret, 
      'results' => ""
    );
    
    respond($response);

    die;
  }
}
?>