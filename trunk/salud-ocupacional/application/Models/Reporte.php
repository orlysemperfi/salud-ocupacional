<?php
    class Models_Reporte extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    public function listarempresas( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( 'view_cita',array('DISTINCT(rucempresaespecializada)') );
            if(isset($datos["p_idCompania"])) $select->where('idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_rucempresaespecializada"]) && $datos["p_rucempresaespecializada"] != "") 
                $select->where('rucempresaespecializada = ?', $datos["p_rucempresaespecializada"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listara( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_reportea' ), 
                    "a.*, (DATEDIFF(minute, a.horainicio, a.horafin)) AS duracion,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horainicio) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horainicio), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horainicio), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horainicio), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horainicio), 103), 2)) AS horainiciox,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horafin) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horafin), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horafin), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horafin), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horafin), 103), 2)) AS horafinx
                    " );
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_motivo"]) && $datos["p_motivo"] != 0) $select->where('a.motivo = ?', $datos["p_motivo"]);
            if(isset($datos["p_estado"]) && $datos["p_estado"] != 0) $select->where('a.estado = ?', $datos["p_estado"]);
            if(isset($datos["p_apto"]) && $datos["p_apto"] != 0) $select->where('a.evaaptotrabajar = ?', $datos["p_apto"]);
            if(isset($datos["p_rucempresaespecializada"]) && $datos["p_rucempresaespecializada"] != "") 
                $select->where('a.rucempresaespecializada = ?', $datos["p_rucempresaespecializada"]);
            if(isset($datos["p_fechainicio"]) && isset($datos["p_fechafin"]) && $datos["p_fechainicio"] != "" && $datos["p_fechafin"] != "") 
                $select->where("CONVERT(VARCHAR(4),YEAR(a.fecha), 103)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.fecha), 103), 2)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.fecha) , 103), 2)
                        BETWEEN '".$datos["p_fechainicio"]."' AND '".$datos["p_fechafin"]."'");
            if(isset($datos["p_idObservaciones"]) && $datos["p_idObservaciones"] != "") 
                $select->where("a.evaobservaciones LIKE '%".$datos["p_idObservaciones"]."%'");
            $select->order('fecha ASC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listarb( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_reporteb' ), 
                    "a.*, (DATEDIFF(minute, a.horainicio, a.horafin)) AS duracion,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horainicio) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horainicio), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horainicio), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horainicio), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horainicio), 103), 2)) AS horainiciox,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horafin) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horafin), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horafin), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horafin), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horafin), 103), 2)) AS horafinx
                    " );
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_motivo"]) && $datos["p_motivo"] != 0) $select->where('a.motivo = ?', $datos["p_motivo"]);
            if(isset($datos["p_estado"]) && $datos["p_estado"] != 0) $select->where('a.estado = ?', $datos["p_estado"]);
            if(isset($datos["p_apto"]) && $datos["p_apto"] != 0) $select->where('a.apto = ?', $datos["p_apto"]);
            if(isset($datos["p_rucempresaespecializada"]) && $datos["p_rucempresaespecializada"] != "") 
                $select->where('a.rucempresaespecializada = ?', $datos["p_rucempresaespecializada"]);
            if(isset($datos["p_fechainicio"]) && isset($datos["p_fechafin"]) && $datos["p_fechainicio"] != "" && $datos["p_fechafin"] != "") 
                $select->where("CONVERT(VARCHAR(4),YEAR(a.fecha), 103)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.fecha), 103), 2)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.fecha) , 103), 2)
                        BETWEEN '".$datos["p_fechainicio"]."' AND '".$datos["p_fechafin"]."'");
            $select->order('fecha ASC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listarc( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_reportec' ), 
                    "a.*, (DATEDIFF(minute, a.horainicio, a.horafin)) AS duracion,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horainicio) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horainicio), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horainicio), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horainicio), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horainicio), 103), 2)) AS horainiciox,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horafin) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horafin), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horafin), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horafin), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horafin), 103), 2)) AS horafinx
                    " );
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_motivo"]) && $datos["p_motivo"] != 0) $select->where('a.motivo = ?', $datos["p_motivo"]);
            if(isset($datos["p_estado"]) && $datos["p_estado"] != 0) $select->where('a.estado = ?', $datos["p_estado"]);
            if(isset($datos["p_apto"]) && $datos["p_apto"] != 0) $select->where('a.apto = ?', $datos["p_apto"]);
            if(isset($datos["p_rucempresaespecializada"]) && $datos["p_rucempresaespecializada"] != "") 
                $select->where('a.rucempresaespecializada = ?', $datos["p_rucempresaespecializada"]);
            if(isset($datos["p_fechainicio"]) && isset($datos["p_fechafin"]) && $datos["p_fechainicio"] != "" && $datos["p_fechafin"] != "") 
                $select->where("CONVERT(VARCHAR(4),YEAR(a.fecha), 103)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.fecha), 103), 2)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.fecha) , 103), 2)
                        BETWEEN '".$datos["p_fechainicio"]."' AND '".$datos["p_fechafin"]."'");
            $select->order('fecha ASC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listard( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_reported' ), 
                    "a.*, (DATEDIFF(minute, a.horainicio, a.horafin)) AS duracion,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horainicio) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horainicio), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horainicio), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horainicio), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horainicio), 103), 2)) AS horainiciox,
                    (RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.horafin) , 103), 2)+'/'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.horafin), 103), 2)+'/'+
                    CONVERT(VARCHAR(4),YEAR(a.horafin), 103)+' '+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(hour, a.horafin), 103), 2)+':'+
                    RIGHT( '00' + CONVERT(VARCHAR(2),DATEPART(minute, a.horafin), 103), 2)) AS horafinx
                    " );
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_motivo"]) && $datos["p_motivo"] != 0) $select->where('a.motivo = ?', $datos["p_motivo"]);
            if(isset($datos["p_estado"]) && $datos["p_estado"] != 0) $select->where('a.estado = ?', $datos["p_estado"]);
            if(isset($datos["p_apto"]) && $datos["p_apto"] != 0) $select->where('a.apto = ?', $datos["p_apto"]);
            if(isset($datos["p_rucempresaespecializada"]) && $datos["p_rucempresaespecializada"] != "") 
                $select->where('a.rucempresaespecializada = ?', $datos["p_rucempresaespecializada"]);
            if(isset($datos["p_fechainicio"]) && isset($datos["p_fechafin"]) && $datos["p_fechainicio"] != "" && $datos["p_fechafin"] != "") 
                $select->where("CONVERT(VARCHAR(4),YEAR(a.fecha), 103)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.fecha), 103), 2)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.fecha) , 103), 2)
                        BETWEEN '".$datos["p_fechainicio"]."' AND '".$datos["p_fechafin"]."'");
            $select->order('fecha ASC');
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