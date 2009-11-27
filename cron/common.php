<?php
/**
 * Set environment for the crontabs
 */
$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.php';
if (!file_exists($config_file)) {
    die('The config.local.php file is missing. Please create it.');
}
require_once $config_file;

set_include_path(PATH_INCLUDE_PATH.get_include_path());
require('Intraface/common.php');
