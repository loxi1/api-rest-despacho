<?php

require_once '../api-rest/src/data/DatabaseJsonResponse.php';

class ColorController {

    private $dbJsonResponse;

    public function __construct() {

        $this->dbJsonResponse = new DatabaseJsonResponse();
    }

    public function getAllColor($op) {

        return $this->dbJsonResponse->getColores($op);
    }

}

?>