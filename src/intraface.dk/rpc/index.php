<?php
require_once dirname(__FILE__) . '/../common.php';
ini_set('include_path', PATH_INCLUDE_PATH);

require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

k()
  // Use container for wiring of components
  // ->setComponentCreator(new k_InjectorAdapter(create_container()))
  // Enable file logging
  //->setLog(dirname(__FILE__) . '/../log/debug.log')
  // Uncomment the next line to enable in-browser debugging
  //->setDebug()
  // Dispatch request
  ->run('Intraface_XMLRPC_Controller')
  ->out();
