<?php
namespace src\controllers;

use src\models\Usuario;

class UsurarioController
{
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['codigo'], $data['clave'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos de login"]);
            return;
        }

        $usuario = new User();
        $resultado = $usuario->login($data['codigo'], $data['clave']);

        if ($resultado) {
            echo json_encode(["success" => true, "usuario" => $resultado]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas"]);
        }
    }
}
