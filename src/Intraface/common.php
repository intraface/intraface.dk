<?php
/**
 * Includes common files and makes common settings
 *
 * Should never be edited. Edit config.local.php in intraface.dk if you want to override default values.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
// required files
require_once 'Ilib/ClassLoader.php';
require_once 'ErrorHandler.php';
require_once 'ErrorHandler/Observer/BlueScreen.php';
require_once 'Log.php';
require_once 'Doctrine/lib/Doctrine.php';
spl_autoload_register(array('Doctrine', 'autoload'));
require_once 'k/urlbuilder.php';
require_once 'Intraface/functions.php';
require_once 'Intraface/shared/systemmessage/SystemDisturbance.php';
require_once 'lib/bucket.inc.php';

// paths
if (!defined('PATH_INCLUDE_IHTML')) define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
if (!defined('PATH_INCLUDE_MODULE')) define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
if (!defined('PATH_INCLUDE_SHARED')) define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);
if (!defined('PATH_INCLUDE_CONFIG')) define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);
if (!defined('PATH_UPLOAD')) define('PATH_UPLOAD', PATH_ROOT . 'upload/'); // Directory for upload of files.
if (!defined('PATH_UPLOAD_TEMPORARY')) define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.
if (!defined('PATH_CAPTCHA')) define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // remember trailing slash - used for the demo formular
if (!defined('PATH_CACHE')) define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache
if (!defined('PATH_INCLUDE_BACKUP')) define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup/');

// paths on www
if (defined('NET_SCHEME') && defined('NET_HOST') && defined('NET_DIRECTORY')) {
    define('PATH_WWW', NET_SCHEME.NET_HOST.NET_DIRECTORY);
    define('PATH_WWW_MODULE', PATH_WWW.'modules/');
    define('PATH_WWW_SHARED', PATH_WWW.'shared/');
    // filehandler
    define('FILE_VIEWER', PATH_WWW . 'main/file/');
}

// errorhandling
if (!defined('ERROR_HANDLE_LEVEL')) {
    define('ERROR_HANDLE_LEVEL', E_ALL);
}
//set_error_handler('intrafaceBackendErrorhandler', ERROR_HANDLE_LEVEL);
//set_exception_handler('intrafaceBackendExceptionhandler');

// wiring
$bucket = new bucket_Container(new Intraface_Factory());

// filehandler
if (!defined('IMAGE_LIBRARY')) define('IMAGE_LIBRARY', 'GD');

// database
if (!defined('DB_DSN')) define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');
if (!defined('MDB2_DEBUG')) {
    define('MDB2_DEBUG', false);
}
$db = $bucket->get('mdb2');


/*
$db = MDB2::singleton(DB_DSN, array('persistent' => true));
if (PEAR::isError($db)) {
    trigger_error($db->getMessage(), E_USER_ERROR);
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->setOption('debug', MDB2_DEBUG);
$db->setOption('portability', MDB2_PORTABILITY_NONE);
$res = $db->setCharset('latin1');
if (PEAR::isError($res)) {
    trigger_error($res->getUserInfo(), E_USER_ERROR);
}

if ($db->getOption('debug')) {
    $db->setOption('log_line_break', "\n\n\n\n\t");

    $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
    $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

    register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
    register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
}
*/

// timezone and local
if (!defined('COUNTRY_LOCAL')) define('COUNTRY_LOCAL', 'da_DK');
if (!defined('TIMEZONE')) define('TIMEZONE', 'Europe/Copenhagen');
setlocale(LC_CTYPE, COUNTRY_LOCAL);
putenv("TZ=".TIMEZONE);
if (defined('TIMEZONE')) {
    $db->exec('SET time_zone=\''.TIMEZONE.'\'');
}
        if (defined('INTRAFACE_K2') AND INTRAFACE_K2 === true) {
            $db->query('SET NAMES utf8');
        } else {

            $db->query('SET NAMES latin1');
        }

// Initializes Doctrine
Doctrine_Manager::getInstance()->setAttribute("use_dql_callbacks", true);
Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_TYPES | Doctrine::VALIDATE_CONSTRAINTS);
Doctrine_Manager::connection(DB_DSN);
