<?php
/**
 * Vi skal have den til at markere e-mailen som sendt, når den er sendt.
 */
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
                    $this->output .= "\n\nBetaling via homebanking\n+71< ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$this->context->getKernel()->setting->get('intranet', 'giro_account_number')."<";
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

class Intraface_modules_debtor_Controller_ReminderEmail extends k_Component
{
    function renderHtml()
    {
        $this->context->getKernel()->module('debtor');
        $this->context->getKernel()->useShared('email');

        $module_invoice = $this->context->getKernel()->useModule('invoice');
        $module_invoice->includeFile('Reminder.php');
        $module_invoice->includeFile('ReminderItem.php');

        $module_debtor = $this->context->getKernel()->useModule('debtor');

        $this->context->getKernel()->useModule('contact');
        $this->context->getKernel()->useModule('product');

        $reminder = new Reminder($this->context->getKernel(), intval($_REQUEST['id']));

        if ($reminder->contact->address->get("email") == '') {
          trigger_error('Kontaktpersonen har ikke nogen email', E_USER_ERROR);
          exit;
        }

        $subject  =	"Påmindelse om betaling";

        $reminder_text = new Reminder_Text();
        $reminder_text->visit($reminder);

        $body = $reminder_text->getText();

        switch($this->context->getKernel()->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                $from_email = '';
                $from_name = '';
                break;
            case 'user':
                $from_email = $this->context->getKernel()->user->getAddress()->get('email');
                $from_name = $this->context->getKernel()->user->getAddress()->get('name');
                break;
            case 'defined':
                $from_email = $this->context->getKernel()->setting->get('intranet', 'debtor.sender.email');
                $from_name = $this->context->getKernel()->setting->get('intranet', 'debtor.sender.name');
                break;
            default:
                trigger_error("Invalid sender!", E_USER_ERROR);
        }

        $email = new Email($this->context->getKernel());
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
            $redirect = new Intraface_Redirect($this->context->getKernel());
            $shared_email = $this->context->getKernel()->useShared('email');
            $url = $redirect->setDestination($shared_email->getPath().'email.php?id='.$id, NET_SCHME . NET_HOST . $this->context->url());
            $redirect->setIdentifier('send_email');
            $redirect->askParameter('send_email_status');
            return new k_SeeOther($url);
        }

    }
}