<?php
session_start();

if(!isset($_SESSION['logged'])){
    header('location: http://infolut1.cba.pl/Baza/');
    exit();
}

// Include database connection
require_once "connect.php";

$connect=@new mysqli($host,$db_user,$db_password,$db_name);

$is_searching=false;

if ($connect->connect_error) {
    $_SESSION['error']="Nie udało połączyć się z bazą danych.";
    header('location: http://infolut1.cba.pl/Baza/logout.php');
} else {
    mysqli_set_charset($connect,"utf8");
    $connected=true;
}

require('fpdf/fpdf.php'); // Include the FPDF library

// Sprawdź, czy użytkownik jest administratorem
$is_admin = isset($_SESSION['super_user']) ? $_SESSION['super_user'] : false;

// Handle PDF generation if the generate_pdf parameter is set
if (isset($_GET['generate_pdf']) && $_GET['generate_pdf'] == 'true') {
    // Pobierz ID faktury z adresu URL
    $id_faktury = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id_faktury) {
        // Create a new PDF instance
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Set font for the whole document
        $pdf->SetFont('Arial', '', 10);

        // Insert a logo in the top-left corner at 300 dpi
        $pdf->Image('pictures/logo.png', 10, 10, 40);

        // Company details
        $pdf->Cell(0, 10, '', 0, 0); // Empty cell to keep invoice details aligned to the right
        $pdf->Cell(0, 10, 'Data wystawienia: ' . date('Y-m-d'), 0, 1, 'R'); // Align to the right

        // Add some space
        $pdf->Ln(10);

        // Fetch company details based on user's session ID
        $user_id = $_SESSION['logged'];
        $query = "SELECT * FROM DetaleFirm WHERE user_id = $user_id";
        $result = mysqli_query($connect, $query);

        // Display company details in the PDF
        if ($row = mysqli_fetch_assoc($result)) {
            $nazwa_firmy = $row['Nazwa_Firmy'];
            $adres_firmy = $row['Adres_Firmy'];
            $nip = $row['NIP'];

            $pdf->Cell(0, 10, 'Sprzedawca', 0, 0, 'L');
            $pdf->Cell(0, 10, 'Nabywca', 0, 1, 'R'); // Align to the right

            $pdf->Cell(0, 10, 'Nazwa firmy: PaperIndustry', 0, 0, 'L');
            $pdf->Cell(0, 10, 'Nazwa firmy: ' . $nazwa_firmy, 0, 1, 'R'); // Align to the right

            $pdf->Cell(0, 10, 'Adres firmy: Papiernicz 21', 0, 0, 'L');
            $pdf->Cell(0, 10, 'Adres firmy: ' . $adres_firmy, 0, 1, 'R'); // Align to the right

            $pdf->Cell(0, 10, 'NIP: 5277672815', 0, 0, 'L');
            $pdf->Cell(0, 10, 'NIP: ' . $nip, 0, 1, 'R'); // Align to the right

            // Add some space
            $pdf->Ln(10);
        }

        // Table header
        $pdf->Cell(50, 10, 'Nazwa produktu', 1, 0, 'C'); // Zmniejsz szerokość kolumny
        $pdf->Cell(20, 10, 'Ilosc', 1, 0, 'C'); // Zmniejsz szerokość kolumny
        $pdf->Cell(42, 10, 'Cena jednostkowa (PLN)', 1, 0, 'C'); // Zmniejsz szerokość kolumny
        $pdf->Cell(30, 10, 'Ceny netto (PLN)', 1, 0, 'C'); // Zmniejsz szerokość kolumny
        $pdf->Cell(30, 10, 'Ceny brutto (PLN)', 1, 1, 'C'); // Zmniejsz szerokość kolumny

        // Fetch data from the DetaleFaktury table
        $query_detale_faktury = "SELECT * FROM DetaleFaktury WHERE id_faktury = $id_faktury";
        $result_detale_faktury = mysqli_query($connect, $query_detale_faktury);

        // Inicjalizacja sum
        $total_netto = 0;
        $total_brutto = 0;
        
        // Table data
        while ($row_detale_faktury = mysqli_fetch_assoc($result_detale_faktury)) {
            $nazwa_produktu = $row_detale_faktury['nazwa'];
            $ilosc_zakupionych = $row_detale_faktury['ilosc_zakupionych'];
            $cena_jednostkowa = $row_detale_faktury['cena_jednostkowa'];
            // Calculate totals
            $cena_netto = $ilosc_zakupionych * $cena_jednostkowa; // Replace with actual netto sum
            $cena_brutto = $cena_netto * 1.23; // 23% VAT

            // Obliczanie sum netto i brutto
            $total_netto += $cena_netto;
            $total_brutto += $cena_brutto;

            $nazwa_produktu = substr($nazwa_produktu, 0, 30); // Obetnij długi tekst do maksymalnie 30 znaków
            $pdf->Cell(50, 10, $nazwa_produktu, 1, 0, 'C');
            $pdf->Cell(20, 10, $ilosc_zakupionych, 1, 0, 'C');
            $pdf->Cell(42, 10, number_format($cena_jednostkowa, 2) . ' PLN', 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($cena_netto, 2) . ' PLN', 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($cena_brutto, 2) . ' PLN', 1, 1, 'C');
        }
            // Dodanie sum do tabeli
            $pdf->Cell(112, 10, 'Suma:', 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($total_netto, 2) . ' PLN', 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($total_brutto, 2) . ' PLN', 1, 1, 'C');

        // Output the PDF to the browser or save it to a file
        $pdf->Output('invoice.pdf', 'D'); // 'D' forces download, you can change this to 'F' to save to a file
        exit; // Exit to prevent further HTML output
    }
}


?>

<?php
// Pobierz daty do filtrowania (jeśli przesłane)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Pobierz ID do filtrowania (tylko dla administratora)
$filter_user_id = $is_admin && isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];

// Pobierz login użytkownika do filtrowania (tylko dla administratora)
$filter_user_login = $is_admin && isset($_GET['user_login']) ? $_GET['user_login'] : null;

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktury</title>
    <link rel="icon" href="http://infolut1.cba.pl/Baza/favicon/favicon.ico" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="http://infolut1.cba.pl/Baza/components/style_faktury.css">
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
          //PRZYPISANIE ID USERA DO ZMIENNEJ SESYJNEJ
          $q_id = "SELECT id FROM `user` WHERE login = '".$_SESSION['myLogin']."'";
          if($to_add_id=mysqli_query($connect,$q_id)){
            $id_row=mysqli_fetch_assoc($to_add_id);
                $_SESSION['user_id'] = $id_row['id'];
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
    <main class='result1 sort' style="overflow-x:auto;">
        <!-- Formularz do wprowadzania dat -->
        <form action="faktury.php" method="get">
            <h1>WPISZ DANE</h1>
            <table>
                <tr>
                    <td><label for="start_date">Data początkowa:</label></td>
                    <td><input type="date" id="start_date" name="start_date" value="<?= $start_date ?>"></td>
                </tr>
                <tr>
                    <td><label for="end_date">Data końcowa:</label></td>
                    <td><input type="date" id="end_date" name="end_date" value="<?= $end_date ?>"></td>
                </tr>
                <?php if ($is_admin): ?>
                    <tr>
                        <td><label for="user_id">ID użytkownika:</label></td>
                        <td><input type="text" id="user_id" name="user_id" value="<?= isset($_GET['user_id']) ? $_GET['user_id'] : '' ?>"></td>
                    </tr>
                <?php endif; ?>
                
                <?php if ($is_admin): ?>
                    <tr>
                        <td><label for="user_login">Login użytkownika:</label></td>
                        <td><input type="text" id="user_login" name="user_login" value="<?= isset($_GET['user_login']) ? $_GET['user_login'] : '' ?>"></td>
                    </tr>
                <?php endif; ?>
            </table>
            <button class='send_word' type="submit">Filtruj</button>
        </form>
                        <button class='back' onclick="przeniesNaLink()">POWRÓT</button>
                        <script>
                        function przeniesNaLink() {
                        // Tutaj podaj adres URL, na który chcesz przekierować użytkownika
                        var odpowiedniLink = "http://infolut1.cba.pl/Baza";
                        
                        // Przekierowanie użytkownika na odpowiedni link
                        window.location.href = odpowiedniLink;
                        }
                        </script>
    </main>
    <div class='result1 invoice'>
    </script>
        <?php
            if(isset($_SESSION['logged'])) {
                $id_user = $_SESSION['user_id']; 

            
                $sql_faktury = "SELECT * FROM `Faktury`";

            // Dodaj do zapytania warunki dla daty i ID klienta (jeśli dostępne)
            if ($start_date && $end_date) {
                $sql_faktury .= " WHERE data_zakupu BETWEEN '$start_date' AND '$end_date'";
            }
            if ($is_admin && $filter_user_id !== "") {
                if ($start_date || $end_date) {
                    $sql_faktury .= " AND id_user = $filter_user_id";
                } else {
                    $sql_faktury .= " WHERE id_user = $filter_user_id";
                }
            }                
            if ($is_admin && $filter_user_login !== "") {
                if ($start_date || $end_date || $filter_user_id) {
                    $sql_faktury .= " AND login LIKE '%$filter_user_login%'";
                } else {
                    $sql_faktury .= " WHERE login LIKE '%$filter_user_login%'";
                }
            }
                

                // Wykonaj zapytanie
                $result_faktury = mysqli_query($connect, $sql_faktury);

                // Sprawdź, czy zapytanie się powiodło
                if($result_faktury) {
                    // Wyświetl faktury
                    echo "<table class='products_list'>";
                    echo "
                    <tr>
                    <th style='width:50px;'>LOGIN</th>
                    <th style='width:50px;'>NR FAKTURY</th>
                    <th style='width:100px;'>DATA ZAKUPU</th>
                    <th>PRODUKT</th>
                    <th style='width:50px;'>SUMA</th>
                    <th style='width:50px;'>ZAPŁAĆ</th>
                    </tr>";
                    while ($row_faktury = mysqli_fetch_assoc($result_faktury)) {
                        $login = $row_faktury['login'];
                        $id_faktury = $row_faktury['id_faktury'];
                        $data_zakupu = $row_faktury['data_zakupu'];
                        echo "<tr>";
                        // Only display details if the user is an admin or if the invoice belongs to the current user
                        if ($is_admin || ($id_user == $row_faktury['id_user'])) {
                            echo "<td>$login</td>";
                            echo "<td>$id_faktury</td>";
                            echo "<td>$data_zakupu</td>";

                            // Inicjalizuj sumę cen dla danej faktury
                            $suma_cen = 0;

                            // Zapytanie SQL, które pobierze szczegóły faktury dla danego użytkownika
                            $sql_detale_faktury = "SELECT DetaleFaktury.*, Produkty.nazwa 
                                                    FROM `DetaleFaktury` 
                                                    INNER JOIN `Produkty` ON DetaleFaktury.id_produktu = Produkty.id_produktu 
                                                    WHERE DetaleFaktury.id_faktury = $id_faktury";

                            // Limituj dostęp dla usera
                            if (!$is_admin) {
                                $sql_detale_faktury .= " AND id_user = $id_user";
                            }

                            // Wykonaj zapytanie
                            $result_detale_faktury = mysqli_query($connect, $sql_detale_faktury);

                            // Sprawdź, czy zapytanie się powiodło
                            if ($result_detale_faktury) {
                                // Wyświetl szczegóły faktury
                                echo "<td>";
                                echo "<table class='product_list'>";
                                $i=0;
                                while ($row_detale_faktury = mysqli_fetch_assoc($result_detale_faktury)) {
                                    $i++;
                                    $nazwa_produktu = $row_detale_faktury['nazwa'];
                                    $ilosc_zakupionych = $row_detale_faktury['ilosc_zakupionych'];
                                    $cena_jednostkowa = $row_detale_faktury['cena_jednostkowa'];

                                    // Dodaj cenę produktu do sumy
                                    $suma_cen += $ilosc_zakupionych * $cena_jednostkowa;
                                    if($i==1){
                                        echo "<tr><th>Nazwa produktu</th><th>Ilość zakupionych</th><th>Cena jednostkowa</th></tr>";
                                    }
                                    echo "
                                        <tr>
                                            <td>$nazwa_produktu</td>
                                            <td>$ilosc_zakupionych</td>
                                            <td>$cena_jednostkowa zł</td>
                                        </tr>";
                                }
                                echo "</table>";
                                echo "</td>";
                            } else {
                                echo "Błąd podczas pobierania szczegółów faktury.";
                            }

                            // Wyświetl sumę cen dla danej faktury
                            // Modify "Do zapłacenia" to "Zapłacone" when payment is made
                            echo "<td>" . str_replace('Do zapłacenia', 'Zapłacone', $suma_cen) . " zł</td>";

                            // Add a "Pay" button with inline style for right alignment
                            echo "<td><form method='post' style='text-align: right;'>
                                <button type='submit' name='pay' class='payButton send_word' data-id='" . $id_faktury . "'>Zapłać</button>
                            </form></td></tr>";

                            // Add JavaScript to change the button text to "Confirm Payment" after clicking "Pay" and generate PDF on the second click
                            echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var buttons = document.querySelectorAll('.payButton');
                                buttons.forEach(function(button) {
                                    // Check if the button state is stored in localStorage
                                    var isClicked = localStorage.getItem('payButtonClicked_' + button.getAttribute('data-id'));
                                    if (isClicked) {
                                        button.innerHTML = 'Pobierz PDF';
                                    }
                            
                                    button.addEventListener('click', function(event) {
                                        event.preventDefault();
                                        if (!isClicked) {
                                            button.innerHTML = 'Pobierz PDF';
                                            // Store the button state in localStorage
                                            localStorage.setItem('payButtonClicked_' + button.getAttribute('data-id'), true);
                                        } else {
                                            document.location.href = 'faktury.php?generate_pdf=true&id=' + button.getAttribute('data-id');
                                        }
                                    });
                                });
                            });
                            </script>";

                        }
                    } 
                    echo "</table>";
            }
        }
            ?>
    </div>
</body>
</html>