<?php
class Intraface_modules_contact_Controller_Show extends k_Component
{
    protected $template;
    protected $contact;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getContact()->getId() == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function map($name)
    {
        if ($name == 'merge') {
            return 'Intraface_modules_contact_Controller_Merge';
        } elseif ($name == 'memo') {
            return 'Intraface_modules_contact_Controller_Memos';
        } elseif ($name == 'contactperson') {
            return 'Intraface_modules_contact_Controller_Contactpersons';
        } elseif ($name == 'keyword') {
            return 'Intraface_Keyword_Controller_Index';
        }
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/show');
        return $smarty->render($this, array('persons' => $this->getContactPersons()));
    }

    function renderHtmlEdit()
    {
        $this->document->addScript('contact/contact_edit.js');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
        $contact = $this->getContact();

        if (!empty($_POST['eniro']) AND !empty($_POST['eniro_phone'])) {

            $eniro = new Services_Eniro();
            $value = $_POST;

            if ($oplysninger = $eniro->query('telefon', $_POST['eniro_phone'])) {
                $address['name'] = $oplysninger['navn'];
                $address['address'] = $oplysninger['adresse'];
                $address['postcode'] = $oplysninger['postnr'];
                $address['city'] = $oplysninger['postby'];
                $address['phone'] = $_POST['eniro_phone'];
            }
        }

        // checking if similiar contacts exists
        if (!empty($similar_contacts) AND count($similar_contacts) > 0 AND empty($_POST['force_save'])) {
        } elseif ($id = $contact->save($_POST)) {

            // $redirect->addQueryString('contact_id='.$id);
            if ($redirect->get('id') != 0) {
                $redirect->setParameter('contact_id', $id);
            }
            return new k_SeeOther($redirect->getRedirect($this->url()));
        }

        $value = $_POST;
        $address = $_POST;
        $delivery_address = array();
        $delivery_address['name'] = $_POST['delivery_name'];
        $delivery_address['address'] = $_POST['delivery_address'];
        $delivery_address['postcode'] = $_POST['delivery_postcode'];
        $delivery_address['city'] = $_POST['delivery_city'];
        $delivery_address['country'] = $_POST['delivery_country'];

        return $this->render();
    }

    function getRedirect()
    {
        return Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        if (is_object($this->contact)) {
            return $this->contact;
        }
        return ($this->contact = $this->context->getGateway()->findById($this->name()));
    }

    function getModel()
    {
        return $this->getContact();
    }

    function getContactPersons()
    {
        if ($this->getContact()->get('type') == "corporation") {
            return $persons = $this->getContact()->contactperson->getList();
        }
        return array();
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

    /*
    function getInvoice()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                return $invoice = new Invoice($this->getKernel());
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
            if ($this->getKernel()->user->hasModuleAccess('order')) {
                $this->getKernel()->useModule('order');
                return $order = new Debtor($this->getKernel(), 'order');
            }
        }
    }

    function getCreditnote()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
                return $creditnote = new CreditNote($this->getKernel());
            }
        }
    }

    function getReminder()
    {
         if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $debtor = $this->getKernel()->useModule('debtor');
            if ($this->getKernel()->user->hasModuleAccess('invoice')) {
                $this->getKernel()->useModule('invoice');
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
        }
    }
    */

    function putForm()
    {
        $contact_module = $this->getKernel()->module("contact");

        if (!empty($_POST['send_email'])) {
            // opretter en kode, hvis kunden ikke har en kode
            if (!$this->getContact()->get('password')) {
                $this->getContact()->generatePassword();
            }
            if (!$this->getContact()->get('code')) {
                $this->getContact()->generateCode();
            }

            $this->getKernel()->useShared('email');
            $email = new Email($this->getKernel());
            if (!$email->save(
                array(
                    'subject' => 'Loginoplysninger',
                    'body' => $this->getKernel()->setting->get('intranet', 'contact.login_email_text') . "\n\n" . $this->getContact()->getLoginUrl() . "\n\nMed venlig hilsen\nEn venlig e-mail-robot\n" . $this->getKernel()->intranet->get('name'),
                    'contact_id' => $this->getContact()->get('id'),
                    'from_email' => $this->getKernel()->intranet->address->get('email'),
                    'from_name' => $this->getKernel()->intranet->get('name'),
                    'type_id' => 9,
                    'belong_to' => $this->getContact()->get('id')
            )
            )) {
                return new k_SeeOther($this->url(null, array('flare' => 'Kunne ikke gemme e-mailen')));
            }

            if ($email->queue()) {
                return new k_SeeOther($this->url(null, array('flare' => 'Login e-mail has been queued')));

            }
            return new k_SeeOther($this->url(null, array('flare' => 'Could not queue the email')));

        } elseif (!empty($_POST['new_password'])) {
            if ($this->getContact()->generatePassword()) {
                return new k_SeeOther($this->url(null, array('flare' => 'New code has been generated')));
            }
        }
        return $this->render();
    }

    function renderVcard()
    {
        $contact = $this->getContact();

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

    function getDependencies()
    {
        $dependencies = array();

        if ($this->getKernel()->user->hasModuleAccess("quotation")):

        $dependencies['quotation'] = array(
            'gateway' => new Intraface_modules_quotation_QuotationGateway($this->getKernel()),
            'url' =>  $this->url('../../debtor/quotation', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  $this->url('../../debtor/quotation/create', array('contact_id' => $this->getContact()->get("id"))),
        	'label' => 'quotation'
        );

        endif;

        if ($this->getKernel()->user->hasModuleAccess("order")):

        $dependencies['order'] = array(
            'gateway' => new Intraface_modules_order_OrderGateway($this->getKernel()),
            'url' =>  $this->url('../../debtor/order', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  $this->url('../../debtor/order/create', array('contact_id' => $this->getContact()->get("id"))),
        	'label' => 'order'
        );

        endif;

        if ($this->getKernel()->user->hasModuleAccess("invoice")):

        $dependencies['invoice'] = array(
            'gateway' => new Intraface_modules_invoice_InvoiceGateway($this->getKernel()),
            'url' =>  $this->url('../../debtor/invoice', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  $this->url('../../debtor/invoice/create', array('contact_id' => $this->getContact()->get("id"))),
        	'label' => 'invoice'
        );


        $dependencies['creditnote'] = array(
            'gateway' => new Intraface_modules_invoice_CreditnoteGateway($this->getKernel()),
            'url' =>  $this->url('../../debtor/credit_note', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  '',
        	'label' => 'creditnote'
        );

        $dependencies['reminder'] = array(
            'gateway' => new Intraface_modules_invoice_ReminderGateway($this->getKernel()),
            'url' =>  $this->url('../../debtor/reminder', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  '',
        	'label' => 'reminder'
        );

        endif;

        if ($this->getKernel()->user->hasModuleAccess("newsletter")):

        $dependencies['newsletter'] = array(
            'gateway' => new Intraface_modules_newsletter_SubscribersGateway($this->getKernel()),
            'url' =>  $this->url('../../newsletter', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  '',
        	'label' => 'newsletter'
        );

        endif;


        if ($this->getKernel()->user->hasModuleAccess("procurement")):

        $dependencies['procurement'] = array(
            'gateway' => new Intraface_modules_procurement_ProcurementGateway($this->getKernel()),
            'url' =>  $this->url('../../procurement', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  '',
        	'label' => 'procurement'
        );

        endif;

        $dependencies['email'] = array(
            'gateway' => new Intraface_shared_email_EmailGateway($this->getKernel()),
            'url' =>  $this->url('../../email', array('contact_id' => $this->getContact()->get("id"))),
            'url_create' =>  '',
        	'label' => 'email'
        );

        return $dependencies;
    }

    function getObject()
    {
        return $this->getContact();
    }
}