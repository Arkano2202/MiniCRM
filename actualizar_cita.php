<?php
require 'conexion.php';
date_default_timezone_set('America/Bogota');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'], $data['titulo'], $data['descripcion'], $data['fecha_hora'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$id = $data['id'];
$titulo = $data['titulo'];
$descripcion = $data['descripcion'];
$fecha = date('Y-m-d', strtotime($data['fecha_hora']));
$hora = date('H:i:s', strtotime($data['fecha_hora']));

$sql = "UPDATE citas SET titulo = ?, descripcion = ?, fecha = ?, hora = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $titulo, $descripcion, $fecha, $hora, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
