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
    <style>
        .policies-container {
            color: black; /* Asegurar que el texto dentro de .policies-container sea negro */
        }
    </style>
</head>
<body class="background-image">
    <div class="header">
        <h1>Realizar Apuesta</h1>
    </div>
    <div class="container">
        <form method="post" id="apuestas_form">
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
    <div class="policies-container">
        <h2>POLÍTICAS DEL JUEGO</h2>
        <ol>
            <li>Todos los funcionarios que lo deseen pueden participar sin restricción alguna en la fase de grupos.</li>
            <li>Se realizará el cobro único por fecha, indiferente a la cantidad de partidos que se juegue.</li>
            <li>Para participar en los partidos de cuartos de final, el requisito principal es haber jugado el 90% en la fase de grupos.</li>
            <li>Para participar en los partidos de semifinal, el requisito principal es haber jugado el 100% en la fase de cuartos de final.</li>
            <li>Para participar en los partidos de la final, el requisito principal es haber jugado el 100% en la fase semifinal.</li>
        </ol>
        <p>Las apuestas son en bolivianos y el monto será según el siguiente orden:</p>
        <ul>
            <li>5 bs por fecha en las fases de grupo.</li>
            <li>10 bs por fecha para participar en 4tvos.</li>
            <li>15 bs por fecha para participar en semifinales.</li>
            <li>20 bs por fecha para participar en la final.</li>
        </ul>
        <p><strong>NOTA 1:</strong> Las apuestas se realizan por fecha, en caso que hubiera dos o más partidos en un día, el monto de la apuesta será la misma (Ejemplo: solo 5 bs para dos partidos).</p>
        <p><strong>NOTA 2:</strong> Se tiene que tomar en cuenta que para ganar tienen que acertar el “score” de uno o todos los partidos en determinada fecha.</p>
        <ol start="7">
            <li>El o los ganadores podrán recoger su premio al día siguiente en acto público.</li>
            <li>Las apuestas se recibirán hasta una hora antes del primer partido de cada fecha.</li>
            <li>Solo podrán participar una vez por fecha (cuenta ya sea 1 o más partidos).</li>
            <li>En caso de no existir ganadores a una determinada fecha, el monto recaudado por todas las apuestas se ira acumulando hasta el ganador de la siguiente fecha.</li>
            <li>En caso de que hubiera varios ganadores, el premio se dividirá de manera equitativa.</li>
            <li>Si al final del campeonato no hubiera ganadores, el monto acumulado será dispuesto para un compartimiento entre todos los participantes desde la 8va fecha (modo a definir).</li>
            <li>Procedimiento:</li>
            <ul>
                <li>Ingresar al link (QR)</li>
                <li>Realizar la apuesta.</li>
                <li>Deberá dirigirse a sistemas para validar su apuesta y recibir su hoja de apuesta.</li>
                <li>Deberá dirigirse hacia Alison para realizar el pago de la apuesta con su ticket.</li>
            </ul>
        </ol>
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

        $('#apuestas_form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'generate_pdf.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    alert('Apuesta generada');
                    var url = 'generate_pdf.php?apuesta_ids=' + response;
                    window.open(url, '_blank'); // Abrir en una nueva pestaña
                    $('#apuestas_form')[0].reset();
                    $('.apuesta').not(':first').remove();
                }
            });
        });
    });
    </script>
</body>
</html>
