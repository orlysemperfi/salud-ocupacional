<?php
require("PHPExcel/IOFactory.php");
class ReporteController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->verificaPermiso(44);
        
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
    public function cabeceratabla($tipo){
        return '
                <tr><td class="bordesa" colspan="13" style="font-size: 1px; height:10px"></td></tr>
                <tr><td class="bordesa" colspan="13" style="font-size: 13px;">MOTIVO DE EXAMEN: '.$tipo.'</td></tr>
                <tr><td class="bordesa" colspan="13" style="font-size: 1px; height:3px"></td></tr>
                <tr>
                    <td class="tblcab">N°</td>
                    <td class="tblcab">APELLIDOS Y NOMBRES</td>
                    <td class="tblcab">DNI</td>
                    <td class="tblcab">FECHA DE NACIMIENTO</td>
                    <td class="tblcab">SEXO</td>
                    <td class="tblcab">PUESTO</td>
                    <td class="tblcab">HORA INICIO</td>
                    <td class="tblcab">HORA FIN</td>
                    <td class="tblcab">DURACIÓN</td>
                    <td class="tblcab">FECHA DE CITA</td>
                    <td class="tblcab">PERIODO</td>
                    <td class="tblcab">ESTADO DE CITA</td>
                    <td class="tblcab">APTO</td>
                </tr>';
    }
    public function sinregistrotabla(){
        return '<tr><td class="bordesa" colspan="10" style="font-size: 10px; height:10px">No hay registros que mostrar.</td></tr>';
    }
    
    public function generapdfAction(){
        $this->verificaPermiso(44);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        ini_set('zlib_output_compression','On');
        
        if( isset($_GET["selCompaniaUsuarioFiltro"]) ){ 
            $post = $this->getRequest()->getParams();
            
            $modelcompania = new Models_Compania();
            $modellocalidad = new Models_Localidad();
            $modelreporte = new Models_Reporte();

            $datos["p_idCompania"] = trim($post["selCompaniaUsuarioFiltro"]);
            $restcompania = $modelcompania->listar($datos);
            $datos["p_idLocalidad"] = trim($post["selLocalidadUsuarioFiltro"]);
            $restlocalidad = $modellocalidad->listar($datos);
            $datos["p_motivo"] = trim($post["selMotivo"]);
            $datos["p_estado"] = trim($post["selEstadoCita"]);
            $datos["p_apto"] = trim($post["selEstadoExamen"]);
            $datos["p_rucempresaespecializada"] = trim($post["hdnEmpresaEspecializada"]);
            $datos["p_idObservaciones"] = trim(((isset($post["hdnEnfermedad"])?$post["hdnEnfermedad"]:"")));
            $datos["p_enfermedad"] = trim(((isset($post["txtEnfermedad"])?$post["txtEnfermedad"]:"")));
            $datos["p_fechainicio"] = (trim($post["txtFechaInicio"]) != "")?$this->convierteFechaSinEspacio(trim($post["txtFechaInicio"])):"";
            $datos["p_fechafin"] = (trim($post["txtFechaFin"]) != "")?$this->convierteFechaSinEspacio(trim($post["txtFechaFin"])):"";
            
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Cyberline SRL")->setLastModifiedBy("Cyberline SRL")
                    ->setTitle("Reporte")->setSubject("Salud Ocupacional")
                    ->setDescription("Este reporte fue generado automáticamente por el sistema de Salud Ocupacional.");
            $objPHPExcel->getDefaultStyle()->getFont()->setName('Tahoma')->setSize(8);
            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('../public/images/logopeque.jpg');
            $objDrawing->setCoordinates('A1');
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
            $objPHPExcel->setActiveSheetIndex(0)
                    ->mergeCells('A1:F1')
                    ->mergeCells('G1:P1')
                    ->setCellValue('G1', trim($restcompania[0]["nombreCompleto"]).' - '.trim($restlocalidad[0]["nombre"]).' - '.date("d/m/Y"));
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(70);
            
            $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(6);
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(6);
            $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(13);
            $objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(6);
            $objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(200);
            $p = 2;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$p, 'N°')
                ->setCellValue('B'.$p, 'EMPRESA')
                ->setCellValue('C'.$p, 'RUC')
                ->setCellValue('D'.$p, 'MOTIVO')
                ->setCellValue('E'.$p, 'APELLIDOS Y NOMBRES')
                ->setCellValue('F'.$p, 'DNI')
                ->setCellValue('G'.$p, 'FECHA DE NAC.')
                ->setCellValue('H'.$p, 'SEXO')
                ->setCellValue('I'.$p, 'PUESTO')
                ->setCellValue('J'.$p, 'HORA INICIO')
                ->setCellValue('K'.$p, 'HORA FIN')
                ->setCellValue('L'.$p, 'DURACIÓN')
                ->setCellValue('M'.$p, 'FECHA DE CITA')
                ->setCellValue('N'.$p, 'PERIODO')
                ->setCellValue('O'.$p, 'ESTADO DE CITA')
                ->setCellValue('P'.$p, 'APTO')
                ->setCellValue('Q'.$p, 'ENFERMEDAD OCUPACIONAL');
            $objPHPExcel->getActiveSheet()->getRowDimension($p)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$p.':Q'.$p)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$p.':Q'.$p)->applyFromArray(array('font' => array('bold' => true),'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'F0F0F0'))));
            $p++;
            $cuerpo = ' 
            <style>
            body{ font-family: arial; font-size: 11px; }
            table{ width: 100%; border-collapse: collapse; font-size: 9px }
            td{ height: 25px }
            .bordesa{ border: solid 1px #000; border-collapse: collapse; padding-left: 5px; }
            .bordesb{ border: solid 1px #000; border-collapse: collapse; text-align:center; }
            .linea{ border-bottom: solid 1px #000; width: 100%; display: block; margin: 10px 0px; }
            .clear{ width: 100%; display: block; margin: 5px 0px; }
            .tblcab{ text-align:center; border: solid 1px #000; border-collapse: collapse; background: #F0F0F0; }
            </style>
            <table>
                <tr>
                    <td colspan="6" '.(isset($post["opc"])?'style="height: 100px !important"':'').'><img src="http://'.$_SERVER["HTTP_HOST"].'/images/logopeque.jpg" width="110px" /></td>
                    <td colspan="7" style="vertical-align: bottom; text-align: right">'.$restcompania[0]["nombreCompleto"].' - '.$restlocalidad[0]["nombre"].' - '.date("d/m/Y").'</td>
                </tr>
            </table>
            <div class="linea"></div>
            ';
            $m = 0;
            $restreporte = $modelreporte->listarempresas($datos);
            foreach($restreporte as $rows){
                $m++;
                $datos["p_rucempresaespecializada"] = $rows["rucempresaespecializada"];
                $nombreempresa = $this->devuelveNombreEmpresa($datos["p_rucempresaespecializada"]);
                $cuerpo.= '
                <table>
                    <tr>
                        <td class="bordesa" style="font-size: 16px;" colspan="6">EMPRESA: '.$nombreempresa.' - '.$datos["p_rucempresaespecializada"].' </td>
                        <td class="bordesa" style="font-size: 12px;" colspan="7">'.(($datos["p_idObservaciones"] != "")?"ENFERMEDAD OCUPACIONAL: ".$datos["p_enfermedad"]:"").'</td>
                    </tr>
                </table>
                <table>
                    ';
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 1){
                    $c = 1;
                    $restreportea = $modelreporte->listara($datos);
                    foreach($restreportea as $row){
                        if($row["motivo"] == 1){
                            if($c == 1 && count($restreportea) > 0){ $cuerpo.=  $this->cabeceratabla("ANUAL");}
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")))
                                    ->setCellValue('Q'.$p, $row["evaobservaciones"]);
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 4){
                    $c = 1;
                    $restreportea = $modelreporte->listara($datos);
                    foreach($restreportea as $row){
                        if($row["motivo"] == 4){
                            if($c == 1 && count($restreportea) > 0){ $cuerpo.=  $this->cabeceratabla("RETIRO"); }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")))
                                    ->setCellValue('Q'.$p, $row["evaobservaciones"]);
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 5){
                    $c = 1;
                    $restreportea = $modelreporte->listara($datos);
                    foreach($restreportea as $row){
                        if($row["motivo"] == 5){
                            if($c == 1 && count($restreportea) > 0){ $cuerpo.=  $this->cabeceratabla("PRE - OCUPACIONAL"); }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")))
                                    ->setCellValue('Q'.$p, $row["evaobservaciones"]);
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["evaaptotrabajar"] == 1)?"SI":(($row["evaaptotrabajar"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 2){
                    $c = 1;
                    $restreporteb = $modelreporte->listarb($datos);
                    foreach($restreporteb as $row){
                        if($row["motivo"] == 2){
                            if($c == 1 && count($restreporteb) > 0){ $cuerpo.=  $this->cabeceratabla("EQUIPOS PESADOS");}
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")));
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 3){
                    $c = 1;
                    $restreportec = $modelreporte->listarc($datos);
                    foreach($restreportec as $row){
                        if($row["motivo"] == 3){
                            if($c == 1 && count($restreportec) > 0){ $cuerpo.=  $this->cabeceratabla("TRABAJOS EN ALTURA"); }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")));
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 6){
                    $c = 1;
                    $restreported = $modelreporte->listard($datos);
                    foreach($restreported as $row){
                        if($row["motivo"] == 6){
                            if($c == 1 && count($restreported) > 0){ $cuerpo.=  $this->cabeceratabla("RETORNO DE VACACIONES"); }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")));
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                if($datos["p_motivo"] == 0 || $datos["p_motivo"] == 7){
                    $c = 1;
                    $restreported = $modelreporte->listard($datos);
                    foreach($restreported as $row){
                        if($row["motivo"] == 7){
                            if($c == 1 && count($restreported) > 0){ $cuerpo.=  $this->cabeceratabla("VISITANTES"); }
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('A'.$p, ($p-2))
                                    ->setCellValue('B'.$p, $nombreempresa)
                                    ->setCellValue('C'.$p, $row["rucempresaespecializada"])
                                    ->setCellValue('D'.$p, $this->devuelveNombreMotivo($row["motivo"]))
                                    ->setCellValue('E'.$p, $row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"])
                                    ->setCellValue('F'.$p, $row["dni"])
                                    ->setCellValue('G'.$p, $this->convierteFechaaLatino($row["fechanacimiento"]))
                                    ->setCellValue('H'.$p, $row["sexo"])
                                    ->setCellValue('I'.$p, strtoupper($row["puesto"]))
                                    ->setCellValue('J'.$p, (($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]))
                                    ->setCellValue('K'.$p, (($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]))
                                    ->setCellValue('L'.$p, (($row["duracion"]=='')?'':$row["duracion"].' min.'))
                                    ->setCellValue('M'.$p, $this->convierteFechaaLatino($row["fecha"]))
                                    ->setCellValue('N'.$p, $this->convierteFechaaPeriodo($row["fecha"]))
                                    ->setCellValue('O'.$p, $this->devuelveNombreEstadoCita($row["estado"]))
                                    ->setCellValue('P'.$p, (($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")));
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$p)->getNumberFormat()->setFormatCode('0000');
                            $objPHPExcel->getActiveSheet()->getStyle('F'.$p)->getNumberFormat()->setFormatCode('00000000');
                            $cuerpo.= '
                                <tr>
                                    <td width="40" class="bordesb">'.str_pad($c, 4 , "0", STR_PAD_LEFT).'</td>
                                    <td width="260" class="bordesb" style="text-align:left">'.$row["appaterno"].' '.$row["apmaterno"].' '.$row["nombres"].'</td>
                                    <td width="70" class="bordesb">'.$row["dni"].'</td>
                                    <td width="90" class="bordesb">'.$this->convierteFechaaLatino($row["fechanacimiento"]).'</td>
                                    <td width="40" class="bordesb">'.$row["sexo"].'</td>
                                    <td width="220" class="bordesb">'.strtoupper($row["puesto"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horainiciox"]=='00/00/ 00:00')?'':$row["horainiciox"]).'</td>
                                    <td width="100" class="bordesb">'.(($row["horafinx"]=='00/00/ 00:00')?'':$row["horafinx"]).'</td>
                                    <td width="80" class="bordesb">'.(($row["duracion"]=='')?'':$row["duracion"].' min.').'</td>                                        
                                    <td width="100" class="bordesb">'.$this->convierteFechaaLatino($row["fecha"]).'</td>
                                    <td width="80" class="bordesb">'.$this->convierteFechaaPeriodo($row["fecha"]).'</td>
                                    <td width="130" class="bordesb">'.$this->devuelveNombreEstadoCita($row["estado"]).'</td>
                                    <td width="40" class="bordesb">'.(($row["apto"] == 1)?"SI":(($row["apto"] == 2)?"NO":"-")).'</td>
                                </tr>';
                            $c++;
                            $p++;
                        }
                    }
                }
                $cuerpo.= '</table>
            <div class="clear"></div>
            <div class="clear"></div>
            <div class="clear"></div>
            <div class="clear"></div>
            <div class="clear"></div>';
            }
            if($m == 0){
                $cuerpo .= '<br /><br /><span style="font-size: 20px;">No hay registros para los datos ingresados.</span>';
            }
            if(isset($post["opc"])){
                $objPHPExcel->getActiveSheet()->setTitle('Reporte');
                $objPHPExcel->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="reporte'.date("YmdHis").'.xlsx"');
                header('Cache-Control: max-age=0');
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save('php://output'); 
            }else{
                echo  $cuerpo;
            }
        }else{
            echo ("<img src='/images/logobuenaventura.jpg' style='margin-left: 490px; margin-top: 130px' />");
        }
    }
}
