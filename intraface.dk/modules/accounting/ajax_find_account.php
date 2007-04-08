<?php
/**
 * Bruges til at finde kontonumre under daybook.php, når man 'tabber' væk fra
 * enten kredit eller debet-feltet.
 */
require('../../include_first.php');

$kernel->module('accounting');

if (empty($_GET['s']) OR !is_numeric($_GET['s'])) {
	exit;
}

$year = new Year($kernel);

$account = Account::factory($year, $_GET['s']);


header('Content-type: text/xml');
if ($account->get('id') > 0) {
	echo utf8_encode($account->get('name'));
}
else {
	echo 'Konto findes ikke';
}
?>
