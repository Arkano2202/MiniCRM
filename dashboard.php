<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params(60 * 60 * 16);
session_start();
session_regenerate_id(true);
include 'conexion.php';

// Verifica si hay sesión activa
if (!isset($_SESSION['usuario_usuario']) || empty($_SESSION['usuario_usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario_usuario'];
$titulo_pagina = "Bienvenido, $usuario";
$tipo_usuario = $_SESSION['usuario_tipo']; // Asegúrate de guardar el tipo al momento de hacer login
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Consulta para obtener los nombres de los usuarios
$query_users = "SELECT id, nombre FROM users";
$result_users = $conn->query($query_users);
$usuarios = [];

if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/components/buttons.css">
    <link rel="stylesheet" href="./css/components/forms.css">
    <link rel="stylesheet" href="./css/components/tables.css">
    <link rel="stylesheet" href="./css/pages/dashboard.css">
    <link rel="stylesheet" href="./CSS/jquery.dataTable.min.css">
    <script src="./JS/jquery-3.6.4.min.js"></script>
    <script src="./JS/jquery.dataTables.min.js"></script>
    <script>
        function confirmLogout() {
            const confirmation = confirm("¿Estás seguro de que deseas cerrar sesión?");
            if (confirmation) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 1): ?>
            <!--button id="asignedtAll">Asignar Seleccion</button-->
            <div class="actions">
                <button id="deselectAll" class="btn btn-primary">Deseleccionar todo</button>
                <button id="exportExcel" class="btn btn-primary">Exportar seleccionados</button>
                <button id="deleteRecords" class="btn btn-danger">Eliminar seleccionados</button>
                <form class="upload" action="procesar_carga.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="archivo" accept=".csv" required>
                    <button class="btn btn-primary" type="submit" name="submit">Cargar</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 1): ?>
            <!-- Aquí puedes colocar el formulario de asignación -->
            <form class="assignment-form" id="asignarForm">
                <label for="usuario">Selecciona un usuario para asignar:</label>
                <select class="btn btn-light" name="usuario_id" id="usuario" required>
                    <option class="opt" value="" disabled selected>Selecciona un usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id']; ?>">
                            <?php echo htmlspecialchars($usuario['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- button type="button" id="asignarButton">Asignar</button -->
            </form>
        <?php endif; ?>

        <div class="dashboard-table mb-4">
            <table id="tablaClientes" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th> <!-- Checkbox maestro -->
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Fecha de Creación</th>
                        <th>Estado</th>
                        <th>Última Gestión</th>
                        <th>Fecha de Asignación</th>
                        <th>Fecha Última Gestión</th>
                        <th>TP</th>
                        <th>País</th>
                        <th>Correo</th>
                        <th>Asignado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // Arreglo global para almacenar IDs seleccionados
            let selectedRows = new Set();

            // Inicializar DataTable
            const tabla = $('#tablaClientes').DataTable({
                ajax: 'datos.php',
                serverSide: true,
                processing: true,
                columns: [
                    { data: 0, orderable: false }, // Checkbox
                    {
                        "data": 1,
                        "render": function (data, type, row) {
                            return `<a href='formulario_cliente.php?tp=${encodeURIComponent(row[8])}' target="_blank">${data}</a>`;
                        }
                    },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5 },
                    { data: 6 },
                    { data: 7 },
                    { data: 8 },
                    { data: 9 },
                    { data: 10 },
                    { data: 11 },
                ],
                drawCallback: function () {
                    // Restaurar el estado de los checkboxes
                    $('#tablaClientes tbody .fila-checkbox').each(function () {
                        const id = $(this).val();
                        $(this).prop('checked', selectedRows.has(id));
                    });
                }
            });

            // Seleccionar/Deseleccionar todos los checkboxes visibles
            $('#selectAll').on('click', function () {
                const isChecked = $(this).is(':checked');
                $('#tablaClientes tbody .fila-checkbox').each(function () {
                    const id = $(this).val();
                    $(this).prop('checked', isChecked);
                    if (isChecked) {
                        selectedRows.add(id);
                    } else {
                        selectedRows.delete(id);
                    }
                });
            });

            // Actualizar selección individual
            $('#tablaClientes tbody').on('change', '.fila-checkbox', function () {
                const id = $(this).val();
                if ($(this).is(':checked')) {
                    selectedRows.add(id);
                } else {
                    selectedRows.delete(id);
                }

                // Actualizar el estado del checkbox maestro
                const allChecked = $('.fila-checkbox:checked').length === $('.fila-checkbox').length;
                $('#selectAll').prop('checked', allChecked);
            });

            // Asegurar que al cambiar de página, el checkbox maestro se deseleccione
            tabla.on('draw', function () {
                $('#selectAll').prop('checked', false);
            });

            // Deseleccionar todo
            $('#deselectAll').on('click', function () {
                selectedRows.clear();
                $('#tablaClientes tbody .fila-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
            });

            // Exportar seleccionados
            $('#exportExcel').on('click', function () {
                if (selectedRows.size === 0) {
                    alert('No hay registros seleccionados para exportar.');
                    return;
                }

                const ids = Array.from(selectedRows);

                $.ajax({
                    url: 'exportar_excel.php',
                    method: 'POST',
                    data: { ids: ids },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (data) {
                        const url = window.URL.createObjectURL(data);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'exportacion_clientes.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        alert('Exportación completada con éxito.');
                    },
                    error: function () {
                        alert('Ocurrió un error al exportar los datos.');
                    }
                });
            });

            // Eliminar seleccionados
            $('#deleteRecords').on('click', function () {
                if (selectedRows.size === 0) {
                    alert('No hay registros seleccionados para eliminar.');
                    return;
                }

                if (!confirm('¿Estás seguro de que deseas eliminar los registros seleccionados? Esta acción no se puede deshacer.')) {
                    return;
                }

                const ids = Array.from(selectedRows);

                $.ajax({
                    url: 'eliminar_registros.php',
                    method: 'POST',
                    data: { ids: ids },
                    success: function (response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            alert(response.success);
                            selectedRows.clear();
                            tabla.ajax.reload();
                        }
                    },
                    error: function () {
                        alert('Ocurrió un error al intentar eliminar los registros.');
                    }
                });
            });

            // Asignar seleccionados
            $('#asignarForm').on('click', function () {

                // Obtener el usuario seleccionado
                const usuarioId = $('#usuario').val();

                if (!usuarioId) {
                    alert('Por favor, selecciona un usuario antes de asignar.');
                    return;
                }

                // Asegurarse de que hay celdas seleccionadas
                if (selectedRows.length === 0) {
                    alert('No hay registros seleccionados para asignar.');
                    return;
                }

                if (selectedRows.size === 0) {
                    alert('No hay registros seleccionados para asignar.');
                    return;
                }

                if (!confirm('¿Estás seguro de que deseas asignar los registros seleccionados? Esta acción no se puede deshacer.')) {
                    return;
                }

                const ids = Array.from(selectedRows);

                $.ajax({
                    url: 'asignar_clientes.php',
                    method: 'POST',
                    data: { ids: ids, usuario_id: usuarioId },
                    success: function (response) {
                        console.log('Respuesta del servidor:', response);
                        if (response.error) {
                            alert(response.error);
                        } else {
                            //alert(response.success);
                            selectedRows.clear();
                            tabla.ajax.reload();
                        }
                    },
                    error: function () {
                        alert('Ocurrió un error al intentar asignar los registros.');
                    }
                });
            });

        });
    </script>
</body>

</html>