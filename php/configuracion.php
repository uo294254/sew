<?php
require_once("claseConfiguracion.php");

$config = new Configuracion();

if (isset($_POST["crear"])) {
    $config->crearBD();
} 

if (isset($_POST["reiniciar"])) {
    $config->reiniciarBD();
}   

if (isset($_POST["eliminar"])) {
    $config->eliminarBD();
}   

if (isset($_POST["exportar"])) {
    $config->exportarCSV();
}   

if (isset($_POST["importar"]) && isset($_FILES["csv"])) {
    $config->importarCSV($_FILES["csv"]["tmp_name"]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset='UTF-8' />
    <meta name='author' content='Mario Trelles' />
    <meta name='description' content='Clasificaciones MotoGP-Desktop' />
    <meta name='keywords' content='MotoGP, base, datos' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />

    <title>MotoGP - configuracion</title>
    <link rel='stylesheet' href='../estilo/estilo.css'>
    <link rel='stylesheet' href='../estilo/layout.css'>
</head>
<body>

<h1>Configuración del Test de Usabilidad</h1>

<form action="#" method="post">
    <button type="submit" name="crear" value="crear">Crear base de datos</button>
    <button type="submit" name="reiniciar" value="reiniciar">Reiniciar base de datos</button>
    <button type="submit" name="eliminar" value="eliminar">Eliminar base de datos</button>
    <button type="submit" name="exportar" value="exportar">Exportar datos en formato .csv</button>
</form>

<h2>Importar .csv a la base de datos:</h2>
<form action="#" method="post" enctype="multipart/form-data">
    <label for="csv">
        Importar datos desde CSV:
    </label>
    <input type="file" name="csv" id="csv" accept=".csv">
    <button type="submit" name="importar" value="importar">Importar CSV</button>
</form>

</body>
</html>