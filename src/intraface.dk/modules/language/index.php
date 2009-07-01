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

$application = new Intraface_modules_language_Controller_Root();

$application->registry->registerConstructor('doctrine', create_function(
  '$className, $args, $registry',
  'return Doctrine_Manager::connection(DB_DSN);'
));

$application->registry->registerConstructor('category_gateway', create_function(
  '$className, $args, $registry',
  'return new Intraface_modules_shop_Shop_Gateway;'
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