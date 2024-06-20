<?php
session_start();
include 'db.php';

if (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['agregar_partido'])) {
        $equipo_local = $_POST['equipo_local'];
        $equipo_visitante = $_POST['equipo_visitante'];
        $fecha = $_POST['fecha'];

        $stmt = $conn->prepare("INSERT INTO partidos (equipo_local, equipo_visitante, fecha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $equipo_local, $equipo_visitante, $fecha);
        $stmt->execute();
    } elseif (isset($_POST['generar_pdf_multiple'])) {
        $apuesta_ids = $_POST['apuesta_id'];
        header("Location: generate_pdf.php?apuesta_ids=" . implode(',', $apuesta_ids));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="background-image">
    <div class="header">
        <h1>Panel de Administración</h1>
    </div>
    <div class="container">
        <h2>Agregar Partido</h2>
        <form method="post" action="">
            <label for="equipo_local">Equipo Local:</label>
            <input type="text" id="equipo_local" name="equipo_local" required>
            <label for="equipo_visitante">Equipo Visitante:</label>
            <input type="text" id="equipo_visitante" name="equipo_visitante" required>
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>
            <button type="submit" name="agregar_partido">Agregar Partido</button>
        </form>

        <h2>Ver Apuestas</h2>
        <form method="post" action="">
            <table>
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Partido</th>
                        <th>Predicción Equipo 1</th>
                        <th>Predicción Equipo 2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT apuestas.id as apuesta_id, usuarios.nombre, partidos.equipo_local, partidos.equipo_visitante, apuestas.prediccion_equipo1, apuestas.prediccion_equipo2 
                                            FROM apuestas 
                                            JOIN usuarios ON apuestas.usuario_id = usuarios.id 
                                            JOIN partidos ON apuestas.partido_id = partidos.id
                                            ORDER BY apuestas.id");
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="apuesta_id[]" value="<?= $row['apuesta_id'] ?>"></td>
                            <td><?= $row['apuesta_id'] ?></td>
                            <td><?= $row['nombre'] ?></td>
                            <td><?= $row['equipo_local'] ?> vs <?= $row['equipo_visitante'] ?></td>
                            <td ><?= $row['prediccion_equipo1'] ?></td>
                            <td><?= $row['prediccion_equipo2'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="generar_pdf_multiple">Generar PDF para Seleccionados</button>
        </form>

        <div class="footer">
            <p>&copy; 2024 Apuestas de Fútbol</p>
        </div>
    </div>
</body>
</html>
