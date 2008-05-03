<?php
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

$application = new Intraface_Shop_Root();

$application->registry->registerConstructor('doctrine', create_function(
  '$className, $args, $registry',
  'return Doctrine_Manager::connection(DB_DSN);'
));

$application->registry->registerConstructor('page', create_function(
  '$className, $args, $registry',
  'return new Page($GLOBALS["kernel"]);'
));
$application->dispatch();
