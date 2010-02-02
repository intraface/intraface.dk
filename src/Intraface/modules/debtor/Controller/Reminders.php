<?php
class Intraface_modules_debtor_Controller_Reminders extends k_Component
{
    protected $debtor;
    protected $contact;
    protected $reminder;
    protected $template;
    protected $gateway;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Reminder';
    }
    /*
    function dispatch()
    {
        $this->url_state->set('contact_id', $this->query('contact_id'));

        return parent::dispatch();
    }
	*/
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getGateway()
    {
        if (is_object($this->gateway)) {
            return $this->gateway;
        }

        return ($this->gateway = new Intraface_modules_invoice_ReminderGateway($this->getKernel()));
    }

    function getReminder()
    {
        $mainInvoice = $this->getKernel()->useModule("invoice");

        if (is_object($this->reminder)) {
            return $this->reminder;
        }
        $this->reminder = new Reminder($this->getKernel());
        return $this->reminder;
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $contact_id = $this->query('contact_id');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/reminders');
        return $smarty->render($this);
    }

    function getContact()
    {
        if (is_object($this->contact)) {
            return $this->contact;
        }
        return $this->contact = new Contact($this->getKernel(), $this->query('contact_id'));
    }

    function renderHtmlCreate()
    {
        $title = "Ny rykker";
        $contact = new Contact($this->getKernel(), $this->query('contact_id'));

        $value["dk_this_date"] = date("d-m-Y");
        $value["dk_due_date"] = date("d-m-Y", time()+3*24*60*60);

        if ($contact->address->get("name") != $contact->address->get("contactname")) {
            $value["attention_to"] = $contact->address->get("contactname");
        }

        $value["text"] = $this->getKernel()->getSetting()->get('intranet', 'reminder.first.text');
        $value["payment_method_key"] = 1;
        $value["number"] = $this->getGateway()->getMaxNumber() + 1;

        $data = array('value' => $value);

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/reminder-edit');
        return $smarty->render($this, $data);
    }

    function getReminders()
    {
        $contact_id = $this->query('contact_id');

        if ($contact_id) {
        	$contact = new Contact($this->getKernel(), $contact_id);
        	$this->getGateway()->getDBQuery()->setFilter("contact_id", $contact->get("id"));
        }

        if (isset($_GET["search"])) {
        	if (isset($_GET["text"]) && $_GET["text"] != "") {
        		$this->getGateway()->getDBQuery()->setFilter("text", $_GET["text"]);
        	}

        	if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
        		$this->getGateway()->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
        	}

        	if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
        		$this->getGateway()->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
        	}

        	if (isset($_GET["status"])) {
        		$this->getGateway()->getDBQuery()->setFilter("status", $_GET["status"]);
        	}
        } else {
        	if ($this->getGateway()->getDBQuery()->checkFilter("contact_id")) {
                $this->getGateway()->getDBQuery()->setFilter("status", "-1");
            } else {
        		$this->getGateway()->getDBQuery()->setFilter("status", "-2");
        	}
        }

        $this->getGateway()->getDBQuery()->usePaging("paging");
        $this->getGateway()->getDBQuery()->storeResult("use_stored", "reminder", "toplevel");
        return $this->getGateway()->findAll();
    }

    function postForm()
    {
        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice->includeFile("Reminder.php");
        $mainInvoice->includeFile("ReminderItem.php");

        $reminder = $this->getReminder();

        if (isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
            $this->contact = new Contact($this->getKernel(), $_POST["contact_id"]);
            $contact_person = new ContactPerson($contact);
            $person["name"] = $_POST['contact_person_name'];
            $person["email"] = $_POST['contact_person_email'];
            $contact_person->save($person);
            $contact_person->load();
            $_POST["contact_person_id"] = $contact_person->get("id");
        }

        if ($reminder->save($_POST)) {
             if ($_POST['send_as'] == 'email') {
                 return new k_SeeOther($this->url($reminder->get("id"), array('email', 'flare' => 'Reminder has been created')));
             } else {
                 return new k_SeeOther($this->url($reminder->get("id"), array('flare' => 'Reminder has been created')));
             }
        }

        $value = $_POST;

        $value["dk_this_date"] = $value["this_date"];
        $value["dk_due_date"] = $value["due_date"];

        $this->contact = new Contact($this->getKernel(), $_POST["contact_id"]);

        if (isset($value["checked_invoice"]) && is_array($value["checked_invoice"])) {
            $checked_invoice = $value["checked_invoice"];
        } else {
            $checked_invoice = array();
        }

        if (isset($value["checked_reminder"]) && is_array($value["checked_reminder"])) {
            $checked_reminder = $value["checked_reminder"];
        } else {
            $checked_reminder = array();
        }

        return $this->render();
    }

    /*
    function _postForm()
    {
        $module = $this->getKernel()->module("debtor");

        $translation = $this->getKernel()->getTranslation('debtor');

        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice->includeFile("Reminder.php");
        $mainInvoice->includeFile("ReminderItem.php");

        $reminder = new Reminder($this->getKernel());

        if (isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
            $contact = new Contact($this->getKernel(), $_POST["contact_id"]);
            $contact_person = new ContactPerson($contact);
            $person["name"] = $_POST['contact_person_name'];
            $person["email"] = $_POST['contact_person_email'];
            $contact_person->save($person);
            $contact_person->load();
            $_POST["contact_person_id"] = $contact_person->get("id");
        }

        if ($reminder->save($_POST)) {

            if ($_POST['send_as'] == 'email') {
                return new k_SeeOther($this->url($reminder->get('id')), array('email', 'flare' => 'Reminder has been updated'));
            } else {
                return new k_SeeOther($this->url($reminder->get('id')), array('flare' => 'Reminder has been updated'));
            }
        }
        return $this->render();

    }
    */
}