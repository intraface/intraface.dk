<?php
error_reporting(E_ALL & ~(E_DEPRECATED | E_STRICT));

define('DB_HOST', $GLOBALS['db_host']);
define('DB_PASS', $GLOBALS['db_password']);
define('DB_USER', $GLOBALS['db_username']);
define('DB_NAME', $GLOBALS['db_name']);
define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/' . DB_NAME);
define('PATH_ROOT', dirname(__FILE__) . '/../../src/');
define('PATH_INCLUDE_CONFIG', PATH_ROOT . 'Intraface/config/');
define('PATH_INCLUDE_MODULE', PATH_ROOT . 'Intraface/modules/');
define('PATH_INCLUDE_SHARED', PATH_ROOT . 'Intraface/shared/');
define('CONNECTION_INTERNET', 'ONLINE');

if (!file_exists($GLOBALS['path_upload'])) {
    mkdir($GLOBALS['path_upload']);
}

define('PATH_UPLOAD', $GLOBALS['path_upload']);
define('PATH_UPLOAD_TEMPORARY', 'tempdir/');
define('FILE_VIEWER', '');
define('PATH_WWW', '');
define('IMAGE_LIBRARY', 'GD');
define('XMLRPC_SERVER_URL', 'http://privatekeyshouldbereplaced:something@' . $GLOBALS['xmlrpc_server_url']);

if (!file_exists($GLOBALS['test_path_temp'])) {
    mkdir($GLOBALS['test_path_temp']);
}

// Directory to move files to temporary in tests
define('TEST_PATH_TEMP', $GLOBALS['test_path_temp']);

set_include_path(dirname(__FILE__) . '/' . PATH_SEPARATOR . dirname(__FILE__) . '/../../vendor/intraface/error/src/' . PATH_SEPARATOR . dirname(__FILE__) . '/../../vendor/troelskn/konstrukt/lib/' . PATH_SEPARATOR . PATH_ROOT. PATH_SEPARATOR . get_include_path());

$db = MDB2::singleton(DB_DSN);
$db->setOption('debug', 0);
$db->setOption('portability', MDB2_PORTABILITY_NONE);

if ($db->getOption('debug')) {
    $db->setOption('log_line_break', "\n\n\n\n\t");

    require_once 'MDB2/Debug/ExplainQueries.php';

    $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
    $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

    register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
    register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
}

Doctrine_Manager::getInstance()->setAttribute("use_dql_callbacks", true);
Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_TYPES | Doctrine::VALIDATE_CONSTRAINTS);
$conn = Doctrine_Manager::connection(DB_DSN);
$conn->setCharset('utf8');
