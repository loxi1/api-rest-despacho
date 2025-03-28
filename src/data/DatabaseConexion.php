<?php

include_once '../api-rest/src/config/EnvironmentVariables.php';
require 'vendor/autoload.php';

class DatabaseConexion {

    private static $instances = [];
    private $connection;
    private $envVariables;
    private $motor;

    private function __construct($motor = 'mysql') {
        $this->envVariables = new EnvironmentVariables();
        $this->motor = strtolower($motor);
        $this->initializeConnection();
    }

    public static function getInstance($motor = 'mysql') {
        $motor = strtolower($motor);
        if (!isset(self::$instances[$motor])) {
            self::$instances[$motor] = new DatabaseConexion($motor);
        }
        return self::$instances[$motor];
    }

    private function initializeConnection() {
        if ($this->motor === 'mysql') {
            $host = $this->envVariables->getHost();
            $dbname = $this->envVariables->getNameDb();
            $user = $this->envVariables->getUserDb();
            $pwd = $this->envVariables->getPasswordDb();

            // Si estás usando FlightPHP para manejar la conexión
            Flight::register('database', 'PDO', [
                "mysql:host=$host;dbname=$dbname;charset=utf8",
                $user,
                $pwd
            ]);

            // Guardamos la conexión desde Flight
            $this->connection = Flight::database();

        } elseif ($this->motor === 'sybase') {
            $dsn = $this->envVariables->getDsn(); // ejemplo: "DRIVER=...;Server=...;Database=..."
            $user = $this->envVariables->getUserDb();
            $pwd  = $this->envVariables->getPasswordDb();

            $this->connection = odbc_connect($dsn, $user, $pwd);
            if (!$this->connection) {
                throw new Exception("Error de conexión ODBC (Sybase): " . odbc_errormsg());
            }
        } else {
            throw new Exception("Motor de base de datos no soportado: $this->motor");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getMotor() {
        return $this->motor;
    }
}
