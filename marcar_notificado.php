<?php
include 'conexion.php'; // asegúrate de que este archivo se conecta a la BD

$data = json_decode(file_get_contents("php://input"));

if (isset($data->id)) {
    $id = $data->id;

    $stmt = $conn->prepare("UPDATE citas SET notificado = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();

    echo json_encode(["success" => $success]);
} else {
    echo json_encode(["success" => false, "error" => "ID no proporcionado"]);
}
?>
