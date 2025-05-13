<?php
$host = 'call.t-lan.co';
$port = 5038;
$username = 'cb_tesst';
$secret = 'Fo8kCg^x8Qgs$t';
$callerid = 'Anonimo';
$timeout = 30000;

$numeroDestino = $_GET['numero'] ?? '';
$extOrigen = $_GET['extension'] ?? '';

if (!$numeroDestino || !$extOrigen) {
    echo "Faltan parámetros: 'numero' y/o 'extension'.";
    exit;
}

$canal = "Local/{$extOrigen}@from-internal";

// Conectar al AMI
$socket = fsockopen($host, $port, $errno, $errstr, 10);
if (!$socket) {
    echo "❌ Error al conectar: $errstr ($errno)";
    exit;
}
stream_set_timeout($socket, 2);

function enviarComando($socket, $comando) {
    fputs($socket, $comando . "\r\n\r\n");
    $respuesta = '';
    while (!feof($socket)) {
        $linea = fgets($socket, 512);
        if ($linea === false || trim($linea) === '') break;
        $respuesta .= $linea;
    }
    return $respuesta;
}

// Login
$loginCmd = "Action: Login\r\nUsername: $username\r\nSecret: $secret\r\nEvents: off";
$respuestaLogin = enviarComando($socket, $loginCmd);
if (strpos($respuestaLogin, 'Success') === false) {
    echo "❌ Login AMI falló:<br><pre>$respuestaLogin</pre>";
    fclose($socket);
    exit;
}

// Originate
$originateCmd = "Action: Originate\r\n"
    . "Channel: $canal\r\n"
    . "Exten: $numeroDestino\r\n"
    . "Context: from-internal\r\n"
    . "Priority: 1\r\n"
    . "Callerid: $callerid\r\n"
    . "Timeout: $timeout\r\n"
    . "Async: true";

$respuestaLlamada = enviarComando($socket, $originateCmd);
echo "✅ Llamada enviada:<br><pre>" . htmlspecialchars($respuestaLlamada) . "</pre>";

fclose($socket);
?>
