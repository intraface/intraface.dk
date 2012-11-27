<?php
error_reporting(E_ALL ^~E_STRICT);
ini_set('display_errors', 1);

if (isset($_SERVER['argv']) && !empty($_SERVER['argv'][1])) {
    $pass = $_SERVER['argv'][1];
} else {
    $pass = '';
}

define('SERVER_STATUS', 'DEVELOPEMENT');
define('DB_DSN', 'mysql://root:' . $pass . '@localhost/intraface_test');
define('DB_NAME', 'intraface_test');

require_once 'Ilib/ClassLoader.php';
$install_class = dirname(__FILE__). '/../../install/Install.php';

if (!file_exists($install_class)) {
    throw new Exception('The install class is not present. Probably because you should not run it now!');
    exit;
}
require $install_class;

try { 
	$install = new Intraface_Install;
	$install->dropDatabase();
	$install->createDatabaseSchema();
} catch (Exception $e) {
	echo $e->getMessage();	
	exit(1);
}

exit(0);
