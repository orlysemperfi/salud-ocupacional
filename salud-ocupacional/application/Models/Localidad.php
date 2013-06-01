<?php
class Models_Localidad extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_LOC' ) );
            if(isset($datos["p_idCompania"])) $select->where('a.id_cia = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.id_loc = ?', $datos["p_idLocalidad"]);
            $select->where('a.id_loc <> ?', '***');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            $array = array();
            foreach($arrays as $index => $row){
                $array[$index]["idCompania"] = $row["id_cia"];
                $array[$index]["idLocalidad"] = $row["id_loc"];
                $array[$index]["nombre"] = $row["descr_loc"];
            }
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
}