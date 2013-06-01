<?php
    class Models_Area extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $d ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_AREAS' ) );
            if(isset($d) && $d != "") $select->where('LTRIM(RTRIM(a.Id_Area)) = ?', $d);
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