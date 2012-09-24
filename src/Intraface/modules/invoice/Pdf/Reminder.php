<?php
/**
 * Invoice
 *
 * @package Intraface_Invoice
 * @author Sune Jensen <sj@sunet.dk>
 * @author Lars Olesen <lars@legestue.net>
 */
class Intraface_modules_invoice_Pdf_Reminder extends Intraface_modules_debtor_Pdf
{
    function __construct($translation, $file = null)
    {
        parent::__construct($translation, $file);
    }

    function visit($reminder)
    {
        if ($reminder->get('id') == 0) {
            throw new Exception("Reminder->pdf must be loaded to create a PDF");
        }

        $this->doc = $this->getDocument();

        if (!empty($this->file) AND $this->file->get('id') > 0) {
            $this->doc->addHeader($this->file->get('file_uri_pdf'));
        }

        $contact = $reminder->contact->address->get();
        if (isset($reminder->contact_person) AND get_class($reminder->contact_person) == "contactperson") {
            $contact["attention_to"] = $reminder->contact_person->get("name");
        }
        $contact['number'] = $reminder->contact->get('number');

        $intranet_address = new Intraface_Address($reminder->get("intranet_address_id"));
        $intranet = $intranet_address->get();

        switch($reminder->kernel->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                // void
                break;
            case 'user':
                $intranet['email'] = $reminder->kernel->user->getAddress()->get('email');
                $intranet['contact_person'] = $reminder->kernel->user->getAddress()->get('name');
                $intranet['phone'] = $reminder->kernel->user->getAddress()->get('phone');
                break;
            case 'defined':
                $intranet['email'] = $reminder->kernel->setting->get('intranet', 'debtor.sender.email');
                $intranet['contact_person'] = $reminder->kernel->setting->get('intranet', 'debtor.sender.name');
                break;
        }

        $this->docinfo[0]["label"] = "Dato:";
        $this->docinfo[0]["value"] = $reminder->get("dk_this_date");

        $this->addRecieverAndSender($contact , $intranet, "Reminder about payment", $this->docinfo);

        $this->doc->setY('-20'); // space to the product list

        $text = explode("\r\n", $reminder->get("text"));
        foreach ($text AS $line) {
            if ($line == "") {
                $this->doc->setY('-'.$this->doc->get('font_spacing'));

                if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                    $this->doc->nextPage(true);
                }
            } else {
                while($line != "") {

                    $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
                    $line = $this->doc->addTextWrap($this->doc->get('x'), $this->doc->get('y'), $this->doc->get("right_margin_position") - $this->doc->get('x'), $this->doc->get("font_size"), $line); // $this->doc->get("right_margin_position") - $this->doc->get('x')
              
                    $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

                    if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                        $this->doc->nextPage(true);
                    }
                }
            }
        }

        // Headlines for products

        $this->doc->setY('-20'); // space to product list

        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 3) {
            $this->doc->nextPage(true);
        }

        $apointX["text"] = $this->doc->get("margin_left");
        $apointX["invoice_date"] = $this->doc->get("right_margin_position") - 225;
        $apointX["due_date"] = $this->doc->get("right_margin_position") - 150;
        $apointX["amount"] = $this->doc->get("right_margin_position");
        $apointX["text_width"] = $this->doc->get("right_margin_position") - $this->doc->get("margin_left") - $apointX["text"] - 60;


        $this->doc->addText($apointX["text"], $this->doc->get('y'), $this->doc->get("font_size"), "Beskrivelse");
        $this->doc->addText($apointX["invoice_date"], $this->doc->get('y'), $this->doc->get("font_size"), "Dato");
        $this->doc->addText($apointX["due_date"], $this->doc->get('y'), $this->doc->get("font_size"), "Forfaldsdato");
        $this->doc->addText($apointX["amount"] - $this->doc->getTextWidth($this->doc->get("font_size"), "Beløb") -3, $this->doc->get('y'), $this->doc->get("font_size"), "Bel�b");

        $this->doc->setY('-'.($this->doc->get("font_spacing") - $this->doc->get("font_size")));

        $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

        // vareoversigt

        $reminder->loadItem();
        $items = $reminder->item->getList("invoice");

        $total = 0;
        $color = 0;

        for ($i = 0, $max = count($items); $i < $max; $i++) {

            if ($color == 1) {
                $this->doc->setColor(0.8, 0.8, 0.8);
                $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                $this->doc->setColor(0, 0, 0);
                $color = 0;
            } else {
                $color = 1;
            }

            $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));

            $this->doc->addText($apointX["text"], $this->doc->get('y'), $this->doc->get("font_size"), "Faktura nr. ".$items[$i]["number"]);
            $this->doc->addText($apointX["invoice_date"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["dk_this_date"]);
            $this->doc->addText($apointX["due_date"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["dk_due_date"]);
            $this->doc->addText($apointX["amount"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["arrears"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["arrears"], 2, ",", "."));
            $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            $total += $items[$i]["arrears"];

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                $this->doc->nextPage(true);
            }
        }

        $items = $reminder->item->getList("reminder");

        for ($i = 0, $max = count($items); $i < $max; $i++) {

            if ($color == 1) {
                $this->doc->setColor(0.8, 0.8, 0.8);
                $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                $this->doc->setColor(0, 0, 0);
                $color = 0;
            } else {
                $color = 1;
            }

            $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
            $this->doc->addText($apointX["text"], $this->doc->get('y'), $this->doc->get("font_size"), "Rykkkergebyr fra tidligere rykker");
            $this->doc->addText($apointX["invoice_date"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["dk_this_date"]);
            $this->doc->addText($apointX["due_date"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["dk_due_date"]);
            $this->doc->addText($apointX["amount"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", "."));
            $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            $total += $items[$i]["reminder_fee"];

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                $this->doc->nextPage(true);
            }
        }

        if ($reminder->get("reminder_fee") > 0) {

            if ($color == 1) {
                $this->doc->setColor(0.8, 0.8, 0.8);
                $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                $this->doc->setColor(0, 0, 0);
                $color = 0;
            } else {
                $color = 1;
            }

            $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
            $this->doc->addText($apointX["text"], $this->doc->get('y'), $this->doc->get("font_size"), "Rykkergebyr p�lagt denne rykker");
            $this->doc->addText($apointX["amount"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($reminder->get("reminder_fee"), 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($reminder->get("reminder_fee"), 2, ",", "."));
            $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            $total += $reminder->get("reminder_fee");

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                $this->doc->nextPage(true);
            }
        }

        $this->doc->setLineStyle(1);
        $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));
        $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
        $this->doc->addText($apointX["due_date"], $this->doc->get('y'), $this->doc->get("font_size"), "<b>Total:</b>");
        $this->doc->addText($apointX["amount"] - $this->doc->getTextWidth($this->doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>"), $this->doc->get('y'), $this->doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>");
        $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
        $this->doc->line($apointX["due_date"], $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

        $parameter = array(
            "contact" => $reminder->contact,
            "payment_text" => "Kontakt ".$reminder->contact->get("number"),
            "amount" => $total,
            "payment" => $reminder->get('payment_total'),
            "due_date" => $reminder->get("dk_due_date"),
            "girocode" => $reminder->get("girocode"));


        $this->addPaymentCondition($reminder->get("payment_method_key"), $parameter, $reminder->getPaymentInformation());

    }
}