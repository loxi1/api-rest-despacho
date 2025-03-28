<?php

require_once '../api-rest/src/data/DatabaseJsonResponse.php';

class TallaController {

    private $dbJsonResponse;

    public function __construct() {

        $this->dbJsonResponse = new DatabaseJsonResponse();
    }

    public function getAllTallaColor($op, $cod_combinacion) {

        return $this->dbJsonResponse->getTallas($op, $cod_combinacion);
    }

}

?>