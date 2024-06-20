<?php
$servername = "172.65.10.50";
$username = "agbc";
$password = "Correos.2023";
$dbname = "apuestas_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
