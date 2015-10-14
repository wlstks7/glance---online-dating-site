<?php  

error_reporting(E_ALL ^ E_NOTICE);

require('../../session-inc.php');
require('../../def-inc.php');
require('../../data-inc.php');
require('../../mailgun-inc.php');


//get the posted data
$token = trim($_POST['f']);
$email_address = trim(sanitize($_POST['email']));
$email_address = strtolower($email_address);

//check to see if form token exists
if ( !isset( $_SESSION["form_token"] ) || $token != $_SESSION["form_token"]){
  
  $response = array(
    'api' => apiName, 
    'version' => apiVersion, 
    'status' => 'fail', 
    'error' => 'true', 
    'msg' => 'Please refresh your browser and try again.', 
    'results' => 'none'
  );
  
  respond($response);

  die;
}

if ($email_address == "") {

  $response = array(
    'api' => apiName, 
    'version' => apiVersion, 
    'status' => 'fail', 
    'error' => 'true', 
    'msg' => 'Please choose a valid email address.', 
    'results' => 'none'
  );
  
  respond($response);

  die;
}

$ret = _validateEmail($email_address);

if ( $ret != "0") {

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

$timestamp = time();
$ip = $_SERVER['REMOTE_ADDR'];
$ip_seed = md5($ip);
$seed = uniqid() . $timestamp . $ip_seed;

//test if this user has recovered more than 2 times today
if ( checkUserSubmission($ip) > 1) {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => "I'm sorry, you can only recover a password  2 times per day.", 
      'results' => 'none'
    );

    respond($response);

    die;
}

//test if this email exists in a non expired invite
if ( checkEmailAccountInvite($email_address) > 0) {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => "I'm sorry, a recovery email was sent to this email address. Please be sure to check your SPAM folder. If you haven't received it in 3 hours, please try again.", 
      'results' => 'none'
    );

    respond($response);

    die;
}

//test to see if this email is in the system
if ( checkEmailAccount($email_address) == 0) {

    //this account does not exist... tell the user it's all good.. but do nothing.
    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'success', 
      'error' => 'false', 
      'msg' => 'Ok.', 
      'results' => $results
    );

    respond($response);

    die;
}

$html = getHTML($seed);

$params = array(
    'from'      => INVITE_FROM_EMAIL,
    'to'        => $email_address,
    'subject'   => APPNAME . ' - Password Reset',
    'html'      => $html,
    'text'      => APPNAME . ' - Password Reset. This looks so much better with HTML enabled.'
  );

$request = 'https://api.mailgun.net/v3/ionquiz.com/messages';

// Generate curl request
$session = curl_init($request);

curl_setopt($session, CURLOPT_USERPWD, MAILGUN_PRIVATE_KEY);

// Tell curl to use HTTP POST
curl_setopt ($session, CURLOPT_POST, true);
// Tell curl that this is the body of the POST
curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
// Tell curl not to return headers, but do return the response
curl_setopt($session, CURLOPT_HEADER, false);
// Tell PHP not to use SSLv3 (instead opting for TLS)
curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// obtain response
$results = curl_exec($session);
curl_close($session);

//add to DB

$timestamp = time();
$time_threehours = $timestamp + 10800;


$_parameterArray = array(
  ':email_address' => strtolower($email_address),
  ':ip_address' => $ip,
  ':create_int' => $timestamp,
  ':expire_int' => $time_threehours,
  ':seed' => $seed
);

//update the line with the user data
$_query = <<<EOT
    
    INSERT INTO password_reset
    (
      email_address,
      ip_address,
      create_int,
      expire_int,
      seed
    )
    VALUES
    (
      :email_address,
      :ip_address,
      :create_int,
      :expire_int,
      :seed
    )
EOT;

try {

  $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
  $stmt = $db->prepare($_query); 
  $stmt->execute($_parameterArray);
  $count = $stmt->rowCount();

  if($count != 0) {
  
    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'success', 
      'error' => 'false', 
      'msg' => 'Ok.', 
      'results' => $results
    );

  } else {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => $msg_error . $stmt->errorCode(),  
      'results' => 'none'
    );
  }

} catch(PDOException $e) {

  $response = array(
    'api' => apiName, 
    'version' => apiVersion, 
    'status' => 'fail', 
    'error' => 'true', 
    'msg' => 'Error 100220. Please seek support.', 
    'results' => 'none'
  );
}

$_SESSION["form_token"] = uniqid();

respond($response);

die;


/*
  ********
  ********
  ********
  FUNCTION
  ********
  ********
  ********
*/

function checkUserSubmission($ip){

  $timestamp = time();
  $time_threehoursago = $timestamp - 10800;
  
  //how many users with this IP address where posted time is greater than 3 hours ago
  $_parameterArray = array(
    ':ip_address' => $ip,
    ':create_int' => $time_threehoursago
  );

  $_query = <<<EOT
    SELECT 
      COUNT(id) as submitCount
    FROM 
      password_reset 
    WHERE 
      ip_address = :ip_address
    AND create_int > :create_int  
    AND create_int < $timestamp 

EOT;
  
  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ret = intval($results[0]["submitCount"]);
    
    return $ret;
  }
  catch(PDOException $e) {

    return -1;
  }
}


/*
  ********
  ********
  ********
  FUNCTION
  ********
  ********
  ********
*/

function checkEmailAccountInvite($email_address) {

  $timestamp = time();
  $time_threehoursago = $timestamp - 10800;
  
  //is there non expired, instance of this email address?
  $_parameterArray = array(
    ':email_address' => $email_address
  );

  $_query = <<<EOT
    SELECT 
      COUNT(id) as emailCount
    FROM 
      password_reset 
    WHERE 
      expire_int > $timestamp 
    AND email_address = :email_address

EOT;
  
  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results[0]["emailCount"];
  }
  catch(PDOException $e) {

    return -1;
  }
}


/*
  ********
  ********
  ********
  FUNCTION
  ********
  ********
  ********
*/

function checkEmailAccount($emailAddress){

  $_parameterArray = array(
    ':emailAddress' => $emailAddress
  );

  $_query = <<<EOT

  SELECT 
    COUNT(emailAddress) as howManyAccounts
  FROM 
    profile 
  WHERE 
    emailAddress = :emailAddress

EOT;

  //get the data
  try {

    $db = new PDO(conn . dbName, dbUser, dbPass);

    $stmt = $db->prepare($_query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)); 

    $stmt->execute($_parameterArray);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $howManyAccounts = $results[0]["howManyAccounts"];

    return $howManyAccounts;
  }
    catch(PDOException $e) {

    return -1;
  }
}


/*
  ********
  ********
  ********
  FUNCTION
  ********
  ********
  ********
*/

 
function getHTML($seed){

  $app_name = APPNAME;
  $site_url = SITEURL;

  $html = <<<EOT
    
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta name="viewport" content="width=device-width" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>$app_name Password Reset</title>
  <style type="text/css">
  /* -------------------------------------
      GLOBAL
      A very basic CSS reset
  ------------------------------------- */
  * {
    margin: 0;
    font-family: Arial, sans-serif;
    box-sizing: border-box;
    font-size: 16px;
  }

  img {
    max-width: 100%;
  }

  body {
    -webkit-font-smoothing: antialiased;
    -webkit-text-size-adjust: none;
    width: 100% !important;
    height: 100%;
    line-height: 1.6;
  }

  /* Let's make sure all tables have defaults */
  table td {
    vertical-align: top;
  }

  /* -------------------------------------
      BODY & CONTAINER
  ------------------------------------- */
  body {
    background-color: #f6f6f6;
  }

  .body-wrap {
    background-color: #f6f6f6;
    width: 100%;
  }

  .container {
    display: block !important;
    max-width: 600px !important;
    margin: 0 auto !important;
    /* makes it centered */
    clear: both !important;
  }

  .content {
    max-width: 600px;
    margin: 0 auto;
    display: block;
    padding: 20px;
  } 

  /* -------------------------------------
      HEADER, FOOTER, MAIN
  ------------------------------------- */
  .main {
    background-color: #fff;
    border: 1px solid #e9e9e9;
    border-radius: 3px;
  }

  .content-wrap {
    padding: 20px;
  }

  .content-block {
    padding: 0 0 20px;
  }

  .header {
    width: 100%;
    margin-bottom: 20px;
  }

  .footer {
    width: 100%;
    clear: both;
    color: #999;
    padding: 20px;
  }
  .footer a {
    color: #999;
  }
  .footer p, .footer a, .footer unsubscribe, .footer td {
    font-size: 12px;
  }

  /* -------------------------------------
      TYPOGRAPHY
  ------------------------------------- */
  h1, h2, h3 {
    font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
    color: #c1c1c1;
    margin: 40px 0 0;
    line-height: 1.2;
    line-height: 1.2;
    margin: -9px 0 17px;
  }

  h1 {
    font-size: 40px;
    font-weight: lighter !important;
    color: #eeeeee;
    font-family: Arial,"Lucida Grande",sans-serif;
    letter-spacing: -2px;
    line-height: 45px;
    margin: -9px 0 17px;
  }

  h2 {
    font-size: 24px;
  }

  h3 {
    font-size: 18px;
  }

  h4 {
    font-size: 14px;
    font-weight: 600;
  }

  p, ul, ol {
    margin-bottom: 10px;
    font-weight: normal;
  }
  p li, ul li, ol li {
    margin-left: 5px;
    list-style-position: inside;
  }

  /* -------------------------------------
      LINKS & BUTTONS
  ------------------------------------- */
  a {
    color: #348eda;
    text-decoration: underline;
  }

  .btn-primary {
    text-decoration: none;
    color: #FFF;
    background-color: #348eda;
    border: solid #348eda;
    border-width: 10px 20px;
    line-height: 2;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
    display: inline-block;
    border-radius: 5px;
    text-transform: capitalize;
  }

  /* -------------------------------------
      OTHER STYLES THAT MIGHT BE USEFUL
  ------------------------------------- */
  .last {
    margin-bottom: 0;
  }

  .first {
    margin-top: 0;
  }

  .aligncenter {
    text-align: center;
  }

  .alignright {
    text-align: right;
  }

  .alignleft {
    text-align: left;
  }

  .clear {
    clear: both;
  }

  /* -------------------------------------
      ALERTS
      Change the class depending on warning email, good email or bad email
  ------------------------------------- */
  .alert {
    font-size: 16px;
    color: #fff;
    font-weight: 500;
    padding: 20px;
    text-align: center;
    border-radius: 3px 3px 0 0;
  }
  .alert a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
  }
  .alert.alert-warning {
    background-color: #FF9F00;
  }
  .alert.alert-bad {
    background-color: #D0021B;
  }
  .alert.alert-good {
    background-color: #68B90F;
  }

  /* -------------------------------------
      INVOICE
      Styles for the billing table
  ------------------------------------- */
  .invoice {
    margin: 40px auto;
    text-align: left;
    width: 80%;
  }
  .invoice td {
    padding: 5px 0;
  }
  .invoice .invoice-items {
    width: 100%;
  }
  .invoice .invoice-items td {
    border-top: #eee 1px solid;
  }
  .invoice .invoice-items .total td {
    border-top: 2px solid #333;
    border-bottom: 2px solid #333;
    font-weight: 700;
  }

  /* -------------------------------------
      RESPONSIVE AND MOBILE FRIENDLY STYLES
  ------------------------------------- */
  @media only screen and (max-width: 640px) {


    h2 {
      font-size: 18px !important;
    }

    h3 {
      font-size: 16px !important;
    }

    .container {
      width: 100% !important;
    }

    .content, .content-wrap {
      padding: 10px !important;
    }

    .invoice {
      width: 100% !important;
    }
  }

  /*# sourceMappingURL=styles.css.map */

  </style>
  </head>

  <body itemscope itemtype="http://schema.org/EmailMessage">

  <table class="body-wrap">
    <tr>
      <td></td>
      <td class="container" width="600">
        <div class="content">
          <table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope itemtype="http://schema.org/ConfirmAction">
            <tr>
              <td class="content-wrap">
                <meta itemprop="name" content="Confirm Email"/>
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td class="content-block">
                      <p><img alt="" height="69" src="$site_url/assets/logo.png" width="136" style="-ms-interpolation-mode: bicubic;border: 0;height: auto;line-height: 100%;outline: none;text-decoration: none;"></p>
                    </td>
                  </tr>
                  <tr>
                    <td class="content-block">
                      <h1>Password Reset</h1>
                      <p>Click the button below to reset the password for your $app_name account.</a></p>
                    </td>
                  </tr>
                  <tr>
                    <td class="content-block" itemprop="handler" itemscope itemtype="http://schema.org/HttpActionHandler">
                      <a href="$site_url/recovery/password_recovery.php?q=$seed" class="btn-primary" itemprop="url">Reset Password</a>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <div class="footer">
            <table width="100%">
              <tr>
                <td class="aligncenter content-block">
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
      
                </td>
              </tr>
            </table>
          </div></div>
      </td>
      <td></td>
    </tr>
  </table>

  </body>
  </html>


EOT;

  return $html;
}

?>