<?php
class EmpleadoController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->verificaPermiso(12);
        
        $modelCronograma = new Models_Cronograma();
        $modelCronograma->modificarestadocronograma();
        $modelCronograma->modificarestadocita();
        
        $modelDepartamento = new Models_Departamento();
        $rstListarDepartamento = $modelDepartamento->listar("");
        $this->view->rstListarDepartamento = $rstListarDepartamento;
        
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
    public function listarAction(){
        $this->verificaPermiso(12);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $datos["p_idCompania"] = $_GET["idCompania"];
            $datos["p_idLocalidad"] = $_GET["idLocalidad"];
            
            $modelEmpleado = new Models_Empleado();
            $rst = $modelEmpleado->listar($datos);
            $i = 0; 
            foreach ($rst as $r) {
                $response['rows'][$i]['id'] = array( $r["idEmpleado"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idEmpleado"],
                    $r["appaterno"],
                    $r["apmaterno"],
                    $r["nombres"],
                    $r["dni"],
                    $this->convierteFechaaLatino($r["fechanacimiento"]),
                    $r["puesto"],
                    $this->devuelveNombreEstadoCivil($r["estadocivil"]), 
                    $this->devuelveNombreSexo($r["sexo"]),
                    $this->convierteFechaaLatino($r["fechaingreso"])
                );
                $i++;
            }
            if($i == 0){
                $response['rows'][$i]['id'] = array( 0 );
                $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--");
            }
            $this->_helper->json( $response );
        }
    }
    public function paginadoAction(){
        $this->verificaPermiso(12);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $datos["p_idCompania"] = $_REQUEST["idCompania"];
            $datos["p_idLocalidad"] = $_REQUEST["idLocalidad"];
            
            $post = array(
		'limit'=>(isset($_REQUEST['rows']))?$_REQUEST['rows']:'',
		'page'=>(isset($_REQUEST['page']))?$_REQUEST['page']:'',
		'orderby'=>(isset($_REQUEST['sidx']))?$_REQUEST['sidx']:'',
		'orden'=>(isset($_REQUEST['sord']))?$_REQUEST['sord']:''
            );
            
            $modelEmpleado = new Models_Empleado();
            $rst = $modelEmpleado->contar($datos);
            
            if(isset($_REQUEST["dni"]) && $_REQUEST["dni"] != "")
                $datos["p_dni"] = $_REQUEST["dni"];
            if(isset($_REQUEST["apellidos"]) && $_REQUEST["apellidos"] != "")
                $datos["p_nombres"] = $_REQUEST["apellidos"];
            
            $count = count($rst);
            if( $count > 0 && $post['limit'] > 0) {
                $total_pages = ceil($count/$post['limit']);
                if ($post['page'] > $total_pages) $post['page'] = $total_pages;
                $post['offset'] = $post['limit']*$post['page'] - $post['limit'];
            } else {
                $total_pages = 0;
                $post['page'] = 0;
                $post['offset'] = 0;
            }
            if( !empty($post['orden']) && !empty($post['orderby'])){
                $datos["orderby"] = $post["orderby"];
                $datos["orden"] = $post["orden"];
            }
            if(($post['limit']) && ($post['offset'])){
                $datos["offset"] = $post["page"];
                $datos["limit"] = $post["limit"];
            }elseif(($post['limit'])){
                $datos["offset"] = 1;
                $datos["limit"] = $post["limit"];
            }
            $i = 0;            
            $response["total"] = $total_pages;
            $response["page"] = $post['page'];
            $response["records"] = $count;
            $rst = $modelEmpleado->paginado($datos);  
            foreach ($rst as $r) {
                $response['rows'][$i]['id'] = array( $r["idEmpleado"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idEmpleado"],
                    $r["appaterno"],
                    $r["apmaterno"],
                    $r["nombres"],
                    $r["dni"],
                    $this->convierteFechaaLatino($r["fechanacimiento"]),
                    $r["puesto"],
                    $this->devuelveNombreEstadoCivil($r["estadocivil"]), 
                    $this->devuelveNombreSexo($r["sexo"]),
                    $this->convierteFechaaLatino($r["fechaingreso"])
                );
                $i++;
            }
            if($i == 0){
                $response['rows'][$i]['id'] = array( 0 );
                $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--");
            }
            $this->_helper->json( $response );
        }
    }
    public function registrarAction(){
        $this->verificaPermiso(13);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            $modelEmpleado = new Models_Empleado();
            $modelEmpresa = new Models_Empresa();
            
            $datos["p_idCompania"] = $post["hdnIdCompania"];
            $datos["p_idLocalidad"] = $post["hdnIdLocalidad"];
            
            $datos["p_appaterno"] = strtoupper($post["txtApellidoPaterno"]);
            $datos["p_apmaterno"] = strtoupper($post["txtApellidoMaterno"]);
            $datos["p_nombres"] = strtoupper($post["txtNombres"]);
            $datos["p_dni"] = $post["txtDNI"];
            $datos["p_fechanacimiento"] = $this->convierteFecha($post["txtFechaNacimiento"]);
            $datos["p_deptnac"] = $post["selDepartamento"];
            $datos["p_provnac"] = $post["selProvincia"];
            $datos["p_distnac"] = $post["selDistrito"];
            $datos["p_sexo"] = $post["rdSexo"];
            $datos["p_fechaingreso"] = $this->convierteFecha($post["txtFechaIngreso"]);
            
            $datos["p_motivo"] = $post["selMotivo"];
            $datos["p_fecha"] = $this->convierteFecha($post["txtFechaCita"]);
            $datos["p_puesto"] = strtoupper($post["txtPuesto"]);
            $datos["p_telefono"] = $post["txtTelefono"];
            $datos["p_gradoinstruccion"] = strtoupper($post["txtGradoInstruccion"]);
            $datos["p_estadocivil"] = $post["selEstadoCivil"];
            $datos["p_rucempresaespecializada"] = (trim($post["hdnRUC"]) == "")?"9999999999A":$post["hdnRUC"];
            $datos["p_flgtipoempresa"] = (isset($post["chkFlgTipoEmp"]))?1:0;
            $datos["p_area"] = $post["hdnArea"];
            $datos["p_tipotrabajador"] = $post["txtTipotrabajador"];
            $datos["p_centrocosto"] = $post["hdnCentrocosto"];
                
            $datos["p_fechainicio"] = $this->convierteFecha($post["txtFechaCita"]);
            $datos["p_fechafin"] = $this->convierteFecha($post["txtFechaCita"]);
            $datos["p_tipo"] = 3;
            $datos["p_correos"] = "";
            $datos["p_direccion"] = "";
            
            $pase = true;
            $rstEmpleado = $modelEmpleado->listar($datos); 
            if(isset($rstEmpleado[0]["nombres"])){
                $resultado["status"] = -1;
                $resultado["message"] = "El empleado que ingresó ya existe.";
                $pase = false;
            }
            $rstEmpresa = $modelEmpresa->listar($datos["p_rucempresaespecializada"]);
            if(!isset($rstEmpresa[0]["num_ruc"])){
                $resultado["status"] = -1;
                $resultado["message"] = "La empresa ingresada no existe.";
                $pase = false;
            }
            if($pase){
                $rst = $modelEmpleado->registrar($datos);
                $datos["p_idEmpleado"] = $rst;
                $rst = $modelCronograma->registrar($datos);
                $datos["p_idCronograma"] = $rst;
                $modelCronograma->registrarcita($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El usuario se ha grabado correctamente.";
            }
            $modelCronograma->modificarestadocronograma();
            $modelCronograma->modificarestadocita();
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function modificarAction(){
        $this->verificaPermiso(23);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelEmpleado = new Models_Empleado();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            $datos["p_idEmpleado"] = $post["hdnIdEmpleado"];
            
            if($datos["p_opcion"]==1){
                $rst = $modelEmpleado->listar($datos);
                $rst[0]["fechanacimiento"] = $this->convierteFechaaLatino($rst[0]["fechanacimiento"]);
                $rst[0]["fechaingreso"] = $this->convierteFechaaLatino($rst[0]["fechaingreso"]);
                $resultado = $rst[0];
            }else{
                $datos["p_idEmpleado"] = $post["hdnIdEmpleado"];
                $datos["p_appaterno"] = strtoupper($post["txtApellidoPaterno2"]);
                $datos["p_apmaterno"] = strtoupper($post["txtApellidoMaterno2"]);
                $datos["p_nombres"] = strtoupper($post["txtNombres2"]);
                $datos["p_dni"] = $post["txtDNI2"];
                $datos["p_fechanacimiento"] = $this->convierteFecha($post["txtFechaNacimiento2"]);
                $datos["p_deptnac"] = $post["selDepartamento2"];
                $datos["p_provnac"] = $post["selProvincia2"];
                $datos["p_distnac"] = $post["selDistrito2"];
                $datos["p_sexo"] = $post["rdSexo2"];
                $datos["p_fechaingreso"] = $this->convierteFecha($post["txtFechaIngreso2"]);
                
                $rst = $modelEmpleado->modificar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El registro se ha grabado correctamente.";
            }
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function eliminarAction(){
        $this->verificaPermiso(24);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelEmpleado = new Models_Empleado();            
            $datos["p_idEmpleado"] = $post["idEmpleado"];
            $rst = $modelEmpleado->eliminar($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "El registro se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    
    public function registrarcitaAction(){
        $this->verificaPermiso(22);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            $modelEmpresa = new Models_Empresa();
            $modelEmpleado = new Models_Empleado();
            
            $datos["p_idCompania"] = $post["hdnIdCompaniaNC"];
            $datos["p_idLocalidad"] = $post["hdnIdLocalidadNC"];
            $datos["p_idEmpleado"] = $post["hdnIdEmpleadoNC"];
                        
            $datos["p_motivo"] = $post["selMotivoNC"];
            $datos["p_fecha"] = $this->convierteFecha($post["txtFechaCitaNC"]);
            $datos["p_puesto"] = strtoupper(utf8_decode($post["txtPuestoNC"]));
            $datos["p_telefono"] = $post["txtTelefonoNC"];
            $datos["p_gradoinstruccion"] = strtoupper($post["txtGradoInstruccionNC"]);
            $datos["p_estadocivil"] = $post["selEstadoCivilNC"];
            $datos["p_rucempresaespecializada"] = (trim($post["hdnRUCNC"]) == "")?"9999999999A":$post["hdnRUCNC"];
            $datos["p_flgtipoempresa"] = (isset($post["chkFlgTipoEmpNC"]))?1:0;
            
            $datos["p_area"] = $post["hdnAreaNC"];
            $datos["p_tipotrabajador"] = $post["txtTipotrabajadorNC"];
            $datos["p_centrocosto"] = $post["hdnCentrocostoNC"];
            
            $datos["p_fechainicio"] = $this->convierteFecha($post["txtFechaCitaNC"]);
            $datos["p_fechafin"] = $this->convierteFecha($post["txtFechaCitaNC"]);
            $datos["p_tipo"] = 3;
            $datos["p_correos"] = "";
            $datos["p_direccion"] = "";
            
            $pase = true;
            $rstEmpresa = $modelEmpresa->listar($datos["p_rucempresaespecializada"]);
            if(!isset($rstEmpresa[0]["num_ruc"])){
                $resultado["status"] = -1;
                $resultado["message"] = "La empresa ingresada no existe.";
                $pase = false;
            }            
            $rstCita = $modelEmpleado->existecita($datos);
            if(isset($rstCita[0]["idCita"])){
                $resultado["status"] = -1;
                $resultado["message"] = "Ya existe una cita para este día con el mismo motivo.";
                $pase = false;
            }
            if($pase){
                $rst = $modelCronograma->registrar($datos);
                $datos["p_idCronograma"] = $rst;
                $modelCronograma->registrarcita($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "La cita se ha grabado correctamente.";
            }
            $modelCronograma->modificarestadocronograma();
            $modelCronograma->modificarestadocita();
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function paginadocitaAction(){
        $this->verificaPermiso(14);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $datos["p_idEmpleado"] = $_REQUEST["idEmpleado"];
            
            $post = array(
		'limit'=>(isset($_REQUEST['rows']))?$_REQUEST['rows']:'',
		'page'=>(isset($_REQUEST['page']))?$_REQUEST['page']:'',
		'orderby'=>(isset($_REQUEST['sidx']))?$_REQUEST['sidx']:'',
		'orden'=>(isset($_REQUEST['sord']))?$_REQUEST['sord']:''
            );
            
            $modelEmpleado = new Models_Empleado();
            $rst = $modelEmpleado->contarcita($datos);
            
            $count = count($rst);
            if( $count > 0 && $post['limit'] > 0) {
                $total_pages = ceil($count/$post['limit']);
                if ($post['page'] > $total_pages) $post['page'] = $total_pages;
                $post['offset'] = $post['limit']*$post['page'] - $post['limit'];
            } else {
                $total_pages = 0;
                $post['page'] = 0;
                $post['offset'] = 0;
            }
            if( !empty($post['orden']) && !empty($post['orderby'])){
                $datos["orderby"] = $post["orderby"];
                $datos["orden"] = $post["orden"];
            }
            if(($post['limit']) && ($post['offset'])){
                $datos["offset"] = $post["page"];
                $datos["limit"] = $post["limit"];
            }elseif(($post['limit'])){
                $datos["offset"] = 1;
                $datos["limit"] = $post["limit"];
            }
            $i = 0;            
            $response["total"] = $total_pages;
            $response["page"] = $post['page'];
            $response["records"] = $count;
            $rst = $modelEmpleado->paginadocita($datos);  
            foreach ($rst as $r) {
                $response['rows'][$i]['id'] = array( $r["idEmpleado"]."_".$r["idCita"] );
                $response['rows'][$i]['cell'] = array(                    
                    $r["idEmpleado"]."_".$r["idCita"],
                    $this->convierteFechaaLatino($r["fecha"]),
                    $r["motivo"],
                    $this->devuelveNombreMotivo($r["motivo"]),
                    $r["telefono"],
                    $this->devuelveNombreEstadoCivil($r["estadocivil"]), 
                    $this->devuelveNombreEstadoCita($r["estado"]),
                    $r["rucempresaespecializada"]
                );
                $i++;
            }
            if($i == 0){
                $response['rows'][$i]['id'] = array( 0 );
                $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--");
            }
            $this->_helper->json( $response );
        }
    }
    public function eliminarcitaAction(){
        $this->verificaPermiso(18);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $x = explode("_", $_POST["idCita"]);
            $datos["p_idCita"] = $x[1];
            $modelEmpleado = new Models_Cronograma();
            $modelEmpleado->eliminarcita($datos);
        }
    }
    public function cancelarcitaAction(){
        $this->verificaPermiso(16);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $x = explode("_", $_POST["idCita"]);
            $datos["p_idCita"] = $x[1];
            $modelEmpleado = new Models_Empleado();
            $modelEmpleado->cancelarcita($datos);
        }
    }
    public function iniciarcitaAction(){
        $this->verificaPermiso(15);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEmpleado = new Models_Empleado();
            $x = explode("_", $_POST["idCita"]);
            $datos["p_idCita"] = $x[1];
            if(!isset($_POST["imprimir"])){
                if(isset($_POST["estado"])) $datos["p_estado"] = $_POST["estado"]; else $datos["p_estado"] = 6;                 
                $modelEmpleado->iniciarcita($datos);
            }
            $datos["p_idEmpleado"] = $x[0];
            $rst = $modelEmpleado->listar($datos);
            echo $rst[0]["appaterno"]." ".$rst[0]["apmaterno"]." ".$rst[0]["nombres"]."||".$rst[0]["dni"];
        }
    }
    public function modificarcitaAction(){
        $this->verificaPermiso(17);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelEmpleado = new Models_Empleado();
            $modelEmpresa = new Models_Empresa();
            
            $datos["p_opcion"] = $post["hdnOpcion"];
            
            if($datos["p_opcion"]==1){
                $x = explode("_", $post["hdnIdCita"]);
                $datos["p_idCita"] = $x[1];
                $rst = $modelEmpleado->listarcita($datos);
                $rst[0]["nomempresa"] = $this->devuelveNombreEmpresa($rst[0]["rucempresaespecializada"]);
                $rst[0]["nomarea"] = $this->devuelveNombreArea($rst[0]["area"]);
                $rst[0]["nomcentrocosto"] = $this->devuelveNombreCentrocosto($rst[0]["centrocosto"]);
                $rst[0]["fecha"] = $this->convierteFechaaLatino($rst[0]["fecha"]);
                $rst[0]["fechainicio"] = $this->convierteFechaaLatino($rst[0]["fechainicio"]);
                $rst[0]["fechafin"] = $this->convierteFechaaLatino($rst[0]["fechafin"]);
                $resultado = $rst[0];
            }else{
                $datos["p_idCita"] = $post["hdnIdCita"];
                $datos["p_motivo"] = $post["hdnMotivo"];
                $datos["p_fecha"] = $this->convierteFecha($post["txtFechaCita2"]);
                $datos["p_puesto"] = strtoupper($post["txtPuesto2"]);
                $datos["p_telefono"] = $post["txtTelefono2"];
                $datos["p_gradoinstruccion"] = strtoupper($post["txtGradoInstruccion2"]);
                $datos["p_estadocivil"] = $post["selEstadoCivil2"];
                $datos["p_rucempresaespecializada"] = (trim($post["hdnRUC2"]) == "")?"9999999999A":$post["hdnRUC2"];
                $datos["p_flgtipoempresa"] = (isset($post["chkFlgTipoEmp2"]))?1:0;
                
                $datos["p_area"] = $post["hdnArea2"];
                $datos["p_tipotrabajador"] = $post["txtTipotrabajador2"];
                $datos["p_centrocosto"] = $post["hdnCentrocosto2"];
            
                $pase = true;
                $rstEmpresa = $modelEmpresa->listar($datos["p_rucempresaespecializada"]);
                if(!isset($rstEmpresa[0]["num_ruc"])){
                    $resultado["status"] = -1;
                    $resultado["message"] = "La empresa ingresada no existe.";
                    $pase = false;
                }
                $rstCita = $modelEmpleado->existecita($datos);
                if(count($rstCita[0]["idCita"])>1){
                    $resultado["status"] = -1;
                    $resultado["message"] = "Ya existe una cita para este día con el mismo motivo.";
                    $pase = false;
                }
                if($pase){
                    $rst = $modelEmpleado->modificarcita($datos);
                    $resultado["status"] = 1;
                    $resultado["message"] = "Se grabó correctamente el registro.";
                }
            }
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function tomarcitaAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelEmpleado = new Models_Empleado();
            $datos["p_idUsuario"] = $post["idUsuario"];            
            $rst = $modelEmpleado->tomarcita($datos);
            $rst[0]["nombreempresa"] = $this->devuelveNombreEmpresa($rst[0]["rucempresaespecializada"]);
            $rst[0]["nomarea"] = $this->devuelveNombreArea($rst[0]["area"]);
            $rst[0]["nomcentrocosto"] = $this->devuelveNombreCentrocosto($rst[0]["centrocosto"]);
            $resultado = $rst[0];
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function areaautocompletarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEmpleado = new Models_Empleado();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_descr_area"] = $_REQUEST["term"];
                $rst = $modelEmpleado->areaautocompletar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function centrocostoautocompletarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEmpleado = new Models_Empleado();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_descr_ccosto"] = $_REQUEST["term"];
                $rst = $modelEmpleado->centrocostocompletar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function arealistarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modelEmpleado = new Models_Area();
        $rst = $modelEmpleado->listar($_POST["term"]);
        echo (isset($rst[0]["descr_area"])?$rst[0]["descr_area"]:"");
    }
    public function centrocostolistarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modelEmpleado = new Models_Centrocosto();
        $rst = $modelEmpleado->listar($_POST["term"]);
        echo (isset($rst[0]["descr_ccosto"])?$rst[0]["descr_ccosto"]:"");
    }
    public function ruclistarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modelEmpleado = new Models_Empresa();
        $rst = $modelEmpleado->listar($_POST["term"]);
        echo $rst[0]["descr_ctta"];
    }
}
