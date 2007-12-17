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
define('NET_DIRECTORY', '/intraface/intraface.dk/'); // subdirectory. if non keep empty

// paths
define('PATH_ROOT', 'c:/Users/Lars Olesen/workspace/intraface/'); // remember trailing slash
define('PATH_INCLUDE_PATH', PATH_ROOT . PATH_SEPARATOR . get_include_path()); // remeber to use constant PATH_SEPARATOR after every path
// optional: define('PATH_UPLOAD', '/home/.investor/intraface/upload/'); // remember trailing slash
// optional: define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache

//
define('CONNECTION_INTERNET', true); // if the system has access to dns and more from the internet. true or false
define('SERVER_STATUS', 'TEST'); // if the system is in PRODUCTION or TEST mode

// error log
define('ERROR_HANDLE_LEVEL', E_ALL); //  which levels should error_handler take care of: E_ALL 
define('ERROR_LEVEL_CONTINUE_SCRIPT', 0); // Which level should the script continue executing. Development: 0, Production: E_USER_NOTICE ^ E_NOTICE
define('ERROR_REPORT_EMAIL', ''); // if you want to recieve an e-mail on every error.
define('ERROR_LOG', PATH_ROOT.'intraface/log/error.log'); // exact directory and filename

// timezone and local
define('TIMEZONE', 'Europe/Copenhagen');
define('COUNTRY_LOCAL', 'da_DK');

// for intranet maintenance
define('INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY', 'privatekeyshouldbereplaced'); // the private key of the intranet that has intranetmaintenance
define('INTRAFACE_XMLRPC_SERVER_URL', ''); // the url for intraface xml-rpc server. Empty for the default url.
define('INTRAFACE_XMLRPC_DEBUG', false);

define('INTRAFACE_ONLINEPAYMENT_PROVIDER', 'FakeQuickpay');
define('INTRAFACE_ONLINEPAYMENT_MERCHANT', '123');
define('INTRAFACE_ONLINEPAYMENT_MD5SECRET', 'fake');

?>