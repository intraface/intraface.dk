<?php
/**
 * Configuration - should never be edited
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @author  Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

// paths
define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);
define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.
define('PATH_CAPTCHA', PATH_ROOT . 'captcha/'); // remember trailing slash - used for the demo formular
define('PATH_CACHE', PATH_ROOT . 'cache/'); // remember trailing slash - path to cache
define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup/');

// paths on www
define('PATH_WWW', NET_SCHEME.NET_HOST.NET_DIRECTORY);
define('PATH_WWW_MODULE', PATH_WWW.'modules/');
define('PATH_WWW_SHARED', PATH_WWW.'shared/');

if (!defined('MDB2_DEBUG')) {
    define('MDB2_DEBUG', false);
}

define('MDB2_PORTABILITY_EMPTY_TO_NULL', false);

// Filehandler
define('FILE_VIEWER', PATH_WWW . 'main/file/');
define('IMAGE_LIBRARY', 'GD');

// database
define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');
?>