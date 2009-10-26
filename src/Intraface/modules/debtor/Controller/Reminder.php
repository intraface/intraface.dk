<?php
/**
 * @todo Test create reminder and pay is not finished
 */
class Intraface_modules_debtor_Controller_Reminder extends k_Component
{
    protected $registry;
    protected $debtor;

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getReminder()
    {
        return $reminder = new Reminder($this->getKernel(), intval($this->name()));
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("debtor");

        $translation = $this->getKernel()->getTranslation('debtor');

        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice->includeFile("Reminder.php");
        $mainInvoice->includeFile("ReminderItem.php");


        $reminder = new Reminder($this->getKernel(), intval($this->name()));
        if (isset($_GET['return_redirect_id'])) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');

            if ($return_redirect->get('identifier') == 'send_email') {
                if ($return_redirect->getParameter('send_email_status') == 'sent') {
                    $reminder->setStatus('sent');
                    $return_redirect->delete();

                    if ($this->getKernel()->user->hasModuleAccess('accounting') && $reminder->somethingToState()) {
                        header('location: state_reminder.php?id=' . intval($reminder->get("id")));
                        exit;
                    }

                }

            }
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/reminder.tpl.php');
        return $smarty->render($this);
    }

    function t($phrase)
    {
        return $phrase;
    }

    function renderHtmlEdit()
    {

$module = $this->getKernel()->module("debtor");

$translation = $this->getKernel()->getTranslation('debtor');

$mainInvoice = $this->getKernel()->useModule("invoice");
$mainInvoice->includeFile("Reminder.php");
$mainInvoice->includeFile("ReminderItem.php");

$mainCustomer = $this->getKernel()->useModule("contact");
$mainProduct = $this->getKernel()->useModule("product");

$checked_invoice = array();
$checked_reminder = array();

if (!empty($_POST)) {

    $reminder = new Reminder($this->getKernel(), intval($_POST["id"]));

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
            header("Location: reminder_email.php?id=".$reminder->get("id"));
            exit;
        } else {
            header("Location: reminder.php?id=".$reminder->get("id"));
            exit;
        }
    } else {
        if (intval($_POST["id"]) != 0) {
            $title = "Ret rykker";
        } else {
            $title = "Ny rykker";
        }

        $value = $_POST;

        $value["dk_this_date"] = $value["this_date"];
        $value["dk_due_date"] = $value["due_date"];

        $contact = new contact($this->getKernel(), $_POST["contact_id"]);

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
    }
} elseif (isset($_GET["id"])) {
    $title = "Ret rykker";
    $reminder = new Reminder($this->getKernel(), intval($_GET["id"]));
    $value = $reminder->get();
    $contact = new Contact($this->getKernel(), $reminder->get('contact_id'));

    $reminder->loadItem();
    $invoices = $reminder->item->getList("invoice");
    $reminders = $reminder->item->getList("reminder");

    for ($i = 0, $max = count($invoices); $i < $max; $i++) {
        $checked_invoice[] = $invoices[$i]["invoice_id"];
    }

    for ($i = 0, $max = count($reminders); $i < $max; $i++) {
        $checked_reminder[] = $reminders[$i]["reminder_id"];
    }
} else {
    $title = "Ny rykker";
    $reminder = new Reminder($this->getKernel());
    $contact = new Contact($this->getKernel(), $this->query('contact_id'));

    $value["dk_this_date"] = date("d-m-Y");
    $value["dk_due_date"] = date("d-m-Y", time()+3*24*60*60);
    /*
    if ($contact->address->get("name") != $contact->address->get("contactname")) {
        $value["attention_to"] = $contact->address->get("contactname");
    }
    */
    //$value["text"] = $this->getKernel()->setting->get('intranet', 'reminder.first.text');
    $value["payment_method_key"] = 1;
    $value["number"] = $reminder->getMaxNumber();
}

 $smarty = new k_Template(dirname(__FILE__) . '/templates/reminder-edit.tpl.php');
        return $smarty->render($this);
    }

    function renderPdf()
    {
        $reminder = new Reminder($this->getKernel(), intval($this->name()));
        return $reminder->pdf();
    }

    function postForm()
    {

        $module = $this->getKernel()->module("debtor");

        $translation = $this->getKernel()->getTranslation('debtor');

        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice->includeFile("Reminder.php");
        $mainInvoice->includeFile("ReminderItem.php");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $reminder = new Reminder($this->getKernel(), intval($_POST["id"]));

            // mark as sent
            if (!empty($_POST["mark_as_sent"])) {
                $reminder->setStatus("sent");

                if ($this->getKernel()->user->hasModuleAccess('accounting') && $reminder->somethingToState()) {
                    header('location: state_reminder.php?id=' . intval($reminder->get("id")));
                    exit;
                }
            }
        }

        if (!empty($_POST)) {

            $reminder = new Reminder($this->getKernel(), intval($_POST["id"]));

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
                    header("Location: reminder_email.php?id=".$reminder->get("id"));
                    exit;
                } else {
                    header("Location: reminder.php?id=".$reminder->get("id"));
                    exit;
                }
            } else {
                if (intval($_POST["id"]) != 0) {
                    $title = "Ret rykker";
                } else {
                    $title = "Ny rykker";
                }

                $value = $_POST;

                $value["dk_this_date"] = $value["this_date"];
                $value["dk_due_date"] = $value["due_date"];

                $contact = new contact($this->getKernel(), $_POST["contact_id"]);

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
            }
        }

        return new k_SeeOther($this->url(), array('flare' => 'Reminder has been updated'));
    }

    function getContact()
    {
        return $this->getReminder()->contact;
    }
}