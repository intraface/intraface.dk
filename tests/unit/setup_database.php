<?php
error_reporting(E_ALL ^~E_STRICT);

define('SERVER_STATUS', 'DEVELOPEMENT');
define('DB_DSN', 'mysql://root:@localhost/intraface_test');
define('DB_NAME', 'intraface_test');

require_once 'Ilib/ClassLoader.php';
$install_class = dirname(__FILE__). '/../../install/Install.php';

if (!file_exists($install_class)) {
    throw new Exception('The install class is not present. Probably because you should not run it now!');
    exit;
}
require $install_class;

$install = new Intraface_Install;
$install->dropDatabase();
$install->createDatabaseSchema();
