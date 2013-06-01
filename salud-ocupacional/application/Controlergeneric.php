<?php
class Controlergeneric extends Zend_Controller_Action{
    protected $destinoOff = '{"status":"-1","message": "Ha ocurrido un error de conexión, por favor comuníquese con el administrador del sistema","alert":""}';
    protected $rutaarchivos = "/var/www/localhost/htdocs/saludocupacional/public/upload/"; 
    public function esDB(){
        try{
            $dbp = Zend_Registry::get('dbMSSQL');
            $dbp->getConnection();
            return true;
        }catch(Zend_Exception $e){
            return false;
        }
    }
    public function dameCompania($idCompania, $rest){
        foreach($rest as $row){
            if(trim($row["idCompania"]) == trim($idCompania)) return $row["nombreCompleto"];
        }
    }
    public function dameLocalidad($idCompania, $idLocalidad, $rest){
        foreach($rest as $row){
            if(trim($row["idCompania"]) == trim($idCompania) && trim($row["idLocalidad"]) == trim($idLocalidad)) return $row["nombre"];
        }        
    }
    public function elimina_duplicados($array, $campo) {
        foreach ($array as $sub) {
            $cmp[] = $sub[$campo];
        }
        $unique = array_unique($cmp);
        foreach ($unique as $k => $campo) {
            $resultado[] = $array[$k];
        }
        return $resultado;
    }
    public function convierteamayusculas($array){ 
        foreach ($array as  $key=>$item) { 
            $item = strtr(strtoupper($item), array( 
            "à" => "À", "è" => "È", "ì" => "Ì", "ò" => "Ò", 
            "ù" => "Ù", "á" => "Á", "é" => "É", "í" => "Í", 
            "ó" => "Ó", "ú" => "Ú", "â" => "Â", "ê" => "Ê", 
            "î" => "Î", "ô" => "Ô", "û" => "Û", "ç" => "Ç", 
            )); 
            if(is_array($item)) $array[$key] = $this->convierteamayusculas($item); 
            else $array[$key] = utf8_decode($item);         
        } 
        return $array;
    } 
    public function verificaPermiso( $modulo = 0 ){
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) $this->_helper->redirector('index','index');
        $permisos = new Zend_Session_Namespace('Permisos');
        if (!isset($permisos->Especificos[$modulo])) $this->_helper->redirector('panel','index');
    }
    function convierteFecha($fecha){
        $ope = strripos($fecha, "/");
        if($ope === false) $op = "-"; else $op = "/";
        $aux = explode($op, $fecha);
        return $aux[2]."-".$aux[1]."-".$aux[0];
    }
    function convierteFechaSinEspacio($fecha){
        $aux = explode("/", $fecha);
        return $aux[2].$aux[1].$aux[0];
    }
    function convierteFechaaLatino($fecha){
        $aux = explode("-", $fecha);
        return $aux[2]."/".$aux[1]."/".$aux[0];
    }
    function convierteFechaaPeriodo($fecha){
        $aux = explode("-", $fecha);
        return $aux[1]."/".$aux[0];
    }
    function devuelveNombreMotivo($id){
        switch ($id) {
            case 1: return "ANUAL"; break;
            case 2: return "EQUIPOS PESADOS"; break;
            case 3: return "TRABAJOS EN ALTURA"; break;
            case 4: return "RETIRO"; break;
            case 5: return "INGRESO"; break;
            case 6: return "RETORNO DE VACACIONES"; break;
            case 7: return "VISITANTE"; break;
        }
    }
    function devuelveNombreAltura($id){
        switch ($id) {
            case 1: return "Debajo de 2500"; break;
            case 2: return "2501 a 3000"; break;
            case 3: return "3001 a 3500"; break;
            case 4: return "3500 a 4000"; break;
            case 5: return "4001 a 4500"; break;
            case 6: return "Más de 4501"; break;
        }
    }
    function devuelveLaboratorioplus($id){
        switch ($id) {
            case 0: return "Negativo"; break;
            case 1: return "Positivo +"; break;
            case 2: return "Positivo ++"; break;
            case 3: return "Positivo +++"; break;
        }
    }
    function devuelveNombreEstadoCronograma($id){
        switch ($id) {
            case 1: return "PENDIENTE APROBACIÓN"; break;
            case 2: return "EN ESPERA (APROBADO)"; break;
            case 3: return "EN CURSO"; break;
            case 4: return "SIN APROBAR"; break;
            case 5: return "CERRADO"; break;
        }
    }
    function devuelveNombreEstadoCita($id){
        switch ($id) {
            case 1: return "EN ESPERA"; break;
            case 2: return "EN CURSO"; break;
            case 3: return "CANCELADO"; break;
            case 4: return "NO SE PRESENTÓ"; break;
            case 5: return "ATENDIDO"; break;
            case 6: return "ATENDIENDO"; break;
        }
    }
    function devuelveNombreSexo($id){
        switch ($id) {
            case "M": return "MASCULINO"; break;
            case "F": return "FEMENINO"; break;
        }
    }
    function devuelveNombreEstadoCivil($id){
        switch ($id) {
            case "CS": return "CASADO"; break;
            case "SL": return "SOLTERO"; break;
            case "VD": return "VIUDO"; break;
            case "CV": return "CONVIVIENTE"; break;
            case "DV": return "DIVORCIADO"; break;
            case "SP": return "SEPARADO"; break;
        }
    }
    function devuelveTipoTrabajador($id){
        switch ($id) {
            case "OBR": return "OBRERO"; break;
            case "MAG": return "MAGISTERIO"; break;
            case "GTE": return "GERENTE"; break;
            case "PRA": return "PRACTICANTE"; break;
            case "EMP": return "EMPLEADO"; break;
            case "EJC": return "EJECUTIVO"; break;
            case "EMI": return "EMPLEADO DE MINA"; break;
            case "EMC": return "EMPLEADO DE CONTRATA"; break;
            case "OBC": return "OBRERO DE CONTRATA"; break;
        }
    }
    function devuelveIMC($id){
        switch ($id) {
            case 0: return "DELGADEZ: <= 18.490"; break;
            case 1: return "NORMAL: 18.50 - 24.99"; break;
            case 2: return "SOBREPESO: 25 - 29.99"; break;
            case 3: return "OBESO TIPO I: 30 - 34.99"; break;
            case 4: return "OBESO TIPO II: 35 - 39.99"; break;
            case 5: return "OBESO TIPO III: >= 40"; break;
        }
    }
    function devuelveNombreMedidaOptometria($id){
        switch ($id) {
            case "0": return "N/A"; break;
            case "1": return "20/200"; break;
            case "2": return "20/100"; break;
            case "3": return "20/70"; break;
            case "4": return "20/50"; break;
            case "5": return "20/40"; break;
            case "6": return "20/30"; break;
            case "7": return "20/25"; break;
            case "8": return "20/20"; break;
        }
    }
    function devuelveNombreDept($d){        
        $model = new Models_Departamento();
        $rst = $model->listar($d);
        return $rst[0]["descr_dpto"];
    }
    function devuelveNombreProv($d, $p){        
        $model = new Models_Provincia();
        $rst = $model->listar($d, $p);
        return $rst[0]["descr_prov"];
    }
    function devuelveNombreDist($d, $p, $di){        
        $model = new Models_Distrito();
        $rst = $model->listar($d, $p, $di);
        return $rst[0]["descr_dist"];
    }
    function devuelveNombreEmpresa($d){        
        $model = new Models_Empresa();
        $rst = $model->listar($d);
        return trim($rst[0]["descr_ctta"]);
    }
    function devuelveNombreArea($d){        
        $model = new Models_Area();
        $rst = $model->listar(trim($d));
        if(isset($rst[0]["descr_area"])) return trim($rst[0]["descr_area"]);
        else return "";
    }
    function devuelveNombreCentrocosto($d){        
        $model = new Models_Centrocosto();
        $rst = $model->listar($d);
        return trim($rst[0]["descr_ccosto"]);
    }
    function dameEmpleado($datos){        
        $modelEmpleado = new Models_Empleado();
        $rstEmpleado = $modelEmpleado->listar($datos);
        if(isset($rstEmpleado[0]["dni"]) && trim($rstEmpleado[0]["dni"]) == trim($datos["p_dni"]))
            return true;
        return false;
    }
    function devuelveEdad ($fecha) {
        list($y, $m, $d) = explode("-", $fecha);
        $y_dif = date("Y") - $y;
        $m_dif = date("m") - $m;
        $d_dif = date("d") - $d;
        if ((($d_dif < 0) && ($m_dif == 0)) || ($m_dif < 0))
            $y_dif--;
        return $y_dif;
    }
    function imprimeinicio($datos = array()){
        $modelLocalidad = new Models_Localidad();
        $rstListarLocalidad = $modelLocalidad->listar();
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["nombremotivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["codestadocivil"] = $rst["empleado"][0]["estadocivil"];
        $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rowem = $rst["empleado"][0];
        $fecha = date("Y-m-d");
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        
        $html = '
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div class="divClear"></div>
            <div style="width:100%" class="divTituloHojaA">CONSTANCIA DE EVALUACIÓN MÉDICA '.  strtoupper($rowem["nombremotivo"]).'</div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div class="divClear"></div><div class="divClear"></div>
            <div id="divFME5" class="divTextoCF" style="line-height: 25px; font-size: 14px">
                Se hace constar que el Señor(a):<br /><br />
                <strong>'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</strong> identificado con 
                DNI N° <strong>'.$rowem["dni"].'</strong>, personal de <strong>'.$rowem["rucempresaespecializada"].'</strong>
                cumplió con el proceso Médico '.$rowem["nombremotivo"].', como '.$rowem["puesto"].'.<br /><br />
                Esta evaluación tiene validez desde '.date("d/m/Y").' hasta '.date("d/m/Y", strtotime ( $fecha.' +1 year' )).'.
                <br /><br /><br /><br />
                <p style="text-align: right">
                '.$dias[date('w')].", ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y').'</p>
            </div>
        </div>
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div class="divClear"></div>
            <div style="width:100%" class="divTituloHojaA">CONSENTIMIENTO INFORMADO PARA EL EXÁMEN MÉDICO</div>
            <div class="divClear"></div><div class="divClear"></div><div class="divClear"></div>
            <div id="divFME5" class="divTextoCF" style="line-height: 25px; font-size: 14px">    
                <p style="text-align: right">'.$dias[date('w')].", ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y').'</p>
                <div class="divClear"></div><div class="divClear"></div><div class="divClear">
                </div><div class="divClear"></div>
                Yo, <strong>'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</strong> identificado con 
                DNI N° <strong>'.$rowem["dni"].'</strong>, con ocupación laboral de <strong>'.$rowem["puesto"].'</strong> certifico que 
                he sido informaco acerca de la naturaleza y propósito de los exámenes ocupacionales y pruebas
                complementarias que la empresa <strong>'.$rowem["rucempresaespecializada"].'</strong>, Unidad <strong>
                '.$this->dameLocalidad($rowem["idCompania"],$rowem["idLocalidad"],$rstListarLocalidad) .'</strong>
                solicita, y que todas mis dudas y preguntas al respecto han sido absueltas; así mismo, autorizo que los
                resultados sean entregados a la empresa la cual soy vinculante.<br /><br />
                Por tanto en forma consciente y voluntaria doy mi consentimiento para que se proceda a efectuar los
                exámenes que correspondan.
                <br /><br /><br /><br /><br /><br />
            </div>
            <div style="margin-left:250px; border-top: solid 1px #000; width: 200px; text-align: center">
            (N° DNI/Carne de Extranjería/Pasaporte)</div>
            <div style="margin-left:30px; margin-top:-60px; border: solid 1px #000; width: 80px; height: 100px;"></div>
            <div style="margin-left:250px;width: 200px; text-align: center">Ley N° 26842 Art. 4 y Art. 25a</div>
            <div style="margin-left:30px; margin-top:5px; width: 80px; text-align:center">(Huella Digital)</div>
            
        </div>
        ';
        return $html;
    }
    function imprimeanexo7c($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["nombremotivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["codestadocivil"] = $rst["empleado"][0]["estadocivil"];
        $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rst["empleado"][0]["lugarnacimiento"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rowem = $rst["empleado"][0];
        $rst["fichasietec"] = $modelEvaluacion->fichaanexo7clistar($datos);
        $rstsc = $rst["fichasietec"][0];
        $rst["fichasieted"] = $modelEvaluacion->fichaanexo7dlistar($datos);
        $rstsd = $rst["fichasieted"][0];
        $rst["rayosx"] = $modelEvaluacion->rayosxlistar($datos);
        $rstrx = $rst["rayosx"][0];
        $rst["laboratorio"] = $modelEvaluacion->laboratoriolistar($datos);
        $rstla = $rst["laboratorio"][0];
        $rst["audiometria"] = $modelEvaluacion->audiometrialistar($datos);
        $rstau = $rst["audiometria"][0];
        $rst["espirometria"]= $modelEvaluacion->espirometrialistar($datos);
        $rstes = $rst["espirometria"][0];
        $rst["odontograma"] = $modelEvaluacion->odontogramalistar($datos);
        $rstod = $rst["odontograma"][0];
        $rst["optometria"]  = $modelEvaluacion->optometrialistar($datos);
        $rst["optometria"][0]["ovcscod"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovcscod"]);
        $rst["optometria"][0]["ovcscoi"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovcscoi"]);
        $rst["optometria"][0]["ovccod"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovccod"]);
        $rst["optometria"][0]["ovccoi"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovccoi"]);
        $rst["optometria"][0]["ovlscod"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlscod"]);
        $rst["optometria"][0]["ovlscoi"] = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlscoi"]);
        $rst["optometria"][0]["ovlcod"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlcod"]);
        $rst["optometria"][0]["ovlcoi"]  = $this->devuelveNombreMedidaOptometria($rst["optometria"][0]["ovlcoi"]);
        $rstop = $rst["optometria"][0];
        
        $otros = explode("-", $rstsc["otros"]);
        
        $datosx["p_idUsuario"] = $rstsc["idUA"];
        $sesTU = new Models_Usuario();
        $resTUs = $sesTU->listar($datosx);
        $resTU = $resTUs[0];
        $html = '
        <div class="divHoja">
            <div id="divF7Clogo"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divF7Ctitulo" class="divTituloHoja">ANEXO N° 7 - C<br />FICHA MÉDICA OCUPACIONAL</div>
            <div id="divF7Ctipoexamen">
                <div class="divTitulo">Examen Médico</div>
                <div class="divCuadro">'.(($rowem["motivo"]==5)?'&nbsp;&nbsp;X':'').'</div><div class="divTexto">Pre-ocupacional</div>
                <div class="divCuadro">'.(($rowem["motivo"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTexto">Anual</div>
                <div class="divCuadro">'.(($rowem["motivo"]==4)?'&nbsp;&nbsp;X':'').'</div><div class="divTexto">Retiro</div>
            </div>
            <div class="divClear"></div>
            <div class="divF7Cattr">EMPRESA</div><div class="divF7Cval">'.(($rowem["flgtipoempresa"]==0)?$rowem["rucempresaespecializada"]:'').'</div>
            <div class="divF7Cattr">CONTRATA</div><div class="divF7Cval">'.(($rowem["flgtipoempresa"]==1)?$rowem["rucempresaespecializada"]:'').'</div>
            <div class="divF7Cattr">APELLIDOS Y NOMBRES</div><div class="divF7Cval">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"]. '</div>
            <div class="divF7Cattr">FECHA</div><div class="divF7Cval">'.date("d/m/Y").'</div>
            <div class="divClear"></div>
            <div id="divF7C1" class="dbIzq dbSup">
                <div class="divTitulo">LUGAR Y FECHA DE NACIMIENTO</div><div class="divTexCua">'.$rowem["lugarnacimiento"].'<br />'.$rowem["fechanacimiento"].'</div>
            </div>
            <div id="divF7C2" class="dbIzq dbSup">
                <div class="divTitulo">DOMICILIO FISCAL</div><div class="divTexCua">'.$rowem["direccion"].'</div>
            </div> 
            <div id="divF7C3" class="dbIzq dbSup">
                <div class="divTitulo">ÁREA DE LABOR</div>
                <div class="divCuadroF">'.(($rstsc["arealabor"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">Superficie</div>
                <div class="divCuadroF">'.(($rstsc["arealabor"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">Concentradora</div>
                <div class="divCuadroF">'.(($rstsc["arealabor"]==3)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">Sub - suelo</div>
            </div>
            <div id="divF7C4" class="dbIzq dbSup dbDer">
                <div class="divTitulo">ALTURA DE LABOR</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">Debajo de 2500</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">3500 a 4000</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==3)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">2501 a 3000</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==4)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">4001 a 4500</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==5)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">3001 a 3500</div>
                <div class="divCuadroF">'.(($rstsc["alturalabor"]==6)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoF">más de 4501</div>
            </div>
            
            <div id="divF7C5" class="dbIzq dbSup">
                <div class="divTitulo">EDAD</div><div class="divTexCua">'.$rowem["edad"].'</div>
            </div>
            <div id="divF7C6" class="dbIzq dbSup">
                <div class="divTitulo">SEXO</div>
                <div class="divCuadroF">'.(($rowem["sexo"]=="MASCULINO")?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFA">Masculino</div>
                <div class="divCuadroF">'.(($rowem["sexo"]=="FEMENINO")?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFA">Femenino</div>
            </div>
            <div id="divF7C7" class="dbIzq dbSup">
                <div class="divTitulo">DOC. DE IDENTIDAD</div><div class="divTexCua">'.$rowem["dni"].'</div>
                <div class="divTitulo">TELÉFONO</div><div class="divTexCua">'.$rowem["telefono"].'</div>
            </div>
            <div id="divF7C8" class="dbIzq dbSup">
                <div class="divTitulo">ESTADO CIVIL</div>
                <div class="divCuadroF">'.(($rowem["codestadocivil"]=='SL')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFB">Soltero</div>
                <div class="divCuadroF">'.(($rowem["codestadocivil"]=='CS')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFB">Casado</div>
                <div class="divCuadroF">'.(($rowem["codestadocivil"]=='VD')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFB">Viudo</div>
                <div class="divCuadroF">'.(($rowem["codestadocivil"]=='CV')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFB">Conviviente</div>
                <div class="divCuadroF">'.(($rowem["codestadocivil"]=='DV')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFB">Divorciado</div>
            </div>
            <div id="divF7C9" class="dbIzq dbSup dbDer">
                <div class="divTitulo">GRADO DE INSTRUCCIÓN</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Analfabeto</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Primaria comp.</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==3)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Primaria incom.</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==4)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Secundaria com.</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==5)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Secund. incom.</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==6)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Técnico</div>
                <div class="divCuadroF">'.(($rstsc["gradoinstruccion"]==7)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFC">Universitario</div>
            </div>
            <div id="divF7C10" class="dbIzq dbSup">
                <div class="divClearA"></div>
                <div class="divCuadroF">'.((in_array(1, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Ruido</div>
                <div class="divCuadroF">'.((in_array(5, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Cancerígenos</div>
                <div class="divCuadroF">'.((in_array(9, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Temperatura</div>
                <div class="divCuadroF">'.((in_array(13, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Cargas</div>
                <div class="divCuadroF">'.((in_array(2, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Polvo</div>
                <div class="divCuadroF">'.((in_array(6, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Mutagénicos</div>
                <div class="divCuadroF">'.((in_array(10, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Biológicos</div>
                <div class="divCuadroF">'.((in_array(14, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Movimientos repetitivos</div>
                <div class="divCuadroF">'.((in_array(3, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Vibración Segmentaria</div>
                <div class="divCuadroF">'.((in_array(7, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Solventes</div>
                <div class="divCuadroF">'.((in_array(11, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Poturas</div>
                <div class="divCuadroF">'.((in_array(15, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">PVD</div>
                <div class="divCuadroF">'.((in_array(4, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Vibración total</div>
                <div class="divCuadroF">'.((in_array(8, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Metales Pesados</div>
                <div class="divCuadroF">'.((in_array(12, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Turnos</div>
                <div class="divCuadroF">'.((in_array(16, $otros))?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Otros</div>
            </div>
            <div id="divF7C11" class="dbIzq dbSup dbDer">
                <div class="divTitulo">Describir según corresponda:</div>
                <div class="divTextoFF">Puesto al que postual: '.$rstsc["puestopostula"].'</div>
                <div class="divTextoFF">Puesto actual: '.$rstsc["puestoactual"].'</div>
                <div class="divTextoFG">Reubica.:</div>
                <div class="divCuadroF">'.(($rstsc["reubicacion"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE">SI</div>
                <div class="divCuadroF">'.(($rstsc["reubicacion"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE">NO</div>
                <div class="divTextoFG">Reinserc.:</div>
                <div class="divCuadroF">'.(($rstsc["reinserccion"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE">SI</div>
                <div class="divCuadroF">'.(($rstsc["reinserccion"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE">NO</div>
            </div>
            <div id="divF7C12" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">ANTECEDENTES OCUPACIONALES</div>
                <div class="divTextoCL">'.$rstsc["antecedentesocupacionales"].'</div>
            </div>
            <div id="divF7C13" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">ANTECEDENTES PERSONALES</div>
                <div class="divTextoCL">'.$rstsc["antecedentespersonales"].'</div>
            </div>
            <div id="divF7C14" class="dbIzq dbSup">
                <div class="divTextoFF">ANTECEDENTES FAMILIARES</div>
                <div class="divTextoCL">'.$rstsc["antecedentesfamiliares"].'</div>
            </div>
            <div id="divF7C15" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">INMUNIZACIONES</div>
                <div class="divTextoCL">'.$rstsc["inmunizaciones"].'</div>
            </div>
            <div id="divF7CGA">
                <div id="divF7C16" class="dbSup dbDer">
                    <div class="divTitulo">NÚMERO DE HIJOS</div>
                </div>
                <div id="divF7C17" class="dbSup dbDer">
                    <div class="divTitulo">VIVOS</div>
                </div>
                <div id="divF7C18" class="dbSup dbDer">
                    <div class="divTitulo">MUERTOS</div>
                </div>
                <div id="divF7C19" class="dbSup dbDer">
                    <div class="divTitulo">'.$rstsc["nhijosvivos"].'</div>
                </div>
                <div id="divF7C20" class="dbSup dbDer">
                    <div class="divTitulo">'.$rstsc["nhijosmuertos"].'</div>
                </div>
            </div>
            <div id="divF7C21" class="dbIzq dbSup dbDer">
                <div class="divTitulo">HÁBITOS:</div>
                <div class="divTextoFH"></div>
                <div class="divCuadroFAT">Tabaco</div>
                <div class="divCuadroFAT">Alcohol</div>
                <div class="divCuadroFAT">Drogas</div>
                
                <div class="divTextoFH">Nada</div>
                <div class="divCuadroFA">'.(($rstsc["habtabaco"]==1)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habalcohol"]==1)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habdrogas"]==1)?'&nbsp;&nbsp;X':'').'</div>
                
                <div class="divTextoFH">Poco</div>
                <div class="divCuadroFA">'.(($rstsc["habtabaco"]==2)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habalcohol"]==2)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habdrogas"]==2)?'&nbsp;&nbsp;X':'').'</div>
                
                <div class="divTextoFH">Habitual</div>
                <div class="divCuadroFA">'.(($rstsc["habtabaco"]==3)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habalcohol"]==3)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habdrogas"]==3)?'&nbsp;&nbsp;X':'').'</div>
                
                <div class="divTextoFH">Excesivo</div>
                <div class="divCuadroFA">'.(($rstsc["habtabaco"]==4)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habalcohol"]==4)?'&nbsp;&nbsp;X':'').'</div>
                <div class="divCuadroFA">'.(($rstsc["habdrogas"]==4)?'&nbsp;&nbsp;X':'').'</div>
            </div>
            <div id="divF7CGB">
                <div id="divF7C22" class="dbSup dbDer">
                    <div class="divTitulo">TALLA</div><div class="divTitulo">'.$rstsd["talla"].'</div>
                </div>
                <div id="divF7C23" class="dbSup dbDer">
                    <div class="divTitulo">PESO</div><div class="divTitulo">'.$rstsd["peso"].'</div>
                </div>
                <div id="divF7C24" class="dbSup dbDer">
                    <div class="divTitulo">IMC</div><div class="divTitulo">'.$rstsd["imc"].'</div>
                </div>
            </div>
            <div id="divF7C25" class="dbSup dbDer">
                <div class="divTitulo">FUNCIÓN RESPIRATORIA</div>
                <div class="divF7CattrA">FVC</div><div class="divF7CvalA">'.$rstes["fvc"].'</div>
                <div class="divF7CattrA">FEV1</div><div class="divF7CvalA">'.$rstes["fev"].'</div>
                <div class="divF7CattrA">FEV1/FVC</div><div class="divF7CvalA">'.$rstes["fevfvc"].'</div>
                <div class="divF7CattrA">FEF 25 - 75 %</div><div class="divF7CvalA">'.$rstes["fev2575"].'</div>
            </div>
            <div id="divF7CGC">
                <div id="divF7C26" class="dbSup dbDer">
                    <div class="divTitulo">TEMPERATURA</div>
                    <div class="divTitulo">'.$rstsd["temperatura"].' °C</div>
                </div>
                <div id="divF7C27" class="dbSup dbDer">
                    <div class="divF7CattrA">Cintura</div><div class="divF7CvalA">'.$rstsd["cintura"].'</div>
                    <div class="divF7CattrA">Cadera</div><div class="divF7CvalA">'.$rstsd["cadera"].'</div>
                    <div class="divF7CattrA">ICC</div><div class="divF7CvalA">'.$rstsd["icc"].'</div>
                </div>
            </div>
            <div id="divF7C28" class="dbIzq dbSup">
                <div class="divTextoFF">CABEZA</div>
                <div class="divTextoCL">'.$rstsc["evacabeza"].'</div>
            </div>
            <div id="divF7C29" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">NARIZ</div>
                <div class="divTextoCL">'.$rstsc["evanariz"].'</div>
            </div>
            <div id="divF7C30" class="dbIzq dbSup">
                <div class="divTextoFF">BOCA, AMIGDALAS, FARINGE Y LARINGE</div>
                <div class="divTextoCL">'.$rstsc["evaboca"].'</div>
            </div>
            <div id="divF7C31" class="dbIzq dbSup dbDer">
                <div class="divF7CattrB">Piezas en mal estado</div><div class="divF7CvalB">'.$rstod["piezamal"].'</div>
                <div class="divF7CattrB">Piezas que faltan</div><div class="divF7CvalB">'.$rstod["piezaextraida"].'</div>
            </div>
            <div id="divF7C32" class="dbIzq dbSup">
                <div class="divTextoFF">OJOS</div>
                <div class="divTextoCL" style="font-size:9px !important">'.$rstsc["evaojos"].'</div>
            </div>
            <div id="divF7CGD">
                <div id="divF7C33" class="dbIzq dbSup">
                    <div class="divTitulo">Sin corregir</div>
                </div>
                <div id="divF7C34" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">OD</div>
                </div>
                <div id="divF7C35" class="dbSup">
                    <div class="divTitulo">OI</div>
                </div>
            </div>
            <div id="divF7CGE">
                <div id="divF7C36" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">Sin corregir</div>
                </div>
                <div id="divF7C37" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">OD</div>
                </div>
                <div id="divF7C38" class="dbSup dbDer">
                    <div class="divTitulo">OI</div>
                </div>
            </div>
            <div id="divF7C39" class="dbSup dbDer">
                <div class="divTextoFF">ENFERMEDADES OCULARES</div>
                <div class="divTextoCL">'.$rstsc["evaenferoculares"].'</div>
            </div>
            
            <div id="divF7C32" class="dbIzq dbSup">
                <div class="divTextoCA">VISIÓN DE CERCA</div>
                <div class="divTextoCA">VISIÓN DE LEJOS</div>
            </div>
            <div id="divF7CGD">
                <div id="divF7C34" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovcscod"].'</div>
                </div>
                <div id="divF7C35" class="dbSup">
                    <div class="divTitulo">'.$rstop["ovcscoi"].'</div>
                </div>
                <div id="divF7C34" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovccod"].'</div>
                </div>
                <div id="divF7C35" class="dbSup">
                    <div class="divTitulo">'.$rstop["ovccoi"].'</div>
                </div>
            </div>
            <div id="divF7CGE">
                <div id="divF7C37" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovlscod"].'</div>
                </div>
                <div id="divF7C38" class="dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovlscoi"].'</div>
                </div>
                <div id="divF7C37" class="dbIzq dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovlcod"].'</div>
                </div>
                <div id="divF7C38" class="dbSup dbDer">
                    <div class="divTitulo">'.$rstop["ovlcoi"].'</div>
                </div>
            </div>
            <div id="divF7C39" class="dbSup dbDer">
                <div class="divTextoFF">REFLEJOS OCULARES</div>
                <div class="divTextoCL">'.$rstsc["evareflejoculares"].'</div>
            </div>
            <div id="divF7C40" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">VISIÓN DE COLORES: '.$rstsc["evavisioncolores"].'</div>
            </div>
            <div id="divF7C41" class="dbIzq dbSup dbDer">
                <div class="divTextoFI">OIDOS: </div>
                <div class="divTextoFI">Audición derecha</div>
                <div class="divTextoFI">Audición Izquierda</div>
                <div id="divF7C42"></div>
                <div id="divF7CGF">
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">Hz</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">500</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">1000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">2000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">3000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">4000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">6000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbDer"><div class="divTitulo">8000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">dB(A)</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad1"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad2"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad3"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad4"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad5"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ad6"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf dbDer"><div class="divTitulo">'.$rstau["ad7"].'</div></div>
                </div>
                <div id="divF7C44"></div>
                <div id="divF7CGF">
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">Hz</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">500</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">1000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">2000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">3000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">4000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup"><div class="divTitulo">6000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbDer"><div class="divTitulo">8000</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">dB(A)</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai1"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai2"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai3"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai4"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai5"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$rstau["ai6"].'</div></div>
                    <div id="divF7C43" class="dbIzq dbSup dbInf dbDer"><div class="divTitulo">'.$rstau["ai7"].'</div></div>
                </div>
            </div>
            <div id="divF7C31" class="dbIzq dbSup dbDer dbInf">
                <div class="divTextoFF">ENFERMEDADES OCULARES</div>
                <div class="divF7CattrC">OD: <span style="font-size: 9px !Important">'.$rstsc["evaotoscopiaderecho"].'</span></div>
                <div class="divF7CattrC">OI: <span style="font-size: 9px !Important">'.$rstsc["evaotoscopiaizquierdo"].'</span></div>
            </div>
            <div id="divF7C45" class="dbSup dbDer dbInf">
                <div class="divF7CattrD">Función respiratoria: '.$rstsd["frecuenciarespiratoria"].' min</div>
                <div class="divF7CattrD">Función cardiaca: '.$rstsd["frecuenciacardiaca"].' min</div>
                <div class="divF7CattrD">Sat. O2: '.$rstsd["sat"].'%</div>
            </div>
            <div id="divF7C45" class="dbSup dbDer dbInf">
                <div class="divTitulo">Presión Arterial</div>
                <div class="divF7CattrD">Sistólica: '.$rstsd["presionarteriala"].' mmHg</div>
                <div class="divF7CattrD">Diastólica: '.$rstsd["presionarterialb"].' mmHg</div>
            </div>
        </div>
        <div class="divHoja">
            <div id="divF7C46" class="dbIzq dbSup dbDer">
                <div class="divTextoFF" style="width:600px !important">
                <div class="divTextoFE" style="width:60px !Important;">PULMONES</div>
                <div class="divCuadroFA">'.(($rstsc["evapulmonesflg"]==0)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:60px !Important;">NORMAL</div>
                <div class="divCuadroFA">'.(($rstsc["evapulmonesflg"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:60px !Important;">ANORMAL</div>
                </div>
                <div class="divTextoCL">'.$rstsc["evapulmonesdescr"].'</div>
            </div>
            <div id="divF7C46" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">MIEMBROS SUPERIORES</div>
                <div class="divTextoCL">'.$rstsc["evamiembrossup"].'</div>
            </div>
            <div id="divF7C46" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">MIEMBROS INFERIORES</div>
                <div class="divTextoCL">'.$rstsc["evamiembrosinf"].'</div>
            </div>
            <div id="divF7C47" class="dbIzq dbSup">
                <div class="divTextoFF">REFLEJOS OSTEO - TENDINOSOS</div>
                <div class="divTextoCL">'.$rstsc["evareflejososteo"].'</div>
            </div>
            <div id="divF7C48" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">MARCHA</div>
                <div class="divTextoCL">'.$rstsc["evamarcha"].'</div>
            </div>
            <div id="divF7C46" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">COLUMNA VERTEBRAL</div>
                <div class="divTextoCL">'.$rstsc["evacolumnavertebral"].'</div>
            </div>
            <div id="divF7C49" class="dbIzq dbSup">
                <div class="divTextoFF">ABDOMEN</div>
                <div class="divTextoCL">'.$rstsc["evaabdomen"].'</div>
            </div>
            <div id="divF7C50" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">TACTO RECTAL</div>
                <div class="divClearA"></div>
                <div class="divCuadroF">'.(($rstsc["evatactorectal"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Ruido</div>
                <div class="divCuadroF">'.(($rstsc["evatactorectal"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Cancerígenos</div>
                <div class="divCuadroF">'.(($rstsc["evatactorectal"]==3)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Temperatura</div>
                <div class="divCuadroF">'.(($rstsc["evatactorectal"]==4)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFD">Cargas</div>
            </div>
            <div id="divF7C51" class="dbIzq dbSup">
                <div class="divTextoFF">ANILLOS INGUINALES</div>
                <div class="divTextoCL">'.$rstsc["evaanillosinguinales"].'</div>
            </div>
            <div id="divF7C52" class="dbIzq dbSup">
                <div class="divTextoFF">HERNIAS</div>
                <div class="divTextoCL">'.$rstsc["evahernias"].'</div>
            </div>
            <div id="divF7C53" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">VARICES</div>
                <div class="divTextoCL">'.$rstsc["evavarices"].'</div>
            </div>
            <div id="divF7C47" class="dbIzq dbSup">
                <div class="divTextoFF">ORGANOS GENITALES</div>
                <div class="divTextoCL">'.$rstsc["evaorganosgenitales"].'</div>
            </div>
            <div id="divF7C48" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">GANGLIOS</div>
                <div class="divTextoCL">'.$rstsc["evaganglios"].'</div>
            </div>
            <div id="divF7C46" class="dbIzq dbSup dbDer">
                <div class="divTextoFF">LENGUAJE, ATENCIÓN, MEMORIA, ORIENTACIÓN, INTELIGENCIA, AFECTIVIDAD</div>
                <div class="divTextoCL">'.$rstsc["evalenguaje"].'</div>
            </div>
            <div id="divF7C54" class="dbIzq dbSup dbDer">
                <img src="../public/images/pulmon.png" width="150" style="margin:10px 10px 5px 23px;" />
                <div class="divTextoCB">N° registro:</div><div class="divTextoCB">'.$rstrx["nregistro"].'</div>
                <div class="divTextoCB">Fecha:</div><div class="divTextoCB">'.$rstrx["fecha"].'</div>
                <div class="divTextoCB">Calidad:</div><div class="divTextoCB">'.$rstsc["evacalidad"].'</div>
                <div class="divTextoCB">Símbolos:</div><div class="divTextoCB">'.$rstsc["evasimbolos"].'</div>
            </div>
            <div id="divF7C55" class="dbSup dbDer">
                <div class="divTextoCC">Vértices:</div><div class="divTextoCD">'.$rstsc["evavertices"].'</div>
                <div class="divTextoCC">Campos pulmonares:</div><div class="divTextoCD">'.$rstsc["evacampospulmonares"].'</div>
                <div class="divTextoCC">Hilios:</div><div class="divTextoCD">'.$rstsc["evahilios"].'</div>
                <div class="divTextoCC">Senos:</div><div class="divTextoCD">'.$rstsc["evasenos"].'</div>
                <div class="divTextoCC">Mediastino:</div><div class="divTextoCD">'.$rstsc["evamediastino"].'</div>
                <div class="divTextoCC">Silueta cardiaca:</div><div class="divTextoCD">'.$rstsc["evasiluetacardiaca"].'</div>
                <div class="divTextoCC">Conclusiones radiográficas:</div><div class="divTextoCD">'.$rstsc["evaconclusionesradiograficas"].'</div>
            </div>
            <div id="divF7CGH">
                <div id="divF7CGG">
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:30px; '.(($rstsc["evaradioneumoflg"]==1)?'border:solid 1px #000':'').')">0/0</div></div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:30px; '.(($rstsc["evaradioneumoflg"]==2)?'border:solid 1px #000':'').')">1/0</div></div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:20px; '.(($rstsc["evaradioneumoflg"]==3)?'border:solid 1px #000':'').')">1/1</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==4)?'border:solid 1px #000':'').')">1/2</div></div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:10px; '.(($rstsc["evaradioneumoflg"]==5)?'border:solid 1px #000':'').')">2/1</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==6)?'border:solid 1px #000':'').')">2/2</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==7)?'border:solid 1px #000':'').')">2/3</div></div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:10px; '.(($rstsc["evaradioneumoflg"]==8)?'border:solid 1px #000':'').')">3/2</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==9)?'border:solid 1px #000':'').')">3/3</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==10)?'border:solid 1px #000':'').')">3/+</div></div></div>
                    <div id="divF7C57" class="dbIzq dbSup dbDer"><div class="divTitulo" style="padding-top:3px"><div style="width:20px; margin-left:10px; '.(($rstsc["evaradioneumoflg"]==11)?'border:solid 1px #000':'').')">A</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==12)?'border:solid 1px #000':'').')">B</div><div style="width:20px; '.(($rstsc["evaradioneumoflg"]==13)?'border:solid 1px #000':'').')">C</div></div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo">CERO</div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo">1/0</div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo">UNO</div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo">DOS</div></div>
                    <div id="divF7C56" class="dbIzq dbSup "><div class="divTitulo">TRES</div></div>
                    <div id="divF7C57" class="dbIzq dbSup dbDer"><div class="divTitulo">CUATRO</div></div>
                </div>
                <div id="divF7C58" class="dbSup dbDer"><div class="divTitulo">St</div></div>
                <div id="divF7C60" class="dbIzq dbSup dbDer"><div class="divTitulo">Sin Neumoconiosis<br />"NORMAL"</div></div>
                <div id="divF7C60" class="dbSup dbDer"><div class="divTitulo">Imagen radiográfica de exposición a polvo<br />"SOSPECHA"</div></div>
                <div id="divF7C59" class="dbSup dbDer"><div class="divTitulo"><pre style="font-size:9px; font-family: tahoma; text-align:left">'.$rstsc["evaradiodescr"].'</pre></div></div>
            </div>
            <div id="divF7CGI">
                <div id="divF7C61" class="dbSup dbDer"><div class="divTitulo">Reacciones serológicas o lúes</div>
                    <div class="divClearA"></div>
                    <div class="divCuadroF">'.(($rstla["rpr"]==0)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFJ">Negativo</div>
                    <div class="divCuadroF">'.(($rstla["rpr"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFJ">Positivo</div>
                </div>
                <div id="divF7C62" class="dbSup dbDer"><div class="divTitulo">Otros exámenes: '.$rstsc["evaotrosexamenes"].'</div></div>
            </div>
            <div id="divF7CGJ">
                <div id="divF7C63" class="dbIzq dbSup dbDer"><div class="divTextoFF" style="width: 400px !Important;">GRUPO SANGUÍNEO</div>
                <div class="divCuadroFA">'.(($rstla["gruposanguineo"]=='O')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">O</div>
                <div class="divCuadroFA">'.(($rstla["gruposanguineo"]=='A')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">A</div>
                <div class="divCuadroFA">'.(($rstla["gruposanguineo"]=='B')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">B</div>
                <div class="divCuadroFA">'.(($rstla["gruposanguineo"]=='AB')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">AB</div>
                <div class="divCuadroFA">'.(($rstla["factorsanguineo"]==0)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">Rh(-)</div>
                <div class="divCuadroFA">'.(($rstla["factorsanguineo"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFE" style="width:19px !Important;">Rh(+)</div>
                </div>
                <div id="divF7C64" class="dbSup dbDer"><div class="divTitulo">HEMOGLOBINA/HEMATOCRITO<br />'.$rstla["hemoglobina"].' gr%</div></div>
                <div id="divF7C65" class="dbIzq dbSup dbDer"><div class="divTitulo">APTO PARA TRABAJAR</div>
                    <div class="divClearA"></div>
                    <div class="divCuadroF">'.(($rstsc["evaaptotrabajar"]==1)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFJ">Positivo</div>
                    <div class="divCuadroF">'.(($rstsc["evaaptotrabajar"]==2)?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFJ">Negativo</div>
                </div>
                <div id="divF7C66" class="dbSup dbDer"><div class="divTextoFF">
                <strong>'.$resTU["apellidos"].' '.$resTU["nombres"].'</strong><br />Nombres y Apellidos del Médico<br />
                N° de Colegiatura '.$resTU["ncolegiatura"].'<br />Firma y sello</div></div>
                <div id="divF7C67" class="dbIzq dbSup dbDer"><div class="divTextoFF" style="width:90% !important">OBSERVACIONES<br />'.$rstsc["evaobservaciones"].'</div></div>
                <div id="divF7C68" class="dbIzq dbSup dbDer dbInf"><div class="divTextoFF" style="width:90% !important">RECOMENDACIONES<br />'.$rstsc["evarecomendaciones"].'</div></div>
            </div>
            <div id="divF7CGI">
                <div id="divF7C69" class="dbSup dbDer"><div class="divTitulo">Firma del examinado</div></div>
                <div id="divF7C70" class="dbSup dbDer"><div class="divTitulo">Huella Digital índice derecho</div></div>
                <div id="divF7C71" class="dbSup dbDer dbInf"><div class="divTitulo">Declaro que toda la información es verdadera</div></div>
            </div>
        </div>';
        return $html;
    }
    function imprimeanexo7d($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["motivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rowem = $rst["empleado"][0];
        $rst["fichasieted"] = $modelEvaluacion->fichaanexo7dlistar($datos);
        $rowsd = $rst["fichasieted"][0];
        
        $datos["p_idUsuario"] = $rowsd["idUA"];
        $sesTU = new Models_Usuario();
        $resTUs = $sesTU->listar($datos);
        $resTU = $resTUs[0];
        $html = '
        <div class="divHoja">
            <div id="divF7D1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divF7D2" class="divTituloHoja">ANEXO N° 7 - D<br />EVALUACIÓN MÉDICA PARA ASCENSO A GRANDES ALTITUDES<br />(mayor de 2,500 m.s.n.m.)</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divF7D3" class="dbIzq dbSup dbDer"><div class="divTextoFK">DATOS PERSONALES</div></div>
            <div id="divF7D3" class="dbIzq dbSup dbDer"><div class="divTextoFK" style="width: 90% !Important;">Apellidos y nombres: '.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</div></div>
            <div id="divF7D4" class="dbIzq dbSup "><div class="divTitulo">Documento de identidad<br />'.$rowem["dni"].'</div></div>
            <div id="divF7D5" class="dbIzq dbSup "><div class="divTitulo">Fecha de nacimiento<br />'.$rowem["fechanacimiento"].'</div></div>
            <div id="divF7D6" class="dbIzq dbSup "><div class="divTitulo">Edad<br />'.$rowem["edad"].'</div></div>
            <div id="divF7D7" class="dbIzq dbSup dbDer">
                <div class="divTitulo">Sexo</div>
                <div class="divCuadroF">'.(($rowem["sexo"]=='MASCULINO')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFA">Masculino</div>
                <div class="divCuadroF">'.(($rowem["sexo"]=='FEMENINO')?'&nbsp;&nbsp;X':'').'</div><div class="divTextoFA">Femenino</div>
            </div>
            <div id="divF7D3" class="dbIzq dbSup dbDer"><div class="divTextoFK" style="width: 90% !Important;">Dirección: '.$rowem["direccion"].'</div></div>
            <div id="divF7D8" class="dbIzq dbSup dbInf"><div class="divTextoFK" style="width: 90% !Important;">Empleador: '.$rowem["rucempresaespecializada"].'</div></div>
            <div id="divF7D9" class="dbIzq dbSup dbDer dbInf"><div class="divTextoFK" style="width: 90% !Important;">Actividad a realizar: '.$rowem["puesto"].'</div></div>
            <div class="divClear"></div>
            <div class="divClear"></div>            
            <div id="divF7D3" class="dbIzq dbSup dbDer"><div class="divTextoFK">Funciones vitales: </div></div>
            <div id="divF7D10" class="dbIzq dbSup dbInf"><div class="divTitulo">FC: '.$rowsd["frecuenciacardiaca"].' x min</div></div>
            <div id="divF7D10" class="dbIzq dbSup dbInf"><div class="divTitulo">PA: '.$rowsd["presionarteriala"].'/'.$rowsd["presionarterialb"].' mmHg</div></div>
            <div id="divF7D10" class="dbIzq dbSup dbInf"><div class="divTitulo">FR: '.$rowsd["frecuenciarespiratoria"].' x min</div></div>
            <div id="divF7D10" class="dbIzq dbSup dbInf"><div class="divTitulo">IMC. '.$rowsd["imc"].' kg/m2</div></div>
            <div id="divF7D11" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">Sat. O2 '.$rowsd["sat"].'%</div></div>
            <div class="divClearB">El/La presenta o ha presentado en los últimos 6 meses:</div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTitulo">DETALLE</div></div>
            <div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">SI</div></div>
            <div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">NO</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Anemia<br />'.$rowsd["opcdesc1"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc1"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc1"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Cirugia mayor reciente<br />'.$rowsd["opcdesc2"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc2"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc2"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Desórdenes de la coagulación, trombosis, etc.<br />'.$rowsd["opcdesc3"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc3"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc3"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Diabetes Mellitus<br />'.$rowsd["opcdesc4"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc4"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc4"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Hipertensión Arterial<br />'.$rowsd["opcdesc5"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc5"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc5"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important">
                <div class="divTextoFL" style="width:98% !important">
                Embarazo'.(($rowem["sexo"]=='MASCULINO')?'(NO APLICA)':'').'<br />'.$rowsd["opcdesc6"].'
                </div>
            </div>
            <div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important">
                <div class="divTitulo">'.(($rowsd["opc6"]==1 && $rowem["sexo"]!='MASCULINO')?'X':'-').'</div>
            </div>
            <div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important">
                <div class="divTitulo">'.(($rowsd["opc6"]==0 && $rowem["sexo"]!='MASCULINO')?'X':'-').'</div>
            </div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Problemas neurológicos: epilepsia, vértigo, etc<br />'.$rowsd["opcdesc7"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc7"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc7"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Infecciones recientes ( especialmente oídos, nariz, garganta)<br />'.$rowsd["opcdesc8"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc8"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc8"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Obesidad Mórbida (IMC mayor a 35 m/kg2)<br />'.$rowsd["opcdesc9"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc9"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc9"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Problemas Cardíacos: marcapasos, coronariaopatía, etc.<br />'.$rowsd["opcdesc10"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc10"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc10"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Problemas Respiratorios: asma, EPOC, etc.<br />'.$rowsd["opcdesc11"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc11"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc11"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Problemas Oftalmológicos: retinopatía, glaucoma, etc.<br />'.$rowsd["opcdesc12"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc12"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc12"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Problemas Digestivos: úlcera péptica, hepatitis, etc.<br />'.$rowsd["opcdesc13"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc13"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc13"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Apnea del Sueño<br />'.$rowsd["opcdesc14"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc14"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc14"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Otra condición médica importante<br />'.$rowsd["opcdesc15"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc15"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc15"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Alergias<br />'.$rowsd["opcdesc16"].'</div></div><div id="divF7D13" class="dbIzq dbSup" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc16"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc16"]==0)?'X':'').'</div></div>
            <div id="divF7D12" class="dbIzq dbSup dbInf" style="width: 667px !important"><div class="divTextoFL" style="width:98% !important">Uso de medicación actual<br />'.$rowsd["opcdesc17"].'</div></div><div id="divF7D13" class="dbIzq dbSup dbInf" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc17"]==1)?'X':'').'</div></div><div id="divF7D14" class="dbIzq dbSup dbDer dbInf" style="width: 40px !important"><div class="divTitulo">'.(($rowsd["opc17"]==0)?'X':'').'</div></div>
            <div class="divClearB">Por lo que certifico que EL/LA  paciente se encuentra APTO('.(($rowsd["apto"]==1)?'X':'  ').') para ascender a grandes altitudes, sin embargo, no aseguramos la respuesta durante el ascenso ni durante su permanencia.</div>
            <div class="divClearB">Observaciones: '.$rowsd["observaciones"].'</div>
            <div class="divClear"></div>
            <div id="divF7D3" class="dbIzq dbSup dbDer"><div class="divTextoFK">DATOS DEL MÉDICO</div></div>
            <div id="divF7D8" class="dbIzq dbSup dbInf"><div class="divTextoFK">Apellidos: '.$resTU["apellidos"].'</div></div>
            <div id="divF7D9" class="dbIzq dbSup dbDer dbInf"><div class="divTextoFK">Nombres: '.$resTU["nombres"].'</div></div>
            <div id="divF7CGK">
                <div id="divF7D15" class="dbIzq dbDer"><div class="divTextoFK" style="width: 300px !Important">Dirección: '.$rowsd["direccion"].'</div></div>
                <div id="divF7D16" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">CMP: '.$resTU["ncolegiatura"].'</div></div>
                <div id="divF7D17" class="dbSup dbDer dbInf"><div class="divTitulo"><br />Fecha:<br /> '.date("d/m/Y").'</div></div>
            </div>
            <div id="divF7D18" class="dbDer dbInf"><div class="divTextoFK">Firma y sello: </div></div>
        </div>
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFME2" class="divTituloHojaA"><br />PAUTAS PARA EL MÉDICO EXAMINADOR</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFPV5" class="divTextoCF">
                <strong>I. EXAMEN FÍSICO Y AUXILIARES</strong><br /><br />
                - Especial énfasis en el examen de piel y mucosas para descartar anemia.<br />
                - Nunca deben de faltar el examen físico de los aparatos cardiovascular y pulmonar.<br />
                - Se debe de realizar eletrocardiograma a todos los mayores de 45 años.<br />
                - En caso de sospecha clínica de patología cardiovascular solicitar una prueba de esfuerzo.<br />
                - A cualquier edad, en caso de que el paciente tenga 1 factor de riesgo mayor a 2 menores se debe de ampliar el examen
                con un hematocrito, electrocardiograma, ergometría y dependiendo del resultado derivarlo al especialista en cardiología.
                <br /><br /><br />
                <strong>II. CONDICIONES CLÍNICAS QUE AMERITAN AMPLIACIÓN DEL ESTUDIO CARDIOVASCULAR CON PRUEBA DE TOLERANCIA
                A LA HIPOXIA:</strong><br /><br />
                a. Anemia<br />
                b. Insuficiencia cardíaca CF I y II<br />
                c. Valvulopatía CF I y II<br />
                d. Hipertensión arterial no controlada<br />
                e. Poliglobulia con plétora<br />
                f. Pacientes con revascularización coronaria.<br />
                g. EPOC<br />
                h. Hipertensión Pulmonar<br />
                i. IMC entre 35 y 40 Kg/m2<br />
                j. Otras patologías Cardiacas (controladas y certificadas por Médico Cardiólogo)<br />
                k. Transtornos del ritmo cardiaco<br />
                l. Diabetes mellitas no controlada<br />
                m. Neumectomía<br />
                n. Patrón espirométrico restrictivo de cualquier causa<br /><br /><br />
                <strong>III. CONTRAINDICACIONES ABSOLUTAS PARA SUBIR A LA GRAN ALTURA</strong><br /><br />
                - IC clase funcional III o mayor<br />
                - Valvulopatia clase funcional III o mayor<br />
                - IMA en los últimos 3 meses<br />
                - ACV en los últimos 3 meses<br />
                - Presencia de angina inestable<br />
                - Epilepsia<br />
                - Embarazo<br />
                - Anemia<br />
                - EPOC severo<br />
                - IMC mayor de 40 K/m2<br />
                - Presencia de marcapaso<br />
                - Antecedente de Trombosis Venosa Cerebral<br />
                - Cirugía mayor reciente<br />
                - Miocardiopatía hipertrófica obstructiva<br />
                - Trombosis venosa profunda (últimos 6 meses)<br />
            </div>
        </div>
        ';
        return $html;
    }
    function imprimehistoria($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["lugarnacimiento"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rst["empleado"][0]["direccion"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rowem = $rst["empleado"][0];
        $rowhi = $modelEvaluacion->historiaocupacionallistar($datos);
        $rowhi = $rowhi[0];
        $rowrx = $modelEvaluacion->rayosxlistar($datos);
        $rowrx = $rowrx[0];
        $html = '
        <div class="divHojaL">
            <div id="divFHO1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFHO2" class="divTituloHoja">HISTORIA OCUPACIONAL</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFHO3">Apellidos y nombres:</div>
            <div id="divFHO4">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</div>
            <div id="divFHO3">Nro. de registro</div>
            <div id="divFHO5">'.$rowrx["nregistro"].'</div>
            <div id="divFHO3">Fecha de nacimiento</div>
            <div id="divFHO5">'.$rowem["fechanacimiento"].'</div>
            <div id="divFHO3">Sexo</div>
            <div id="divFHO5">'.$rowem["sexo"].'</div>
            <div id="divFHO3">Lugar de nacimiento:</div>
            <div id="divFHO4">'.$rowem["lugarnacimiento"].'</div>
            <div id="divFHO3">Lugar de procedencia:</div>
            <div id="divFHO4">'.$rowem["direccion"].'</div>
            <div id="divFHO3">Profesión</div>
            <div id="divFHO4">'.$rowem["puesto"].'</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFHO6" class="dbIzq dbSup"><div class="divTitulo">Fecha de inicio</div></div>
            <div id="divFHO7" class="dbIzq dbSup"><div class="divTitulo">Empresas</div></div>
            <div id="divFHO8" class="dbIzq dbSup"><div class="divTitulo">Altitud</div></div>
            <div id="divFHO9" class="dbIzq dbSup"><div class="divTitulo">Actividad de la empresa</div></div>
            <div id="divFHO10" class="dbIzq dbSup"><div class="divTitulo">Area de trabajo</div></div>
            <div id="divFHO11" class="dbIzq dbSup"><div class="divTitulo">Ocupación</div></div>
            <div id="divF7CGL">
                <div id="divFHO26" class="dbIzq dbSup"><div class="divTitulo">Tiempo de trabajo</div></div>
                <div id="divFHO12" class="dbIzq dbSup"><div class="divTitulo">Subsuelo</div></div>
                <div id="divFHO13" class="dbIzq dbSup"><div class="divTitulo">Superficie</div></div>
            </div>
            <div id="divFHO14" class="dbIzq dbSup"><div class="divTitulo">Peligros/Agentes ocupacionales</div></div>
            <div id="divF7CGM">
                <div id="divFHO15" class="dbIzq dbSup dbDer"><div class="divTitulo">Uso EPP</div></div>            
                <div id="divFHO15" class="dbIzq dbSup dbDer"><div class="divTitulo">Tipo EPP</div></div>            
            </div>
            <div id="divFHO16" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["fechainicio"].'</div>
            <div id="divFHO17" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["empresa"].'</div>
            <div id="divFHO18" class="dbIzq dbSup dbInf divTitulo">'.$this->devuelveNombreAltura($rowhi["altitud"]).'</div>
            <div id="divFHO19" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["actividadempresa"].'</div>
            <div id="divFHO20" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["areatrabajo"].'</div>
            <div id="divFHO21" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["ocupacion"].'</div>
            <div id="divFHO22" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["ttsubsuelo"].'</div>
            <div id="divFHO23" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["ttsuperficie"].'</div>
            <div id="divFHO24" class="dbIzq dbSup dbInf divTitulo">'.$rowhi["pelageocupacional"].'</div>
            <div id="divFHO25" class="dbIzq dbSup dbInf dbDer divTitulo">'.$rowhi["usotipoepp"].'</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFHO27"><div class="divTitulo">Fecha: '.date("d/m/Y").'</div></div>            
            <div id="divFHO28"><div class="divTitulo">Firma del trabajador</div></div>       
        </div>
        ';
        return $html;
    }
    function imprimeevamed($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["nombremotivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["codestadocivil"] = $rst["empleado"][0]["estadocivil"];
        $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rst["empleado"][0]["lugarnacimiento"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rowem = $rst["empleado"][0];
        $rst["trabajoaltura"] = $modelEvaluacion->trabajoalturalistar($datos);
        $rstta = $rst["trabajoaltura"][0];
        
        $datos["p_idUsuario"] = $rstta["idUA"];
        $sesTU = new Models_Usuario();
        $resTUs = $sesTU->listar($datos);
        $resTU = $resTUs[0];
        
        $html = '
        <div class="divHoja">
            <div id="divFEM1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFEM2" class="divTituloHoja">EVALUACIÓN MÉDICA DE SUFICIENCIA FÍSICA Y PSICOLÓGICA<br />PARA TRABAJOS EN ALTURA</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFEM3" class="dbIzq dbSup dbInf"><div class="divTitulo">Razón social</div></div>
            <div id="divFEMGA">
                <div id="divFEM4" class="dbIzq dbSup"><div class="divTitulo">EMPRESA</div></div>            
                <div id="divFEM5" class="dbIzq dbSup"><div class="divTitulo">EMP. ESPEC.</div></div>            
                <div id="divFEM6" class="dbIzq dbSup dbDer"><div class="divTitulo">OTROS</div></div>            
                <div id="divFEM7" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rowem["flgtipoempresa"]==0)?$rowem["rucempresaespecializada"]:'').'</div></div>            
                <div id="divFEM8" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rowem["flgtipoempresa"]==1)?$rowem["rucempresaespecializada"]:'').'</div></div>            
                <div id="divFEM9" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo"></div></div>            
            </div>
            <div id="divFEMGB">
                <div id="divFEM10" class="dbIzq dbSup dbDer"><div class="divTitulo">FECHA DE EXAMEN</div></div>            
                <div id="divFEM11" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.date("d/m/Y").'</div></div>
            </div>
            <div class="divClear"></div>
            <div id="divFEM12" class="dbIzq dbSup dbInf"><div class="divTitulo">APELLIDOS Y NOMBRES<br />'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"]. '</div></div>
            <div id="divFEM13" class="dbIzq dbSup dbInf"><div class="divTitulo">EDAD - AÑOS<br />'.$rowem["edad"].'</div></div>
            <div id="divFEM14" class="dbIzq dbSup dbInf dbDer"><div class="divTitulo">DOCUMENTO DE IDENTIDAD<br />'.$rowem["dni"].'</div></div>
            <div class="divClear"></div>
            <div id="divFEMGC">
                <div id="divFEM15" class="dbIzq dbSup "><div class="divTitulo">HABITOS</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">NADA</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">POCO</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">HABITUAL</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">EXCESIVO</div></div>
                <div id="divFEM15" class="dbIzq dbSup "><div class="divTitulo">Coca</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habcoca"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habcoca"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habcoca"]==3)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["habcoca"]==4)?'X':'').'</div></div>
                <div id="divFEM15" class="dbIzq dbSup "><div class="divTitulo">Alcohol</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habalcohol"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habalcohol"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habalcohol"]==3)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["habalcohol"]==4)?'X':'').'</div></div>
                <div id="divFEM15" class="dbIzq dbSup "><div class="divTitulo">Tabaco</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habtabaco"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habtabaco"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["habtabaco"]==3)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["habtabaco"]==4)?'X':'').'</div></div>
                <div id="divFEM15" class="dbIzq dbSup dbInf"><div class="divTitulo">Drogas</div></div>            
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["habdrogas"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["habdrogas"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["habdrogas"]==3)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.(($rstta["habdrogas"]==4)?'X':'').'</div></div>
            </div>
            <div id="divFEM17"></div>
            <div id="divFEMGD">
                <div id="divFEM18" class="dbIzq dbSup "><div class="divTitulo">FOBIAS</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">NADA</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">POCO</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">EXCESIVO</div></div>
                <div id="divFEM18" class="dbIzq dbSup "><div class="divTitulo">Altura</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["fobaltura"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["fobaltura"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["fobaltura"]==3)?'X':'').'</div></div>
                <div id="divFEM18" class="dbIzq dbSup "><div class="divTitulo">Lugares cerrados</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["foblugcer"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["foblugcer"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["foblugcer"]==3)?'X':'').'</div></div>
                <div id="divFEM18" class="dbIzq dbSup "><div class="divTitulo">Espacios confinados</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["fobespcon"]==1)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["fobespcon"]==2)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["fobespcon"]==3)?'X':'').'</div></div>
                <div id="divFEM18" class="dbIzq dbSup dbInf"><div class="divTitulo"></div></div>            
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo"></div></div>
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo"></div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo"></div></div>
            </div>
            <div class="divClear"></div>
            <div id="divFEMGC">
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">EPILEPSIA</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">SI</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">NO</div></div>
                <div id="divFEM19" class="dbIzq dbSup dbInf"><div class="divTitulo">Antecedente convulsivo</div></div>            
                <div id="divFEM20" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["epilepsia"]==3)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.(($rstta["epilepsia"]==3)?'X':'').'</div></div>
            </div>
            <div id="divFEM23"></div>
            <div id="divFEMGD">
                <div id="divFEM21" class="dbIzq dbSup "><div class="divTitulo"></div></div>            
                <div id="divFEM22" class="dbIzq dbSup "><div class="divTitulo">CERCA</div></div>
                <div id="divFEM22" class="dbIzq dbSup dbDer"><div class="divTitulo">LEJOS</div></div>
                <div id="divFEM21" class="dbIzq dbSup dbInf"><div class="divTitulo">VISION</div></div>            
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$this->devuelveNombreMedidaOptometria($rstta["viscerder"]).'</div></div>
                <div id="divFEM24" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$this->devuelveNombreMedidaOptometria($rstta["viscerizq"]).'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbInf"><div class="divTitulo">'.$this->devuelveNombreMedidaOptometria($rstta["vislejder"]).'</div></div>
                <div id="divFEM24" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.$this->devuelveNombreMedidaOptometria($rstta["vislejizq"]).'</div></div>
            </div>
            <div class="divClear"></div>
            <div id="divFEMGC">
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">VERTIGO</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">SI</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">NO</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Antecedentes de mareos</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["vertigo1"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["vertigo1"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Prueba de dedo - nariz</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["vertigo2"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["vertigo2"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Prueba de Romberg</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["vertigo3"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["vertigo3"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Nistagmus</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["vertigo4"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["vertigo4"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Test de Unterberger</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["vertigo5"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["vertigo5"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup dbInf"><div class="divTitulo">Test de Babinsky - Weil</div></div>            
                <div id="divFEM20" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["vertigo6"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.(($rstta["vertigo6"]==1)?'X':'').'</div></div>
            </div>
            <div id="divFEM25"></div>
            <div id="divFEMGC">
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">ASMA BRONQUIAL</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">SI</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">NO</div></div>
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">Antecedentes personales de asma-alergia</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["asma1"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["asma1"]==1)?'X':'').'</div></div>
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">Antecedentes familiares</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["asma2"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["asma2"]==1)?'X':'').'</div></div>
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">Capacidad vital a 3,000 msnm o más</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["asma3"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["asma3"]==1)?'X':'').'</div></div>
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">Perímetro Toráxico</div></div>            
                <div id="divFEM27" class="dbIzq dbSup dbDer"><div class="divTitulo">'.$rstta["asma4"].'</div></div>
                <div id="divFEM26" class="dbIzq dbSup "><div class="divTitulo">Máxima inspiración</div></div>            
                <div id="divFEM27" class="dbIzq dbSup dbDer"><div class="divTitulo">'.$rstta["asma5"].'</div></div>
                <div id="divFEM26" class="dbIzq dbSup dbInf"><div class="divTitulo">Expiración forzada</div></div> 
                <div id="divFEM27" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.$rstta["asma6"].'</div></div>
            </div>
            <div class="divClear"></div>
            <div id="divFEMGC">
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">EVALUACIÓN CARDIOLÓGICA</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">SI</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">NO</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Disnea en reposo</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard1"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard1"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Disnea con la marcha</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard2"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard2"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Disnea paraxística</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard3"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard3"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Cianosis</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard4"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard4"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Examen cardiológico normal</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard5"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard5"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Edemas -Ascitis-</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard6"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard6"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Hepatomegalia</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard7"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard7"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Ingurgitaciones yugular</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evacard8"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evacard8"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup dbInf"><div class="divTitulo">Reflejo Hepato-yugular</div></div>            
                <div id="divFEM20" class="dbIzq dbSup dbInf"><div class="divTitulo">'.(($rstta["evacard9"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">'.(($rstta["evacard9"]==1)?'X':'').'</div></div>
                <div class="divClear"></div>
                <div id="divFEM19" class="dbIzq dbSup dbInf" style="width:180px !important;"><div class="divTitulo">INDICE DE MASA CORPORAL</div></div>            
                <div id="divFEM27" class="dbIzq dbSup dbDer dbInf" style="width:183px !important;"><div class="divTitulo">'.$this->devuelveIMC($rstta["indicemasa"]).'</div></div>
                <div class="divClear"></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">SISTEMA LOCOMOTOR</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">SI</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">NO</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Fractura de cadera</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc1"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc1"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Fractura de fémur, tibia, perone, pie</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc2"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc2"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Fractura, humero, cubito, radio, mano</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc3"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc3"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Operación de miembros inferiores</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc4"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc4"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Operación de miembros superiores</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc5"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc5"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup "><div class="divTitulo">Deambulación adecuada</div></div>            
                <div id="divFEM20" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["sisloc6"]==0)?'X':'').'</div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["sisloc6"]==1)?'X':'').'</div></div>
                <div id="divFEM19" class="dbIzq dbSup dbInf"><div class="divTitulo"></div></div>            
                <div id="divFEM20" class="dbIzq dbSup dbInf"><div class="divTitulo"></div></div>
                <div id="divFEM20" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo"></div></div>                
            </div>
            <div id="divFEM28"></div>
            <div id="divFEMGC">
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">EVALUACIÓN PSICOLÓGICA</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">BUENO</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">MALO</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">LOTEP</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi1"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi1"]==1)?'X':'').'</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">Concentración</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi2"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi2"]==1)?'X':'').'</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">Memoria: mediana - intermedia</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi3"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi3"]==1)?'X':'').'</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">Pensamiento abstracto - interpretar un refrán</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi4"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi4"]==1)?'X':'').'</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">Estado de ánimo</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi5"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi5"]==1)?'X':'').'</div></div>
                <div id="divFEM30" class="dbIzq dbSup "><div class="divTitulo">Ordenes sencillas - coloque su pulgar derecho sobre la oreja izquierda</div></div>            
                <div id="divFEM31" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi6"]==0)?'X':'').'</div></div>
                <div id="divFEM31" class="dbIzq dbSup dbDer"><div class="divTitulo">'.(($rstta["evapsi6"]==1)?'X':'').'</div></div>
                <div id="divFEM29" class="dbIzq dbSup "><div class="divTitulo">Relación en el espacio</div></div>            
                <div id="divFEM16" class="dbIzq dbSup "><div class="divTitulo">'.(($rstta["evapsi7"]==0)?'X':'').'</div></div>
                <div id="divFEM16" class="dbIzq dbSup dbDer "><div class="divTitulo">'.(($rstta["evapsi7"]==1)?'X':'').'</div></div>
                <div id="divFEM32" class="dbIzq dbSup dbDer dbInf">Dibujos para copiar:<br /><img src="../public/images/imgpoligonospeque.jpg" /></div>
            </div>
            <div class="divClear"></div>
            <div id="divFEM33" class="dbIzq dbSup dbDer"><div class="divF7CattrD">OBSERVACIONES<br /></div></div>
            <div id="divFEMGD">
                <div id="divFEM34" class="dbIzq dbSup dbDer dbInf"><div class="divTitulo">APTO PARA TRABAJAR</div></div>
                <div id="divFEM35" class="dbIzq dbInf dbDer"><div class="divTitulo">FIRMA DEL TRABAJADOR - HUELLA DIGITAL<br /></div></div>
            </div>
            <div id="divFEM36" class="dbInf dbSup dbDer"><div class="divTitulo">SELLO Y FIRMA DEL MÉDICO<br />
            '.$resTU["apellidos"].' '.$resTU["nombres"].'<br />C.M.P. '.$resTU["ncolegiatura"].'</div></div> 
        </div>
        <div class="divHoja">
            <div id="divFEM1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div class="divClear"></div>
            <div id="divFEM37" class="divTituloHojaA">EXAMEN DE ALTURA</div>
            <div id="divFEM38" class="divTextoSuelto">NOMBRES:</div>
            <div id="divFEM39" class="divTextoSuelto">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"]. '</div>
            <div id="divFEM40" class="divTextoSuelto">EDAD:</div>
            <div id="divFEM41" class="divTextoSuelto">'.$rowem["edad"].' AÑOS</div>
            <div id="divFEM42" class="divTextoSuelto">EMPRESA:</div>
            <div id="divFEM43" class="divTextoSuelto">'.$rowem["rucempresaespecializada"].'</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFEM40" class="divTextoSueltoA"><strong>APTO →</strong></div>
            <div id="divFEM41" class="divTextoSueltoA">'.(($rstta["apto"]==1)?'SI':'NO').'</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFEM40" class="divTextoSuelto"></div>
            <div id="divFEM41" class="divTextoSuelto"></div>
            <div id="divFEM42" class="divTextoSuelto">FECHA:</div>
            <div id="divFEM43" class="divTextoSuelto">'.date("d/m/Y").'</div>
        </div>
        ';
        return $html;
    }
    function imprimelaboratorio($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rowem = $rst["empleado"][0];
        $rst["laboratorio"] = $modelEvaluacion->laboratoriolistar($datos);
        $rowla = $rst["laboratorio"][0];
        
        $datos["p_idUsuario"] = $rowla["idUA"];
        $sesTU = new Models_Usuario();
        $resTUs = $sesTU->listar($datos);
        $resTU = $resTUs[0];
        $html1 = '
        <div class="divHoja">
            <div id="divFEM1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFEM2" class="divTituloHojaA"><br />LABORATORIO - N° 354</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFLA1" class="dbIzq dbSup dbInf"><div class="divTitulo">APELLIDOS Y NOMBRES<br /></div></div>
            <div id="divFLA2" class="dbIzq dbSup dbInf dbDer"><div class="divTextoCE">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</div></div>
            <div class="divClear"></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">GRUPO SANGUINEO: '.$rowla["gruposanguineo"].'</div></div>
            <div id="divFLA3" class="dbIzq dbSup dbInf dbDer"><div class="divTextoCE">FACTOR Rh: '.(($rowla["factorsanguineo"]==0)?'Rh(-)':'Rh(+)').'</div></div>
            <div class="divClear"></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">RPR: '.(($rowla["rpr"]==0)?'Negativo':'Positivo').'</div></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">SUB UNIDAD BETA: '.$rowla["subunidadbeta"].'</div></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">FUR: '.$rowla["fur"].'</div></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">HEMOGRAMA: </div></div>
            <div id="divFLA4" class="dbIzq dbSup dbDer">
                <div class="divClear"></div>
                <div id="divFLA5">Hemoglobina</div>
                <div id="divFLA6">'.$rowla["hemoglobina"].'</div>
                <div id="divFLA7">g/dl</div>
                <div id="divFLA8">(H:13.5-17.5/M:12-16)</div>
                <div id="divFLA5">Hematocrito</div>
                <div id="divFLA6">'.$rowla["hematocrito"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(H:41-53/M:12-16)</div>
                <div id="divFLA5">Hematies</div>
                <div id="divFLA6">'.$rowla["hematies"].'</div>
                <div id="divFLA7">mm3</div>
                <div id="divFLA8">(4.5-5.5 x 10*6)</div>
                <div id="divFLA5">Leucocitos</div>
                <div id="divFLA6">'.$rowla["leucocitos"].'</div>
                <div id="divFLA7">mm3</div>
                <div id="divFLA8">(4,500 - 11,000)</div>
                <div id="divFLA5">Juveniles</div>
                <div id="divFLA6">'.$rowla["juveniles"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8"></div>
                <div id="divFLA5">Abastonados</div>
                <div id="divFLA6">'.$rowla["abastonados"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(0-5)</div>
                <div id="divFLA5">Segmentados</div>
                <div id="divFLA6">'.$rowla["segmentados"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(35-66)</div>
                <div id="divFLA5">Linfocitos</div>
                <div id="divFLA6">'.$rowla["linfocitos"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(24-44)</div>
                <div id="divFLA5">Monocitos</div>
                <div id="divFLA6">'.$rowla["monocitos"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(3-6)</div>
                <div id="divFLA5">Eosinofilos</div>
                <div id="divFLA6">'.$rowla["eosinofilos"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(0-3)</div>
                <div id="divFLA5">Basofilos</div>
                <div id="divFLA6">'.$rowla["basofilos"].'</div>
                <div id="divFLA7">%</div>
                <div id="divFLA8">(0-1)</div>
                <div id="divFLA5">Plaquetas</div>
                <div id="divFLA6">'.$rowla["plaquetas"].'</div>
                <div id="divFLA7">mm3</div>
                <div id="divFLA8">(150,000 - 450,000)</div>
                <div id="divFLA9">Comentario: '.$rowla["comentario"].'</div>
            </div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">EXAMEN COMPLETO ORINA: </div></div>
            <div id="divFLA13" class="dbIzq dbSup dbDer dbInf">
                <div class="divClear"></div>
                <div id="divFLA10">COLOR:</div>
                <div id="divFLA11">'.$rowla["color"].'</div>
                <div id="divFLA12">SEDIMENTO:</div>
                <div id="divFLA10">ASPECTO:</div>
                <div id="divFLA11">'.$rowla["aspecto"].'</div>
                <div id="divFLA10">Leucocitos:</div>
                <div id="divFLA11">'.$rowla["sedleucocitos"].'</div>
                <div id="divFLA10">REACCIÓN:</div>
                <div id="divFLA11">'.$rowla["reaccion"].'</div>
                <div id="divFLA10">Celulas Epiteliales:</div>
                <div id="divFLA11">'.$rowla["celulasepiteliales"].'</div>
                <div id="divFLA10">DENSIDAD:</div>
                <div id="divFLA11">'.$rowla["densidad"].'</div>
                <div id="divFLA10">Hematies:</div>
                <div id="divFLA11">'.$rowla["sedhematies"].'</div>
                <div id="divFLA12">TIRA REACTIVA:</div>
                <div id="divFLA10">Cristales:</div>
                <div id="divFLA11">'.$rowla["cristales"].'</div>
                <div id="divFLA10">Glucosa:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["glucosa"]).'</div>
                <div id="divFLA10">Cilindros:</div>
                <div id="divFLA11">'.$rowla["cilindros"].'</div>
                <div id="divFLA10">Proteínas:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["proteinas"]).'</div>
                <div id="divFLA10">Otros:</div>
                <div id="divFLA11">'.$rowla["otros"].'</div>
                <div id="divFLA10">Cetonas:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["cetonas"]).'</div>
                <div id="divFLA10"></div>
                <div id="divFLA11"></div>
                <div id="divFLA10">Bilirrubina:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["bilirrubina"]).'</div>
                <div id="divFLA10"></div>
                <div id="divFLA11"></div>
                <div id="divFLA10">Urobilinogeno:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["urobilinogeno"]).'</div>
                <div id="divFLA10"></div>
                <div id="divFLA11"></div>
                <div id="divFLA10">Nitritos:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["nitritos"]).'</div>
                <div id="divFLA10"></div>
                <div id="divFLA11"></div>
                <div id="divFLA10">Sangre:</div>
                <div id="divFLA11">'.$this->devuelveLaboratorioplus($rowla["sangre"]).'</div>
                <div id="divFLA10"></div>
                <div id="divFLA11"></div>
            </div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFLA1"><div class="divTitulo">FECHA: '.date("d/m/Y").'</div></div>
            <div id="divFLA2" class="dbInf ">Personal médico que lo atendió: '.$resTU["apellidos"].' '.$resTU["nombres"].'</div>
        </div>
        ';
        $html2 = '
        <div class="divHoja">
            <div id="divFEM1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFEM2" class="divTituloHojaA"><br />LABORATORIO</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFLA1" class="dbIzq dbSup dbInf"><div class="divTitulo">APELLIDOS Y NOMBRES<br /></div></div>
            <div id="divFLA2" class="dbIzq dbSup dbInf dbDer"><div class="divTextoCE">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'<br /></div></div>
            <div class="divClear"></div>
            <div id="divFLA3" class="dbIzq dbSup dbDer"><div class="divTextoCE">BIOQUÍMICA: </div></div>
            <div id="divFLA4" class="dbIzq dbSup dbDer dbInf">
                <div class="divClear"></div>
                <div id="divFLA5">Colesterol total</div>
                <div id="divFLA6">'.$rowla["colesteroltotal"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(<200)</div>
                <div id="divFLA5">HDL</div>
                <div id="divFLA6">'.$rowla["hdl"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(30-65)</div>
                <div id="divFLA5">Trigliceridos</div>
                <div id="divFLA6">'.$rowla["trigliceridos"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(25-160)</div>
                <div id="divFLA5">Proteinas totales</div>
                <div id="divFLA6">'.$rowla["proteinastotales"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(6.0-8.3)</div>
                <div id="divFLA5">Albumina</div>
                <div id="divFLA6">'.$rowla["albumina"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(3.2-5.0)</div>
                <div id="divFLA5">Globulinas</div>
                <div id="divFLA6">'.$rowla["globulinas"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(2.5-3.6)</div>
                <div id="divFLA5">Acido Urico</div>
                <div id="divFLA6">'.$rowla["acidourico"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(H:3.6-7.7/M:2.5-6.8)</div>
                <div id="divFLA5">Glucosa</div>
                <div id="divFLA6">'.$rowla["bioglucosa"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(65-110)</div>
                <div id="divFLA5">Urea</div>
                <div id="divFLA6">'.$rowla["urea"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(13-45)</div>
                <div id="divFLA5">Creatinina</div>
                <div id="divFLA6">'.$rowla["creatinina"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(H:0.7-1.4/M:42-98)</div>
                <div id="divFLA5">Amilasa</div>
                <div id="divFLA6">'.$rowla["amilasa"].'</div>
                <div id="divFLA7">U/L</div>
                <div id="divFLA8"></div>
                <div id="divFLA5">TGO</div>
                <div id="divFLA6">'.$rowla["tgo"].'</div>
                <div id="divFLA7">U/L</div>
                <div id="divFLA8">(H:31/M:37)</div>
                <div id="divFLA5">TGP</div>
                <div id="divFLA6">'.$rowla["tgp"].'</div>
                <div id="divFLA7">U/L</div>
                <div id="divFLA8">(H:42/M:32)</div>
                <div id="divFLA5">GGT</div>
                <div id="divFLA6">'.$rowla["ggt"].'</div>
                <div id="divFLA7">U/L</div>
                <div id="divFLA8">(H:11-50/M:7-32)</div>
                <div id="divFLA5">Fosfatasa Alcalina</div>
                <div id="divFLA6">'.$rowla["fosfatasaalcalina"].'</div>
                <div id="divFLA7">U/L</div>
                <div id="divFLA8">(H:53-128/M:42-98)</div>
                <div id="divFLA5">Bilirrubina total</div>
                <div id="divFLA6">'.$rowla["bilirrubinatotal"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(0.1-1.2)</div>
                <div id="divFLA5">Bilirrubina directa</div>
                <div id="divFLA6">'.$rowla["bilirrubinadirecta"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8">(0.0-0.3)</div>
                <div id="divFLA5">Bilirrubina indirecta</div>
                <div id="divFLA6">'.$rowla["bilirrubinaindirecta"].'</div>
                <div id="divFLA7">mg/dl</div>
                <div id="divFLA8"></div>
                <div id="divFLA9">Comentario: '.$rowla["biocomentario"].'</div>
            </div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFLA1"><div class="divTitulo">FECHA: '.date("d/m/Y").'</div></div>
            <div id="divFLA2" class="dbInf ">Personal médico que lo atendió: '.$resTU["apellidos"].' '.$resTU["nombres"].'</div>
        </div>
        ';
        return $html1 . $html2;
    }
    function imprimeodontograma($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["nombremotivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rst["empleado"][0]["lugarnacimiento"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rowem = $rst["empleado"][0];
        $rst["odontograma"] = $modelEvaluacion->odontogramalistar($datos);
        $rowod = $rst["odontograma"][0];
        $rowod["grafico"] = str_replace("/images/odontograma/diente", "", $rowod["grafico"]); 
        $rowod["grafico"] = str_replace('class=clearFloatLimite', ' style="border: none !important;width: 520px !Important;height:2px !Important; clear: both;font-size: 0;line-height: 0px;"', $rowod["grafico"]); 
        $rowod["grafico"] = str_replace('; border-right: solid 2px #555;', "", $rowod["grafico"]);
        $rowod["grafico"] = str_replace('; BORDER-RIGHT: #555 2px solid', "", $rowod["grafico"]);
        $rowod["grafico"] = str_replace("/images/odontograma/", "../public/images/odontograma/", $rowod["grafico"]);
        $rowod["grafico"] = str_replace('<div style="float: left; height: 30px; width: 7px; margin-right: 3px; border-right: solid 2px #555;"></div>', '<div style="float: left; height: 30px; margin-left:-9px; width: 19px; border-right:solid 1px #000 "></div>', $rowod["grafico"]);
        $rowod["grafico"] = str_replace('<div style="float: left; height: 20px; width: 7px; margin-right: 3px; border-right: solid 2px #555;"></div>', '<div style="float: left; height: 20px; margin-left:-9px; width: 19px; border-right:solid 1px #000 "></div>', $rowod["grafico"]);
//        $rowod["grafico"] = str_replace("/images/odontograma/", "../public/images/odontograma/", $rowod["grafico"]);
//        $rowod["grafico"] = str_replace('class="odontodientea', 'style="background-image: url(../public/images/odontograma/diente33.png);" class="odontodientea', $rowod["grafico"]);
//        $rowod["grafico"] = str_replace('class="odontodienteb', 'style="background-image: url(../public/images/odontograma/diente34.png);" class="odontodienteb', $rowod["grafico"]);
//        $rowod["grafico"] = str_replace('<div style="float: left; height: 30px; width: 7px; margin-right: 3px; border-right: solid 2px #555;"></div>', '<div style="float: left; height: 30px; margin-left:-9px; width: 19px; border-right:solid 1px #000 "></div>', $rowod["grafico"]);
//        $rowod["grafico"] = str_replace('<div style="float: left; height: 20px; width: 7px; margin-right: 3px; border-right: solid 2px #555;"></div>', '<div style="float: left; height: 20px; margin-left:-9px; width: 19px; border-right:solid 1px #000 "></div>', $rowod["grafico"]);
        $html = '
        <div class="divHoja">
            <div id="divFEM1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFEM2" class="divTituloHojaA"><br />ODONTOGRAMA</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divTitulo" style="margin: 0px 150px !important; background-image:url(../public/images/odontogramacompleto.png); background-repeat: no-repeat">'.$rowod["grafico"].'</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">APELLIDOS Y NOMBRES</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">DOCUMENTO DE IDENTIDAD</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowem["dni"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">FECHA</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.date("d/m/Y").'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">EDAD</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowem["edad"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">PROCEDENCIA</div></div>
            <div id="divFLA2" class="dbIzq dbSup  dbDer"><div class="divTextoCE">'.$rowem["lugarnacimiento"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">TIPO DE TRABAJADOR</div></div>
            <div id="divFLA2" class="dbIzq dbSup  dbDer"><div class="divTextoCE">'.$this->devuelveTipoTrabajador($rowem["tipotrabajador"]).'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">MOTIVO DE EXAMEN</div></div>
            <div id="divFLA2" class="dbIzq dbSup  dbDer"><div class="divTextoCE">'.$rowem["nombremotivo"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup dbInf"><div class="divTitulo">EMPRESA</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbInf dbDer"><div class="divTextoCE">'.$rowem["rucempresaespecializada"].'</div></div>
            <div class="divClear"></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">FIEZAS DENTAL COMPLETA</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowod["piezacompleta"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">PIEZAS EXTRAIDAS</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowod["piezaextraida"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup "><div class="divTitulo">PIEZAS EN MAL ESTADO</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbDer"><div class="divTextoCE">'.$rowod["piezamal"].'</div></div>
            <div id="divFLA1" class="dbIzq dbSup dbInf"><div class="divTitulo">OBSERVACIONES</div></div>
            <div id="divFLA2" class="dbIzq dbSup dbInf dbDer"><div class="divTextoCE">'.$rowod["observaciones"].'</div></div>
        </div>
        ';
        return $html;
    }
    function imprimememorandum($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["nombremotivo"] = $this->devuelveNombreMotivo($rst["empleado"][0]["motivo"]);
        $rst["empleado"][0]["codestadocivil"] = $rst["empleado"][0]["estadocivil"];
        $rst["empleado"][0]["estadocivil"] = $this->devuelveNombreEstadoCivil($rst["empleado"][0]["estadocivil"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rst["empleado"][0]["lugarnacimiento"] = 
                $this->devuelveNombreDept($rst["empleado"][0]["deptnac"])." - ".
                $this->devuelveNombreProv($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"])." - ".
                $this->devuelveNombreDist($rst["empleado"][0]["deptnac"], $rst["empleado"][0]["provnac"], $rst["empleado"][0]["distnac"]);
        $rowem = $rst["empleado"][0];
        $rst["equipopesado"] = $modelEvaluacion->resultadoeelistar($datos);
        $rstep = $rst["equipopesado"][0];
        $html = '
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFME2" class="divTituloHojaA"><br />MEMORANDUM</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFME3" class="dbInf">
                <div class="divAttr divTextoCF">DE:</div><div class="divVal divTextoCF">Jefe de Centro Médico</div>
                <div class="divAttr divTextoCF">PARA:</div><div class="divVal divTextoCF" style="font-size:11px">'.$rowem["rucempresaespecializada"].'<br />'.$rstep["para"].'</div>
            </div>
            <div id="divFME4" class="dbIzq dbInf">
                <div class="divAttr divTextoCF">FECHA:</div><div class="divVal divTextoCF">'.date("d/m/Y").'</div>
                <div class="divAttr divTextoCF">ASUNTO:</div><div class="divVal divTextoCF">Lo que indica.</div>
            </div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFME5" class="divTextoCF">
                La pte. tiene la finalidad de informarle que, luego de haber realizado los exámenes de Audiometría, Espirometría y Agudeza visual abajo mencionado,
                los resultados fueron los siguientes:<br /><br />
                <strong><u>EXAMEN ESPECIAL</u>:</strong> '.$rstep["tipoexamen"].'<br /><br />
                <i>N° DNI '.$rowem["dni"].' '.$rowem["appaterno"].' '.$rowem["apmaterno"].' '.$rowem["nombres"]. ' DE '.$rowem["edad"].' AÑOS DE EDAD</i><br /><br />
                <strong><u>RESULTADOS DE EXAMEN</u>:</strong><br /><br />
                <strong>ESPIROMETRÍA:</strong><br />
                '.$rstep["resespirometria"].'<br /><br />
                <strong>AUDIOMETRÍA:</strong><br />
                '.$rstep["resaudiometria"].'<br /><br />
                <strong>OPTOMETRÍA:</strong><br />
                '.$rstep["resoptometria"].'<br />
                <strong style="font-size:18px;"><u>APTO</u>: '.(($rstep["apto"]==1)?"SI":"NO").'</strong>
            </div>
        </div>
        ';
        return $html;
    }
    function imprimepasevisitante($datos = array()){
        $modelEvaluacion = new Models_Evaluacion();
        $rst["empleado"] = $modelEvaluacion->empleadolistar($datos);
        $rst["empleado"][0]["nregistro"] = trim($rst["empleado"][0]["nregistro"]);
        $rst["empleado"][0]["edad"] = $this->devuelveEdad($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fechanacimiento"] = $this->convierteFechaaLatino($rst["empleado"][0]["fechanacimiento"]);
        $rst["empleado"][0]["fecha"] = $this->convierteFechaaLatino($rst["empleado"][0]["fecha"]);
        $rst["empleado"][0]["sexo"] = $this->devuelveNombreSexo($rst["empleado"][0]["sexo"]);
        $rst["empleado"][0]["rucempresaespecializada"] = $this->devuelveNombreEmpresa($rst["empleado"][0]["rucempresaespecializada"]);
        $rowem = $rst["empleado"][0];
        $rst["ficha7d"] = $modelEvaluacion->fichaanexo7dlistar($datos);
        $rstfd = $rst["ficha7d"][0];
        $rst["pasevisitante"] = $modelEvaluacion->pasevisitantelistar($datos);
        $rstpv = $rst["pasevisitante"][0];
        
        $datos["p_idUsuario"] = $rstpv["idUA"];
        $sesTU = new Models_Usuario();
        $resTUs = $sesTU->listar($datos);
        $resTU = $resTUs[0];
        $html = '
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFME2" class="divTituloHojaA"><br />PASE PARA VISITANTES</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFPV3">
                <div class="divAttrA divTextoCF">APELLIDOS Y NOMBRES:</div><div class="divValA divTextoCF">'.$rowem["apmaterno"].' '.$rowem["nombres"]. '</div>
                <div class="divAttrA divTextoCF">EMPRESAS:</div><div class="divValA divTextoCF">'.$rowem["rucempresaespecializada"].'</div>
            </div>
            <div id="divFPV4">
                <div class="divAttrA divTextoCF">FECHA:</div><div class="divValA divTextoCF">'.date("d/m/Y").'</div>
                <div class="divAttrA divTextoCF">TELEFONO EMERGENCIA:</div><div class="divValA divTextoCF">'.$rowem["telefono"].'</div>
            </div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFPV5" class="divTextoCF">
                <strong>NOTIFICACIÓN</strong><br /><br />
                <ol>
                    <li><strong></strong>Este pase es válido a partir de su fecha de emisión y hasta un año de vigencia y deberá ser necesariamente suscrito por la persona
                    a quien se otorga. El pase es personal e intransferible.</span></li>
                    <li>Cada vez que ingrese a la Unidad el visitante está obligado a detenerse en la Garita Principal de control (Alfa N° 2), identificarse 
                    indicando su nombre, la empresa que representa y el motivo de su visita. Recabar el presente pase si no lo tuviera o mostrar el mismo 
                    cada vez que ingresar a la Unidad.</li>
                    <li>Recabado el presente pase, dirigirse inmediatamente al Hospital de la Unidad, para un chequeo de su Salud y responder la encuesta
                    respectiva que se menciona al reverso.</li>
                    <li>Debe reportarse directamente a la persona y/o área con la que coordinó la visita. Solo podrá transitar dentro de la Unidad con la persona
                    a quién visita o con la que designe aquel.</li>
                    <li>Al ingresar o retirarse de la Unidad, todas las personas y/o vehículos sin excepción de ninguna clase, estarán sujetos a revisión por
                    parte de Seguridad Interna de Interandina S.A.</li>
                </ol>
                <strong>NOTIFICACIÓN</strong><br /><br />
                El visitante declara expresamente que acepta este PASE  asumiendo todos los riesogos y se somete a las siguientes condiciones:<br />
                <ol>
                    <li><strong></strong>He leído y estoy de acuerdo con todas las Reglas de Seguridad (escritas y verbales), los requerimientos de Equipo de Protección, instrucciones
                    y los procedimientos de emergencia explicados en la Cartilla de Visitas adjuntas a este Pase.</li>
                    <li>Entiendo que la violación de los términos de éste y/o de cartilla, constituirá causa suficiente para que me retire de la Unidad.</li>
                    <li>Renuncio a todos los reclamos por daños personales o de equipos en el interior de la Mina, Planta concentradora, Talleres y demás instalaciones 
                    de la Unidad de Producción de Uchucchacua de la Compañia de Minas Buenaventura S.A.A. y que me puedan ocurrir por motivos propios.</li>
                </ol>
                <br /><br /><br />
                <strong></strong>FIRMA:.........................................
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                LE/DNI:................
            </div>
        </div>
        <div class="divHoja">
            <div id="divFME1"><img src="../public/images/logopeque.jpg" width="140" /></div>
            <div id="divFME2" class="divTituloHojaA"><br />HOSPITAL</div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div class="divClear"></div>
            <div id="divFPV6" class="dbIzq dbSup dbDer">
                <div class="divValA divTextoCF">1. FUNCIONES VITALES:</div>
                <div class="divValA divTextoCF">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                FC: '.$rstfd["frecuenciacardiaca"].' x min
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                FR: '.$rstfd["frecuenciarespiratoria"].' x min
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                PA: '.$rstfd["presionarteriala"].'/'.$rstfd["presionarterialb"].' mmHg
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                T°: '.$rstfd["temperatura"].' °C
                </div>
            </div>
            <div id="divFPV7" class="dbIzq dbSup dbDer dbInf">
                <div class="divValA">2. ENCUESTA</div><br /><br />
                <div id="divFPV8" class="divValA">
                <ol type="A">
                    <li><strong></strong>
                    ¿Conoce Ud. su grupo sanguíneo? ¿Cuál?<br />'.$rstpv["pregunta1"].'<br /><br />
                    </li>
                    <li>
                    ¿Tiene algún problema en la visión?<br />'.$rstpv["pregunta2"].'<br /><br />
                    </li>
                    <li>
                    ¿Algún problema auditivo?<br />'.$rstpv["pregunta3"].'<br /><br />
                    </li>
                    <li>
                    ¿Sufre Ud. alguna enfermedad? ¿Cuál?<br />'.$rstpv["pregunta4"].'<br /><br />
                    </li>
                    <li>
                    ¿Ha viajado anteriormente a altura? ¿Cuántos m.s.n.m.?<br />'.$rstpv["pregunta5"].'<br /><br />
                    </li>
                    <li>
                    ¿Sufrió anteriormente de mal de altura?<br />'.$rstpv["pregunta6"].'<br /><br />
                    </li>
                    <li>
                    Si ha trabajado en Mina ¿Cuánto tiempo estuvo en interior de mina?<br />'.$rstpv["pregunta7"].'<br /><br />
                    </li>
                    <li>
                    ¿Sufrió accidente de trabajo? ¿Tiene secuelas?<br />'.$rstpv["pregunta8"].'<br /><br />
                    </li>
                    <li>
                    ¿Tiene seguro social o particular? ¿Cuál es? Anote además clínica de referencia.<br />'.$rstpv["pregunta9"].'<br /><br />
                    </li>
                    <li>
                    ¿Ha recibido capacitación en primeros auxilios? ¿Cuándo?<br />'.$rstpv["pregunta10"].'<br /><br />
                    </li>
                </ol>
                <br /><strong>
                Personal Médico que lo atendió: '.$resTU["apellidos"].' '.$resTU["nombres"].'<br /><br /></strong>
                <br /><br /><br /><br /><br /><br />
                FIRMA:.........................................
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                FECHA: '.date("d/m/Y").'
                </div>
            </div>
        </div>
        ';
        return $html;
    }    
}
?>
