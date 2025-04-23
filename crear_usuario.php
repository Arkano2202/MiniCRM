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
    <title>Crear Usuario</title>
</head>
<body>
    <h1>Crear Usuario</h1>
    <form action="guardar_usuario.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" required><br><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br><br>

        <label for="ext">Ext:</label>
        <input type="text" id="ext" name="ext" required><br><br>

        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione un tipo</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo['codigo']); ?>">
                    <?php echo htmlspecialchars($tipo['grupo'] . ' - ' . $tipo['tipo']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Guardar Usuario</button>
    </form>
</body>
</html>
