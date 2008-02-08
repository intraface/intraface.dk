<?php
require_once 'config.local.php';

ini_set('include_path', '.' . PATH_SEPARATOR . $GLOBALS['PATH_INCLUDE']);

require_once 'k.php';
require_once 'root.php';

$application = new Demo_CMS_Root();

$application->registry->registerConstructor('cms:client', create_function(
  '$className, $args, $registry',
  'return new IntrafacePublic_CMS_XMLRPC_Client(array("private_key" => "Sm2ndXm9kQbasKh8M1MbnLeD52htb1fL4YY7L2XWzQXHhWFUMaV", "session_id" => uniqid()), 4, false);'
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
