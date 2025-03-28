<?php


// Modelo de usuario
class User {
    
    private $identificador;
    private $datos;
    private $codigo;
    private $clave;

    public function __construct($identificador, $datos, $codigo, $clave) {
        
            $this->identificador = $identificador;
            $this->datos = $datos;
            $this->codigo = $codigo;
            $this->clave = $clave;

    }

    public function getIdUser() {
        return $this->identificador;
    }

    public function getNombUser() {
        return $this->datos;
    }

    public function getCodigolUser() {
        return $this->codigo;
    }

    public function getPasswUser() {
        return $this->clave;
    }

}


?>