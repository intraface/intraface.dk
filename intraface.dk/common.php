<?php
/**
 * To be included on every page accessible through the webserver
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @author  Sune Jensen <sj@sunet.dk>
 * @since   0.1.0
 * @version @package-version@
 */

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
	trigger_error('This file cannot be accessed directly', E_USER_ERROR);
}

$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.php';

if (!file_exists($config_file)) {
	die('The config.local.php file is missing. Please create it.');
}

require_once $config_file;
require_once 'Intraface/common.php';
?>