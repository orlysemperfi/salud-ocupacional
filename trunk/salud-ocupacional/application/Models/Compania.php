<?php
class Models_Compania extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            
            $select = $db->select()->from( array( 'a' => 'UVW_CIAS' ) );
            if(isset($datos["p_idCompania"])) $select->where('a.id_cia = ?', $datos["p_idCompania"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            $array = Array();
            foreach($arrays as $index => $row){
                $array[$index]["idCompania"] = $row["id_cia"];
                $array[$index]["nombreCompleto"] = $row["descr_larga"];
                $array[$index]["nombre"] = $row["descr_corta"];
            }
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
}