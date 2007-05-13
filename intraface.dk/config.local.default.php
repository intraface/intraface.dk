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

// net
define('NET_SCHEME', 'https://'); // http:// or https://
define('NET_HOST', 'www.intraface.dk'); // www.intraface.dk
define('NET_DIRECTORY', '/'); // / (slash) or other subdirectory

// paths
define('PATH_ROOT', ''); // remember trailing slash
define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // don't know what this is for? /Sune (11-05-2007)
define('PATH_CACHE', PATH_ROOT . 'cache/'); // path to cache
define('PATH_UPLOAD', '/home/.investor/intraface/upload/'); // remember trailing slash
define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup' . DIRECTORY_SEPARATOR);


// This part should have includepathes to:
// - intraface root: PATH_ROOT
// - intraface 3party (internal) (depricated)
// - intrafacePublic
// - Smartypants,
// - Pear

set_include_path(
	PATH_ROOT .
	PATH_SEPARATOR . PATH_ROOT.'Intraface/3Party/'. 
	PATH_SEPARATOR . '/usr/share/pear/' . 
	PATH_SEPARATOR . get_include_path()
);


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
define('USE_CACHE', false); // this is static deactivated in Page.php

// external connection

// lokale indstillinger
define('TIMEZONE', 'Europe/Copenhagen');
setlocale(LC_CTYPE, "da_DK");
putenv("TZ=".TIMEZONE);

?>