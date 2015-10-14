<?php  

//your mailgun API keys
const MAILGUN_PUBLIC_KEY = ''; //i.e. pubkey-1234567890
const MAILGUN_PRIVATE_KEY = ''; //i.e. api:key-1234567890

//your email globals
const INVITE_FROM_EMAIL = ''; //i.e. invitation@glancedate.com
const ALERT_EMAIL_ADDRESS = ''; // i.e. admin@you.com

/*****************************************
******************************************
******************************************
******************************************
******************************************
Function: _validateEmail
******************************************
******************************************
******************************************
******************************************
*****************************************/

function _validateEmail($emailAddress){

  //this just checks the email for validity and not for use
  
  //check for empty email
  if ($emailAddress == "") {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => "All fields need to be completed and the email address cannot be an empty value. Please check this and try again.", 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }

    $pub_api_key = MAILGUN_PUBLIC_KEY;
    $validate_url = 'https://api.mailgun.net/v2/address/validate?address=' . $emailAddress . '&api_key=' . $pub_api_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $validate_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = str_replace("false", "0", $response);
    $response = str_replace("true", "1", $response);

    //echo $response;
    $a = json_decode($response);

    //print_r($a);

    $ret = "0";

    if ($a->is_valid != "1") {
      
      return "Your email address: " . $emailAddress . " cannot be verified. Please check this and try again.";
    
    } else {

      return $ret;
    }
}

/*****************************************
******************************************
******************************************
******************************************
******************************************
Function: validateEmail
******************************************
******************************************
******************************************
******************************************
*****************************************/

function validateEmail($emailAddress){

  //check for empty email
  if ($emailAddress == "") {

    $response = array(
      'api' => apiName, 
      'version' => apiVersion, 
      'status' => 'fail', 
      'error' => 'true', 
      'msg' => "All fields need to be completed and the email address cannot be an empty value. Please check this and try again.", 
      'results' => 'none'
    );
    
    respond($response);

    die;
  }

    $pub_api_key = MAILGUN_PUBLIC_KEY;
    $validate_url = 'https://api.mailgun.net/v2/address/validate?address=' . $emailAddress . '&api_key=' . $pub_api_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $validate_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = str_replace("false", "0", $response);
    $response = str_replace("true", "1", $response);

    //echo $response;
    $a = json_decode($response);

    $ret = "0";

    if ($a->is_valid != "1") {
      
      return "Your email address: " . $emailAddress . " cannot be verified. Please check this and try again.";
    
    } else {

      //check for duplicate
    $_parameterArray = array(
      ':emailAddress' => $emailAddress
    );

    $_query = <<<EOT
      SELECT 
        emailAddress
      FROM 
        profile 
      WHERE 
        emailAddress = :emailAddress
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
        $ret = "0";

      } else {

        $ret = "This email address is being used by another member. Please try another email address.";
      }
    }
    catch(PDOException $e) {

      $ret = "Could not finish the task. Error 100220. Please contact our support team.";
    }
  }
    
    return $ret;
}

function __emailAlert($msg, $email_address){

  $serverInfo = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
  $msg .= " - " . $serverInfo;

  $html = __getEmailTemplate($msg);

  $params = array(
      'from'      => INVITE_FROM_EMAIL,
      'to'        => $email_address,
      'subject'   => APPNAME . ' - Email Alert - FROM ' . SITEURL,
      'html'      => $html,
      'text'      => 'This is an email alert.'
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
  $response = curl_exec($session);
  curl_close($session);
}

function __getEmailTemplate($msg){
  
  $app_name = APPNAME;
  $site_url = SITEURL;

  $html = <<<EOT
    
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta name="viewport" content="width=device-width" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>$app_name - Email Alert</title>
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
    padding: 0 0 16px;
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
                      
                    </td>
                  </tr>
                  <tr>
                    <td class="content-block">
                      <h1>Email Alert</h1>
                      <p>$msg</p>
                    </td>
                  </tr>
                  <tr>
                    <td class="content-block" itemprop="handler" itemscope itemtype="http://schema.org/HttpActionHandler">
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