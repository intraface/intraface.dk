<?php
class Intraface_modules_contact_Controller_Show extends k_Component
{
    protected $registry;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function map($name)
    {
        if ($name == 'merge') {
            return 'Intraface_modules_contact_Controller_Merge';
        }
    }

    function renderHtml()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/show.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        $contact = new Contact($this->getKernel(), $this->name());
        $value = $contact->get();

        if ($value['type'] == "corporation") {
            $persons = $contact->contactperson->getList();
        }

        // The compare function has been removed from the class
        // $similar_contacts = $contact->compare();
        $similar_contacts = array();
        return $contact;
    }

    function getValues()
    {
        return $this->getContact()->get();
    }

    function getAddressValues()
    {
        return $this->getContact()->address->get();
    }

    function getDeliveryAddressValues()
    {
        return $this->getContact()->delivery_address->get();
    }

    function getContactModule()
    {
        return $contact_module = $this->getKernel()->module('contact');
    }

    function t($phrase)
    {
         return $phrase;
    }

    function getInvoice()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess("quotation")) {
                $quotation = new Debtor($this->getKernel(), 'quotation');
            }
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                $order = new Debtor($this->getKernel(), 'order');
            }
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                return $invoice = new Invoice($this->getKernel());
                $creditnote = new CreditNote($this->getKernel());
                $reminder = new Reminder($this->getKernel());
            }
        }
    }

    function getDebtorModule()
    {
        return $debtor = $this->getKernel()->useModule('debtor');
    }

    function getOrder()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess("quotation")) {
                $quotation = new Debtor($this->getKernel(), 'quotation');
            }
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                return $order = new Debtor($this->getKernel(), 'order');
            }
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                $invoice = new Invoice($this->getKernel());
                $creditnote = new CreditNote($this->getKernel());
                $reminder = new Reminder($this->getKernel());
            }
        }
    }

    function getCreditnote()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess("quotation")) {
                $quotation = new Debtor($this->getKernel(), 'quotation');
            }
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                $order = new Debtor($this->getKernel(), 'order');
            }
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                $invoice = new Invoice($this->getKernel());
                return $creditnote = new CreditNote($this->getKernel());
                $reminder = new Reminder($this->getKernel());
            }
        }
    }

    function getReminder()
    {
         if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess("quotation")) {
                $quotation = new Debtor($this->getKernel(), 'quotation');
            }
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                $order = new Debtor($this->getKernel(), 'order');
            }
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                $invoice = new Invoice($this->getKernel());
                $creditnote = new CreditNote($this->getKernel());
                return $reminder = new Reminder($this->getKernel());
            }
        }
    }

    function getQuotation()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess("quotation")) {
                return $quotation = new Debtor($this->getKernel(), 'quotation');
            }
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                $order = new Debtor($this->getKernel(), 'order');
            }
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                $invoice = new Invoice($this->getKernel());
                $creditnote = new CreditNote($this->getKernel());
                $reminder = new Reminder($this->getKernel());
            }
        }
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');

        $contact = new Contact($this->getKernel(), $this->name());

        if (!empty($_POST['send_email'])) {
            $contact->sendLoginEmail(Intraface_Mail::factory());
            return new k_SeeOther($this->url(null, array('flare' => 'Login e-mail has been sent')));

        } elseif (!empty($_POST['new_password'])) {
            if ($contact->generatePassword()) {
                return new k_SeeOther($this->url(null, array('flare' => 'New code has been generated')));
            }
        }
        return $this->render();
    }

    function renderVcard()
    {
        $this->getKernel()->module('contact');

        $contact = new Contact($this->getKernel(), (int)$this->name());


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
        $vcard->addEmail($contact->address->get('email'));
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
        header('Content-Disposition: attachment; filename='. $filename);
        header('Content-Description: VCard for ' . $contact->get('name'));
        //header("Content-Transfer-Encoding: binary');
        header('Connection: close');

        return $output;
    }
}