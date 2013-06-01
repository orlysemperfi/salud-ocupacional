<?php
class MaestroController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function moduloAction(){
        $this->verificaPermiso(45);
    }
    public function visualAction(){
        $this->verificaPermiso(45);
        $modelCompania = new Models_Compania();
        $rstListarCompania = $modelCompania->listar();
        $datos["p_idCompania"] = $_SESSION["Permisos"]["Generales"]["idCompania"];
        $this->view->idCompania = $datos["p_idCompania"];
        $this->view->rstListarCompania = $rstListarCompania;
    }
    
    public function listaripAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelCompania = new Models_Compania();
            $modelLocalidad = new Models_Localidad();
            $rstListarCompania = $modelCompania->listar();
            $rstListarLocalidad = $modelLocalidad->listar();
            
            $modelVisual = new Models_Visual();
            $rstListarIp = $modelVisual->listarip();
            $i = 0;
            foreach ($rstListarIp as $r) {
                $response['rows'][$i]['id'] = array( $r["idIP"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idIP"], 
                    $r["idCompania"], 
                    $r["idLocalidad"], 
                    $this->dameCompania($r["idCompania"], $rstListarCompania), 
                    $this->dameLocalidad($r["idCompania"],$r["idLocalidad"],$rstListarLocalidad), 
                    $r["ip"], 
                    $r["descripcion"]
                );
                $i++;
            }
            $this->_helper->json( $response );
        }
    }
    public function listarrolesAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {          
            $modelRol = new Models_Rol();
            $rst = $modelRol->listar();
            $i = 0;
            foreach ($rst as $r) {
                $response['rows'][$i]['id'] = array( $r["idRol"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idRol"], 
                    $r["nombre"], 
                    $r["descripcion"]
                );
                $i++;
            }
            $this->_helper->json( $response );
        }
    }
    public function listarmodulosAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $datos["p_idModuloPadre"] = 0;
            $modelModulo = new Models_Modulo();
            $modelRol = new Models_Rol();
            $rst = $modelModulo->listar($datos);
            $i = 0;
            foreach ($rst as $r) {
                $datos["p_idRol"] = (isset($_REQUEST["idRol"])?$_REQUEST["idRol"]:99999);
                $datos["p_idModulo"] = $r["idModulo"];
                $rstrm = $modelRol->listarrolmodulo($datos);
                $response['rows'][$i]['id'] = array( $r["idModulo"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idModulo"],
                    $r["idModuloPadre"],
                    $r["nombre"],
                    $r["descripcion"],
                    ((isset($rstrm[0]["flg_leer"]) && $rstrm[0]["flg_leer"] == 1)?1:0)
                );
                unset($datos["p_idRol"]);
                $i++;
                $datos["p_idModuloPadre"] = $r["idModulo"];
                $rsts = $modelModulo->listar($datos);
                foreach ($rsts as $rs) {
                    $datos["p_idRol"] = (isset($_REQUEST["idRol"])?$_REQUEST["idRol"]:99999);
                    $datos["p_idModulo"] = $rs["idModulo"];
                    $rstrm = $modelRol->listarrolmodulo($datos);
                    $response['rows'][$i]['id'] = array( $rs["idModulo"] );
                    $response['rows'][$i]['cell'] = array(                    
                        $rs["idModulo"],
                        $rs["idModuloPadre"], 
                        "∟ " . $rs["nombre"],
                        $rs["descripcion"],
                        ((isset($rstrm[0]["flg_leer"]) && $rstrm[0]["flg_leer"] == 1)?1:0)
                    );
                    $i++;
                    unset($datos["p_idRol"]);
                    $datos["p_idModuloPadre"] = $rs["idModulo"];
                    $rstss = $modelModulo->listar($datos);
                    foreach ($rstss as $rss) {
                        $datos["p_idRol"] = (isset($_REQUEST["idRol"])?$_REQUEST["idRol"]:99999);
                        $datos["p_idModulo"] = $rss["idModulo"];
                        $rstrm = $modelRol->listarrolmodulo($datos);
                        $response['rows'][$i]['id'] = array($rss["idModulo"]);
                        $response['rows'][$i]['cell'] = array(
                            $rss["idModulo"],
                            $rss["idModuloPadre"], 
                            "     ∟ " . $rss["nombre"],
                            $rss["descripcion"],
                            ((isset($rstrm[0]["flg_leer"]) && $rstrm[0]["flg_leer"] == 1)?1:0)
                        );
                        $i++;
                        unset($datos["p_idRol"]);
                    }
                    unset($datos["p_idModuloPadre"]);
                }
                unset($datos["p_idModuloPadre"]);
            }
            $this->_helper->json( $response );
        }
    }
    public function guardarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelRol = new Models_Rol();
            $datos["p_idRol"] = $post["idRol"];
            $rst = $modelRol->eliminar($datos);
            foreach($post["groupim"] as $row){
                $datos["p_idModulo"] = $row;
                $rst = $modelRol->guardar($datos);
                print_r($datos);
            }
            
        }
    }
    public function registrarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelRol = new Models_Rol();
            
            $datos["p_nombre"] = utf8_decode($post["txtNombre"]);
            $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);

            $rst = $modelRol->registrar($datos);
            $resultado["status"] = 1;
            $resultado["message"] = "El rol se ha grabado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function registraripAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelVisual = new Models_Visual();
            
            $datos["p_idCompania"] = $post["selCompaniaUsuario"];
            $datos["p_idLocalidad"] = $post["selLocalidadUsuario"];
            $datos["p_ip"] = $post["txtIP"];
            $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);

            $modelVisual->registrarip($datos);
            $resultado["status"] = 1;
            $resultado["message"] = "El IP se ha grabado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function modificarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelRol = new Models_Rol();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idRol"] = $post["hdnIdRol"];
            
            if($datos["p_opcion"]==1){
                $rst = $modelRol->listar($datos);
                $resultado = $rst[0];
            }else{
                $datos["p_idRol"] = $post["hdnIdRol"];
                $datos["p_nombre"] = utf8_decode($post["txtNombre"]);
                $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);
                $rst = $modelRol->modificar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El usuario se ha grabado correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function modificaripAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelVisual = new Models_Visual();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idIP"] = $post["hdnIdIP"];
            
            if($datos["p_opcion"]==1){
                $rst = $modelVisual->listarip($datos);
                $resultado = $rst[0];
            }else{
                $datos["p_idIP"] = $post["hdnIdIP"];
                $datos["p_ip"] = $post["txtIP"];
                $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);
                $modelVisual->modificarip($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "La ubicación se ha grabado correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function eliminarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelRol = new Models_Rol();            
            $datos["p_idRol"] = $post["idRol"];
            $rst = $modelRol->eliminarrol($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "El usuario se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function eliminaripAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelVisual = new Models_Visual(); 
            $datos["p_idIP"] = $post["idIP"];
            $modelVisual->eliminarip($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "La ubicación se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    
}
