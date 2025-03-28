<?php

// Clase para retornar las consultas a la db
class QueriesPdo {

    public function queryPrendas() {
        $query = "select id_rfid, id_barras, op, hoja_marcacion, corte, subcorte, talla, color 
        from prenda order by fecha_registro desc limit 10";

        return $query;
    }
}

?>