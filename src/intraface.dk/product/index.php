<?php
require_once dirname(__FILE__) . '/../include_first.php';

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME);
}

class This_Product_Root extends k_Dispatcher
{
    public $map = array('product' => 'Intraface_modules_product_Controller_Index');
    public $debug = true;

    function __construct()
    {
        parent::__construct();
        $this->document->template = 'document.tpl.php';
        $this->document->title = 'Filemanager';
    }

    function execute()
    {
        return $this->forward('product');
    }
}

$application = new This_Product_Root();

$GLOBALS['kernel'] = $kernel;
$GLOBALS['intranet'] = $kernel->intranet;
$GLOBALS['db'] = $db;
$GLOBALS['intraface.kernel'] = $kernel;

$application->registry->registerConstructor('database:db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql();'
));

$application->registry->registerConstructor('intraface:kernel', create_function(
  '$className, $args, $registry',
  'return $GLOBALS["intraface.kernel"];'
));

$application->registry->registerConstructor('database:mdb2', create_function(
  '$className, $args, $registry',
  '$options= array("debug" => 0);
   $db = MDB2::factory(DB_DSN, $options);
   if (PEAR::isError($db)) {
        die($db->getMessage());
   }
   $db->setOption("portability", MDB2_PORTABILITY_NONE);
   $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
   $db->exec("SET time_zone=\"-01:00\"");
   return $db;
'
));

$application->registry->registerConstructor('intraface:filehandler:gateway', create_function(
  '$className, $args, $registry',
  'return new Ilib_Filehandler_Gateway($registry->get("intraface:kernel"));'
));

$application->dispatch();
