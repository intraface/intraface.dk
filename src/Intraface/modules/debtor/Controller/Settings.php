<?php
/**
 * Her skal kunne indstilles hvilke betalingsformer der kan bruges i forbindelse
 * med debtor-modulet. Det skal altså være noget, man tilføjer.
 *
 * Dvs. man tilføjer fx girokontobetaling - og så skal man indtaste oplysninger om det
 * Tilføjer man bankoverførsel, skal man indtaste bankoplysninger
 * Der skal laves noget lidt smartere med læs-ind-bureau og elektronisk faktura
 * Tekst på rykkere skal måske differentieres, så der er standardtekster til forskellige rykkere
 *
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 *
 */
class Intraface_modules_debtor_Controller_Settings extends k_Component
{
    function renderHtml()
    {
        $debtor_module = $this->context->getKernel()->module('debtor');
        $this->context->getKernel()->useModule('invoice');

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                // would be better if the return were a post
                $this->context->getKernel()->setting->set('intranet', 'debtor.scan_in_contact', $redirect->getParameter('contact_id'));
            }
        }

        // find settings frem
        $values['debtor_sender'] = $this->context->getKernel()->setting->get('intranet', 'debtor.sender');
        $values['debtor_sender_email'] = $this->context->getKernel()->setting->get('intranet', 'debtor.sender.email');
        $values['debtor_sender_name'] = $this->context->getKernel()->setting->get('intranet', 'debtor.sender.name');
        $values['bank_name'] = $this->context->getKernel()->setting->get('intranet', 'bank_name');
        $values['bank_reg_number'] = $this->context->getKernel()->setting->get('intranet', 'bank_reg_number');
        $values['bank_account_number'] = $this->context->getKernel()->setting->get('intranet', 'bank_account_number');
        $values['giro_account_number'] = $this->context->getKernel()->setting->get('intranet', 'giro_account_number');
        $values['reminder_text'] = $this->context->getKernel()->setting->get('intranet', 'reminder.first.text');
        $values['invoice_text'] = $this->context->getKernel()->setting->get('intranet', 'debtor.invoice.text');
        $values['order_email_text'] = $this->context->getKernel()->setting->get('intranet', 'debtor.order.email.text');


        if ($this->context->getKernel()->setting->get('intranet', 'debtor.scan_in_contact') > 0) {
            $scan_in_contact = new Contact($this->context->getKernel(), $this->context->getKernel()->setting->get('intranet', 'debtor.scan_in_contact'));
        }


        $string = 'Det er gratis for små og mellemstore virksomheder at bruge Læs-ind bureauer. <a href="http://www.eogs.dk/sw7483.asp">Tjek her om det gælder for din virksomhed</a>.';
        $smarty = new k_Template(dirname(__FILE__) . '/templates/settings.tpl.php');
        return $smarty->render($this, array('string' => $string, 'values' => $values, 'kernel' => $this->context->getKernel()));
    }

    function postForm()
    {

        $debtor_module = $this->context->getKernel()->module('debtor');
        $this->context->getKernel()->useModule('invoice');

        if (!empty($_POST)) {


            $error = new Intraface_Error;
            $validator = new Intraface_Validator($error);

            if ($_POST['debtor_sender'] == 'defined') {
                $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail');
                $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name');
            }
            else {
                $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail', 'allow_empty');
                $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name', '', 'allow_empty');

            }

            if (!$error->isError()) {

                $this->context->getKernel()->setting->set('intranet', 'debtor.sender', $_POST['debtor_sender']);

                $this->context->getKernel()->setting->set('intranet', 'debtor.sender.email', $_POST['debtor_sender_email']);
                $this->context->getKernel()->setting->set('intranet', 'debtor.sender.name', $_POST['debtor_sender_name']);
            }

            // reminder
            $this->context->getKernel()->setting->set('intranet', 'reminder.first.text', $_POST['reminder_text']);
            $this->context->getKernel()->setting->set('intranet', 'debtor.invoice.text', $_POST['invoice_text']);
            $this->context->getKernel()->setting->set('intranet', 'debtor.order.email.text', $_POST['order_email_text']);


            // bank
            $this->context->getKernel()->setting->set('intranet', 'bank_name', $_POST['bank_name']);
            $this->context->getKernel()->setting->set('intranet', 'bank_reg_number', $_POST['bank_reg_number']);
            $this->context->getKernel()->setting->set('intranet', 'bank_account_number', $_POST['bank_account_number']);
            $this->context->getKernel()->setting->set('intranet', 'giro_account_number', $_POST['giro_account_number']);
        }

        if (!empty($_POST['delete_scan_in_contact'])) {
            $this->context->getKernel()->setting->set('intranet', 'debtor.scan_in_contact', 0);

            header('Location: setting.php');
            exit;
        }

        elseif (isset($_POST['add_scan_in_contact'])) {
            if ($this->context->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->context->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $debtor_module->getPath()."setting.php");
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                if ($this->context->getKernel()->setting->get('intranet', 'debtor.scan_in_contact') > 0) {
                    header("Location: ".$url . '?contact_id='.$this->context->getKernel()->setting->get('intranet', 'debtor.scan_in_contact'));
                }
                else {
                    header("Location: ".$url );
                }
                exit;
            }
            else {
                trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
            }
        }


        elseif (isset($_POST['edit_scan_in_contact'])) {
            if ($this->context->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->context->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."contact_edit.php?id=".intval($_POST['scan_in_contact']), $debtor_module->getPath()."setting.php");
                header("location: ".$url );
                exit;


            }
            else {
                trigger_error("Du har ikke adgang til modulet contact", E_ERROR_ERROR);
            }
        }

        if (!$error->isError()) {
            header('Location: index.php'); // Changed from setting.php, but don't know what is most right /SJ (14/1 2007)
            exit;
        }
        $values = $_POST;
    }

    function t($phrase)
    {
        return $phrase;
    }
}


