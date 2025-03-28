<?php

// Clase para retornar las consultas a la db
class Queries {

    public function queryLogin() {
        $query = "SELECT top 1 identificador, codigo, datos, clave 
                    FROM usuario_timbrado WHERE codigo = :codigo and estado = 'ACTIVO'";

        return $query;
    }

    public function queryRegisterUser() {
        $query = "INSERT INTO usuario_timbrado
                    SET codigo = :codigo,
                        datos = :datos,
                        codigo = :codigo,
                        clave = :clave";
        
        return $query;
    }

    public function queryGetUserById() {
        $query = "SELECT identificador, codigo, datos, codigo
                    FROM usuario_timbrado WHERE identificador = :id";
        
        return $query;
    }

    public function queryGetUsers() {
        $query = "SELECT identificador, codigo, datos, codigo FROM usuario_timbrado where estado = 'ACTIVO'";
        
        return $query;
    }

    public function queryGetColor() {
        $query = "SELECT DISTINCT cclrcl, tclrcl  FROM altopd WHERE altopd.nnope = :op ORDER BY tclrcl";
        
        return $query;
    }

    public function queryGetTalla() {
        $query = "SELECT ordenserviciostallasmov.cod_talla, almcad.tdscr
        FROM ordenserviciostallasmov 
        LEFT JOIN	almcad ON ordenserviciostallasmov.Cod_Talla= almcad.ccrct 
        WHERE ordenserviciostallasmov.ccmpn='02' and ordenserviciostallasmov.nnope= :op AND ordenserviciostallasmov.flgestado='INGRESO A ACABADO' AND ordenserviciostallasmov.cod_combinacion= :cod_combinacion AND codQR IS NULL AND almcad.ctpar = '10' AND almcad.norden = '6' GROUP BY ordenserviciostallasmov.cod_talla, almcad.tdscr";
        
        return $query;
    }
}

?>