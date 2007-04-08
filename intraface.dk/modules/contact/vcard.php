<?php
require('../../include_first.php');
require('Contact/Vcard/Build.php');

if (empty($_GET['id']) OR !is_numeric($_GET['id'])) {
	trigger_error('Du kan ikke lave et vCard uden et kontakt id', E_USER_ERROR);
}

$kernel->module('contact');

$contact = new Contact($kernel, (int)$_GET['id']);


// instantiate a builder object
// (defaults to version 3.0)
$vcard = new Contact_Vcard_Build();

// set a formatted name
$vcard->setFormattedName($contact->get('name'));

// set the structured name parts
$vcard->setName($contact->get('surname'), $contact->get('firstname'), '', '', '');

// add phone
$vcard->addTelephone($contact->address->get('phone'));
$vcard->addParam('TYPE', 'HOME');
$vcard->addParam('TYPE', 'PREF');

// add a home/preferred email
$vcard->addEmail('bolivar@example.net');
$vcard->addParam('TYPE', 'HOME');
$vcard->addParam('TYPE', 'PREF');

// add a work address
$vcard->addAddress('', '', $contact->address->get('address'), $contact->address->get('city'), '', $contact->address->get('postcode'), $contact->address->get('country'));
$vcard->addParam('TYPE', 'WORK');

// get back the vCard and print it
$output = $vcard->fetch();

$filename = $contact->get('name') . '.vcf';

header('HTTP/1.1 200 OK');
header('Content-Length: ' . strlen($output));
//header("Content-Type: application/force-download");
header('Content-Type: text/x-vCard; name='.$filename);
header('Content-Disposition: attachment; filename=$filename');
header('Content-Description: VCard for ' . $contact->get('name'));
//header("Content-Transfer-Encoding: binary');
header('Connection: close');

echo $output;
?>