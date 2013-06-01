<?php
 //error_reporting(E_ALL|E_STRICT);
 error_reporting(E_ALL);
ini_set('display_errors', 'on');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
 
// Define application environment
defined('APPLICATION_ENV')    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
 
//Define el nombre del archivo de configuraci�n INI compartido
defined('APPLICATION_CONFIG_INI')
    || define('APPLICATION_CONFIG_INI', realpath(dirname(__FILE__) . '/../application/configs/application.ini'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../application'),
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
 
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('My_');
$loader->setFallbackAutoloader(true);
$loader->suppressNotFoundWarnings(false);

$application->bootstrap()
            ->run();