<?php
require_once '../../include_first.php';
ini_set('include_path', PATH_INCLUDE_PATH);

require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');
/*
class Year_Gateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function getYearFromId($id = 0)
    {
        return new Year($this->kernel, $id);
    }
}
*/
require_once 'phemto.php';
function create_container() {
  $injector = new Phemto();
  // put application wiring here
  $template_dir = realpath(dirname(__FILE__) . '/../../../Intraface/modules/accounting/Controller/templates');
  $injector->whenCreating('TemplateFactory')->forVariable('template_dir')->willUse(new Value($template_dir));
  //$injector->whenCreating('Intraface_Kernel')->forVariable('session_id')->willUse(new Value(session_id()));
  //$injector->whenCreating('Year_Gateway')->forVariable('kernel')->willUse(new Intraface_Kernel);

  return $injector;
}

class TemplateFactory {
  protected $template_dir;
  function __construct($template_dir) {
    $this->template_dir = $template_dir;
  }
  function create() {
    $smarty = new k_Template($this->template_dir);
    return $smarty;
  }
}

$GLOBALS['kernel'] = $kernel;
$GLOBALS['intranet'] = $kernel->intranet;

// FIXME Setting the charset to utf8. Works beautifull on getting from the db, but cannot save, as info is iso.
//$db = MDB2::singleton();
//$res = $db->setCharset('utf8');
//if (PEAR::isError($res)) {
//    trigger_error($res->getUserInfo(), E_USER_WARNING);
//}
$GLOBALS['db'] = $db;

class WireFactory {
    function __construct()
    {
    }

    function create()
    {
    	$registry = new k_Registry();
        $registry->registerConstructor('doctrine', create_function(
            '$className, $args, $registry',
            'return Doctrine_Manager::connection(DB_DSN);'
        ));
        $registry->registerConstructor('category_gateway', create_function(
          '$className, $args, $registry',
          'return new Intraface_modules_shop_Shop_Gateway;'
        ));

        $registry->registerConstructor('kernel', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["kernel"];'
        ));

        $registry->registerConstructor('intranet', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["intranet"];'
        ));

        $registry->registerConstructor('db', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["db"];'
        ));

        $registry->registerConstructor('page', create_function(
          '$className, $args, $registry',
          'return new Intraface_Page($registry->get("kernel"));'
        ));

        return $registry;
    }
}

k()
  // Use container for wiring of components
  ->setComponentCreator(new k_InjectorAdapter(create_container()))
  // Enable file logging
  //->setLog(dirname(__FILE__) . '/../log/debug.log')
  // Uncomment the next line to enable in-browser debugging
  //->setDebug()
  // Dispatch request
  ->run('Intraface_modules_accounting_Controller_Index')
  ->out();
