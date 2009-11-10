<?php
require_once 'Ilib/ClassLoader.php';

$config = new Config();
$parsed = $config->parseConfig('config.local.php', 'PHPConstants');
$settings = $parsed->toArray();

print_r($settings);

echo $settings['root']['DB_HOST'];