<?php
require('../../include_first.php');

$kernel->module("debtor");
$translation = $kernel->getTranslation('contact');

if ($kernel->user->hasModuleAccess("invoice")) {
	header("Location: list.php?type=invoice");
	exit;
} elseif ($kernel->user->hasModuleAccess("order")) {
	header("Location: list.php?type=order");
	exit;
} elseif ($kernel->user->hasModulesAccess("quotation")) {
	header("Location: list.php?type=quotation");
	exit;
}

$page = new Intraface_Page($kernel);
$page->start();
?>

<h1><?php e(__('Debtor')); ?></h1>

<p><?php e(__('You do not have access to the following modules: quotation, order or invoice.')); ?></p>

<?php
$page->end();
