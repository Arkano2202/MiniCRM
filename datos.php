<?php
include 'conexion.php';
$request = $_REQUEST;

// Incluir columna ficticia "checkbox" como primera

$limit = isset($request['length']) ? intval($request['length']) : 10;
$offset = isset($request['start']) ? intval($request['start']) : 0;

// Ordenamiento personalizado desde selects
$columns = [
    "Nombre", "Apellido", "FechaCreacion", "Estado",
    "UltimaGestion", "FechaAsignacion", "FechaUltimaGestion",
    "TP", "Pais", "Correo", "Asignado"
];

// Inicializa por defecto
$order_column = "Nombre";
$order_dir = "asc";

if (!empty($request['orden_campo']) && in_array($request['orden_campo'], $columns)) {
    $order_column = "ordered_data." . $conn->real_escape_string($request['orden_campo']);
    $order_dir = (!empty($request['orden_direccion']) && in_array(strtolower($request['orden_direccion']), ['asc', 'desc']))
        ? strtolower($request['orden_direccion'])
        : 'asc';
} elseif (!empty($request['order'][0]['column'])) {
    $order_column_index = intval($request['order'][0]['column']);
    if (isset($columns[$order_column_index])) {
        $order_column = "ordered_data." . $columns[$order_column_index];
    }
    $order_dir = (isset($request['order'][0]['dir']) && in_array(strtolower($request['order'][0]['dir']), ['asc', 'desc']))
        ? strtolower($request['order'][0]['dir'])
        : 'asc';
}

$search = isset($request['search']['value']) ? $conn->real_escape_string($request['search']['value']) : '';

session_start();
$tipo_usuario = $_SESSION['usuario_tipo'];
$usuario_actual = $_SESSION['usuario_nombre'];

$where_conditions = [];

// Filtro por grupo
if ($tipo_usuario == 1) {
    // Sin restricciones
} elseif (in_array($tipo_usuario, [2, 4])) {
    $where_conditions[] = "c.Grupo = 'FTD'";
} elseif (in_array($tipo_usuario, [3, 5])) {
    $where_conditions[] = "c.Grupo = 'Rete'";
}

// Filtro por asignación
if (in_array($tipo_usuario, [2, 3])) {
    $where_conditions[] = "c.Asignado = '" . $conn->real_escape_string($usuario_actual) . "'";
}

// Filtro de búsqueda
if (!empty($search)) {
    $where_conditions[] = "(
        c.Nombre LIKE '%$search%' OR
        c.TP LIKE '%$search%' OR
        c.Apellido LIKE '%$search%' OR
        c.Correo LIKE '%$search%' OR
        c.Numero LIKE '%$search%' OR
        c.Asignado LIKE '%$search%' 
    )";
}

// Filtros del sidebar
if (!empty($request['usuarios'])) {
    $usuarios = array_map([$conn, 'real_escape_string'], $request['usuarios']);
    $usuariosList = "'" . implode("','", $usuarios) . "'";
    $where_conditions[] = "c.Asignado IN ($usuariosList)";
}
if (!empty($request['grupos'])) {
    $grupos = array_map([$conn, 'real_escape_string'], $request['grupos']);
    $gruposList = "'" . implode("','", $grupos) . "'";
    $where_conditions[] = "c.Grupo IN ($gruposList)";
}
if (!empty($request['apellidos'])) {
    $apellidos = array_map([$conn, 'real_escape_string'], $request['apellidos']);
    $apellidosList = "'" . implode("','", $apellidos) . "'";
    $where_conditions[] = "c.Apellido IN ($apellidosList)";
}
if (!empty($request['ultimagestions'])) {
    $ultimagestions = array_map([$conn, 'real_escape_string'], $request['ultimagestions']);
    $ultimagestionsList = "'" . implode("','", $ultimagestions) . "'";
    $where_conditions[] = "n.UltimaGestion IN ($ultimagestionsList)";
}
if (!empty($request['paises'])) {
    $paises = array_map([$conn, 'real_escape_string'], $request['paises']);
    $paisesList = "'" . implode("','", $paises) . "'";
    $where_conditions[] = "c.Pais IN ($paisesList)";
}

// WHERE final
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Consulta principal
$sql = "
    SELECT ordered_data.*
    FROM (
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
    ) AS ordered_data
    LEFT JOIN users u ON ordered_data.Asignado = u.Nombre
    ORDER BY $order_column $order_dir
    LIMIT $limit OFFSET $offset
    ;
";

error_log("DEBUG: orden_campo=" . $request['orden_campo']);
error_log("DEBUG: orden_direccion=" . $request['orden_direccion']);
error_log("DEBUG: order_column final=" . $order_column);
error_log("DEBUG: order_dir final=" . $order_dir);

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $correo_mostrado = $row['Correo'];
    if (in_array($tipo_usuario, [2, 3])) {
        $correo_mostrado = str_repeat('*', strlen($row['Correo']));
    }
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
        $correo_mostrado,
        $row['Asignado']
    ];
}

// Respuesta JSON
echo json_encode([
    "draw" => intval($request['draw']),
    "recordsTotal" => $conn->query("SELECT COUNT(TP) FROM clientes")->fetch_row()[0],
    "recordsFiltered" => $conn->query("
        SELECT COUNT(c.TP) 
        FROM clientes c 
        LEFT JOIN notas n ON n.TP = c.TP
        $where_clause
    ")->fetch_row()[0],
    "data" => $data
]);
exit();
?>
