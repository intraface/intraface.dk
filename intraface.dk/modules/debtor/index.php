<?php
require('../../include_first.php');

$kernel->module("debtor");
$translation = $kernel->getTranslation('contact');

if($kernel->user->hasModuleAccess("invoice")) {
	header("Location: list.php?type=invoice");
	exit;
}
elseif($kernel->user->hasModuleAccess("order")) {
	header("Location: list.php?type=order");
	exit;
}
elseif($kernel->user->hasModulesAccess("quotation")) {
	header("Location: list.php?type=quotation");
	exit;
}
else {
	$page = new Page($kernel);
	$page->start();
	?>
	<H1>Debitor</h1>

	<p>Du mangler adgang til enten faktura-, ordre- eller tilbudsmodullet.</p>

	<?php
	$page->end();
}
?>
