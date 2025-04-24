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

// Consulta con JOIN para obtener también el nombre del usuario
$sql = "SELECT c.id, c.titulo, c.descripcion, c.fecha, c.hora, c.notificado, u.nombre AS nombre_usuario
        FROM citas c
        JOIN users u ON c.usuario_id = u.id
        WHERE c.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$eventos = [];

while ($row = $result->fetch_assoc()) {
    $start_datetime = $row['fecha'] . ' ' . $row['hora'];
    $start = new DateTime($start_datetime);
    $end = clone $start;
    $end->modify('+30 minutes');

    $eventos[] = [
        'id' => $row['id'],
        'title' => $row['titulo'],
        'start' => $start->format('Y-m-d\TH:i:s'),
        'end' => $end->format('Y-m-d\TH:i:s'),
        'description' => $row['descripcion'],
        'notificado' => $row['notificado'],
        'usuario_nombre' => $row['nombre_usuario'],
        'allDay' => false
    ];
}

echo json_encode($eventos);
$stmt->close();
$conn->close();
