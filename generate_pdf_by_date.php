<?php
session_start();
require 'vendor/autoload.php';
include 'db.php';

if (!isset($_GET['fecha'])) {
    die("Fecha no especificada.");
}

$fecha = $_GET['fecha'];

// Consulta las apuestas de la fecha especificada
$query = $conn->prepare("SELECT apuestas.id as apuesta_id, usuarios.nombre, partidos.equipo_local, partidos.equipo_visitante, apuestas.prediccion_equipo1, apuestas.prediccion_equipo2, apuestas.fecha 
                         FROM apuestas 
                         JOIN usuarios ON apuestas.usuario_id = usuarios.id 
                         JOIN partidos ON apuestas.partido_id = partidos.id
                         WHERE DATE(partidos.fecha) = ?");
$query->bind_param("s", $fecha);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
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
    $pdf->Cell(0, 10, 'Reporte de Apuestas para el ' . $fecha, 0, 1, 'C');
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
   while ($row = $result->fetch_assoc()) {
       $pdf->Cell(10, 10, $row['apuesta_id'], 1, 0, 'C', $fill);
       $pdf->Cell(60, 10, $row['nombre'], 1, 0, 'C', $fill);
       $pdf->Cell(60, 10, $row['equipo_local'] . ' vs ' . $row['equipo_visitante'], 1, 0, 'C', $fill);
       $pdf->Cell(10, 10, $row['prediccion_equipo1'], 1, 0, 'C', $fill);
       $pdf->Cell(10, 10, $row['prediccion_equipo2'], 1, 0, 'C', $fill);
       $pdf->Cell(40, 10, $row['fecha'], 1, 1, 'C', $fill); // Agregar la fecha aquí
       $fill = !$fill;
   }
    // Encabezado para forzar la descarga en una nueva pestaña
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Reporte_Apuestas_' . $fecha . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Salida del PDF
    $pdf->Output('Reporte_Apuestas_' . $fecha . '.pdf', 'I'); // 'I' para enviar el archivo al navegador
    exit;
} else {
    echo "No se encontraron apuestas para la fecha seleccionada.";
}
?>


