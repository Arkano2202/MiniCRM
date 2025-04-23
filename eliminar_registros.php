<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];

    if (empty($ids) || !array_reduce($ids, fn($carry, $id) => $carry && is_numeric($id), true)) {
        echo json_encode(['error' => 'No se recibieron IDs válidos para eliminar.']);
        exit;
    }

    // Convertir los IDs en una lista separada por comas
    $ids_list = implode(',', array_map('intval', $ids));

    // Iniciar transacción
    mysqli_begin_transaction($conn);

    try {
        // Eliminar las notas asociadas
        $query_notas = "DELETE FROM notas WHERE TP IN (SELECT TP FROM clientes WHERE id IN ($ids_list))";
        if (!mysqli_query($conn, $query_notas)) {
            throw new Exception('Error al eliminar notas: ' . mysqli_error($conn));
        }

        // Eliminar los clientes
        $query_clientes = "DELETE FROM clientes WHERE id IN ($ids_list)";
        if (!mysqli_query($conn, $query_clientes)) {
            throw new Exception('Error al eliminar clientes: ' . mysqli_error($conn));
        }

        // Confirmar transacción
        mysqli_commit($conn);

        // Registro de depuración
        file_put_contents('debug.log', json_encode(['success' => 'Eliminación ejecutada correctamente', 'ids' => $ids], JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

        echo json_encode(['success' => 'Registros eliminados correctamente.']);
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        mysqli_rollback($conn);
        file_put_contents('debug.log', json_encode(['error' => $e->getMessage(), 'ids' => $ids], JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}
?>
