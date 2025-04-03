<?php

require_once '../api-rest/src/controllers/VinculadorController.php';
require_once '../api-rest/src/controllers/ColorController.php';
require_once '../api-rest/src/controllers/TallaController.php';
require_once '../api-rest/src/controllers/UserController.php';
require_once '../api-rest/src/controllers/OPHMController.php';
include_once '../api-rest/src/config/constants.php';
include_once '../api-rest/src/config/VerToken.php';
require_once '../api-rest/src/models/Usuario.php';

require 'vendor/autoload.php';

function getUserController() {
    return $userController  = new UserController();
}

function getVinculadorController() {
    return $vinculadorController = new VinculadorController();
}

function getColorController() {
    return $colorController = new ColorController();
}

function getTallaController() {
    return $tallaController = new TallaController();
}

function getOPHMController() {
    return $ophmController = new OPHMController();
}

// Recibe json con el cod personal y clave
Flight::route('POST /login', function () {
    
    $data = Flight::request()->data; // Obtener los datos JSON del cuerpo de la solicitud

    if (isset($data['codigo']) && isset($data['password'])) {
        
        Flight::json(getUserController()->login($data['codigo'], $data['password']));

    } else {
        
        Flight::json(["error" => "Se requiere email y password"], BAD_REQUEST);
    }
});

// Recibe json con los datos del usuario a registrar
Flight::route('POST /registerUser', function () {
    $data = Flight::request()->data;
    // Verifica que los datos no estén vacíos
    //$identificador, $datos, $empresa, $estado, $area, $privilegios, $turno, $codigo, $clave
    $required = ['codigo', 'datos', 'empresa', 'estado', 'area', 'privilegios', 'turno', 'clave'];
    foreach ($required as $campo) {
        if (empty($data[$campo])) {
            Flight::halt(400, "Falta el campo requerido: $campo");
        }
    }

    $usuario = new Usuario(
        0,
        $data['datos'],
        $data['empresa'],
        $data['estado'],
        $data['area'],
        $data['privilegios'],
        $data['turno'],
        $data['codigo'],
        $data['clave']
    );

    $response = getUserController()->registerUser($usuario);
    Flight::json($response);
});

//Envia parametros: 
//'E28068900000500DBA24241A,E28068900000500DBA24241C,E28068900000400DBA24241B,E28068900000400DBA242417,E28068900000500DBA242875','1000036763','0003','36104','0001','26',5,'20240808121323'
Flight::route('POST /vincular', function () {
    $data = Flight::request()->data;

    $required = ['rfid', 'op', 'hm', 'iduser', 'color', 'talla', 'cantidad'];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            Flight::halt(400, "Falta el campo requerido: $field");
        }
    }

    $response = getVinculadorController()->vincular(
        $data['rfid'],
        $data['op'],
        $data['hm'],
        $data['iduser'],
        $data['color'],
        $data['talla'],
        $data['cantidad'],
        date("YmdHis")
    );

    Flight::json($response);
});

//Envia op
Flight::route('GET /getColor', function () {
    $op = Flight::request()->query['op'] ?? null;
    if (!$op) {
        Flight::halt(400, "Parámetro 'op' requerido");
    }

    $response = getColorController()->getAllColor($op);

    if (!is_array($response)) {
        Flight::halt(500, "Respuesta inválida del servidor");
    }

    if ($response["status"] === 'error') {
        Flight::halt(403, $response["error"]);
    }

    Flight::json($response);
});

//Envia op y cod_talla-> codigo de talla
Flight::route('GET /getTalla', function () {
    $op = Flight::request()->query['op'] ?? null;
    $cod = Flight::request()->query['codigo'] ?? null;
    
    $response = getTallaController()->getAllTallaColor($op, $cod);
    
    if (!is_array($response)) {
        Flight::halt(500, "Respuesta inválida del servidor");
    }

    if ($response["status"] === 'error') {
        Flight::halt(403, $response["error"]);
    }

    Flight::json($response);
});

//Mostrar todo los usuario
protect('GET', '/getUsers', function () {
    $response = getUserController()->getUsers(apache_request_headers());
    
    if (!is_array($response)) {
        Flight::halt(500, "Respuesta inválida del servidor");
    }

    if ($response["status"] === 'error') {
        Flight::halt(403, $response["error"]);
    }

    Flight::json($response);
});

//Envia op
Flight::route('GET /getOp', function () {
    $op = Flight::request()->query['op'] ?? null;
    if (!$op) {
        Flight::halt(400, "Parámetro 'op' requerido");
    }

    $response = getOPHMController()->getOP($op);

    if (!is_array($response)) {
        Flight::halt(500, "Respuesta inválida del servidor");
    }

    if ($response["status"] === 'error') {
        Flight::halt(403, $response["error"]);
    }

    Flight::json($response);
});

//Envia op y hm
Flight::route('GET /getHM', function () {
    $op = Flight::request()->query['op'] ?? null;
    $cod = Flight::request()->query['hm'] ?? null;
    
    $response = getOPHMController()->getHM($op, $cod);
    
    if (!is_array($response)) {
        Flight::halt(500, "Respuesta inválida del servidor");
    }

    if ($response["status"] === 'error') {
        Flight::halt(403, $response["error"]);
    }

    Flight::json($response);
});


Flight::start();

function protect($method, $path, $callback) {
    Flight::route("{$method} {$path}", function () use ($callback) {
        $verToken = new VerToken();
        $verToken->middleware(); // ⛔ si no pasa, detiene aquí
        $callback(); // ✅ si pasa, ejecuta la lógica de la ruta
    });
}
?>