<?php
require_once 'config.local.php';

set_include_path(INTRAFACEPUBLIC_SHOP_INCLUDE_PATH);

/** 
 * NOTICE: session_start is needed to be executed before Classloader is initialized.
 * Otherwise it gives strange error trying to start MDB2_Driver_mysql 
 */
session_start();

require_once 'k.php';
require_once 'Ilib/ClassLoader.php';

$application = new Demo_Root();

$application->registry->registerConstructor('admin', create_function(
  '$className, $args, $registry',  
  'return new IntrafacePublic_Admin_Client_XMLRPC("abcdefghijklmnopqrstuvwxyz123456789#", false, INTRAFACE_XMLPRC_SERVER_PATH . "admin/server.php");'
));

$application->registry->registerConstructor('cache', create_function(
  '$className, $args, $registry',
  '
   $options = array(
       "cacheDir" => PATH_CACHE,
       "lifeTime" => 3600
   );
   return new Cache_Lite($options);'
));

$application->registry->registerConstructor('translation', create_function(
  '$className, $args, $registry',
  '$options = array(
    "da" => true,
    "en" => true
   );

   $language = HTTP::negotiateLanguage($options, "en");

   $translation = IntrafacePublic_Frontend_Translation::factory($language);
   $translation->setPageID("kundelogin");
   return $translation;
  '
));
$application->dispatch();