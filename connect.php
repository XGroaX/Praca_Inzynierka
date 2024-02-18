<?php
  $host="infolut.cba.pl";
	$db_user="filip02521";
	$db_password ="Deathtrack1";
	$db_name="filip02521";

	$connect = mysqli_connect($host, $db_user, $db_password, $db_name);
	$connect->set_charset("utf8mb4");

	if (!$connect) {
		die("Connection failed: " . mysqli_connect_error());
	}
?>
