<?php
require_once '../api-rest/src/data/DatabaseJsonResponse.php';

class VinculadorController {
    
        private $dbJsonResponse;
    
        public function __construct() {
    
            $this->dbJsonResponse = new DatabaseJsonResponse();
        }
    
        public function vincular($rfid, $op, $hm, $color, $talla, $cantidad) {
    
            return $this->dbJsonResponse->vincularRfid($rfid, $op, $hm, $color, $talla, $cantidad);
        }
    
    }