<?php
class Models_Rol extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_rol' ) );
            if(isset($datos["p_idRol"])) $select->where('a.idRol = ?', $datos["p_idRol"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function listarrolmodulo( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_roldetalle' ) );
            if(isset($datos["p_idRol"])) $select->where('a.idRol = ?', $datos["p_idRol"]);
            if(isset($datos["p_idModulo"])) $select->where('a.idModulo = ?', $datos["p_idModulo"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function eliminar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_roldetalle_eliminar ?,?,?');                        
            $stmt->bindParam(1, $datos["p_idRol"], PDO::PARAM_STR);
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
    public function guardar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_roldetalle_registrar ?,?,?,?');                        
            $stmt->bindParam(1, $datos["p_idRol"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idModulo"],   PDO::PARAM_STR);
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
    public function registrar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_rol_registrar ?,?,?,?');                        
            $stmt->bindParam(1, $datos["p_nombre"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_descripcion"], PDO::PARAM_STR);
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
    public function modificar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_rol_modificar ?,?,?,?,?');                        
            $stmt->bindParam(1, $datos["p_idRol"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_nombre"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_descripcion"], PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(4, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(5, $data,                     PDO::PARAM_STR);
            $stmt->execute(); 
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminarrol( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_rol_eliminar ?,?,?');                        
            $stmt->bindParam(1, $datos["p_idRol"], PDO::PARAM_STR);
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
}