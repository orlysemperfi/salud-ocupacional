<?php
class LocalidadController extends Controlergeneric{ 
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
    }
    public function listarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $datos["p_idCompania"] = $post["selCompaniaUsuario"];
            
            $sesTU = $_SESSION["Zend_Auth"]["storage"];
            if($sesTU->flg_nivel == 2){
                $datos["p_idUsuario"] = $_SESSION["Permisos"]["Generales"]["idUsuario"];
                $modelUsuario = new Models_Usuario();
                $rstUsuarioRol = $modelUsuario->usuariorol($datos);
            }
            
            $modelLocalidad = new Models_Localidad();
            $rstListarLocalidad = $modelLocalidad->listar($datos);
            if($sesTU->flg_nivel == 1){
                if(isset($rstListarLocalidad[0]["nombre"])){
                    foreach ($rstListarLocalidad as $r) {
                        $response[$r["idLocalidad"]] = $r["nombre"];
                    }
                }else{
                    $response[""] = "Sin registros";
                }
            }
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
                if(isset($val[0]["nombre"])){
                    foreach ($val as $r) {
                        $response[$r["idLocalidad"]] = $r["nombre"];
                    }
                }else{
                    $response[""] = "Sin registros";
                }
            }
            
            $this->_helper->json( Zend_Json::encode($response) );
        }
    }
}
