<?php
session_start();

// Include your database connection file
require_once "connect.php";

if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}

function validateMail($param) {
    $isValid = filter_var($param, FILTER_VALIDATE_EMAIL);
    if (!$isValid) {
        echo "Invalid email address: $param<br>";
    }
    return filter_var($param, FILTER_VALIDATE_EMAIL) ? $param : null;
}

if (isset($_POST['submit'])) {
    // Validate email
    $email = validateMail($_POST['email']);

    if (!$email) {
        $_SESSION['error'] = "Email address is required.";
    } else {
        // Check if the email exists in the database
        $check_email_query = "SELECT * FROM `user` WHERE `email` = '$email'";
        
        // Debugging: Output the SQL query
        //echo "SQL Query: $check_email_query<br>";
        
        $check_email_result = mysqli_query($connect, $check_email_query);

        if (!$check_email_result) {
            $_SESSION['error'] = "Error checking email in the database: " . mysqli_error($connect);
        } else {
            if (mysqli_num_rows($check_email_result) > 0) {
                // Email found in the database
                // Generate a unique token for password reset
                $token = bin2hex(random_bytes(32));
            
                // Store the token in the database along with the user's email
                $store_token_query = "UPDATE `user` SET `reset_token` = '$token' WHERE `email` = '$email'";
                $store_token_result = mysqli_query($connect, $store_token_query);
            
                if ($store_token_result) {
                    // Debugging: Output the token and email for verification
                    //echo "Token: $token<br>";
                    //echo "Email: $email<br>";
            
                    // Send a password reset email with a link containing the token
                    $reset_link = "http://infolut1.cba.pl/Baza/password_reset_form.php?token=$token";
                    $to = $email;
                    $subject = "Zresetuj hasło";
                    $message = "Kliknij poniższy link, aby zresetować hasło:\n$reset_link";
                    $headers = "From: filip02521@infolut1.cba.pl";
            
                    // Uncomment the line below to send the email (make sure your server supports mail())
                    if (mail($to, $subject, $message, $headers)) {
                        $_SESSION['success'] = "Wysłano e-mail z instrukcjami dotyczącymi resetowania hasła.";
                    } else {
                        $_SESSION['error'] = "Wystąpił błąd podczas wysyłania e-maila: " . error_get_last()['message'];
                    }
                } else {
                    $_SESSION['error'] = "Error storing reset token in the database: " . mysqli_error($connect);
                }
            } else {
                $_SESSION['error'] = "Adres e-mail nie został znaleziony w naszych danych.";
            }
        }
    }
}

mysqli_close($connect);

?>


<!-- Your HTML code for the forgot password form -->
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.gstatic.com">
<!-- <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300&display=swap" rel="stylesheet"> -->
<link rel="icon" href="http://infolut1.cba.pl/Baza/favicon/favicon.ico" type="image/x-icon" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
<link rel="stylesheet" href='components/style_login.css' type='text/css'>
<title>Niepamiętam hasła</title>
</head>
<body>
<div class="login_container">
<form action="password_reset.php" method="post">
    <div class="login_box">
        <span class='login_header'>RESETOWANIE HASŁA</span>
        <input type="text" class='password' name="email" placeholder="Wprowadź adres email">
        <input type='submit' class='submit' name='submit' value="Zresetuj hasło">
        <a class='login_button' href='http://infolut1.cba.pl/Baza/index.php'>Wróć do logowania</a>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='message'>".$_SESSION['error']."</div>";
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            echo "<div class='message success'>".$_SESSION['success']."</div>";
            unset($_SESSION['success']);
        }
        ?>
    </form>
</div>
</div>
</body>
</html>