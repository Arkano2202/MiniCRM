<?php
if (isset($_GET['numero'])) {
    $numero = escapeshellarg($_GET['numero']); 
    shell_exec("python llamada.py $numero");
} else {
    echo "Número no proporcionado.";
}
?>
