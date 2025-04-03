<?php
require_once '../api-rest/src/data/DatabaseJsonResponse.php';

class VinculadorController {
    
        private $dbJsonResponse;
    
        public function __construct() {
    
            $this->dbJsonResponse = new DatabaseJsonResponse();
        }
    
        public function vincular($rfid, $op, $hm, $iduser, $color, $talla, $cantidad, $token) {
    
            return $this->dbJsonResponse->vincularRfid($rfid, $op, $hm, $iduser, $color, $talla, $cantidad, $token);
        }
    
    }