<html>

  <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </head>

  <body>
    <?php
      // display errors (debug)
      ini_set('display_errors', '1');
      error_reporting(E_ALL);

      require './includes/credentials.php';

      if(!isset($_GET['id']) || !isset($_GET['data'])){
        showError('Invalid link.');
        exit();
      }

      $connection = new mysqli($server, $database_username, $database_password, $database_name);

      $id_table = $connection -> real_escape_string($_GET['id']);
      $data = base64_decode($_GET['data']);

      $statement = $connection->prepare("SELECT Password, InitializationVector, activated FROM Users2 WHERE ID = ?");
      $statement->bind_param("d", $id_table);
      $statement->execute();

      $result = $statement->get_result();

      if($result->num_rows == 0){
        showError('Invalid link.');
        exit();
      }
      $row = $result->fetch_assoc();

      if($row['activated'] == false){
        showError("This item has not been lost. If you think it is, try again later.");
        exit();
      }
      else{
        $password = $row['Password'];
        $iv = base64_decode($row['InitializationVector']);

        $decrypted_data = openssl_decrypt($data, 'aes-128-ctr', $password, 0, $iv);
        $dataArray = json_decode($decrypted_data, true);


        $name = isset($dataArray['Name'])? $dataArray['Name'] : "";
        $surname = isset($dataArray['Surname'])? $dataArray['Surname'] : "";
        $city = isset($dataArray['City'])? $dataArray['City'] : "";
        $zip = isset($dataArray['Zip'])? $dataArray['Zip'] : "";
        $address = isset($dataArray['Address'])? $dataArray['Address'] : "";
        $phone1 = isset($dataArray['Phone1'])? $dataArray['Phone1'] : "";
        $phone2 = isset($dataArray['Phone2'])? $dataArray['Phone2'] : "";
        $email = isset($dataArray['Email'])? $dataArray['Email'] : "";
        $state = isset($dataArray['State'])? $dataArray['State'] : "";

        showData($name, $surname, $city, $zip, $address, $phone1, $phone2, $email, $state);
      }

      $statement->close();
      $connection->close();

      function showError($msg){
        echo '
        <div class="alert alert-warning" role="alert">
          <h4 class="alert-heading">An error has occurred</h4>
          <p>' . $msg . '</p>
        </div>
        ';
      }

      function showData($name, $surname, $city, $zip, $address, $phone1, $phone2, $email, $state){
        echo '
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">This item has been lost!</h4>
          <p>Please, return this item to its owner using the following info</p>
        </div>
        ';

        if(!empty($name)){
          echo '
            <label class="form-label">Name</label>
            <b><h3>' . $name . '</h3></b><hr>
          ';
        }

        if(!empty($surname)){
          echo '
            <label class="form-label">Surname</label>
            <b><h3>' . $surname . '</h3></b><hr>
          ';
        }

        if(!empty($city)){
          echo '
            <label class="form-label">City</label>
            <b><h3>' . $city . '</h3></b><hr>
          ';
        }

        if(!empty($zip)){
          echo '
            <label class="form-label">Zip</label>
            <b><h3>' . $zip . '</h3></b><hr>
          ';
        }

        if(!empty($address)){
          echo '
            <label class="form-label">Address</label>
            <b><h3>' . $address . '</h3></b><hr>
          ';
        }

        if(!empty($phone1)){
          echo '
            <label class="form-label">Phone 1</label>
            <b><h3>' . $phone1 . '</h3></b><hr>
          ';
        }

        if(!empty($phone2)){
          echo '
            <label class="form-label">Phone 2</label>
            <b><h3>' . $phone2 . '</h3></b><hr>
          ';
        }

        if(!empty($email)){
          echo '
            <label class="form-label">Email</label>
            <b><h3>' . $email . '</h3></b><hr>
          ';
        }

        if(!empty($state)){
          echo '
            <label class="form-label">State</label>
            <b><h3>' . $state . '</h3></b><hr>
          ';
        }

      }

      ?>
  </body>
</html>
