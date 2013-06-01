<?php
    class Models_Enfermedad extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_enfermedades' ) );
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
}