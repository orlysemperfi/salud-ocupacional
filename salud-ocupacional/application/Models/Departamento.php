<?php
class Models_Departamento extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $id ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_DPTO' ) );
            if(isset($id) && $id != "") $select->where('a.id_dpto = ?', $id);
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