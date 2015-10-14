<?php  

//Include to provide logging throughout the site

  function logThis($msg, $profile_id, $logType){
    
    $serverInfo = "ip:" . $_SERVER['REMOTE_ADDR'] . " referrer: " . $_SERVER['HTTP_REFERER'] . " req method:"  . $_SERVER['REQUEST_METHOD'];
    $msg .= " - " . $serverInfo;

    $_parameterArray = array(
      ':profile_id' => $profile_id,
      ':logType' => $logType,
      ':logMessage' => $msg
    );

    $_query = <<<EOT
        
        INSERT INTO log
        (
          profile_id,
          logType,
          logMessage
        )
        VALUES
        (
          :profile_id,
          :logType,
          :logMessage
        )
EOT;

    try {

      $db = new PDO(conn . dbName, dbUser, dbPass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
      $stmt = $db->prepare($_query); 
      $stmt->execute($_parameterArray);
      $count = $stmt->rowCount();

      if($count != 0) {
        

      } else {

        $msg = "When trying to log this message: " . $logMessage . " I recieved this message: " . $stmt->errorCode() . ". In module [log-inc.php]=>logthis";
        __sendAdminAlert($msg);

      }

    } catch(PDOException $e) {

      $msg = "When trying to log this message: " . $logMessage . " the database connection failed. In module [log-inc.php]=>logthis";
      __sendAdminAlert($msg);
      
    }
  }

  function __sendAdminAlert ($msg){

    //REQUIRES MAILGUN-INC
    __emailAlert($msg, ALERT_EMAIL_ADDRESS);

  }

?>