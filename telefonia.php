<?php
session_start(); // Iniciar la sesión
include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos

// Configuración de AMI
$ami_host = 'call.t-lan.co'; // Dirección del servidor Issabel
$ami_port = 5038;      // Puerto del AMI (por defecto es 5038)
$ami_user = 'cb_tesst';    // Usuario de AMI configurado en manager.conf
$ami_pass = 'Fo8kCg^x8Qgs$t'; // Contraseña de AMI

// Obtener el número de teléfono desde la URL
if (!isset($_GET['numero'])) {
    die('Número no proporcionado.');
}

$numero = $_GET['numero'];

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    die('Usuario no autenticado.');
}

$user_id = $_SESSION['usuario_id'];

// Obtener la extensión desde la base de datos
$query = "SELECT ext FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Extensión no encontrada para el usuario.');
}

$row = $result->fetch_assoc();
$origen = $row['ext'];

// Validar que la extensión no esté vacía
if (empty($origen)) {
    die('La extensión del usuario está vacía.');
}

// Configuración de la extensión que realizará la llamada
$contexto = 'from-internal'; // Contexto utilizado por Issabel para llamadas internas

/*var_dump($origen);
exit;*/

// Conexión al AMI
$socket = fsockopen($ami_host, $ami_port, $errno, $errstr, 10);
if (!$socket) {
    die("No se pudo conectar al AMI: $errstr ($errno)\n");
}

// Autenticación
fwrite($socket, "Action: Login\r\n");
fwrite($socket, "Username: $ami_user\r\n");
fwrite($socket, "Secret: $ami_pass\r\n\r\n");

// Iniciar la llamada
fwrite($socket, "Action: Originate\r\n");
fwrite($socket, "Channel: T-LAN/SIP/$origen\r\n");
fwrite($socket, "Exten: $numero\r\n");
fwrite($socket, "Context: $contexto\r\n");
fwrite($socket, "Priority: 1\r\n");
fwrite($socket, "CallerID: $origen\r\n\r\n");

// Leer la respuesta del AMI
while (!feof($socket)) {
    $line = fgets($socket, 128);
    echo $line . "<br>";
}

// Cerrar la conexión
fclose($socket);

?>
