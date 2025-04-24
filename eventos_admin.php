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
$coloresDisponibles = [
    '#1abc9c', '#16a085', '#2ecc71', '#27ae60', '#3498db', '#2980b9', '#9b59b6', '#8e44ad',
    '#34495e', '#2c3e50', '#f1c40f', '#f39c12', '#e67e22', '#d35400', '#e74c3c', '#c0392b',
    '#ecf0f1', '#bdc3c7', '#95a5a6', '#7f8c8d', '#ff5733', '#33ff57', '#5733ff', '#33d1ff',
    '#ff33a6', '#a633ff', '#33ffbd', '#ffbd33', '#3366ff', '#66ff33', '#ff3366', '#6f42c1',
    '#fd7e14', '#20c997', '#ffc107', '#6c757d', '#17a2b8', '#6610f2', '#dc3545', '#28a745',
    '#007bff', '#6f42c1', '#e83e8c', '#343a40', '#adb5bd', '#198754', '#0dcaf0', '#fdc500',
    '#a3e635', '#f87171'
];
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

    $inicio = $row['fecha'] . 'T' . $row['hora'];
    $duracionMinutos = 30; // o la duración real si la tienes
    $fin = date('Y-m-d\TH:i:s', strtotime("+$duracionMinutos minutes", strtotime($inicio)));

    $eventos[] = [
        'id' => $row['id'],
        'title' => $row['titulo'],
        'start' => $inicio,
        'end' => $fin,
        'description' => $row['descripcion'],
        'notificado' => $row['notificado'],
        'usuario_id' => $usuario_id,
        'color' => $colores[$usuario_id],
        'usuario_nombre' => $usuarioNombre // ← este campo es esencial
    ];
}

echo json_encode($eventos);
?>
