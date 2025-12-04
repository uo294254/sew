<?php
include_once "php/claseCronometro.php";
session_start();

if (!isset($_SESSION['cronometro'])) {
    $_SESSION['cronometro'] = new Cronometro();
}
$cronometro = $_SESSION['cronometro'];
$tiempoMostrado = "00:00.0";

if (count($_POST) > 0) {
    if (isset($_POST['arrancar'])) { $cronometro->arrancar(); }
    if (isset($_POST['parar'])) { $cronometro->parar(); }
    if (isset($_POST['mostrar'])) { $tiempoMostrado = $cronometro->mostrar(); }
    $_SESSION['cronometro'] = $cronometro;
}

echo "
<!DOCTYPE HTML>
<html lang='es'>
<head>
    <meta charset='UTF-8' />
    <meta name='author' content='Mario Trelles' />
    <meta name='description' content='Clasificaciones MotoGP-Desktop' />
    <meta name='keywords' content='MotoGP, Clasificación' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />

    <title>MotoGP - Clasificaciones</title>
    <link rel='icon' href='multimedia/favicon.ico'>
    <link rel='stylesheet' href='estilo/estilo.css'>
    <link rel='stylesheet' href='estilo/layout.css'>
</head>

<body>
<header>
    <h1><a href='index.html'>MotoGP Desktop</a></h1>
    <nav>
        <a href='index.html'  title='Indice'>Indice</a>
        <a href='piloto.html' title='Información del piloto'>Piloto</a>
        <a href='circuito.html'  title='Información del circuito'>Circuito</a>
        <a href='meteorologia.html'  title='Información de la meteorologia'>Meteorologia</a>
        <a href='clasificaciones.php'  title='Información de la clasificacion'>Clasificaciones</a>
        <a class='active' href='juegos.html'  title='Información de los juegos'>Juegos</a>
        <a href='ayuda.html'  title='Información de la ayuda'>Ayuda</a>
    </nav>
</header>

<p>
    Estás en: <a href='index.html'>Inicio</a> || Cronometro
</p>

<main>
    <h2>Control del Cronómetro</h2>
    <form action='#' method='post'>
        <section>
            <input type='submit' class='button' name='arrancar' value='Arrancar'/>
            <input type='submit' class='button' name='parar' value='Parar'/>
            <input type='submit' class='button' name='mostrar' value='Mostrar'/>
            <p>Tiempo: $tiempoMostrado</p>
        </section>
    </form>
</main>
</body>
</html>
";

?>