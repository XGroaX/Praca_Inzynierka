<?php
session_start();
if(isset($_SESSION['logged'])){
    header('location: http://infolut1.cba.pl/Baza/database.php');
    exit();
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300&display=swap" rel="stylesheet"> -->
    <link rel="icon" href="http://infolut1.cba.pl/Baza/favicon/favicon.ico" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href='components/style_login.css' type='text/css'>
    <title>Rejestracja konta</title>
</head>
<body>
        <div class="login_container">
          <form action="register_now.php" method="post">
            <div class="login_box">
              <span class='login_header'>REJESTRACJA</span>
              <input type="text" class='password' name="reg_login" placeholder="Nazwa użytkownika">
              <input type="text" class='password' name="reg_email" placeholder="Adres email">
              <input type="password" class='password' name='reg_pass1' placeholder="Hasło">
              <input type="password" class='password' name='reg_pass2' placeholder="Powtórz hasło">
              <label class='checkbox_class'><input type="checkbox" name='reg_check'> Akceptuje regulamin</label>
              <input type='submit' class='submit' name='submit' value="Zarejestruj">
              <a class='login_button' style='align-self:flex-start;' href='http://infolut1.cba.pl/Baza/'>Logowanie</a>
              <?php
              if(isset($_SESSION['reg_error'])){
                echo "<div class='message'>".$_SESSION['reg_error']."</div>";
              }
              ?>
            </form>
          </div>
        </div>



</body>
</html>
