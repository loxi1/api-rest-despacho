<?php

require_once '../api-rest/src/data/DatabaseJsonResponse.php';

class OPHMController {

    private $dbJsonResponse;

    public function __construct() {

        $this->dbJsonResponse = new DatabaseJsonResponse();
    }

    public function getOP($op) {
        return $this->dbJsonResponse->getOP($op);
    }    

    public function getHM($op, $hm) {
        return $this->dbJsonResponse->getHM($op, $hm);
    }
}

?>