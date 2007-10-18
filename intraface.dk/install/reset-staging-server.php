<?php
require_once '../common.php';

$install_class = PATH_ROOT.'install/Install.php';
if(!file_exists($install_class)) {
    trigger_error('The install class is not present. Probably because you should not run it now!', E_USER_ERROR);
    exit;
}
require $install_class;

$install = new Intraface_Install;

if ($install->resetServer()) {
    echo 'staging server reset. Go to <a href="../main/login.php">login</a>.';
}
else {
    echo 'error';
}


?>