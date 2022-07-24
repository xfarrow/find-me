<?php

  // display errors (debug)
  //ini_set('display_errors', '1');
  //error_reporting(E_ALL);

  require './includes/phpmailer/PHPMailer.php';
  require './includes/phpmailer/SMTP.php';
  require './includes/phpmailer/Exception.php';
  require './includes/Credentials.php';
  use PHPMailer\PHPMailer\PHPMailer;


  $connection = new mysqli(DATABASE_ADDRESS, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
  if ($connection -> connect_errno) {
    die();
  }

  $data_json = get_data_from_post_to_json($connection);
  $encrypted_data = encrypt($data_json);
  $activationLink = generate_unique_activation_link($connection, 32);

  $row_id = insert_into_table($encrypted_data, $activationLink, $connection);

  // Generate activationLink and qrValue
  $activationLink = http_protocol() . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)) . "/Activate.php?activationLink=" . $activationLink;
  $qrValue = http_protocol() . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)) . "/View.php?id=" . $row_id . "&data=" . $encrypted_data['Encrypted'];

  // If an email has been provided, send it the activationLink
  $email = json_decode($data_json,true)['Email'];
  if( !empty($email) ){
    send_email($email, $activationLink);
  }

  // Send the result to the Ajax request
  echo $qrValue . "%{DELIMITER}%" . $activationLink;

  // this function is used to egnerate a random activation key
  function random_key($length) {
      $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
      $key = '';

      for($i=0; $i < $length; $i++) {
          $key .= $pool[mt_rand(0, count($pool) - 1)];
      }
      return $key;
  }

  function send_email($email, $link){

    $mail = new PHPMailer(true);
    //$mail->SMTPDebug = 3; // debug

    // sender info
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->setFrom(SMTP_USERNAME, 'Find-My-Luggage Team');

    $mail->addAddress($email);
    $mail->Subject = 'Find-My-Luggage QR link activator';
    $mailContent = "<h1>Find-My-Luggage QR link activator</h1>
    Click on <a href=\"$link\">THIS LINK </a> to activate the QR in case of lost item.";
    $mail->Body = $mailContent;
    $mail->isHTML(true);

    return $mail->send();

    //echo 'Mailer Error: ' . $mail->ErrorInfo;
  }

  function get_data_from_post_to_json($connection){

    // filter for SQL Injection
    $name = $connection -> real_escape_string($_POST['Name']);
    $surname = $connection -> real_escape_string($_POST['Surname']);
    $city = $connection -> real_escape_string($_POST['City']);
    $state = $connection -> real_escape_string($_POST['State']);
    $zip = $connection -> real_escape_string($_POST['Zip']);
    $address = $connection -> real_escape_string($_POST['Address']);
    $phone1 = $connection -> real_escape_string($_POST['Phone1']);
    $phone2 = $connection -> real_escape_string($_POST['Phone2']);
    $email = $connection -> real_escape_string($_POST['Email']);

    $data_json = json_encode(array("Name" => $name, "Surname" => $surname,
                              "City" => $city, "Zip" => $zip,
                              "Address" => $address, "Phone1" => $phone1,
                              "Phone2" => $phone2, "Email" => $email,
                              "State" => $state));

    return $data_json;
  }

  function encrypt($data){

    $cipher = 'aes-128-ctr';

    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    $password = hash('sha256',openssl_random_pseudo_bytes(64));

    $encrypted_data = openssl_encrypt($data, $cipher, $password, 0, $iv);
    $encrypted_data = base64_encode($encrypted_data);

    $iv = base64_encode($iv);

    return array('Encrypted' => $encrypted_data,
                'Password' => $password,
                'InitVector' => $iv);
  }

  function generate_unique_activation_link($connection, $length){

    do{
      $activationLink = random_key($length);
      $sql_get_activationLink = sprintf("SELECT * FROM Users2 WHERE ActivationLink='%s'", $connection->real_escape_string($activationLink));
    }while($connection->query($sql_get_activationLink)->num_rows > 0);

    return $activationLink;

  }

  function insert_into_table($encrypted_data, $activationLink, $connection){

    $password = $encrypted_data['Password'];
    $iv = $encrypted_data['InitVector'];

    $statement = $connection->prepare('INSERT INTO Users2 (Password, InitializationVector, ActivationLink, activated) VALUES (?, ?, ?, false)');
    $statement->bind_param("sss", $password, $iv, $activationLink);
    $statement->execute();

    $rowId = $statement->insert_id;

    $statement->close();
    $connection->close();

    return $rowId;
  }

  function http_protocol(){
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
      return "https://";
    }
    else{
      return "http://";
    }
  }

?>
