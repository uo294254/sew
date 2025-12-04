<?php

class Cronometro {
    protected $tiempo;
    protected $inicio;

    public function __construct() {
        $this->tiempo = 0;
    }

    public function arrancar() {
        $this->inicio = microtime(true);
    }

    public function parar() {
        $fin = microtime(true);
        $this->tiempo = $fin - $this->inicio;
    }

    public function mostrar() {
        $total = $this->tiempo;

        $minutos = floor($total / 60);
        $segundos = floor($total % 60);
        $decimas = floor(($total - floor($total)) * 10);

        $minutos_str = $minutos < 10 ? "0".$minutos : $minutos;
        $segundos_str = $segundos < 10 ? "0".$segundos : $segundos;
    
        return $minutos_str . ":" . $segundos_str . "." . $decimas;
    }
}
?>