<?php
set_time_limit(0);
include 'conexion.php'; // asegúrate que esta ruta sea correcta

$tiempo_inicio = microtime(true); // Marca el inicio

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo']['tmp_name'];

    if (($handle = fopen($archivo, "r")) !== false) {
        // Saltamos la primera línea (encabezados)
        //fgetcsv($handle, 1000, ";");

        $insertados = 0;

        while (($datos = fgetcsv($handle, 1000, ";")) !== false) {
            if (count($datos) >= 11) {
                $nombre           = mysqli_real_escape_string($conn, $datos[0]);
                $apellido         = mysqli_real_escape_string($conn, $datos[1]);
                $correo           = mysqli_real_escape_string($conn, $datos[2]);
                $numero           = mysqli_real_escape_string($conn, $datos[3]);
                $pais             = mysqli_real_escape_string($conn, $datos[4]);
                $tp               = mysqli_real_escape_string($conn, $datos[5]);
                $campaña          = mysqli_real_escape_string($conn, $datos[6]);
                $grupo            = '';
                $asignado         = 'AdminCRM';
                $fechacreacion    = mysqli_real_escape_string($conn, $datos[9]);
                $fechaasignacion  = mysqli_real_escape_string($conn, $datos[10]);
                $estado           = mysqli_real_escape_string($conn, $datos[11]);

                $query = "INSERT INTO clientes
                VALUES 
                (null,'$nombre', '$apellido', '$correo', '$numero', '$pais', '$tp', '$campaña', '$grupo', '$asignado', CURDATE(), '$fechaasignacion', '$estado')";

                if (mysqli_query($conn, $query)) {
                    $insertados++;
                }
            }
        }

        fclose($handle);

        header("Location: dashboard.php");
        exit();
    } else {
        echo "No se pudo abrir el archivo.";
    }
} else {
    echo "No se recibió ningún archivo.";
}
?>
