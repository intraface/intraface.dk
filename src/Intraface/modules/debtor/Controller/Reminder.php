<?php
/**
 * @todo Test create reminder and pay is not finished
 */
class Intraface_modules_debtor_Controller_Reminder extends k_Component
{
    protected $debtor;

    function getFormUrl()
    {
        return $this->url();
    }

    function getType()
    {
        return 'reminder';
    }

    function getModel()
    {
        return $this->getReminder();
    }

    function map($name)
    {
        if ($name == 'payment') {
            return 'Intraface_modules_debtor_Controller_Payments';
        } elseif ($name == 'email') {
            return 'Intraface_modules_debtor_Controller_ReminderEmail';
        } elseif ($name == 'state') {
            return 'Intraface_modules_accounting_Controller_State_Reminder';
        } elseif ($name == 'depreciation') {
            return 'Intraface_modules_debtor_Controller_Depreciations';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getReminder()
    {
        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice = $this->getKernel()->useModule("contact");

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
                        return new k_SeeOther($this->url('state'));
                    }

                }

            }
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/reminder.tpl.php');
        return $smarty->render($this);
    }

    function renderHtmlEdit()
    {
        $mainInvoice = $this->getKernel()->useModule("invoice");
        $mainInvoice->includeFile("Reminder.php");
        $mainInvoice->includeFile("ReminderItem.php");

        $mainCustomer = $this->getKernel()->useModule("contact");
        $mainProduct = $this->getKernel()->useModule("product");

        $checked_invoice = array();
        $checked_reminder = array();
        /*
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
        */


        if (isset($_GET["id"])) {
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
                    return new k_SeeOther($this->url('state'));

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
                    return new k_SeeOther($this->url(null, array('email')));
                } else {
                    return new k_SeeOther($this->url());
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

    function renderHtmlDelete()
    {
        $this->getKernel()->module('debtor');
        $this->getKernel()->useShared('email');

        $module_invoice = $this->getKernel()->useModule('invoice');
        $module_invoice->includeFile('Reminder.php');
        $module_invoice->includeFile('ReminderItem.php');

        $module_debtor = $this->getKernel()->useModule('debtor');

        $this->getKernel()->useModule('contact');
        $this->getKernel()->useModule('product');

        $reminder = new Reminder($this->getKernel(), intval($this->name()));
        $reminder->delete();

        return new k_SeeOther($this->url('../', array('flare' => 'Reminder deleted')));
    }

    function renderHtmlEmail()
    {
        $this->getKernel()->module('debtor');
        $this->getKernel()->useShared('email');

        $module_invoice = $this->getKernel()->useModule('invoice');
        $module_invoice->includeFile('Reminder.php');
        $module_invoice->includeFile('ReminderItem.php');

        $module_debtor = $this->getKernel()->useModule('debtor');

        $this->getKernel()->useModule('contact');
        $this->getKernel()->useModule('product');

        $reminder = new Reminder($this->getKernel(), intval($this->name()));

        if ($reminder->contact->address->get("email") == '') {
            trigger_error('Kontaktpersonen har ikke nogen email', E_USER_ERROR);
            exit;
        }

        $subject  =	"Påmindelse om betaling";

        $reminder_text = new Reminder_Text();
        $reminder_text->visit($reminder);

        $body = $reminder_text->getText();

        switch($this->getKernel()->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                $from_email = '';
                $from_name = '';
                break;
            case 'user':
                $from_email = $this->getKernel()->user->getAddress()->get('email');
                $from_name = $this->getKernel()->user->getAddress()->get('name');
                break;
            case 'defined':
                $from_email = $this->getKernel()->setting->get('intranet', 'debtor.sender.email');
                $from_name = $this->getKernel()->setting->get('intranet', 'debtor.sender.name');
                break;
            default:
                trigger_error("Invalid sender!", E_USER_ERROR);
        }

        $email = new Email($this->getKernel());
        $var = array(
            'body' => $body,
            'subject' => $subject,
            'contact_id' => $reminder->contact->get('id'),
            'from_email' => $from_email,
            'from_name' => $from_name,
            'type_id' => 5, // type_id 5 er reminder
            'belong_to' => $reminder->get('id')
        );

        if ($id = $email->save($var)) {
            $redirect = new Intraface_Redirect($this->getKernel());
            $shared_email = $this->getKernel()->useShared('email');
            $url = $redirect->setDestination($shared_email->getPath().'email.php?id='.$id, NET_SCHEME . NET_HOST . $this->url());
            $redirect->setIdentifier('send_email');
            $redirect->askParameter('send_email_status');
            header('Location: ' . $url);
            exit;
        }
    }
}

class Reminder_Text {
    private $output;
    function __construct() {}
    function visit(Reminder $reminder) {
        $this->output .= "Dato: " . $reminder->get("dk_this_date") ."\n\n";
        $this->output .= $reminder->contact->address->get("name") . "\n";
        if ($reminder->get("attention_to") != "") {
            $this->output .= "Att.: ".$reminder->get("attention_to")."\n";
        }
        $this->output .= $reminder->contact->address->get("address") . "\n";
        $this->output .= $reminder->contact->address->get("postcode") . "  " .  $reminder->contact->address->get("city") ."\n\n";
        $this->output .= $reminder->get("text") . "\n\n";

        // Overskrifter - Vareudskrivning
        $this->output .= "Beskrivelse          Dato        Forfaldsdato    Beløb\n";
        // vareoversigt
        $reminder->loadItem();
        $items = $reminder->item->getList("invoice");
        $total = 0;
        for ($i = 0, $max = count($items); $i < $max; $i++) {
            $this->output .= "\nFak# ".$items[$i]["number"];
            $spaces = -strlen($items[$i]["number"]) - 5 + 20;
            for ($j = 0; $j < $spaces; $j++) { $this->output .= ' ';  }
                $this->output .= ' ' . $items[$i]["dk_this_date"];
                $this->output .= '  ' . $items[$i]["dk_due_date"];
                $this->output .= '      ' . number_format($items[$i]["arrears"], 2, ",", ".");
                $total += $items[$i]["arrears"];
            }
            $items = $reminder->item->getList("reminder");
            for ($i = 0, $max = count($items); $i < $max; $i++) {
                $this->output .= "\nTidl. rykkkergebyr  ";
                $this->output .= ' ' . $items[$i]["dk_this_date"];
                $this->output .= '  ' .$items[$i]["dk_due_date"];
                $this->output .= '      ' . number_format($items[$i]["reminder_fee"], 2, ",", ".");
                $total += $items[$i]["reminder_fee"];
            }
            if ($reminder->get("reminder_fee") != 0) {
                $this->output .= "\nRykkergebyr                                      ".number_format($reminder->get("reminder_fee"), 2, ",", ".");
                $total += $reminder->get("reminder_fee");
            }
            $this->output .= "\n\nTotal:                                           " . number_format($total, 2, ",", ".");

            $parameter = array(
                "contact" => $reminder->contact,
                "payment_text" => "Rykker #".$reminder->get("number"),
                "amount" => $total,
                "due_date" => $reminder->get("dk_due_date"),
                "girocode" => $reminder->get("girocode"));
            $this->output .= "\n\nDet skyldige beløb betales senest: " . $parameter['due_date'];

            // TODO: change to payment_method
            switch ($reminder->get('payment_method_key')) {
                case 1: // fall through - ingen valgt
                case 2: // kontooverførsel
                    $this->output .= "\n\nBetales på konto:";
                    $this->output .= "\nBank:                ".$reminder->kernel->setting->get('intranet', 'bank_name');
                    $this->output .= "\nRegnr.:              ".$reminder->kernel->setting->get('intranet', 'bank_reg_number');
                    $this->output .= "\nKontonr.:            ".$reminder->kernel->setting->get('intranet', 'bank_account_number');
                    $this->output .= "\nBesked til modtager: " . "Kunde #" . $reminder->contact->get("number");
                break;
                case 3:
                    $this->output .= "\n\nBetaling via homebanking\n+71< ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$kernel->setting->get('intranet', 'giro_account_number')."<";
                break;
            }

            $this->output .= "\n\nMed venlig hilsen\n\n" . $reminder->kernel->user->getAddress()->get("name") . "\n" .$reminder->kernel->intranet->get("name");
            $this->output .= "\n" . $reminder->kernel->intranet->address->get("address");
            $this->output .= "\n" . $reminder->kernel->intranet->address->get("postcode") . "  " . $reminder->kernel->intranet->address->get("city");
    }

    public function getText() {
        return $this->output;
    }

}