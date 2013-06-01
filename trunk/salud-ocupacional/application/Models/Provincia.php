<?php
class Models_Provincia extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $d, $p ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_PROV' ) );
            if(isset($d) && $d != "") $select->where('a.id_dpto = ?', $d);
            if(isset($p) && $p != "") $select->where('a.id_prov = ?', $p);
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