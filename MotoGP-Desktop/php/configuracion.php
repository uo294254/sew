<?php
require_once("claseConfiguracion.php");

$config = new Configuracion();

if (isset($_POST["accion"])) {
    if ($_POST["accion"] == "reiniciar") $config->reiniciarBD();
    if ($_POST["accion"] == "eliminar") $config->eliminarBD();
    if ($_POST["accion"] == "exportar") $config->exportarCSV();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset='UTF-8' />
    <meta name='author' content='Mario Trelles' />
    <meta name='description' content='Clasificaciones MotoGP-Desktop' />
    <meta name='keywords' content='MotoGP, Clasificación' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />

    <title>MotoGP - configuracion</title>
    <link rel='stylesheet' href='../estilo/estilo.css'>
    <link rel='stylesheet' href='../estilo/layout.css'>
</head>
<body>

<h1>Configuración del Test de Usabilidad</h1>

<form method="POST">
    <button name="accion" value="reiniciar">Reiniciar base de datos</button>
    <button name="accion" value="eliminar">Eliminar base de datos</button>
    <button name="accion" value="exportar">Exportar datos en formato .csv</button>
</form>

</body>
</html>