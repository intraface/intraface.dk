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

if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql://' .  DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);
}

$GLOBALS['kernel'] = $kernel;
$GLOBALS['intranet'] = $kernel->intranet;
$GLOBALS['db'] = $db;

$application = new Intraface_modules_shop_Controller_Root();

$application->registry->registerConstructor('doctrine', create_function(
  '$className, $args, $registry',
  'Doctrine_Manager::getInstance()->setAttribute("use_dql_callbacks", true); ' .
  'return Doctrine_Manager::connection(DB_DSN);'
//   'Doctrine_Manager::getInstance()->addRecordListener(new Intraface_Doctrine_Intranet(1)); ' .
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
