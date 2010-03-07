<?php
require_once 'config.local.php';

ini_set('include_path', $GLOBALS['include_path']);

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

$application = new Intraface_Blog_Root();

$application->registry->registerConstructor('cms:client', create_function(
  '$className, $args, $registry',
  'return new IntrafacePublic_CMS_Client_XMLRPC(array("private_key" => INTRAFACE_PRIVATE_KEY, "session_id" => uniqid()), INTRAFACE_SITE_ID, false);'
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
