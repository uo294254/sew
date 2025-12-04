<?php
class Clasificacion {

    public $documento;
    public $xml;

    public function __construct() {
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {

        $datos = file_get_contents($this->documento);
        if ($datos == null) {
            echo "<h3>Error en el archivo XML recibido</h3>";
            return;
        }

        $this->xml = new SimpleXMLElement($datos);
        return $this->xml;
    }
}

$clasificacion = new Clasificacion();
$xml = $clasificacion->consultar();

$ganador = (string)$xml->resultado->vencedor;
$tiempo  = (string)$xml->resultado->tiempo;

$piloto1 = (string)$xml->clasificacion->piloto1;
$piloto2 = (string)$xml->clasificacion->piloto2;
$piloto3 = (string)$xml->clasificacion->piloto3;

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
            <a class='active' href='clasificaciones.php'  title='Información de la clasificacion'>Clasificaciones</a>
            <a href='juegos.html'  title='Información de los juegos'>Juegos</a>
            <a href='ayuda.html'  title='Información de la ayuda'>Ayuda</a>
    </nav>
</header>

<p>Estás en: <a href='index.html'>Inicio</a> || Clasificaciones</p>

<main>
    
    <h2>Ganador de la carrera</h2>
    <p>Piloto: $ganador</p>
    <p>Tiempo: $tiempo</p>

    <h2>Clasificación del Mundial</h2>
    <ol>
        <li>$piloto1</li>
        <li>$piloto2</li>
        <li>$piloto3</li>
    </ol>
</main>

</body>
</html>
";
?>

