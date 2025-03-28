<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../config/EnvironmentVariables.php';

class VerToken {
    private $env;

    public function __construct()
    {
        $this->env = new EnvironmentVariables();
    }

    public function middleware()
    {
        $headers = apache_request_headers();
        $authorization = null;

        if (isset($headers["Authorization"])) {
            $authorization = $headers["Authorization"];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (empty($authorization)) {
            Flight::halt(403, json_encode(["error" => "Token no enviado"]));
        }

        $parts = explode(" ", $authorization);
        if (count($parts) !== 2 || strtolower($parts[0]) !== "bearer") {
            Flight::halt(403, json_encode(["error" => "Formato del token inválido"]));
        }

        $token = $parts[1];

        try {
            $decoded = JWT::decode($token, new Key(
                $this->env->getKeyJwt(),
                $this->env->getAlgJwt()
            ));

            Flight::set('user', $decoded->user); // Disponible para cualquier uso
        } catch (\Throwable $th) {
            Flight::halt(403, json_encode(["error" => "Token inválido: " . $th->getMessage()]));
        }
    }
}
