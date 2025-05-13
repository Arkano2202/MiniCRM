<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components/buttons.css">
    <link rel="stylesheet" href="css/pages/formulario_cliente.css">
    <title>Asignar Grupos</title>
</head>

<body>
    <?php
    include 'conexion.php';
    session_start();

    // Verificar si el usuario tiene tipo 1
    if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != 1) {
        header('Location: dashboard.php'); // Redirige a dashboard.php si no tiene tipo 1
        exit; // Detiene la ejecución del script
    }

    $sql_checkboxes = "SELECT id, nombre FROM users WHERE tipo NOT IN (1, 4, 5)";
    $result_checkboxes = $conn->query($sql_checkboxes);

    $sql_select = "SELECT id, nombre FROM users WHERE tipo IN (4, 5)";
    $result_select = $conn->query($sql_select);

    $sql_usuarios_grupo = "
        SELECT u.id, u.nombre AS usuario, u.grupo, g.nombre AS nombre_grupo
        FROM users u
        LEFT JOIN users g ON u.grupo = g.id
        WHERE u.tipo NOT IN (1, 4, 5)
    ";
    $result_usuarios_grupo = $conn->query($sql_usuarios_grupo);
    ?>

    <?php include 'includes/header.php'; ?>

    <div class="container center">

        <div id="mensaje" class="alert d-none"></div>

        <form id="formAsignar" class="formulario">
            <h2>Usuarios</h2>
            <table class="form-table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 8px;">Nombre</th>
                        <th style="text-align: left; padding: 8px;">Seleccionar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_checkboxes->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 8px;"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td style="padding: 8px;">
                                <input class="form-check-input" type="checkbox" name="usuarios[]" value="<?= $row['id'] ?>" id="usuario<?= $row['id'] ?>">
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <label for="asignado_a">Asignar a TL</label>
            <select id="asignado_a" name="asignado_a">
                <option value="">-- Seleccionar usuario --</option>
                <?php while ($row = $result_select->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
                <?php endwhile; ?>
            </select>

            <div style="margin-top: 1em;">
                <button type="submit" class="btn btn-primary sm">Asignar</button>
                <button type="button" id="btnDesasignar" class="btn btn-danger sm">Desasignar</button>
            </div>
        </form>

        <div class="table-container">
            <h2>Usuarios y sus Grupos Asignados</h2>
            <table class="notes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Grupo Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_usuarios_grupo->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['usuario']) ?></td>
                            <td><?= $row['nombre_grupo'] ? htmlspecialchars($row['nombre_grupo']) : '<em>Sin asignar</em>' ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('formAsignar').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const mensajeDiv = document.getElementById('mensaje');

            const desmarcados = [];
            document.querySelectorAll('input[name="usuarios[]"]:not(:checked)').forEach(chk => {
                desmarcados.push(chk.value);
            });
            formData.append('desmarcados', JSON.stringify(desmarcados));

            fetch('procesar_asignacion.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    mensajeDiv.classList.remove('d-none', 'alert-danger', 'alert-success');
                    if (data.success) {
                        mensajeDiv.classList.add('alert-success');
                        mensajeDiv.innerText = 'Asignación realizada con éxito.';
                        form.reset();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mensajeDiv.classList.add('alert-danger');
                        mensajeDiv.innerText = data.error || 'Ocurrió un error.';
                    }
                })
                .catch(() => {
                    mensajeDiv.classList.remove('d-none');
                    mensajeDiv.classList.add('alert-danger');
                    mensajeDiv.innerText = 'Error en la comunicación con el servidor.';
                });
        });

        document.getElementById('btnDesasignar').addEventListener('click', function () {
            const seleccionados = Array.from(document.querySelectorAll('input[name="usuarios[]"]:checked')).map(chk => chk.value);

            if (seleccionados.length === 0) {
                alert('Selecciona al menos un usuario para desasignar.');
                return;
            }

            if (!confirm('¿Estás seguro de que deseas desasignar los usuarios seleccionados?')) return;

            const formData = new FormData();
            formData.append('accion', 'desasignar');
            formData.append('usuarios', JSON.stringify(seleccionados));

            fetch('procesar_asignacion.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    const mensajeDiv = document.getElementById('mensaje');
                    mensajeDiv.classList.remove('d-none', 'alert-danger', 'alert-success');

                    if (data.success) {
                        mensajeDiv.classList.add('alert-success');
                        mensajeDiv.innerText = 'Usuarios desasignados correctamente.';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mensajeDiv.classList.add('alert-danger');
                        mensajeDiv.innerText = data.error || 'Error al desasignar.';
                    }
                })
                .catch(() => {
                    const mensajeDiv = document.getElementById('mensaje');
                    mensajeDiv.classList.remove('d-none', 'alert-success');
                    mensajeDiv.classList.add('alert-danger');
                    mensajeDiv.innerText = 'Error en la comunicación con el servidor.';
                });
        });

        const grupoUsuarios = <?php
        $sql_grupos_js = "SELECT id, grupo FROM users WHERE tipo NOT IN (1, 4, 5)";
        $result_grupos_js = $conn->query($sql_grupos_js);
        $grupo_usuarios = [];
        while ($row = $result_grupos_js->fetch_assoc()) {
            $grupo_usuarios[$row['id']] = $row['grupo'];
        }
        echo json_encode($grupo_usuarios);
        ?>;

        document.getElementById('asignado_a').addEventListener('change', function () {
            const seleccionado = this.value;
            document.querySelectorAll('input[name="usuarios[]"]').forEach(chk => chk.checked = false);

            Object.entries(grupoUsuarios).forEach(([usuarioId, grupoId]) => {
                if (grupoId == seleccionado) {
                    const checkbox = document.querySelector(`input[name="usuarios[]"][value="${usuarioId}"]`);
                    if (checkbox) checkbox.checked = true;
                }
            });
        });
    </script>
</body>

</html>
