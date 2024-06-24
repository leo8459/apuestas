<?php
session_start();
require 'vendor/autoload.php';
include 'db.php';

if (!isset($_SESSION['usuario_id']) && (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $partido_ids = $_POST['partido_id'];
    $prediccion_equipo1s = $_POST['prediccion_equipo1'];
    $prediccion_equipo2s = $_POST['prediccion_equipo2'];
    $monto = 5; // Monto fijo solo para la primera apuesta

    $apuestas = [];
    $total_monto = $monto; // Solo considerar el monto de la primera apuesta

    for ($i = 0; $i < count($partido_ids); $i++) {
        $partido_id = $partido_ids[$i];
        $prediccion_equipo1 = $prediccion_equipo1s[$i];
        $prediccion_equipo2 = $prediccion_equipo2s[$i];

        $stmt = $conn->prepare("INSERT INTO apuestas (usuario_id, partido_id, prediccion_equipo1, prediccion_equipo2, monto) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $_SESSION['usuario_id'], $partido_id, $prediccion_equipo1, $prediccion_equipo2, $monto);
        $stmt->execute();

        $apuesta_id = $stmt->insert_id;
        $stmt->close();

        $partido_stmt = $conn->prepare("SELECT equipo_local, equipo_visitante FROM partidos WHERE id = ?");
        $partido_stmt->bind_param("i", $partido_id);
        $partido_stmt->execute();
        $partido_stmt->bind_result($equipo_local, $equipo_visitante);
        $partido_stmt->fetch();
        $partido_stmt->close();

        // Obtener la fecha de creación de la apuesta
        $fecha_stmt = $conn->prepare("SELECT fecha FROM apuestas WHERE id = ?");
        $fecha_stmt->bind_param("i", $apuesta_id);
        $fecha_stmt->execute();
        $fecha_stmt->bind_result($fecha_creacion);
        $fecha_stmt->fetch();
        $fecha_stmt->close();

        $apuestas[] = [
            'apuesta_id' => $apuesta_id,
            'equipo_local' => $equipo_local,
            'equipo_visitante' => $equipo_visitante,
            'prediccion_equipo1' => $prediccion_equipo1,
            'prediccion_equipo2' => $prediccion_equipo2,
            'fecha_creacion' => $fecha_creacion
        ];

        // Reset monto to 0 after the first iteration
        $monto = 0;
    }

    $usuario_stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $usuario_stmt->bind_param("i", $_SESSION['usuario_id']);
    $usuario_stmt->execute();
    $usuario_stmt->bind_result($usuario_nombre);
    $usuario_stmt->fetch();
    $usuario_stmt->close();

    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $html = "<h1>Ficha de Apuesta</h1>";
    $html .= "<p><strong>Usuario:</strong> $usuario_nombre</p>";
    $html .= "<p><strong>Monto Total:</strong> Bs$total_monto</p>";

    $html .= "<table border=\"1\" cellpadding=\"4\">
                <tr>";

    foreach ($apuestas as $apuesta) {
        $html .= "
                <td>
                    <p><strong>ID de Apuesta:</strong> {$apuesta['apuesta_id']}</p>
                    <p><strong>Partido:</strong> {$apuesta['equipo_local']} vs {$apuesta['equipo_visitante']}</p>
                    <p><strong>Predicción:</strong> {$apuesta['equipo_local']} {$apuesta['prediccion_equipo1']} - {$apuesta['prediccion_equipo2']} {$apuesta['equipo_visitante']}</p>
                    <p><strong>Fecha:</strong> {$apuesta['fecha_creacion']}</p>
                </td>
        ";
    }

    $html .= "</tr></table>";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('ficha_apuesta.pdf', 'D');
    exit;
}

if (isset($_GET['apuesta_ids'])) {
    $apuesta_ids = explode(',', $_GET['apuesta_ids']);

    $apuestas = [];
    $total_monto = 0;
    foreach ($apuesta_ids as $index => $apuesta_id) {
        $apuesta_stmt = $conn->prepare("SELECT apuestas.id as apuesta_id, usuarios.nombre, partidos.equipo_local, partidos.equipo_visitante, apuestas.prediccion_equipo1, apuestas.prediccion_equipo2, apuestas.monto, apuestas.fecha 
                                        FROM apuestas 
                                        JOIN usuarios ON apuestas.usuario_id = usuarios.id 
                                        JOIN partidos ON apuestas.partido_id = partidos.id 
                                        WHERE apuestas.id = ?");
        $apuesta_stmt->bind_param("i", $apuesta_id);
        $apuesta_stmt->execute();
        $apuesta_stmt->bind_result($apuesta_id, $usuario_nombre, $equipo_local, $equipo_visitante, $prediccion_equipo1, $prediccion_equipo2, $monto, $fecha_creacion);
        $apuesta_stmt->fetch();
        $apuesta_stmt->close();

        // Solo considerar el monto de la primera apuesta
        if ($index == 0) {
            $total_monto += $monto;
        }

        $apuestas[] = [
            'apuesta_id' => $apuesta_id,
            'usuario_nombre' => $usuario_nombre,
            'equipo_local' => $equipo_local,
            'equipo_visitante' => $equipo_visitante,
            'prediccion_equipo1' => $prediccion_equipo1,
            'prediccion_equipo2' => $prediccion_equipo2,
            'fecha_creacion' => $fecha_creacion
        ];
    }

    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $html = "<h1>Ficha de Apuestas</h1>";
    $html .= "<p><strong>Monto Total:</strong> Bs$total_monto</p>";

    $html .= "<table border=\"1\" cellpadding=\"4\">
                <tr>";

    foreach ($apuestas as $apuesta) {
        $html .= "
                <td>
                    <p><strong>ID de Apuesta:</strong> {$apuesta['apuesta_id']}</p>
                    <p><strong>Usuario:</strong> {$apuesta['usuario_nombre']}</p>
                    <p><strong>Partido:</strong> {$apuesta['equipo_local']} vs {$apuesta['equipo_visitante']}</p>
                    <p><strong>Predicción:</strong> {$apuesta['equipo_local']} {$apuesta['prediccion_equipo1']} - {$apuesta['prediccion_equipo2']} {$apuesta['equipo_visitante']}</p>
                    <p><strong>Fecha:</strong> {$apuesta['fecha_creacion']}</p>
                </td>
        ";
    }

    $html .= "</tr></table>";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('fichas_apuestas.pdf', 'D');
    exit;
}
?>
