<?php
include 'conexion.php';
$request = $_REQUEST;

$columns = [
    "Nombre", "Apellido", "FechaCreacion", "Estado",
    "UltimaGestion", "FechaAsignacion", "FechaUltimaGestion",
    "TP", "Pais", "Correo", "Asignado"
];

$limit = isset($request['length']) ? intval($request['length']) : 10;
$offset = isset($request['start']) ? intval($request['start']) : 0;
$order_column_index = isset($request['order'][0]['column']) ? intval($request['order'][0]['column']) : 0;
$order_column = isset($columns[$order_column_index]) ? $columns[$order_column_index] : 'Nombre';
$order_dir = (isset($request['order'][0]['dir']) && in_array($request['order'][0]['dir'], ['asc', 'desc'])) ? $request['order'][0]['dir'] : 'asc';

$search = isset($request['search']['value']) ? $conn->real_escape_string($request['search']['value']) : '';

// Obtener tipo de usuario y usuario actual de la sesión
session_start();
$tipo_usuario = $_SESSION['usuario_tipo'];
$usuario_actual = $_SESSION['usuario_nombre'];

// Condiciones adicionales según el tipo de usuario
$where_conditions = [];

// Filtro por grupo
if ($tipo_usuario == 1) {
    // Sin restricciones para tipo 1
} elseif (in_array($tipo_usuario, [2, 4])) {
    $where_conditions[] = "c.Grupo = 'FTD'";
} elseif (in_array($tipo_usuario, [3, 5])) {
    $where_conditions[] = "c.Grupo = 'Rete'";
}

// Filtro por asignación
if (in_array($tipo_usuario, [2, 3])) {
    $where_conditions[] = "c.Asignado = '" . $conn->real_escape_string($usuario_actual) . "'";
}

// Filtro de búsqueda general
if (!empty($search)) {
    $where_conditions[] = "(
        c.Nombre LIKE '%$search%' OR
        c.TP LIKE '%$search%' OR
        c.Apellido LIKE '%$search%' OR
        c.Correo LIKE '%$search%' OR
        c.Numero LIKE '%$search%'
    )";
}

// Construir condición WHERE final
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Consulta principal
$sql = "
    SELECT * FROM (
        SELECT 
            c.*, 
            n.UltimaGestion, 
            n.FechaUltimaGestion
        FROM clientes c
        LEFT JOIN (
            SELECT n1.*
            FROM notas n1
            INNER JOIN (
                SELECT TP, MAX(FechaUltimaGestion) AS max_fecha
                FROM notas
                GROUP BY TP
            ) n2 ON n1.TP = n2.TP AND n1.FechaUltimaGestion = n2.max_fecha
        ) n ON c.TP = n.TP
        $where_clause
        ORDER BY $order_column $order_dir
    ) AS ordered_data
    LIMIT $limit OFFSET $offset
";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[]  = [
        '<input type="checkbox" class="fila-checkbox" value="' . $row['id'] . '">',
        $row['Nombre'],
        $row['Apellido'],
        $row['FechaCreacion'],
        $row['Estado'],
        $row['UltimaGestion'],
        $row['FechaAsignacion'],
        $row['FechaUltimaGestion'],
        $row['TP'],
        $row['Pais'],
        $row['Correo'],
        $row['Asignado']
    ];
}

// Respuesta en formato JSON
echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => $conn->query("SELECT COUNT(*) FROM clientes")->fetch_row()[0],
    "recordsFiltered" => $conn->query("SELECT COUNT(*) FROM clientes c $where_clause")->fetch_row()[0],
    "data" => $data
]);
?>
