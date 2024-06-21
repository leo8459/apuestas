<?php
$servername = "172.65.10.170";
$username = "root";
$password = "Correos.2022";
$dbname = "apuestas_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
