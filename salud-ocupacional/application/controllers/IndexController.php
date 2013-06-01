<?php
class IndexController extends Controlergeneric{ 
    public function init(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $permisos = new Zend_Session_Namespace('Permisos');
            $this->view->perGen = $permisos->Generales;
            $this->view->perEsp = $permisos->Especificos;
        }
    } 
    public function indexAction(){
        $this->_helper->layout->setLayout("login");
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) $this->_helper->redirector('panel','index');
    }
    public function panelAction(){
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) $this->_helper->redirector('index','index');
    }
}
