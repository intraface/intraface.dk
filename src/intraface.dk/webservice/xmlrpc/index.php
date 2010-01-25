<?php
require_once dirname(__FILE__) . '/../../common.php';
ini_set('include_path', PATH_INCLUDE_PATH);
require_once 'konstrukt/konstrukt.inc.php';

class MyIdentityLoader extends k_BasicHttpIdentityLoader {

    function selectUser($session_id, $private_key)
    {
        $auth_adapter = new Intraface_Auth_PrivateKeyLogin(MDB2::singleton(DB_DSN), $session_id, $private_key);
        $weblogin = $auth_adapter->auth();

        if ($weblogin) {

            $this->kernel = new Intraface_Kernel($session_id);
            $this->kernel->weblogin = $weblogin;
            $this->kernel->intranet = new Intraface_Intranet($weblogin->getActiveIntranetId());
            $this->kernel->setting = new Intraface_Setting($this->kernel->intranet->get('id'));

            // makes intranet_id accessable in Doctrine
            Intraface_Doctrine_Intranet::singleton($this->kernel->intranet->getId());

            return new k_AuthenticatedUser($private_key);
        }
    }
}

require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');

XML_RPC2_Backend::setBackend('php');
$HTTP_RAW_POST_DATA = file_get_contents('php://input');

k()
  ->setIdentityLoader(new MyIdentityLoader())
  // Use container for wiring of components
  // ->setComponentCreator(new k_InjectorAdapter(create_container()))
  // Enable file logging
  //->setLog(dirname(__FILE__) . '/../log/debug.log')
  // Uncomment the next line to enable in-browser debugging
  //->setDebug()
  // Dispatch request
  ->run('Intraface_XMLRPC_Controller')
  ->out();
