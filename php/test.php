<?php
require_once("claseCronometro.php");
session_start();

$mysqli = new mysqli("localhost", "DBUSER2025", "DBPSWD2025", "UO294254_DB");
if ($mysqli->connect_error) die("Error de conexión: " . $mysqli->connect_error);

$errorFormulario = false;
$errores = [];
for ($i = 1; $i <= 10; $i++) {
    $errores["pregunta$i"] = "";
}
$errores["profesion"] = "";
$errores["edad"] = "";
$errores["genero"] = "";
$errores["pericia"] = "";
$errores["dispositivo"] = "";
$errores["comentarios"] = "";
$errores["mejoras"] = "";
$errores["valoracion"] = "";

$mostrarFormularioObservador = false;

if (!isset($_SESSION['cronometro'])) {
    $_SESSION['cronometro'] = new Cronometro();
}
$cronometro = $_SESSION['cronometro'];

if (isset($_POST['accion'])) {

    if ($_POST['accion'] == 'iniciar') {
        $cronometro->arrancar();
        $_SESSION['cronometro'] = $cronometro;
        $_SESSION['test_iniciado'] = true;
        echo "<p>Prueba iniciada</p>";
    }

    elseif ($_POST['accion'] == 'terminar') {

        if (!isset($_SESSION['test_iniciado']) || !$_SESSION['test_iniciado']) {
            echo "<p>Error: Debes iniciar la prueba primero</p>";
        } else {
            
            if (empty($_POST["profesion"])) {
                $errorFormulario = true;
                $errores["profesion"] = " * Este campo es obligatorio";
            }
            if (empty($_POST["edad"])) {
                $errorFormulario = true;
                $errores["edad"] = " * Debe ser un número";
            }
            if (empty($_POST["genero"])) {
                $errorFormulario = true;
                $errores["genero"] = " * Selecciona una opción";
            }
            if (empty($_POST["pericia"])) {
                $errorFormulario = true;
                $errores["pericia"] = " * Debe ser un número del 1 al 10";
            }
            if (empty($_POST["dispositivo"])) {
                $errorFormulario = true;
                $errores["dispositivo"] = " * Este campo es obligatorio";
            }

            for ($i = 1; $i <= 10; $i++) {
                if (empty($_POST["pregunta$i"])) {
                    $errorFormulario = true;
                    $errores["pregunta$i"] = " * Esta pregunta es obligatoria";
                }
            }

            if (empty($_POST["valoracion"]) || !is_numeric($_POST["valoracion"]) || $_POST["valoracion"] < 0 || $_POST["valoracion"] > 10) {
                $errorFormulario = true;
                $errores["valoracion"] = " * Debe ser un número del 0 al 10";
            }

            if (!$errorFormulario) {
                $cronometro->parar();
                $tiempo_formato = $cronometro->mostrar();

                $profesion = $_POST["profesion"];
                $edad = (int)$_POST["edad"];
                $genero = $_POST["genero"];
                $nivel = (int)$_POST["pericia"];
                $dispositivo = $_POST["dispositivo"];
                $comentarios = $_POST["comentarios"] ?? '';
                $mejoras = $_POST["mejoras"] ?? '';
                $valoracion = (int)$_POST["valoracion"];

                $stmt_user = $mysqli->prepare("INSERT INTO usuarios (profesion, edad, genero, nivel) VALUES (?, ?, ?, ?)");
                $stmt_user->bind_param("sisi", $profesion, $edad, $genero, $nivel);
                $stmt_user->execute();
                $id_usuario = $stmt_user->insert_id;

                $stmt = $mysqli->prepare("INSERT INTO tests (id_usuario, dispositivo, tiempo, completado, comentarios, mejoras, valoracion) VALUES (?, ?, ?, 1, ?, ?, ?)");
                $stmt->bind_param("issssi", $id_usuario, $dispositivo, $tiempo_formato, $comentarios, $mejoras, $valoracion);
                $stmt->execute();
                $id_test = $stmt->insert_id;

                $stmt_resp = $mysqli->prepare("INSERT INTO respuestas (id_test, pregunta, respuesta) VALUES (?, ?, ?)");
                for ($i = 1; $i <= 10; $i++) {
                    $pregunta_texto = "Pregunta $i"; 
                    $respuesta = $_POST["pregunta$i"];
                    $stmt_resp->bind_param("iss", $id_test, $pregunta_texto, $respuesta);
                    $stmt_resp->execute();
                }

                echo "<p>Prueba completada y guardada. Tiempo empleado: $tiempo_formato</p>";

                $_SESSION['cronometro'] = new Cronometro();
                $_SESSION['test_iniciado'] = false;
                $_SESSION['id_usuario_actual'] = $id_usuario;
                
                $mostrarFormularioObservador = true;
            } else {
                echo "<p>Faltan respuestas en el formulario</p>";
            }
        }
    }

    elseif ($_POST['accion'] == 'guardar_observacion') {
        $comentario = $_POST['comentario_observador'] ?? '';
        $id_usuario = $_SESSION['id_usuario_actual'] ?? 1;
        
        if (!empty($comentario)) {
            $stmt_obs = $mysqli->prepare("INSERT INTO observaciones (id_usuario, comentario) VALUES (?, ?)");
            $stmt_obs->bind_param("is", $id_usuario, $comentario);
            $stmt_obs->execute();
            echo "<p>Comentario del observador guardado correctamente.</p>";
        } else {
            echo "<p>No se ha escrito ningún comentario.</p>";
        }
    }
}

if (!$mostrarFormularioObservador) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8' />
        <meta name='author' content='Mario Trelles' />
        <meta name='description' content='Test de Usabilidad MotoGP-Desktop' />
        <meta name='keywords' content='MotoGP, Test' />
        <meta name='viewport' content='width=device-width, initial-scale=1.0' />

        <title>MotoGP - Test de Usabilidad</title>
        <link rel='stylesheet' href='../estilo/estilo.css'>
        <link rel='stylesheet' href='../estilo/layout.css'>
    </head>
    <body>

    <h1>Test de Usabilidad</h1>

    <form action='#' method='post' name='formulario'>

        <h2>Datos del participante</h2>

        <label for='profesion'>Profesión:</label>
        <p>
            <input type='text' id='profesion' name='profesion' 
                value='" . ($_POST['profesion'] ?? '') . "'/>
            <span>" . $errores["profesion"] . "</span>
        </p>

        <label for='edad'>Edad:</label>
        <p>
            <input type='number' id='edad' name='edad'
                value='" . ($_POST['edad'] ?? '') . "'/>
            <span>" . $errores["edad"] . "</span>
        </p>

        <label>Género:</label>
        <p>
            <input type='radio' id='generoH' name='genero' value='Hombre' " . (isset($_POST['genero']) && $_POST['genero'] == 'Hombre' ? 'checked' : '') . "/> 
            <label for='generoH'>Hombre</label>

            <input type='radio' id='generoM' name='genero' value='Mujer' " . (isset($_POST['genero']) && $_POST['genero'] == 'Mujer' ? 'checked' : '') . "/> 
            <label for='generoM'>Mujer</label>

            <input type='radio' id='generoO' name='genero' value='Otros' " . (isset($_POST['genero']) && $_POST['genero'] == 'Otros' ? 'checked' : '') . "/> 
            <label for='generoO'>Otros</label>

            <span>" . $errores["genero"] . "</span>
        </p>

        <label for='pericia'>Pericia informática (1-10):</label>
        <p>
            <input type='number' id='pericia' name='pericia' min='1' max='10'
                value='" . ($_POST['pericia'] ?? '') . "'/>
            <span>" . $errores["pericia"] . "</span>
        </p>

        <label for='dispositivo'>Dispositivo:</label>
        <p>
            <input type='text' id='dispositivo' name='dispositivo' 
                value='" . ($_POST['dispositivo'] ?? '') . "'/>
            <span>" . $errores["dispositivo"] . "</span>
        </p>

        <p>
            <button type='submit' name='accion' value='iniciar'>Iniciar Prueba</button>
        </p>


        <h2>Preguntas</h2>

        <label>Pregunta 1: ¿En qué año nació Álex Márquez?</label>
        <p>
            <input type='radio' id='p1_1996' name='pregunta1' value='1996' " . (isset($_POST['pregunta1']) && $_POST['pregunta1'] == '1996' ? 'checked' : '') . "/>
            <label for='p1_1996'>1996</label>

            <input type='radio' id='p1_1998' name='pregunta1' value='1998' " . (isset($_POST['pregunta1']) && $_POST['pregunta1'] == '1998' ? 'checked' : '') . "/>
            <label for='p1_1998'>1998</label>

            <input type='radio' id='p1_1999' name='pregunta1' value='1999' " . (isset($_POST['pregunta1']) && $_POST['pregunta1'] == '1999' ? 'checked' : '') . "/>
            <label for='p1_1999'>1999</label>

            <span>" . $errores["pregunta1"] . "</span>
        </p>

        <label>Pregunta 2: ¿Cuántos metros tiene el Circuito de Jerez?</label>
        <p>
            <input type='radio' id='p2_4423' name='pregunta2' value='4423' " . (isset($_POST['pregunta2']) && $_POST['pregunta2'] == '4423' ? 'checked' : '') . "/>
            <label for='p2_4423'>4423 metros</label>

            <input type='radio' id='p2_4657' name='pregunta2' value='4657' " . (isset($_POST['pregunta2']) && $_POST['pregunta2'] == '4657' ? 'checked' : '') . "/>
            <label for='p2_4657'>4657 metros</label>

            <input type='radio' id='p2_5100' name='pregunta2' value='5100' " . (isset($_POST['pregunta2']) && $_POST['pregunta2'] == '5100' ? 'checked' : '') . "/>
            <label for='p2_5100'>5100 metros</label>

            <span>" . $errores["pregunta2"] . "</span>
        </p>

        <label>Pregunta 3: ¿Qué sensación térmica había a las 14:00 el día de la carrera?</label>
        <p>
            <input type='radio' id='p3_20' name='pregunta3' value='20' " . (isset($_POST['pregunta3']) && $_POST['pregunta3'] == '20' ? 'checked' : '') . "/>
            <label for='p3_20'>20°C</label>

            <input type='radio' id='p3_25' name='pregunta3' value='25' " . (isset($_POST['pregunta3']) && $_POST['pregunta3'] == '25' ? 'checked' : '') . "/>
            <label for='p3_25'>25°C</label>

            <input type='radio' id='p3_30' name='pregunta3' value='30' " . (isset($_POST['pregunta3']) && $_POST['pregunta3'] == '30' ? 'checked' : '') . "/>
            <label for='p3_30'>30°C</label>

            <span>" . $errores["pregunta3"] . "</span>
        </p>

        <label>Pregunta 4: ¿Quién va primero en la clasificación del mundial?</label>
        <p>
            <input type='radio' id='p4_mm' name='pregunta4' value='Marc Márquez' " . (isset($_POST['pregunta4']) && $_POST['pregunta4'] == 'Marc Márquez' ? 'checked' : '') . "/>
            <label for='p4_mm'>Marc Márquez</label>

            <input type='radio' id='p4_jm' name='pregunta4' value='Jorge Martín' " . (isset($_POST['pregunta4']) && $_POST['pregunta4'] == 'Jorge Martín' ? 'checked' : '') . "/>
            <label for='p4_jm'>Jorge Martín</label>

            <input type='radio' id='p4_pb' name='pregunta4' value='Pecco Bagnaia' " . (isset($_POST['pregunta4']) && $_POST['pregunta4'] == 'Pecco Bagnaia' ? 'checked' : '') . "/>
            <label for='p4_pb'>Pecco Bagnaia</label>

            <span>" . $errores["pregunta4"] . "</span>
        </p>

        <label>Pregunta 5: ¿De que va el juego de cartas de MotoGP-Desktop?</label>
        <p>
            <input type='radio' id='p5_5' name='pregunta5' value='Estrategia' " . (isset($_POST['pregunta5']) && $_POST['pregunta5'] == 'Estrategia' ? 'checked' : '') . "/>
            <label for='p5_5'>De estrategia</label>

            <input type='radio' id='p5_6' name='pregunta5' value='Memoria' " . (isset($_POST['pregunta5']) && $_POST['pregunta5'] == 'Memoria' ? 'checked' : '') . "/>
            <label for='p5_6'>De memoria</label>

            <input type='radio' id='p5_7' name='pregunta5' value='Rol' " . (isset($_POST['pregunta5']) && $_POST['pregunta5'] == 'Rol' ? 'checked' : '') . "/>
            <label for='p5_7'>De rol</label>

            <span>" . $errores["pregunta5"] . "</span>
        </p>

        <label>Pregunta 6: ¿En qué equipo está Álex Márquez?</label>
        <p>
            <input type='radio' id='p6_gresini' name='pregunta6' value='Gresini Racing Ducati' " . (isset($_POST['pregunta6']) && $_POST['pregunta6'] == 'Gresini Racing Ducati' ? 'checked' : '') . "/>
            <label for='p6_gresini'>Gresini Racing Ducati</label>

            <input type='radio' id='p6_pramac' name='pregunta6' value='Aprilia Racing' " . (isset($_POST['pregunta6']) && $_POST['pregunta6'] == 'Aprilia Racing' ? 'checked' : '') . "/>
            <label for='p6_pramac'>Aprilia Racing</label>

            <input type='radio' id='p6_lcr' name='pregunta6' value='Monster Energy Yamaha' " . (isset($_POST['pregunta6']) && $_POST['pregunta6'] == 'Monster Energy Yamaha' ? 'checked' : '') . "/>
            <label for='p6_lcr'>Monster Energy Yamaha</label>

            <span>" . $errores["pregunta6"] . "</span>
        </p>

        <label>Pregunta 7: ¿Qué es la chicane?</label>
        <p>
            <input type='radio' id='p7_curvas' name='pregunta7' value='Serie de curvas pronunciadas' " . (isset($_POST['pregunta7']) && $_POST['pregunta7'] == 'Serie de curvas pronunciadas' ? 'checked' : '') . "/>
            <label for='p7_curvas'>Serie de curvas pronunciadas</label>

            <input type='radio' id='p7_recta' name='pregunta7' value='Recta de boxes' " . (isset($_POST['pregunta7']) && $_POST['pregunta7'] == 'Recta de boxes' ? 'checked' : '') . "/>
            <label for='p7_recta'>Recta de boxes</label>

            <input type='radio' id='p7_adel' name='pregunta7' value='Zona de adelantamiento' " . (isset($_POST['pregunta7']) && $_POST['pregunta7'] == 'Zona de adelantamiento' ? 'checked' : '') . "/>
            <label for='p7_adel'>Zona de adelantamiento</label>

            <span>" . $errores["pregunta7"] . "</span>
        </p>

        <label>Pregunta 8: ¿Cuántos podios tiene Álex Márquez?</label>
        <p>
            <input type='radio' id='p8_1' name='pregunta8' value='1' " . (isset($_POST['pregunta8']) && $_POST['pregunta8'] == '1' ? 'checked' : '') . "/>
            <label for='p8_1'>1</label>

            <input type='radio' id='p8_3' name='pregunta8' value='3' " . (isset($_POST['pregunta8']) && $_POST['pregunta8'] == '3' ? 'checked' : '') . "/>
            <label for='p8_3'>3</label>

            <input type='radio' id='p8_5' name='pregunta8' value='5' " . (isset($_POST['pregunta8']) && $_POST['pregunta8'] == '5' ? 'checked' : '') . "/>
            <label for='p8_5'>5</label>

            <span>" . $errores["pregunta8"] . "</span>
        </p>

        <label>Pregunta 9: ¿Llovió durante los entrenamientos?</label>
        <p>
            <input type='radio' id='p9_si' name='pregunta9' value='Sí' " . (isset($_POST['pregunta9']) && $_POST['pregunta9'] == 'Sí' ? 'checked' : '') . "/>
            <label for='p9_si'>Si</label>

            <input type='radio' id='p9_no' name='pregunta9' value='No' " . (isset($_POST['pregunta9']) && $_POST['pregunta9'] == 'No' ? 'checked' : '') . "/>
            <label for='p9_no'>No</label>

            <input type='radio' id='p9_sc' name='pregunta9' value='Durante una hora' " . (isset($_POST['pregunta9']) && $_POST['pregunta9'] == 'Durante una hora' ? 'checked' : '') . "/>
            <label for='p9_sc'>Durante una hora</label>

            <span>" . $errores["pregunta9"] . "</span>
        </p>

        <label>Pregunta 10: ¿Quién es el patrocinador principal del circuito de Jerez?</label>
        <p>
            <input type='radio' id='p10_eg' name='pregunta10' value='Estrella Galicia' " . (isset($_POST['pregunta10']) && $_POST['pregunta10'] == 'Estrella Galicia' ? 'checked' : '') . "/>
            <label for='p10_eg'>Estrella Galicia</label>

            <input type='radio' id='p10_rb' name='pregunta10' value='Red Bull' " . (isset($_POST['pregunta10']) && $_POST['pregunta10'] == 'Red Bull' ? 'checked' : '') . "/>
            <label for='p10_rb'>Red Bull</label>

            <input type='radio' id='p10_me' name='pregunta10' value='Monster Energy' " . (isset($_POST['pregunta10']) && $_POST['pregunta10'] == 'Monster Energy' ? 'checked' : '') . "/>
            <label for='p10_me'>Monster Energy</label>

            <span>" . $errores["pregunta10"] . "</span>
        </p>


        <h2>Evaluación de la aplicación</h2>

        <label for='comentarios'>Comentarios (opcional):</label>
        <p>
            <textarea id='comentarios' name='comentarios' rows='4' cols='50'>" . ($_POST['comentarios'] ?? '') . "</textarea>
            <span>" . $errores["comentarios"] . "</span>
        </p>

        <label for='mejoras'>Propuestas de mejora (opcional):</label>
        <p>
            <textarea id='mejoras' name='mejoras' rows='4' cols='50'>" . ($_POST['mejoras'] ?? '') . "</textarea>
            <span>" . $errores["mejoras"] . "</span>
        </p>

        <label for='valoracion'>Valoración (0-10):</label>
        <p>
            <input type='number' id='valoracion' name='valoracion' min='0' max='10'
                value='" . ($_POST['valoracion'] ?? '') . "'/>
            <span>" . $errores["valoracion"] . "</span>
        </p>

        <p>
            <button type='submit' name='accion' value='terminar'>Terminar Prueba</button>
        </p>
    </form>
    </body>
    </html>";
} else {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8' />
        <title>Comentarios del Observador</title>
        <link rel='stylesheet' href='../estilo/estilo.css'>
        <link rel='stylesheet' href='../estilo/layout.css'>
    </head>
    <body>
    <h1>Comentarios del Observador</h1>
    <form action='#' method='post'>
        <p>Comentario del observador:</p>
        <p>
            <textarea name='comentario_observador' rows='5' cols='50'></textarea>
        </p>
        <p>
            <input type='hidden' name='accion' value='guardar_observacion'/>
            <input type='submit' value='Guardar comentario'/>
        </p>
    </form>
    </body>
    </html>";
}

$mysqli->close();
?>