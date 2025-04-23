<?php
session_start();

// Verificar si la sesión ya está activa
if (isset($_SESSION['usuario_usuario']) && !empty($_SESSION['usuario_usuario'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRMMini - Iniciar Sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="header">CRMMini</div>

    <div class="main-content">
        <div class="login-box">
            <form action="validar_login.php" method="POST">
                <h2>Iniciar Sesión</h2>
                <?php if (isset($_GET['error'])): ?>
                    <p class="error">Usuario o contraseña incorrectos.</p>
                <?php endif; ?>
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="contrasena" placeholder="Contraseña" required>
                <input type="submit" value="Iniciar Sesión">
            </form>
        </div>
    </div>
</body>
</html>
