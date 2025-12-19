<?php
class Configuracion {

    private $conexion;
    private $database = "UO294254_DB";

    public function __construct() {
        $this->conexion = new mysqli("localhost", "DBUSER2025", "DBPSWD2025");

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        
        $this->conexion->set_charset("utf8mb4");
    }

    public function crearBD() {
        $sql = "CREATE DATABASE IF NOT EXISTS {$this->database}
                CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

        if (!$this->conexion->query($sql)) {
            echo "<p>Error creando la BD: {$this->conexion->error}</p>";
            return;
        }

        $this->conexion->select_db($this->database);

        if (!file_exists("UO294254_DB.sql")) {
            echo "<p>Error: no se encuentra UO294254_DB.sql</p>";
            return;
        }

        $script = file("UO294254_DB.sql");
        $query = "";

        foreach ($script as $linea) {
            $linea = trim($linea);
            if ($linea === "" || str_starts_with($linea, "--")) continue;

            $query .= $linea . " ";

            if (str_ends_with($linea, ";")) {
                if (!$this->conexion->query($query)) {
                    echo "<p>Error ejecutando: $query<br>{$this->conexion->error}</p>";
                }
                $query = "";
            }
        }

        echo "<p>Base de datos creada correctamente</p>";
    }

    public function reiniciarBD() {
        $this->conexion->select_db($this->database);
        
        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 0");

        $tablas = ["respuestas", "observaciones", "tests", "usuarios"];
        foreach ($tablas as $tabla) {
            $this->conexion->query("TRUNCATE TABLE $tabla");
        }

        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 1");

        echo "<p>Base de datos reiniciada correctamente</p>";
    }
    
    public function eliminarBD() {
        if ($this->conexion->query("DROP DATABASE IF EXISTS {$this->database}")) {
            echo "<p>Base de datos eliminada correctamente</p>";
        } else {
            echo "<p>Error eliminando BD: {$this->conexion->error}</p>";
        }
    }

    public function exportarCSV() {
        $this->conexion->select_db($this->database);
        
        $sqlUsuarios = "
            SELECT u.*, 
                   t.dispositivo, t.tiempo, t.completado, 
                   t.comentarios, t.mejoras, t.valoracion,
                   o.comentario AS obs_facilitador
            FROM usuarios u
            LEFT JOIN tests t ON u.id_usuario = t.id_usuario
            LEFT JOIN observaciones o ON u.id_usuario = o.id_usuario
        ";

        $sqlRespuestas = "
            SELECT r.id_respuesta, r.id_test, r.pregunta, r.respuesta
            FROM respuestas r
            ORDER BY r.id_test, r.id_respuesta
        ";

        $resU = $this->conexion->query($sqlUsuarios);
        $resR = $this->conexion->query($sqlRespuestas);

        if (!$resU || !$resR) {
            echo "<p>Error exportando datos</p>";
            return;
        }

        $nombre = "respuestas_" . date("Ymd_Hi") . ".csv";
        $fp = fopen($nombre, "w");

        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($fp, array_keys($resU->fetch_assoc()));
        $resU->data_seek(0);

        while ($fila = $resU->fetch_assoc()) {
            fputcsv($fp, $fila);
        }

        fputcsv($fp, []);

        fputcsv($fp, array_keys($resR->fetch_assoc()));
        $resR->data_seek(0);

        while ($fila = $resR->fetch_assoc()) {
            fputcsv($fp, $fila);
        }

        fclose($fp);

        echo "<p>CSV generado: <a href='$nombre' download>$nombre</a></p>";
    }

    public function importarCSV($rutaCSV) {
        if (!file_exists($rutaCSV)) {
            echo "<p>Error: El archivo no existe</p>";
            return;
        }

        $this->conexion->select_db($this->database);

        $fp = fopen($rutaCSV, "r");
        if (!$fp) {
            echo "<p>Error al abrir el archivo CSV</p>";
            return;
        }

       $this->conexion->query("SET FOREIGN_KEY_CHECKS = 0");

        $stmtUsuario = $this->conexion->prepare(
            "INSERT INTO usuarios (id_usuario, profesion, edad, genero, nivel)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmtTest = $this->conexion->prepare(
            "INSERT INTO tests (id_usuario, dispositivo, tiempo, completado, comentarios, mejoras, valoracion)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmtObs = $this->conexion->prepare(
            "INSERT INTO observaciones (id_usuario, comentario)
            VALUES (?, ?)"
        );

        $stmtResp = $this->conexion->prepare(
            "INSERT INTO respuestas (id_test, pregunta, respuesta)
            VALUES (?, ?, ?)"
        );

        $cabecera = fgetcsv($fp);

        $enRespuestas = false;
        $usuarios = 0;
        $respuestas = 0;

        while (($fila = fgetcsv($fp)) !== false) {

            if (count(array_filter($fila)) === 0) {
                $enRespuestas = true;
                fgetcsv($fp);
                continue;
            }

            if (!$enRespuestas) {
                $stmtUsuario->bind_param(
                    "isiss",
                    $fila[0],
                    $fila[1],
                    $fila[2],
                    $fila[3],
                    $fila[4]
                );
                $stmtUsuario->execute();

                $stmtTest->bind_param(
                    "ississi",
                    $fila[0],
                    $fila[5],
                    $fila[6],
                    $fila[7],
                    $fila[8],
                    $fila[9],
                    $fila[10]
                );
                $stmtTest->execute();

                if (!empty($fila[11])) {
                    $stmtObs->bind_param("is", $fila[0], $fila[11]);
                    $stmtObs->execute();
                }

                $usuarios++;

            } else {

                if (!empty($fila[1])) {
                    $stmtResp->bind_param(
                        "iss",
                        $fila[1],
                        $fila[2],
                        $fila[3]
                    );
                    $stmtResp->execute();
                    $respuestas++;
                }
            }
        }

        fclose($fp);

        $stmtUsuario->close();
        $stmtTest->close();
        $stmtObs->close();
        $stmtResp->close();

        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 1");

        echo "<p>Importación completada</p>";
        echo "<ul>";
        echo "<li>Usuarios importados: $usuarios</li>";
        echo "<li>Respuestas importadas: $respuestas</li>";
        echo "</ul>";
    }
}
?>