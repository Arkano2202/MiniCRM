<?php
session_start();

$titulo_pagina = 'Calendario de citas';
$archivo_eventos = in_array($_SESSION['usuario_tipo'], [1, 4, 5]) ? 'eventos_admin.php' : 'eventos.php';

// Solo si es admin, cargamos la lista de usuarios
$usuarios = [];
if (in_array($_SESSION['usuario_tipo'], [1, 4, 5])) {
    include 'conexion.php'; // Asegúrate de que este archivo establece la conexión a la BD
    $resultado = $conn->query("SELECT id, nombre FROM users");
    while ($row = $resultado->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components/buttons.css">
    <link rel="stylesheet" href="css/components/forms.css">
    <link rel="stylesheet" href="css/pages/calendario.css">
    <title>Calendario de Citas</title>
    <!-- Cargar jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Cargar Moment.js (requerido por FullCalendar) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.css" rel="stylesheet" />

</head>

<?php if (in_array($_SESSION['usuario_tipo'], [1, 4, 5])): ?>
    <button class="btn btn-success" id="abrirModalAdmin">Crear Cita para Usuario</button>
<?php endif; ?>

<body>
    
    <?php include 'includes/header.php'; ?>
    <div class="container">

        <!-- <h2>Calendario de Citas</h2> -->
        <div id="leyenda-usuarios" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
        <div id="calendar"></div>

        <!-- Modal para agregar nueva cita o modificarla -->
        <div id="modalCita" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Detalles de la Cita</h3>
                <form class="form" id="formCita">
                    <div class="form-group">
                        <label>Título:</label>
                        <input type="text" id="titulo" required>
                    </div>
                    <div class="form-group flex-column align-start">
                        <label>Link Cliente:</label>
                        <textarea id="descripcion"></textarea>
                        <div id="descripcionHTML" style="margin-top: 10px;"></div>
                    </div>
                    <div class="form-group flex-column align-start">
                        <label>Fecha y Hora:</label>
                        <input type="datetime-local" id="fecha_hora" required>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Guardar Cita">
                </form>

                <!-- Botones de acciones adicionales (Actualizar y Eliminar) -->
                <div class="actions">
                    <div class="appointments">
                        <button class="btn btn-secondary" id="actualizar" style="display:none;">Actualizar Cita</button>
                        <button class="btn btn-danger" id="eliminar" style="display:none;">Eliminar Cita</button>
                    </div>
                    <button type="button" class="btn btn-light" id="cancelar">Cancelar</button>

                </div>
            </div>
        </div>

        <!--Modal para admin-->
        <?php if (in_array($_SESSION['usuario_tipo'], [1, 4, 5])): ?>
        <div id="modalCitaAdmin" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Crear Cita para un Usuario</h3>
                <form class="form" id="formCitaAdmin">
                    <div class="form-group">
                        <label for="usuario_id">Seleccionar Usuario:</label>
                        <select id="usuario_id" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Título:</label>
                        <input type="text" id="titulo_admin" required>
                    </div>
                    <div class="form-group">
                        <label>Link Cliente:</label>
                        <textarea id="descripcion_admin"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Fecha y Hora:</label>
                        <input type="datetime-local" id="fecha_hora_admin" required>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Guardar Cita">
                </form>
                <button type="button" class="btn btn-light" id="cancelar_admin">Cancelar</button>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>

    <!-- Script de inicialización -->
    <script>
        // Función para confirmar el cierre de sesión
        function confirmLogout() {
            if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                window.location.href = "logout.php"; // Redirigir a la página de cierre de sesión
            }
        }
        //<<<<<<<<<<<<<<<<<<<<<<<<<<<
        function generarLeyendaUsuarios(eventos) {
            const contenedor = $('#leyenda-usuarios');
            contenedor.empty();

            const usuarios = {};

            eventos.forEach(evento => {
                if (!usuarios[evento.usuario_id]) {
                    usuarios[evento.usuario_id] = {
                        nombre: evento.usuario_nombre,
                        color: evento.color
                    };
                }
            });

            Object.values(usuarios).forEach(usuario => {
                const etiqueta = $('<div></div>').text(usuario.nombre).css({
                    backgroundColor: usuario.color,
                    color: '#fff',
                    padding: '6px 12px',
                    borderRadius: '6px',
                    fontWeight: 'bold'
                });
                contenedor.append(etiqueta);
            });
        }
        //>>>>>>>>>>>>>>>>>>>>>>>>>>>

        function yaFueNotificado(id) {
            return localStorage.getItem("notificado_" + id) === "true";
        }

        function marcarComoNotificado(id) {
            localStorage.setItem("notificado_" + id, "true");
        }

        function obtenerHoraBogota(fecha) {
            const localStr = fecha.toLocaleString("en-US", { timeZone: "America/Bogota" });
            return new Date(localStr);
        }

        function verificarNotificaciones() {
            const ahora = obtenerHoraBogota(new Date());

            $('#calendar').fullCalendar('clientEvents', function (event) {
                if (!event.real || !event.id || event.notificado == 1) return false;

                const inicio = obtenerHoraBogota(new Date(event.real));
                const diferenciaMin = (inicio - ahora) / 60000;

                console.log(`⏱️ Evento "${event.title}" comienza en: ${inicio}`);
                console.log(`⏱️ Hora actual: ${ahora}`);
                console.log(`Diferencia en minutos: ${diferenciaMin}`);

                if (diferenciaMin > 1 && diferenciaMin < 20) {
                    const mensaje = `Tienes una cita: "${event.title}" a las ${inicio.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

                    if (Notification.permission === "granted") {
                        new Notification("⏰ Recordatorio de cita", {
                            body: mensaje,
                            icon: "https://cdn-icons-png.flaticon.com/512/747/747310.png"
                        });
                    } else {
                        alert(mensaje);
                    }

                    // Actualizar en la base de datos
                    fetch("marcar_notificado.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: event.id })
                    }).then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                console.log("✅ Evento actualizado como notificado");
                                event.notificado = 1; // para que no lo vuelva a verificar en esta sesión
                            }
                        });
                }

                return false;
            });
        }

        // Solicita permiso de notificaciones y lanza notificación de prueba
        document.addEventListener("DOMContentLoaded", function () {
            if ("Notification" in window) {
                if (Notification.permission !== "granted") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            new Notification("✅ Notificaciones activadas", {
                                body: "Recibirás avisos de tus citas.",
                                icon: "https://cdn-icons-png.flaticon.com/512/747/747310.png"
                            });
                        }
                    });
                } else {
                    new Notification("✅ Notificaciones activas", {
                        body: "Estás listo para recibir avisos.",
                        icon: "https://cdn-icons-png.flaticon.com/512/747/747310.png"
                    });
                }
            }
            console.log(`Entro`);
            // Ejecutar verificación cada minuto
            setInterval(verificarNotificaciones, 60000);
        });

        //<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("formCita").addEventListener("submit", function (e) {
                e.preventDefault(); // evita que recargue la página

                const titulo = document.getElementById("titulo").value;
                const descripcion = document.getElementById("descripcion").value;
                const fecha_hora = document.getElementById("fecha_hora").value;
                const usuario_id = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null'; ?>;

                if (!usuario_id) {
                    alert("Usuario no identificado.");
                    return;
                }

                fetch("guardar_cita.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        titulo: titulo,
                        descripcion: descripcion,
                        fecha_hora: fecha_hora,
                        usuario_id: usuario_id
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            $('#calendar').fullCalendar('refetchEvents');
                            alert("✅ Cita guardada correctamente.");
                            $('#modalCita').hide();
                        } else {
                            alert("❌ Error al guardar la cita.");
                        }
                    })
                    .catch(err => {
                        console.error("Error en el fetch:", err);
                        alert("❌ Error en la solicitud.");
                    });
            });
        });
        //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

        $(document).ready(function () {
            const usuario_id = <?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'null'; ?>;

            if (usuario_id === null) {
                alert('No estás logueado');
                return;
            }

            $('#calendar').fullCalendar({
                locale: 'es',
                //events: '<?php echo $archivo_eventos; ?>', // Cargar los eventos desde el archivo PHP
                events: {
                    url: '<?php echo $archivo_eventos; ?>',
                    method: 'GET',
                    success: function (eventos) {
                        generarLeyendaUsuarios(eventos);
                        return eventos;
                    },
                    failure: function () {
                        alert('Error al cargar las citas');
                    }
                },
                eventLimit: true, // activa el "mostrar más"
                eventLimitText: "más", // texto personalizado
                selectable: true,
                viewRender: function (view, element) {
                    $(".fc-day-grid-event").css({
                        "white-space": "normal"
                    });

                    $(".fc-day").css({
                        "max-height": "120px",
                        "overflow-y": "auto"
                    });
                },

                eventRender: function (event, element) {
                    element.hover(
                        function (e) {
                            const tooltip = $('<div class="tooltip"></div>')
                                .html(
                                    `<strong>Cita:</strong> ${event.title}<br>
                                    <strong>Hora:</strong> ${moment(event.start).format('HH:mm')}<br>
                                    <strong>Usuario:</strong> ${event.usuario_nombre}`
                                )
                                .css({
                                    position: 'absolute',
                                    background: 'rgba(0, 0, 0, 0.85)',
                                    color: '#fff',
                                    padding: '12px',
                                    borderRadius: '8px',
                                    fontSize: '16px',
                                    fontWeight: 'bold',
                                    zIndex: 1000,
                                    pointerEvents: 'none',
                                    boxShadow: '0 0 10px rgba(0,0,0,0.5)'
                                })
                                .appendTo('body')
                                .fadeIn('slow');

                            $(this).on('mousemove.tooltip', function (e) {
                                tooltip.css({
                                    top: e.pageY - tooltip.outerHeight() - 10,
                                    left: e.pageX + 10
                                });
                            });
                        },
                        function () {
                            $('.tooltip').remove();
                            $(this).off('mousemove.tooltip');
                        }
                    );
                },

                select: function (start, end) {
                    // Mostrar el modal con la fecha seleccionada
                    $('#modalCita').show();
                    $('#fecha_hora').val(moment(start).toISOString().slice(0, 16)); // Corregido aquí
                    $('#titulo').val(''); // Limpiar los campos
                    $('#descripcion').val('');
                    $('#descripcionHTML').hide(); // Oculta el div con HTML (por si venías de otra cita)
                    $('#descripcion').show();     // Muestra el textarea para escribir nueva cita

                    // Cerrar el modal al hacer clic en la 'X'
                    $('.close').click(function () {
                        $('#modalCita').hide();
                    });

                    // Cancelar acción en el modal
                    $('#cancelar').click(function () {
                        $('#modalCita').hide();
                    });
                },
                eventClick: function (event) {
                    // Mostrar los datos de la cita seleccionada en el modal
                    $('#modalCita').show();
                    $('#titulo').val(event.title); // Asignar el título de la cita
                    //$('#descripcion').val(event.description); // Asignar la descripción

                    $('#descripcion').val(event.description); // para edición
                    // Convertir URLs en la descripción en enlaces
                    function enlazarTexto(texto) {
                        const urlRegex = /(https?:\/\/[^\s]+)/g;
                        return texto.replace(urlRegex, url => `<a href="${url}" target="_blank" class="enlace-bonito">${url}</a>`);
                    }

                    $('#descripcionHTML').html(enlazarTexto(event.description)).show();
                    $('#descripcion').hide(); // oculta el textarea mientras se visualiza
                    $('#fecha_hora').val(moment(event.start).toISOString().slice(0, 16)); // Asignar la fecha y hora

                    // Mostrar los botones de acción (actualizar y eliminar)
                    $('#actualizar').show();
                    $('#eliminar').show();

                    // Cerrar el modal al hacer clic en la 'X'
                    $('.close').click(function () {
                        $('#modalCita').hide();
                    });

                    // Cancelar acción en el modal
                    $('#cancelar').click(function () {
                        $('#modalCita').hide();
                    });

                    // Cerrar modal
                    $('.close, #cancelar').off('click').click(function () {
                        $('#modalCita').hide();
                    });

                    // ← Limpiar eventos previos para evitar múltiples clics
                    $('#actualizar').off('click').click(function () {
                        const titulo = $('#titulo').val();
                        const descripcion = $('#descripcion').val();
                        const fecha_hora = $('#fecha_hora').val();

                        if (!usuario_id) {
                            alert('Usuario no identificado');
                            return;
                        }

                        fetch('actualizar_cita.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id: event.id,
                                titulo: titulo,
                                descripcion: descripcion,
                                fecha_hora: fecha_hora,
                                usuario_id: usuario_id
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    $('#calendar').fullCalendar('refetchEvents');
                                    alert('Cita actualizada');
                                    $('#modalCita').hide();
                                } else {
                                    alert('Error al actualizar');
                                }
                            });
                    });

                    $('#eliminar').off('click').click(function () {
                        if (confirm('¿Estás seguro de eliminar esta cita?')) {
                            fetch('eliminar_cita.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    id: event.id
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        $('#calendar').fullCalendar('refetchEvents');
                                        alert('Cita eliminada');
                                        $('#modalCita').hide();
                                    } else {
                                        alert('Error al eliminar');
                                    }
                                });
                        }
                    });
                }
            });
        });
    </script>



    <script>
    $(document).ready(function () {
        // Mostrar modal de admin manualmente (puedes ligarlo a un botón)
        $('#abrirModalAdmin').on('click', function () {
            $('#modalCitaAdmin').show();
        });

        // Cerrar el modal
        $('#cancelar_admin, #modalCitaAdmin .close').on('click', function () {
            $('#modalCitaAdmin').hide();
            $('#formCitaAdmin')[0].reset();
        });

        // Enviar cita del admin
        $('#formCitaAdmin').on('submit', function (e) {
            e.preventDefault();

            const citaData = {
                usuario_id: $('#usuario_id').val(),
                titulo: $('#titulo_admin').val(),
                descripcion: $('#descripcion_admin').val(),
                fecha_hora: new Date($('#fecha_hora_admin').val()).toISOString()
            };

            $.ajax({
                url: 'eventos_admin.php',
                method: 'POST',
                data: {
                    action: 'crear_admin',
                    ...citaData
                },
                success: function (response) {
                    alert('Cita creada correctamente');
                    $('#modalCitaAdmin').hide();
                    $('#calendar').fullCalendar('refetchEvents');
                },
                error: function () {
                    alert('Error al crear la cita');
                }
            });
        });
    });
    </script>



</body>

</html>