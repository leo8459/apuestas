<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
    $es_admin = isset($_POST['es_admin']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, contraseña, es_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nombre, $contraseña, $es_admin);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="background-image">
    <div class="header">
        <h1>Registro de Usuario</h1>
    </div>
    <div class="container">
        <form method="post" action="">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="contraseña">Contraseña:</label>
            <input type="password" id="contraseña" name="contraseña" required>
            <button type="submit">Registrarse</button>
        </form>

        <div class="footer">
            <p>&copy; 2024 Apuestas de Fútbol</p>
        </div>
    </div>
</body>
</html>
