<?php
require 'config.local.php';

set_include_path(INTRAFACE_PATH_INCLUDE);

require 'k.php';
require 'MDB2.php';
require 'Ilib/ClassLoader.php';

define('INTRAFACE_TOOLS_DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);
define('DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);

$application = new Intraface_Tools_Controller_Root();

$application->registry->registerConstructor('errorlist', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_ErrorList(ERROR_LOG, ERROR_LOG_UNIQUE);'
));

$application->registry->registerConstructor('database', create_function(
  '$className, $args, $registry',
  'return MDB2::singleton(INTRAFACE_TOOLS_DB_DSN);'
));

$application->registry->registerConstructor('db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql;'
));

$application->registry->registerConstructor('user', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_User($registry->SESSION);'
));

$application->dispatch();