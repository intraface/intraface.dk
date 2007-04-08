<?php
/**
 * Configuration - should never be edited
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

// path
define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.

define('PATH_INCLUDE_COMMON', PATH_INCLUDE.'common'.DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_IHTML', PATH_INCLUDE.'ihtml' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_MODULE', PATH_INCLUDE.'modules' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_SHARED', PATH_INCLUDE.'shared' . DIRECTORY_SEPARATOR);

define('PATH_INCLUDE_FUNCTIONS', PATH_INCLUDE_COMMON.'functions' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_CONFIG', PATH_INCLUDE.'config'.DIRECTORY_SEPARATOR);

define('PATH_INCLUDE_3PARTY', PATH_INCLUDE . '3Party' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_PEAR', PATH_INCLUDE_3PARTY . 'PEAR' . DIRECTORY_SEPARATOR);

// path to backup
define('PATH_INCLUDE_BACKUP', PATH_ROOT . 'backup' . DIRECTORY_SEPARATOR);

// paths on www
define('PATH_WWW_MODULE', PATH_WWW.'modules/');
define('PATH_WWW_SHARED', PATH_WWW.'shared/');

// error logs - defined in config.local.php
// define('ERROR_LOG', PATH_INCLUDE.'log'.DIRECTORY_SEPARATOR.'error.log'); // exact directory and filename
// define('ERROR_LOG_UNIQUE', PATH_INCLUDE.'log'.DIRECTORY_SEPARATOR.'error-unique.log'); // exact directory and filename

if (!defined('MDB2_DEBUG')) {
	define('MDB2_DEBUG', 0);
}
define('PATH_CAPTCHA', PATH_ROOT . 'captcha/');
define('PATH_CACHE', PATH_ROOT . 'cache/');

// include path
set_include_path(
	PATH_SEPARATOR . PATH_INCLUDE_PEAR
	. PATH_SEPARATOR . PATH_INCLUDE
	. PATH_SEPARATOR . PATH_INCLUDE_3PARTY
	. PATH_SEPARATOR . PATH_ROOT
	. PATH_SEPARATOR . get_include_path());

// Filehandler
define('FILE_VIEWER', PATH_WWW . 'main/file/'); // is this used anymore?
define('IMAGE_LIBRARY', 'GD');

// database
define('DB_DSN', 'mysql://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.'');

// sessions
//ini_set('session.use_trans_sid', false); // disable transparent sessions
//ini_set('url_rewriter.tags','');



/*
 * On my platform, I need to set the BaseURL for ZF 0.20
 * RewriteBase is assumed to be $_SERVER['PHP_SELF'] after
 * removing the trailing "index.php" string.
 *
 * PHP_SELF can be user manipulated. Avoided using SCRIPT_NAME
 * or SCRIPT_FILENAME because they may differ depending on SAPI
 * being used.
 */
// we should probalbly implement something like this.
//$base_url = substr($_SERVER['PHP_SELF'], 0, -9);


?>