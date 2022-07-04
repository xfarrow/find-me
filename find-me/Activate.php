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

      if(!isset($_GET['activationLink'])){
        showError('Invalid link.');
        exit();
      }

      $connection = new mysqli($server, $database_username, $database_password, $database_name);

      $activationLink = $connection -> real_escape_string($_GET['activationLink']);

      $statement = $connection->prepare("UPDATE Users2 SET activated = true WHERE ActivationLink = ?");
      $statement->bind_param("s", $activationLink);
      if($statement->execute()){
        showSuccess();
      }
      else{
        showError("Unable to activate. Try again later.");
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

      function showSuccess(){
        echo '
        <div class="alert alert-success" role="alert">
          <h4 class="alert-heading">Qr activated</h4>
          <p>The QR has been activated. From now on, your personal information will be
          visible to anyone scanning it.</p>
        </div>
        ';
      }
      ?>
  </body>
</html>
