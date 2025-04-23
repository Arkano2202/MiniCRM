<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    $usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : null;

    if (empty($ids) || !$usuario_id) {
        echo json_encode(['error' => 'Faltan datos para procesar la solicitud.']);
        exit;
    }

    // Consulta para obtener el tipo de usuario
    $sql_usuario = "SELECT Tipo, Nombre FROM users WHERE id = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);

    if (!$stmt_usuario) {
        echo json_encode(['error' => 'Error al preparar la consulta para obtener el tipo de usuario.']);
        exit;
    }

    $stmt_usuario->bind_param('i', $usuario_id);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();

    if ($result_usuario->num_rows === 0) {
        echo json_encode(['error' => 'Usuario no encontrado.']);
        exit;
    }

    $usuario = $result_usuario->fetch_assoc();
    $usuario_tipo = intval($usuario['Tipo']);
    $usuario_nombre = $usuario['Nombre'];

    // Definir valores dinámicos para Grupo y Estado
    if (in_array($usuario_tipo, [2, 4])) {
        $grupo = 'FTD';
        $estado = 'Asignado';
    } elseif (in_array($usuario_tipo, [3, 5])) {
        $grupo = 'Rete';
        $estado = 'Convertido';
    } else {
        echo json_encode(['error' => 'Tipo de usuario no válido para esta operación.']);
        exit;
    }

    // Validar y construir placeholders para IDs
    $ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
    $sql_update = "UPDATE clientes SET Grupo = ?, Estado = ?, Asignado = ?, FechaAsignacion = CURDATE() WHERE id IN ($ids_placeholder)";
    $stmt_update = $conn->prepare($sql_update);

    if (!$stmt_update) {
        echo json_encode(['error' => 'Error al preparar la consulta de actualización.']);
        exit;
    }

    // Vincular parámetros dinámicamente
    $types = str_repeat('i', count($ids));
    $params = array_merge([$grupo, $estado, $usuario_nombre], $ids);
    $stmt_update->bind_param("sss$types", ...$params);

    if ($stmt_update->execute()) {
        echo json_encode(['success' => 'Registros actualizados correctamente.']);
    } else {
        echo json_encode(['error' => 'Error al actualizar los registros.']);
    }

    $stmt_usuario->close();
    $stmt_update->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}
?>
