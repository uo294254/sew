<?php
require_once("claseCronometro.php");
session_start();

$mysqli = new mysqli("localhost", "DBUSER2025", "DBPSWD2025", "UO294254_DB");
if ($mysqli->connect_error) die("Error de conexión: " . $mysqli->connect_error);

$errorFormulario = false;
$errorFormulario = false;
$errores = [];
for ($i = 1; $i <= 10; $i++) {
    $errores["pregunta$i"] = "";
}
$errores["profesion"] = "";
$errores["edad"] = "";
$errores["genero"] = "";
$errores["pericia"] = "";

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
            if (empty($_POST["edad"]) || !is_numeric($_POST["edad"])) {
                $errorFormulario = true;
                $errores["edad"] = " * Debe ser un número";
            }
            if (empty($_POST["genero"])) {
                $errorFormulario = true;
                $errores["genero"] = " * Selecciona una opción";
            }
            if (empty($_POST["pericia"]) || !is_numeric($_POST["pericia"]) || $_POST["pericia"] < 1 || $_POST["pericia"] > 10) {
                $errorFormulario = true;
                $errores["pericia"] = " * Debe ser un número del 1 al 10";
            }

            for ($i = 1; $i <= 10; $i++) {
                if (empty($_POST["pregunta$i"])) {
                    $errorFormulario = true;
                    $errores["pregunta$i"] = " * Esta pregunta es obligatoria";
                }
            }

            if (!$errorFormulario) {
                $cronometro->parar();
                $tiempo_formato = $cronometro->mostrar();

                $profesion = $_POST["profesion"];
                $edad = (int)$_POST["edad"];
                $genero = $_POST["genero"];
                $pericia = (int)$_POST["pericia"];
                $dispositivo = $_POST["dispositivo"];

                $stmt_prof = $mysqli->prepare("INSERT INTO profesiones (profesion) VALUES (?)");
                $stmt_prof->bind_param("s", $profesion);
                $stmt_prof->execute();
                $id_profesion = $mysqli->insert_id;
                if ($id_profesion == 0) {
                    $result = $mysqli->query("SELECT id_profesion FROM profesiones WHERE profesion='$profesion'");
                    $id_profesion = $result->fetch_assoc()['id_profesion'];
                }

                $stmt_gen = $mysqli->prepare("INSERT INTO generos (genero) VALUES (?)");
                $stmt_gen->bind_param("s", $genero);
                $stmt_gen->execute();
                $id_genero = $mysqli->insert_id;
                if ($id_genero == 0) {
                    $result = $mysqli->query("SELECT id_genero FROM generos WHERE genero='$genero'");
                    $id_genero = $result->fetch_assoc()['id_genero'];
                }

                $stmt_per = $mysqli->prepare("INSERT INTO pericias (nivel) VALUES (?)");
                $pericia_str = "Nivel " . $pericia;
                $stmt_per->bind_param("s", $pericia_str);
                $stmt_per->execute();
                $id_pericia = $mysqli->insert_id;
                if ($id_pericia == 0) {
                    $result = $mysqli->query("SELECT id_pericia FROM pericias WHERE nivel='$pericia_str'");
                    $id_pericia = $result->fetch_assoc()['id_pericia'];
                }

                $stmt_disp = $mysqli->prepare("INSERT INTO dispositivos (dispositivo) VALUES (?)");
                $stmt_disp->bind_param("s", $dispositivo);
                $stmt_disp->execute();
                $id_dispositivo = $mysqli->insert_id;
                if ($id_dispositivo == 0) {
                    $result = $mysqli->query("SELECT id_dispositivo FROM dispositivos WHERE dispositivo='$dispositivo'");
                    $id_dispositivo = $result->fetch_assoc()['id_dispositivo'];
                }

                $stmt_user = $mysqli->prepare("INSERT INTO usuarios (id_profesion, edad, id_genero, id_pericia) VALUES (?, ?, ?, ?)");
                $stmt_user->bind_param("iiii", $id_profesion, $edad, $id_genero, $id_pericia);
                $stmt_user->execute();
                $id_usuario = $stmt_user->insert_id;

                $stmt = $mysqli->prepare("INSERT INTO tests (id_usuario, id_dispositivo, tiempo, completado, comentarios, mejoras, valoracion) VALUES (?, ?, ?, 1, '', '', 0)");
                $stmt->bind_param("iis", $id_usuario, $id_dispositivo, $tiempo_formato);
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
        <p>
            <button type='submit' name='accion' value='iniciar'>Iniciar Prueba</button>
        </p>

        <h2>Datos del participante</h2>

        <p>Profesión:</p>
        <p>
            <input type='text' name='profesion' value='" . ($_POST['profesion'] ?? '') . "'/>
            <span style='color:red'>" . $errores["profesion"] . "</span>
        </p>

        <p>Edad:</p>
        <p>
            <input type='number' name='edad' value='" . ($_POST['edad'] ?? '') . "'/>
            <span style='color:red'>" . $errores["edad"] . "</span>
        </p>

        <p>Género:</p>
        <p>
            <input type='radio' name='genero' value='Hombre' " . (isset($_POST['genero']) && $_POST['genero'] == 'Hombre' ? 'checked' : '') . "/>Hombre
            <input type='radio' name='genero' value='Mujer' " . (isset($_POST['genero']) && $_POST['genero'] == 'Mujer' ? 'checked' : '') . "/>Mujer
            <input type='radio' name='genero' value='Otros' " . (isset($_POST['genero']) && $_POST['genero'] == 'Otros' ? 'checked' : '') . "/>Otros
            <span style='color:red'>" . $errores["genero"] . "</span>             
        </p>

        <p>Pericia informática (1-10):</p>
        <p>
            <input type='number' name='pericia' min='1' max='10' value='" . ($_POST['pericia'] ?? '') . "'/>
            <span style='color:red'>" . $errores["pericia"] . "</span>
        </p>

        <p>Dispositivo:</p>
        <p>
            <input type='radio' name='dispositivo' value='Ordenador' " . (isset($_POST['dispositivo']) && $_POST['dispositivo'] == 'Ordenador' ? 'checked' : '') . "/>Ordenador
            <input type='radio' name='dispositivo' value='Tableta' " . (isset($_POST['dispositivo']) && $_POST['dispositivo'] == 'Tableta' ? 'checked' : '') . "/>Tableta
            <input type='radio' name='dispositivo' value='Móvil' " . (isset($_POST['dispositivo']) && $_POST['dispositivo'] == 'Móvil' ? 'checked' : '') . "/>Móvil
        </p>

        <h2>Preguntas</h2>

        <p>Pregunta 1: ¿En qué año nació Álex Márquez?</p>
        <p>
            <input type='radio' name='pregunta1' value='1996' />1996
            <input type='radio' name='pregunta1' value='1998' />1998
            <input type='radio' name='pregunta1' value='1999' />1999
            <span style='color:red'>" . $errores["pregunta1"] . "</span>
        </p>

        <p>Pregunta 2: ¿Cuántos metros de longitud tiene el Circuito de Jerez?</p>
        <p>
            <input type='radio' name='pregunta2' value='4423' />4,423 metros
            <input type='radio' name='pregunta2' value='4657' />4,657 metros
            <input type='radio' name='pregunta2' value='5100' />5,100 metros
            <span style='color:red'>" . $errores["pregunta2"] . "</span>
        </p>

        <p>Pregunta 3: ¿Qué temperatura había el día de la carrera a las 14:00?</p>
        <p>
            <input type='radio' name='pregunta3' value='20' />20°C
            <input type='radio' name='pregunta3' value='25' />25°C
            <input type='radio' name='pregunta3' value='30' />30°C
            <span style='color:red'>" . $errores["pregunta3"] . "</span>
        </p>

        <p>Pregunta 4: ¿Quién está en la primera posición de la clasificación general?</p>
        <p>
            <input type='radio' name='pregunta4' value='Marc Márquez' />Marc Márquez
            <input type='radio' name='pregunta4' value='Jorge Martín' />Jorge Martín
            <input type='radio' name='pregunta4' value='Pecco Bagnaia' />Pecco Bagnaia
            <span style='color:red'>" . $errores["pregunta4"] . "</span>
        </p>

        <p>Pregunta 5: ¿Cuántas secciones principales tiene el menú de navegación?</p>
        <p>
            <input type='radio' name='pregunta5' value='5' />5
            <input type='radio' name='pregunta5' value='6' />6
            <input type='radio' name='pregunta5' value='7' />7
            <span style='color:red'>" . $errores["pregunta5"] . "</span>
        </p>

        <p>Pregunta 6: ¿En qué equipo está Álex Márquez actualmente?</p>
        <p>
            <input type='radio' name='pregunta6' value='Gresini Racing Ducati' />Gresini Racing Ducati
            <input type='radio' name='pregunta6' value='Pramac Racing' />Pramac Racing
            <input type='radio' name='pregunta6' value='Honda LCR' />Honda LCR
            <span style='color:red'>" . $errores["pregunta6"] . "</span>
        </p>

        <p>Pregunta 7: ¿Qué es la chicane?</p>
        <p>
            <input type='radio' name='pregunta7' value='Serie de curvas pronunciadas' />Serie de curvas pronunciadas
            <input type='radio' name='pregunta7' value='Recta de boxes' />Recta de boxes
            <input type='radio' name='pregunta7' value='Zona de adelantamiento' />Zona de adelantamiento
            <span style='color:red'>" . $errores["pregunta7"] . "</span>
        </p>

        <p>Pregunta 8: ¿Cuántos podios ha obtenido Álex Márquez?</p>
        <p>
            <input type='radio' name='pregunta8' value='1' />1
            <input type='radio' name='pregunta8' value='3' />3
            <input type='radio' name='pregunta8' value='5' />5
            <span style='color:red'>" . $errores["pregunta8"] . "</span>
        </p>

        <p>Pregunta 9: ¿Llovió durante los entrenamientos?</p>
        <p>
            <input type='radio' name='pregunta9' value='Sí' />Sí
            <input type='radio' name='pregunta9' value='No' />No
            <input type='radio' name='pregunta9' value='Solo en clasificación' />Solo en clasificación
            <span style='color:red'>" . $errores["pregunta9"] . "</span>
        </p>

        <p>Pregunta 10: ¿Quién es el patrocinador principal del circuito?</p>
        <p>
            <input type='radio' name='pregunta10' value='Estrella Galicia' />Estrella Galicia
            <input type='radio' name='pregunta10' value='Red Bull' />Red Bull
            <input type='radio' name='pregunta10' value='Monster Energy' />Monster Energy
            <span style='color:red'>" . $errores["pregunta10"] . "</span>
        </p>

        <p>
            <button type='submit' name='accion' value='terminar'>Terminar Prueba</button>
        </p>              
    </form>

    </body>
    </html>
    ";
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