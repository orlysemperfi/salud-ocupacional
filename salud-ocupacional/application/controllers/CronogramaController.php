<?php
require("PHPExcel/IOFactory.php");
class CronogramaController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->verificaPermiso(25);
        
        $_SESSION["CronogramaEditar"] = array();
        $modelCronograma = new Models_Cronograma();
        $modelCronograma->modificarestadocronograma();
        $modelCronograma->modificarestadocita();
        
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
        $this->verificaPermiso(25);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $datos["p_idCompania"] = $_GET["idCompania"];
        $datos["p_idLocalidad"] = $_GET["idLocalidad"];
        if(isset($_GET["fechainicio"]) && $_GET["fechainicio"] != "")
        $datos["p_fechainicio"] = $this->convierteFechaSinEspacio($_GET["fechainicio"]);
        if(isset($_GET["fechafin"]) && $_GET["fechafin"] != "")
        $datos["p_fechafin"] = $this->convierteFechaSinEspacio($_GET["fechafin"]);
        if(isset($_GET["motivo"]) && $_GET["motivo"] != 0)
        $datos["p_motivo"] = $_GET["motivo"];
        if(isset($_GET["estado"]) && $_GET["estado"] != 0)
        $datos["p_estado"] = $_GET["estado"];
        
        $modelCronograma = new Models_Cronograma();
        $rst = $modelCronograma->listar($datos);
        $i = 0;
        foreach ($rst as $r) {
            $response['rows'][$i]['id'] = array( $r["idCronograma"] );
            $response['rows'][$i]['cell'] = array(                    
                $r["idCronograma"],
                $this->convierteFechaaLatino($r["fechainicio"]),
                $this->convierteFechaaLatino($r["fechafin"]),
                $this->devuelveNombreMotivo($r["motivo"]), 
                $r["nrocitas"],
                $r["estado"], 
                $this->devuelveNombreEstadoCronograma($r["estado"]), 
                $r["nroespera"],
                ($r["nrocurso"] == 0)?$r["nroatendiendose"]:$r["nrocurso"],
                $r["nrocancelado"],
                $r["nronopresento"],
                $r["nrocerrado"],
                $r["porcompletado"]
            );
            $i++;
        }
        if($i == 0){
            $response['rows'][$i]['id'] = array( 0 );
            $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--");
        }
        $this->_helper->json( $response );
    }
    
    public function nuevoAction(){
        $this->verificaPermiso(26);
        
        $_SESSION["Reglas"] = array();
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
    public function eliminarAction(){
        $this->verificaPermiso(30);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();            
            $datos["p_idCronograma"] = $post["idCronograma"];
            $rst = $modelCronograma->eliminar($datos);

            $resultado["status"] = 1;
            $resultado["message"] = "El cronograma se ha eliminado correctamente.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function reglaobtenerAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(!isset($_SESSION["Reglas"])){
            $_SESSION["Reglas"] = array();
        }
	echo json_encode($_SESSION["Reglas"]);
    }
    public function reglanuevaAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $c = count($_SESSION["Reglas"]);
        $title = $_REQUEST["title"];
        $start = $_REQUEST["start"];
        $end = $_REQUEST["end"];
        $_SESSION["Reglas"][$c] = array(
                                    'id' => ($c+1),
                                    'title' => $title,
                                    'start' => $start,
                                    'end' => $end,
                                    'tipo' => "regla"
                                    );
        echo json_encode(array(
                            'id' => ($c+1),
                            'title' => $title,
                            'start' => $start,
                            'end' => $end,
                            'tipo' => "regla"
                            ));
    }
    public function reglaeliminarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(isset($_REQUEST["id"])){
            $id = $_REQUEST["id"];
            $_SESSION["Reglas"][$id-1] = array();
        }
    }
    public function reglalimpiarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(isset($_SESSION["Reglas"])){
            $_SESSION["Reglas"] = array();
        }
    }
    
    public function reglaobtenerdbAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            $data = array();
            $datos["p_idCronograma"] = $post["idCronograma"];
            $rest = $modelCronograma->listarregla($datos);
            $c = 0;
            foreach($rest as $index => $row){
                $rows["id"] = ($c+1);
                $rows["title"] = $row["descripcion"];
                $rows["start"] = $row["fechainicio"];
                $rows["end"] = $row["fechafin"];
                $rows["tipo"] = "regla";
                $rows["editable"] = false;
                $data[$c] = $rows;
                $c++;
            }
            echo json_encode($data);
        }
    }
    public function cronogramaobtenerAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            $_SESSION["CronogramaEditar"] = array();
            
            $datos["p_idCronograma"] = $post["idCronograma"];
            $restCronograma = $modelCronograma->listar($datos);
            $estado = $restCronograma[0]["estado"];
            $rest = $modelCronograma->listarregla($datos);
            $c = 0;
            foreach($rest as $index => $row){
                $row["id"] = ($c+1);
                $row["title"] = $row["descripcion"];
                $row["start"] = $row["fechainicio"];
                $row["end"] = $row["fechafin"];
                $row["tipo"] = "regla";
                $row["editable"] = false;
                $_SESSION["CronogramaEditar"][$c] = $row;
                $c++;
            }
            $datos["p_opcion"] = "citanombreempleado";
            $rest = $modelCronograma->listarcita($datos);
            foreach($rest as $index => $row){
                $row["id"] = ($c+1);
                $row["title"] = $row["descripcion"];
                $row["start"] = $row["fecha"];
                $row["end"] = $row["fecha"];
                $row["tipo"] = "usuario";
                if($row["estado"] == 1) $row["color"] = "#848485";
                if($row["estado"] == 2) $row["color"] = "#F6B724";
                if($row["estado"] == 3) $row["color"] = "#CC0000";
                if($row["estado"] == 4) $row["color"] = "#CCCCCC";
                if($row["estado"] == 5) $row["color"] = "#B2CC3E";
                if($row["estado"] == 6) $row["color"] = "#0099CC";
                if($estado == 1) $row["editable"] = true;
                else $row["editable"] = false;
                $_SESSION["CronogramaEditar"][$c] = $row;
                $c++;
            }
            echo json_encode($_SESSION["CronogramaEditar"]);
        }
    }
    public function obtenercorreosAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            
            $datos["p_idCompania"] = $post["selCompaniaUsuario"];
            $datos["p_idLocalidad"] = $post["selLocalidadUsuario"];
            $rest = $modelCronograma->obtenercorreo($datos);
            $correos = "";
            foreach($rest as $index => $row){
                $correos .= $row["correo"].", ";
            }
            echo $correos;
        }
    }
    public function cronogramaeventoeliminarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(isset($_REQUEST["id"])){
            $id = $_REQUEST["id"];
            $_SESSION["CronogramaEditar"][$id-1]["accion"] = "eliminar";
        }
    }
    public function cronogramamodificarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(isset($_REQUEST["id"])){
            $id = $_REQUEST["id"];
            $start = $_REQUEST["start"];
            $_SESSION["CronogramaEditar"][$id-1]["start"] = $start;
            $_SESSION["CronogramaEditar"][$id-1]["end"] = $start;
        }
    }
    public function cronogramaobtenereventoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
	echo json_encode($_SESSION["ReglasDep"]);
    }
    public function cronogramamodificareventoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if(isset($_REQUEST["id"])){
            $id = $_REQUEST["id"];
            $start = $_REQUEST["start"];
            $_SESSION["ReglasDep"][$id-1]["start"] = $start;
            $_SESSION["ReglasDep"][$id-1]["end"] = $start;
        }
    }
    public function generarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            unset($_SESSION["array"]["aux"]);
            unset($_SESSION["ReglasDep"]);
            
            $datos["p_idCompania"] = $post["selCompaniaUsuario"];
            $datos["p_idLocalidad"] = $post["selLocalidadUsuario"];
            $datos["p_fechaInicioRegla"] = $post["hdnFechaInicioRegla"];
            $datos["p_fechaFinRegla"] = $post["hdnFechaFinRegla"];
            $datos["p_tipo"] = $post["selTipo"];
            
            $totalEmpleados = count($post["groupcb"]);
            $x = explode("/",$post["hdnFechaInicioRegla"]);
            $y = explode("/",$post["hdnFechaFinRegla"]);            
            $totalDias = floor(abs((mktime(0,0,0,$x[1],$x[0],$x[2]) - mktime(4,12,0,$y[1],$y[0],$y[2])) / (60 * 60 * 24))) + 1; 
            $j = 0;
            foreach($_SESSION["Reglas"] as $row){
                $c = 0;
                $start = date("Ymd", strtotime($row['start']));
                $aux = date("Ymd", strtotime($row['start']." +$c day"));
                $end = date("Ymd", strtotime($row['end']));
                while($start <= $aux && $aux <= $end){
                    $array[$j] = substr($aux, 0, 4)."-".substr($aux, 4, 2)."-".substr($aux, 6, 2);
                    $c++; $j++;
                    $aux = date("Ymd", strtotime($row['start']." +$c day"));
                }
            }
            
            $c = $u = 0;
            foreach($_SESSION["array"]["dataEmpleadosAux"] as $index => $row){
                if (in_array($row["dni"], $post["groupcb"])) {
                    if($u >= $totalDias) $u = 0;
                    $start = date("Y-m-d", strtotime("$x[2]-$x[1]-$x[0] +$u day"));
                    foreach ($array as $r){
                        if (in_array($start, $array)){ $u++; }
                        if($u >= $totalDias) $u = 0;
                        $start = date("Y-m-d", strtotime("$x[2]-$x[1]-$x[0] +$u day"));
                    }
                    if(trim($row["iniciodescanso"]) != "" && trim($row["findescanso"]) != ""){
                        $cx = 0;
                        $jx = 0;
                        $arrayx = array();
                        $startx = date("Ymd", strtotime($row['iniciodescanso']));
                        $auxx = date("Ymd", strtotime($row['iniciodescanso']." +$cx day"));
                        $endx = date("Ymd", strtotime($row['findescanso']));
                        $xx = $x[2].$x[1].$x[0];
                        $yx = $y[2].$y[1].$y[0];
                        
                        if((int)$xx != (int)$startx || (int)$yx != (int)$endx){
                            while($startx <= $auxx && $auxx <= $endx){
                                $arrayx[$jx] = substr($auxx, 0, 4)."-".substr($auxx, 4, 2)."-".substr($auxx, 6, 2);
                                $cx++; $jx++;
                                $auxx = date("Ymd", strtotime($row['iniciodescanso']." +$cx day"));
                            }
                            foreach ($arrayx as $r){
                                if (in_array($start, $arrayx)){ $u++; }
                                if($u >= $totalDias) $u = 0;
                                $start = date("Y-m-d", strtotime("$x[2]-$x[1]-$x[0] +$u day"));                            
                            }
                            if($u >= $totalDias) $u = 0;
                            foreach ($array as $r){
                                if (in_array($start, $array)){ $u++; }
                                if($u >= $totalDias) $u = 0;
                                $start = date("Y-m-d", strtotime("$x[2]-$x[1]-$x[0] +$u day"));
                            }
                            $z = explode("-", $start);
                            $z = $z[0].$z[1].$z[2];
                            $xx = $x[2].$x[1].$x[0];
                            $yx = $y[2].$y[1].$y[0];
                            if((int)$xx <= (int)$z || (int)$yx >= (int)$z){
                                $row["id"] = ($c+1);
                                $row["title"] = $row["appaterno"]." ".$row["apmaterno"]." ".$row["nombres"];
                                $row["start"] = $start;
                                $row["end"] = $start;
                                $row["tipo"] = "usuario";
                                $row["color"] = "#0066CC";
                                $_SESSION["ReglasDep"][$c] = $row;
                                $c++;
                                if($totalEmpleados == $c) break;                        
                                $u++;
                            }
                        }
                    }else{
                        $z = explode("-", $start);
                        $z = $z[0].$z[1].$z[2];
                        $xx = $x[2].$x[1].$x[0];
                        $yx = $y[2].$y[1].$y[0];
                        if((int)$xx <= (int)$z || (int)$yx >= (int)$z){
                            $row["id"] = ($c+1);
                            $row["title"] = $row["appaterno"]." ".$row["apmaterno"]." ".$row["nombres"];
                            $row["start"] = $start;
                            $row["end"] = $start;
                            $row["tipo"] = "usuario";
                            $row["color"] = "#0066CC";
                            $_SESSION["ReglasDep"][$c] = $row;
                            $c++;
                            if($totalEmpleados == $c) break;                        
                            $u++;
                        }
                    }
                }
            }
            foreach($_SESSION["Reglas"] as $row){
                $row["id"] = $c+1;
                $row["editable"] = false;
                $_SESSION["ReglasDep"][$c] = $row;
                $c++;
            }
            echo json_encode($_SESSION["ReglasDep"]);
        }
    }
    public function registrarAction(){
        $this->verificaPermiso(26);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelUsuarioRRHH = new Models_UsuarioRRHH();
            $modelEmpleado = new Models_Empleado();
            $modelCronograma = new Models_Cronograma();
            
            $datos["p_correos"] = $post["txtCorreos"];
            $datos["p_motivo"] = $post["selMotivo"];
            $datos["p_tipo"] = $post["selTipo"];
            $datos["p_idCompania"] = $post["selCompaniaUsuario"];
            $datos["p_idLocalidad"] = $post["selLocalidadUsuario"];
            
            $datos["p_fechainicio"] = $this->convierteFecha($post["hdnFechaInicioRegla"]);
            $datos["p_fechafin"] = $this->convierteFecha($post["hdnFechaFinRegla"]);
            
            $rest = $modelCronograma->registrar($datos);
            $datos["p_idCronograma"] = $rest; 
            $mensajeRojo = "";
            $mensaje = "
            <div style='font-family: arial, tahoma; font-size: 11px;'>
            La siguiente lista es una relación de trabajadores que pasarán por los exámenes de Salud Ocupacional:<br /><br />
            <table width='100%' style='border:solid 1px #2E7667; border-collapse:collapse; font-size: 11px;'>
                <tr>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='4%' height='30'>Nro</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='30%'>Nombres</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='16%'>DNI</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Fecha de atención</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Puesto</td>
                </tr>
            ";
            $c = $a = 0;
            $fechamenor = 99991231;
            $fechamayor = 0;
            foreach($_SESSION["ReglasDep"] as $row){
                if($row["tipo"] == "usuario"){
                    $fechaaux = $this->convierteFechaSinEspacio($row["fechafvac"]);
                    if($fechaaux < $fechamenor){ $fechamenor = $fechaaux; $fechaICV = $row["fechafvac"]; }
                    if($fechaaux > $fechamayor){ $fechamayor = $fechaaux; $fechaFCV = $row["fechafvac"]; }
                    unset($datos["p_idEmpleado"]);
                    $datos["p_dni"] = trim($row["dni"]);
                    if(!$this->dameEmpleado($datos)){
                        $datos["p_appaterno"] = $row["appaterno"];
                        $datos["p_apmaterno"] = $row["apmaterno"];
                        $datos["p_nombres"] = $row["nombres"];
                        $datos["p_fechanacimiento"] = $this->convierteFecha($row["fechanacimiento"]);
                        $datos["p_deptnac"] = $row["deptnac"];
                        $datos["p_provnac"] = $row["provnac"];
                        $datos["p_distnac"] = $row["distnac"];
                        $datos["p_sexo"] = $row["sexo"];
                        $datos["p_fechaingreso"] = $this->convierteFecha($row["fechaingreso"]);
                        
                        $rstEmpleado = $modelEmpleado->registrar($datos);
                        
                        $datos["p_idEmpleado"] = $rstEmpleado;
                        $datos["p_fecha"] = $row["start"];
                        $datos["p_direccion"] = "";
                        $datos["p_puesto"] = utf8_decode($row["puesto"]);
                        $datos["p_telefono"] = $row["telefono"];
                        $datos["p_estadocivil"] = $row["estadocivil"];
                        $datos["p_gradoinstruccion"] = utf8_decode($row["gradoinstruccion"]);
                        $datos["p_rucempresaespecializada"] = $row["rucempresaespecializada"];
                        $datos["p_flgtipoempresa"] = $row["flgtipoempresa"];
                        $datos["p_area"] = $row["area"];
                        $datos["p_tipotrabajador"] = $row["tipotrabajador"];
                        $datos["p_centrocosto"] = $row["centrocosto"];
                        
                        $rstEmpleado = $modelCronograma->registrarcita($datos);
                    }else{
                        $rstEmpleado = $modelEmpleado->listar($datos);
                        $datos["p_idEmpleado"] = $rstEmpleado[0]["idEmpleado"];
                        $datos["p_fecha"] = $row["start"];
                        $datos["p_direccion"] = "";
                        $datos["p_puesto"] = utf8_decode($row["puesto"]);
                        $datos["p_telefono"] = $row["telefono"];
                        $datos["p_estadocivil"] = $row["estadocivil"];
                        $datos["p_gradoinstruccion"] = utf8_decode($row["gradoinstruccion"]);
                        $datos["p_rucempresaespecializada"] = $row["rucempresaespecializada"];
                        $datos["p_flgtipoempresa"] = $row["flgtipoempresa"];
                        $datos["p_area"] = $row["area"];
                        $datos["p_tipotrabajador"] = $row["tipotrabajador"];
                        $datos["p_centrocosto"] = $row["centrocosto"];
                        
                        $rstEmpleado = $modelCronograma->registrarcita($datos);
                    }
                    $c++;
                    $mensaje .= "
                <tr>
                    <td height='30'>$c</td>
                    <td>".$row["appaterno"]." ".$row["apmaterno"]." ".$row["nombres"]."</td>
                    <td>".$row["dni"]."</td>
                    <td>".$row["start"]."</td>
                    <td>".$row["puesto"]."</td>
                </tr>        
                    ";
                }else{
                    $datos["p_descripcion"] = utf8_decode($row["title"]);
                    $datos["p_fechainicio"] = $row["start"];
                    $datos["p_fechafin"] = $row["end"];
                    $rest = $modelCronograma->registrarregla($datos);
                    $a++;
                    $mensajeRojo .= "
                <tr>
                    <td height='30'>$a</td>
                    <td>".$row["title"]."</td>
                    <td>".$row["start"]."</td>
                    <td>".$row["end"]."</td>
                </tr>        
                    ";
                }
            }
            $mensaje .= "
            </table>";
            if($a != 0){
                $mensaje .= "<br /><br />
                La siguiente lista son las fechas en las cuales no habrá atención para los exámenes de Salud Ocupacional.
                <br /><br />
                <table width='100%' style='border:solid 1px #EC7E1B; border-collapse:collapse; font-size: 11px;'>
                    <tr>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='4%' height='30'>Nro</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='30%'>Motivo</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='16%'>Inicio</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='25%'>Fin</td>
                    </tr>
                    $mensajeRojo
                </table>";
            }
            
            $mensaje .= "<br /><br />
            Si existiera alguna observación respecto a las fechas asignadas para la atención de algún trabajador
            por favor informe inmediatamente para subsanar dicha observación.<br /><br/>
            Si no hubiera observación en 24 horas se tomará como aceptado dicho cronograma.<br /><br/>
            Atentamente<br /><br />
            Director del Hospital
            </div>
            ";
            if($datos["p_correos"] != ""){
                mail($datos["p_correos"], "Cronograma de citas: ".$this->devuelveNombreMotivo($datos["p_motivo"]), $mensaje,  "From:hospital@buenaventura.pe \nX-Mailer: Buenaventura\nContent-Type: text/html");
            }
            
            if($datos["p_motivo"] == 1){
                $datos["p_motivo"] = 6;
                
                $fechaICV = explode("/", $fechaICV);
                $fechaICV = $fechaICV[2]."/".$fechaICV[1]."/".$fechaICV[0];
                $fechaICV = date("d/m/Y", strtotime("$fechaICV +1 day"));                
                $datos["p_fechainicio"] = $this->convierteFecha($fechaICV);
                $fechaFCV = explode("/", $fechaFCV);
                $fechaFCV = $fechaFCV[2]."/".$fechaFCV[1]."/".$fechaFCV[0];
                $fechaFCV = date("d/m/Y", strtotime("$fechaFCV +1 day"));   
                $datos["p_fechafin"] = $this->convierteFecha($fechaFCV);

                $rest = $modelCronograma->registrar($datos);
                $datos["p_idCronograma"] = $rest; 
                $mensaje = "
                <div style='font-family: arial, tahoma; font-size: 11px;'>
                La siguiente lista es una relación de trabajadores que pasarán por los exámenes de Salud Ocupacional:<br /><br />
                <table width='100%' style='border:solid 1px #2E7667; border-collapse:collapse; font-size: 11px;'>
                    <tr>
                        <td bgcolor='#2E7667' style='color: #FFF;' width='4%' height='30'>Nro</td>
                        <td bgcolor='#2E7667' style='color: #FFF;' width='30%'>Nombres</td>
                        <td bgcolor='#2E7667' style='color: #FFF;' width='16%'>DNI</td>
                        <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Fecha de atención</td>
                        <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Puesto</td>
                    </tr>
                ";
                $c = $a = 0;
                foreach($_SESSION["ReglasDep"] as $row){
                    if($row["tipo"] == "usuario"){
                        unset($datos["p_idEmpleado"]);
                        $datos["p_dni"] = trim($row["dni"]);
                        $rstEmpleado = $modelEmpleado->listar($datos);
                        $datos["p_idEmpleado"] = $rstEmpleado[0]["idEmpleado"];
                        
                        $fechavac = explode("/", $row["fechafvac"]);
                        $fechavac = $fechavac[2]."/".$fechavac[1]."/".$fechavac[0];
                        $row["fechafvac"] = date("d/m/Y", strtotime("$fechavac +1 day"));
                        
                        $datos["p_fecha"] = $this->convierteFecha($row["fechafvac"]);
                        $datos["p_direccion"] = "";
                        $datos["p_puesto"] = utf8_decode($row["puesto"]);
                        $datos["p_telefono"] = $row["telefono"];
                        $datos["p_estadocivil"] = $row["estadocivil"];
                        $datos["p_gradoinstruccion"] = utf8_decode($row["gradoinstruccion"]);
                        $datos["p_rucempresaespecializada"] = $row["rucempresaespecializada"];
                        $datos["p_flgtipoempresa"] = $row["flgtipoempresa"];
                        $datos["p_area"] = $row["area"];
                        $datos["p_tipotrabajador"] = $row["tipotrabajador"];
                        $datos["p_centrocosto"] = $row["centrocosto"];
                        
                        $rest = $modelCronograma->registrarcita($datos);
                        $c++;
                        $mensaje .= "
                    <tr>
                        <td height='30'>$c</td>
                        <td>".$row["appaterno"]." ".$row["apmaterno"]." ".$row["nombres"]."</td>
                        <td>".$row["dni"]."</td>
                        <td>".$row["fechafvac"]."</td>
                        <td>".$row["puesto"]."</td>
                    </tr>        
                        ";
                    }
                }
                $mensaje .= "
                </table><br /><br />
                Si existiera alguna observación respecto a las fechas asignadas para la atención de algún trabajador
                por favor informe inmediatamente para subsanar dicha observación.<br /><br/>
                Si no hubiera observación en 24 horas se tomará como aceptado dicho cronograma.<br /><br/>
                Atentamente<br /><br />
                Director del Hospital
                </div>
                ";
                if($datos["p_correos"] != ""){
                    mail($datos["p_correos"], "Cronograma de citas: ".$this->devuelveNombreMotivo($datos["p_motivo"]), $mensaje,  "From:hospital@buenaventura.pe \nX-Mailer: Buenaventura\nContent-Type: text/html");
                }
            }
            unset($_SESSION["Reglas"]);
            unset($_SESSION["ReglasDep"]);
            unset($_SESSION["array"]["dataEmpleadosAux"]);
        }
    }
    public function enviarcronogramaparavalidarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            $datos["p_correos"] = $post["txtCorreos"];
            $datos["p_idCronograma"] = $post["hdnId"];
            
            $resCronograma = $modelCronograma->listar($datos);
            $resCitas = $modelCronograma->listarcita($datos);
            $resReglas = $modelCronograma->listarregla($datos);
            $mensajeRojo = "";
            $mensaje = "
            <div style='font-family: arial, tahoma; font-size: 11px;'>
            La siguiente lista es una relación de trabajadores que pasarán por los exámenes de Salud Ocupacional:<br /><br />
            <table width='100%' style='border:solid 1px #2E7667; border-collapse:collapse; font-size: 11px;'>
                <tr>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='4%' height='30'>Nro</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='30%'>Nombres</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='16%'>DNI</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Fecha de atención</td>
                    <td bgcolor='#2E7667' style='color: #FFF;' width='25%'>Puesto</td>
                </tr>
            ";
            $c = $a = 0;
            foreach($resCitas as $row){
                $c++;
                $mensaje .= "
                <tr>
                    <td height='30'>$c</td>
                    <td>".$row["appaterno"]." ".$row["apmaterno"]." ".$row["nombres"]."</td>
                    <td>".$row["dni"]."</td>
                    <td>".$this->convierteFecha($row["fecha"])."</td>
                    <td>".$row["puesto"]."</td>
                </tr>        
                    ";
            }
            foreach($resReglas as $row){
                $a++;
                $mensajeRojo .= "
                <tr>
                    <td height='30'>$a</td>
                    <td>".$row["descripcion"]."</td>
                    <td>".$this->convierteFecha($row["fechainicio"])."</td>
                    <td>".$this->convierteFecha($row["fechafin"])."</td>
                </tr>        
                    ";
            }
            $mensaje .= "
            </table>";
            if($a != 0){
                $mensaje .= "<br /><br />
                La siguiente lista son las fechas en las cuales no habrá atención para los exámenes de Salud Ocupacional.
                <br /><br />
                <table width='100%' style='border:solid 1px #EC7E1B; border-collapse:collapse; font-size: 11px;'>
                    <tr>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='4%' height='30'>Nro</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='30%'>Motivo</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='16%'>Inicio</td>
                        <td bgcolor='#EC7E1B' style='color: #FFF;' width='25%'>Fin</td>
                    </tr>
                    $mensajeRojo
                </table>";
            }
            
            $mensaje .= "<br /><br />
            Si existiera alguna observación respecto a las fechas asignadas para la atención de algún trabajador
            por favor informe inmediatamente para subsanar dicha observación.<br /><br/>
            Si no hubiera observación en 24 horas se tomará como aceptado dicho cronograma.<br /><br/>
            Atentamente<br /><br />
            Director del Hospital
            </div>
            ";
            if($datos["p_correos"] != ""){
                mail($datos["p_correos"], "Cronograma de citas: ".$this->devuelveNombreMotivo($resCronograma[0]["motivo"]), $mensaje,  "From:hospital@buenaventura.pe \nX-Mailer: Buenaventura\nContent-Type: text/html");
            }
            
            $resultado["status"] = 1;
            $resultado["message"] = "Se ha enviado el cronograma.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    public function cronogramaactualizarAction(){
        $this->verificaPermiso(27);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();
            foreach($_SESSION["CronogramaEditar"] as $row){
                if(isset($row["accion"]) && $row["accion"] == "eliminar"){
                    if($row["tipo"] == "usuario"){
                        $datos["p_idCita"] = $row["idCita"];
                        $rstEmpleado = $modelCronograma->eliminarcita($datos);
                    }elseif($row["tipo"] == "regla"){
                        $datos["p_idRegla"] = $row["idRegla"];
                        $rstEmpleado = $modelCronograma->eliminarregla($datos);
                    }
                }else{
                    if($row["tipo"] == "usuario"){
                        $datos["p_idCita"] = $row["idCita"];
                        $datos["p_fecha"] = $row["start"];
                        $rstEmpleado = $modelCronograma->modificarcita($datos);
                    }
                }
            }
            unset($_SESSION["CronogramaEditar"]);
        }
    }
    public function activarAction(){
        $this->verificaPermiso(29);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $modelCronograma = new Models_Cronograma();            
            $datos["p_idCronograma"] = $post["idCronograma"];
            $rsta = $modelCronograma->listar($datos);
            $datos["p_flg_activo"] = ($rsta[0]["estado"]==1)?2:1;
            if($rsta[0]["estado"]==1 || $rsta[0]["estado"]==2){
                $rst = $modelCronograma->activar($datos);
            }

            $resultado["status"] = 1;
            $resultado["message"] = "Se ha modificado el estado del cronograma.";
            
            $this->_helper->json( Zend_Json::encode( $resultado ) );
        }
    }
    
    
    public function recuperarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            unset($_SESSION["array"]["dataEmpleadosAux"]);
            if(isset($_GET["selTipo"]) && $_GET["selTipo"] != ""){
                switch($_GET["selTipo"]){
                    case 1:
                        $datos["p_tipo"] = $_GET["selTipo"];

                        $datos["p_idCompania"] = $_GET["selCompaniaUsuario"];
                        $datos["p_idLocalidad"] = $_GET["selLocalidadUsuario"];
                        
                        if(!isset($_GET["txtEdadLimite"])){
                            $fechai = explode("/", $_GET["txtFechaInicio"]);
                            $datos["p_fechainicio1"] = $fechai[2]."".$fechai[1]."".$fechai[0];
                            $datos["p_fechainicio2"] = $fechai[2]."-".$fechai[1]."-".$fechai[0];
                            $anioi = $fechai[2];

                            $fechaf = explode("/", $_GET["txtFechaFin"]);
                            $datos["p_fechafin1"] = $fechaf[2]."".$fechaf[1]."".$fechaf[0];
                            $datos["p_fechafin2"] = $fechaf[2]."-".$fechaf[1]."-".$fechaf[0];
                            $aniof = $fechaf[2];
                        }else{
                            unset($datos["p_tipo"]);
                            $datos["p_edad"] = $_GET["txtEdadLimite"];
                        }
                        
                        $modelUsuario = new Models_UsuarioRRHH();
                        $rst = $modelUsuario->listar($datos);

                        $i = 0;
                        foreach ($rst as $index => $r) {
                            $aux = $fechaia = $fechaf = "";
                            
                            $fecha = date("d/m/Y", strtotime($r["fechaingreso"]));
                            $fechanac = date("d/m/Y", strtotime($r["Fecha_Nacimiento"]));
                            if(!isset($_GET["txtEdadLimite"])){
                                $fechavac = $anioi."/".date("m/d", strtotime($r["fechaingreso"]));

                                if($anioi != $aniof){
                                    $mesf = date("m", strtotime($fechavac));
                                    if($mesf < 12) $aux = " +1 year";
                                }
                                if($r["Fe_Ini_Vacs"] != ""){ $fechai = date("d/m/Y", strtotime($r["Fe_Ini_Vacs"]));
                                }else{ $fechai = date("d/m/Y", strtotime($fechavac.$aux)); }
                                if($r["Fe_Fin_Vacs"] != ""){ $fechaf = date("d/m/Y", strtotime($r["Fe_Fin_Vacs"]));
                                }else{ $fechaf = date("d/m/Y", strtotime("$fechavac +1 month$aux")); }
                            }else{
                                $fechai = $r["edad"];
                                $fechaf = "años cumplidos";
                                $r["Fe_Ini_Vacs"] = "";
                            }
                            $q = 0; foreach($r as $val){ if($q == 14){ $r["telefono"] = $val; } $q++; }
                            
                            /* INICIO SESION DE DATA */
                            $_SESSION["array"]["dataEmpleadosAux"][$i] = array(
                                "idCompania" => $r["Id_cia"],
                                "idLocalidad" => $r["Id_Loc"],
                                "appaterno" => $r["Ap_Paterno"],
                                "apmaterno" => $r["Ap_Materno"],
                                "nombres" => $r["Nombres"],
                                "dni" => $r["DNI"],
                                "fechanacimiento" => $fechanac,
                                "sexo" => $r["Sexo"],
                                "fechaingreso" => $fecha,
                                "puesto" => $r["Puesto"],
                                "telefono" => $r["telefono"],
                                "estadocivil" => $r["Estado_Civil"],
                                "gradoinstruccion" => $r["Grado_Instruccion"],
                                "deptnac" => $r["dept_nac"],
                                "provnac" => $r["prov_nac"],
                                "distnac" => $r["dist_nac"],
                                "fechaivac" => $fechai,
                                "fechafvac" => $fechaf,
                                "flgtipoempresa" => $r["ind_contrata"],
                                "rucempresaespecializada" => $r["emp_espec"],
                                "area" => $r["Id_Area"],
                                "tipotrabajador" => $r["tipotrab"],
                                "centrocosto" => $r["centrocosto"],
                                "email" => $r["email"]
                            );
                            /* FIN SESION DE DATA */
                            
                            $response['rows'][$i]['id'] = array( $r["DNI"] );
                            $response['rows'][$i]['cell'] = array(                    
                                $r["DNI"],
                                $r["Nombres"],
                                $r["Ap_Paterno"] . " " . $r["Ap_Materno"], 
                                $r["DNI"], 
                                $fecha,
                                $r["Puesto"],
                                ($r["Fe_Ini_Vacs"] != "")?"Sí":"No",
                                $fechai . " - " . $fechaf
                            );
                            $i++;
                        }
                        if($i == 0){
                            $response['rows'][$i]['id'] = array( 0 );
                            $response['rows'][$i]['cell'] = array("--", "Sin registrosSRs", "--", "--", "--", "--", "--", "--", "--");
                        }
                        break;
                    case 2:
                        $i = 0;
                        unset($_SESSION["array"]["data"][1]);
                        foreach ($_SESSION["array"]["data"] as $r) {
                            if  (
                                       $r["A"] != "" //ApPaterno
                                    && $r["B"] != "" //ApMaterno
                                    && $r["C"] != "" //Nombres
                                    && $r["D"] != "" //DNI
                                    && $r["E"] != "" //Nacimiento
                                    && $r["F"] != "" //Sexo
                                    && $r["G"] != "" //Fecha ingreso  
                                    
                                    && $r["L"] != "" //Departamento
                                    && $r["M"] != "" //Provincia
                                    && $r["N"] != "" //Distrito
                                    
                                    && $r["Q"] != "" //Contrata
                                    && $r["R"] != "" //RUC Empresa
                                ){
                                $fechaia = $fechaf = "";
                                
                                $r["E"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["E"]));
                                $r["G"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["G"]));
                                
                                $fecha = date("d/m/Y", strtotime($r["G"]));
                                $fechanac = date("d/m/Y", strtotime($r["E"]));
                                $fechavac = date("Y")."/".date("m/d", strtotime($r["G"]));
                                
                                if($r["O"] != "" && $r["O"] != "NULL"){ 
                                    $r["O"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["O"]));
                                    $fechai = date("d/m/Y", strtotime($r["O"]));
                                }else $fechai = date("d/m/Y", strtotime($fechavac));
                                if($r["P"] != "" && $r["P"] != "NULL"){
                                    $r["P"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["P"]));
                                    $fechaf = date("d/m/Y", strtotime($r["P"]));
                                }else $fechaf = date("d/m/Y", strtotime("$fechavac +1 month"));
                                
                                if($r["S"] != "" && $r["S"] != "NULL"){ 
                                    $r["S"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["S"]));
                                    $fechaid = date("d/m/Y", strtotime($r["S"]));
                                }
                                if($r["T"] != "" && $r["T"] != "NULL"){
                                    $r["T"] = date("Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP($r["T"]));
                                    $fechafd = date("d/m/Y", strtotime($r["T"]));
                                }
                                
                                /* INICIO SESION DE DATA */
                                $_SESSION["array"]["dataEmpleadosAux"][$i] = array(
                                    "idCompania" => $_GET["selCompaniaUsuario"],
                                    "idLocalidad" => $_GET["selLocalidadUsuario"],
                                    "appaterno" => $r["A"],
                                    "apmaterno" => $r["B"],
                                    "nombres" => $r["C"],
                                    "dni" => $r["D"],
                                    "fechanacimiento" => $fechanac,
                                    "sexo" => $r["F"],
                                    "fechaingreso" => $fecha,
                                    "puesto" => $r["H"],
                                    "telefono" => $r["I"],
                                    "estadocivil" => $r["J"],
                                    "gradoinstruccion" => $r["K"],
                                    "deptnac" => $r["L"],
                                    "provnac" => $r["M"],
                                    "distnac" => $r["N"],
                                    "fechaivac" => $fechai,
                                    "fechafvac" => $fechaf,
                                    "flgtipoempresa" => $r["Q"],
                                    "rucempresaespecializada" => $r["R"],
                                    "iniciodescanso" => $r["S"],
                                    "findescanso" => $r["T"],
                                    "area" => $r["U"],
                                    "tipotrabajador" => $r["V"],
                                    "centrocosto" => $r["W"],
                                    "email" => ""
                                );
                                /* FIN SESION DE DATA */
                                
                                $response['rows'][$i]['id'] = array( $r["D"] );
                                $response['rows'][$i]['cell'] = array(                    
                                    $r["D"],
                                    $r["C"],
                                    $r["A"] . " " . $r["B"], 
                                    $r["D"], 
                                    $fecha,
                                    $r["H"],
                                    ($r["O"] != "NULL" && $r["O"] != "")?"Sí":"No",
                                    $fechai . " - " . $fechaf
                                );
                                $i++;
                            }
                        }
                        if($i == 0){
                            $response['rows'][$i]['id'] = array( 0 );
                            $response['rows'][$i]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--", "--", "--");
                        }
                        break;
                    case 3:
                        $datos["p_idCompania"] = $_GET["selCompaniaUsuario"];
                        $datos["p_idLocalidad"] = $_GET["selLocalidadUsuario"];
                        $datos["p_dni"] = $_GET["hdnDNI"];
                        $modelUsuario = new Models_UsuarioRRHH();
                        $rst = $modelUsuario->listar($datos);
                        $i = 0;
                        foreach ($rst as $r) {
                            $fechaia = $fechaf = "";
                            
                            $fecha = date("d/m/Y", strtotime($r["fechaingreso"]));
                            $fechanac = date("d/m/Y", strtotime($r["Fecha_Nacimiento"]));
                            $fechavac = date("Y")."/".date("m/d", strtotime($r["fechaingreso"]));
                                                        
                            $auxf = date("Ymd", strtotime("now"));
                            if($r["Fe_Ini_Vacs"] != ""){
                                $auxi = date("Ymd", strtotime($r["Fe_Ini_Vacs"]));                                    
                                if($auxi > $auxf) $fechai = date("d/m/Y", strtotime($r["Fe_Ini_Vacs"]));
                                else $fechai = date("d/m/Y", strtotime($r["Fe_Ini_Vacs"]." +1 year"));
                            }else{
                                $auxi = date("Ymd", strtotime($fechavac));                                    
                                if($auxi > $auxf) $fechai = date("d/m/Y", strtotime($fechavac));
                                else $fechai = date("d/m/Y", strtotime($fechavac." +1 year"));
                            }
                            if($r["Fe_Fin_Vacs"] != ""){
                                $auxi = date("Ymd", strtotime($r["Fe_Fin_Vacs"]));                                    
                                if($auxi > $auxf) $fechaf = date("d/m/Y", strtotime($r["Fe_Fin_Vacs"]));
                                else $fechaf = date("d/m/Y", strtotime($r["Fe_Fin_Vacs"]." +1 year"));
                            }else{
                                $auxi = date("Ymd", strtotime($fechavac));                                    
                                if($auxi > $auxf) $fechaf = date("d/m/Y", strtotime("$fechavac +1 month"));
                                else $fechaf = date("d/m/Y", strtotime("$fechavac +1 month +1 year"));
                            }
                                
                            $q = 0; foreach($r as $val){ if($q == 14){ $r["telefono"] = $val; } $q++; }
                            
                            /* INICIO SESION DE DATA */
                            $_SESSION["array"]["dataEmpleadosAux"][$i] = array(
                                "idCompania" => $r["Id_cia"],
                                "idLocalidad" => $r["Id_Loc"],
                                "appaterno" => $r["Ap_Paterno"],
                                "apmaterno" => $r["Ap_Materno"],
                                "nombres" => $r["Nombres"],
                                "dni" => $r["DNI"],
                                "fechanacimiento" => $fechanac, 
                                "sexo" => $r["Sexo"],
                                "fechaingreso" => $fecha,
                                "puesto" => $r["Puesto"],
                                "telefono" => $r["telefono"],
                                "estadocivil" => $r["Estado_Civil"],
                                "gradoinstruccion" => $r["Grado_Instruccion"],
                                "deptnac" => $r["dept_nac"],
                                "provnac" => $r["prov_nac"],
                                "distnac" => $r["dist_nac"],
                                "fechaivac" => $fechai,
                                "fechafvac" => $fechaf,
                                "flgtipoempresa" => $r["ind_contrata"],
                                "rucempresaespecializada" => $r["emp_espec"],
                                "area" => $r["Id_Area"],
                                "tipotrabajador" => $r["tipotrab"],
                                "centrocosto" => $r["centrocosto"],
                                "email" => $r["email"]
                            );
                            /* FIN SESION DE DATA */
                            
                            $response['rows'][$i]['id'] = array( $r["DNI"] );
                            $response['rows'][$i]['cell'] = array(                    
                                $r["DNI"],
                                $r["Nombres"],
                                $r["Ap_Paterno"] . " " . $r["Ap_Materno"], 
                                $r["DNI"], 
                                $fecha,
                                $r["Puesto"],
                                ($r["Fe_Ini_Vacs"] != "")?"Sí":"No",
                                $fechai . " - " . $fechaf
                            );
                            $i++;
                        }
                        if($i == 0){
                            $response['rows'][$i]['id'] = array( 0 );
                            $response['rows'][$i]['cell'] = array("--", "Sin registrosSR", "--", "--", "--", "--", "--", "--", "--");
                        }
                        break;
                    default:
                        $response['rows'][0]['id'] = array( 0 );
                        $response['rows'][0]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--", "--", "--");
                        break;
                }
            }else{
                $response['rows'][0]['id'] = array( 0 );
                $response['rows'][0]['cell'] = array("--", "Sin registros", "--", "--", "--", "--", "--", "--", "--");
            }
            $this->_helper->json( $response );
        }
    }
    public function subirarchivoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();    
        $error = $msg = "";
        $fileElementName = 'fileExcel';
        if(!empty($_FILES[$fileElementName]['error'])){
            switch($_FILES[$fileElementName]['error']){
                case '1': $error = 'El archivo ha excedido el peso permitido por el servidor.'; break;
                case '2': $error = 'El archivo ha excedido el peso permitido por el formulario.'; break;
                case '3': $error = 'El archivo no se ha cargado completamente.'; break;
                case '4': $error = 'El archivo no existe.'; break;
                case '6': $error = 'Falta la carpeta temporal.'; break;
                case '7': $error = 'No hay permisos para escribir el archivo.'; break;
                case '8': $error = 'El archivo tiene una extensión no válida.'; break;
                case '999':
                default: $error = 'Código de error no disponible.';
            }
        }elseif(empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none'){
            $error = 'El archivo no existe.';
        }else{
            $msg .= "Correcto";

            $destination_path = getcwd().DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR;
            $target_path = $destination_path . basename( $_FILES[$fileElementName]['name']);

            $type = $_FILES[$fileElementName]['type'];
            if($type == "application/vnd.ms-excel" || $type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                if(@move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $target_path)){
                    if($type == "application/vnd.ms-excel"){
                        $ext = 'xls';
                    }elseif($type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
                        $ext = 'xlsx';
                    }
                    $xlsx = 'Excel2007';
                    $xls  = 'Excel5';
                    //creando el lector
                    $objReader = PHPExcel_IOFactory::createReader($$ext);
                    $objPHPExcel = $objReader->load($target_path);
                    $dim = $objPHPExcel->getActiveSheet()->calculateWorksheetDimension();
                    list($start, $end) = explode(':', $dim); 
                    if(!preg_match('#([A-Z]+)([0-9]+)#', $start, $rslt)){ return false; }
                    list($start, $start_h, $start_v) = $rslt;
                    if(!preg_match('#([A-Z]+)([0-9]+)#', $end, $rslt)){ return false; }
                    list($end, $end_h, $end_v) = $rslt;
                    $error = "";
                    for($v=$start_v; $v<=$end_v; $v++){
                        for($h=$start_h; ord($h)<=ord($end_h); $this->pp($h)){
                            $cellValue = $this->get_cell($h.$v, $objPHPExcel);
                            if($cellValue !== null){
                                $array[$v][$h] = $cellValue;
                            }else{
                                $array[$v][$h] = "";
                            }
                        }
                    }
                    $_SESSION["array"]["data"] = "";
                    $_SESSION["array"]["tipo"] = "";
                    $_SESSION["array"]["tipo"] = $type;
                    $_SESSION["array"]["data"] = $array;
                    @unlink($target_path);
                }else{
                    $error = 'No se puede subir el archivo.';
                }
            }else{
                $error = 'El tipo de archivo no es un excel.';
            }
            @unlink($_FILES[$fileElementName]);		
        }
        $response["error"] = $error;
        $response["msg"] = $msg;
        echo json_encode($response);
    }
    function get_cell($cell, $objPHPExcel){
        $objCell = ($objPHPExcel->getActiveSheet()->getCell($cell));
        return $objCell->getvalue();
    }
    function pp(&$var){
        $var = chr(ord($var)+1);
        return true;
    }
}