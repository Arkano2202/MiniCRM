<!--PYTHON-->
<!--?php
if (isset($_GET['numero']) && isset($_GET['ext'])) {
    $numero = escapeshellarg($_GET['numero']); 
    $ext = escapeshellarg($_GET['ext']); 

    // Construir el comando para ejecutar el script Python
    $cmd = "python llamada.php $numero $ext";

    // Ejecutar el comando
    $output = shell_exec($cmd);

    echo "Llamada iniciada.";
} else {
    echo "Número o extensión no proporcionados.";
}
?-->

<!--PHP-->
<?php
$_GET['numero'] = $_GET['numero'] ?? '';
$_GET['extension'] = $_GET['ext'] ?? '';

// Incluir el script directamente
include 'llamada.php';
?>
