<?php
class UsuarioController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->verificaPermiso(1);
        
        $modelRol = new Models_Rol();
        $rstListarRol = $modelRol->listar();
        $this->view->rstListarRol = $rstListarRol;
        
        $datos = array();
        $sesTU = $_SESSION["Zend_Auth"]["storage"];
        if($sesTU->flg_nivel == 2){
            $datos["p_idUsuario"] = $_SESSION["Permisos"]["Generales"]["idUsuario"];
            $modelUsuario = new Models_Usuario();
            $rstUsuarioRol = $modelUsuario->usuariorol($datos);
        }
        
        $modelCompania = new Models_Compania();
        $rstListarCompania = $modelCompania->listar($datos);
        $datos["p_idCompania"] = $_SESSION["Permisos"]["Generales"]["idCompania"];
        $this->view->idCompania = $datos["p_idCompania"];
        if($sesTU->flg_nivel == 1) $this->view->rstListarCompania = $rstListarCompania;
        if($sesTU->flg_nivel == 2) {
            $val = array();
            $c = 0;
            foreach($rstListarCompania as $rowa){
                foreach($rstUsuarioRol as $rowb){
                    if($rowa["idCompania"] == $rowb["idCompania"]){
                        $val[$c] = $rowa;
                        $c++;
                    }
                }
            }
            $this->view->rstListarCompania = $this->elimina_duplicados($val,'idCompania');
        }
        
        $modelLocalidad = new Models_Localidad();
        $rstListarLocalidad = $modelLocalidad->listar($datos);
        $datos["p_idLocalidad"] = $_SESSION["Permisos"]["Generales"]["idLocalidad"];
        $this->view->idLocalidad = $datos["p_idLocalidad"];
        if($sesTU->flg_nivel == 1) $this->view->rstListarLocalidad = $rstListarLocalidad;
        if($sesTU->flg_nivel == 2) {
            $val = array();
            $c = 0;
            foreach($rstListarLocalidad as $rowa){
                foreach($rstUsuarioRol as $rowb){
                    if($datos["p_idCompania"] == $rowb["idCompania"] && $rowa["idLocalidad"] == $rowb["idLocalidad"]){
                        $val[$c] = $rowa;
                        $c++;
                    }
                }
            }
            $this->view->rstListarLocalidad = $val;
        }
    }
    
    public function iniciarsesionAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $datos["p_usuario"] = $post["txtUsuario"];
            $datos["p_clave"] = $post["txtClave"];
            $modelUsuario = new Models_Usuario();
            $flgAuth = $modelUsuario->iniciarsesion($datos);
            if ($flgAuth){                
                $usuario = Zend_Auth::getInstance()->getIdentity();
                $pase = true;
                if($usuario->flg_reemplazo == 1){
                    $datos["p_activo"] = 1;
                    $datos["p_idUsuariox"] = $usuario->idUsuario;
                    $restUsuariox = $modelUsuario->listarsubgrid($datos);
                    if(isset($restUsuariox[0]["fechainicio"])){
                        $fechainicio = explode("-", $restUsuariox[0]["fechainicio"]);
                        $fechafin = explode("-", $restUsuariox[0]["fechafin"]);
                        $fechainicio = $fechainicio[0].$fechainicio[1].$fechainicio[2];
                        $fechafin = $fechafin[0].$fechafin[1].$fechafin[2];
                        $fechaactual = date("Ymd");                    
                        if($fechainicio <= $fechaactual && $fechafin >= $fechaactual){
                            $pase = true;
                        }else{
                            Zend_Auth::getInstance()->clearIdentity();
                            Zend_Session::namespaceUnset('Permisos');
                            $pase = false;
                        } 
                        if($fechafin < $fechaactual){
                            $datos["p_idUsuarioReemplazo"] = $restUsuariox[0]["idUsuarioReemplazo"];
                            $modelUsuario->estadoreemplazo($datos);
                        }
                    }else{
                        $pase = false;
                    }
                }
                if($pase){
                    $datos["p_idUsuario"] = $usuario->idUsuario;
                    $datos["p_isonline"] = 1;
                    $datos["p_time"] = time();
                    $rst = $modelUsuario->cambiarestado($datos);
                    
                    $datos["p_opcion"] = "flgprincipal";
                    $restUsuarioRol = $modelUsuario->usuariorol($datos);

                    $datos["p_idUsuarioRol"] = $restUsuarioRol[0]["idUsuarioRol"];
                    $datos["p_opcion"] = "iniciarsesion";
                    $restUsuarioPermiso = $modelUsuario->permisos($datos);

                    $permisos = new Zend_Session_Namespace('Permisos');
                    $permisos->Generales = new stdClass();
                    $permisos->Generales = $restUsuarioRol[0];

                    foreach($restUsuarioPermiso as $row){ 
                        $permisos->Especificos[$row["idModulo"]] = $row["flg_leer"];
                    }

                    $resultado["status"] = 1;
                    $resultado["message"] = "Ingresando...";
                }else{
                    $resultado["status"] = -1;
                    $resultado["message"] = "Este usuario no está activo.";
                }
            }else{
                $resultado["status"] = -1;
                $resultado["message"] = "Verifique que los datos ingresados sean correctos";
            }
        }else{
            $resultado["status"] = -1;
            $resultado["message"] = "Verifique que los datos ingresados sean correctos";
        }
        $this->_helper->json( Zend_Json::encode( $resultado ) );
    }
    public function cerrarsesionAction(){
        $modelUsuario = new Models_Usuario();
        $usuario = Zend_Auth::getInstance()->getIdentity();
        $datos["p_idUsuario"] = $usuario->idUsuario;
        $datos["p_isonline"] = 0;
        $datos["p_time"] = time();
        $rst = $modelUsuario->cambiarestado($datos);
        
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::namespaceUnset('Permisos');
        $this->_helper->redirector('index','index');
    }

    public function listarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelUsuario = new Models_Usuario();            
            $modelRol = new Models_Rol();
            $datos = array();
            $sesTU = $_SESSION["Zend_Auth"]["storage"];
            if(isset($_REQUEST["selUsuarios"]) && $_REQUEST["selUsuarios"] == 1) $datos["p_opcion"] = "usuariosnoreemplazo";
            if(isset($_REQUEST["selUsuarios"]) && $_REQUEST["selUsuarios"] == 2) $datos["p_opcion"] = "usuariossireemplazo";
            if(isset($_REQUEST["selActivos"]) && $_REQUEST["selActivos"] == 1) $datos["p_opcionaux"] = "siactivos";
            if(isset($_REQUEST["selActivos"]) && $_REQUEST["selActivos"] == 2) $datos["p_opcionaux"] = "noactivos";
            if(isset($_REQUEST["term"])) { 
                $datos["p_opcionaux"] = "autocomrem"; 
                $datos["p_nombres"] = $_REQUEST["term"];
            }
            if($sesTU->flg_nivel == 2){
                $datos["p_flg_nivel"] = $sesTU->flg_nivel;
            }
            $rst = $modelUsuario->listar($datos);
            
            if(isset($_REQUEST["term"])) {
                echo json_encode($rst);
            }else{
                $i = 0;
                foreach ($rst as $r) {
                    $datos["p_idUsuario"] = $r["idUsuario"];
                    $datos["p_opcion"] = "flgprincipal";
                    $rstAux = $modelUsuario->usuariorol($datos);
                    if($_REQUEST["idCompania"] == $rstAux[0]["idCompania"] && $_REQUEST["idLocalidad"] == $rstAux[0]["idLocalidad"]){
                        $datos["p_idCompania"] = $rstAux[0]["idCompania"];
                        $datos["p_idLocalidad"] = $rstAux[0]["idLocalidad"];
                        $datos["p_idRol"] = $rstAux[0]["idRol"];
                        $rstAuxR = $modelRol->listar($datos);
                        $datos["p_idCompania"] = $rstAux[0]["idCompania"];
                        $response['rows'][$i]['id'] = array( $r["idUsuario"] );
                        $response['rows'][$i]['cell'] = array(                    
                            $r["idUsuario"], 
                            $rstAux[0]["idCompania"], 
                            $rstAux[0]["idLocalidad"], 
                            $r["usuario"], 
                            $r["nombres"], 
                            $r["apellidos"],
                            $r["correo"],
                            $rstAuxR[0]["nombre"],
                            ($r["flg_activo"]=="0")?"No":"Sí"
                        );
                        unset($datos["p_idCompania"]);
                        unset($datos["p_idLocalidad"]);
                        $i++;
                    }
                }
                if($i == 0){
                    $response['rows'][$i]['id'] = array( 0 );
                    $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--", "--", "--");
                }
                $this->_helper->json( $response );
            }
        }
    }
    public function registrarAction(){
        $this->verificaPermiso(2);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            
            $datos["p_usuario"] = utf8_decode($post["txtUsuario"]);
            $rst = $modelUsuario->listar($datos);
            
            if(isset($rst[0]["usuario"])){
                $resultado["status"] = -1;
                $resultado["message"] = "El usuario que ingresó ya existe, ingrese otro por favor.";
            }else{
                $datos["p_idCompania"] = $post["selCompaniaUsuario"];
                $datos["p_idLocalidad"] = $post["selLocalidadUsuario"];
                $datos["p_clave"] = utf8_decode($post["txtClave"]);
                $datos["p_nombres"] = utf8_decode($post["txtNombres"]);
                $datos["p_apellidos"] = utf8_decode($post["txtApellidos"]);
                $datos["p_correo"] = utf8_decode($post["txtCorreo"]);
                $datos["p_flg_nivel"] = $post["selTipoUsuario"];
                $datos["p_idRol"] = $post["selRolUsuario"];
                $datos["p_dni"] = $post["txtDNI"];
                $datos["p_titulo"] = utf8_decode($post["txtTitulo"]);
                $datos["p_ncolegiatura"] = $post["txtNColegiatura"];
                $datos["p_flg_activo"] = 1;
                $datos["p_flg_principal"] = 1;
                
                $rst = $modelUsuario->registrar($datos);
                
                $resultado["status"] = 1;
                $resultado["message"] = "El usuario se ha grabado correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function modificarAction(){
        $this->verificaPermiso(3);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idUsuario"] = $post["hdnIdUsuario"];
            
            if($datos["p_opcion"]==1){
                $rst = $modelUsuario->listar($datos);
                $resultado = $rst[0];
            }else{
                $datos["p_clave"] = utf8_decode($post["txtClaveM"]);
                $datos["p_correo"] = utf8_decode($post["txtCorreo"]);
                $datos["p_nombres"] = utf8_decode($post["txtNombres"]);
                $datos["p_apellidos"] = utf8_decode($post["txtApellidos"]);
                $datos["p_dni"] = $post["txtDNI"];
                $datos["p_titulo"] = utf8_decode($post["txtTitulo"]);
                $datos["p_ncolegiatura"] = $post["txtNColegiatura"];
                $rst = $modelUsuario->modificar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El usuario se ha grabado correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function eliminarAction(){
        $this->verificaPermiso(9);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();            
            $datos["p_idUsuario"] = $post["idUsuario"];
            $rst = $modelUsuario->eliminar($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "El usuario se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function activarAction(){
        $this->verificaPermiso(7);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();            
            $datos["p_idUsuario"] = $post["idUsuario"];
            $rsta = $modelUsuario->listar($datos);
            $datos["p_flg_activo"] = ($rsta[0]["flg_activo"]==1)?0:1;
            $rst = $modelUsuario->activar($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "Se ha modificado el estado del usuario.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function permisoAction(){
        $this->verificaPermiso(4);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            $modelCompania = new Models_Compania();
            $modelLocalidad = new Models_Localidad();
            $modelModulo = new Models_Modulo();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idUsuario"] = $post["hdnIdUsuario"];
            
            if($datos["p_opcion"]==1){
                if(isset($post["hdnIdCompania"])){
                    $datos["p_idCompania"] = $post["hdnIdCompania"];
                    $datos["p_idLocalidad"] = $post["hdnIdLocalidad"];
                }
                $datos["p_opcion"] = "ascendente";
                
                $restUsuarioRol = $modelUsuario->usuariorol($datos);
                
                if(isset($post["hdnIdCompania"])){
                    unset($datos["p_idCompania"]);
                    unset($datos["p_idLocalidad"]);
                }
                $restCompania = $modelCompania->listar($datos);
                $restLocalidad = $modelLocalidad->listar($datos);
                $restModulo = $modelModulo->listar($datos);
                
                $permisos["Compania"] = $restCompania;
                $permisos["Localidad"] = $restLocalidad;
                $permisos["Modulo"] = $restModulo;
                
                if(count($restUsuarioRol)==0){
                    $c = 0;
                    $restUsuarioRol[$c]["idCompania"] = $post["hdnIdCompania"];
                    $restUsuarioRol[$c]["idLocalidad"] = $post["hdnIdLocalidad"];
                    $restUsuarioRol[$c]["idUsuario"] = $post["hdnIdUsuario"];
                    $restUsuarioRol[$c]["idRol"] = $post["hdnIdRol"];
                    $restUsuarioRol[$c]["idUsuarioRol"] = "";
                    $restUsuarioRol[$c]["flg_principal"] = 0;
                    $restUsuarioRol[$c]["flg_activo"] = 1;
                    
                    $restUsuarioRols = $modelUsuario->usuariorol($datos);
                    
                    $c = 1;
                    foreach($restUsuarioRols as $row){
                        $restUsuarioRol[$c]["idCompania"] = $row["idCompania"];
                        $restUsuarioRol[$c]["idLocalidad"] = $row["idLocalidad"];
                        $restUsuarioRol[$c]["idUsuario"] = $row["idUsuario"];
                        $restUsuarioRol[$c]["idRol"] = $row["idRol"];
                        $restUsuarioRol[$c]["idUsuarioRol"] = $row["idUsuarioRol"];
                        $restUsuarioRol[$c]["flg_principal"] = $row["flg_principal"];
                        $restUsuarioRol[$c]["flg_activo"] = $row["flg_activo"];
                        $c++;
                    }
                    
                    $permisos["Generales"] = $restUsuarioRol;
                    $permisos["Especificos"][1]["flg_leer"] = 0;
                    $permisos["Especificos"][1]["flg_escribir"] = 0;
                    $permisos["Especificos"][1]["flg_modificar"] = 0;
                    $permisos["Especificos"][1]["flg_eliminar"] = 0;
                }else{
                    $datos["p_idUsuarioRol"] = $restUsuarioRol[0]["idUsuarioRol"];
                    if(isset($post["hdnIdCompania"])){
                        $c = 0;
                        $restUsuarioRols = $restUsuarioRol;
                        $restUsuarioRol[$c]["idCompania"] = $restUsuarioRols[0]["idCompania"];
                        $restUsuarioRol[$c]["idLocalidad"] = $restUsuarioRols[0]["idLocalidad"];
                        $restUsuarioRol[$c]["idUsuario"] = $restUsuarioRols[0]["idUsuario"];
                        $restUsuarioRol[$c]["idRol"] = $restUsuarioRols[0]["idRol"];
                        $restUsuarioRol[$c]["idUsuarioRol"] = $restUsuarioRols[0]["idUsuarioRol"];
                        $restUsuarioRol[$c]["flg_principal"] = $restUsuarioRols[0]["flg_principal"];
                        $restUsuarioRol[$c]["flg_activo"] = $restUsuarioRols[0]["flg_activo"];
                        
                        $datos["p_idUsuarioRolNO"] = $restUsuarioRols[0]["idUsuarioRol"];
                        $datos["p_opcion"] = "usuariorolno";
                        $restUsuarioRols = $modelUsuario->usuariorol($datos);
                        if(count($restUsuarioRols)!=0){
                            $c = 1;
                            foreach($restUsuarioRols as $row){
                                $restUsuarioRol[$c]["idCompania"] = $row["idCompania"];
                                $restUsuarioRol[$c]["idLocalidad"] = $row["idLocalidad"];
                                $restUsuarioRol[$c]["idUsuario"] = $row["idUsuario"];
                                $restUsuarioRol[$c]["idRol"] = $row["idRol"];
                                $restUsuarioRol[$c]["idUsuarioRol"] = $row["idUsuarioRol"];
                                $restUsuarioRol[$c]["flg_principal"] = $row["flg_principal"];
                                $restUsuarioRol[$c]["flg_activo"] = $row["flg_activo"];
                                $c++;
                            }
                        }
                    }else{
                        $restUsuarioRol = $modelUsuario->usuariorol($datos);
                    }
                    $permisos["Generales"] = $restUsuarioRol;
                    
                    $datos["p_opcion"] = "iniciarsesion";
                    $restUsuarioPermiso = $modelUsuario->permisos($datos);
                    foreach($restUsuarioPermiso as $row){ 
                        $permisos["Especificos"][$row["idModulo"]]["flg_leer"] = $row["flg_leer"];
                        $permisos["Especificos"][$row["idModulo"]]["flg_escribir"] = $row["flg_escribir"];
                        $permisos["Especificos"][$row["idModulo"]]["flg_modificar"] = $row["flg_modificar"];
                        $permisos["Especificos"][$row["idModulo"]]["flg_eliminar"] = $row["flg_eliminar"];
                    }
                }
                $resultado = $permisos;
            }else{
                $datos["p_idCompania"] = $post["hdnIdCompania"];
                $datos["p_idLocalidad"] = $post["hdnIdLocalidad"];
                $datos["p_idUsuario"] = $post["hdnIdUsuario"];
                $datos["p_idRol"] = $post["hdnIdRol"];
                $datos["p_idUsuarioRol"] = $post["hdnIdUsuarioRol"];
                $datos["p_flg_principal"] = isset($post["chkPrincipal"])?1:0;
                $datos["p_flg_activo"] = 1;
                
                $restModulo = $modelModulo->listar($datos);
                $pase = false;
                foreach($restModulo as $row){
                    if(isset($post["permiso_".$row["idModulo"]."_1"]) || isset($post["permiso_".$row["idModulo"]."_2"]) ||
                       isset($post["permiso_".$row["idModulo"]."_3"]) || isset($post["permiso_".$row["idModulo"]."_4"])){
                        $pase = true;
                    }
                }
                if($pase){
                    $rst = $modelUsuario->permisosregistrar($datos);
                    $datos["p_idUsuarioRol"] = $rst;                
                    foreach($restModulo as $row){
                        $datos["p_idModulo"] = $row["idModulo"];
                        if(isset($post["permiso_".$row["idModulo"]."_1"])) $datos["p_flg_leer"] = 1;
                        else $datos["p_flg_leer"] = 0;
                        if(isset($post["permiso_".$row["idModulo"]."_2"])) $datos["p_flg_escribir"] = 1;
                        else $datos["p_flg_escribir"] = 0;
                        if(isset($post["permiso_".$row["idModulo"]."_3"])) $datos["p_flg_modificar"] = 1;
                        else $datos["p_flg_modificar"] = 0;
                        if(isset($post["permiso_".$row["idModulo"]."_4"])) $datos["p_flg_eliminar"] = 1;
                        else $datos["p_flg_eliminar"] = 0;
                        if(isset($post["permiso_".$row["idModulo"]."_1"]) || isset($post["permiso_".$row["idModulo"]."_2"]) ||
                           isset($post["permiso_".$row["idModulo"]."_3"]) || isset($post["permiso_".$row["idModulo"]."_4"])){
                            $rst = $modelUsuario->permisosregistrardetalle($datos);
                        }
                    }
                    $resultado["idUsuarioRol"] = $datos["p_idUsuarioRol"];
                    $resultado["status"] = 1;
                    $resultado["message"] = "El usuario se ha grabado correctamente.";
                }else{
                    if($datos["p_flg_principal"] == 0){
                        $rst = $modelUsuario->permisoseliminar($datos);
                        $resultado["status"] = 1;
                        $resultado["message"] = "El usuario se ha grabado correctamente.";
                        $resultado["idUsuarioRol"] = "";
                    }else{
                        $resultado["status"] = -1;
                        $resultado["message"] = "No puedes eliminar el rol principal.";
                        $resultado["idUsuarioRol"] = "";
                    }
                }
            }
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function asignarAction(){
        $this->verificaPermiso(6);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            
            $datos["p_usuario"] = utf8_decode($post["txtUsuario"]);
            $rst = $modelUsuario->listar($datos);
            $datos["p_idUsuario"] = $post["hdnIdUsuarioReemplazo"];
            $rstre = $modelUsuario->reemplazoestado($datos);
            if(isset($rstre[0]["flg_activoActual"]) && $rstre[0]["flg_activoActual"] == 1){
                $resultado["status"] = -1;
                $resultado["message"] = "El usuario tiene un acuerdo de reemplazo pendiente.";
            }elseif(isset($rst[0]["usuario"]) && $post["hdnIdUsuarioReemplazo"] == ""){
                $resultado["status"] = -1;
                $resultado["message"] = "El usuario que ingresó ya existe, ingrese otro por favor.";
            }else{
                unset($datos["p_usuario"]);                
                $datos["p_idUsuario"] = $post["hdnIdUsuario"];
                $rst = $modelUsuario->listar($datos);
                if($post["hdnIdUsuarioReemplazo"] != "" && isset($rst[0]["idUsuario"])){
                    $datos["p_idUsuario"] = $post["hdnIdUsuarioReemplazo"];
                    
                    $datos["p_flg_activo"] = $rst[0]["flg_activo"];
                    $datos["p_flg_nivel"] = $rst[0]["flg_nivel"];
                    $datos["p_idUsuarioOrigen"] = $rst[0]["idUsuario"];
                    
                    $fi = explode("/",$post["txtFechaInicio"]);
                    $datos["p_fechainicio"] = $fi[2]."-".$fi[1]."-".$fi[0];
                    $ff = explode("/",$post["txtFechaFin"]);
                    $datos["p_fechafin"] = $ff[2]."-".$ff[1]."-".$ff[0];
                    $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);
                    
                    $rstUsu = $modelUsuario->reemplazoregistrarexiste($datos);
                    $rst = $modelUsuario->usuariorol($datos);
                    unset($datos);
                    $datos["p_idUsuario"] = $post["hdnIdUsuarioReemplazo"];
                    foreach($rst as $row){
                        $datos["p_idCompania"] = $row["idCompania"];
                        $datos["p_idLocalidad"] = $row["idLocalidad"];
                        $datos["p_idRol"] = $row["idRol"];
                        $datos["p_idUsuarioRol"] = $row["idUsuarioRol"];
                        $datos["p_flg_principal"] = $row["flg_principal"];
                        $datos["p_flg_activo"] = $row["flg_activo"];
                        $rstUsu = $modelUsuario->reemplazoregistrarroles($datos);
                    }
                    $resultado["status"] = 1;
                    $resultado["message"] = "Se ha grabado con éxito el usuario de reemplazo.";
                }elseif(isset($rst[0]["idUsuario"])){
                    $datos["p_flg_activo"] = $rst[0]["flg_activo"];
                    $datos["p_flg_nivel"] = $rst[0]["flg_nivel"];                    
                    $datos["p_idUsuarioOrigen"] = $rst[0]["idUsuario"];                    
                    $datos["p_usuario"] = utf8_decode($post["txtUsuario"]);
                    $datos["p_clave"] = utf8_decode($post["txtClaveA"]);
                    $datos["p_nombres"] = utf8_decode($post["txtNombres"]);
                    $datos["p_apellidos"] = utf8_decode($post["txtApellidos"]);
                    $datos["p_dni"] = utf8_decode($post["txtDNI"]);
                    $datos["p_titulo"] = utf8_decode($post["txtTitulo"]);
                    $datos["p_ncolegiatura"] = utf8_decode($post["txtNColegiatura"]);
                    $datos["p_correo"] = utf8_decode($post["txtCorreo"]);
                    $fi = explode("/",$post["txtFechaInicio"]);
                    $datos["p_fechainicio"] = $fi[2]."-".$fi[1]."-".$fi[0];
                    $ff = explode("/",$post["txtFechaFin"]);
                    $datos["p_fechafin"] = $ff[2]."-".$ff[1]."-".$ff[0];
                    $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);
                    $datos["p_flg_reemplazo"] = 1;
                    
                    $rstUsu = $modelUsuario->reemplazoregistrar($datos);
                    $rst = $modelUsuario->usuariorol($datos);
                    
                    unset($datos);
                    $datos["p_idUsuario"] = $rstUsu;
                    foreach($rst as $row){
                        $datos["p_idCompania"] = $row["idCompania"];
                        $datos["p_idLocalidad"] = $row["idLocalidad"];
                        $datos["p_idRol"] = $row["idRol"];
                        $datos["p_idUsuarioRol"] = $row["idUsuarioRol"];
                        $datos["p_flg_principal"] = $row["flg_principal"];
                        $datos["p_flg_activo"] = $row["flg_activo"];
                        $rstUsu = $modelUsuario->reemplazoregistrarroles($datos);
                    }
                    $resultado["status"] = 1;
                    $resultado["message"] = "Se ha grabado con éxito el usuario de reemplazo.";
                }else{
                    $resultado["status"] = -1;
                    $resultado["message"] = "Ha ocurrido un error interno. Actualice la página para continuar.";
                }
            }
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function clonarAction(){
        $this->verificaPermiso(5);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            
            $datos["p_usuario"] = utf8_decode($post["txtUsuario"]);
            $rst = $modelUsuario->listar($datos);
            
            if(isset($rst[0]["usuario"])){
                $resultado["status"] = -1;
                $resultado["message"] = "El usuario que ingresó ya existe, ingrese otro por favor.";
            }else{
                unset($datos["p_usuario"]);
                $datos["p_idUsuario"] = $post["hdnIdUsuario"];
                $rst = $modelUsuario->listar($datos);
                if(isset($rst[0]["idUsuario"])){
                    $datos["p_flg_activo"] = $rst[0]["flg_activo"];
                    $datos["p_flg_nivel"] = $rst[0]["flg_nivel"];
                    $datos["p_idUsuarioOrigen"] = $rst[0]["idUsuario"];
                    $datos["p_usuario"] = utf8_decode($post["txtUsuario"]);
                    $datos["p_clave"] = utf8_decode($post["txtClaveC"]);
                    $datos["p_nombres"] = utf8_decode($post["txtNombres"]);
                    $datos["p_apellidos"] = utf8_decode($post["txtApellidos"]);
                    $datos["p_dni"] = utf8_decode($post["txtDNI"]);
                    $datos["p_titulo"] = utf8_decode($post["txtTitulo"]);
                    $datos["p_ncolegiatura"] = utf8_decode($post["txtNColegiatura"]);
                    $datos["p_correo"] = utf8_decode($post["txtCorreo"]);
                    $datos["p_flg_reemplazo"] = 0;
                    
                    $rstUsu = $modelUsuario->clonarregistrar($datos);
                    $rst = $modelUsuario->usuariorol($datos);
                    
                    unset($datos);
                    $datos["p_idUsuario"] = $rstUsu;
                    foreach($rst as $row){
                        $datos["p_idCompania"] = $row["idCompania"];
                        $datos["p_idLocalidad"] = $row["idLocalidad"];
                        $datos["p_idRol"] = $row["idRol"];
                        $datos["p_idUsuarioRol"] = $row["idUsuarioRol"];
                        $datos["p_flg_principal"] = $row["flg_principal"];
                        $datos["p_flg_activo"] = $row["flg_activo"];
                        $rstUsu = $modelUsuario->reemplazoregistrarroles($datos);
                    }
                    $resultado["status"] = 1;
                    $resultado["message"] = "Se ha grabado con éxito el usuario de reemplazo.";
                }else{
                    $resultado["status"] = -1;
                    $resultado["message"] = "Ha ocurrido un error interno. Actualice la página para continuar.";
                }
            }
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
        
    public function listarrrhhAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelUsuario = new Models_UsuarioRRHH();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_opcionaux"] = "autocomrem"; 
                $datos["p_nombres"] = $_REQUEST["term"];
                $datos["p_idCompania"] = $_REQUEST["compania"];
                $datos["p_idLocalidad"] = $_REQUEST["localidad"];
                $rst = $modelUsuario->listar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function listarsubgridAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelUsuario = new Models_Usuario();
            
            $datos["p_idUsuario"] = $_GET["p_idUsuario"];            
            $rst = $modelUsuario->listarsubgrid($datos);            
            $i = 0;
            foreach ($rst as $r) {
                $fecha = explode("-", $r["fechainicio"]);
                $r["fechainicio"] = $fecha[2]."/".$fecha[1]."/".$fecha[0];
                $fecha = explode("-", $r["fechafin"]);
                $r["fechafin"] = $fecha[2]."/".$fecha[1]."/".$fecha[0];
                
                $response['rows'][$i]['id'] = array( $r["idUsuarioReemplazo"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idUsuarioReemplazo"], 
                    $r["usuario"], 
                    $r["nombres"], 
                    $r["apellidos"],
                    $r["correo"],
                    $r["fechainicio"],
                    $r["fechafin"],
                    $r["descripcion"],
                    ($r["flg_activoActual"]=="0")?"No":"Sí",
                );
                $i++;
            }
            $this->_helper->json( $response );
        }
    }
    public function modificarreemplazoAction(){
        $this->verificaPermiso(10);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idUsuarioReemplazo"] = $post["hdnIdUsuarioReemplazo"];
            
            if($datos["p_opcion"]==1){
                $rst = $modelUsuario->listarsubgrid($datos);
                $resultado = $rst[0];
            }else{
                $fecha = explode("/", $post["txtFechaInicioM"]);
                $fecha = $fecha[2]."-".$fecha[1]."-".$fecha[0];
                $datos["p_fechainicio"] = $fecha;
                $fecha = explode("/", $post["txtFechaFinM"]);
                $fecha = $fecha[2]."-".$fecha[1]."-".$fecha[0];
                $datos["p_fechafin"] = $fecha;
                $datos["p_descripcion"] = utf8_decode($post["txtDescripcion"]);
                $rst = $modelUsuario->modificarreemplazo($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "Se han modificado los datos del reemplazo correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function eliminarreemplazoAction(){
        $this->verificaPermiso(11);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuario = new Models_Usuario();            
            $datos["p_idUsuarioReemplazo"] = $post["idUsuarioReemplazo"];
            $rst = $modelUsuario->eliminarreemplazo($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "El reemplazo se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function comprobarestadoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = Zend_Auth::getInstance()->getIdentity();
        $modelUsuario = new Models_Usuario();            
        $datos["p_idUsuario"] = $usuario->idUsuario;
        $rst = $modelUsuario->listar($datos);
        echo $rst[0]["isonline"];
    }
    public function cambiarestadoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = Zend_Auth::getInstance()->getIdentity();
        $modelUsuario = new Models_Usuario();            
        $datos["p_idUsuario"] = $usuario->idUsuario;
        $rst = $modelUsuario->listar($datos);
        $datos["p_isonline"] = (($rst[0]["isonline"] == 1)?0:1);
        $datos["p_time"] = time();
        $rst = $modelUsuario->cambiarestado($datos);
    }
    public function manteneractivoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = Zend_Auth::getInstance()->getIdentity();
        $modelUsuario = new Models_Usuario();            
        $datos["p_idUsuario"] = $usuario->idUsuario;
        $datos["p_isonline"] = 2;
        $datos["p_time"] = time();
        $rst = $modelUsuario->cambiarestado($datos);
    }
}
