<?php
/**
 * This file is to be included on every page.
 *
 * @author Lars Olesen <lars@legestue.net>
 */

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
	trigger_error('This file cannot be accessed directly', E_USER_ERROR);
}

$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.php';

if (!file_exists($config_file)) {
	die('The config.php file is missing. Please create it.');
}

require $config_file;
require PATH_ROOT . 'Intraface/include_first.php';
?>