<?php
/**
 * @todo make a check on the shop whether there is an 
 *       email for the intranet, otherwise the shop 
 *       will fail sending email
 * 
 * @todo make it possible to shut down the shop from the webinterface
 */

require_once dirname(__FILE__) . '/../../include_first.php';
ini_set('include_path', PATH_INCLUDE_PATH);

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

class Intraface_Shop_Root extends k_Dispatcher
{
    public $map = array(
        'shop' => 'Intraface_modules_shop_Controller_Index'
    );

    function getHeader()
    {
        $page = $this->registry->get('page');
        ob_start();
        $page->start();
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

    function execute()
    {
        return $this->forward('shop');
    }

    function handleRequest()
    {
        $content = parent::handleRequest();
        return $this->getHeader() . $content . $this->getFooter();
    }
}

if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql://' .  DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);
}

$GLOBALS['kernel'] = $kernel;
$GLOBALS['intranet'] = $kernel->intranet;
$GLOBALS['db'] = $db;


$application = new Intraface_Shop_Root();

$application->registry->registerConstructor('doctrine', create_function(
  '$className, $args, $registry',
  'return Doctrine_Manager::connection(DB_DSN);'
));

$application->registry->registerConstructor('kernel', create_function(
  '$className, $args, $registry',
  'return $GLOBALS["kernel"];'
));

$application->registry->registerConstructor('intranet', create_function(
  '$className, $args, $registry',
  'return $GLOBALS["intranet"];'
));

$application->registry->registerConstructor('db', create_function(
  '$className, $args, $registry',
  'return $GLOBALS["db"];'
));

$application->registry->registerConstructor('page', create_function(
  '$className, $args, $registry',
  'return new Intraface_Page($registry->get("kernel"));'
));
$application->dispatch();
