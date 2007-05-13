<?php
/**
 * Configuration - should never be edited
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

// path
define('PATH_UPLOAD_TEMPORARY', 'tempdir/'); // Mappen i Upload_path, under intranetid, hvor temp-filer placeres.

define('PATH_INCLUDE_COMMON', PATH_ROOT.'Intraface/common'.DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_IHTML', PATH_ROOT.'Intraface/ihtml' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_MODULE', PATH_ROOT.'Intraface/modules' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_SHARED', PATH_ROOT.'Intraface/shared' . DIRECTORY_SEPARATOR);

define('PATH_INCLUDE_FUNCTIONS', PATH_INCLUDE_COMMON.'functions' . DIRECTORY_SEPARATOR);
define('PATH_INCLUDE_CONFIG', PATH_ROOT.'/Intraface/config'.DIRECTORY_SEPARATOR);

// paths on www
define('PATH_WWW', NET_SCHEME.NET_HOST.NET_DIRECTORY);
define('PATH_WWW_MODULE', PATH_WWW.'modules/');
define('PATH_WWW_SHARED', PATH_WWW.'shared/');


if (!defined('MDB2_DEBUG')) {
	define('MDB2_DEBUG', 0);
}

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