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

<style>
    #sidebarToggle {
        position: fixed;
        top: -10px;
        left: 30%;
        transform: translateX(-50%);
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        cursor: pointer;
        z-index: 1001;
        border-radius: 0 0 5px 5px;
    }

    #sidebar {
        position: fixed;
        top: -100%;
        left: 0;
        width: 100%;
        max-height: 90%; /* para que no tape todo si hay mucho contenido */
        background-color: #f8f9fa;
        border-bottom: 1px solid #ccc;
        transition: top 0.3s ease;
        z-index: 1000;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        overflow-y: auto; /* para permitir scroll si hay mucho contenido */
    }

    #sidebar form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .filtro-box {
        flex: 1 1 200px; /* mínimo 200px de ancho, se ajusta automáticamente */
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;
        background: #fff;
    }

    #sidebar.visible {
        top: 0;
    }
</style>


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
    
    <div id="sidebar">
        
        <form id="filtroUsuarios">
            <div class="filtro-box">
                <h3>Usuario</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Sel.</th>
                                <th style="text-align: left;">Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="usuarios[]" value="<?= htmlspecialchars($user['nombre']) ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($user['nombre']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            <div class="filtro-box">
                <h3>Grupo</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Sel.</th>
                                <th style="text-align: left;">Grupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta para obtener grupos únicos
                            $grupos = [];
                            $query = "SELECT DISTINCT TRIM(grupo) AS grupo FROM clientes WHERE TRIM(grupo) != ''";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $grupos[] = $row['grupo'];
                            }

                            // Generar filas para los grupos
                            foreach ($grupos as $grupo): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="grupos[]" value="<?= htmlspecialchars($grupo) ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($grupo) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            <div class="filtro-box">
                <h3>Apellido</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Sel.</th>
                                <th style="text-align: left;">Apellido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta para obtener apellidos únicos
                            $grupos = [];
                            $query = "SELECT DISTINCT TRIM(apellido) AS apellido FROM clientes";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $apellidos[] = $row['apellido'];
                            }

                            // Generar filas para los apellidos
                            foreach ($apellidos as $apellido): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="apellidos[]" value="<?= htmlspecialchars($apellido) ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($apellido) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            <?php
            // Inicializar arreglo para evitar errores si no hay resultados
            $ultimagestions = [];

            // Consulta para obtener gestión únicos
            $query = "SELECT DISTINCT TRIM(ultimagestion) AS ultimagestion FROM notas";
            $result = $conn->query($query);

            // Verificar que la consulta se ejecutó correctamente y tiene resultados
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if (!empty($row['ultimagestion'])) {
                        $ultimagestions[] = $row['ultimagestion'];
                    }
                }
            }
            ?>

            <?php if (!empty($ultimagestions)): ?>
                <div class="filtro-box">
                    <h3>Última Gestión</h3>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">Sel.</th>
                                    <th style="text-align: left;">Gestión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimagestions as $ultimagestion): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ultimagestions[]" value="<?= htmlspecialchars($ultimagestion) ?>">
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($ultimagestion) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>


            <div class="filtro-box">
                <h3>Pais</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Sel.</th>
                                <th style="text-align: left;">Pais</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta para obtener pais únicos
                            $grupos = [];
                            $query = "SELECT DISTINCT TRIM(pais) AS pais FROM clientes";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $paises[] = $row['pais'];
                            }

                            // Generar filas para los paises
                            foreach ($paises as $pais): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="paises[]" value="<?= htmlspecialchars($pais) ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($pais) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>

            <!--div class="filtro-box">
                <h3>TL</h3>
                <div style="max-height: 200px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Sel.</th>
                                <th style="text-align: left;">TL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta para obtener TL únicos
                            $grupos = [];
                            $query = "SELECT id, TRIM(nombre) AS nombre FROM users where tipo IN (4,5)";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                $nombres[] = $row['nombre'];
                            }

                            // Generar filas para los TL
                            foreach ($nombres as $nombre): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="nombres[]" value="<?= htmlspecialchars($nombre) ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($nombre) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <br-->

            <button type="submit">Buscar</button>
            <button type="button" id="limpiarFiltros">Limpiar</button>
        </form>
    </div>


    <?php if ($tipo_usuario == 1): ?>
        <div id="sidebarToggle">☰</div>
    <?php endif; ?>

    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 1): ?>
            <!--button id="asignedtAll">Asignar Seleccion</button-->
            <div class="actions">
                <a href="asignar_grupos.php" target="_blank" class="btn btn-success">Asignar Grupo</a>
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


        <div class="ordenamiento">
        <label for="orden_campo">Ordenar por:</label>
        <select id="orden_campo">
            <option value="Nombre">Nombre</option>
            <option value="Apellido">Apellido</option>
            <option value="FechaCreacion">FechaCreacion</option>
            <option value="Estado">Estado</option>
            <option value="UltimaGestion">UltimaGestion</option>
            <option value="FechaAsignacion">FechaAsignacion</option>
            <option value="FechaUltimaGestion">FechaUltimaGestion</option>
            <option value="TP">TP</option>
            <option value="Pais">Pais</option>
        </select>

        <select id="orden_direccion">
            <option value="ASC">Ascendente</option>
            <option value="DESC">Descendente</option>
        </select>

        <button id="btn_ordenar">Ordenar</button>
        </div>


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
                ajax: {
                    url: 'datos.php',
                    type: 'GET',
                    data: function (d) {
                        // Filtro por usuarios
                        const usuariosSeleccionados = [];
                        $('#filtroUsuarios input[name="usuarios[]"]:checked').each(function () {
                            usuariosSeleccionados.push($(this).val());
                        });
                        if (usuariosSeleccionados.length > 0) {
                            d['usuarios[]'] = usuariosSeleccionados;
                        }

                        // Filtro por grupos
                        const gruposSeleccionados = [];
                        $('#filtroUsuarios input[name="grupos[]"]:checked').each(function () {
                            gruposSeleccionados.push($(this).val());
                        });
                        if (gruposSeleccionados.length > 0) {
                            d['grupos[]'] = gruposSeleccionados;
                        }

                        // Filtro por apellidos
                        const apellidosSeleccionados = [];
                        $('#filtroUsuarios input[name="apellidos[]"]:checked').each(function () {
                            apellidosSeleccionados.push($(this).val());
                        });
                        if (apellidosSeleccionados.length > 0) {
                            d['apellidos[]'] = apellidosSeleccionados;
                        }

                        // Filtro por gestion
                        const ultimagestionSeleccionados = [];
                        $('#filtroUsuarios input[name="ultimagestions[]"]:checked').each(function () {
                            ultimagestionSeleccionados.push($(this).val());
                        });
                        if (ultimagestionSeleccionados.length > 0) {
                            d['ultimagestions[]'] = ultimagestionSeleccionados;
                        }

                        // Filtro por pais
                        const paisesSeleccionados = [];
                        $('#filtroUsuarios input[name="paises[]"]:checked').each(function () {
                            paisesSeleccionados.push($(this).val());
                        });
                        if (paisesSeleccionados.length > 0) {
                            d['paises[]'] = paisesSeleccionados;
                        }

                        // Filtro por grupo
                        const nombresSeleccionados = [];
                        $('#filtroUsuarios input[name="nombres[]"]:checked').each(function () {
                            nombresSeleccionados.push($(this).val());
                        });
                        if (nombresSeleccionados.length > 0) {
                            d['nombres[]'] = nombresSeleccionados;
                        }

                        //Filtros para ordenamiento
                        d.orden_campo = $('#orden_campo').val();
                        d.orden_direccion = $('#orden_direccion').val();
                        
                    }
                },
                serverSide: true,
                //processing: true,
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

            // Cuando el usuario hace clic en el botón "Ordenar"
            $('#btn_ordenar').on('click', function () {
                tabla.ajax.reload();
        });

            // Manejar el envío del formulario
            $('#filtroUsuarios').on('submit', function (e) {
                e.preventDefault(); // Evitar recargar la página
                tabla.ajax.reload(); // Recargar la tabla con los nuevos filtros
            });


            // Manejar el botón "Limpiar"
            $('#limpiarFiltros').on('click', function () {
                // Desmarcar todos los checkboxes en el formulario
                $('#filtroUsuarios input[name="usuarios[]"]').prop('checked', false);

                // Eliminar cualquier filtro adicional configurado en el DataTable
                tabla.ajax.url('datos.php').load();
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

            //Evento Submit para Sidebar
            $('#filtroUsuarios').on('submit', function (e) {
                e.preventDefault(); // Evita que se recargue la página
                $('#tablaClientes').DataTable().ajax.reload(); // Reemplaza #miTabla con tu ID real

                // Obtener valores seleccionados de usuarios y grupos
                const usuariosSeleccionados = $('input[name="usuarios[]"]:checked').map(function () {
                    return this.value;
                }).get();

                const gruposSeleccionados = $('input[name="grupos[]"]:checked').map(function () {
                    return this.value;
                }).get();

                const apellidosSeleccionados = $('input[name="apellidos[]"]:checked').map(function () {
                    return this.value;
                }).get();

                const ultimagestionSeleccionados = $('input[name="ultimagestions[]"]:checked').map(function () {
                    return this.value;
                }).get();

                const paisesSeleccionados = $('input[name="paises[]"]:checked').map(function () {
                    return this.value;
                }).get();

                const nombresSeleccionados = $('input[name="nombres[]"]:checked').map(function () {
                    return this.value;
                }).get();

                // Validar que haya al menos un filtro seleccionado
                if (usuariosSeleccionados.length === 0 && gruposSeleccionados.length === 0 && apellidosSeleccionados.length === 0 && ultimagestionSeleccionados.length === 0 && paisesSeleccionados.length === 0 && nombresSeleccionados.length === 0) {
                    alert('Por favor selecciona un dato para filtrar.');
                    return;
                }

                // Configurar los parámetros para la solicitud AJAX
                const parametros = {
                    usuarios: usuariosSeleccionados,
                    grupos: gruposSeleccionados,
                    apellidos: apellidosSeleccionados,
                    ultimagestions: ultimagestionSeleccionados,
                    paises: paisesSeleccionados,
                    nombres: nombresSeleccionados,
                };

                // Actualizar la URL del DataTable y recargar los datos
                $('#tablaClientes').DataTable().ajax.url('datos.php').load({
                    data: parametros,
                    success: function () {
                        alert('Filtro aplicado correctamente.');
                    },
                    error: function () {
                        alert('Error al aplicar el filtro.');
                    },
                });
            });

            // Botón de limpiar filtros
            $('#limpiarFiltros').on('click', function () {
                // Deseleccionar todos los checkboxes
                $('#filtroUsuarios input[type="checkbox"]').prop('checked', false);

                // Restablecer la tabla sin filtros
                $('#tablaClientes').DataTable().ajax.url('datos.php').load();
            });

        });
    </script>

    <!--Sidebar-->
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');

        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('visible');
        });
    </script>


</body>

</html>