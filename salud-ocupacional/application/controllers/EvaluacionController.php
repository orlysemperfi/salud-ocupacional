<?php
include("Pdf/mpdf.php");
class EvaluacionController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->verificaPermiso(31);
        
        $modelDepartamento = new Models_Departamento();
        $rstListarDepartamento = $modelDepartamento->listar("");
        $this->view->rstListarDepartamento = $rstListarDepartamento;
        
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
    public function paginadoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $datos["p_idCompania"] = $_REQUEST["idCompania"];
            $datos["p_idLocalidad"] = $_REQUEST["idLocalidad"];
            if(isset($_REQUEST["motivo"]) && $_REQUEST["motivo"] != "") $datos["p_motivo"] = $_REQUEST["motivo"];

            $modelEvaluacion = new Models_Evaluacion();
            $rst = $modelEvaluacion->paginado($datos);
            $i = 0; 
            foreach ($rst as $r) {
                $response['rows'][$i]['id'] = array( $r["idCita"] );
                $datos["p_idCita"] = $r["idCita"];
                $row = $modelEvaluacion->examencomprobar($datos);
                $row = $row[0];
                $response['rows'][$i]['cell'] = array(                    
                    $r["idCita"],
                    $r["idEmpleado"],
                    $r["motivo"],
                    $r["appaterno"],
                    $r["apmaterno"],
                    $r["nombres"],
                    $r["dni"],
                    $this->devuelveNombreMotivo($r["motivo"]),
                    $row["d1"]."-".$row["d2"]."-".$row["d3"]."-".$row["d4"]."-".$row["d5"]."-".$row["d6"]."-".
                    $row["d7"]."-".$row["d8"]."-".$row["d9"]."-".$row["d10"]."-".$row["d11"]."-".$row["d12"]
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
    public function historiaocupacionalAction(){
        $this->verificaPermiso(32);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
                $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
                $rst["empleado"][0]["fechainicio"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechainicio"]);
                $rst["empleado"][0]["areatrabajo"] = $this->devuelveNombreArea($rst["empleado"][0]["area"]);
                $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
                $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
                $rst["empleado"][0]["lugarnacimiento"] = 
                        $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                        $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                        $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
                if(trim($rst["empleado"][0]["direccion"]) == "")
                    $rst["empleado"][0]["direccion"] = 
                        $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                        $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                        $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
                $rst["historia"] = $modelEvaluacion->historiaocupacionallistar($datos);
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function historiaocupacionalgrabarAction(){
        $this->verificaPermiso(32);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $posts = $this->getRequest()->getPost();
                $post = $this->convierteamayusculas($posts);
                $datos["p_idCita"] = $post["hdnIdCitaHO"];
                $datos["p_nregistro"] = 0;
                $datos["p_direccion"] = $post["txtLugarProcedenciaHO"];
                
                $datos["p_idHistoria"] = $post["hdnIdHistoriaHO"];
                $datos["p_fechainicio"] = $post["txtFechaInicioHO"];
                $datos["p_empresa"] = $post["txtEmpresaHO"];
                $datos["p_altitud"] = $post["txtAltitudHO"];
                $datos["p_actividadempresa"] = $post["txtActividadesEmpresaHO"];
                $datos["p_areatrabajo"] = $post["txtAreaTrabajoHO"];
                $datos["p_ocupacion"] = $post["txtOcupacionHO"];
                $datos["p_ttsubsuelo"] = $post["txtTiempoTrabajoSubsueloHO"];
                $datos["p_ttsuperficie"] = $post["txtTiempoTrabajoSuperficieHO"];
                $datos["p_pelageocupacional"] = $post["txtPeligroAgenteOcupacionalesHO"];
                $datos["p_usotipoepp"] = $post["txtUsoEPPTipoEPPHO"];
                $modelEvaluacion = new Models_Evaluacion();
                $rst = $modelEvaluacion->historiaocupacionalgrabar($datos);
                $resultado["status"] = $rst;
                $resultado["message"] = "Ocurrió un error mientras se procesaba la información.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function fichaanexo7cAction(){
        $this->verificaPermiso(33);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->fichasieteccomprobar($datos);
                if(isset($eval[0]["idHistoria"])){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
                    $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
                    $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
                    $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
                    $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
                    $rst["empleado"][0]["motivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
                    $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
                    $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
                    $rst["empleado"][0]["lugarnacimiento"] = 
                            $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                            $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                            $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
                    
                    $rst["historia"] = $modelEvaluacion->historiaocupacionallistar($datos);
                    $rst["fichasietec"] = $modelEvaluacion->fichaanexo7clistar($datos);
                    $rst["fichasieted"] = $modelEvaluacion->fichaanexo7dlistar($datos);
                    $rst["rayosx"]      = $modelEvaluacion->rayosxlistar($datos);
                    $rst["laboratorio"] = $modelEvaluacion->laboratoriolistar($datos);
                    $rst["audiometria"] = $modelEvaluacion->audiometrialistar($datos);
                    $rst["espirometria"]= $modelEvaluacion->espirometrialistar($datos);
                    $rst["odontograma"] = $modelEvaluacion->odontogramalistar($datos);
                    $rst["optometria"]  = $modelEvaluacion->optometrialistar($datos);
                    if(isset($rst["optometria"][0])){
                        $rst["optometria"][0]["ovcscod"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovcscod"]);
                        $rst["optometria"][0]["ovcscoi"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovcscoi"]);
                        $rst["optometria"][0]["ovccod"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovccod"]);
                        $rst["optometria"][0]["ovccoi"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovccoi"]);
                        $rst["optometria"][0]["ovlscod"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlscod"]);
                        $rst["optometria"][0]["ovlscoi"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlscoi"]);
                        $rst["optometria"][0]["ovlcod"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlcod"]);
                        $rst["optometria"][0]["ovlcoi"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlcoi"]);
                    }
                    $rst["secretaria"] = 1;
                    if( isset($rst["fichasietec"][0]) && isset($rst["fichasieted"][0]) && isset($rst["rayosx"][0]) && isset($rst["laboratorio"][0]) &&
                        isset($rst["audiometria"][0]) && isset($rst["espirometria"][0]) && isset($rst["odontograma"][0]) && isset($rst["optometria"][0])){
                        $rst["secretaria"] = 0;
                    }
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function fichasietecgrabarAction(){
        $this->verificaPermiso(33);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["hdnIdCitaAF7C"];                
                $datos["p_idFichasietec"] = $post["hdnIdFicha7C"];
                $datos["p_arealabor"] = $post["radAreaLabor"];
                $datos["p_alturalabor"] = $post["hdnAlturaLabor"];
                $datos["p_gradoinstruccion"] = $post["radGradoInstruccion"];
                $datos["p_secretaria"] = $post["hdnSecretaria"];
                
                $c = 0;
                $cad = "";
                while($c < count($post["radOtros"])){
                    $x = $post["radOtros"][$c];
                    if($c != (count($post["radOtros"])-1)) $cad .= "$x-"; else $cad .= "$x";
                    $c++;
                }
                $datos["p_otros"] = $cad;
                
                $datos["p_reubicacion"] = $post["radReubica"];
                $datos["p_reinserccion"] = $post["radReinser"];
                $datos["p_habtabaco"] = $post["radHabitosTabaco"];
                $datos["p_habalcohol"] = $post["radHabitosAlcohol"];
                $datos["p_habdrogas"] = $post["radHabitosDrogas"];
                $datos["p_puestopostula"] = $post["txtPuestoPostula"];
                $datos["p_puestoactual"] = $post["txtPuestoActual"];
                $datos["p_antecedentesocupacionales"] = $post["txtAnteOcupa"];
                $datos["p_antecedentespersonales"] = $post["txtAntePerso"];
                $datos["p_antecedentesfamiliares"] = $post["txtAnteFamil"];
                $datos["p_nhijosvivos"] = $post["txtHijosVivos"];
                $datos["p_nhijosmuertos"] = $post["txtHijosMuertos"];
                
                $datos["p_inmunizaciones"] = $post["txtInmunizaciones"];
                $datos["p_evacabeza"] = $post["txtCabeza"];
                $datos["p_evanariz"] = $post["txtNariz"];
                $datos["p_evaboca"] = $post["txtBoca"];
                $datos["p_evaojos"] = $post["txtOjos"];
                $datos["p_evaenferoculares"] = $post["txtEnfoculares"];
                $datos["p_evareflejoculares"] = $post["txtRefoculares"];
                $datos["p_evavisioncolores"] = $post["txtVisioncolores"];
                $datos["p_evaotoscopiaderecho"] = $post["txtOtoscopiaod"];
                $datos["p_evaotoscopiaizquierdo"] = $post["txtOtoscopiaoi"];
                $datos["p_evapulmonesflg"] = $post["radPulmonesEstado"];//*
                $datos["p_evapulmonesdescr"] = $post["txtPulmones"];
                $datos["p_evamiembrossup"] = $post["txtMiemSup"];
                $datos["p_evamiembrosinf"] = $post["txtMiemInf"];
                $datos["p_evareflejososteo"] = $post["txtReflejososteo"];
                $datos["p_evamarcha"] = $post["txtMarcha"];
                $datos["p_evacolumnavertebral"] = $post["txtColumnavertebral"];
                $datos["p_evaabdomen"] = $post["txtAbdomen"];
                $datos["p_evatactorectal"] = isset($post["radTactorectal"])?$post["radTactorectal"]:"";//*
                $datos["p_evaanillosinguinales"] = $post["txtAnillosinguinales"];
                $datos["p_evahernias"] = $post["txtHernias"];
                $datos["p_evavarices"] = $post["txtVarices"];
                $datos["p_evaorganosgenitales"] = $post["txtOrganosgenitales"];
                $datos["p_evaganglios"] = $post["txtGanglios"];
                $datos["p_evalenguaje"] = $post["txtLenguaje"];
                $datos["p_evavertices"] = $post["txtVertices"];
                $datos["p_evacampospulmonares"] = $post["txtCampospulmo"];
                $datos["p_evahilios"] = $post["txtHilios"];
                $datos["p_evasenos"] = $post["txtSenos"];
                $datos["p_evamediastino"] = $post["txtMediastino"];
                $datos["p_evasiluetacardiaca"] = $post["txtSiluetacardiaca"];
                $datos["p_evaconclusionesradiograficas"] = $post["txtConcluradio"];
                $datos["p_evacalidad"] = $post["txtCalidadradio"];
                $datos["p_evasimbolos"] = $post["txtSimbolosradio"];
                $datos["p_evaradioneumoflg"] = isset($post["radNeumoconiosis"])?$post["radNeumoconiosis"]:"";//*
                $datos["p_evaradiodescr"] = $post["txtDescrneumoconiosis"];
                $datos["p_evaotrosexamenes"] = $post["txtOtrosexamenes"];
                $datos["p_evaaptotrabajar"] = isset($post["radAptotrabajar"])?$post["radAptotrabajar"]:""; //*
                $datos["p_evaobservaciones"] = $post["txtObservacioneseval"];
                $datos["p_evarecomendaciones"] = $post["txtRecomendacioneseval"];
                
                $datos = $this->convierteamayusculas($datos);
                $modelEvaluacion = new Models_Evaluacion();
                $rst = $modelEvaluacion->fichasietecgrabar($datos);                                              
                $resultado["status"] = $rst;
                
                $modelEmpleado = new Models_Empleado();
                $rst = $modelEmpleado->listarcita($datos);                                
                $resultado["fin"] = $rst[0]["estado"];  
                
                $resultado["message"] = "Ocurrió un error mientras se procesaba la información.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function fichasietecimprimirAction(){
        $this->verificaPermiso(33);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $html = '
        <style type="text/css">
        </style>
        <div>
            <div id="colwrap1">
                <div id="Div" class="bordeDer tituloFondoAli quitarPadTop2">Empresa</div>
                <div class="clearFloat"></div>
                <div id="Div2" class="bordeDer tituloFondoAli quitarPadTop2">Empresa Especializada</div>
                <div class="clearFloat"></div>
                <div id="Div3" class="bordeDer tituloFondoAli quitarPadTop2">Apellidos y nombres</div>
            </div>
            <div id="colwrap2">
                <div id="Div4" class="bordeDer quitarPadTop2">&nbsp;&nbsp;<span id="spanEmpresa"></span></div>
                <div class="clearFloat"></div>
                <div id="Div5" class="bordeDer quitarPadTop2">&nbsp;&nbsp;<span id="spanEmpresaEspecializada"></span></div>
                <div class="clearFloat"></div>
                <div id="Div6" class="bordeDer quitarPadTop2">&nbsp;&nbsp;<span id="spanApellidosNombres"></span></div>
            </div>
            <div style="float: right; width: 340px">
                <div id="Div7" class="bordeDer tituloFondoAli quitarPadTop2">N° de registro</div>
                <div id="Div8" class="quitarPadTop2">&nbsp;&nbsp;<span id="spanNRegistro"></span></div>
                <div class="clearFloat"></div>
            </div>
            <div style="float: right; width: 340px">
                <div id="Div9" class="bordeDer tituloFondoAli quitarPadTop2">Fecha</div>
                <div id="Div10" class="quitarPadTop2">&nbsp;&nbsp;<span id="spanFecha"></span></div>
                <div class="clearFloat"></div>
            </div>
            <div style="float: right; width: 340px">
                <div id="Div11" class="bordeDer tituloFondoAli quitarPadTop2">Examen médico</div>
                <div id="Div12" class="quitarPadTop2">&nbsp;&nbsp;<span id="spanExamenMedico">Ingreso</span></div>
            </div>
            <div class="clearFloat"></div>
            <div id="Div13"><span class="spanTituloForm">Detalle</span></div>
            <div class="clearFloat"></div>
            <div id="Div14" class="txtCen bordeDer">
                <strong>Lugar de nacimiento</strong><br /><span id="spanLugarNacimiento"></span><br />
                <strong>Fecha de nacimiento</strong><br /><span id="spanFechaNacimiento"></span>
            </div>
            <div id="Div15" class="txtCen bordeDer">
                <strong>Domicilio Habitual</strong><br /><span id="spanDomicilioHabitual"></span>
            </div>
            <div id="Div18" class="txtCen bordeDer"><strong>Edad</strong><br /><span id="spanEdad"></span></div>
            <div id="Div19" class="txtCen bordeDer"><strong>Sexo</strong><br /><span id="spanSexo"></span></div>
            <div id="Div20" class="txtCen bordeDer">
                <strong>Documento de identidad</strong><br /><span id="spanDNI"></span><br />
                <strong>Teléfono</strong><br /><span id="spanTelefono"></span>
            </div>
            <div id="Div21" class="txtCen"><strong>Estado Civil</strong><br /><span id="spanEstadoCivil"></span></div>
            <div class="clearFloat"></div>
            <div id="Div16" class="bordeDer">
                <strong style="display: block; text-align: center; height: 18px">Área de Labor</strong>
                <input type="radio" name="radAreaLabor" class="validate[required]" id="radAreaLaborSuperficie" value="1" /> <label for="radAreaLaborSuperficie">Superficie</label><br />
                <input type="radio" name="radAreaLabor" class="validate[required]" id="radAreaLaborConcentradora" value="2" /> <label for="radAreaLaborConcentradora">Concentradora</label><br />
                <input type="radio" name="radAreaLabor" class="validate[required]" id="radAreaLaborSubsuelo" value="3" /> <label for="radAreaLaborSubsuelo">Sub - suelo</label><br />
            </div>
            <div id="Div17" class="bordeDer">
                <strong style="display: block; text-align: center; height: 18px">Altura de Labor</strong>
                <div style="float: left; width: 50%">
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborA" value="1" /> <label for="radAlturaLaborA">Debajo de 2500</label><br />
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborB" value="2" /> <label for="radAlturaLaborB">2501 a 3000</label><br />
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborC" value="3" /> <label for="radAlturaLaborC">3001 a 3500</label><br />
                </div>
                <div style="float: left; width: 50%">
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborD" value="4" /> <label for="radAlturaLaborD">3500 a 4000</label><br />
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborE" value="5" /> <label for="radAlturaLaborE">4001 a 4500</label><br />
                    <input type="radio" name="radAlturaLabor" class="validate[required]" id="radAlturaLaborF" value="6" /> <label for="radAlturaLaborF">Más de 4501</label><br />
                </div>
            </div>
            <div id="Div22">
                <strong style="display: block; text-align: center; height: 18px">Grado de Instrucción</strong>
                <div style="float: left; width: 38%">
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionAnalfabeto" value="1" /> <label for="radGradoInstruccionAnalfabeto">Analfabeto</label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionPrimariaCompleta" value="2" /> <label for="radGradoInstruccionPrimariaCompleta">Primaria completa</label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionPrimariaIncompleta" value="3" /> <label for="radGradoInstruccionPrimariaIncompleta">Primaria incompleta</label><br />
                </div>
                <div style="float: left; width: 38%">
                    <input type="radio" name="" id="" value="" style="visibility: hidden" /> <label for=""></label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionSecundariaCompleta" value="4" /> <label for="radGradoInstruccionSecundariaCompleta">Segundaria completa</label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionSecundariaIncompleta" value="5" /> <label for="radGradoInstruccionSecundariaIncompleta">Secundaria incompleta</label><br />
                </div>
                <div style="float: left; width: 24%">
                    <input type="radio" name="" id="" value="" style="visibility: hidden" /> <label for=""></label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionTecnico" value="6" /> <label for="radGradoInstruccionTecnico">Técnico</label><br />
                    <input type="radio" name="radGradoInstruccion" class="validate[required]" id="radGradoInstruccionUniversitario" value="7" /> <label for="radGradoInstruccionUniversitario">Universitario</label><br />
                </div>
            </div>
            <div class="clearFloat"></div>
            <div id="Div23" class="bordeDer">
                <div style="float: left; width: 25%">
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros1" value="1" /> <label for="radOtros1">Ruido</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros2" value="2" /> <label for="radOtros2">Polvo</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros3" value="3" /> <label for="radOtros3">Vibración segmentaria</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros4" value="4" /> <label for="radOtros4">Vibración total</label><br />
                </div>
                <div style="float: left; width: 25%">
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros5" value="5" /> <label for="radOtros5">Cancerígenos</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros6" value="6" /> <label for="radOtros6">Mutagénicos</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros7" value="7" /> <label for="radOtros7">Solventes</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros8" value="8" /> <label for="radOtros8">Metales pesados</label><br />
                </div>
                <div style="float: left; width: 25%">
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros9" value="9" /> <label for="radOtros9">Temperaturas</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros10" value="10" /> <label for="radOtros10">Biológicos</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros11" value="11" /> <label for="radOtros11">Posturas</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros12" value="12" /> <label for="radOtros12">Turnos</label><br />
                </div>
                <div style="float: left; width: 25%">
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros13" value="13" /> <label for="radOtros13">Cargas</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros14" value="14" /> <label for="radOtros14">Movimientos repetitivos</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros15" value="15" /> <label for="radOtros15">PVD</label><br />
                    <input type="checkbox" name="radOtros[]" class="validate[required]" id="radOtros16" value="16" /> <label for="radOtros16">Otros</label><br />
                </div>
            </div>
            <div id="Div24">
                <strong>Describir según corresponda</strong><br />
                Puesto al que postula: <input type="text" id="txtPuestoPostula" name="txtPuestoPostula" maxlength="50" /><br />
                Puesto actual: <input type="text" id="txtPuestoActual" name="txtPuestoActual" maxlength="50" /><br />
                Reubicación: 
                <input type="radio" name="radReubica" id="radReubicaS" value="1" class="validate[required]" /> <label for="radReubicaS">Sí</label> 
                <input type="radio" name="radReubica" id="radReubicaN" value="2" class="validate[required]" /> <label for="radReubicaN">No</label>&nbsp;&nbsp;&nbsp;
                Reinsercción: 
                <input type="radio" name="radReinser" id="radReinserS" value="1" class="validate[required]" /> <label for="radReinserS">Sí</label> 
                <input type="radio" name="radReinser" id="radReinserN" value="2" class="validate[required]" /> <label for="radReinserN">No</label>
            </div>
            <div class="clearFloat"></div>
            <div id="Div25">
                <strong>Antecedentes Ocupacionales</strong><br />
                <textarea id="txtAnteOcupa" name="txtAnteOcupa" style="width: 960px; height: 37px;" class="validate[required]"></textarea>
            </div>
            <div class="clearFloat"></div>
            <div id="Div26">
                <strong>Antecedentes Personales</strong><br />
                <textarea id="txtAntePerso" name="txtAntePerso" style="width: 960px; height: 37px;" class="validate[required]"></textarea>
            </div>
            <div class="clearFloat"></div>
            <div id="Div27" class="bordeDer">
                <strong>Antecedentes Familiares</strong><br />
                <textarea id="txtAnteFamil" name="txtAnteFamil" style="width: 463px; height: 87px;" class="validate[required]"></textarea>
            </div>
            <div id="Div28" class="bordeDer">
                <strong>Hábitos</strong><br />
                <div style="float: left; width: 30%; line-height: 20px"><br />Nada<br />Poco<br />Habitual<br />Excesivo<br /></div>
                <div style="float: left; width: 20%" class="txtCen">Tabaco<br />
                    <input type="radio" name="radHabitosTabaco" id="radHabitosTabaco" value="1" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosTabaco" id="radHabitosTabaco" value="2" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosTabaco" id="radHabitosTabaco" value="3" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosTabaco" id="radHabitosTabaco" value="4" class="validate[required]" /><br />
                </div>
                <div style="float: left; width: 20%" class="txtCen">Alcohol<br />
                    <input type="radio" name="radHabitosAlcohol" id="radHabitosAlcohol" value="1" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosAlcohol" id="radHabitosAlcohol" value="2" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosAlcohol" id="radHabitosAlcohol" value="3" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosAlcohol" id="radHabitosAlcohol" value="4" class="validate[required]" /><br />
                </div>
                <div style="float: left; width: 20%" class="txtCen">Drogas <br />
                    <input type="radio" name="radHabitosDrogas" id="radHabitosDrogas" value="1" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosDrogas" id="radHabitosDrogas" value="2" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosDrogas" id="radHabitosDrogas" value="3" class="validate[required]" /><br />
                    <input type="radio" name="radHabitosDrogas" id="radHabitosDrogas" value="4" class="validate[required]" /><br />
                </div>
            </div>
            <div id="colwrap3">
                <div id="Div29" class="txtCen"><strong>Número de hijos</strong></div>
                <div class="clearFloat"></div>
                <div id="Div30" class="bordeDer txtCen"><strong>Vivos</strong></div>
                <div id="Div31" class="txtCen"><strong>Muertos</strong></div>
                <div class="clearFloat"></div>
                <div id="Div32" class="bordeDer txtCen">
                    <input type="text" id="txtHijosVivos" name="txtHijosVivos" maxlength="2" value="0" class="validate[required, custom[integer]]" style="width: 80% !Important" />
                </div>
                <div id="Div33" class="txtCen">
                    <input type="text" id="txtHijosMuertos" name="txtHijosMuertos" maxlength="2" value="0" class="validate[required, custom[integer]]" style="width: 80% !Important" />
                </div>
                <div class="clearFloat"></div>
            </div>
            <div class="clearFloat"></div>
            <input type="hidden" id="hdnSecretaria" name="hdnSecretaria" />
            <div id="divFicha7CEval">
                <div id="divane001" class="bordeDer">
                    <strong>Inmunizaciones</strong><br />
                    <textarea id="txtInmunizaciones" name="txtInmunizaciones" style="width: 250px; height: 65px;"></textarea>
                </div>
                <div id="colwrap1a">
                    <div id="divane003" class="txtCen bordeDer"><strong>Talla</strong><br /><span id="spanTalla"></span></div>
                    <div id="divane004" class="txtCen bordeDer"><strong>Peso</strong><br /><span id="spanPeso"></span></div>
                    <div class="clearFloat"></div>
                    <div id="divane005" class="txtCen bordeDer"><strong>IMC</strong><br /><span id="spanIMC"></span></div>
                </div>
                <div id="divane002" class="bordeDer">
                    <strong style="display: block; text-align: center; height: 18px">Función Respiratoria</strong>
                    <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">FVC:</span>
                    <span style="display: block; width: 120px; float: left" id="spanFVC"></span>
                    <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">FEV1:</span>
                    <span style="display: block; width: 120px; float: left" id="spanFEV1"></span>
                    <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">FEV1/FVC:</span>
                    <span style="display: block; width: 120px; float: left" id="spanFEV1FVC"></span>
                    <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">FEF 25 - 75%</span>
                    <span style="display: block; width: 120px; float: left" id="spanFEF2575"></span>
                </div>
                <div id="colwrap2b">
                    <div id="divane007" class="txtCen"><strong>Temperatura</strong><br /><span id="spanTemperatura"></span> °</div>
                    <div class="clearFloat"></div>
                    <div id="divane006">
                        <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">Cintura:</span>
                        <span style="display: block; width: 60px; float: left" id="spanCintura"></span>
                        <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">Cadera:</span>
                        <span style="display: block; width: 60px; float: left" id="spanCadera"></span>
                        <span style="display: block; width: 80px; float: left; font-weight: bold; padding-left: 10px;">ICC:</span>
                        <span style="display: block; width: 60px; float: left" id="spanICC"></span>
                    </div>
                </div>
                <div class="clearFloat"></div>
                <div id="divane008" class="bordeDer">
                    <strong>Cabeza</strong><br /><textarea id="txtCabeza" name="txtCabeza" style="width: 465px; height: 25px;">
Craneo: Normocéfalo.
Cabello color negro, delgado con adecuada implantación.</textarea>
                </div>
                <div id="divane016">
                    <strong>Nariz</strong><br /><textarea id="txtNariz" name="txtNariz" style="width: 465px; height: 25px;">
Central, piramidal; fosas nasales permeables, sin lesiones, no aleteo nasal.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane017" class="bordeDer">
                    <strong>Boca, amígdalas, faringe y laringe</strong><br />
                    <textarea id="txtBoca" name="txtBoca" style="width: 465px; height: 25px;">
Comisuras nasogeneanas simétricas, lengua y mucosa secas. </textarea>
                </div>
                <div id="divane020">
                    <span style="display: block; width: 130px; float: left; font-weight: bold; padding-top: 4px;">Piezas en mal estado:</span>
                    <span style="display: block; width: 340px; float: left; padding-top: 5px;" id="spanPiezasmalestado"></span>
                    <span style="display: block; width: 130px; float: left; font-weight: bold; padding-top: 4px;">Piezas que faltan:</span>
                    <span style="display: block; width: 340px; float: left; padding-top: 5px;" id="spanPiezasfaltan"></span>
                </div>
                <div class="clearFloat"></div>
                <div id="divane031" class="bordeDer">
                    <strong>Ojos</strong><br /><textarea id="txtOjos" name="txtOjos" style="width: 170px; height: 25px;">
Párpados normales no congestión ni edemas.</textarea>
                </div>
                <div id="colwrap3b">
                    <div id="divane034" class="txtCen bordeDer"><strong>Sin Corregir</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane036" class="txtCen bordeDer"><strong>OD</strong></div>
                    <div id="divane035" class="txtCen bordeDer"><strong>OI</strong></div>
                </div>
                <div id="colwrap4">
                    <div id="divane037" class="txtCen bordeDer"><strong>Corregidos</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane039" class="txtCen bordeDer"><strong>OD</strong></div>
                    <div id="divane038" class="txtCen bordeDer"><strong>OI</strong></div>
                </div>
                <div id="divane032">
                    <strong>Enfermedades oculares</strong><br />
                    <textarea id="txtEnfoculares" name="txtEnfoculares" style="width: 445px; height: 25px;">No presenta</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="colwrap5">
                    <div id="divane042" class="txtCen bordeDer"><strong>Visión de cerca</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane043" class="txtCen bordeDer"><strong>Visión de lejos</strong></div>
                </div>
                <div id="colwrap6">
                    <div id="divane041" class="txtCen bordeDer"><span id="spanVCSCOD"></span></div>
                    <div class="clearFloat"></div>
                    <div id="divane082" class="txtCen bordeDer"><span id="spanVLSCOD"></span></div>
                </div>
                <div id="colwrap7">
                    <div id="divane040" class="txtCen bordeDer"><span id="spanVCSCOI"></span></div>
                    <div class="clearFloat"></div>
                    <div id="divane081" class="txtCen bordeDer"><span id="spanVLSCOI"></span></div>
                </div>
                <div id="colwrap8">
                    <div id="divane080" class="txtCen bordeDer"><span id="spanVCCOD"></span></div>
                    <div class="clearFloat"></div>
                    <div id="divane084" class="txtCen bordeDer"><span id="spanVLCOD"></span></div>
                </div>
                <div id="colwrap9">
                    <div id="divane079" class="txtCen bordeDer"><span id="spanVCCOI"></span></div>
                    <div class="clearFloat"></div>
                    <div id="divane083" class="txtCen bordeDer"><span id="spanVLCOI"></span></div>
                </div>
                <div id="divane033">
                    <strong>Reflejos oculares</strong><br />
                    <textarea id="txtRefoculares" name="txtRefoculares" style="width: 445px; height: 25px;">Pupilas: CIRLE, reflejos normales.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane018">
                    Visión de colores: <input type="text" style="width: 870px;" id="txtVisioncolores" name="txtVisioncolores" maxlength="200" value="Test de Ishihara Adecuado" />
                </div>
                <div class="clearFloat"></div>
                <div id="divane044" class="txtCen bordeDer"><strong>Oídos</strong></div>
                <div id="divane110" class="bordeDer"></div>
                <div id="divane045" class="txtCen bordeDer"><strong>Audición derecha</strong></div>
                <div id="divane111" class="bordeDer"></div>
                <div id="divane046" class="txtCen bordeDer"><strong>Audición izquierda</strong></div>
                <div id="divane113"></div>
                <div class="clearFloat"></div>
                <div id="divane112" class="bordeDer"></div>
                <div id="colwrap10">
                    <div id="divane047" class="txtCen bordeDer"><strong>Hz</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane048" class="txtCen bordeDer"><strong>dB(A)</strong></div>
                </div>
                <div id="colwrap11">
                    <div id="divane049" class="txtCen bordeDer"><strong>500</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane050" class="txtCen bordeDer"><span id="spanAD500"></span></div>
                </div>
                <div id="colwrap12">
                    <div id="divane051" class="txtCen bordeDer"><strong>1000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane052" class="txtCen bordeDer"><span id="spanAD1000"></span></div>
                </div>
                <div id="colwrap13">
                    <div id="divane053" class="txtCen bordeDer"><strong>2000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane054" class="txtCen bordeDer"><span id="spanAD2000"></span></div>
                </div>
                <div id="colwrap14">
                    <div id="divane055" class="txtCen bordeDer"><strong>3000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane056" class="txtCen bordeDer"><span id="spanAD3000"></span></div>
                </div>
                <div id="colwrap15">
                    <div id="divane057" class="txtCen bordeDer"><strong>4000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane058" class="txtCen bordeDer"><span id="spanAD4000"></span></div>
                </div>
                <div id="colwrap16">
                    <div id="divane059" class="txtCen bordeDer"><strong>6000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane060" class="txtCen bordeDer"><span id="spanAD6000"></span></div>
                </div>
                <div id="colwrap17">
                    <div id="divane061" class="txtCen bordeDer"><strong>8000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane062" class="txtCen bordeDer"><span id="spanAD8000"></span></div>
                </div>
                <div id="divane114" class="bordeDer"></div>
                <div id="colwrap18">
                    <div id="divane063" class="txtCen bordeDer"><strong>Hz</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane064" class="txtCen bordeDer"><strong>dB(A)</strong></div>
                </div>
                <div id="colwrap19">
                    <div id="divane065" class="txtCen bordeDer"><strong>500</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane066" class="txtCen bordeDer"><span id="spanAI500"></span></div>
                </div>
                <div id="colwrap20">
                    <div id="divane067" class="txtCen bordeDer"><strong>1000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane068" class="txtCen bordeDer"><span id="spanAI1000"></span></div>
                </div>
                <div id="colwrap21">
                    <div id="divane069" class="txtCen bordeDer"><strong>2000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane070" class="txtCen bordeDer"><span id="spanAI2000"></span></div>
                </div>
                <div id="colwrap22">
                    <div id="divane071" class="txtCen bordeDer"><strong>3000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane072" class="txtCen bordeDer"><span id="spanAI3000"></span></div>
                </div>
                <div id="colwrap23">
                    <div id="divane073" class="txtCen bordeDer"><strong>4000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane074" class="txtCen bordeDer"><span id="spanAI4000"></span></div>
                </div>
                <div id="colwrap24">
                    <div id="divane075" class="txtCen bordeDer"><strong>6000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane076" class="txtCen bordeDer"><span id="spanAI6000"></span></div>
                </div>
                <div id="colwrap25">
                    <div id="divane077" class="txtCen bordeDer"><strong>8000</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane078" class="txtCen bordeDer"><span id="spanAI8000"></span></div>
                </div>
                <div id="divane115"></div>
                <div class="clearFloat"></div>
                <div id="divane019"></div>
                <div class="clearFloat"></div>
                <div id="divane009" class="bordeDer">
                    <strong>Otoscopía</strong><br />
                    <span style="display: block; width: 30px; float: left; font-weight: bold; padding-top: 4px;">OD:</span>
                    <span style="display: block; width: 530px; float: left">
                        <input type="text" style="width: 520px;" id="txtOtoscopiaod" name="txtOtoscopiaod" maxlength="300" value="CAE permeable, se observa tímpano normal." /></span>
                    <span style="display: block; width: 30px; float: left; font-weight: bold; padding-top: 4px;">OI:</span>
                    <span style="display: block; width: 530px; float: left">
                        <input type="text" style="width: 520px;" id="txtOtoscopiaoi" name="txtOtoscopiaoi" maxlength="300" value="CAE permeable, no congestión, se visualiza tímpano normal." /></span>
                </div>
                <div id="divane021" class="bordeDer">
                    <span style="display: block; width: 120px; float: left; font-weight: bold; padding-left: 10px;">F. Respiratoria:</span>
                    <span style="display: block; width: 40px; float: left" id="spanFR"></span> min
                    <span style="display: block; width: 120px; float: left; font-weight: bold; padding-left: 10px;">F. Cardiaca:</span>
                    <span style="display: block; width: 40px; float: left" id="spanFC"></span> min
                    <span style="display: block; width: 120px; float: left; font-weight: bold; padding-left: 10px;">Sat. O2:</span>
                    <span style="display: block; width: 40px; float: left" id="spanSAT"></span> %
                </div>
                <div id="divane030">
                    <strong style="display: block; text-align: center; height: 18px">Presión Arterial</strong>
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px;">Sistólica:</span>
                    <span style="display: block; width: 60px; float: left" id="spanSistolica"></span>
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px;">Diastólica:</span>
                    <span style="display: block; width: 60px; float: left" id="spanDiastolica"></span>
                </div>
                <div class="clearFloat"></div>
                <div id="divane010">
                    <strong>Pulmones:</strong>&nbsp;&nbsp;&nbsp;
                    Normal <input type="radio" name="radPulmonesEstado" id="chkPulmonesNormal" value="0" checked="checked" style="width: auto" /> 
                    Anormal <input type="radio" name="radPulmonesEstado" id="chkPulmonesAnormal" value="1" style="width: auto" /><br />
                    <textarea id="txtPulmones" name="txtPulmones" style="width: 960px; height: 25px;">
Tórax simétrico, amplexación conservada, no se palpan masas, Murmullo vesicular pasa bien en ambos campos pulmonares,no estertores ni sibilantes.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane011">
                    <strong>Miembros superiores:</strong><br />
                    <textarea id="txtMiemSup" name="txtMiemSup" style="width: 960px; height: 25px;">
Simetricas, no protesis, no hematomas, no edemas, amplitud de movimiento, tono y fuerza muscular conservados.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane012">
                    <strong>Miembros inferiores:</strong><br />
                    <textarea id="txtMiemInf" name="txtMiemInf" style="width: 960px; height: 25px;">
Simetricas, no protesis, no hematomas, no edemas, amplitud de movimiento, tono y fuerza muscular conservados.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane015" class="bordeDer">
                    <strong>Reflejos ósteo - tendinosos:</strong><br />
                    <textarea id="txtReflejososteo" name="txtReflejososteo" style="width: 680px; height: 25px;">
Normales. No atrofia muscular, buena tonicidad, no babinski.</textarea>
                </div>
                <div id="divane022">
                    <strong>Marcha:</strong><br />
                    <textarea id="txtMarcha" name="txtMarcha" style="width: 250px; height: 25px;">
Postura en DD activo; marcha activa.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane013">
                    <strong>Columna vertebral:</strong><br />
                    <textarea id="txtColumnavertebral" name="txtColumnavertebral" style="width: 960px; height: 25px;">
Sin dolor a la palpacion superficial y profunda en region dorso-lumbar y sacra.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane028" class="bordeDer">
                    <strong>Abdomen:</strong><br />
                    <textarea id="txtAbdomen" name="txtAbdomen" style="width: 680px; height: 25px;">
Blando, depresible, no distendido, timpanico, no puntos dolorosos  a la palpacion superficial ni profunda, RHA (++), no visceromegalia. </textarea>
                </div>
                <div id="divane029">
                    <strong style="display: block; text-align: center; height: 18px">Tacto rectal</strong>
                    <div style="float: left; width: 35%">
                        <input type="radio" name="radTactorectal" class="validate[required]" id="radTactorectalNosehizo" value="1" checked="checked" /> <label for="radTactorectalNosehizo">No se hizo</label><br />
                        <input type="radio" name="radTactorectal" class="validate[required]" id="radTactorectalNormal" value="2" /> <label for="radTactorectalNormal">Normal</label><br />
                    </div>
                    <div style="float: left; width: 65%">
                        <input type="radio" name="radTactorectal" class="validate[required]" id="radTactorectalAnormal" value="3" /> <label for="radTactorectalAnormal">Anormal</label><br />
                        <input type="radio" name="radTactorectal" class="validate[required]" id="radTactorectalDescribir" value="4" /> <label for="radTactorectalDescribir">Describir en observaciones</label><br />
                    </div>
                </div>
                <div class="clearFloat"></div>
                <div id="divane023" class="bordeDer">
                    <strong>Anillos inguinales:</strong><br />
                    <textarea id="txtAnillosinguinales" name="txtAnillosinguinales" style="width: 310px; height: 25px;">Presentes y no permeables.</textarea>
                </div>
                <div id="divane027" class="bordeDer">
                    <strong>Hernias:</strong><br />
                    <textarea id="txtHernias" name="txtHernias" style="width: 290px; height: 25px;">No presenta hernias inguinal, crural ni umbilical.</textarea>
                </div>
                <div id="divane025">
                    <strong>Várices:</strong><br />
                    <textarea id="txtVarices" name="txtVarices" style="width: 300px; height: 25px;">No se evidencian dilataciones varicosas en ningún miembro inferior.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane023a" class="bordeDer">
                    <strong>Organos genitales:</strong><br />
                    <textarea id="txtOrganosgenitales" name="txtOrganosgenitales" style="width: 478px; height: 25px;">Genitales externos normales de acuerdo a edad y sexo.</textarea>
                </div>
                <div id="divane027a">
                    <strong>Ganglios:</strong><br />
                    <textarea id="txtGanglios" name="txtGanglios" style="width: 448px; height: 25px;">No presenta adenopatias.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane014">
                    <strong>Lenguaje, atención, memoria, orientación, inteligencia y afectividad:</strong><br />
                    <textarea id="txtLenguaje" name="txtLenguaje" style="width: 960px; height: 25px;">Lucido, orientado en tiempo, espacio y persona. Se expresa adecuadamente. Memoria conservada.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="divane026" class="bordeDer">
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px; padding-top: 4px">N° registro:</span>
                    <span style="display: block; width: 120px; float: left; padding-top: 4px" id="spanNumregistro"></span>
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px; padding-top: 4px">Fecha:</span>
                    <span style="display: block; width: 120px; float: left; padding-top: 4px" id="spanFecharadio"></span>
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px; padding-top: 4px">Calidad:</span>
                    <span style="display: block; width: 120px; float: left">
                        <input type="text" style="width: 100px;" id="txtCalidadradio" name="txtCalidadradio" maxlength="10" class="validate[required]" />
                    </span>
                    <span style="display: block; width: 100px; float: left; font-weight: bold; padding-left: 10px; padding-top: 4px">Símbolos:</span>
                    <span style="display: block; width: 120px; float: left">
                        <input type="text" style="width: 100px;" id="txtSimbolosradio" name="txtSimbolosradio" maxlength="10" class="validate[required]" />
                    </span>
                </div>
                <div id="divane024">
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Vértices:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtVertices" name="txtVertices" maxlength="300" value="Libres" />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Campos pulmonares:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtCampospulmo" name="txtCampospulmo" maxlength="300" value="No se evidencia la presencia de masas, consolidación ni efusiones." />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Hilios:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtHilios" name="txtHilios" maxlength="300" value="Presentes y normales" />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Senos:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtSenos" name="txtSenos" maxlength="300" value="Presentes y libres " />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Mediastino:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtMediastino" name="txtMediastino" maxlength="300" value="Libres" />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Silueta cardiaca:</span>
                    <span style="display: block; width: 510px; float: left">
                        <input type="text" style="width: 510px;" id="txtSiluetacardiaca" name="txtSiluetacardiaca" maxlength="300" value="Normal" />
                    </span>
                    <span style="display: block; width: 200px; float: left; font-weight: bold; padding-left: 10px; padding-top: 6px">Conclusiones radiográficas:</span>
                    <textarea id="txtConcluradio" name="txtConcluradio" style="margin: 5px 0px 0px 10px; width: 710px; height: 90px;">Radiografía normal.</textarea>
                </div>
                <div class="clearFloat"></div>
                <div id="colwrap26">
                    <div id="divane085" class="txtCen bordeDer"><input type="radio" name="radNeumoconiosis" id="radNeumoconiosis1" value="1" checked="checked" class="validate[required]" /> <label for="radNeumoconiosis1"><strong>0/0</strong></label></div>
                    <div class="clearFloat"></div>
                    <div id="divane086" class="txtCen bordeDer"><strong>CERO</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane089" class="txtCen bordeDer">Sin Neumoconiosis<br /><strong>"NORMAL"</strong></div>
                    <div class="clearFloat"></div>
                </div>
                <div id="colwrap27">
                    <div id="divane087" class="txtCen bordeDer"><input type="radio" name="radNeumoconiosis" id="radNeumoconiosis2" value="2" class="validate[required]" /> <label for="radNeumoconiosis2"><strong>1/0</strong></label></div>
                    <div class="clearFloat"></div>
                    <div id="divane088" class="txtCen bordeDer"><strong>1/0</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane090" class="txtCen bordeDer">Imagen radiográfica de exposición a polvo<br /><strong>"SOSPECHA"</strong></div>
                    <div class="clearFloat"></div>
                </div>
                <div id="colwrap28">
                    <div id="colwrap30">
                        <div id="divane091" class="txtCen bordeDer">
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis3" value="3" class="validate[required]" /> <label for="radNeumoconiosis3"><strong>1/1</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis4" value="4" class="validate[required]" /> <label for="radNeumoconiosis4"><strong>1/2</strong></label>
                        </div>
                        <div class="clearFloat"></div>
                        <div id="divane092" class="txtCen bordeDer"><strong>UNO</strong></div>
                    </div>
                    <div id="colwrap31">
                        <div id="divane093" class="txtCen bordeDer">
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis5" value="5" class="validate[required]" /> <label for="radNeumoconiosis5"><strong>2/1</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis6" value="6" class="validate[required]" /> <label for="radNeumoconiosis6"><strong>2/2</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis7" value="7" class="validate[required]" /> <label for="radNeumoconiosis7"><strong>2/3</strong></label>
                        </div>
                        <div class="clearFloat"></div>
                        <div id="divane094" class="txtCen bordeDer"><strong>DOS</strong></div>
                    </div>
                    <div id="colwrap32">
                        <div id="divane095" class="txtCen bordeDer">
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis8" value="8" class="validate[required]" /> <label for="radNeumoconiosis8"><strong>3/2</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis9" value="9" class="validate[required]" /> <label for="radNeumoconiosis9"><strong>3/3</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis10" value="10" class="validate[required]" /> <label for="radNeumoconiosis10"><strong>3/+</strong></label>
                        </div>
                        <div class="clearFloat"></div>
                        <div id="divane098" class="txtCen bordeDer"><strong>TRES</strong></div>
                    </div>
                    <div id="colwrap32x">
                        <div id="divane095x" class="txtCen bordeDer">
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis11" value="11" class="validate[required]" /> <label for="radNeumoconiosis11"><strong>A</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis12" value="12" class="validate[required]" /> <label for="radNeumoconiosis12"><strong>B</strong></label>
                            <input type="radio" name="radNeumoconiosis" id="radNeumoconiosis13" value="13" class="validate[required]" /> <label for="radNeumoconiosis13"><strong>C</strong></label>
                        </div>
                        <div class="clearFloat"></div>
                        <div id="divane098x" class="txtCen bordeDer"><strong>CUATRO</strong></div>
                    </div>
                    <div id="divane099" class="txtCen bordeDer"><strong>St</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane100" class="txtCen bordeDer">
                        <textarea id="txtDescrneumoconiosis" name="txtDescrneumoconiosis" style="width: 576px; height: 78px;">CON NEUMOCONIOSIS </textarea>
                    </div>
                    <div class="clearFloat"></div>
                </div>
                <div id="colwrap32b">
                    <div id="divane096">
                        <strong style="display: block; text-align: center; height: 18px">Reacciones serológicas o lúes</strong>
                        <div style="float: left; width: 80%">
                            <input type="radio" name="radReaccionesserologicas" id="radReaccionesserologicasNeg" value="0" /> <label for="radReaccionesserologicasNeg">Negativo</label><br />
                            <input type="radio" name="radReaccionesserologicas" id="radReaccionesserologicasPos" value="1" /> <label for="radReaccionesserologicasPos">Positivo</label><br />
                        </div>
                    </div>
                    <div class="clearFloat"></div>
                    <div id="divane097">
                        <strong>Otros exámenes:</strong><br />
                        <textarea id="txtOtrosexamenes" name="txtOtrosexamenes" style="width: 168px; height: 60px;"></textarea>
                    </div>
                    <div class="clearFloat"></div>
                </div>
                <div id="colwrap33">
                    <div id="divane101" class="bordeDer">
                        <strong>Grupo sanguíneo</strong><br />
                        <input type="radio" name="radGruposan" id="radGruposano" value="O" /> <label for="radGruposano">O</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="radGruposan" id="radGruposana" value="A" /> <label for="radGruposana">A</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="radGruposan" id="radGruposanb" value="B" /> <label for="radGruposanb">B</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="radGruposan" id="radGruposanab" value="AB" /> <label for="radGruposanab">AB</label>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 
                        <input type="radio" name="radFactosan" id="radGruposann" value="0" /> <label for="radGruposann">Rh(-)</label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="radFactosan" id="radGruposanm" value="1" /> <label for="radGruposanm">Rh(+)</label>
                    </div>
                    <div id="divane102" class="txtCen bordeDer"><strong>Hemoglobina / Hematocrito</strong><br /><span id="spanHemoglobina"></span>%</div>
                    <div class="clearFloat"></div>
                    <div id="divane103" class="txtCen bordeDer">
                        <strong style="display: block; text-align: center; height: 18px">APTO PARA TRABAJAR</strong>
                        <input type="radio" name="radAptotrabajar" id="radAptotrabajars" value="1" class="validate[required]" /> <label for="radAptotrabajars">SÍ </label><br />
                        <input type="radio" name="radAptotrabajar" id="radAptotrabajarn" value="2" class="validate[required]" /> <label for="radAptotrabajarn">NO</label>
                    </div>
                    <div id="divane104" class="bordeDer"><strong>Nombres y Apellidos del Médico - N° de Colegiatura</strong><br />Firma y Sello</div>
                    <div class="clearFloat"></div>
                    <div id="divane105" class="bordeDer">
                        <strong>Observaciones:</strong><br />
                        <textarea id="txtObservacioneseval" name="txtObservacioneseval" style="width: 758px; height: 100px;"></textarea>
                    </div>
                    <div class="clearFloat"></div>
                    <div id="divane106" class="bordeDer">
                        <strong>Recomendaciones:</strong><br />
                        <textarea id="txtRecomendacioneseval" name="txtRecomendacioneseval" style="width: 758px; height: 100px;"></textarea>
                    </div>
                    <div class="clearFloat"></div>
                </div>
                <div id="colwrap34">
                    <div id="divane107" class="txtCen"><strong>Firma del Examinado</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane108" class="txtCen"><strong>Huella Digital Índice Derecho</strong></div>
                    <div class="clearFloat"></div>
                    <div id="divane109" class="txtCen">Declaro que toda la información es verdadera.</div>
                    <div class="clearFloat"></div>
                </div>
            </div>
        </div>
        ';
        echo $html;
    }
    public function fichaanexo7dAction(){
        $this->verificaPermiso(34);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $modelLocalidad = new Models_Localidad();
                $rstListarLocalidad = $modelLocalidad->listar();
        
                $datos["p_idCita"] = $post["idCita"];
                $idMotivo = $post["idMotivo"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->fichasietedcomprobar($datos);
                if(isset($eval[0]["idFichasietec"]) || $idMotivo == 7 || $idMotivo == 6){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
                    $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
                    $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
                    $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
                    $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
                    $rst["empleado"][0]["motivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
                    $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
                    $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
                    $rst["empleado"][0]["localidad"] = $this->dameLocalidad($rst["empleado"][0]["idCompania"], $rst["empleado"][0]["idLocalidad"],$rstListarLocalidad);
                    $rst["fichasieted"] = $modelEvaluacion->fichaanexo7dlistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function fichasietedgrabarAction(){
        $this->verificaPermiso(34);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaAF7D"];                
                $datos["p_idFichasieted"] = $post["hdnIdFicha7D"];
                $datos["p_opc1"] = $post["radOpc1"];
                $datos["p_opc2"] = $post["radOpc2"];
                $datos["p_opc3"] = $post["radOpc3"];
                $datos["p_opc4"] = $post["radOpc4"];
                $datos["p_opc5"] = $post["radOpc5"];
                $datos["p_opc6"] = (isset($post["radOpc6"]))?$post["radOpc6"]:0;
                $datos["p_opc7"] = $post["radOpc7"];
                $datos["p_opc8"] = $post["radOpc8"];
                $datos["p_opc9"] = $post["radOpc9"];
                $datos["p_opc10"] = $post["radOpc10"];
                $datos["p_opc11"] = $post["radOpc11"];
                $datos["p_opc12"] = $post["radOpc12"];
                $datos["p_opc13"] = $post["radOpc13"];
                $datos["p_opc14"] = $post["radOpc14"];
                $datos["p_opc15"] = $post["radOpc15"];
                $datos["p_opc16"] = $post["radOpc16"];
                $datos["p_opc17"] = $post["radOpc17"];
                $datos["p_opcdesc1"] = (isset($post["txtOpc1"]))?$post["txtOpc1"]:"";
                $datos["p_opcdesc2"] = (isset($post["txtOpc2"]))?$post["txtOpc2"]:"";
                $datos["p_opcdesc3"] = (isset($post["txtOpc3"]))?$post["txtOpc3"]:"";
                $datos["p_opcdesc4"] = (isset($post["txtOpc4"]))?$post["txtOpc4"]:"";
                $datos["p_opcdesc5"] = (isset($post["txtOpc5"]))?$post["txtOpc5"]:"";
                $datos["p_opcdesc6"] = (isset($post["txtOpc6"]))?$post["txtOpc6"]:"";
                $datos["p_opcdesc7"] = (isset($post["txtOpc7"]))?$post["txtOpc7"]:"";
                $datos["p_opcdesc8"] = (isset($post["txtOpc8"]))?$post["txtOpc8"]:"";
                $datos["p_opcdesc9"] = (isset($post["txtOpc9"]))?$post["txtOpc9"]:"";
                $datos["p_opcdesc10"] = (isset($post["txtOpc10"]))?$post["txtOpc10"]:"";
                $datos["p_opcdesc11"] = (isset($post["txtOpc11"]))?$post["txtOpc11"]:"";
                $datos["p_opcdesc12"] = (isset($post["txtOpc12"]))?$post["txtOpc12"]:"";
                $datos["p_opcdesc13"] = (isset($post["txtOpc13"]))?$post["txtOpc13"]:"";
                $datos["p_opcdesc14"] = (isset($post["txtOpc14"]))?$post["txtOpc14"]:"";
                $datos["p_opcdesc15"] = (isset($post["txtOpc15"]))?$post["txtOpc15"]:"";
                $datos["p_opcdesc16"] = (isset($post["txtOpc16"]))?$post["txtOpc16"]:"";
                $datos["p_opcdesc17"] = (isset($post["txtOpc17"]))?$post["txtOpc17"]:"";
                $datos["p_apto"] = (isset($post["chkApto"])?1:0);
                $datos["p_frecuenciacardiaca"] = $post["txtFC"];
                $datos["p_presionarteriala"] = $post["txtPAa"];
                $datos["p_presionarterialb"] = $post["txtPAb"];
                $datos["p_frecuenciarespiratoria"] = $post["txtFR"];
                $datos["p_imc"] = $post["txtIMC"];
                $datos["p_sat"] = $post["txtSAT"];
                $datos["p_observaciones"] = $post["txtObservaciones"];
                $datos["p_direccion"] = $post["txtDireccion"];
                
                $datos["p_temperatura"] = $post["txtTemperaturaF7D"];
                $datos["p_talla"] = number_format($post["txtTallaF7D"], 2);
                $datos["p_peso"] = number_format($post["txtPesoF7D"], 2);
                $datos["p_cintura"] = ((trim($post["txtCinturaF7D"]) == "")?0:number_format($post["txtCinturaF7D"], 2));
                $datos["p_cadera"] = ((trim($post["txtCaderaF7D"]) == "")?0:number_format($post["txtCaderaF7D"], 2));
                $datos["p_icc"] = ((trim($post["txtICCF7D"]) == "")?0:$post["txtICCF7D"]);
                
                $datos = $this->convierteamayusculas($datos);
                
                $modelEvaluacion = new Models_Evaluacion();
                $rst = $modelEvaluacion->fichasietedgrabar($datos);
                $resultado["status"] = $rst;
                
                $modelEmpleado = new Models_Empleado();
                $rst = $modelEmpleado->listarcita($datos);                                
                $resultado["fin"] = $rst[0]["estado"]; 
                
                $resultado["message"] = "Ocurrió un error mientras se procesaba la información.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function rayosxAction(){
        $this->verificaPermiso(37);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"])){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["rayosx"] = $modelEvaluacion->rayosxlistar($datos);
                    if(isset($rst["rayosx"][0]["fecha"]))
                    $rst["rayosx"][0]["fecha"] = $this->convierteFechaaLatino($rst["rayosx"][0]["fecha"]);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function rayosxgrabarAction(){
        $this->verificaPermiso(37);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaRX"];                
                $datos["p_idRayosx"] = (trim($post["hdnIdRayosxRX"]) != "")?$post["hdnIdRayosxRX"]:0;
                $datos["p_nregistro"] = $post["txtNregistroRX"];
                $datos["p_fecha"] = $this->convierteFecha($post["txtFechaRX"]);
                
                $modelEvaluacion = new Models_Evaluacion();
                $rsts = $modelEvaluacion->rayosxlistar($datos);
                if(count($rsts) < 1){
                    $modelEvaluacion->rayosxgrabar($datos);
                    $resultado["status"] = 1;
                }else{
                    $resultado["status"] = -1;
                }                
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function laboratorioAction(){
        $this->verificaPermiso(38);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"])){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["laboratorio"] = $modelEvaluacion->laboratoriolistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function laboratoriograbarAction(){
        $this->verificaPermiso(38);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaLA"]; //*
                $datos["p_idLaboratorio"] = $post["hdnIdLaboratorioLA"];//*
                $datos["p_gruposanguineo"] = $post["selGruposanLA"];//*
                $datos["p_factorsanguineo"] = $post["selFactorsanLA"];//*
                $datos["p_rpr"] = $post["radRPRL"];//*
                $datos["p_subunidadbeta"] = $post["txtSubunidadbetaLA"];//*
                $datos["p_fur"] = $post["txtFurLA"];//*
                $datos["p_hemoglobina"] = $post["txtHemoLA"];  //*              
                $datos["p_hematocrito"] = $post["txtHematocritoLA"];
                $datos["p_hematies"] = $post["txtHematiesLA"];
                $datos["p_leucocitos"] = $post["txtLeucocitosLA"];
                $datos["p_juveniles"] = $post["txtJuvenilesLA"];
                $datos["p_abastonados"] = $post["txtAbastonadosLA"];                
                $datos["p_segmentados"] = $post["txtSegmentadosLA"];
                $datos["p_linfocitos"] = $post["txtLinfocitosLA"];
                $datos["p_monocitos"] = $post["txtMonocitosLA"];
                $datos["p_eosinofilos"] = $post["txtEosinofilosLA"];
                $datos["p_basofilos"] = $post["txtBasofilosLA"];                
                $datos["p_plaquetas"] = $post["txtPlaquetasLA"];
                $datos["p_comentario"] = $post["txtComentarioLA"];
                $datos["p_color"] = $post["txtColorLA"];
                $datos["p_aspecto"] = $post["txtAspectoLA"];
                $datos["p_sedleucocitos"] = $post["txtLeucocitosseLA"];                
                $datos["p_reaccion"] = $post["txtReaccionLA"];
                $datos["p_celulasepiteliales"] = $post["txtCelulasEpitelialesLA"];
                $datos["p_densidad"] = $post["txtDensidadLA"];
                $datos["p_sedhematies"] = $post["txtHematiesseLA"];
                $datos["p_cristales"] = $post["txtCristalesLA"];                
                $datos["p_glucosa"] = $post["selGlucosaLA"];
                $datos["p_cilindros"] = $post["txtCilindrosLA"];
                $datos["p_proteinas"] = $post["selProteinastiLA"];
                $datos["p_otros"] = $post["txtOtrosLA"];
                $datos["p_cetonas"] = $post["selCetonasLA"];                
                $datos["p_bilirrubina"] = $post["selBilirrubinaLA"];
                $datos["p_urobilinogeno"] = $post["selUrobilinogenoLA"];
                $datos["p_nitritos"] = $post["selNitritosLA"];
                $datos["p_sangre"] = $post["selSangreLA"];
                $datos["p_colesteroltotal"] = $post["txtColesteroltotalLA"];                
                $datos["p_hdl"] = $post["txtHDLLA"];
                $datos["p_trigliceridos"] = $post["txtTrigliceridosLA"];
                $datos["p_proteinastotales"] = $post["txtProteinastotalesLA"];
                $datos["p_albumina"] = $post["txtAlbuminaLA"];
                $datos["p_globulinas"] = $post["txtGlubulinasLA"];                
                $datos["p_acidourico"] = $post["txtAcidouricoLA"];
                $datos["p_bioglucosa"] = $post["txtGlucosaLA"];
                $datos["p_urea"] = $post["txtUreaLA"];
                $datos["p_creatinina"] = $post["txtCreatininaLA"];
                $datos["p_amilasa"] = $post["txtAmilasaLA"];                
                $datos["p_tgo"] = $post["txtTGOLA"];
                $datos["p_tgp"] = $post["txtTGPLA"];
                $datos["p_ggt"] = $post["txtGGTLA"];
                $datos["p_fosfatasaalcalina"] = $post["txtFosfatasaAlcalinaLA"];
                $datos["p_bilirrubinatotal"] = $post["txtBilirrubinaTotalLA"];                
                $datos["p_bilirrubinadirecta"] = $post["txtBilirrubinaDirectaLA"];
                $datos["p_bilirrubinaindirecta"] = $post["txtBilirrubinaIndirectaLA"];
                $datos["p_biocomentario"] = $post["txtComentariobioLA"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->laboratoriograbar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function audiometriaAction(){
        $this->verificaPermiso(39);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $idMotivo = $post["idMotivo"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"]) || $idMotivo == 2){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["audiometria"] = $modelEvaluacion->audiometrialistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function audiometriagrabarAction(){
        $this->verificaPermiso(39);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaAU"];                
                $datos["p_idAudiometria"] = $post["hdnIdAudiometriaAU"];
                $datos["p_comentario"] = $post["audiocomentario"];
                $datos["p_ad1"] = $post["txtAD500"];
                $datos["p_ad2"] = $post["txtAD1000"];
                $datos["p_ad3"] = $post["txtAD2000"];
                $datos["p_ad4"] = $post["txtAD3000"];
                $datos["p_ad5"] = $post["txtAD4000"];
                $datos["p_ad6"] = $post["txtAD6000"];
                $datos["p_ad7"] = $post["txtAD8000"];
                
                $datos["p_ai1"] = $post["txtAI500"];
                $datos["p_ai2"] = $post["txtAI1000"];
                $datos["p_ai3"] = $post["txtAI2000"];
                $datos["p_ai4"] = $post["txtAI3000"];
                $datos["p_ai5"] = $post["txtAI4000"];
                $datos["p_ai6"] = $post["txtAI6000"];
                $datos["p_ai7"] = $post["txtAI8000"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->audiometriagrabar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function espirometriaAction(){
        $this->verificaPermiso(40);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $idMotivo = $post["idMotivo"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"]) || $idMotivo == 2){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["espirometria"] = $modelEvaluacion->espirometrialistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function espirometriagrabarAction(){
        $this->verificaPermiso(40);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaES"];                
                $datos["p_idEspirometria"] = $post["hdnIdEspirometriaES"];
                $datos["p_espirometria"] = $post["txtEspirometriaES"];
                $datos["p_edadpulmonar"] = $post["txtEdadpulmonarES"];
                $datos["p_fev"] = $post["txtFEVES"];
                $datos["p_fvc"] = $post["txtFVCES"];
                $datos["p_fevfvc"] = $post["txtFEVFVCES"];
                $datos["p_fev2575"] = $post["txtFEV2575ES"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->espirometriagrabar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function optometriaAction(){
        $this->verificaPermiso(41);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $idMotivo = $post["idMotivo"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"]) || $idMotivo == 2){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["optometria"] = $modelEvaluacion->optometrialistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function optometriagrabarAction(){
        $this->verificaPermiso(41);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaOT"];                
                $datos["p_idOptometria"] = $post["hdnIdOptometriaOT"];
                $datos["p_ovcscod"] = $post["txtVCSCOD"];
                $datos["p_ovlscod"] = $post["txtVLSCOD"];
                $datos["p_ovcscoi"] = $post["txtVCSCOI"];
                $datos["p_ovlscoi"] = $post["txtVLSCOI"];
                $datos["p_ovccod"] = $post["txtVCCOD"];
                $datos["p_ovlcod"] = $post["txtVLCOD"];
                $datos["p_ovccoi"] = $post["txtVCCOI"];
                $datos["p_ovlcoi"] = $post["txtVLCOI"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->optometriagrabar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function odontogramaAction(){
        $this->verificaPermiso(42);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobar($datos);
                if(isset($eval[0]["idFichasieted"])){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["odontograma"] = $modelEvaluacion->odontogramalistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function odontogramagrabarAction(){
        $this->verificaPermiso(42);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaOD"];                
                $datos["p_idOdontograma"] = $post["hdnIdOdontogramaOD"];
                $datos["p_piezacompleta"] = $post["txtPiezacompletaOD"];
                $datos["p_piezaextraida"] = $post["txtPiezaextraidaOD"];
                $datos["p_piezamal"] = $post["txtPiezamalOD"];
                $datos["p_observaciones"] = $post["txtObservacionesOD"];
                $datos = $this->convierteamayusculas($datos);
                $datos["p_grafico"] = $post["hdnGrafico"];
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->odontogramagrabar($datos);
                $resultado["status"] = 1;
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function trabajoalturaAction(){
        $this->verificaPermiso(36);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
                $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
                $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
                $rst["trabajoaltura"] = $modelEvaluacion->trabajoalturalistar($datos);
                $rst["general"] = 1;
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function trabajoalturagrabarAction(){
        $this->verificaPermiso(36);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaEMSFP"];
                $datos["p_idTrabajoaltura"] = $post["hdnIdExamenMedicoSFP"];
                $datos["p_habcoca"] = $post["radHabitosCoca"];
                $datos["p_habalcohol"] = $post["radHabitosAlcohol"];
                $datos["p_habtabaco"] = $post["radHabitosTabaco"];
                $datos["p_habdrogas"] = $post["radHabitosDrogas"];
                $datos["p_fobaltura"] = $post["radFobiasAltura"];
                $datos["p_foblugcer"] = $post["radFobiasLugcerr"];
                $datos["p_fobespcon"] = $post["radFobiasEspconf"];
                $datos["p_epilepsia"] = $post["radEpilepsia"];
                $datos["p_viscerder"] = $post["selVisionCOD"];
                $datos["p_viscerizq"] = $post["selVisionCOI"];
                $datos["p_vislejder"] = $post["selVisionLOD"];
                $datos["p_vislejizq"] = $post["selVisionLOI"];
                $datos["p_vertigo1"] = $post["radVertigoA"];
                $datos["p_vertigo2"] = $post["radVertigoB"];
                $datos["p_vertigo3"] = $post["radVertigoC"];
                $datos["p_vertigo4"] = $post["radVertigoD"];
                $datos["p_vertigo5"] = $post["radVertigoE"];
                $datos["p_vertigo6"] = $post["radVertigoF"];
                $datos["p_asma1"] = $post["radAsmaA"];
                $datos["p_asma2"] = $post["radAsmaB"];
                $datos["p_asma3"] = $post["radAsmaC"];
                $datos["p_asma4"] = $post["txtAsmaD"];
                $datos["p_asma5"] = $post["txtAsmaE"];
                $datos["p_asma6"] = $post["txtAsmaF"];
                $datos["p_evacard1"] = $post["radEvcardioA"];
                $datos["p_evacard2"] = $post["radEvcardioB"];
                $datos["p_evacard3"] = $post["radEvcardioC"];
                $datos["p_evacard4"] = $post["radEvcardioD"];
                $datos["p_evacard5"] = $post["radEvcardioE"];
                $datos["p_evacard6"] = $post["radEvcardioF"];
                $datos["p_evacard7"] = $post["radEvcardioG"];
                $datos["p_evacard8"] = $post["radEvcardioH"];
                $datos["p_evacard9"] = $post["radEvcardioI"];
                $datos["p_indicemasa"] = $post["selMasacorp"];
                $datos["p_sisloc1"] = $post["radSislocA"];
                $datos["p_sisloc2"] = $post["radSislocB"];
                $datos["p_sisloc3"] = $post["radSislocC"];
                $datos["p_sisloc4"] = $post["radSislocD"];
                $datos["p_sisloc5"] = $post["radSislocE"];
                $datos["p_sisloc6"] = $post["radSislocF"];
                $datos["p_evapsi1"] = $post["radEvapsiA"];
                $datos["p_evapsi2"] = $post["radEvapsiB"];
                $datos["p_evapsi3"] = $post["radEvapsiC"];
                $datos["p_evapsi4"] = $post["radEvapsiD"];
                $datos["p_evapsi5"] = $post["radEvapsiE"];
                $datos["p_evapsi6"] = $post["radEvapsiF"];
                $datos["p_evapsi7"] = $post["radEvapsiG"];
                $datos["p_observaciones"] = $post["txtObservacionesEMSFP"];
                $datos["p_apto"] = $post["radAptoEMSFP"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->trabajoalturagrabar($datos);
                $resultado["status"] = 1;
                
                $modelEmpleado = new Models_Empleado();
                $rst = $modelEmpleado->listarcita($datos);                                
                $resultado["fin"] = $rst[0]["estado"];  
                
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function pasevisitanteAction(){
        $this->verificaPermiso(35);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
                $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
                $rst["fichasieted"] = $modelEvaluacion->fichaanexo7dlistar($datos);
                $rst["pasevisitante"] = $modelEvaluacion->pasevisitantelistar($datos);
                $rst["general"] = 1;
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function pasevisitantegrabarAction(){
        $this->verificaPermiso(35);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaPV"];
                $datos["p_idPasevisitante"] = $post["hdnIdPaseVisitante"];
                $datos["p_pregunta1"] = $post["txtPregunta1"];
                $datos["p_pregunta2"] = $post["txtPregunta2"];
                $datos["p_pregunta3"] = $post["txtPregunta3"];
                $datos["p_pregunta4"] = $post["txtPregunta4"];
                $datos["p_pregunta5"] = $post["txtPregunta5"];
                $datos["p_pregunta6"] = $post["txtPregunta6"];
                $datos["p_pregunta7"] = $post["txtPregunta7"];
                $datos["p_pregunta8"] = $post["txtPregunta8"];
                $datos["p_pregunta9"] = $post["txtPregunta9"];
                $datos["p_pregunta10"] = $post["txtPregunta10"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->pasevisitantegrabar($datos);
                $resultado["status"] = 1;
                
                $modelEmpleado = new Models_Empleado();
                $rst = $modelEmpleado->listarcita($datos);                                
                $resultado["fin"] = $rst[0]["estado"];  
                
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function resultadoeeAction(){
        $this->verificaPermiso(43);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                $datos["p_idCita"] = $post["idCita"];
                $modelEvaluacion = new Models_Evaluacion();
                $eval = $modelEvaluacion->examenescomprobaree($datos);
                if(isset($eval[0]["a"]) && isset($eval[0]["b"]) && isset($eval[0]["c"])){
                    $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
                    $rst["resultadoee"] = $modelEvaluacion->resultadoeelistar($datos);
                    $rst["espirometria"] = $modelEvaluacion->espirometrialistar($datos);
                    $rst["optometria"] = $modelEvaluacion->optometrialistar($datos);
                    $rst["general"] = 1;
                }else{
                    $rst["general"] = 0;
                }
                $this->_helper->json( Zend_Json::encode( $rst ) );
            }
        }
    }
    public function resultadoeegrabarAction(){
        $this->verificaPermiso(43);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            if( $this->getRequest()->isPost() ){
                $post = $this->getRequest()->getPost();
                
                $datos["p_idCita"] = $post["hdnIdCitaREE"];       
                $datos["p_idEquipopesado"] = $post["hdnIdResultadoEE"];
                $datos["p_para"] = $post["txtParaREE"];         
                $datos["p_tipoexamen"] = $post["txtExamenREE"];         
                $datos["p_resespirometria"] = $post["txtEspiroREE"];
                $datos["p_resaudiometria"] = $post["txtAudioREE"];
                $datos["p_resoptometria"] = $post["txtOptoREE"];
                $datos["p_apto"] = $post["radAptoREE"];
                
                $modelEvaluacion = new Models_Evaluacion();
                $modelEvaluacion->resultadoeegrabar($datos);
                $resultado["status"] = 1;
                
                $modelEmpleado = new Models_Empleado();
                $rst = $modelEmpleado->listarcita($datos);                                
                $resultado["fin"] = $rst[0]["estado"];  
                
                $resultado["message"] = "El número de registro ya existe.";
                $this->_helper->json( Zend_Json::encode( $resultado ) );
            }
        }
    }
    public function archivosubirAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 
        $error = $msg = "";
        $fileElementName = "file".$_POST["file"];
        $msg = "Correcto";
        $target_path = $this->rutaarchivos . basename($_POST["cita"]."_".$_POST["file"].".pdf");
        $fileTypes = array('pdf'); 
        $fileSize = $_FILES[$fileElementName]['size'];  
        $fileParts = pathinfo($_FILES[$fileElementName]['name']);
        if (!in_array($fileParts['extension'],$fileTypes)) {
            $error = "El tipo de archivo no es un PDF.";
        }elseif($fileSize > 400000){
            $error = "El archivo sobrepasa los 400Kb permitidos";
        }else{
            if (file_exists($target_path)) { @unlink($target_path); }
            if(@move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $target_path)){
                $modelEvaluacion = new Models_Evaluacion();
                $x = explode("_", $_POST["cita"]);
                $datos["p_idCita"] = $x[1];
                $datos["p_tipo"] = $_POST["file"];
                $datos["p_nombre"] = $_POST["cita"]."_".$_POST["file"].".pdf";
                $modelEvaluacion->archivonombregrabar($datos);
            }else $error = "No se puede subir el archivo.";
        }
        @unlink($_FILES[$fileElementName]);	
        $response["error"] = $error;
        $response["msg"] = $msg;
        echo json_encode($response);
    }
    public function archivonombresAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 
        $modelEvaluacion = new Models_Evaluacion();
        $x = explode("_", $_POST["cita"]);
        $datos["p_idCita"] = (isset($x[1])?$x[1]:$_POST["cita"]);
        $response = $modelEvaluacion->archivonombrelistar($datos);
        $this->_helper->json( Zend_Json::encode( $response ) );
    }
    
    public function empresaespecializadaautocompletarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEmpresa = new Models_Empresa();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_descr_ctta"] = $_REQUEST["term"];
                $rst = $modelEmpresa->empresaespecializadaautocompletar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function enfermedadesautocompletarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEvaluación = new Models_Evaluacion();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_nombre"] = $_REQUEST["term"];
                $rst = $modelEvaluación->enfermedadesautocompletar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function puestoautocompletarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();        
        if ($this->_request->isXmlHttpRequest()) {
            $modelEvaluacion = new Models_Evaluacion();
            $datos = array();            
            if(isset($_REQUEST["term"])) { 
                $datos["p_puesto"] = $_REQUEST["term"];
                $rst = $modelEvaluacion->puestoautocompletar($datos);
                echo json_encode($rst);
            }
        }
    }
    public function generapdfAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 
        ini_set('zlib_output_compression','On');
                
        $mpdf = new mPDF('es-GB-x','A4','','',5,5,5,5,0,0); 
        $mpdf->SetCompression(true);
        $mpdf->mirrorMargins = 1;
        $mpdf->SetDisplayMode('fullpage','two');
        $stylesheet = file_get_contents('../public/css/imprimira.css');
        $mpdf->WriteHTML($stylesheet, 1);
        
        $request = $this->getRequest();
        $datos["p_idCita"] = $request->getParam("idcita");
        
        $modelCita = new Models_Empleado();
        $restCita = $modelCita->listarcita($datos);
        switch ($restCita[0]["motivo"]){
            case 1:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeinicio($datos));
                $mpdf->AddPage('L');
                $mpdf->WriteHTML($this->imprimehistoria($datos));
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeanexo7c($datos));
                $mpdf->WriteHTML($this->imprimeanexo7d($datos));
                $mpdf->WriteHTML($this->imprimelaboratorio($datos));
                $mpdf->WriteHTML($this->imprimeodontograma($datos));
                break;
            case 2:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimememorandum($datos));
                break;
            case 3:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeevamed($datos));
                break;
            case 4:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeinicio($datos));
                $mpdf->AddPage('L');
                $mpdf->WriteHTML($this->imprimehistoria($datos));
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeanexo7c($datos));
                $mpdf->WriteHTML($this->imprimeanexo7d($datos));
                $mpdf->WriteHTML($this->imprimelaboratorio($datos));
                $mpdf->WriteHTML($this->imprimeodontograma($datos));
                break;
            case 5:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeinicio($datos));
                $mpdf->AddPage('L');
                $mpdf->WriteHTML($this->imprimehistoria($datos));
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeanexo7c($datos));
                $mpdf->WriteHTML($this->imprimeanexo7d($datos));
                $mpdf->WriteHTML($this->imprimelaboratorio($datos));
                $mpdf->WriteHTML($this->imprimeodontograma($datos));
                break;
            case 6:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeanexo7d($datos));
                break;
            case 7:
                $mpdf->AddPage('');
                $mpdf->WriteHTML($this->imprimeanexo7d($datos));
                $mpdf->WriteHTML($this->imprimepasevisitante($datos));
                break;
        }
        
        $audiometria = $this->rutaarchivos.$restCita[0]["idEmpleado"]."_".$datos["p_idCita"]."_2.pdf";
        $espirometria = $this->rutaarchivos.$restCita[0]["idEmpleado"]."_".$datos["p_idCita"]."_3.pdf";
        $generadopdf = $datos["p_idCita"].date("dmYhis").".pdf";
        
        if($restCita[0]["motivo"] == 1 || $restCita[0]["motivo"] == 2 || 
           $restCita[0]["motivo"] == 4 || $restCita[0]["motivo"] == 5) {
            
            $mpdf->Output("/mnt/SOPDFs/".$generadopdf,"F"); 
            
            $pdfDocs = array('/mnt/SOPDFs/'.$generadopdf, $audiometria, $espirometria);
            
            if (!file_exists($audiometria)) {
                $pdfDocs = array('/mnt/SOPDFs/'.$generadopdf, $espirometria);
            }
            if (!file_exists($espirometria)) {
                $pdfDocs = array('/mnt/SOPDFs/'.$generadopdf, $audiometria);
            }
            if(!file_exists($audiometria) && !file_exists($espirometria)){
                $pdfDocs = array('/mnt/SOPDFs/'.$generadopdf);
            }

            $pdfNew = new Zend_Pdf();
            foreach ($pdfDocs as $file) {
                $pdf = Zend_Pdf::load($file);
                $extractor = new Zend_Pdf_Resource_Extractor();
                foreach ($pdf->pages as $page) {
                    $pdfExtract = $extractor->clonePage($page);
                    $pdfNew->pages[] = $pdfExtract;
                }
            }
            $archivoRuta = "/mnt/SOPDFs/" . $generadopdf;
            $pdfNew->save($archivoRuta);
            header('Content-type: application/pdf');
            readfile($archivoRuta);
        }else{
            $mpdf->Output(); 
        }
    }
} 