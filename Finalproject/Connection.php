<?php
$servername = "localhost:3307";
$username   = "root";
$password   = "";
$dbname     = "data";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// echo "Database connected successfully";
?>
