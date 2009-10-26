<?php
class Intraface_modules_debtor_Controller_Reminders extends k_Component
{
    protected $registry;
    protected $debtor;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

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
        $kernel = $this->getKernel();
        $module = $kernel->module("debtor");

        $mainInvoice = $kernel->useModule("invoice");
        $translation = $kernel->getTranslation('debtor');
        $reminder = new Reminder($this->getKernel());
        return $reminder;
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

    function t($phrase)
    {
        return $phrase;
    }
}