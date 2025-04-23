<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';
require 'vendor/autoload.php'; // Asegúrate de tener PhpSpreadsheet instalado

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];

    if (empty($ids) || !array_reduce($ids, fn($carry, $id) => $carry && is_numeric($id), true)) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibieron IDs válidos para exportar.']);
        exit;
    }

    // Consultar datos de los IDs seleccionados
    $ids_list = implode(',', array_map('intval', $ids));
    $query = "SELECT c.id, c.Nombre, c.Apellido, c.Correo, c.Pais, 
                     COALESCE(n.UltimaGestion, '') AS UltimaGestion, 
                     COALESCE(n.FechaUltimaGestion, '') AS FechaUltimaGestion
              FROM clientes c
              LEFT JOIN (
                  SELECT TP, MAX(UltimaGestion) AS UltimaGestion, MAX(FechaUltimaGestion) AS FechaUltimaGestion
                  FROM notas
                  GROUP BY TP
              ) n ON c.TP = n.TP
              WHERE c.id IN ($ids_list)";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = mysqli_error($conn);
        http_response_code(500);
        echo json_encode(['error' => 'Error en la consulta a la base de datos.', 'details' => $error]);
        exit;
    }

    // Crear hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados
    $sheet->setCellValue('A1', 'ID')
          ->setCellValue('B1', 'Nombre')
          ->setCellValue('C1', 'Apellido')
          ->setCellValue('D1', 'Correo')
          ->setCellValue('E1', 'País')
          ->setCellValue('F1', 'Última Gestión')
          ->setCellValue('G1', 'Fecha Última Gestión');

    // Agregar datos
    $row = 2;
    while ($data = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue("A$row", $data['id'])
              ->setCellValue("B$row", $data['Nombre'])
              ->setCellValue("C$row", $data['Apellido'])
              ->setCellValue("D$row", $data['Correo'])
              ->setCellValue("E$row", $data['Pais'])
              ->setCellValue("F$row", $data['UltimaGestion'])
              ->setCellValue("G$row", $data['FechaUltimaGestion']);
        $row++;
    }

    // Forzar descarga directa del archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="exportacion_clientes_' . time() . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}
?>