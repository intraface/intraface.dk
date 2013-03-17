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
define('DB_PASS', 'intraface');
define('DB_NAME', 'intraface_test');

// net
define('NET_SCHEME', 'http://'); // http:// or https://
define('NET_HOST', 'localhost:8080'); // www.intraface.dk
define('NET_DIRECTORY', '/src/intraface.dk/'); // subdirectory. if non keep empty

// define paths - remember trailing slahs
define('PATH_ROOT', dirname(__FILE__) . '/../');

// optional upload path - remember trailing slash 
define('PATH_UPLOAD', '/var/cache/intraface/upload/');

// optional path to cache - remember trailing slash
define('PATH_CACHE', '/var/cache/intraface/');

// set include path
define('PATH_INCLUDE_PATH', PATH_ROOT . PATH_SEPARATOR . get_include_path());
set_include_path(PATH_INCLUDE_PATH);

// error logs
define('ERROR_LOG', '/var/log/intraface/error.log'); // exact directory and filename
define('K2_LOG', '/var/log/intraface/k2.log');
define('TRANSLATION_ERROR_LOG', ERROR_LOG);

// smtp
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_HOST', '');

// timezone and local
define('TIMEZONE', 'Europe/Copenhagen');
define('COUNTRY_LOCAL', 'da_DK');

// for intranet maintenance 
// the private key of the intranet that has intranetmaintenance
define('INTRAFACE_INTRANETMAINTENANCE_INTRANET_PRIVATE_KEY', 'privatekeyshouldbereplaced'); 

// the id of the shop used to modulepackage
define('INTRAFACE_INTRANETMAINTENANCE_SHOP_ID', 0); 

// the url for intraface xml-rpc server. Empty for the default url.
define('INTRAFACE_XMLRPC_SERVER_URL', '');
define('INTRAFACE_XMLRPC_DEBUG', false);
define('INTRAFACE_ONLINEPAYMENT_PROVIDER', 'Testing');
define('INTRAFACE_ONLINEPAYMENT_MERCHANT', '123');
define('INTRAFACE_ONLINEPAYMENT_MD5SECRET', 'fake');

// if the system has access to dns and more from the internet. true or false
define('CONNECTION_INTERNET', true);

// if the system is in PRODUCTION or TEST mode
define('SERVER_STATUS', 'TEST'); 
