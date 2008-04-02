<?php
require_once 'config.local.php';

set_include_path(INTRAFACEPUBLIC_SHOP_INCLUDE_PATH);

require_once 'k.php';
require_once 'root.php';

class intraface_ClassLoader extends k_ClassLoader
{
  /**
    * Default autoloader for Konstrukt naming scheme.
    */
  static function autoload($classname) {
    $filename = str_replace('_', '/', $classname).'.php';
    if (self::SearchIncludePath($filename)) {
      require_once($filename);
    }
    else {
        throw new Exception('Unable to include file '.$filename);
    }
  }
}

spl_autoload_register(Array('intraface_ClassLoader', 'autoload'));


$application = new Demo_Shop_Root();

$application->registry->registerConstructor('admin', create_function(
  '$className, $args, $registry',
  'return XML_RPC2_Client::create(INTRAFACE_XMLPRC_SERVER_PATH . "admin/server.php", array("prefix" => "intraface."));'
));


$application->registry->registerConstructor('cache', create_function(
  '$className, $args, $registry',
  '
   $options = array(
       "cacheDir" => "",
       "lifeTime" => 3600
   );
   return new Cache_Lite($options);'
));


$application->dispatch();