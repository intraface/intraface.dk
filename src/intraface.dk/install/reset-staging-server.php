<?php
require_once dirname(__FILE__) . '/../common.php';

$install_class = realpath(dirname(__FILE__) . '/../../../install/') . '/Install.php';

if (!file_exists($install_class)) {
    throw new Exception('The install class is not present. Probably because you should not run it now!');
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

    if (!empty($_GET['login'])) {
        echo 'staging server reset. Go to <a href="../core/testlogin">login</a>.';
    } else {
        echo 'staging server reset. Go to <a href="../core/login">login</a>.';
    }

    echo '<p>Year: <span id="year">'.date('Y').'</span></p>';
    echo '<p>Date (da): <span id="date_da">'.date('d-m-Y').'</span></p>';
    echo '<p>Shortyear: <span id="short_year">'.substr(date('Y'), -2).'</span></p>';

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
