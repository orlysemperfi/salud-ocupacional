<?php 
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap{
    protected function _initBD(){
        $config = new Zend_Config_Ini(APPLICATION_CONFIG_INI, APPLICATION_ENV);
        $dbMSSQL    = Zend_Db::factory($config->dbMSSQL);
	Zend_Registry::set('dbMSSQL', $dbMSSQL);
	$dbMSSQLs    = Zend_Db::factory($config->dbMSSQLs);
	Zend_Registry::set('dbMSSQLs', $dbMSSQLs);        
    }
    protected function _initView(){
        Zend_Session::start();
        date_default_timezone_set("America/Lima");
        date_default_timezone_set('UTC');
        $docTypeHelper = new Zend_View_Helper_Doctype();
        $docTypeHelper->doctype('HTML5');
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
        
        $view->headTitle('Salud Ocupacional')->headTitle('Buenaventura')->setSeparator(' - ');
        $view->headLink()->prependStylesheet('/css/jqueryuitheme/jquery-ui-1.9.0.custom.css');        
        $view->headLink()->prependStylesheet('/css/ui.totop.css');
        $view->headLink()->prependStylesheet('/css/ui.jqgrid.css');
        $view->headLink()->prependStylesheet('/css/validationEngine.jquery.css');
        $view->headLink()->prependStylesheet('/css/tipTip.css');
        $view->headLink()->prependStylesheet('/css/jquery.tree.css');
        $view->headLink()->prependStylesheet('/css/jquery.treecollapse.css');
        $view->headLink()->prependStylesheet('/css/jquery.treecontextmenu.css');
        $view->headLink()->prependStylesheet('/css/jquery.treednd.css');
        $view->headLink()->prependStylesheet('/css/style.css');
        $view->headLink()->prependStylesheet('/css/fullcalendar.css');
        
        $view->headScript()->appendFile('/js/jquery-1.8.2.js');
        $view->headScript()->appendFile('/js/jquery-ui-1.9.0.custom.min.js');        
        $view->headScript()->appendFile('/js/general.js');
        $view->headScript()->appendFile('/js/jquery.contextmenu.r2.js');
        $view->headScript()->appendFile('/js/jquery.validationEngine-es.js');
        $view->headScript()->appendFile('/js/jquery.validationEngine.js');
        $view->headScript()->appendFile('/js/jquery.maskedinput-1.3.min.js');
        $view->headScript()->appendFile('/js/jquery.ui.totop.js');
        $view->headScript()->appendFile('/js/i18n/grid.locale-es.js');
        $view->headScript()->appendFile('/js/jquery.jqGrid.min.js');
        $view->headScript()->appendFile('/js/jquery.tree.js');
        $view->headScript()->appendFile('/js/jquery.treeajax.js');
        $view->headScript()->appendFile('/js/jquery.treecheckbox.js');
        $view->headScript()->appendFile('/js/jquery.treecollapse.js');
        $view->headScript()->appendFile('/js/jquery.treecontextmenu.js');
        $view->headScript()->appendFile('/js/jquery.treednd.js');
        $view->headScript()->appendFile('/js/jquery.treeselect.js');
        $view->headScript()->appendFile('/js/ajaxfileupload.js');
        $view->headScript()->appendFile('/js/fullcalendar.js');
        $view->headScript()->appendFile('/js/jquery.printElement.min.js');
        $view->headScript()->appendFile('/js/jquery.placeholder.js');
    } 
}
