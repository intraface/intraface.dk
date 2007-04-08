<?php
/**
 * Local configuration
 *
 * Change values and rename to config.local.php
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

define('CONNECTION_INTERNET', true); // if the system has access to dns and more from the internet. true or false
define('SERVER_STATUS', 'PRODUCTION'); // if the system is in PRODUCTION or TEST mode

// paths
define('PATH_ROOT', ''); // remember trailing slash
define('PATH_WWW', 'http://localhost/intraface/intraface.dk/'); // remember trailins slash
define('PATH_INCLUDE', PATH_ROOT . '');

// upload path
define('PATH_UPLOAD', '/home/.investor/intraface/upload/'); // remember trailing slash

// database
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

// error log
define('ERROR_REPORT_EMAIL', '');
define('ERROR_LOG', PATH_ROOT.'intraface/log/error.log'); // exact directory and filename
define('ERROR_LOG_UNIQUE', PATH_ROOT.'intraface/log/unique-error.log'); // exact directory and filename
define('ERROR_DISPLAY_USER', true);
define('ERROR_DISPLAY', true);
define('ERROR_HANDLE_LEVEL', E_ALL);
define('ERROR_LEVEL_CONTINUE_SCRIPT', 10);

// cache
define('USE_CACHE', true);

// external connection

// lokale indstillinger
define('TIMEZONE', 'Europe/Copenhagen');
setlocale(LC_CTYPE, "da_DK");
putenv("TZ=".TIMEZONE);

?>