<?php
/**
 * Includes common files and makes common settings
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

//configuration - this should never be edited - only edit intraface.dk/config.local.php

// paths
if(!defined('PATH_INCLUDE_IHTML')) define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
if(!defined('PATH_INCLUDE_MODULE')) define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
if(!defined('PATH_INCLUDE_SHARED')) define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);
if(!defined('PATH_INCLUDE_CONFIG')) define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);
if(!defined('PATH_UPLOAD_TEMPORARY')) define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.
define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // remember trailing slash - used for the demo formular
if(!defined('PATH_CACHE')) {
    define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache
}
define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup/');

// paths on www
if(defined('NET_SCHEME') && defined('NET_HOST') && defined('NET_DIRECTORY')) {
    define('PATH_WWW', NET_SCHEME.NET_HOST.NET_DIRECTORY);
    define('PATH_WWW_MODULE', PATH_WWW.'modules/');
    define('PATH_WWW_SHARED', PATH_WWW.'shared/');
    // filehandler
    define('FILE_VIEWER', PATH_WWW . 'main/file/');
}

if (!defined('MDB2_DEBUG')) {
    define('MDB2_DEBUG', false);
}

// This showed not to be the right solution to change this setting - but what then... /Sune (29-08-2007)
// define('MDB2_PORTABILITY_EMPTY_TO_NULL', false);

// Filehandler
if (!defined('IMAGE_LIBRARY')) define('IMAGE_LIBRARY', 'GD');

// timezone and local
if(!defined('COUNTRY_LOCAL')) define('COUNTRY_LOCAL', 'da_DK');
if(!defined('TIMEZONE')) define('TIMEZONE', 'Europe/Copenhagen');
setlocale(LC_CTYPE, COUNTRY_LOCAL);
putenv("TZ=".TIMEZONE);

// database
if (!defined('DB_DSN')) define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');
// settings for theme
require_once 'config/setting_themes.php';

// functions
require_once 'Intraface/functions/functions.php';

// third party .
require_once 'DB/Sql.php';

// system files
require_once 'Intraface/Standard.php';
require_once 'Intraface/Main.php';
require_once 'Intraface/Shared.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/User.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/Address.php';
require_once 'Intraface/Page.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/Redirect.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Validator.php';

// database
require_once 'MDB2.php';

// Systembesked
require_once 'Intraface/shared/systemmessage/SystemDisturbance.php';

// core files
require_once 'Intraface/tools/Date.php';
require_once 'Intraface/tools/Amount.php';

$db = MDB2::singleton(DB_DSN, array('persistent' => true));
if (PEAR::isError($db)) {
    trigger_error($db->getMessage(), E_USER_ERROR);
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->setOption('debug', MDB2_DEBUG);
$db->setOption('portability', MDB2_PORTABILITY_NONE);

if ($db->getOption('debug')) {
    $db->setOption('log_line_break', "\n\n\n\n\t");

    require_once 'MDB2/Debug/ExplainQueries.php';

    $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
    $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

    register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
    register_shutdown_function(array($my_debug_handler, 'dumpInfo'));


}

if(defined('TIMEZONE')) {
    $db->exec('SET time_zone=\''.TIMEZONE.'\'');
}

require_once 'ErrorHandler.php';
require_once 'ErrorHandler/Observer/Echo.php';
require_once 'ErrorHandler/Observer/File.php';

function intrafaceBackendErrorhandler($errno, $errstr, $errfile, $errline, $errcontext) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    //$errorhandler->addObserver(new ErrorHandler_Observer_Echo, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // From php.net "~ $a: Bits that are set in $a are not set, and vice versa." That means the observer is used on everything but ERROR_LEVEL_CONTINUE_SCRIPT
    return $errorhandler->handleError($errno, $errstr, $errfile, $errline, $errcontext);
}

function intrafaceBackendExceptionhandler($e) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    //$errorhandler->addObserver(new ErrorHandler_Observer_Echo);
    return $errorhandler->handleException($e);
}

set_error_handler('intrafaceBackendErrorhandler', ERROR_HANDLE_LEVEL);
set_exception_handler('intrafaceBackendExceptionhandler');

// This is probably not the correct place/way to put this, but we should make it as some kind of at global setting - maybe a constant is the way to go.
// @todo: of some strange reason dreamhost does not support XMLRPCext on the server - why do the cms clients then work on other sites?!
require_once 'XML/RPC2/Backend.php';
XML_RPC2_Backend::setBackend('php');

// vi skal have lavet en fil, der bare sørger for at inkludere filer.
// i virkelighede var det måske smart, hvis vi brugte lidt
// require_once så listen ikke var så lang - på den måde
// fandt vi også mere grundigt ud af, hvilke viler der behøver
// hvilke filer i stedet for bare en stor sikkerhedshalløj.
// på den måde kan vi også flytte authentication is logged in til denne fil


//require 'auth.php';

?>