<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$hoy = date('Y-m-d');
$result = $conn->query("SELECT * FROM partidos WHERE fecha = '$hoy'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Apuesta</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="background-image">
    <div class="header">
        <h1>Realizar Apuesta</h1>
    </div>
    <div class="container">
        <form method="post" action="generate_pdf.php" id="apuestas_form">
            <div id="apuestas_container">
                <div class="apuesta">
                    <div class="apuesta-top">
                        <label for="partido">Partido:</label>
                        <select class="partido" name="partido_id[]" required>
                            <option value="">Selecciona un partido</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" data-equipo-local="<?= $row['equipo_local'] ?>" data-equipo-visitante="<?= $row['equipo_visitante'] ?>">
                                    <?= $row['equipo_local'] ?> vs <?= $row['equipo_visitante'] ?> - <?= $row['fecha'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="apuesta-bottom">
                        <label for="prediccion_equipo1" class="label_prediccion_equipo1">Predicción Equipo 1:</label>
                        <input type="number" class="prediccion_equipo1" name="prediccion_equipo1[]" required>

                        <label for="prediccion_equipo2" class="label_prediccion_equipo2">Predicción Equipo 2:</label>
                        <input type="number" class="prediccion_equipo2" name="prediccion_equipo2[]" required>
                    </div>

                    <button type="button" class="remove_apuesta">Quitar apuesta</button>
                </div>
            </div>
            <button type="button" id="add_apuesta">Añadir otra apuesta</button>
            <button type="submit">Confirmar</button>
        </form>

        <?php if ($result->num_rows == 0): ?>
            <p>No hay partidos para hoy.</p>
        <?php endif; ?>

        <a href="logout.php">Cerrar Sesión</a>

        <div class="footer">
            <p>&copy; 2024 Apuestas de Fútbol</p>
        </div>
    </div>
    <div class="image-container">
            <!-- <img src="src/img.jpg" alt="Descripción de la imagen 1"> -->
            <img src="src/img1.jpg" alt="Descripción de la imagen 2">
    </div>
    <script>
    $(document).ready(function() {
        function updateLabels(apuestaDiv, selectedOption) {
            var equipoLocal = selectedOption.data('equipo-local');
            var equipoVisitante = selectedOption.data('equipo-visitante');

            apuestaDiv.find('.label_prediccion_equipo1').text('Predicción ' + equipoLocal + ':');
            apuestaDiv.find('.label_prediccion_equipo2').text('Predicción ' + equipoVisitante + ':');
        }

        $('#add_apuesta').click(function() {
            var apuestaHTML = `
            <div class="apuesta">
                <div class="apuesta-top">
                    <label for="partido">Partido:</label>
                    <select class="partido" name="partido_id[]" required>
                        <option value="">Selecciona un partido</option>
                        <?php
                        $result = $conn->query("SELECT * FROM partidos WHERE fecha = '$hoy'");
                        while ($row = $result->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" data-equipo-local="<?= $row['equipo_local'] ?>" data-equipo-visitante="<?= $row['equipo_visitante'] ?>">
                                <?= $row['equipo_local'] ?> vs <?= $row['equipo_visitante'] ?> - <?= $row['fecha'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="apuesta-bottom">
                    <label for="prediccion_equipo1" class="label_prediccion_equipo1">Predicción Equipo 1:</label>
                    <input type="number" class="prediccion_equipo1" name="prediccion_equipo1[]" required>

                    <label for="prediccion_equipo2" class="label_prediccion_equipo2">Predicción Equipo 2:</label>
                    <input type="number" class="prediccion_equipo2" name="prediccion_equipo2[]" required>
                </div>

                <button type="button" class="remove_apuesta">Quitar apuesta</button>
            </div>
            `;
            $('#apuestas_container').append(apuestaHTML);
        });

        $(document).on('click', '.remove_apuesta', function() {
            $(this).closest('.apuesta').remove();
        });

        $(document).on('change', '.partido', function() {
            var selectedOption = $(this).find('option:selected');
            var apuestaDiv = $(this).closest('.apuesta');
            updateLabels(apuestaDiv, selectedOption);
        });

        // Actualizar etiquetas al cargar la página para la primera apuesta
        var initialSelectedOption = $('.partido').find('option:selected');
        updateLabels($('.apuesta'), initialSelectedOption);
    });
    </script>
</body>
</html>
