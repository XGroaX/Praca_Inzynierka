<?php
session_start();

if(!isset($_SESSION['logged'])){
    header('location: http://infolut1.cba.pl/Baza/');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie użytkownikami</title>
    <link rel="icon" href="http://infolut1.cba.pl/Baza/favicon/favicon.ico" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href='components/style.css' type='text/css'> -->
    <link rel="stylesheet" href="components/style_faktury.css">
</head>
<body>
<nav class="menu">

<?php

// POŁĄCZNENIE Z BAZĄ

    require_once "connect.php";

    $connect=@new mysqli($host,$db_user,$db_password,$db_name);          // $connected=false;

    $is_searching=false;

    if ($connect->connect_error) {

        $_SESSION['error']="Nie udało połączyć się z bazą danych.";

        header('location: http://infolut1.cba.pl/Baza/logout.php');

     }else{

        mysqli_set_charset($connect,"utf8");

        $connected=true;

     }

?>

      <div class="message">PAPIERNICZY</div>
      <div class='status'>
          <div class='flex'><a style='margin-right:10px;' class='log_out_container1' href="http://infolut1.cba.pl/Baza/zmiana_danych.php"><img src="http://infolut1.cba.pl/Baza/pictures/user.svg" style='width:26px;' alt="user"></a></div>
          <div class='flex'>

          <span>zalogowano jako: <?php echo $_SESSION['myLogin'] ?></span>

          <span class='statusOut'>

          <?php

          $_SESSION['super_user']=0;

          // WYŚWIETLENIE UPRAWNIEŃ

          $q_permission = "SELECT rank FROM `user` WHERE login = '".$_SESSION['myLogin']."'";

          if($rank=mysqli_query($connect,$q_permission)){

            $rank_row=mysqli_fetch_assoc($rank);

            if($rank_row['rank'] == 'Administrator' || $rank_row['rank'] == 'Moderator'){
                echo "Ranga: ".$rank_row['rank'];
                $_SESSION['super_user']=1;
            }else{
                $_SESSION['super_user']=0;
            }

          }
          ?>

          </span>

      </div>
          <div class='flex'><a class='log_out_container1' style='margin-left:10px' href="http://infolut1.cba.pl/Baza/logout.php"><img style='width:26px;' src="http://infolut1.cba.pl/Baza/pictures/log_out.svg" alt='Wyloguj'></a></div>

      </div>



      <div class='nav_inputs_container'>
      <div class='flex'><a class='log_out_container' href="http://infolut1.cba.pl/Baza/faktury.php">FAKTURY</a></div>
          <?php

          //OPCJE ADMINISTRATORA

                          if($_SESSION['super_user']==1){
                              echo "<div class='flex'><a class='log_out_container' href='http://infolut1.cba.pl/Baza/user_list.php'>ZARZĄDZAJ</a></div>";

                          }       
            ?>
      </div>

  </nav>
        <main class="result2" style="overflow-x:auto;">
        <button class='back' onclick="przeniesNaLink()">POWRÓT</button>
                        <script>
                        function przeniesNaLink() {
                        // Tutaj podaj adres URL, na który chcesz przekierować użytkownika
                        var odpowiedniLink = "http://infolut1.cba.pl/Baza";
                        
                        // Przekierowanie użytkownika na odpowiedni link
                        window.location.href = odpowiedniLink;
                        }
                        </script>
            <div class="login_box">
              <?php
                if ($connected != true) {
                    $_SESSION['error']="Nie udało połączyć się z bazą danych.";
                    header('location: http://infolut1.cba.pl/Baza/logout.php');
                 }else{
                    $q_list = "SELECT * FROM `user` WHERE `email` LIKE '%$search_term%' OR `id` = '$search_term' ORDER BY id";
                      echo "<form action='' method='post' style='margin-bottom:5px;'>
                            <input type='text' name='search_term' class='search_text' id='search_term' placeholder='Szukaj po adresie email lub ID użytkownika:'><button type='submit' class='send_word1' name='search_user'>Szukaj</button>
                            </form>";
                            // Check if the search form is submitted
                            if (isset($_POST['search_user'])) {
                            $search_term = $_POST['search_term'];

                            // Perform a search query
                            $q_search = "SELECT * FROM `user` WHERE `email` LIKE '%$search_term%' OR `id` = '$search_term'";
                            if ($result = mysqli_query($connect, $q_search)) {
                                $search_num_rows = mysqli_num_rows($result);

                                if ($search_num_rows > 0) {
                                    echo "<table>";
                                    echo "<tr>
                                            <th>ID</th>
                                            <th>Login</th>
                                            <th>Email</th>
                                            <th>Ranga</th>
                                            <th>Zmiana adresu</th>
                                            <th>Zmiana rangi</th>
                                            <th>Usuń</th>
                                        </tr>";

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>{$row['login']}</td>
                                                <td>{$row['email']}</td>
                                                <td>{$row['rank']}</td>
                                                <td>
                                                    <form action='' method='post'>
                                                        <input type='hidden' name='id_to_change_email' value='{$row['id']}'>
                                                        <input type='text' name='email_change[{$row['id']}]' placeholder='Nowy email' class='input-field' required>
                                                        <button type='submit' name='change_email' class='send_word1'>Zmień email</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form action='' method='post'>
                                                        <input type='hidden' name='id_to_change' value='{$row['id']}'>
                                                        <select name='rank_change' class='input-field'>
                                                            <option value='2'>Administrator</option>
                                                            <option value='1'>Użytkownik</option><!-- Dodaj opcje dla innych rang -->
                                                        </select>
                                                        <button type='submit' name='change_rank' class='send_word1'>Zmień rangę</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form action='' method='post'>
                                                        <input type='hidden' name='id_to_delete' value='{$row['id']}'>
                                                        <button style='border:none;cursor:pointer;' type='submit' name='user_delete'>
                                                            <img src='http://infolut1.cba.pl/Baza/pictures/remove.png'>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                    }

                                    echo "</table>";
                                } else {
                                    echo "<div class='message'>Nie znaleziono użytkowników.</div>";
                                }

                                mysqli_free_result($result);
                            } else {
                                echo "<div class='message'>Wystąpił błąd: " . mysqli_error($connect) . "</div>";
                            }
                            } else {
                            // Display all users when no search is performed
                            $q_list = "SELECT * FROM `user` ORDER BY id";
                            if ($list = mysqli_query($connect, $q_list)) {
                                echo "<table>";
                                echo "<tr>
                                        <th>ID</th>
                                        <th>Login</th>
                                        <th>Email</th>
                                        <th>Ranga</th>
                                        <th>Zmiana adresu</th>
                                        <th>Zmiana rangi</th>
                                        <th>Usuń</th>
                                    </tr>";

                                while ($list_row = mysqli_fetch_assoc($list)) {
                                    echo "<tr>
                                            <td>{$list_row['id']}</td>
                                            <td>{$list_row['login']}</td>
                                            <td>{$list_row['email']}</td>
                                            <td>{$list_row['rank']}</td>
                                            <td>
                                                <form action='' method='post'>
                                                    <input type='hidden' name='id_to_change_email' value='{$list_row['id']}'>
                                                    <input type='text' name='email_change[{$list_row['id']}]' placeholder='Nowy email' class='input-field' required>
                                                    <button type='submit' name='change_email' class='send_word1'>Zmień email</button>
                                                </form>
                                            </td>
                                            <td>
                                                <form action='' method='post'>
                                                    <input type='hidden' name='id_to_change' value='{$list_row['id']}'>
                                                    <select name='rank_change' class='input-field'>
                                                        <option value='2'>Administrator</option>
                                                        <option value='1'>Użytkownik</option><!-- Dodaj opcje dla innych rang -->
                                                    </select>
                                                    <button type='submit' name='change_rank' class='send_word1'>Zmień rangę</button>
                                                </form>
                                            </td>
                                            <td>
                                                <form action='' method='post'>
                                                    <input type='hidden' name='id_to_delete' value='{$list_row['id']}'>
                                                    <button style='border:none;cursor:pointer;' type='submit' name='user_delete'>
                                                        <img src='http://infolut1.cba.pl/Baza/pictures/remove.png'>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>";
                                }

                                echo "</table>";
                            }
                            }
                 }
//USUWANIE UŻYTKONIKÓW I NADAWANIE RANG
              if(isset($_POST['user_delete'])){
                $q="DELETE FROM `user` WHERE `user`.`id` = ".$_POST['id_to_delete'];
                if($q_user=mysqli_query($connect,$q)){
                  $_SESSION['manager_error']= 'Usunięto konto o id '.$_POST['id_to_delete'];
                  header('Refresh:0');
                }
              }
              if (isset($_POST['change_rank'])) {
                $new_rank = $_POST['rank_change'];
                $user_id = $_POST['id_to_change'];
                
                require_once "connect.php";
                $connect = @new mysqli($host, $db_user, $db_password, $db_name);
            
                if ($connect->connect_error) {
                    $_SESSION['error'] = "Nie udało połączyć się z bazą danych.";
                    header('location: http://infolut1.cba.pl/Baza/logout.php');
                    exit();
                } else {
                    mysqli_set_charset($connect, "utf8");
            
                    $q_update_rank = "UPDATE `user` SET `rank` = '$new_rank' WHERE `id` = '$user_id'";
                    if ($q_user = mysqli_query($connect, $q_update_rank)) {
                        $_SESSION['manager_error'] = 'Zmieniono rangę użytkownika o ID ' . $user_id;
                        header('Refresh:0');
                        exit();
                    } else {
                        $_SESSION['manager_error'] = 'Nie udało się zmienić rangi użytkownika o ID ' . $user_id;
                        header('Refresh:0');
                        exit();
                    }
                }
              }
                // Handling the email change - check if the form was submitted
                if (isset($_POST['change_email'])) {
                  foreach ($_POST['email_change'] as $user_id_email_change => $new_email) {
                      if (!empty($new_email)) { // Ensure the new email is not empty
                          $q_update_email = "UPDATE `user` SET `email` = '$new_email' WHERE `id` = '$user_id_email_change'";
                          if ($q_email_change = mysqli_query($connect, $q_update_email)) {
                              $_SESSION['manager_error'] = 'Zmieniono email użytkownika o ID ' . $user_id_email_change;
                              header('Refresh:0');
                              exit();
                          } else {
                              $_SESSION['manager_error'] = 'Nie udało się zmienić emaila użytkownika o ID ' . $user_id_email_change;
                              header('Refresh:0');
                              exit();
                          }
                      }
                  }
                }
              ?>
              <?php
              if(isset($_SESSION['manager_error'])){
                echo "<div class='message'>".$_SESSION['manager_error']."</div>";
              }
              ?>
          </div>
            </main>


        <?php
          if($connected==true){
            mysqli_close($connect);
          }
        ?>

</body>
</html>