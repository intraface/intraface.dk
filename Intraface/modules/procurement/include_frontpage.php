<?php
$procurement_module = $kernel->useModule('procurement');

$procurement = new Procurement($kernel);

if (!$procurement->isFilledIn()):
	$_advice[] = array(
		'msg' => 'you can create new procurements',
		'link' => $procurement_module->getPath(),
		'module' => $procurement_module->getName()
	);
endif; 

?>