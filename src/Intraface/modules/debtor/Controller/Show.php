<?php
class Intraface_modules_debtor_Controller_Show extends k_Component
{
    protected $registry;
    protected $debtor;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getObject()
    {
        return $this->getDebtor();
    }

    function map($name)
    {
        if ($name == 'contact') {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        } elseif ($name == 'selectproduct') {
            return 'Intraface_modules_product_Controller_Selectproduct';
        } elseif ($name == 'selectproductvariation') {
            return 'Intraface_modules_product_Controller_Selectproductvariation';
        } elseif ($name == 'payment') {
            return 'Intraface_modules_debtor_Controller_Payments';
        } elseif ($name == 'depreciation') {
            return 'Intraface_modules_debtor_Controller_Depreciation';
        } elseif ($name == 'state') {
            if ($this->getType() == 'credit_note') {
                return 'Intraface_modules_accounting_Controller_State_Creditnote';
            } elseif ($this->getType() == 'invoice') {
                return 'Intraface_modules_accounting_Controller_State_Invoice';
            } else {
                throw new Exception('Cannot state type ' . $this->getType());
            }
        } elseif ($name == 'item') {
            return 'Intraface_modules_debtor_Controller_Items';
        } elseif ($name == 'onlinepayment') {
            return 'Intraface_modules_onlinepayment_Controller_Index';
        } elseif ($name == 'send') {
            return 'Intraface_modules_debtor_Controller_Send';
        }
    }

    function getModel()
    {
        return $this->getDebtor();
    }

    function getType()
    {
        return $this->getDebtor()->get('type');
    }

    function renderHtml()
    {
        $contact_module = $this->getKernel()->useModule('onlinepayment');
        $contact_module = $this->getKernel()->getModule('contact');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/show.tpl.php');
        return $smarty->render($this);
    }

    function getValues()
    {
        return $this->getDebtor()->get();
    }

    function getAction()
    {
        return 'Update';
    }

    function getContact()
    {
        return $this->getDebtor()->getContact();
    }

    function renderHtmlEdit()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->getModule('contact');

        // slet debtoren
        if (!empty($_POST['delete'])) {
            $type = $this->getDebtor()->get("type");
            $this->getDebtor()->delete();
            return new k_SeeOther($this->url('../', array('use_stored' => 'true')));
        } elseif (!empty($_POST['send_electronic_invoice'])) {
            return new k_SeeOther($this->url('send', array('send' => 'electronic_email')));
        } elseif (!empty($_POST['send_email'])) {
            return new k_SeeOther($this->url('send', array('send' => 'email')));
        }

        // annuller ordre tilbud eller order
        elseif (!empty($_POST['cancel']) AND ($this->getDebtor()->get("type") == "quotation" || $this->getDebtor()->get("type") == "order") && ($this->getDebtor()->get('status') == "created" || $this->getDebtor()->get('status') == "sent")) {
            $this->getDebtor()->setStatus('cancelled');
        }

        // sæt status til sendt
        elseif (!empty($_POST['sent'])) {
            $this->getDebtor()->setStatus('sent');

            if (($this->getDebtor()->get("type") == 'credit_note' || $this->getDebtor()->get("type") == 'invoice') AND $this->getKernel()->user->hasModuleAccess('accounting')) {
                header('location: state_'.$this->getDebtor()->get('type').'.php?id=' . intval($this->getDebtor()->get("id")));
            }
        }


        // Overføre tilbud til ordre
        elseif (!empty($_POST['order'])) {
            if ($this->getKernel()->user->hasModuleAccess('order') && $this->getDebtor()->get("type") == "quotation") {
                $this->getKernel()->useModule("order");
                $order = new Order($this->getKernel());
                if ($id = $order->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../'.$id));
                }
            }
        }

        // Overføre ordre til faktura
        elseif (!empty($_POST['invoice'])) {
            if ($this->getKernel()->user->hasModuleAccess('invoice') && ($this->getDebtor()->get("type") == "quotation" || $this->getDebtor()->get("type") == "order")) {
                $this->getKernel()->useModule("invoice");
                $invoice = new Invoice($this->getKernel());
                if ($id = $invoice->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../'.$id));
                }
            }
        }

        // Overfør til kreditnota
        elseif (!empty($_POST['credit_note'])) {
            if ($this->getKernel()->user->hasModuleAccess('invoice') && $this->getDebtor()->get("type") == "invoice") {
                $credit_note = new CreditNote($this->getKernel());

                if ($id = $credit_note->create($this->getDebtor())) {
                    return new k_SeeOther($this->url('../'.$id));
                }
            }
        }

        // cancel onlinepayment
        elseif (isset($_POST['onlinepayment_cancel']) && $this->getKernel()->user->hasModuleAccess('onlinepayment')) {
            $onlinepayment_module = $this->getKernel()->useModule('onlinepayment');
            $onlinepayment = OnlinePayment::factory($this->getKernel(), 'id', intval($_POST['onlinepayment_id']));

            $onlinepayment->setStatus('cancelled');
            $this->getDebtor()->load();
        }

        else {
            	$debtor = $this->getDebtor();
    	$contact = new Contact($this->getKernel(), $_POST["contact_id"]);

    	if (isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
    		$contact_person = new ContactPerson($contact);
    		$person["name"] = $_POST['contact_person_name'];
    		$person["email"] = $_POST['contact_person_email'];
    		$contact_person->save($person);
    		$contact_person->load();
    		$_POST["contact_person_id"] = $contact_person->get("id");
    	}

        if ($this->getKernel()->intranet->hasModuleAccess('currency') && !empty($_POST['currency_id'])) {
            $currency_module = $this->getKernel()->useModule('currency', false); // false = ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection(DB_DSN));
            $currency = $gateway->findById($_POST['currency_id']);
            if ($currency == false) {
                throw new Exception('Invalid currency');
            }

            $_POST['currency'] = $currency;
        }

    	if ($debtor->update($_POST)) {
    	    return new k_SeeOther($this->url(null));
    	}
        }

        return new k_SeeOther($this->url());
    }

    function GET()
    {
         if (isset($_GET["action"]) && $_GET["action"] == "send_onlinepaymentlink") {

            $shared_email = $this->getKernel()->useShared('email');
            if ($this->getDebtor()->getPaymentMethodKey() == 5 AND $this->getDebtor()->getWhereToId() == 0) {
                try {
                    // echo $this->getDebtor()->getWhereFromId();
                    /**
                     * @todo We should use a shop gateway here instead
                     */
                    $shop = Doctrine::getTable('Intraface_modules_shop_Shop')->findOneById($this->getDebtor()->getWhereFromId());
                    if ($shop) {
                        $payment_url = $this->getDebtor()->getPaymentLink($shop->getPaymentUrl());
                    }
                } catch (Doctrine_Record_Exeption $e) {
                    trigger_error('Could not send an e-mail with onlinepayment-link', E_USER_ERROR);
                }
            }

            if ($this->getKernel()->intranet->get("pdf_header_file_id") != 0) {
                $file = new FileHandler($this->getKernel(), $this->getKernel()->intranet->get("pdf_header_file_id"));
            } else {
                $file = NULL;
            }

            $body = 'Tak for din bestilling i vores onlineshop. Vi har ikke registreret nogen onlinebetaling sammen med bestillingen, hvilket kan skyldes flere ting.

    1) Du fortrudt bestillingen, da du skulle til at betale. I så fald må du meget gerne skrive tilbage og annullere din bestilling.
    2) Der er sket en fejl under betalingen. I det tilfælde må du gerne betale ved at gå ind på nedenstående link:

    ' .  $payment_url;
            $subject = 'Betaling ikke modtaget';

            // gem debtoren som en fil i filsystemet
            $filehandler = new FileHandler($this->getKernel());
            $tmp_file = $filehandler->createTemporaryFile(__($this->getDebtor()->get("type")).$this->getDebtor()->get('number').'.pdf');

            if (($this->getDebtor()->get("type") == "order" || $this->getDebtor()->get("type") == "invoice") && $this->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
                $this->getKernel()->useModule('onlinepayment', true); // true: ignore_user_access
                $onlinepayment = OnlinePayment::factory($this->getKernel());
            } else {
                $onlinepayment = NULL;
            }

            // Her gemmes filen
            $report = new Intraface_modules_debtor_Visitor_Pdf($translation, $file);
            $report->visit($this->getDebtor(), $onlinepayment);
            $report->output('file', $tmp_file->getFilePath());

            // gem filen med filehandleren
            $filehandler = new FileHandler($this->getKernel());
            if (!$file_id = $filehandler->save($tmp_file->getFilePath(), $tmp_file->getFileName(), 'hidden', 'application/pdf')) {
                echo $filehandler->error->view();
                trigger_error('Filen kunne ikke gemmes', E_USER_ERROR);
            }

            $input['accessibility'] = 'intranet';
            if (!$file_id = $filehandler->update($input)) {
                echo $filehandler->error->view();
                trigger_error('Oplysninger om filen kunne ikke opdateres', E_USER_ERROR);
            }

            switch($this->getKernel()->getSetting()->get('intranet', 'debtor.sender')) {
                case 'intranet':
                    $from_email = '';
                    $from_name = '';
                    break;
                case 'user':
                    $from_email = $this->getKernel()->user->getAddress()->get('email');
                    $from_name = $this->getKernel()->user->getAddress()->get('name');
                    break;
                case 'defined':
                    $from_email = $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.email');
                    $from_name = $this->getKernel()->getSetting()->get('intranet', 'debtor.sender.name');
                    break;
                default:
                    trigger_error("Invalid sender!", E_USER_ERROR);
                    exit;
            }
            $contact = new Contact($this->getKernel(), $this->getDebtor()->get('contact_id'));
            // opret e-mailen
            $email = new Email($this->getKernel());
            if (!$email->save(array(
                    'contact_id' => $contact->get('id'),
                    'subject' => $subject,
                    'body' => $body . "\n\n--\n" . $this->getKernel()->user->getAddress()->get('name') . "\n" . $this->getKernel()->intranet->get('name'),
                    'from_email' => $from_email,
                    'from_name' => $from_name,
                    'type_id' => 10, // electronic invoice
                    'belong_to' => $this->getDebtor()->get('id')
                ))) {
                echo $email->error->view();
                exit;
                trigger_error('E-mailen kunne ikke gemmes', E_USER_ERROR);
            }

            // tilknyt fil
            if (!$email->attachFile($file_id, $filehandler->get('file_name'))) {
                echo $email->error->view();
                trigger_error('Filen kunne ikke vedhæftes', E_USER_ERROR);
            }

            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $shared_email = $this->getKernel()->useShared('email');

            // First vi set the last, because we need this id to the first.
            $url = $redirect->setDestination($shared_email->getPath().'edit.php?id='.$email->get('id'), $debtor_module->getPath().'view.php?id='.$this->getDebtor()->get('id'));
            $redirect->setIdentifier('send_onlinepaymentlink');
            $redirect->askParameter('send_onlinepaymentlink_status');

            header('Location: ' . $url);
            exit;

        }


        // delete item
        if (isset($_GET["action"]) && $_GET["action"] == "delete_item") {
            $this->getDebtor()->loadItem(intval($_GET["item_id"]));
            $this->getDebtor()->item->delete();
            return new k_SeeOther($this->url(null, array('flare' => 'Item has been deleted')));
        }
        // move item
        if (isset($_GET['action']) && $_GET['action'] == "moveup") {
            $this->getDebtor()->loadItem(intval($_GET['item_id']));
            $this->getDebtor()->item->getPosition(MDB2::singleton(DB_DSN))->moveUp();
        }

        // move item
        if (isset($_GET['action']) && $_GET['action'] == "movedown") {
            $this->getDebtor()->loadItem(intval($_GET['item_id']));
            $this->getDebtor()->item->getPosition(MDB2::singleton(DB_DSN))->moveDown();
        }

        // registrere onlinepayment
        if ($this->getKernel()->user->hasModuleAccess('onlinepayment') && isset($_GET['onlinepayment_action']) && $_GET['onlinepayment_action'] != "") {
            if ($_GET['onlinepayment_action'] != 'capture' || ($this->getDebtor()->get("type") == "invoice" && $this->getDebtor()->get("status") == "sent")) {
                $onlinepayment_module = $this->getKernel()->useModule('onlinepayment'); // true: ignore user permisssion
                $onlinepayment = OnlinePayment::factory($this->getKernel(), 'id', intval($_GET['onlinepayment_id']));

                if (!$onlinepayment->transactionAction($_GET['onlinepayment_action'])) {
                    $onlinepayment_show_cancel_option = true;
                }

                $this->getDebtor()->load();

                // @todo vi skulle faktisk kun videre, hvis det ikke er
                // en tilbagebetaling eller hvad?
                if ($this->getDebtor()->get("type") == "invoice" && $this->getDebtor()->get("status") == "sent" AND !$onlinepayment->error->isError()) {
                    if ($this->getKernel()->user->hasModuleAccess('accounting')) {
                        header('location: state_payment.php?for=invoice&id=' . intval($this->getDebtor()->get("id")).'&payment_id='.$onlinepayment->get('created_payment_id'));
                        exit;
                    }
                }
            }
        }

        // edit contact
        if (isset($_GET['edit_contact'])) {
            $debtor_module = $this->getKernel()->module('debtor');
            $contact_module = $this->getKernel()->getModule('contact');
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination($contact_module->getPath().'contact_edit.php?id='.intval($this->getDebtor()->contact->get('id')), NET_SCHEME . NET_HOST . $this->url());
            return new k_SeeOther($url);
        }

        // Redirect til tilføj produkt
        if (isset($_GET['add_item'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $product_module = $this->getKernel()->useModule('product');
            $redirect->setIdentifier('add_item');

            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('selectproduct', array('set_quantity' => true)), NET_SCHEME . NET_HOST . $this->url());

            $redirect->askParameter('product_id', 'multiple');

            return new k_SeeOther($url);
        }


        // Return fra tilføj produkt og send email
        if (isset($_GET['return_redirect_id'])) {

            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');

            if ($return_redirect->get('identifier') == 'add_item') {
                $selected_products = $return_redirect->getParameter('product_id', 'with_extra_value');
                foreach ($selected_products as $product) {
                    $this->getDebtor()->loadItem();
                    $product['value'] = unserialize($product['value']);
                    $this->getDebtor()->item->save(array('product_id' => $product['value']['product_id'], 'product_variation_id' => $product['value']['product_variation_id'], 'quantity' => $product['extra_value'], 'description' => ''));
                }
                $return_redirect->delete();
                $this->getDebtor()->load();
            } elseif ($return_redirect->get('identifier') == 'send_email') {
                if ($return_redirect->getParameter('send_email_status') == 'sent' OR $return_redirect->getParameter('send_email_status') == 'outbox') {
                    $email_send_with_success = true;
                    // hvis faktura er genfremsendt skal den ikke sætte status igen
                    if ($this->getDebtor()->get('status') != 'sent') {
                        $this->getDebtor()->setStatus('sent');
                    }
                    $return_redirect->delete();

                    if (($this->getDebtor()->get("type") == 'credit_note' || $this->getDebtor()->get("type") == 'invoice') AND !$this->getDebtor()->isStated() AND $this->getKernel()->user->hasModuleAccess('accounting')) {
                        header('location: state_'.$this->getDebtor()->get('type').'.php?id=' . intval($this->getDebtor()->get("id")));
                    }
                }

            }
        }
         return parent::GET();
    }

    function addItem($product, $quantity)
    {
        $this->getDebtor()->loadItem();
        $this->getDebtor()->item->save(array('product_id' => $product['product_id'], 'product_variation_id' => $product['product_variation_id'], 'quantity' => $quantity, 'description' => ''));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
         return $phrase;
    }

    function getDebtor()
    {
        $contact_module = $this->getKernel()->getModule('contact');

        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());
        return $this->debtor = Debtor::factory($this->getKernel(), intval($this->name()));
    }

    function renderPdf()
    {
        if (($this->getDebtor()->get("type") == "order" || $this->getDebtor()->get("type") == "invoice") && $this->getKernel()->intranet->hasModuleAccess('onlinepayment')) {
            $this->getKernel()->useModule('onlinepayment', true); // true: ignore_user_access
            $onlinepayment = OnlinePayment::factory($this->getKernel());
        } else {
            $onlinepayment = NULL;
        }

        if ($this->getKernel()->intranet->get("pdf_header_file_id") != 0) {
            $this->getKernel()->useShared('filehandler');
            $filehandler = new FileHandler($this->getKernel(), $this->getKernel()->intranet->get("pdf_header_file_id"));
        } else {
            $filehandler = NULL;
        }

        $report = new Intraface_modules_debtor_Visitor_Pdf($this->registry->get('translation2'), $filehandler);
        $report->visit($this->getDebtor(), $onlinepayment);

        return $report->output('stream');
    }

    function renderHtmlDelete()
    {
        $this->getDebtor()->delete();
        return new k_SeeOther($this->url('../', array('use_stored' => true)));
    }
}