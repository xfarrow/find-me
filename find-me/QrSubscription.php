<?php
  // display errors (debug)
  ini_set('display_errors', '1');
  error_reporting(E_ALL);

  require './includes/phpmailer/PHPMailer.php';
  require './includes/phpmailer/SMTP.php';
  require './includes/phpmailer/Exception.php';
  require './includes/credentials.php';
  use PHPMailer\PHPMailer\PHPMailer;

  $connection = new mysqli($server, $database_username, $database_password, $database_name);

  // Check connection
  if ($connection -> connect_errno) {
    die();
  }

  // filter for SQL Injection
  $name = $connection -> real_escape_string($_POST['Name']);
  $surname = $connection -> real_escape_string($_POST['Surname']);
  $city = $connection -> real_escape_string($_POST['City']);
  $zip = $connection -> real_escape_string($_POST['Zip']);
  $address = $connection -> real_escape_string($_POST['Address']);
  $phone1 = $connection -> real_escape_string($_POST['Phone1']);
  $phone2 = $connection -> real_escape_string($_POST['Phone2']);
  $email = $connection -> real_escape_string($_POST['Email']);
  $state = "UNKNOWN";

  // generate random strings
  do{
    $email_link = randomKey(32);
    $sql_get_email_link = sprintf("SELECT * FROM Users WHERE email_link='%s'", $connection->real_escape_string($email_link));
  }while($connection->query($sql_get_email_link)->num_rows > 0);

  do{
    $url_link = randomKey(32);
    $sql_get_url_link = sprintf("SELECT * FROM Users WHERE url_link='%s'", $connection->real_escape_string($url_link));
  }while($connection->query($sql_get_url_link)->num_rows > 0);

  //$stmt = $connection->prepare('INSERT INTO Users (firstName) VALUES (:first_name)',);
  //$stmt->execute(':first_name', $name);
  $statement = $connection->prepare('INSERT INTO Users (firstname, lastname, city, state, zip, address, phone1, phone2, email, activated, email_link, url_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, false, ?, ?)');
  $statement->bind_param("sssssssssss", $name, $surname, $city, $state, $zip, $address, $phone1, $phone2, $email, $email_link, $url_link);
  $statement->execute();

  $statement->close();
  $connection->close();

  if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    $httpProtocol = "https://";
  }
  else{
    $httpProtocol = "http://";
  }

  $email_link = $httpProtocol . $_SERVER['SERVER_NAME'] . "/find-me/activate.php?email_link=" . $email_link;
  $url_link = $httpProtocol . $_SERVER['SERVER_NAME'] . "/find-me/view.php?url_link=" . $url_link;

  if(!empty($email)){
    sendEmail($email, $email_link);
  }

  echo $url_link.";".$email_link;

  function randomKey($length) {
      $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
      $key = '';

      for($i=0; $i < $length; $i++) {
          $key .= $pool[mt_rand(0, count($pool) - 1)];
      }
      return $key;
  }

  function sendEmail($email, $link){

    $mail = new PHPMailer(true);
    //$mail->SMTPDebug = 3;

    // sender info
    $mail->isSMTP();
    $mail->Host = $GLOBALS['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $GLOBALS['smtp_username'];
    $mail->Password = $GLOBALS['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->setFrom($GLOBALS['smtp_username'], 'Find-Me Team');

    $mail->addAddress($email);
    $mail->Subject = 'Find-me QR link activator';
    $mailContent = "<h1>Find-me QR link activator</h1>
    Click on <a href=\"$link\">THIS LINK </a> to activate the QR in case of lost item.";
    $mail->Body = $mailContent;
    $mail->isHTML(true);

    return $mail->send();

    //echo 'Mailer Error: ' . $mail->ErrorInfo;
  }

?>
