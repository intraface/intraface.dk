<?php
/**
 * Includes common files and makes common settings
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

//configuration
require_once 'config/configuration.php'; // this is the one in your source control

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