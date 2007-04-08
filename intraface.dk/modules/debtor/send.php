<?php
require('../../include_first.php');
$module_debtor = $kernel->module('debtor');
$kernel->useModule('contact');
$kernel->useModule('product');
$kernel->useShared('pdf');
$kernel->useShared('filehandler');
$kernel->useShared('email');

$translation = $kernel->getTranslation('debtor');

$send_as = $_GET['send'];
$id = intval($_GET['id']);


# find debtoren
$debtor = Debtor::factory($kernel, intval($id));

switch ($send_as) {

	case 'email':

		$contact = new Contact($kernel, $debtor->get('contact_id'));
		if (!$contact->get('id') > 0) {
			trigger_error('Der er ikke angivet nogen kontakt at sende debtoren til', E_USER_ERROR);
		}
		elseif (!$contact->address->get('email')) {
			trigger_error('Der er ikke angivet nogen e-mail til kunden', E_USER_ERROR);
		}

		if ($debtor->contact->get('preferred_invoice') <> 2) { // email
			trigger_error('Kunden foretrækker ikke post på e-mail', E_USER_ERROR);
		}

		// vi skal lige have oversat den her rigtigt
		$subject = $translation->get($debtor->get('type')) . ' #' . $debtor->get('number');

		// hvad skal den skrive her?
		$body = '';

		break;

	case 'electronic_email':

		// find ud af hvem der er scan in contact
		// måske skal vi lige tjekke om det overhovedet er en faktura
		$scan_in_contact_id = $kernel->setting->get('intranet', 'debtor.scan_in_contact');

		$contact = new Contact($kernel, $scan_in_contact_id);
		if (!$contact->get('id') > 0) {
			trigger_error('Der er ikke angivet nogen kontakt at sende de elektroniske fakturaer til', E_USER_ERROR);
		}
		elseif (!$contact->address->get('email')) {
			trigger_error('Der er ikke angivet nogen e-mail til Læs-Ind bureauet', E_USER_ERROR);
		}

		if ($debtor->contact->get('preferred_invoice') <> 3) { // elektronisk faktura
			trigger_error('Kunden foretrækker ikke elektronisk faktura!', E_USER_ERROR);
		}
		elseif(!$debtor->contact->address->get('ean')) {
			trigger_error('EAN-nummeret er ikke sat', E_USER_ERROR);
		}

		$subject = 'Elektronisk faktura';
		$body = 'Hermed faktura #' . $debtor->get('number') . ' til at læse ind';

		break;

}


# gem debtoren som en fil i filsystemet
$filename = 'invoice' .$debtor->get('number').'.pdf';
$filepath = PATH_UPLOAD . $kernel->intranet->get('id') . '/';

if(!is_dir($filepath)) {
	mkdir($filepath);
}
$filepath = $filepath .'/tempdir/';
if(!is_dir($filepath)) {
	mkdir($filepath);
}

$upload_filename = $filepath . $filename;

// Her gemmes filen
$debtor->pdf('file', $upload_filename);

$full_filename = $filepath . $filename;

$input['file_size'] = filesize($full_filename);
$input['file_name'] = $filename;
$input['file_type'] = 'application/pdf';
$input['accessibility'] = 'public';
// $input['server_file_name'] = $filename;
$input['description'] = 'Faktura ' . $debtor->get('number') . ' sendt som elektronisk faktura';

# gem filen med filehandleren
$filehandler = new FileHandler($kernel);
if (!$file_id = $filehandler->save($full_filename, $filename, 'hidden')) {
	$filehandler->error->view();
	trigger_error('Filen kunne ikke gemmes', E_USER_ERROR);
}


if (!$file_id = $filehandler->update($input)) {
	$filehandler->error->view();
	trigger_error('Oplysninger om filen kunne ikke opdateres', E_USER_ERROR);

}

$from_email = $kernel->setting->get('intranet', 'debtor.sender.email');
$from_name = $kernel->setting->get('intranet', 'debtor.sender.name');

if (empty($from_email)) {
	$from_email = $kernel->intranet->address->get('email');
}
if (empty($from_name)) {
	$from_name = $kernel->intranet->address->get('name');
}


# opret e-mailen
$email = new Email($kernel);
if (!$email->save(array(
	'contact_id' => $contact->get('id'),
	'subject' => $subject,
	'body' => $body . "\n\n--\n" . $kernel->user->address->get('name') . "\n" . $kernel->intranet->get('name'),
	'from_email' => $from_email,
	'from_name' => $from_name,
	'type_id' => 10, // electronic invoice
	'belong_to' => $debtor->get('id')
))) {
	$email->error->view();
	trigger_error('E-mailen kunne ikke gemmes', E_USER_ERROR);
}

# tilknyt fil
if (!$email->attachFile($file_id, 'invoice' . $debtor->get('number') . '.pdf')) {
	$email->error->view();
	trigger_error('Filen kunne ikke vedhæftes', E_USER_ERROR);
}


switch ($send_as) {
	case 'email':

			// Because we are going thru to pages we are making to redirects.
			$first_redirect = Redirect::factory($kernel, 'go');
			$second_redirect = Redirect::factory($kernel, 'go');
			$shared_email = $kernel->useShared('email');

			// First vi set the last, because we need this id to the first.
			$second_redirect->setDestination($shared_email->getPath().'email.php?id='.$email->get('id'), $module_debtor->getPath().'view.php?id='.$debtor->get('id'));
			$second_redirect->setIdentifier('send_email');
			$second_redirect->askParameter('send_email_status');

			// then we set the first redirect, notice we are using the first redirect id, and send that with the page.
			$url = $first_redirect->setDestination($shared_email->getPath().'edit.php?id='.$email->get('id'), $shared_email->getPath().'email.php?id='.$email->get('id').'&redirect_id='.$second_redirect->get('id'));

			header('Location: ' . $url);

			/*$redirect = new Redirect($kernel);
			$shared_email = $kernel->useShared('email');


			$url = $redirect->setDestination($shared_email->getPath().'email.php?id='.$email->get('id'), $module_debtor->getPath().'view.php?id='.$debtor->get('id'));
			$redirect->setIdentifier('send_email');
			$redirect->askParameter('send_email_status');

			// some redirect stuff should probably be made here.
			header('Location: ' . $shared_email->getPath().'edit.php?id='.$email->get('id'));
			*/
			exit;
		break;
	case 'electronic_email':
		// Sender e-mailen
		if($email->send()) {
			if ($debtor->get('status') == 'created') {
				$debtor->setStatus('sent');
			}
			header('Location: view.php?id='.$debtor->get('id'));
			exit;
		}
		else {
			$email->error->view();
			trigger_error('E-mailen kunne ikke sendes', E_USER_ERROR);
		}

		break;
	default:
			trigger_error('not valid send as', E_USER_ERROR);
		break;
}
exit;
?>
