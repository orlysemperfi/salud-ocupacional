<?php
class Models_Empleado extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
        $this->connectMSSQLs();
    }
    
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "nompue"){
                $sql = "SELECT a.dni, a.appaterno, a.amaterno, a.nombres FROM view_empleado AS a ";
            }else{
                $sql = "SELECT a.*, 
                (SELECT TOP 1 b.puesto FROM view_cita AS b WHERE a.idEmpleado = b.idEmpleado ORDER BY b.idCita DESC) AS puesto,
                (SELECT TOP 1 b.estadocivil FROM view_cita AS b WHERE a.idEmpleado = b.idEmpleado ORDER BY b.idCita DESC) AS estadocivil
                FROM view_empleado AS a ";
            }
            if(isset($datos["p_dni"]) || isset($datos["p_idEmpleado"])) $aux = "WHERE";
            if(isset($datos["p_dni"])) $sql .= "$aux LTRIM(RTRIM(a.dni)) = '".$datos["p_dni"]."'";       
            if(isset($datos["p_idEmpleado"])) $sql .= "$aux a.idEmpleado = '".$datos["p_idEmpleado"]."'";       
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function contar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "SELECT a.idEmpleado
            FROM view_empleado AS a WHERE
                (SELECT TOP 1 c.idCompania FROM view_cronograma AS c 
                WHERE c.idCronograma = (SELECT TOP 1 b.idCronograma FROM view_cita AS b
                WHERE a.idEmpleado = b.idEmpleado)) = '".$datos["p_idCompania"]."' AND
                (SELECT TOP 1 c.idLocalidad FROM view_cronograma AS c 
                WHERE c.idCronograma = (SELECT TOP 1 b.idCronograma FROM view_cita AS b
                WHERE a.idEmpleado = b.idEmpleado)) = '".$datos["p_idLocalidad"]."'";      
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function paginado( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $subsql = "
            SELECT a.*, 
                (SELECT TOP 1 b.puesto FROM view_cita AS b WHERE 
                a.idEmpleado = b.idEmpleado ORDER BY b.idCita DESC) AS puesto,
                (SELECT TOP 1 b.estadocivil FROM view_cita AS b 
                WHERE a.idEmpleado = b.idEmpleado ORDER BY b.idCita DESC) AS estadocivil,
                ROW_NUMBER() OVER (ORDER BY ".$datos['orderby']." ".$datos['orden'].") AS RowNum
            FROM view_empleado AS a WHERE
                (SELECT TOP 1 c.idCompania FROM view_cronograma AS c 
                WHERE c.idCronograma = (SELECT TOP 1 b.idCronograma FROM view_cita AS b
                WHERE a.idEmpleado = b.idEmpleado)) = '".$datos["p_idCompania"]."' AND
                (SELECT TOP 1 c.idLocalidad FROM view_cronograma AS c 
                WHERE c.idCronograma = (SELECT TOP 1 b.idCronograma FROM view_cita AS b
                WHERE a.idEmpleado = b.idEmpleado)) = '".$datos["p_idLocalidad"]."'
                ".((isset($datos["p_dni"]))?" AND a.dni LIKE '%".$datos["p_dni"]."%'":"")."
                ".((isset($datos["p_nombres"]))?" AND a.appaterno+' '+a.apmaterno+' '+a.nombres LIKE '%".$datos["p_nombres"]."%'":"")."
                ";
            
            $sql = "SELECT * FROM ($subsql) AS x WHERE x.RowNum 
                BETWEEN (".$datos['offset']."-1)*".$datos['limit']."+1 AND ".$datos['offset']."*".$datos['limit']."";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function registrar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_empleado_registrar ?,?,?,?,?,?,?,?,?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_appaterno"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_apmaterno"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_nombres"],        PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_dni"],            PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_fechanacimiento"],PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_deptnac"],        PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_provnac"],        PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_distnac"],        PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_sexo"],           PDO::PARAM_STR);
            $stmt->bindParam(10,$datos["p_fechaingreso"],   PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(11, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(12, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $db->closeConnection();
            return $rows[0]["id"];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_empleado_modificar ?,?,?,?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idEmpleado"],     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_appaterno"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_apmaterno"],      PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_nombres"],        PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_dni"],            PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_fechanacimiento"],PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_deptnac"],        PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_provnac"],        PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_distnac"],        PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_sexo"],          PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_fechaingreso"],  PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(12, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(13, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarcita( $datos = array() ){
        try {
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare("EXEC ep_cita_modificar ?,?,?,?,?,?,?,?,?,?,?,?,?");    

            $stmt->bindParam(1, $datos["p_idCita"],                     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_fecha"],                      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_puesto"],                     PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_telefono"],                   PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_estadocivil"],                PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_gradoinstruccion"],           PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_rucempresaespecializada"],    PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_flgtipoempresa"],             PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_area"],                      PDO::PARAM_STR);
            $stmt->bindParam(10, $datos["p_tipotrabajador"],            PDO::PARAM_STR);
            $stmt->bindParam(11, $datos["p_centrocosto"],               PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(12, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(13, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        }catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_empleado_eliminar ?,?,?');                        
            $stmt->bindParam(1, $datos["p_idEmpleado"], PDO::PARAM_STR);
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
    public function contarcita( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "SELECT a.idCita FROM view_cita AS a WHERE a.idEmpleado = ".$datos["p_idEmpleado"];
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function existecita( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            if(!isset($datos["p_idCita"]))
                $sql = "SELECT a.idCita, a.idEmpleado, a.fecha, b.motivo FROM view_cita AS a 
                        INNER JOIN view_cronograma AS b ON a.idCronograma = b.idCronograma
                        WHERE a.idEmpleado = ".$datos["p_idEmpleado"]." AND a.fecha = '".$datos["p_fecha"]."' AND b.motivo = '".$datos["p_motivo"]."'";
            else
                $sql = "SELECT a.idCita, a.idEmpleado, a.fecha, b.motivo FROM view_cita AS a 
                        INNER JOIN view_cronograma AS b ON a.idCronograma = b.idCronograma
                        WHERE a.idEmpleado = (SELECT c.idEmpleado FROM view_cita AS c WHERE c.idCita = ".$datos["p_idCita"].") 
                            AND a.fecha = '".$datos["p_fecha"]."' 
                            AND b.motivo = '".$datos["p_motivo"]."'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listarcita( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "SELECT a.*, 
                (SELECT b.motivo FROM view_cronograma AS b WHERE b.idCronograma = a.idCronograma) AS motivo,
                (SELECT b.fechainicio FROM view_cronograma AS b WHERE b.idCronograma = a.idCronograma) AS fechainicio,
                (SELECT b.fechafin FROM view_cronograma AS b WHERE b.idCronograma = a.idCronograma) AS fechafin
                FROM view_cita AS a WHERE a.idCita = ".$datos["p_idCita"];
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function tomarcita( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $sql = "SELECT TOP 1 a.* FROM view_cita AS a WHERE a.idEmpleado = ".$datos["p_idUsuario"]." ORDER BY a.idCita DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function paginadocita( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $subsql = "
            SELECT a.*,
                ROW_NUMBER() OVER (ORDER BY a.".$datos['orderby']." ".$datos['orden'].") AS RowNum
            FROM view_cita AS a WHERE a.idEmpleado = ".$datos["p_idEmpleado"];
            
            $sql = "SELECT * FROM ($subsql) AS x WHERE x.RowNum 
                BETWEEN (".$datos['offset']."-1)*".$datos['limit']."+1 AND ".$datos['offset']."*".$datos['limit']."";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    
    public function cancelarcita( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_cita_cancelar ?,?,?');
            
            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
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
    public function iniciarcita( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_cita_iniciar ?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idCita"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_estado"], PDO::PARAM_STR);
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
    
    public function areaautocompletar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            $sql = "
                SELECT * FROM UVW_AREAS AS a
                WHERE a.descr_area LIKE '%".$datos["p_descr_area"]."%' ORDER BY a.descr_area ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function centrocostocompletar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQLs;
            $sql = "
                SELECT * FROM UVW_CCOSTO AS a
                WHERE a.descr_ccosto LIKE '%".$datos["p_descr_ccosto"]."%' ORDER BY a.descr_ccosto ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $arrays = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($arrays);
        } catch (Exception $e) {
            return -1;
        }        
    }
}