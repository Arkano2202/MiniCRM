<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Clientes</title>
</head>
<body>
    <h2>Cargar archivo CSV de clientes</h2>
    <form action="procesar_carga.php" method="post" enctype="multipart/form-data">
        <input type="file" name="archivo" accept=".csv" required>
        <button type="submit" name="submit">Cargar</button>
    </form>
</body>
</html>
