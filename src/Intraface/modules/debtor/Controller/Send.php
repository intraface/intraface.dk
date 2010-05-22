<?php
class Intraface_modules_debtor_Controller_Send extends k_Component
{
    function renderHtml()
    {
        $module_debtor = $this->context->getKernel()->getModule('debtor');

        $this->context->getKernel()->useModule('contact');
        $this->context->getKernel()->useModule('product');
        $this->context->getKernel()->useModule('filemanager');
        $this->context->getKernel()->useShared('email');

        $translation = $this->translator();

        // find debtoren
        $debtor = $this->context->getDebtor();

        switch ($this->query('send')) {

            case 'email':
                $contact = $debtor->getContact();
                if (!$contact->address->get('email')) {
                    throw new Exception('Der er ikke angivet nogen e-mail til kunden');
                }

                if ($debtor->contact->get('preferred_invoice') <> 2) { // email
                    throw new Exception('Kunden foretrækker ikke post på e-mail');
                }

                // vi skal lige have oversat den her rigtigt
                $subject = $translation->get($debtor->get('type')) . ' #' . $debtor->get('number');

                // hvad skal den skrive her?
                if ($debtor->get('type') == 'order') {
                    $body = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.order.email.text');
                } elseif ($debtor->get('type') == 'invoice') {
                    $body = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.invoice.email.text');
                } elseif ($debtor->get('type') == 'quotation') {
                    $body = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.quotation.email.text');
                } else {
                    $body = '';
                }
                break;

            case 'electronic_email':

                // find ud af hvem der er scan in contact
                // måske skal vi lige tjekke om det overhovedet er en faktura
                $scan_in_contact_id = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact');

                $contact = new Contact($this->context->getKernel(), $scan_in_contact_id);
                if (!$contact->get('id') > 0) {
                    throw new Exception('Der er ikke angivet nogen kontakt at sende de elektroniske fakturaer til');
                } elseif (!$contact->address->get('email')) {
                    throw new Exception('Der er ikke angivet nogen e-mail til Læs-Ind bureauet');
                }

                if ($debtor->contact->get('preferred_invoice') <> 3) { // elektronisk faktura
                    throw new Exception('Kunden foretrækker ikke elektronisk faktura!');
                } elseif (!$debtor->contact->address->get('ean')) {
                    throw new Exception('EAN-nummeret er ikke sat');
                }

                $subject = 'Elektronisk faktura';
                $body = 'Hermed faktura #' . $debtor->get('number') . ' til at læse ind';

                break;

        }

        if (($debtor->get("type") == "order" || $debtor->get("type") == "invoice") && $this->context->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
            $this->context->getKernel()->useModule('onlinepayment');
            $onlinepayment = OnlinePayment::factory($this->context->getKernel());
        } else {
            $onlinepayment = NULL;
        }

        if ($this->context->getKernel()->intranet->get("pdf_header_file_id") != 0) {
            $file = new FileHandler($this->context->getKernel(), $this->context->getKernel()->intranet->get("pdf_header_file_id"));
        } else {
            $file = NULL;
        }

        // gem debtoren som en fil i filsystemet
        $filehandler = new FileHandler($this->context->getKernel());
        $tmp_file = $filehandler->createTemporaryFile($translation->get($debtor->get("type")).$debtor->get('number').'.pdf');

        // Her gemmes filen
        $report = new Intraface_modules_debtor_Visitor_Pdf($translation, $file);
        $report->visit($debtor, $onlinepayment);
        $report->output('file', $tmp_file->getFilePath());

        // gem filen med filehandleren
        $filehandler = new FileHandler($this->context->getKernel());
        if (!$file_id = $filehandler->save($tmp_file->getFilePath(), $tmp_file->getFileName(), 'hidden', 'application/pdf')) {
            echo $filehandler->error->view();
            throw new Exception('Filen kunne ikke gemmes');
        }

        $input['accessibility'] = 'intranet';
        if (!$file_id = $filehandler->update($input)) {
            echo $filehandler->error->view();
            throw new Exception('Oplysninger om filen kunne ikke opdateres');

        }

        switch($this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
            case 'intranet':
                $from_email = '';
                $from_name = '';
                break;
            case 'user':
                $from_email = $this->context->getKernel()->user->getAddress()->get('email');
                $from_name = $this->context->getKernel()->user->getAddress()->get('name');
                break;
            case 'defined':
                $from_email = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender.email');
                $from_name = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender.name');
                break;
            default:
                throw new Exception("Invalid sender!");
                exit;
        }

        $signature = new Intraface_shared_email_Signature($this->context->getKernel()->user, $this->context->getKernel()->intranet, $this->context->getKernel()->getSetting());

        // opret e-mailen
        $email = new Email($this->context->getKernel());
        if (!$email->save(array(
                'contact_id' => $contact->get('id'),
                'subject' => $subject,
                'body' => $body . "\n\n" . $signature->getAsText(),
                'from_email' => $from_email,
                'from_name' => $from_name,
                'type_id' => 10, // electronic invoice
                'belong_to' => $debtor->get('id')
            ))) {
            echo $email->error->view();
            throw new Exception('E-mailen kunne ikke gemmes');
        }

        // tilknyt fil
        if (!$email->attachFile($file_id, $filehandler->get('file_name'))) {
            echo $email->error->view();
            throw new Exception('Filen kunne ikke vedhæftes');
        }

        switch ($this->query('send')) {
            case 'email':
                    $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'go');
                    $shared_email = $this->context->getKernel()->useModule('email');

                    // First vi set the last, because we need this id to the first.
                    $url = $redirect->setDestination($shared_email->getPath().$email->get('id') . '?edit', NET_SCHEME . NET_HOST . $this->url('../'));
                    $redirect->setIdentifier('send_email');
                    $redirect->askParameter('send_email_status');

                    return new k_SeeOther($url);
                break;
            case 'electronic_email':
                // Sender e-mailen
                if ($email->queue()) {
                    if ($debtor->get('status') == 'created') {
                        $debtor->setStatus('sent');
                    }
                    return new k_SeeOther($this->url('../'));

                } else {
                    echo $email->error->view();
                    throw new Exception('E-mailen kunne ikke sendes');
                }

                break;
            default:
                    throw new Exception('not valid send as');
                break;
        }
        throw new Exception('Something went wrong');
    }
}
