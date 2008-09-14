<?php
require_once 'Doctrine/lib/Doctrine.php';
spl_autoload_register(array('Doctrine', 'autoload'));
require 'Ilib/ClassLoader.php';

$conn = Doctrine_Manager::connection('mysql://root:klani@localhost/intraface');    
Doctrine_Manager::getInstance()->setAttribute("model_loading", "conservative");

$item = new Model_Product;
$item->name = 'Version 1';
$item->Translation['DK']->description = 'En dansk beskrivelse';
$item->Translation['EN']->description = 'Some english description';
$item->price = 1;
$item->save();

echo $item->version;

$item->name = 'Version 2';

$item->save();

echo $item->version;


$item->name = 'Version 3';

$item->save();

echo $item->version;

// @todo hvorfor er det at den ikke loader relationer automatisk

$reopened = Doctrine::getTable('Model_Product')->findOneById($item->id);
echo $reopened->name;
echo $reopened->Translation['DK']->description;
$log = $reopened->getAuditLog();
$version1 =  $log->getVersion($item, 2);
print_r($version1);

