<?php
require_once 'config.local.php';

ini_set('include_path', $GLOBALS['include_path']);

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

$application = new Intraface_Blog_Root();

$application->registry->registerConstructor('cms:client', create_function(
  '$className, $args, $registry',
  'return new IntrafacePublic_CMS_XMLRPC_Client(array("private_key" => $GLOBALS["intraface_private_key"], "session_id" => uniqid()), $GLOBALS["intraface_site_id"], false);'
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
