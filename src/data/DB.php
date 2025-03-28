<?php
namespace Src\data;
use src\config\EnvironmentVariables;

class DB
{
    private $user;
    private $password;
    private $dsn;
    private $ODBCConnection = null;

    public function __construct()
    {
        $this->user = EnvironmentVariables::get('DB_USER');
        $this->password = EnvironmentVariables::get('DB_PASSWORD');
        $this->dsn = EnvironmentVariables::get('DB_DSN');
    }

    public function conectar()
    {
        $this->ODBCConnection = odbc_connect($this->dsn, $this->user, $this->password);
        if (!$this->ODBCConnection) {
            throw new \Exception('Error de conexión: ' . odbc_errormsg());
        }
        return $this->ODBCConnection;
    }

    public function cerrar()
    {
        if ($this->ODBCConnection) {
            odbc_close($this->ODBCConnection);
        }
    }
}
