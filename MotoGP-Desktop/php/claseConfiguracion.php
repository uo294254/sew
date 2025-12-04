<?php
class Configuracion {

    private $conexion;
    private $database = "UO294254_DB";

    public function __construct() {
        $this->conexion = new mysqli("localhost", "DBUSER2025", "DBPSWD2025");

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        
        $this->conexion->select_db($this->database);
    }

    public function reiniciarBD() {
        $tablas = [
            "respuestas",
            "observaciones",
            "tests", 
            "usuarios",
            "dispositivos",
            "pericias",
            "generos",
            "profesiones"
        ];

        foreach ($tablas as $tabla) {
            $this->conexion->query("DELETE FROM $tabla");
        }

        echo "<p>Base de datos reiniciada correctamente</p>";
    }

    public function eliminarBD() {
        $this->conexion->query("DROP DATABASE IF EXISTS $this->database");

        echo "<p>Base de datos eliminada correctamente</p>";
    }

    public function exportarCSV() {
        $resultado = $this->conexion->query("SELECT * FROM tests");

        if (!$resultado) {
            echo "<p>Error al consultar datos: " . $this->conexion->error . "</p>";
            return;
        }

        $archivo = "tests.csv";
        $fp = fopen($archivo, "w");

        if (!$fp) {
            echo "<p>Error al crear el archivo CSV</p>";
            return;
        }

        if ($resultado->num_rows > 0) {
            $primeraFila = $resultado->fetch_assoc();
            fputcsv($fp, array_keys($primeraFila));
            fputcsv($fp, $primeraFila);
            
            while ($fila = $resultado->fetch_assoc()) {
                fputcsv($fp, $fila);
            }
        }

        fclose($fp);

        echo "<p>Datos exportados a $archivo</p>";
    }

    public function __destruct() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>