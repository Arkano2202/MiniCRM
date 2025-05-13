<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components/buttons.css">
    <link rel="stylesheet" href="css/pages/formulario_cliente.css">
    <title>Formulario Cliente</title>
    <script>
        function validarFormulario(event) {
            const estado = document.getElementById('estado').value;
            const observaciones = document.getElementById('notas').value.trim();

            if (!estado) {
                alert('Por favor, selecciona un estado.');
                event.preventDefault();
                return false;
            }

            if (observaciones.length < 2) {
                alert('Por favor, ingresa al menos 2 caracteres en observaciones.');
                event.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
    <?php
    session_start(); // Iniciar la sesión
    include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos
    
    // Obtener el parámetro "tp" de la URL
    $tp_cliente = isset($_GET['tp']) ? $_GET['tp'] : '';

    // Validar el tp del cliente
    if (empty($tp_cliente)) {
        echo "<h2>Error: TP de cliente no válido.</h2>";
        exit;
    }

    // Validar conexión
    if (!isset($conn) || $conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $titulo_pagina = "Formulario cliente";

    // Consultar los datos del cliente usando TP
    $query_cliente = $conn->prepare("SELECT Nombre, Apellido, TP, Correo, Pais, Numero FROM clientes WHERE TP = ?");
    $query_cliente->bind_param("s", $tp_cliente);
    $query_cliente->execute();
    $resultado = $query_cliente->get_result();
    $cliente = $resultado->fetch_assoc();

    if (!$cliente) {
        echo "<h2>Error: Cliente no encontrado.</h2>";
        exit;
    }

    // Obtener el tipo de usuario
    $tipo_usuario = $_SESSION['usuario_tipo'];

    // Obtener la extensión del usuario desde la tabla users
    $user_id = $_SESSION['usuario_id']; // Asegúrate que en la sesión se guarde este ID
    $query_ext = $conn->prepare("SELECT Ext FROM users WHERE id = ?");
    $query_ext->bind_param("i", $user_id);
    $query_ext->execute();
    $result_ext = $query_ext->get_result();
    $ext_row = $result_ext->fetch_assoc();
    $extension_usuario = $ext_row['Ext'] ?? '';

    // Funciones para ocultar datos
    function ocultar_dato($dato)
    {
        return str_repeat('*', strlen($dato));
    }

    // Aplicar restricciones según el tipo de cliente
    $correo_mostrado = $cliente['Correo'];
    $numero_mostrado = $cliente['Numero'];
    $numero_sin_filtro = $cliente['Numero'];


    if (in_array($tipo_usuario, [4, 5])) {
        $numero_mostrado = ocultar_dato($cliente['Numero']);
    } elseif (in_array($tipo_usuario, [2, 3])) {
        $correo_mostrado = ocultar_dato($cliente['Correo']);
        $numero_mostrado = ocultar_dato($cliente['Numero']);
    }

    // Consultar los estados
    $query_estados = "SELECT id, estado FROM estados";
    $resultado_estados = $conn->query($query_estados);

    // Consultar las notas del cliente
    $query_notas = $conn->prepare("SELECT UltimaGestion, FechaUltimaGestion, Descripcion, user FROM notas WHERE TP = ? ORDER BY FechaUltimaGestion DESC");
    $query_notas->bind_param("s", $tp_cliente);
    $query_notas->execute();
    $resultado_notas = $query_notas->get_result();
    ?>
    <?php include 'includes/header.php'; ?>
    <div class="container center">
        <form action="procesar_cliente.php" method="POST" onsubmit="return validarFormulario(event)">
            <h1>Formulario del Cliente</h1>
            <table class="form-table">
                <tr>
                    <td>
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo $cliente['Nombre'] ?? ''; ?>"
                            readonly>
                    </td>
                    <td>
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido"
                            value="<?php echo $cliente['Apellido'] ?? ''; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="tp">TP:</label>
                        <input type="text" id="tp" name="tp" value="<?php echo $cliente['TP'] ?? ''; ?>" readonly>
                    </td>
                    <td>
                        <label for="correo">Correo:</label>
                        <input type="email" id="correo" name="correo" value="<?php echo $correo_mostrado; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pais">País:</label>
                        <input type="text" id="pais" name="pais" value="<?php echo $cliente['Pais'] ?? ''; ?>" readonly>
                    </td>
                    <td>
                        <label for="telefono">Teléfono:</label>
                        <a href="#" class="llamar" data-numero="<?php echo htmlspecialchars($numero_sin_filtro); ?>"
                        data-ext="<?php echo htmlspecialchars($extension_usuario); ?>"
                        id="telefono" style="
                        display: inline-block; 
                        padding: 8px 10px; 
                        border: 1px solid #ccc; 
                        border-radius: 4px; 
                        background-color: #fff; 
                        text-decoration: none; 
                        color: #333; 
                        font-size: 14px; 
                        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                    ">
                            <?php echo $numero_mostrado; ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <label for="notas">Observaciones:</label>
                        <textarea id="notas" name="notas" maxlength="1000"
                            placeholder="Escribe tus observaciones aquí..."></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <label for="estado">Estado:</label>
                        <select id="estado" name="estado">
                            <option value="">Selecciona un estado</option>
                            <?php while ($estado = $resultado_estados->fetch_assoc()): ?>
                                <option value="<?php echo $estado['estado']; ?>">
                                    <?php echo $estado['estado']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="tp_cliente" value="<?php echo $tp_cliente; ?>">
            <button class="btn btn-primary sm" type="submit">Guardar</button>
        </form>

        <div class="table-container">
            <h1>Notas del Cliente</h1>
            <table class="notes-table">
                <thead>
                    <tr>
                        <th>Última Gestión</th>
                        <th>Fecha de Última Gestión</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($nota = $resultado_notas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $nota['UltimaGestion']; ?></td>
                            <td><?php echo $nota['FechaUltimaGestion']; ?></td>
                            <td><?php echo $nota['Descripcion']; ?></td>
                            <td><?php echo $nota['user']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function confirmLogout() {
            if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                window.location.href = "logout.php"; // Redirigir a la página de cierre de sesión
            }
        }
        document.addEventListener("DOMContentLoaded", function () {
            const botones = document.querySelectorAll(".llamar");
            botones.forEach(boton => {
                boton.addEventListener("click", function (e) {
                    e.preventDefault();

                    const numero = this.dataset.numero;
                    const extension = this.dataset.ext;

                    fetch("llamar.php?numero=" + encodeURIComponent(numero) + "&ext=" + encodeURIComponent(extension))
                        .then(response => response.text())
                        .then(data => {
                            console.log("Respuesta de llamar.php:", data);
                        })
                        .catch(error => {
                            console.error("Error al llamar:", error);
                        });
                });
            });
        });
    </script>
</body>

</html>