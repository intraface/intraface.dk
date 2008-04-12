<?php
require '../../include_first.php';
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
        } elseif (!$contact->address->get('email')) {
            trigger_error('Der er ikke angivet nogen e-mail til kunden', E_USER_ERROR);
        }

        if ($debtor->contact->get('preferred_invoice') <> 2) { // email
            trigger_error('Kunden foretrækker ikke post på e-mail', E_USER_ERROR);
        }

        // vi skal lige have oversat den her rigtigt
        $subject = $translation->get($debtor->get('type')) . ' #' . $debtor->get('number');

        // hvad skal den skrive her?

        if($debtor->get('type') == 'order') {
            $body = $kernel->setting->get('intranet', 'debtor.order.email.text');
        } else {
            $body = '';
        }
        break;

    case 'electronic_email':

        // find ud af hvem der er scan in contact
        // måske skal vi lige tjekke om det overhovedet er en faktura
        $scan_in_contact_id = $kernel->setting->get('intranet', 'debtor.scan_in_contact');

        $contact = new Contact($kernel, $scan_in_contact_id);
        if (!$contact->get('id') > 0) {
            trigger_error('Der er ikke angivet nogen kontakt at sende de elektroniske fakturaer til', E_USER_ERROR);
        } elseif (!$contact->address->get('email')) {
            trigger_error('Der er ikke angivet nogen e-mail til Læs-Ind bureauet', E_USER_ERROR);
        }

        if ($debtor->contact->get('preferred_invoice') <> 3) { // elektronisk faktura
            trigger_error('Kunden foretrækker ikke elektronisk faktura!', E_USER_ERROR);
        } elseif(!$debtor->contact->address->get('ean')) {
            trigger_error('EAN-nummeret er ikke sat', E_USER_ERROR);
        }

        $subject = 'Elektronisk faktura';
        $body = 'Hermed faktura #' . $debtor->get('number') . ' til at læse ind';

        break;

}

if(($debtor->  get("type") == "order" || $debtor->get("type") == "invoice") && $kernel->intranet->hasModuleAccess('onlinepayment')) {
    $kernel->useModule('onlinepayment');
    $onlinepayment = OnlinePayment::factory($kernel);
}
else {
    $onlinepayment = NULL;
}

if($kernel->intranet->get("pdf_header_file_id") != 0) {
    $file = new FileHandler($kernel, $kernel->intranet->get("pdf_header_file_id"));
}
else {
    $file = NULL;
}

# gem debtoren som en fil i filsystemet
$filehandler = new FileHandler($kernel);
$tmp_file = $filehandler->createTemporaryFile($translation->get($debtor->get("type")).$debtor->get('number').'.pdf');

// Her gemmes filen
require_once 'Intraface/modules/debtor/Visitor/Pdf.php';
$report = new Debtor_Report_Pdf($translation, $file);
$report->visit($debtor, $onlinepayment);
$report->output('file', $tmp_file->getFilePath());

# gem filen med filehandleren
$filehandler = new FileHandler($kernel);
if (!$file_id = $filehandler->save($tmp_file->getFilePath(), $tmp_file->getFileName(), 'hidden', 'application/pdf')) {
    echo $filehandler->error->view();
    trigger_error('Filen kunne ikke gemmes', E_USER_ERROR);
}

/**
 * @TODO: This is not right! the invoice should not be public!
 */
$input['accessibility'] = 'public';
if (!$file_id = $filehandler->update($input)) {
    echo $filehandler->error->view();
    trigger_error('Oplysninger om filen kunne ikke opdateres', E_USER_ERROR);

}

switch($kernel->setting->get('intranet', 'debtor.sender')) {
    case 'intranet':
        $from_email = '';
        $from_name = '';
        break;
    case 'user':
        $from_email = $kernel->user->getAddress()->get('email');
        $from_name = $kernel->user->getAddress()->get('name');
        break;
    case 'defined':
        $from_email = $kernel->setting->get('intranet', 'debtor.sender.email');
        $from_name = $kernel->setting->get('intranet', 'debtor.sender.name');
        break;
    default:
        trigger_error("Invalid sender!", E_USER_ERROR);
        exit;
}

# opret e-mailen
$email = new Email($kernel);
if (!$email->save(array(
        'contact_id' => $contact->get('id'),
        'subject' => $subject,
        'body' => $body . "\n\n--\n" . $kernel->user->getAddress()->get('name') . "\n" . $kernel->intranet->get('name'),
        'from_email' => $from_email,
        'from_name' => $from_name,
        'type_id' => 10, // electronic invoice
        'belong_to' => $debtor->get('id')
    ))) {
    echo $email->error->view();
    trigger_error('E-mailen kunne ikke gemmes', E_USER_ERROR);
}

# tilknyt fil
if (!$email->attachFile($file_id, $filehandler->get('file_name'))) {
    echo $email->error->view();
    trigger_error('Filen kunne ikke vedhæftes', E_USER_ERROR);
}


switch ($send_as) {
    case 'email':

            /*
            This is now changed so we only make one redirect
            // Because we are going thru two pages we are making to redirects.
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
            */

            $redirect = Redirect::factory($kernel, 'go');
            $shared_email = $kernel->useShared('email');

            // First vi set the last, because we need this id to the first.
            $url = $redirect->setDestination($shared_email->getPath().'edit.php?id='.$email->get('id'), $module_debtor->getPath().'view.php?id='.$debtor->get('id'));
            $redirect->setIdentifier('send_email');
            $redirect->askParameter('send_email_status');

            header('Location: ' . $url);
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
            echo $email->error->view();
            trigger_error('E-mailen kunne ikke sendes', E_USER_ERROR);
        }

        break;
    default:
            trigger_error('not valid send as', E_USER_ERROR);
        break;
}
exit;
?>
