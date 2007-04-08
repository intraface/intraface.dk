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

// errorhandler php5
require_once 'ErrorHandler/ErrorHandler.php';

// settings for theme
require_once 'Intraface/config/setting_themes.php';

// functions
require_once 'Intraface/functions/functions.php';

// third party
require_once PATH_INCLUDE_3PARTY . 'Database'.DIRECTORY_SEPARATOR.'Db_sql.php';
//require(PATH_INCLUDE_3PARTY . 'mysql_session_handler'.DIRECTORY_SEPARATOR.'mysql_session_handler.php');

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

// Systembesked - ikke kønt, men ved ikke lige hvordan vi ellers kunne gøre det
require_once PATH_INCLUDE_SHARED.'systemmessage'.DIRECTORY_SEPARATOR.'SystemDisturbance.php';

// core files
require_once 'Intraface/tools'.DIRECTORY_SEPARATOR.'Date.php';
require_once 'Intraface/tools'.DIRECTORY_SEPARATOR.'Amount.php';

$db = MDB2::singleton(DB_DSN, array('persistent' => true));
if (PEAR::isError($db)) {
	trigger_error($db->getMessage(), E_USER_ERROR);
}

$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->setOption('debug', MDB2_DEBUG);

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

// vi skal have lavet en fil, der bare sørger for at inkludere filer.
// i virkelighede var det måske smart, hvis vi brugte lidt
// require_once så listen ikke var så lang - på den måde
// fandt vi også mere grundigt ud af, hvilke viler der behøver
// hvilke filer i stedet for bare en stor sikkerhedshalløj.
// på den måde kan vi også flytte authentication is logged in til denne fil


//require 'auth.php';

?>