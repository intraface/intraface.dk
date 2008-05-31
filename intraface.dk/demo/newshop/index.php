<?php
require_once 'config.local.php';

set_include_path(INTRAFACEPUBLIC_SHOP_INCLUDE_PATH);

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';
require_once 'root.php';

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