<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar los datos recibidos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
    $ext = isset($_POST['ext']) ? trim($_POST['ext']) : '';
    $tipo = isset($_POST['tipo']) ? (int) $_POST['tipo'] : 0;

    if (empty($nombre) || empty($usuario) || empty($contrasena) || empty($ext) || empty($tipo)) {
        die('Todos los campos son obligatorios.');
    }

    // Encriptar la contraseña
    $contrasena_hashed = password_hash($contrasena, PASSWORD_BCRYPT);

    // Insertar en la tabla users
    $query = "INSERT INTO users (Nombre, Usuario, Contraseña, Ext, Tipo, Grupo) VALUES (?, ?, ?, ?, ?,'0')";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $usuario, $contrasena, $ext, $tipo);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: dashboard.php');
        exit;
    } else {
        echo 'Error al crear el usuario: ' . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo 'Método no permitido.';
}
?>
