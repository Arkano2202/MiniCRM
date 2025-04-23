<?php
require 'conexion.php';
date_default_timezone_set('America/Bogota');

$ahora = new DateTime();
$limite = (clone $ahora)->modify('+20 minutes');
$fecha_actual = $ahora->format('Y-m-d');
$hora_inicio = $ahora->format('H:i:s');
$hora_limite = $limite->format('H:i:s');

$sql = "SELECT * FROM citas WHERE fecha = ? AND hora BETWEEN ? AND ? AND notificado = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $fecha_actual, $hora_inicio, $hora_limite);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Aquí podrías enviar un correo, SMS, WhatsApp, etc.
    $mensaje = "Hola, tienes una cita próxima: '{$row['titulo']}' a las {$row['hora']}.";

    // Por ejemplo, enviar por correo (requiere configurar correo en el servidor):
    mail("usuario@example.com", "Recordatorio de cita", $mensaje); // Cambia esto por el correo real del usuario

    // Marcar como notificada
    $update = $conn->prepare("UPDATE citas SET notificado = 1 WHERE id = ?");
    $update->bind_param("i", $row['id']);
    $update->execute();
}

echo "Notificaciones procesadas.";
?>
