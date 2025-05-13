<?php

session_start(); // Iniciar la sesión

// Verificar si el usuario tiene tipo 1
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != 1) {
    header('Location: dashboard.php'); // Redirige a dashboard.php si no tiene tipo 1
    exit; // Detiene la ejecución del script
}

include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    die('Usuario no autenticado.');
}

$titulo_pagina = "Gestión de Usuarios"; // Título de la página

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
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/components/buttons.css">
    <link rel="stylesheet" href="./css/pages/usuarios.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .toggle-password {
            cursor: pointer;
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
    <?php include 'includes/header.php'; ?>
    <div class="container">

        <!-- Formulario de creación de usuario -->
        <form class="user-form" method="POST" action="">
            <input type="hidden" name="accion" value="crear">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required><br>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required><br>
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" required>
                <button class="btn sm btn-primary" type="button"
                    onclick="togglePassword('contraseña', this)">Mostrar</button><br>
            </div>

            <div class="form-group">
                <label for="ext">Extensión:</label>
                <input type="text" id="ext" name="ext" required><br>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select class="btn btn-light sm" id="tipo" name="tipo" required>
                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id']; ?>">
                            <?php echo htmlspecialchars($grupo['grupo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
            </div>
            <button class="btn btn-primary" type="submit">Crear Usuario</button>
        </form>

        <!-- Listado de usuarios -->
        <h2>Listado de Usuarios</h2>
        <div class="card">
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-striped">
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
                                    <input type="password" id="contraseña_<?php echo $row['id']; ?>" name="contraseña"
                                        value="<?php echo htmlspecialchars($row['contraseña']); ?>" readonly>
                                    <button class="btn sm btn-primary" type="button"
                                        onclick="togglePassword('contraseña_<?php echo $row['id']; ?>', this)">Mostrar</button>
                                </td>
                                <td>
                                    <input type="text" id="ext_<?php echo $row['id']; ?>" name="ext"
                                        value="<?php echo htmlspecialchars($row['ext']); ?>" readonly>
                                </td>
                                <td>
                                    <select class="btn btn-light sm" id="tipo_<?php echo $row['id']; ?>" name="tipo" disabled>
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
                                        <button class="btn sm btn-outline-primary" type="button"
                                            onclick="enableEditing('<?php echo $row['id']; ?>')">Editar</button>
                                        <button class="btn sm btn-primary" type="button"
                                            onclick="prepareAndSubmit('<?php echo $row['id']; ?>', this)">Guardar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay usuarios disponibles para mostrar.</p>
            <?php endif; ?>
        </div>
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