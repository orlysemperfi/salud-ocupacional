<?php
class Models_Visual extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    public function listarcita( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_visualempleado' ) );
            if(isset($datos["p_idCompaniax"])) $select->where('a.idCompania = ?', $datos["p_idCompaniax"]);
            if(isset($datos["p_idLocalidadx"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidadx"]);
            $select->order('estado DESC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listarip( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_ip' ) );
            if(isset($datos["p_idIP"])) $select->where("a.idIP = ?", $datos["p_idIP"]);
            if(isset($datos["p_ip"]) && $datos["p_ip"]!= "") $select->where("a.ip LIKE '".$datos["p_ip"]."%'");
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function limpiar( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_offline ?');
            $stmt->bindParam(1, $datos["p_time"],  PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function registrarip( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_ip_registrar ?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idCompania"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idLocalidad"], PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_ip"], PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_descripcion"], PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarip( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_ip_modificar ?,?,?');
            
            $stmt->bindParam(1, $datos["p_idIP"],       PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_ip"],         PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_descripcion"],PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminarip( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_ip_eliminar ?');
            
            $stmt->bindParam(1, $datos["p_idIP"],       PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
}