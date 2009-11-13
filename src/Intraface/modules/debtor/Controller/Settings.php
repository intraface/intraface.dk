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
    protected $error;

    function renderHtml()
    {
        $this->context->getKernel()->useModule('invoice');

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                // would be better if the return were a post
                $this->context->getKernel()->getSetting()->set('intranet', 'debtor.scan_in_contact', $redirect->getParameter('contact_id'));
            }
        }

        if ($this->context->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact') > 0) {
            $scan_in_contact = new Contact($this->context->getKernel(), $this->context->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact'));
        }

        $string = 'Det er gratis for små og mellemstore virksomheder at bruge Læs-ind bureauer. <a href="http://www.eogs.dk/sw7483.asp">Tjek her om det gælder for din virksomhed</a>.';
        $smarty = new k_Template(dirname(__FILE__) . '/templates/settings.tpl.php');
        return $smarty->render($this, array('string' => $string, 'values' => $values, 'kernel' => $this->context->getKernel()));
    }

    function getError()
    {
        if (is_object($this->error)) {
            return $this->error;
        }

        return $this->error = new Intraface_Error();
    }

    function postForm()
    {
        $this->context->getKernel()->useModule('invoice');

        if (!empty($_POST)) {

            $error = $this->getError();
            $validator = new Intraface_Validator($error);

            if ($_POST['debtor_sender'] == 'defined') {
                $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail');
                $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name');
            } else {
                $validator->isEmail($_POST['debtor_sender_email'], 'Invalid e-mail in Sender e-mail', 'allow_empty');
                $validator->isString($_POST['debtor_sender_name'], 'Error in Sender name', '', 'allow_empty');
            }

            if (!$error->isError()) {
                $this->context->getKernel()->getSetting()->set('intranet', 'debtor.sender', $_POST['debtor_sender']);
                $this->context->getKernel()->getSetting()->set('intranet', 'debtor.sender.email', $_POST['debtor_sender_email']);
                $this->context->getKernel()->getSetting()->set('intranet', 'debtor.sender.name', $_POST['debtor_sender_name']);
            }

            // reminder
            $this->context->getKernel()->getSetting()->set('intranet', 'reminder.first.text', $_POST['reminder_text']);
            $this->context->getKernel()->getSetting()->set('intranet', 'debtor.invoice.text', $_POST['invoice_text']);
            $this->context->getKernel()->getSetting()->set('intranet', 'debtor.order.email.text', $_POST['order_email_text']);

            // bank
            $this->context->getKernel()->getSetting()->set('intranet', 'bank_name', $_POST['bank_name']);
            $this->context->getKernel()->getSetting()->set('intranet', 'bank_reg_number', $_POST['bank_reg_number']);
            $this->context->getKernel()->getSetting()->set('intranet', 'bank_account_number', $_POST['bank_account_number']);
            $this->context->getKernel()->getSetting()->set('intranet', 'giro_account_number', $_POST['giro_account_number']);
        }

        if (!empty($_POST['delete_scan_in_contact'])) {
            $this->context->getKernel()->getSetting()->set('intranet', 'debtor.scan_in_contact', 0);
            return new k_SeeOther($this->url());
        } elseif (isset($_POST['add_scan_in_contact'])) {
            if ($this->context->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->context->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $debtor_module->getPath()."setting.php");
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                if ($this->context->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact') > 0) {
                    return new k_SeeOther(url($url, array('contact_id' => $this->context->getKernel()->getSetting()->get('intranet', 'debtor.scan_in_contact'))));
                } else {
                    return new k_SeeOther($url);
                }
            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        } elseif (isset($_POST['edit_scan_in_contact'])) {
            if ($this->context->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->context->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->context->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."contact_edit.php?id=".intval($_POST['scan_in_contact']), $debtor_module->getPath()."setting.php");

                return new k_SeeOther($url);

            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        }

        if (!$error->isError()) {
            return new k_SeeOther($this->url('../'));
        }
        $values = $_POST;
        return $this->render();
    }

    function getValues()
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        // find settings frem
        $values['debtor_sender'] = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender');
        $values['debtor_sender_email'] = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender.email');
        $values['debtor_sender_name'] = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.sender.name');
        $values['bank_name'] = $this->context->getKernel()->getSetting()->get('intranet', 'bank_name');
        $values['bank_reg_number'] = $this->context->getKernel()->getSetting()->get('intranet', 'bank_reg_number');
        $values['bank_account_number'] = $this->context->getKernel()->getSetting()->get('intranet', 'bank_account_number');
        $values['giro_account_number'] = $this->context->getKernel()->getSetting()->get('intranet', 'giro_account_number');
        $values['reminder_text'] = $this->context->getKernel()->getSetting()->get('intranet', 'reminder.first.text');
        $values['invoice_text'] = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.invoice.text');
        $values['order_email_text'] = $this->context->getKernel()->getSetting()->get('intranet', 'debtor.order.email.text');
        return $values;
    }

    function t($phrase)
    {
        return $phrase;
    }
}
