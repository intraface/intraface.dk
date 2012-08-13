<?php
/**
 * Local configuration
 *
 * Change values and rename to config.local.php
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

error_reporting(E_ALL & ~E_DEPRECATED);

// database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'intraface_test');

// net
define('NET_SCHEME', 'http://'); // http:// or https://
define('NET_HOST', 'localhost'); // www.intraface.dk
define('NET_DIRECTORY', '/intraface/src/intraface.dk/'); // subdirectory. if non keep empty

// smtp
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_HOST', '');

// paths
define('PATH_ROOT', dirname(__FILE__) . '/../'); // remember trailing slash
define('PATH_INCLUDE_PATH', PATH_ROOT . PATH_SEPARATOR . get_include_path()); // remember to use constant PATH_SEPARATOR after every path
// optional: define('PATH_UPLOAD', '/home/.investor/intraface/upload/'); // remember trailing slash
// optional: define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache

//
define('CONNECTION_INTERNET', true); // if the system has access to dns and more from the internet. true or false
define('SERVER_STATUS', 'TEST'); // if the system is in PRODUCTION or TEST mode

// error log
define('ERROR_LOG', PATH_ROOT.'intraface/log/error.log'); // exact directory and filename
define('K2_LOG', '/var/lib/wwwrun/intraface/log/k2.log');

// timezone and local
define('TIMEZONE', 'Europe/Copenhagen');
define('COUNTRY_LOCAL', 'da_DK');

// for intranet maintenance
define('INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY', 'privatekeyshouldbereplaced'); // the private key of the intranet that has intranetmaintenance
define('INTRAFACE_INTRANETMAINTENANCE_SHOP_ID', 0); // the id of the shop used to modulepackage
define('INTRAFACE_XMLRPC_SERVER_URL', ''); // the url for intraface xml-rpc server. Empty for the default url.
define('INTRAFACE_XMLRPC_DEBUG', false);

define('INTRAFACE_ONLINEPAYMENT_PROVIDER', 'Testing');
define('INTRAFACE_ONLINEPAYMENT_MERCHANT', '123');
define('INTRAFACE_ONLINEPAYMENT_MD5SECRET', 'fake');
