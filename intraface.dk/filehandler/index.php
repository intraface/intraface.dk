<?php
require_once dirname(__FILE__) . '/../include_first.php';
ini_set('include_path', '/home/lsolesen/workspace/ilib/Ilib_Filemanager_Controller/src/' . PATH_SEPARATOR .
'/home/lsolesen/workspace/ilib/Ilib_Filemanager_Controller/src/www/' . PATH_SEPARATOR .
'/home/lsolesen/workspace/ilib/Ilib_Keyword_Controller/src/' . PATH_SEPARATOR .
'/home/lsolesen/workspace/ilib/Ilib_Keyword/src/' . PATH_SEPARATOR .
'/home/lsolesen/workspace/ilib/Ilib_Filemanager/src/' . PATH_SEPARATOR  . PATH_SEPARATOR . PATH_INCLUDE_PATH);
require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME);
}

class This_Filehandler_Root extends k_Dispatcher
{
    public $map = array('file'        => 'Intraface_Filehandler_Controller_Viewer',
                        'filemanager' => 'Intraface_Filehandler_Controller_Index',
                        'keyword'     => 'Intraface_Keyword_Controller_Index');
    public $debug = true;

    function __construct()
    {
        parent::__construct();
        $this->document->template = 'document.tpl.php';
        $this->document->title = 'Filemanager';
    }

    function execute()
    {
        return $this->forward('filemanager');
    }
}

$application = new This_Filehandler_Root();

$GLOBALS['kernel'] = $kernel;
$GLOBALS['intranet'] = $kernel->intranet;
$GLOBALS['db'] = $db;

$application->registry->registerConstructor('database:db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql();'
));

// I don't know if this i right?
$GLOBALS['intraface.kernel'] = $kernel;

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

$application->dispatch();
