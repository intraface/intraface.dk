<?php
$contact_module = $kernel->useModule('contact');

$contact = new Contact($kernel);

if (!$contact->isFilledIn()):
	$_advice[] = array(
		'msg' => 'you can create contacts in the contact module',
		'link' => $contact_module->getPath(),
		'module' => $contact_module->getName()
	);
endif; 

?>