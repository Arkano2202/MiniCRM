<?php

session_start(); // Iniciar la sesión
include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    die('Usuario no autenticado.');
}

// Procesar el formulario de creación de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $ext = $_POST['ext'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if ($nombre && $usuario && $contraseña && $ext && $tipo) {
        // Verificar si el usuario o la extensión ya existen
        $query_check = "SELECT id FROM users WHERE usuario = ? OR ext = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param('ss', $usuario, $ext);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            echo "<p>Error: El usuario o la extensión ya están en uso. Por favor, elige valores diferentes.</p>";
        } else {
            // Insertar nuevo usuario
            $query_insert = "INSERT INTO users (nombre, usuario, contraseña, ext, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query_insert);
            $stmt->bind_param('ssssi', $nombre, $usuario, $contraseña, $ext, $tipo);

            if ($stmt->execute()) {
                echo "<p>Usuario creado correctamente.</p>";
            } else {
                echo "<p>Error al crear el usuario: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }

        $stmt_check->close();
    } else {
        echo "<p>Por favor, completa todos los campos.</p>";
    }
}

// Procesar el formulario de actualización de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'modificar') {
    $id = $_POST['id'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $ext = $_POST['ext'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if ($id && $contraseña && $ext && $tipo) {
        // Actualizar usuario
        $query_update = "UPDATE users SET contraseña = ?, ext = ?, tipo = ? WHERE id = ?";
        $stmt = $conn->prepare($query_update);
        $stmt->bind_param('ssii', $contraseña, $ext, $tipo, $id);

        if ($stmt->execute()) {
            echo "<p>Usuario actualizado correctamente.</p>";
        } else {
            echo "<p>Error al actualizar el usuario: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p>Por favor, completa todos los campos para modificar el usuario.</p>";
    }
}

// Obtener los usuarios de la tabla users, excluyendo a los de tipo 1
$query = "SELECT users.id, users.nombre, users.usuario, users.contraseña, users.ext, users.tipo AS tipo_id, t_user.grupo AS tipo_nombre 
          FROM users 
          INNER JOIN t_user ON users.tipo = t_user.id 
          WHERE users.tipo != 1";
$result = $conn->query($query);

if (!$result) {
    die('Error al obtener los usuarios: ' . $conn->error);
}

// Obtener los grupos para el campo de selección de tipo
$query_grupos = "SELECT id, grupo FROM t_user";
$result_grupos = $conn->query($query_grupos);

if (!$result_grupos) {
    die('Error al obtener los grupos: ' . $conn->error);
}

$grupos = [];
while ($row = $result_grupos->fetch_assoc()) {
    $grupos[] = $row;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .toggle-password {
            cursor: pointer;
        }
        .volver-button {
            display: block;
            margin: 50px auto;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-size: 16px;
        }
        .volver-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function togglePassword(inputId, toggleBtn) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggleBtn.textContent = isPassword ? 'Ocultar' : 'Mostrar';
        }

        function enableEditing(rowId) {
            document.getElementById('contraseña_' + rowId).removeAttribute('readonly');
            document.getElementById('ext_' + rowId).removeAttribute('readonly');
            document.getElementById('tipo_' + rowId).removeAttribute('disabled');
        }

        function prepareAndSubmit(rowId, btn) {
            const form = btn.closest('form');
            const contraseña = document.getElementById('contraseña_' + rowId).value;
            const ext = document.getElementById('ext_' + rowId).value;
            const tipo = document.getElementById('tipo_' + rowId).value;

            form.querySelector('input[name="contraseña"]').value = contraseña;
            form.querySelector('input[name="ext"]').value = ext;
            form.querySelector('input[name="tipo"]').value = tipo;

            form.submit();
        }
    </script>
</head>
<body>
    <h1>Gestión de Usuarios</h1>

    <!-- Formulario de creación de usuario -->
    <button class="volver-button" onclick="location.href='dashboard.php';">Volver</button>
    <form method="POST" action="">
        <input type="hidden" name="accion" value="crear">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" required><br>

        <label for="contraseña">Contraseña:</label>
        <input type="password" id="contraseña" name="contraseña" required>
        <button type="button" onclick="togglePassword('contraseña', this)">Mostrar</button><br>

        <label for="ext">Extensión:</label>
        <input type="text" id="ext" name="ext" required><br>

        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required>
            <?php foreach ($grupos as $grupo): ?>
                <option value="<?php echo $grupo['id']; ?>">
                    <?php echo htmlspecialchars($grupo['grupo']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Crear Usuario</button>
    </form>

    <!-- Listado de usuarios -->
    <h2>Listado de Usuarios</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Contraseña</th>
                    <th>Extensión</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td>
                            <input type="password" id="contraseña_<?php echo $row['id']; ?>" name="contraseña" value="<?php echo htmlspecialchars($row['contraseña']); ?>" readonly>
                            <button type="button" onclick="togglePassword('contraseña_<?php echo $row['id']; ?>', this)">Mostrar</button>
                        </td>
                        <td>
                            <input type="text" id="ext_<?php echo $row['id']; ?>" name="ext" value="<?php echo htmlspecialchars($row['ext']); ?>" readonly>
                        </td>
                        <td>
                            <select id="tipo_<?php echo $row['id']; ?>" name="tipo" disabled>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?php echo $grupo['id']; ?>" <?php echo $grupo['id'] == $row['tipo_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($grupo['grupo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="accion" value="modificar">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="contraseña" value="">
                                <input type="hidden" name="ext" value="">
                                <input type="hidden" name="tipo" value="">
                                <button type="button" onclick="enableEditing('<?php echo $row['id']; ?>')">Editar</button>
                                <button type="button" onclick="prepareAndSubmit('<?php echo $row['id']; ?>', this)">Guardar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay usuarios disponibles para mostrar.</p>
    <?php endif; ?>
</body>
</html>
