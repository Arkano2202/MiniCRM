<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

// Consultar los tipos de usuario desde la tabla t_users
$query_tipos = "SELECT codigo, grupo, tipo FROM t_user";
$result_tipos = mysqli_query($conn, $query_tipos);
$tipos = [];
if ($result_tipos) {
    while ($row = mysqli_fetch_assoc($result_tipos)) {
        $tipos[] = $row;
    }
} else {
    die('Error al cargar tipos de usuario: ' . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/pages/crear_usuario.css">
    <link rel="stylesheet" href="css/components/buttons.css">
    <title>Crear Usuario</title>
</head>

<body>
    <header class="header">
        <nav class="dashboard-header container">
            <h1 class="title">Gestión de Usuarios</h1>
            <ul class="navbar-nav">
                <li class="nav-item"><a href="dashboard.php">Inicio</a></li>
                <li class="nav-item"><a href="usuarios.php">Usuarios</a></li>
                <li class="nav-item"><a href="calendario.php">Calendario</a></li>
            </ul>
            <button class="btn btn-danger" onclick="confirmLogout()">Salir</a></button>
        </nav>
    </header>
    <div class="container">
        <h1>Crear Usuario</h1>
        <form class="form" action="guardar_usuario.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required><br><br>
            </div>

            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required><br><br>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required><br><br>
            </div>

            <div class="form-group">
                <label for="ext">Ext:</label>
                <input type="text" id="ext" name="ext" required><br><br>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select class="btn btn-light sm" id="tipo" name="tipo" required>
                    <option value="">Seleccione un tipo</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>">
                            <?php echo htmlspecialchars($tipo['grupo'] . ' - ' . $tipo['tipo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>
            </div>

            <button class="btn btn-primary" type="submit">Guardar Usuario</button>
        </form>
    </div>
    <script>
        // Función para confirmar el cierre de sesión
        function confirmLogout() {
            if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                window.location.href = "logout.php"; // Redirigir a la página de cierre de sesión
            }
        }
    </script>
</body>

</html>