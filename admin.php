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
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <form method="post" id="pdf_multiple_form">
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
                            <td><?= $row['prediccion_equipo1'] ?></td>
                            <td><?= $row['prediccion_equipo2'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="generar_pdf_multiple">Generar PDF para Seleccionados</button>
        </form>

        <h2>Generar Reporte por Día</h2>
        <form method="post" id="pdf_por_dia_form">
            <label for="fecha_reporte">Fecha:</label>
            <input type="date" id="fecha_reporte" name="fecha_reporte" required>
            <button type="submit" name="generar_pdf_por_dia">Generar Reporte por Día</button>
        </form>

        <div class="footer">
            <p>&copy; 2024 Apuestas de Fútbol</p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#pdf_multiple_form').on('submit', function(e) {
            e.preventDefault();
            var apuesta_ids = [];
            $('input[name="apuesta_id[]"]:checked').each(function() {
                apuesta_ids.push($(this).val());
            });
            if (apuesta_ids.length > 0) {
                var url = 'generate_pdf.php?apuesta_ids=' + apuesta_ids.join(',');
                window.open(url, '_blank'); // Abrir en una nueva pestaña
            } else {
                alert('Por favor seleccione al menos una apuesta.');
            }
        });

        $('#pdf_por_dia_form').on('submit', function(e) {
            e.preventDefault();
            var fecha_reporte = $('#fecha_reporte').val();
            if (fecha_reporte) {
                var url = 'generate_pdf_by_date.php?fecha=' + fecha_reporte;
                window.open(url, '_blank'); // Abrir en una nueva pestaña
            } else {
                alert('Por favor seleccione una fecha.');
            }
        });
    });
    </script>
</body>
</html>
