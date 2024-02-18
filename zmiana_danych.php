<?php
session_start();

if(!isset($_SESSION['logged'])){
    header('location: http://infolut1.cba.pl/Baza/');
    exit();
}

// Include your database connection file here
require_once "connect.php";
$connect=@new mysqli($host,$db_user,$db_password,$db_name);     
if(isset($_SESSION['logged']) && $connect) {
    $id_user = $_SESSION['user_id']; 

    if(isset($_POST['submit'])) {
        // Retrieve form data
        $company_name = $_POST['company_name'];
        $company_address = $_POST['company_address'];
        $company_nip = $_POST['company_nip'];

        // Sanitize data to prevent SQL injection
        $company_name = $connect->real_escape_string($company_name);
        $company_address = $connect->real_escape_string($company_address);
        $company_nip = $connect->real_escape_string($company_nip);

        // Check if data exists for the user
        $check_query = "SELECT * FROM DetaleFirm WHERE user_id = $id_user";
        $result = $connect->query($check_query);

        if($result->num_rows > 0) {
            // Data exists, update
            $update_query = "UPDATE DetaleFirm 
                             SET Nazwa_Firmy = '$company_name', 
                                 Adres_Firmy = '$company_address', 
                                 NIP = '$company_nip' 
                             WHERE user_id = $id_user";

            // Perform the update
            if($connect->query($update_query) === TRUE) {
                echo "Dane firmy zostały zaktualizowane pomyślnie.";
            } else {
                echo "Błąd podczas aktualizacji danych firmy: " . $connect->error;
            }
        } else {
            // Data doesn't exist, insert
            $insert_query = "INSERT INTO DetaleFirm (user_id, Nazwa_Firmy, Adres_Firmy, NIP) 
                             VALUES ('$id_user', '$company_name', '$company_address', '$company_nip')";

            // Perform the insert
            if($connect->query($insert_query) === TRUE) {
                echo "Dane firmy zostały dodane pomyślnie.";
            } else {
                echo "Błąd podczas dodawania danych firmy: " . $connect->error;
            }
        }

        // Close database connection
        $connect->close();
    }
}

// Retrieve current data for display
$retrieve_query = "SELECT * FROM DetaleFirm WHERE user_id = $id_user";
$result = $connect->query($retrieve_query);

// Check if data exists
if($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_company_name = $row['Nazwa_Firmy'];
    $current_company_address = $row['Adres_Firmy'];
    $current_company_nip = $row['NIP'];
} else {
    $current_company_name = "";
    $current_company_address = "";
    $current_company_nip = "";
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="icon" href="http://infolut1.cba.pl/Baza/favicon/favicon.ico" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href='components/style_login.css' type='text/css'>
    <title>Zmiana danych firmy</title>
</head>
<body>
    <div class="login_container">
    <form method="post">
        <div class="login_box">
                <span class='login_header'>SZCZEGÓŁY FIRMY</span>
                <input type="text" class='password' name="company_name" placeholder="Nazwa firmy" value="<?php echo $current_company_name; ?>">
                <input type="text" class='password' name="company_address" placeholder="Adres firmy" value="<?php echo $current_company_address; ?>">
                <input type="text" class='password' name='company_nip' placeholder="NIP" value="<?php echo $current_company_nip; ?>">
                <input type='submit' class='submit' name='submit' value="Zmień">
            <a class='login_button' style='align-self:flex-start;' href='http://infolut1.cba.pl/Baza/database.php'>Wróć</a>
            <?php
            if(isset($_SESSION['company_reg_error'])){
                echo "<div class='message'>".$_SESSION['company_reg_error']."</div>";
            }
            ?>
        </div>
    </form>
    </div>
</body>
</html>