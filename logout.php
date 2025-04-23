<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = [];

// Si es necesario eliminar la cookie de sesión, establecerla con un tiempo pasado
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header("Location: login.php");
exit();
?>
