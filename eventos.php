<?php
header('Content-Type: application/json');
session_start();

// Verifica que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]); // Devuelve un array vacío si no hay sesión
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Conexión a la base de datos
include 'conexion.php';

if ($conn->connect_error) {
    echo json_encode([]); // Si hay error de conexión, devuelve vacío
    exit;
}

// Consulta todas las citas del usuario logueado
$sql = "SELECT id, titulo, descripcion, fecha, hora, notificado FROM citas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$eventos = [];

while ($row = $result->fetch_assoc()) {
    $eventos[] = [
        'id' => $row['id'],
        'title' => $row['titulo'],
        'start' => $row['fecha'] . 'T' . $row['hora'] . '-05:00',
        'description' => $row['descripcion'],
        'notificado' => $row['notificado'] 
    ];
}

echo json_encode($eventos);
$stmt->close();
$conn->close();
