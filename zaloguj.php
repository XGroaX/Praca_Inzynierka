<?php
session_start();
if(isset($_SESSION['logged'])){
    header('location: http://infolut1.cba.pl/Baza/database.php');
    exit();
}
// }else if(!isset($_SESSION['logged'])){
//     header('location: http://infolut1.cba.pl/Baza/');
//     exit();
// }

require_once "connect.php";
$connect=@new mysqli($host,$db_user,$db_password,$db_name);
if ($connect->connect_error) {
$_SESSION['error']="<font color='red'>Nie udało połączyć się z bazą danych</font>";
}else{
    $log=$_POST['login'];
    $pass=$_POST['password'];
    if($log=='' || $pass==''){
      $_SESSION['error']='Nie wprowadzono użytkownika lub hasła.';
      header('location: http://infolut1.cba.pl/Baza');
    }else{
      $sql="SELECT login,password FROM user WHERE login = '$log'";
      if($result= @mysqli_query($connect,$sql)){
          $num_users = mysqli_num_rows($result);
          $row=mysqli_fetch_assoc($result);
          if(password_verify($pass,$row['password'])){
              $_SESSION['myLogin']=$log;
              $_SESSION['logged']=true;
              $_SESSION['my_cart']=array();
              unset($_SESSION['error']);
              $result->free();
              header('location: http://infolut1.cba.pl/Baza/database.php');
          }else{
              $_SESSION['error']='Nieprawidłowy login lub hasło.';
              header('location: http://infolut1.cba.pl/Baza/');
          }
      }else{
          $_SESSION['error']='Nie udało połączyć się z bazą danych';
          header('location: http://infolut1.cba.pl/Baza/');
      }

      // USTAWIENIE HASŁA
      // $pass=$_POST['password'];
      // $pass_hash=password_hash($pass,PASSWORD_DEFAULT);
      // $sql="INSERT INTO user (`password`) VALUES ('$pass_hash')";
      // mysqli_query($connect,$sql);
      // $_SESSION['error']="Ustawiono hasło!";
      // header('location: http://localhost:8080/Baza/');
      // DEFICYT123
    }
    mysqli_close($connect);
}
?>
