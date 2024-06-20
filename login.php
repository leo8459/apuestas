<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $contraseña = $_POST['contraseña'];
    
    $stmt = $conn->prepare("SELECT id, contraseña, es_admin FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hash, $es_admin);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($contraseña, $hash)) {
        $_SESSION['usuario_id'] = $id;
        $_SESSION['es_admin'] = $es_admin;
        header("Location: index.php");
        exit;
    } else {
        echo "Nombre de usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="background-image">
    <div class="header">
        <h1>Iniciar Sesión</h1>
    </div>
    <div class="container">
        <form method="post" action="">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="contraseña">Contraseña:</label>
            <input type="password" id="contraseña"" name="contraseña"" required>

            <button type="submit">Iniciar sesión</button>
        </form>

        <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>

        <div class="footer">
            <p>&copy; 2024 Apuestas de Fútbol</p>
        </div>
    </div>
</body>
</html>
