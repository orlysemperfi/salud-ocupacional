<?php
    class Models_Centrocosto extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $d ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_CCOSTO' ) );
            if(isset($d) && $d != "") $select->where('a.id_ccosto = ?', $d);
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