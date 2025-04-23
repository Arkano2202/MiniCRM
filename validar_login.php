<?php
session_start();
include 'conexion.php';

// Evitamos problemas con espacios en blanco
ob_start();

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

// Preparar y ejecutar consulta segura
$sql = "SELECT * FROM users WHERE usuario = ? AND contraseña = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $contrasena);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $datos = $result->fetch_assoc();
    $_SESSION['usuario_id'] = $datos['id'];
    $_SESSION['usuario_nombre'] = $datos['Nombre'];
    $_SESSION['usuario_usuario'] = $datos['Usuario'];
    $_SESSION['usuario_ext'] = $datos['Ext'];
    $_SESSION['usuario_tipo'] = $datos['Tipo'];

    echo "Inicio de sesión exitoso";
    header("refresh:1;url=dashboard.php");
    exit(); // importante
} else {
    header("Location: login.php?error=1");
    exit(); // importante
}
?>
