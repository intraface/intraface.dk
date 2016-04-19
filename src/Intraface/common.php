<?php
/**
 * Includes common files and makes common settings
 *
 * Should never be edited. Edit config.local.php in intraface.dk if you want to override default values.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
// required files
require_once dirname(__FILE__) . '/../../vendor/autoload.php';
require_once 'Intraface/functions.php';

// paths
if (!defined('PATH_INCLUDE_IHTML')) {
    define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
}
if (!defined('PATH_INCLUDE_MODULE')) {
    define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
}
if (!defined('PATH_INCLUDE_SHARED')) {
    define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);
}
if (!defined('PATH_INCLUDE_CONFIG')) {
    define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);
}
if (!defined('PATH_UPLOAD')) {
    define('PATH_UPLOAD', PATH_ROOT . 'upload/'); // Directory for upload of files.
}if (!defined('PATH_UPLOAD_TEMPORARY')) {
    define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.
}if (!defined('PATH_CAPTCHA')) {
    define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // remember trailing slash - used for the demo formular
}if (!defined('PATH_CACHE')) {
    define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache
}if (!defined('PATH_INCLUDE_BACKUP')) {
    define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup/');
}

// paths on www
if (defined('NET_SCHEME') && defined('NET_HOST') && defined('NET_DIRECTORY')) {
    define('PATH_WWW', NET_SCHEME.NET_HOST.NET_DIRECTORY);
    define('PATH_WWW_MODULE', PATH_WWW.'modules/');
    define('PATH_WWW_SHARED', PATH_WWW.'shared/');
    // filehandler
    define('FILE_VIEWER', PATH_WWW . 'file');
}

// wiring
$bucket = new bucket_Container(new Intraface_Factory());

// filehandler
if (!defined('IMAGE_LIBRARY')) {
    define('IMAGE_LIBRARY', 'GD');
}

// database
if (!defined('DB_DSN')) {
    define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');
}
if (!defined('MDB2_DEBUG')) {
    define('MDB2_DEBUG', false);
}
$db = $bucket->get('mdb2');

// timezone and local
if (!defined('COUNTRY_LOCAL')) {
    define('COUNTRY_LOCAL', 'da_DK');
}
if (!defined('TIMEZONE')) {
    define('TIMEZONE', 'Europe/Copenhagen');
}
setlocale(LC_CTYPE, COUNTRY_LOCAL);
putenv("TZ=".TIMEZONE);
if (defined('TIMEZONE')) {
    $db->exec('SET time_zone=\''.TIMEZONE.'\'');
}
$db->query('SET NAMES utf8');

Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_USE_DQL_CALLBACKS, true);
Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_TYPES | Doctrine::VALIDATE_CONSTRAINTS);
$doctrine_connection = $bucket->get('Doctrine_Connection_Common');
