<?php
/**
 * Local configuration
 *
 * Change values and rename to config.local.php
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

// database
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', 'intraface');

// paths
define('PATH_ROOT', '/home/intraface/'); // remember trailing slash
define('PATH_INCLUDE_PATH', ''); // remeber to use constant PATH_SEPARATOR after every path

// 
define('SERVER_STATUS', 'TEST'); // if the system is in PRODUCTION or TEST mode

// error log
define('ERROR_HANDLE_LEVEL', E_ALL); //  which levels should error_handler take care of: E_ALL 
define('ERROR_LEVEL_CONTINUE_SCRIPT', E_NOTICE); // Which level should the script continue executing. Development: 0, Production: E_USER_NOTICE ^ E_NOTICE
define('ERROR_REPORT_EMAIL', ''); // if you want to recieve an e-mail on every error.
define('ERROR_LOG', PATH_ROOT.'log/error.log'); // exact directory and filename

?>