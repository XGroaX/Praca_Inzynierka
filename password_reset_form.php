<?php
session_start();

// Include your database connection file
require_once "connect.php";

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($connect, $_GET['token']);

    // Check if the token exists in the database
    $check_token_query = "SELECT * FROM `user` WHERE `reset_token` = '$token'";
    $check_token_result = mysqli_query($connect, $check_token_query);

    if ($check_token_result && mysqli_num_rows($check_token_result) > 0) {
        // Token is valid, allow the user to reset their password
        if (isset($_POST['reset_password'])) {
            $new_password = mysqli_real_escape_string($connect, $_POST['new_password']);
            $confirm_password = mysqli_real_escape_string($connect, $_POST['confirm_password']);

            // Check if the passwords match
            if ($new_password === $confirm_password) {
                // Update the password in the database
                $user_data = mysqli_fetch_assoc($check_token_result);
                $user_id = $user_data['id'];

                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_query = "UPDATE `user` SET `password` = '$hashed_password', `reset_token` = NULL WHERE `id` = '$user_id'";
                $update_password_result = mysqli_query($connect, $update_password_query);

                if ($update_password_result) {
                    $_SESSION['success'] = "Resetowanie hasła zakończone powodzeniem. Możesz teraz zalogować się za pomocą nowego hasła";
                    header("location: http://infolut1.cba.pl/Baza/index.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Wystąpił błąd podczas aktualizacji hasła. Proszę spróbować ponownie";
                }
            } else {
                $_SESSION['error'] = "Hasła nie pasują do siebie.";
            }
        }

        // Display password reset form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href='components/style_login.css' type='text/css'>
            <title>Password Reset</title>
        </head>
        <body>
            <div class="login_container">
                <form action="" method="post">
                    <div class="login_box">
                        <label for="new_password">Nowe hasło</label>
                        <input type="password" class='password' name="new_password" required>
                        
                        <label for="confirm_password">Powtórz hasło:</label>
                        <input type="password" class='password' name="confirm_password" required>
                        
                        <input type="submit" class='submit' name="reset_password" value="Zresetuj Hasło">
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        $_SESSION['error'] = "Invalid or expired reset token.";
        header("location: http://infolut1.cba.pl/Baza/index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Reset token not provided.";
    header("location: http://infolut1.cba.pl/Baza/index.php");
    exit();
}

mysqli_close($connect);
?>