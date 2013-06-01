<?php
class Models_Empresa extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQLs();
    }
    public function listar( $d ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( 'a' => 'UVW_CTTAS' ) );
            if(isset($d) && $d != "") $select->where('a.num_ruc = ?', $d);
            $select->where("a.num_ruc like '20%'");
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function empresaespecializadaautocompletar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            $select = $db->select()->from( array( "a" => "UVW_CTTAS" ) );
            if(isset($datos["p_descr_ctta"])) $select->where("a.descr_ctta like '%".$datos["p_descr_ctta"]."%'");
            $select->where("a.num_ruc like '20%'");
            $select->order("a.descr_ctta ASC");
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