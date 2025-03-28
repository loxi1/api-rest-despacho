<?php
include_once 'DatabaseConexion.php';
include_once '../api-rest/src/config/EnvironmentVariables.php';
include_once '../api-rest/src/config/constants.php';
include_once 'QueriesPdo.php';

class DatabaseJsonResponsePdo {
    private $pdo;
    private $sqlQueries;
    private $envVariables;

    public function __construct()
    {
        $this->envVariables = new EnvironmentVariables();
        $this->sqlQueries = new QueriesPdo();

        try {
            $this->pdo = DatabaseConexion::getInstance('mysql')->getConnection();
        } catch (Exception $e) {
            $this->pdo = null;
        }
    }

    // 🔍 Método para obtener las últimas 10 prendas
    public function verPrendas() {
        if (!$this->pdo) {
            return [
                "error" => "Conexión MySQL no disponible",
                "status" => "error"
            ];
        }

        $query = $this->sqlQueries->queryPrendas();

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "total_prendas" => count($data),
                "prendas" => $data,
                "status" => "OK"
            ];
        } catch (PDOException $e) {
            return [
                "error" => "Error al ejecutar la consulta: " . $e->getMessage(),
                "status" => "error"
            ];
        }
    }
}
