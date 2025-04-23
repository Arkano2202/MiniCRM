<?php
header('Content-Type: application/json');
session_start();

error_log("TIPO DE USUARIO EN SESIÓN: " . print_r($_SESSION['usuario_tipo'], true));

// Verifica si el usuario es de tipo 1 (administrador)
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] != 1) {
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit;
}

// Conexión a la base de datos
include 'conexion.php';

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// Consulta todas las citas
$sql = "SELECT c.id, c.titulo, c.descripcion, c.fecha, c.hora, c.notificado, c.usuario_id, u.nombre 
        FROM citas c 
        JOIN users u ON c.usuario_id = u.id";
$result = $conn->query($sql);

$eventos = [];
$colores = []; // Mapeo usuario_id => color

// Lista de colores predefinidos
$coloresDisponibles = ['#3498db', '#f1c40f', '#2ecc71', '#9b59b6', '#e67e22', '#e74c3c', '#1abc9c', '#34495e', '#7f8c8d', '#16a085'];
$coloresUsados = [];

function generarColorHex() {
    // Genera un color aleatorio en formato HEX
    return sprintf("#%06X", mt_rand(0, 0xFFFFFF));
}

while ($row = $result->fetch_assoc()) {
    $usuario_id = $row['usuario_id'];
    $usuarioNombre = $row['nombre'];

    // Asignar color único si aún no se ha hecho
    if (!isset($colores[$usuario_id])) {
        // Tomar un color disponible que no haya sido usado
        $color = null;
        foreach ($coloresDisponibles as $c) {
            if (!in_array($c, $coloresUsados)) {
                $color = $c;
                break;
            }
        }

        // Si se agotaron los colores predefinidos, generar uno aleatorio único
        if (!$color) {
            do {
                $color = generarColorHex();
            } while (in_array($color, $coloresUsados));
        }

        $colores[$usuario_id] = $color;
        $coloresUsados[] = $color;
        $nombresUsuarios[$usuario_id] = $usuarioNombre;
    }

    $eventos[] = [
        'id' => $row['id'],
        'title' => $row['titulo'],
        'start' => $row['fecha'] . 'T' . $row['hora'], // Sin offset
        'description' => $row['descripcion'],
        'notificado' => $row['notificado'],
        'usuario_id' => $usuario_id,
        'color' => $colores[$usuario_id],
        'usuario_nombre' => $usuarioNombre // ← este campo es esencial
    ];
}

echo json_encode($eventos);
?>
