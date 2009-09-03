<?php
require_once dirname(__FILE__) . '/../../include_first.php';

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
        $this->document->title = 'Filemanager';
    }

    function getHeader()
    {
        $page = $this->registry->get('page');
        ob_start();
        $page->start($this->document->title);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function getFooter()
    {
        $page = $this->registry->get('page');
        ob_start();
        $page->end();
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function handleRequest()
    {
        $content = parent::handleRequest();
        $data = array('content' => $content);
        return $this->getHeader() . $this->render(dirname(__FILE__) . '/tpl/content.tpl.php', $data) . $this->getFooter();
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
$GLOBALS['intraface.kernel'] = $kernel;

$application->registry->registerConstructor('database:db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql();'
));

$application->registry->registerConstructor('intraface:kernel', create_function(
  '$className, $args, $registry',
  'return $GLOBALS["intraface.kernel"];'
));

$application->registry->registerConstructor('kernel', create_function(
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

$application->registry->registerConstructor('page', create_function(
  '$className, $args, $registry',
  'return new Intraface_Page($registry->get("kernel"));'
));

$application->registry->registerConstructor('intraface:filehandler:gateway', create_function(
  '$className, $args, $registry',
  'return new Ilib_Filehandler_Gateway($registry->get("intraface:kernel"));'
));

$application->dispatch();
