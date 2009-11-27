<?php
class Intraface_modules_debtor_Controller_Reminders extends k_Component
{
    protected $debtor;
    protected $contact;
    protected $reminder;

    function map($name)
    {
        return 'Intraface_modules_debtor_Controller_Reminder';
    }

    function getKernel()
    {
        return $this->context->getKernel();
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
        $reminder = $this->getReminder();

        if (isset($_GET["delete"])) {
        	$reminder = new Reminder($kernel, (int)$_GET["delete"]);
        	$reminder->delete();
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/reminders.tpl.php');

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
        $reminder = $this->getReminder();
        $contact = new Contact($this->getKernel(), $this->query('contact_id'));

        $value["dk_this_date"] = date("d-m-Y");
        $value["dk_due_date"] = date("d-m-Y", time()+3*24*60*60);

        if ($contact->address->get("name") != $contact->address->get("contactname")) {
            $value["attention_to"] = $contact->address->get("contactname");
        }

        //$value["text"] = $this->getKernel()->setting->get('intranet', 'reminder.first.text');
        $value["payment_method_key"] = 1;
        $value["number"] = $reminder->getMaxNumber();
        $smarty = new k_Template(dirname(__FILE__) . '/templates/reminder-edit.tpl.php');

        return $smarty->render($this, array('value' => $value));

    }

    function getReminders()
    {
        $reminder = $this->getReminder();
        $contact_id = $this->query('contact_id');

        if ($contact_id) {
        	$contact = new Contact($this->getKernel(), $contact_id);
        	$reminder->getDBQuery()->setFilter("contact_id", $contact->get("id"));
        }

        if (isset($_GET["search"])) {
        	if (isset($_GET["text"]) && $_GET["text"] != "") {
        		$reminder->getDBQuery()->setFilter("text", $_GET["text"]);
        	}

        	if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
        		$reminder->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
        	}

        	if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
        		$reminder->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
        	}

        	if (isset($_GET["status"])) {
        		$reminder->getDBQuery()->setFilter("status", $_GET["status"]);
        	}
        } else {
        	if ($reminder->getDBQuery()->checkFilter("contact_id")) {
                $reminder->getDBQuery()->setFilter("status", "-1");
            } else {
        		$reminder->getDBQuery()->setFilter("status", "-2");
        	}
        }

        $reminder->getDBQuery()->usePaging("paging");
        $reminder->getDBQuery()->storeResult("use_stored", "reminder", "toplevel");
        return $reminders = $reminder->getList();
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