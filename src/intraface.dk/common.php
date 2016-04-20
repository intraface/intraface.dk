<?php
/**
 * To be included to make intraface work correctly.
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @author  Sune Jensen <sj@sunet.dk>
 * @since   0.1.0
 * @version @package-version@
 */

if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) {
    throw new Exception('This file cannot be accessed directly');
}

$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.php';

if (!file_exists($config_file)) {
    $config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.local.default.php';
}

require_once $config_file;

require_once 'Intraface/common.php';
