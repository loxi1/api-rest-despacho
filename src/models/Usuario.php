<?php


// Modelo de usuario
class Usuario {
    
    private $identificador;
    private $datos;
    private $empresa;
    private $estado;
    private $area;
    private $codigo;
    private $clave;
    private $privilegios;
    private $turno;

    public function __construct($identificador, $datos, $empresa, $estado, $area, $privilegios, $turno, $codigo, $clave) {        
        $this->identificador = $identificador;
        $this->datos = $datos;
        $this->empresa = $empresa;
        $this->estado = $estado;
        $this->area = $area;
        $this->privilegios = $privilegios;
        $this->turno = $turno;
        $this->codigo = $codigo;
        $this->clave = $clave;
    }

    public function getId() {
        return $this->identificador;
    }

    public function getDatos() {
        return $this->datos;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getArea() {
        return $this->area;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function getClave() {
        return $this->clave;
    }
    
    public function getPrivilegios() {
        return $this->privilegios;
    }

    public function getTurno() {
        return $this->turno;
    }
}


?>