<?php
require '../config.local.php';

set_include_path(PATH_INCLUDE_PATH);
define('DB_DSN', 'mysql://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME);

require 'Ilib/ClassLoader.php';
require 'k.php';

$application = new Intraface_Tools_Controller_Root();

$application->registry->registerConstructor('errorlist', create_function(
  '$className, $args, $registry',
  'return new Intraface_Tools_ErrorList(ERROR_LOG);'
));

$application->registry->registerConstructor('database', create_function(
  '$className, $args, $registry',
  'return MDB2::singleton(DB_DSN);'
));

$application->registry->registerConstructor('db_sql', create_function(
  '$className, $args, $registry',
  'return new DB_Sql;'
));

$application->registry->registerConstructor('mdb2', create_function(
  '$className, $args, $registry',
  ' $db = MDB2::singleton(DB_DSN, array("persistent" => true));
    if (PEAR::isError($db)) {
        trigger_error($db->getMessage(), E_USER_ERROR);
    }
    $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
    $db->setOption("portability", MDB2_PORTABILITY_NONE);
    return $db;'
));

$application->registry->registerConstructor('user', create_function(
  '$className, $args, $registry',
  'return new Ilib_SimpleLogin_User($registry->SESSION);'
));

$application->registry->registerConstructor('translation_admin', create_function(
  '$className, $args, $registry',
  '
    $driver = "mdb2";
    $options = array(
        "hostspec" => DB_HOST,
        "database" => DB_NAME,
        "phptype" => "mysql",
        "username" => DB_USER,
        "password" => DB_PASS
    );
    $params = array(
        "langs_avail_table" => "core_translation_langs",
        "strings_default_table" => "core_translation_i18n"
    );
    
    $translation = Translation2_Admin::factory($driver, $options, $params);
    if (PEAR::isError($translation)) {
        exit($translation->getMessage());
    }
    return $translation;
 '
));

$application->registry->registerConstructor('translation', create_function(
  '$className, $args, $registry',
  '
    $driver = "mdb2";
    $options = array(
        "hostspec" => DB_HOST,
        "database" => DB_NAME,
        "phptype" => "mysql",
        "username" => DB_USER,
        "password" => DB_PASS
    );
    $params = array(
        "langs_avail_table" => "core_translation_langs",
        "strings_default_table" => "core_translation_i18n"
    );
    
    $translation = Translation2::factory($driver, $options, $params);
    if (PEAR::isError($translation)) {
        exit($translation->getMessage());
    }
    $translation->setLang("dk");
    return $translation;
 '
));


$application->dispatch();