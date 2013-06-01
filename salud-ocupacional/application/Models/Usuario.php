<?php
class Models_Usuario extends Models_Generico{	
    public function __construct(){
        $this->connectMSSQL();
    }
    
    public function iniciarsesion( $datos = array() ) {
        try{
            $db = $this->_dbMSSQL;
            $auth = Zend_Auth::getInstance();
            $authAdapter = new Zend_Auth_Adapter_DbTable($db);
            $select = $authAdapter->getDbSelect();
            $select->where('flg_activo = 1');
            $authAdapter->setTableName('view_usuario');
            $authAdapter->setIdentityColumn('usuario');
            $authAdapter->setCredentialColumn('clave');
            $authAdapter->setIdentity($datos["p_usuario"]);
            $authAdapter->setCredential($datos["p_clave"]);
            $result = $auth->authenticate($authAdapter);
            $isValid = $result->isValid();
            if( $isValid ){
                $data = $authAdapter->getResultRowObject(null,'clave');
                $auth->getStorage()->write($data);
            }
            return $isValid;
        } catch (Exception $e) {
            return -1;
        }
    }
    public function usuariorol( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_usuario_rol' ) );
            if(isset($datos["p_idUsuario"])) $select->where('a.idUsuario = ?', $datos["p_idUsuario"]);
            if(isset($datos["p_idCompania"])) $select->where('a.idCompania = ?', $datos["p_idCompania"]);
            if(isset($datos["p_idLocalidad"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidad"]);
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "flgprincipal"){ $select->where('a.flg_principal = 1'); }
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "ascendente"){ $select->order('a.idUsuarioRol ASC'); }
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "usuariorolno"){ $select->where('a.idUsuarioRol <> ?', $datos["p_idUsuarioRolNO"]); }
            $select->where('a.flg_activo = 1');
            $select->order('a.flg_principal DESC');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    
    public function listar( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_usuario' ) );
            if(isset($datos["p_idCompaniax"])) $select->where('a.idCompania = ?', $datos["p_idCompaniax"]);
            if(isset($datos["p_idLocalidadx"])) $select->where('a.idLocalidad = ?', $datos["p_idLocalidadx"]);
            if(isset($datos["p_isonlinex"])) $select->where('a.isonline = ?', $datos["p_isonlinex"]);
            if(isset($datos["p_flg_nivel"])) $select->where('a.flg_nivel = ?', $datos["p_flg_nivel"]);
            if(isset($datos["p_idUsuario"])) $select->where('a.idUsuario = ?', $datos["p_idUsuario"]);
            if(isset($datos["p_usuario"])) $select->where('a.usuario = ?', $datos["p_usuario"]);
            if(isset($datos["p_clave"])) $select->where('a.clave = ?', $datos["p_clave"]);
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "usuariosnoreemplazo"){ $select->where('a.flg_reemplazo = 0'); }
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "usuariossireemplazo"){ $select->where('a.flg_reemplazo = 1'); }
            if(isset($datos["p_opcionaux"]) && $datos["p_opcionaux"] == "siactivos"){ $select->where('a.flg_activo = 1'); }
            if(isset($datos["p_opcionaux"]) && $datos["p_opcionaux"] == "noactivos"){ $select->where('a.flg_activo = 0'); }
            if(isset($datos["p_opcionaux"]) && $datos["p_opcionaux"] == "autocomrem"){ 
                $select->where('a.flg_reemplazo = 1'); 
                $select->where("a.apellidos like '%".$datos["p_nombres"]."%'");
                $select->order("a.apellidos ASC");
            }
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
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_registrar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idCompania"],     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idLocalidad"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_idRol"],          PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_usuario"],        PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_clave"],          PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_nombres"],        PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_apellidos"],      PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_correo"],         PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_flg_nivel"],      PDO::PARAM_STR);
            $stmt->bindParam(10,$datos["p_flg_principal"],  PDO::PARAM_STR);
            $stmt->bindParam(11,$datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(12,$datos["p_dni"],            PDO::PARAM_STR);
            $stmt->bindParam(13,$datos["p_titulo"],         PDO::PARAM_STR);
            $stmt->bindParam(14,$datos["p_ncolegiatura"],   PDO::PARAM_STR);
            $stmt->bindParam(15,$datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(16,$idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(17,$data,                      PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_modificar ?,?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idUsuario"],  PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_clave"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_correo"],     PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_nombres"],    PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_apellidos"],  PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_dni"],        PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_titulo"],     PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_ncolegiatura"],PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["IND_OPERACION"],PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(10,$idUA,                  PDO::PARAM_STR);
            $stmt->bindParam(11,$data,                  PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_eliminar ?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idUsuario"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(3, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(4, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function activar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_activar ?,?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idUsuario"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(4, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(5, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function permisos( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_permiso' ) );
            if(isset($datos["p_opcion"]) && $datos["p_opcion"] == "iniciarsesion"){
                $select->where('a.idUsuarioRol = ?', $datos["p_idUsuarioRol"]);
            }
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $array;
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function permisosregistrar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_permisosregistrar ?,?,?,?,?,?,?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idCompania"],     PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idLocalidad"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_idUsuario"],      PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_idRol"],          PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_idUsuarioRol"],   PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_flg_principal"],  PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(9, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(10,$data,                      PDO::PARAM_STR);
            $stmt->execute();
            
            $array = $db->query("SELECT MAX(idUsuarioRol) AS total FROM view_usuario_rol")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array["total"];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function permisoseliminar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_permisoseliminar ?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idUsuarioRol"],   PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(3, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(4, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array["total"];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function permisosregistrardetalle( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_permisosregistrardetalle ?,?,?,?,?,?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idModulo"],       PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idUsuarioRol"],   PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_flg_leer"],       PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_flg_escribir"],   PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_flg_modificar"],  PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_flg_eliminar"],   PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(8, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(9, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array["total"];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function clonarregistrar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_clonarregistrar ?,?,?,?,?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_usuario"],        PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_clave"],          PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_nombres"],        PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_apellidos"],      PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_correo"],         PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_flg_nivel"],      PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_flg_reemplazo"],  PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_dni"],            PDO::PARAM_STR);
            $stmt->bindParam(10,$datos["p_titulo"],         PDO::PARAM_STR);
            $stmt->bindParam(11,$datos["p_ncolegiatura"],   PDO::PARAM_STR);
            $stmt->bindParam(12,$datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(13, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(14, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT MAX(idUsuario) AS total FROM usu_usuario")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    
    public function reemplazoestado( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_usuario_reemplazo' ) );
            if(isset($datos["p_idUsuario"])) $select->where('a.idUsuario = ?', $datos["p_idUsuario"]);
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function reemplazoregistrar( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_reemplazoregistrar ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_usuario"],        PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_clave"],          PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_nombres"],        PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_apellidos"],      PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_correo"],         PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_flg_nivel"],      PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["p_flg_reemplazo"],  PDO::PARAM_STR);
            $stmt->bindParam(9, $datos["p_fechainicio"],    PDO::PARAM_STR);
            $stmt->bindParam(10,$datos["p_fechafin"],       PDO::PARAM_STR);
            $stmt->bindParam(11,$datos["p_descripcion"],    PDO::PARAM_STR);
            $stmt->bindParam(12,$datos["p_idUsuarioOrigen"],PDO::PARAM_STR);
            $stmt->bindParam(13,$datos["p_dni"],            PDO::PARAM_STR);
            $stmt->bindParam(14,$datos["p_titulo"],         PDO::PARAM_STR);
            $stmt->bindParam(15,$datos["p_ncolegiatura"],   PDO::PARAM_STR);
            $stmt->bindParam(16,$datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(17, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(18, $data,                      PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT TOP 1 * FROM usu_usuario ORDER BY idUsuario DESC")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['idUsuario'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function reemplazoregistrarexiste( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_reemplazoregistrarexiste ?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idUsuario"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_flg_nivel"],      PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_flg_activo"],     PDO::PARAM_STR);            
            $stmt->bindParam(4, $datos["p_fechainicio"],    PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_fechafin"],       PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_descripcion"],    PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_idUsuarioOrigen"],PDO::PARAM_STR);            
            $stmt->bindParam(8, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(9, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(10, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function reemplazoregistrarroles( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_reemplazoregistrarroles ?,?,?,?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idUsuario"],      PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_idCompania"],     PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_idLocalidad"],    PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_idRol"],          PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["p_idUsuarioRol"],   PDO::PARAM_STR);
            $stmt->bindParam(6, $datos["p_flg_principal"],  PDO::PARAM_STR);
            $stmt->bindParam(7, $datos["p_flg_activo"],     PDO::PARAM_STR);
            $stmt->bindParam(8, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(9, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(10, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function modificarreemplazo( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_modificarreemplazo ?,?,?,?,?,?,?');
            
            $stmt->bindParam(1, $datos["p_idUsuarioReemplazo"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_fechainicio"],    PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_fechafin"],       PDO::PARAM_STR);
            $stmt->bindParam(4, $datos["p_descripcion"],    PDO::PARAM_STR);
            $stmt->bindParam(5, $datos["IND_OPERACION"],    PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(6, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(7, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function eliminarreemplazo( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_eliminarreemplazo ?,?,?,?');
                        
            $stmt->bindParam(1, $datos["p_idUsuarioReemplazo"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["IND_OPERACION"],        PDO::PARAM_STR);
            $idUA = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $data = implode(".-|-.", $datos);
            $stmt->bindParam(3, $idUA,                      PDO::PARAM_STR);
            $stmt->bindParam(4, $data,                     PDO::PARAM_STR);
            $stmt->execute();
            $array = $db->query("SELECT @@ROWCOUNT AS total")->fetch(PDO::FETCH_ASSOC);
            $db->closeConnection();
            return $array['total'];
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function listarsubgrid( $datos = array() ) {
        try{        
            $db = $this->_dbMSSQL;
            $select = $db->select()->from( array( 'a' => 'view_usuario_reemplazo' ) );
            if(isset($datos["p_idUsuario"])) $select->where('a.idUsuarioOrigen = ?', $datos["p_idUsuario"]);
            if(isset($datos["p_idUsuarioReemplazo"])) $select->where('a.idUsuarioReemplazo = ?', $datos["p_idUsuarioReemplazo"]);
            if(isset($datos["p_idUsuariox"])) $select->where('a.idUsuario = ?', $datos["p_idUsuariox"]);
            if(isset($datos["p_activo"])) $select->where('a.flg_activoActual = 1');
            $stmt = $db->prepare($select->assemble());
            $stmt->execute();
            $array = $stmt->fetchall();
            $db->closeConnection();
            return $this->convertAnsi2UTF8($array);
        } catch (Exception $e) {
            return -1;
        }        
    }
    public function estadoreemplazo( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_estadoreemplazo ?');
            
            $stmt->bindParam(1, $datos["p_idUsuarioReemplazo"], PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
    public function cambiarestado( $datos = array() ){
        try{
            $db = $this->_dbMSSQL;
            $stmt = $db->prepare('EXECUTE ep_usuario_cambiarestado ?,?,?');
            
            $stmt->bindParam(1, $datos["p_idUsuario"], PDO::PARAM_STR);
            $stmt->bindParam(2, $datos["p_isonline"], PDO::PARAM_STR);
            $stmt->bindParam(3, $datos["p_time"], PDO::PARAM_STR);
            $stmt->execute();
            $db->closeConnection();
            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return -1;
        }
    }
}
