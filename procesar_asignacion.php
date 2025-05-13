<?php
include 'conexion.php';
session_start();

// Forzar salida en formato JSON
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DESASIGNAR USUARIOS
    if (isset($_POST['accion']) && $_POST['accion'] === 'desasignar') {
        if (!isset($_POST['usuarios'])) {
            echo json_encode(['success' => false, 'error' => 'Faltan usuarios para desasignar.']);
            exit;
        }

        $usuarios = json_decode($_POST['usuarios'], true);

        if (!is_array($usuarios) || empty($usuarios)) {
            echo json_encode(['success' => false, 'error' => 'Lista de usuarios inválida.']);
            exit;
        }

        // Preparar consulta para desasignar (grupo = 0)
        $placeholders = implode(',', array_fill(0, count($usuarios), '?'));
        $types = str_repeat('i', count($usuarios));
        $stmt = $conn->prepare("UPDATE users SET grupo = 0 WHERE id IN ($placeholders)");

        if ($stmt === false) {
            echo json_encode(['success' => false, 'error' => 'Error preparando la consulta: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param($types, ...$usuarios);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al ejecutar la actualización.']);
        }

        $stmt->close();
        $conn->close();
        exit;
    }

    // ASIGNAR USUARIOS A UN GRUPO
    if (!isset($_POST['usuarios']) || !isset($_POST['asignado_a'])) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
        exit;
    }

    $usuarios = $_POST['usuarios'];
    $asignado_a = intval($_POST['asignado_a']);

    if (empty($usuarios) || !is_array($usuarios)) {
        echo json_encode(['success' => false, 'error' => 'No se seleccionaron usuarios válidos.']);
        exit;
    }

    // Preparar consulta para asignar grupo
    $stmt_asignar = $conn->prepare("UPDATE users SET grupo = ? WHERE id = ?");

    if (!$stmt_asignar) {
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $conn->error]);
        exit;
    }

    foreach ($usuarios as $usuario_id) {
        $usuario_id = intval($usuario_id);
        $stmt_asignar->bind_param("ii", $asignado_a, $usuario_id);
        $stmt_asignar->execute();
    }

    $stmt_asignar->close();
    $conn->close();

    echo json_encode(['success' => true]);
    exit;
}
?>
