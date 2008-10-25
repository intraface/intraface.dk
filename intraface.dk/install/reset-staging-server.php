<?php
require_once '../common.php';

$install_class = PATH_ROOT.'install/Install.php';
if (!file_exists($install_class)) {
    trigger_error('The install class is not present. Probably because you should not run it now!', E_USER_ERROR);
    exit;
}
require $install_class;

$install = new Intraface_Install;

session_start();
$auth = new Intraface_Auth(session_id());
$auth->clearIdentity();

if ($install->resetServer()) {

    if (!empty($_GET['modules'])) {
        $install->grantModuleAccess($_GET['modules']);
    }

    if (!empty($_GET['helper_function'])) {
        $install->runHelperFunction($_GET['helper_function']);
    }

    if (!empty($_GET['login'])) {
        if ($install->loginUser()) {
            // header('location: /main/index.php');
            // exit;
        } else {
            echo 'Error in login';
        }
    }

    delete_cache_files_from_demo(dirname(__FILE__) . '/../demo/');

    echo 'staging server reset. Go to <a href="../main/index.php">login</a>.';
} else {
    echo 'error';
}

function delete_cache_files_from_demo($f)
{
        if (is_dir($f)) {
            foreach (scandir($f) as $item) {
                if (strpos($item, 'cache_') !== false) {
                    unlink($f . $item);
                }
            }
        }
}