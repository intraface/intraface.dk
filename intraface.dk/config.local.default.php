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
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pear');

// net
define('NET_SCHEME', 'http://'); // http:// or https://
define('NET_HOST', 'localhost'); // www.intraface.dk
define('NET_DIRECTORY', '/intraface/intraface.dk/'); // / (slash) or other subdirectory

// paths
define('PATH_ROOT', 'c:/Users/Lars Olesen/workspace/intraface/'); // remember trailing slash
define('PATH_UPLOAD', '/home/.investor/intraface/upload/'); // remember trailing slash
define('PATH_INCLUDE_PATH', PATH_ROOT . PATH_SEPARATOR . get_include_path());

//
define('CONNECTION_INTERNET', true); // if the system has access to dns and more from the internet. true or false
define('SERVER_STATUS', 'TEST'); // if the system is in PRODUCTION or TEST mode

// error log
define('ERROR_REPORT_EMAIL', '');
define('ERROR_LOG', PATH_ROOT.'intraface/log/error.log'); // exact directory and filename
define('ERROR_LOG_UNIQUE', PATH_ROOT.'intraface/log/unique-error.log'); // exact directory and filename
define('ERROR_DISPLAY_USER', true);
define('ERROR_DISPLAY', true);
define('ERROR_HANDLE_LEVEL', E_ALL);
define('ERROR_LEVEL_CONTINUE_SCRIPT', 10);
?>