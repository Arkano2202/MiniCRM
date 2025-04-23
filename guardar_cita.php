<?php
header('Content-Type: application/json');

// Leer el cuerpo crudo del POST (JSON)
$input = json_decode(file_get_contents("php://input"), true);

$titulo = $input['titulo'] ?? null;
$descripcion = $input['descripcion'] ?? null;
$fechaHora = $input['fecha_hora'] ?? null;
$usuario_id = $input['usuario_id'] ?? null;

// Validar que todos los campos estén presentes
if (!$titulo || !$descripcion || !$fechaHora || !$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

// Establecer zona horaria a Colombia
date_default_timezone_set('America/Bogota');
$fechaCreacion = date('Y-m-d H:i:s');

// Conexión a la base de datos
include 'conexion.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Separar fecha y hora
$fecha = date('Y-m-d', strtotime($fechaHora));
$hora = date('H:i:s', strtotime($fechaHora));

// Insertar en la base de datos
$sql = "INSERT INTO citas (titulo, descripcion, fecha, hora, usuario_id, creado_en)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssis", $titulo, $descripcion, $fecha, $hora, $usuario_id, $fechaCreacion);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al insertar']);
}

$stmt->close();
$conn->close();
