<?php

include_once 'DatabaseConexion.php';
include_once '../api-rest/src/config/EnvironmentVariables.php';
include_once '../api-rest/src/crypto/CryptoHelper.php';
include_once '../api-rest/src/config/constants.php';
include_once 'Queries.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Clase para retornar los datos traidos de la db en json
class DatabaseJsonResponse
{
    private $pdo;
    private $odbc ;
    private $sqlQueries;
    private $envVariables;

    public function __construct()
    {
        $this->envVariables = new EnvironmentVariables();
        $this->sqlQueries = new Queries();        

        try {
            $this->odbc = DatabaseConexion::getInstance('sybase')->getConnection();
        } catch (Exception $e) {
            $this->odbc = null;
        }
    }

    // Función para iniciar sesión, recibe email y contraseña
    public function loginUser($codigo, $password)
    {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }
    
        $sql = $this->sqlQueries->queryLogin();
        $stmt = odbc_prepare($this->odbc, $sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . odbc_errormsg($this->odbc ));
        }

        // Ejecutar consulta con valor de código
        $executed = odbc_execute($stmt, [$codigo]);

        if (!$executed) {
            throw new Exception("Error al ejecutar consulta: " . odbc_errormsg($this->odbc ));
        }

        // Obtener el resultado
        $dataUser = odbc_fetch_array($stmt);
        
        if ($dataUser) {
            $passw_db = $dataUser['clave'] ?? null;
            $clave_real = null;

            if (!empty($passw_db)) {
                $crypto = new CryptoHelper();
                $clave_real = $crypto->desencriptar($passw_db);
            }
            if ($passw_db && $password === $clave_real) {
                $token = $this->buildToken($dataUser);
                $jwt = JWT::encode(
                    $token,
                    $this->envVariables->getKeyJwt(),
                    $this->envVariables->getAlgJwt()
                );

                return [
                    "message" => "Inicio de sesión satisfactorio.",
                    "token" => $jwt,
                    "status" => 'OK'
                ];
            } else {
                return [
                    "message" => "Contraseña Incorrecta.",
                    "status" => 'error'
                ];
            }
        } else {
            return [
                "message" => "Usuario no encontrado.",
                "status" => 'error'
            ];
        }
    }


    // Función para registrar usuario, recibe el modelo user
    public function registerUser($user)
    {
        $stmt = odbc_prepare($this->odbc, $this->sqlQueries->querySaveUser());

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc),
                "status" => "error"
            ];
        }

        // Si estás usando encriptación con openssl y no bcrypt:
        $crypto = new CryptoHelper();
        $clave_encriptada = $crypto->encriptar($user->getPasswUser());

        $params = [
            $user->getCodigo(),
            $user->getDatos(),
            $user->getEmpresa(),
            $user->getEstado(),
            $user->getArea(),
            $clave_encriptada,
            $user->getPrivilegio(),
            $user->getTurno()
        ];

        $executed = odbc_execute($stmt, $params);

        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc),
                "status" => "error"
            ];
        }

        return [
            "message" => "Registro satisfactorio.",
            "status" => "OK"
        ];
    }


    public function getUsers($headers)
    {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }

        $sql = $this->sqlQueries->queryGetUsers();
        $stmt = odbc_prepare($this->odbc , $sql);

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $executed = odbc_execute($stmt, [$headers]);
        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $users = [];

        while ($row = odbc_fetch_array($stmt)) {
            if (!$row) break;
            $users[] = [
                "id" => $row['identificador'] ?? null,
                "datos" => mb_convert_encoding($row['datos'], 'UTF-8', 'ISO-8859-1') ?? null,
                "codigo" => $row['codigo'] ?? null
            ];
        }
        //print_r($users);die();
        return [
            "total_users" => count($users),
            "users" => $users,
            "status" => "OK"
        ];
    }

    public function vincularRfid($rfid, $op, $hm, $iduser, $color, $talla, $cantidad, $token)
    {
        $sql = "{CALL USP_SAL_EMB_CON_RFID_DATA(?, ?, ?, ?, ?, ?, ?, ?)}";

        $stmt = odbc_prepare($this->odbc , $sql);
        if (!$stmt) {
            return ["error" => "Error preparando SP: " . odbc_errormsg($this->odbc )];
        }

        $params = [$rfid, $op, $hm, $iduser, $color, $talla, $cantidad, $token];
        $executed = odbc_execute($stmt, $params);

        if (!$executed) {
            return ["error" => "Error ejecutando SP: " . odbc_errormsg($this->odbc )];
        }

        return ["message" => "Datos vinculados correctamente", "status" => "OK"];
    }

    public function getColores($op) {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }

        $sql = $this->sqlQueries->queryGetColor();
        $stmt = odbc_prepare($this->odbc , $sql);

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $executed = odbc_execute($stmt, [$op]);
        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $colores = [];

        while ($row = odbc_fetch_array($stmt)) {
            if (!$row) break;
            $colores[] = [
                "codigo" => mb_convert_encoding($row['cclrcl'], 'UTF-8', 'ISO-8859-1') ?? null,
                "color" => $row['tclrcl'] ?? null
            ];
        }
        
        return [
            "total_colors" => count($colores),
            "colors" => $colores,
            "status" => "OK"
        ];
    }

    public function getTallas($op, $cod_combinacion) {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }

        $sql = $this->sqlQueries->queryGetTalla();
        $stmt = odbc_prepare($this->odbc , $sql);

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $executed = odbc_execute($stmt, [$op, $cod_combinacion]);
        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $tallas = [];

        while ($row = odbc_fetch_array($stmt)) {
            if (!$row) break;
            $tallas[] = [
                "talla" => mb_convert_encoding($row['tdscr'], 'UTF-8', 'ISO-8859-1') ?? null,
                "cod_talla" => $row['cod_talla'] ?? null
            ];
        }
        
        return [
            "total_tallas" => count($tallas),
            "tallas" => $tallas,
            "status" => "OK"
        ];
    }

    public function getOP($op) {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }

        $sql = $this->sqlQueries->queryGetOP();
        $stmt = odbc_prepare($this->odbc , $sql);

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $executed = odbc_execute($stmt, [':op' => '%' . $op . '%']);
        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $op = "";
        while ($row = odbc_fetch_array($stmt)) {
            if (!$row) break;
            $op = $row["norpd"] ?? null;
        }
        
        return [
            "op" => $op,
            "status" => "OK"
        ];
    }   

    public function getHM($op, $hm) {
        if (!$this->odbc) {
            return [
                "error" => "Conexión Sybase no disponible",
                "status" => "error"
            ];
        }

        $sql = $this->sqlQueries->queryGetHM();
        $stmt = odbc_prepare($this->odbc , $sql);

        if (!$stmt) {
            return [
                "error" => "Error al preparar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $executed = odbc_execute($stmt, [$op, $hm]);
        if (!$executed) {
            return [
                "error" => "Error al ejecutar consulta: " . odbc_errormsg($this->odbc ),
                "status" => "error"
            ];
        }

        $hojamarca = "";

        while ($row = odbc_fetch_array($stmt)) {
            if (!$row) break;
            $hojamarca = $row["nhjmr"] ?? null;
        }
        
        return [
            "total_hm" => count($hojamarca),
            "hm" => $hojamarca,
            "status" => "OK"
        ];
    }

    // Construye y retorna el token con la información y el usuario($data) requeridos
    private function buildToken($dataUser)
    {
        return array(
            "iss" => ISS,
            "aud" => AUD,
            "iat" => IAT,
            "nbf" => NBF,
            "exp" => EXP,

            "user" => array(
                "id" => $dataUser['identificador'],
                "datos" => $dataUser['datos'],
                "codigo" => $dataUser['codigo']
            )
        );
    }

    // Función para obtener token y retornar decodeado
    private function getToken($headers)
    {
        if (isset($headers["Authorization"])) {
            $authorization = $headers["Authorization"];
            $authorizationArray = explode(" ", $authorization);

            // Verificar si el token está vacío después de dividirlo
            if (empty($authorizationArray[1])) {
                return array(
                    "error" => 'Unauthenticated request',
                    "status" => 'error'
                );
            }

            try {
                $token = $authorizationArray[1]; // Obtener token
                return array(
                    "data" => JWT::decode($token, new Key($this->envVariables->getKeyJwt(), $this->envVariables->getAlgJwt())),
                    "status" => 'OK'
                );
            } catch (\Throwable $th) {
                return array(
                    "error" => $th->getMessage(),
                    "status" => 'error'
                );
            }
        } else {
            return array(
                "error" => 'Unauthenticated request',
                "status" => 'error'
            );
        }
    }

    // Función para validar token 
    private function validateToken($headers)
    {
        $token = $this->getToken($headers);

        if ($token["status"] !== 'OK') {
            return $token; // Error en el token
        }

        $data = $token["data"];
        $sql = $this->sqlQueries->queryGetUserById(); // Consulta con :id
        $sql = str_replace(":id", "'" . $data->user->id . "'", $sql); // Reemplazo manual del parámetro

        $stmt = odbc_exec($this->odbc, $sql);

        if (!$stmt) {
            return [
                "error" => "Error al ejecutar la consulta: " . odbc_errormsg($this->odbc),
                "status" => "error"
            ];
        }

        if (odbc_fetch_row($stmt)) {
            return ["status" => "OK"];
        } else {
            return [
                "error" => "Token válido, pero el usuario no existe.",
                "status" => "error"
            ];
        }
    }
}
