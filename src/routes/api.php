<?php

require_once '../api-rest/src/controllers/UserController.php';
require_once '../api-rest/src/controllers/VinculadorController.php';
require_once '../api-rest/src/controllers/ColorController.php';
require_once '../api-rest/src/controllers/TallaController.php';
require_once '../api-rest/src/models/User.php';
include_once '../api-rest/src/config/constants.php';
include_once '../api-rest/src/config/VerToken.php';



require 'vendor/autoload.php';

function getUserController() {
    return $userController = new UserController();
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

// Recibe json con el email y contraseña
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

    if ($data != null) {

        $user = new User(0, $data['nombre'], $data['apellido'], 
                        $data['nick'], $data['email'], $data['password']);

        Flight::json(getUserController()->registerUser($user));

    } else {

        Flight::json(["error" => "Se requiere todos los campos", BAD_REQUEST]);
    }

});

Flight::route('POST /vincular', function () {
    $data = Flight::request()->data;

    $required = ['rfid', 'op', 'hm', 'color', 'talla', 'cantidad'];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            Flight::halt(400, "Falta el campo requerido: $field");
        }
    }

    /*$response = getVinculadorController()->vincularRfid(
        $data['rfid'],
        $data['op'],
        $data['hm'],
        $data['color'],
        $data['talla'],
        (int)$data['cantidad']
    );*/
    $response = ['message' => 'Datos vinculados correctamente', 'status' => 'OK', 'rta'=>$data];

    Flight::json($response);
});

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



Flight::start();

function protect($method, $path, $callback) {
    Flight::route("{$method} {$path}", function () use ($callback) {
        $verToken = new VerToken();
        $verToken->middleware(); // ⛔ si no pasa, detiene aquí
        $callback(); // ✅ si pasa, ejecuta la lógica de la ruta
    });
}
?>