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
define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);
define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.
define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // remember trailing slash - used for the demo formular
define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache
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
define('IMAGE_LIBRARY', 'GD');

// timezone and local
if(!defined('COUNTRY_LOCAL')) define('COUNTRY_LOCAL', 'da_DK');
if(!defined('TIMEZONE')) define('TIMEZONE', 'Europe/Copenhagen');
setlocale(LC_CTYPE, COUNTRY_LOCAL);
putenv("TZ=".TIMEZONE);

// database
define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');
// settings for theme
require_once 'config/setting_themes.php';

// functions
require_once 'functions/functions.php';

// third party .
require_once '3Party/Database/Db_sql.php';

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
if(defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
   require_once 'ErrorHandler/Observer/BlueScreen.php';
}
else {
    require_once 'ErrorHandler/Observer/User.php';
}
require_once 'ErrorHandler/Observer/File.php';

function errorhandler($errno, $errstr, $errfile, $errline, $errcontext) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if(defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // From php.net "~ $a: Bits that are set in $a are not set, and vice versa." That means the observer is used on everything but ERROR_LEVEL_CONTINUE_SCRIPT
    }
    else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // See description of ~ above
    }
    return $errorhandler->handleError($errno, $errstr, $errfile, $errline, $errcontext);
}

function exceptionhandler($e) {
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if(defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen);
    }
    else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User);
    }
    return $errorhandler->handleException($e);
}

set_error_handler('errorhandler', ERROR_HANDLE_LEVEL);
set_exception_handler('exceptionhandler');

// vi skal have lavet en fil, der bare sørger for at inkludere filer.
// i virkelighede var det måske smart, hvis vi brugte lidt
// require_once så listen ikke var så lang - på den måde
// fandt vi også mere grundigt ud af, hvilke viler der behøver
// hvilke filer i stedet for bare en stor sikkerhedshalløj.
// på den måde kan vi også flytte authentication is logged in til denne fil


//require 'auth.php';

?>