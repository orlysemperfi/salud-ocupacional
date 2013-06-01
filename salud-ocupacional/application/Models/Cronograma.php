<?php
class Models_Cronograma extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    
    public function listar( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_cronograma' ), 
                    'a.*, (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma) AS nrocitas,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND
                    (b.estado = 3 OR b.estado = 4 OR b.estado = 5)) AS porcompletado,
                    
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 1) AS nroespera,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 2) AS nrocurso,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 3) AS nrocancelado,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 4) AS nronopresento,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 5) AS nrocerrado,
                    (SELECT COUNT(*) FROM view_cita AS b WHERE b.idCronograma = a.idCronograma AND b.estado = 6) AS nroatendiendose
                    ');
            if(isset($datos["p_idCronograma"])) $select->where('a.idCronograma = ?', $datos["p_idCronograma"]);
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_motivo"])) $select->where('a.motivo = ?', $datos["p_motivo"]);
            if(isset($datos["p_estado"])) $select->where('a.estado = ?', $datos["p_estado"]);
            if(isset($datos["p_fechainicio"])) 
                $select->where("CONVERT(VARCHAR(4),YEAR(a.fechainicio), 103)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),MONTH(a.fechainicio), 103), 2)+
                        RIGHT( '00' + CONVERT(VARCHAR(2),DAY(a.fechainicio) , 103), 2)
                        BETWEEN '".$datos["p_fechainicio"]."' AND '".$datos["p_fechafin"]."'");
            $select->order('a.idCronograma DESC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function obtenercorreo( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_usuario_rol' ), 
                    'a.*, (SELECT b.correo FROM view_usuario AS b WHERE b.idUsuario = a.idUsuario) AS correo' );
            
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            
            $select->where('a.flg_activo = 1');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function registrar( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_registrar ?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCompania"],     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idLocalidad"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_fechainicio"],    PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_fechafin"],       PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_motivo"],         PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_tipo"],           PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_correos"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(8, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(9, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return $rows[0]["id"];
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_cronograma_eliminar ?,?,?');                        
            $stmt->bindParam(1, $datos["p_idCronograma"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(2, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(3, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function activar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_cronograma_activar ?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idCronograma"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(3, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(4, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function listarregla( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_regla' ) );
            if(isset($datos["p_idCronograma"])) $select->where('a.idCronograma = ?', $datos["p_idCronograma"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function registrarregla( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_regla_registrar ?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCronograma"],   PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_descripcion"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_fechainicio"],    PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_fechafin"],       PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(5, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(6, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return $rows[0]["id"];
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function listarcita( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "citanombreempleado"){
                $stmt = $db->prepare("SELECT *, 
                    (SELECT b.appaterno+' '+b.apmaterno+' '+b.nombres FROM view_empleado AS b WHERE b.idEmpleado = a.idEmpleado) AS descripcion
                    FROM view_cita AS a WHERE a.idCronograma = ".$datos["p_idCronograma"]);
            }else{
                $select = $db->select()->from( array( 'a' => 'view_cita' ) );
                if(isset($datos["p_idCronograma"])) $select->where('a.idCronograma = ?', $datos["p_idCronograma"]);
                $stmt = $db->prepare($select->assemble());
            }
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function registrarcita( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_cita_registrar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCronograma"],               PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idEmpleado"],                 PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_fecha"],                      PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_direccion"],                  PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_puesto"],                     PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_telefono"],                   PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_estadocivil"],                PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_gradoinstruccion"],           PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_rucempresaespecializada"],    PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_flgtipoempresa"],            PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_area"],                      PDO::PARAM_STR);
            $stmt->bindParam(12, $datos["p_tipotrabajador"],            PDO::PARAM_STR);
            $stmt->bindParam(13, $datos["p_centrocosto"],               PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(14, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(15, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return $rows[0]["id"];
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarcita( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_cita_modificar ?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_fecha"],  PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(3, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(4, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminarcita( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_cita_eliminar ?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(2, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(3, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminarregla( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_regla_eliminar ?,?,?");    

            $stmt->bindParam(1, $datos["p_idRegla"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(2, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(3, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarestadocronograma(  ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cronograma_estado_modificar");    
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarestadocita(  ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cita_estado_modificar");    
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
}