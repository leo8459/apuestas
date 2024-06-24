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

    // Retornar los IDs de las apuestas generadas para la redirección
    $apuesta_ids = array_column($apuestas, 'apuesta_id');
    echo implode(',', $apuesta_ids);
    exit;
}

if (isset($_GET['apuesta_ids'])) {
    $apuesta_ids = explode(',', $_GET['apuesta_ids']);

    $apuestas = [];
    foreach ($apuesta_ids as $apuesta_id) {
        $apuesta_stmt = $conn->prepare("SELECT apuestas.id as apuesta_id, usuarios.nombre, partidos.equipo_local, partidos.equipo_visitante, apuestas.prediccion_equipo1, apuestas.prediccion_equipo2, apuestas.fecha 
                                        FROM apuestas 
                                        JOIN usuarios ON apuestas.usuario_id = usuarios.id 
                                        JOIN partidos ON apuestas.partido_id = partidos.id 
                                        WHERE apuestas.id = ?");
        $apuesta_stmt->bind_param("i", $apuesta_id);
        $apuesta_stmt->execute();
        $apuesta_stmt->bind_result($apuesta_id, $usuario_nombre, $equipo_local, $equipo_visitante, $prediccion_equipo1, $prediccion_equipo2, $fecha_creacion);
        $apuesta_stmt->fetch();
        $apuesta_stmt->close();

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

    // Crear una instancia del objeto TCPDF
    $pdf = new TCPDF();
    
    // Configuración del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Tu Nombre');
    $pdf->SetTitle('Reporte de Apuestas');
    $pdf->SetSubject('Reporte');
    $pdf->SetKeywords('TCPDF, PDF, ejemplo, prueba, reporte');
    
    // Agregar una página
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Apuestas', 0, 1, 'C');
    $pdf->Ln(5);

    // Monto de la apuesta
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Monto de la apuesta: Bs5', 0, 1, 'C');
    $pdf->Ln(10);
    
   // Columnas de la tabla
   $pdf->SetFont('helvetica', 'B', 10);
   $pdf->SetFillColor(200, 220, 255);
   $pdf->Cell(10, 10, 'ID', 1, 0, 'C', 1);
   $pdf->Cell(60, 10, 'Usuario', 1, 0, 'C', 1);
   $pdf->Cell(60, 10, 'Partido', 1, 0, 'C', 1);
   $pdf->Cell(20, 10, 'Resultado', 1, 0, 'C', 1);
   $pdf->Cell(40, 10, 'Fecha', 1, 1, 'C', 1); // Nueva columna para la fecha
   
   // Datos de la tabla
   $pdf->SetFont('helvetica', '', 10);
   $pdf->SetFillColor(240, 240, 240);
   $fill = 0;
   foreach ($apuestas as $row) {
       $pdf->Cell(10, 10, $row['apuesta_id'], 1, 0, 'C', $fill);
       $pdf->Cell(60, 10, $row['usuario_nombre'], 1, 0, 'C', $fill);
       $pdf->Cell(60, 10, $row['equipo_local'] . ' vs ' . $row['equipo_visitante'], 1, 0, 'C', $fill);
       $pdf->Cell(10, 10, $row['prediccion_equipo1'], 1, 0, 'C', $fill);
       $pdf->Cell(10, 10, $row['prediccion_equipo2'], 1, 0, 'C', $fill);
       $pdf->Cell(40, 10, $row['fecha_creacion'], 1, 1, 'C', $fill); // Agregar la fecha aquí
       $fill = !$fill;
   }

   // Encabezado para forzar la descarga en una nueva pestaña
   header('Content-Type: application/pdf');
   header('Content-Disposition: inline; filename="Reporte_Apuestas.pdf"');
   header('Cache-Control: private, max-age=0, must-revalidate');
   header('Pragma: public');
    
   // Salida del PDF
   $pdf->Output('Reporte_Apuestas.pdf', 'I'); // 'I' para enviar el archivo al navegador
   exit;
}
?>
