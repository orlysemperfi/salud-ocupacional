<?php
class UbigeoController extends Controlergeneric{ 
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
    public function listarprovinciaAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $d = $post["idDepartamento"];
            $model = new Models_Provincia();
            $rst = $model->listar($d, "");
            if(isset($rst[0]["descr_prov"])){
                foreach ($rst as $r) {
                    $response[$r["id_prov"]] = $r["descr_prov"];
                }
            }else{
                $response[""] = "Sin registros";
            }
            $this->_helper->json( Zend_Json::encode($response) );
        }
    }
    public function listardistritoAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if( $this->getRequest()->isPost() ){
            $post = $this->getRequest()->getPost();
            $d = $post["idDepartamento"];
            $p = $post["idProvincia"];
            $model = new Models_Distrito();
            $rst = $model->listar($d, $p, "");
            if(isset($rst[0]["descr_dist"])){
                foreach ($rst as $r) {
                    $response[$r["id_dist"]] = $r["descr_dist"];
                }
            }else{
                $response[""] = "Sin registros";
            }
            $this->_helper->json( Zend_Json::encode($response) );
        }
    }
}
