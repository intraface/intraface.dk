<?php
require_once 'config.local.php';

set_include_path(INTRAFACEPUBLIC_SHOP_INCLUDE_PATH);

require_once 'k.php';
require_once 'root.php';

$application = new Demo_Root();

$application->registry->registerConstructor('shop', create_function(
  '$className, $args, $registry',
  'return new IntrafacePublic_Shop_XMLRPC_Client(array("private_key" => INTRAFACE_PRIVATE_KEY, "session_id" => md5($registry->SESSION->getSessionId())), false, "http://localhost/intraface/intraface.dk/xmlrpc/shop/server3.php");'
));

$application->registry->registerConstructor('admin', create_function(
  '$className, $args, $registry',
  'return XML_RPC2_Client::create("http://localhost/intraface/intraface.dk/xmlrpc/admin/server.php", array("prefix" => "intraface."));'
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