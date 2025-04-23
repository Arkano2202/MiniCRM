<?php
session_start(); // Iniciar la sesión

// Configurar la zona horaria a hora colombiana
date_default_timezone_set('America/Bogota');

include 'conexion.php'; // Asegúrate de tener la conexión a la base de datos

// Obtener el tipo de usuario
$nombre_usuario = $_SESSION['usuario_nombre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tp_cliente = $_POST['tp_cliente'];
    $estado = $_POST['estado'];
    $observaciones = trim($_POST['notas']);
    $fecha_actual = date('Y-m-d H:i:s');

    // Validar campos obligatorios
    if (empty($estado) || strlen($observaciones) < 2) {
        echo "Error: Todos los campos son obligatorios.";
        exit;
    }

    // Insertar los datos en la tabla notas
    $query = $conn->prepare("INSERT INTO notas (TP, UltimaGestion, FechaUltimaGestion, Descripcion, user) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("sssss", $tp_cliente, $estado, $fecha_actual, $observaciones, $nombre_usuario);

    if ($query->execute()) {
        echo "Nota guardada exitosamente.";
        header("Location: formulario_cliente.php?tp=$tp_cliente");
        exit;
    } else {
        echo "Error al guardar la nota: " . $conn->error;
    }
}
?>
