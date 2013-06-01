<?php
class Models_Modulo extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_modulo' ) );
            if(isset($datos["p_idModuloPadre"])) $select->where('a.idModuloPadre = ?', $datos["p_idModuloPadre"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
}