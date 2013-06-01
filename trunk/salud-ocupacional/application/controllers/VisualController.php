<?php
class VisualController extends Controlergeneric{ 
    public function init(){
        
    } 
    function getRealIP() {
        $ip = null;  
        if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if( isset( $_SERVER ['HTTP_VIA'] ))  $ip = $_SERVER['HTTP_VIA'];
        else if( isset( $_SERVER ['REMOTE_ADDR'] ))  $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
    public function indexAction(){
        $this->_helper->layout->setLayout("visual");
        $modelCronograma = new Models_Cronograma();
        $modelCronograma->modificarestadocronograma();
        $modelCronograma->modificarestadocita();
    }
    public function refrescarpersonalAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modelUsuario = new Models_Usuario();
        $modelVisual = new Models_Visual();
        
        $ip = $this->getRealIP();
        $ip = explode(".", $ip);
        unset($ip[3]);
        $ip = implode(".", $ip).".";
        $datos["p_ip"] = $ip;
        $rstListarIp = $modelVisual->listarip($datos);
        
        $datos["p_isonlinex"] = 1;
        $datos["p_flg_nivel"] = 2;
        $datos["p_time"] = (time()-60);
        $modelVisual->limpiar($datos);
        $c = 0;
        $response = array();
        foreach($rstListarIp as $row){
            $datos["p_idCompaniax"] = $row["idCompania"];
            $datos["p_idLocalidadx"] = $row["idLocalidad"];
            $rstListarUsuario = $modelUsuario->listar($datos);
            foreach($rstListarUsuario as $rowa){
                $rowa["nombres"] = explode(" ", $rowa["nombres"]);
                $rowa["nombres"] = $rowa["nombres"][0];
                $response[$c]["nombre"] = $rowa["apellidos"].", ".$rowa["nombres"];
                $response[$c]["titulo"] = $rowa["titulo"];
                $c++;
            }
        }
        $this->_helper->json( Zend_Json::encode($response) );
    }
    public function refrescarpacientesAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modelVisual = new Models_Visual();
        
        $ip = $this->getRealIP();
        $ip = explode(".", $ip);
        unset($ip[3]);
        $ip = implode(".", $ip).".";
        $datos["p_ip"] = $ip;
        $rstListarIp = $modelVisual->listarip($datos);
        
        $c = 0;
        $response = array();
        $datos["p_isonlinex"] = 1;
        
        foreach($rstListarIp as $row){
            $datos["p_idCompaniax"] = $row["idCompania"];
            $datos["p_idLocalidadx"] = $row["idLocalidad"];
            $rstListar = $modelVisual->listarcita($datos);
            foreach($rstListar as $rowa){
                $rowa["nombres"] = explode(" ", $rowa["nombres"]);
                $rowa["nombres"] = $rowa["nombres"][0];
                $response[$c]["dispon"] = ($rowa["estado"] == 2)?'<img src="/images/nodisponib.gif" />':'<img src="/images/disponible.gif" />';
                $response[$c]["nombre"] = $rowa["appaterno"]." ".$rowa["apmaterno"].", ".$rowa["nombres"];
                $response[$c]["motivo"] = $this->devuelveNombreMotivo($rowa["motivo"]);
                $examenes = array();
                if($rowa["idHistoria"] != "") $examenes[] ="HISTORIA OCUPACIONAL";
                if($rowa["idFichasieted"] != "") $examenes[] ="FICHA ANEXO N° 7 - D";
                if($rowa["idFichasietec"] != "") $examenes[] ="FICHA ANEXO N° 7 - C";
                if($rowa["idRayosx"] != "") $examenes[] ="RAYOS X";
                if($rowa["idLaboratorio"] != "") $examenes[] ="LABORATORIO";
                if($rowa["idAudiometria"] != "") $examenes[] ="AUDIOMETRÍA";
                if($rowa["idEspirometria"] != "") $examenes[] ="ESPIROMETRÍA";
                if($rowa["idOptometria"] != "") $examenes[] ="OPTOMETRÍA";
                if($rowa["idOdontograma"] != "") $examenes[] ="ODONTOGRAMA";
                if($rowa["idEquipopesado"] != "") $examenes[] ="EXAMEN ESPECIAL DE EQUIPOS PESADOS";
                if($rowa["idPasevisitante"] != "") $examenes[] ="PASE DE VISITANTE";
                if($rowa["idTrabajoaltura"] != "") $examenes[] ="EXAMEN MÉDICO DE SUFICIENCIA FÍSICA Y PSICOLÓGICA";
                $response[$c]["examen"] = implode(", ", $examenes);
                $c++;
            }
        }
        $this->_helper->json( Zend_Json::encode($response) );
    }
}
