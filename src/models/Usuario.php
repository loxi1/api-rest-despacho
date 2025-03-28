<?php
namespace Src\models;

use Src\data\DB;

class Usuario
{
    public function login($codigo, $clave)
    {
        $conexion = new DatabaseConexion();
        $conn = $conexion->conectar();

        $sql = "SELECT identificador, codigo, datos, empresa, estado 
                FROM usuario_timbrado 
                WHERE estado = 'ACTIVO' AND codigo = ? AND clave = ?";

        $stmt = odbc_prepare($conn, $sql);
        $result = odbc_execute($stmt, [$codigo, $clave]);

        if ($result) {
            $row = odbc_fetch_array($stmt);
            $conexion->cerrar();
            return $row ?: null;
        }

        $conexion->cerrar();
        return null;
    }
}
