<?php
$servername = "172.65.10.52";
$username = "root";
$password = "";
$dbname = "apuestas_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
