<?php
/**
 * Creates a pdf of a debtor. The class implements the visitor pattern.
 *
 * The debtor must comply with a certain interface.
 *
 * PHP version 5
 *
 * TODO Put in the doc instead of having it started up.
 *
 * <code>
 * $file = new FileHandler($file_id);
 * $report = new Debtor_Report_Pdf($file);
 * $report->visit($debtor);
 * </code>
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

class Debtor_Report_Pdf
{
    protected $file;
    protected $translation;
    protected $doc;

    /**
     * Constructor
     *
     * @param object $translation Used to do the translation in the class
     * @param object $file        File to use for the header
     *
     * @return void
     */
    function __construct($translation, $file = null)
    {
        $this->translation = $translation;
        $this->file = $file;
    }

    /**
     * Creates the document to write
     *
     * @return PdfMaker object
     */
    protected function createDocument()
    {
        require_once 'Intraface/shared/pdf/PdfMaker.php';
        //require_once 'Intraface/shared/pdf/PdfMakerDebtor.php';

        // todo - mon ikke alt fra pdfmakerdebtor kan flyttes hertil?
        $doc = new PdfMaker(/*$debtor->kernel*/);
        //$doc->start();
        return $doc;

    }

    /**
     * Visitor for the debtor
     *
     * @param object $debtor The debtor to be written
     *
     * @return void
     */
    function visit($debtor)
    {

        //$shared_pdf = $debtor->kernel->useShared('pdf');
        //$shared_pdf->includeFile('PdfMakerDebtor.php');

        $this->doc = $this->createDocument();

        if (!empty($this->file) AND $this->file->get('id') > 0) {
            $this->doc->addHeader($this->file->get('file_uri_pdf'));
        }

        $this->doc->setY('-5');

        $contact = $debtor->contact->address->get();
        if (strtolower(get_class($debtor->contact_person)) == "contactperson") {
            $contact["attention_to"] = $debtor->contact_person->get("name");
        }
        $contact['number'] = $debtor->contact->get('number');

        $intranet_address = $debtor->getIntranetAddress();
        // $intranet_address = new Address($debtor->get("intranet_address_id"));
        $intranet = $intranet_address->get();

        $intranet = array_merge($intranet, $debtor->getContactInformation());

        /*
        switch($debtor->kernel->setting->get('intranet', 'debtor.sender')) {
            case 'intranet':
                // void
                break;
            case 'user':
                $intranet['email'] = $debtor->kernel->user->address->get('email');
                $intranet['contact_person'] = $debtor->kernel->user->address->get('name');
                $intranet['phone'] = $debtor->kernel->user->address->get('phone');
                break;
            case 'defined':
                $intranet['email'] = $debtor->kernel->setting->get('intranet', 'debtor.sender.email');
                $intranet['contact_person'] = $debtor->kernel->setting->get('intranet', 'debtor.sender.name');
                break;
        }
        */

        $this->docinfo[0]["label"] = $this->translation->get($debtor->get('type').' number').":";
        $this->docinfo[0]["value"] = $debtor->get("number");
        $this->docinfo[1]["label"] = "Dato:";
        $this->docinfo[1]["value"] = $debtor->get("dk_this_date");
        if ($debtor->get("type") != "credit_note" && $debtor->get("due_date") != "0000-00-00") {
            $this->docinfo[2]["label"] = $this->translation->get($debtor->get('type').' due date').":";
            $this->docinfo[2]["value"] = $debtor->get("dk_due_date");
        }

        $this->addRecieverAndSender($contact, $intranet, $this->translation->get($debtor->get('type').' title'), $this->docinfo);

        $this->doc->setY('-'.$this->doc->get("font_spacing"));

        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
            $this->doc->nextPage(true);
        }

        if ($debtor->get('message')) {
            $text = explode("\r\n", $debtor->get('message'));
            foreach ($text AS $line) {
                if ($line == "") {
                    $this->doc->setY('-'.$this->doc->get("font_spacing"));
                    if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                        $this->doc->nextPage(true);
                    }
                } else {
                    while ($line != "") {
                        $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
                        $line = $this->doc->addTextWrap($this->doc->get('margin_left'), $this->doc->get('y'), $this->doc->get('content_width'), $this->doc->get("font_size"), $line);
                        $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
                        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                            $this->doc->nextPage(true);
                        }
                    }
                }
            }
        }

        // Overskrifter - Vareudskrivning
        $this->doc->setY('-40'); // mellemrum til vareoversigt

        $apointX["varenr"] = 80;
        $apointX["tekst"] = 90;
        $apointX["antal"] = $this->doc->get("right_margin_position") - 150;
        $apointX["enhed"] = $this->doc->get('right_margin_position') - 145;
        $apointX["pris"] = $this->doc->get('right_margin_position') - 60;
        $apointX["beloeb"] = $this->doc->get('right_margin_position');
        $apointX["tekst_width"] = $this->doc->get('right_margin_position') - $this->doc->get("margin_left") - $apointX["tekst"] - 60;
        $apointX["tekst_width_small"] = $apointX["antal"] - $this->doc->get("margin_left") - $apointX["tekst"];


        $this->doc->addText($apointX["varenr"] - $this->doc->getTextWidth($this->doc->get("font_size"), "Varenr."), $this->doc->get('y'), $this->doc->get("font_size"), "Varenr.");
        $this->doc->addText($apointX["tekst"], $this->doc->get('y'), $this->doc->get("font_size"), "Tekst");
        $this->doc->addText($apointX["antal"] - $this->doc->getTextWidth($this->doc->get("font_size"), "Antal"), $this->doc->get('y'), $this->doc->get("font_size"), "Antal");
        // $this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), "Enhed");
        $this->doc->addText($apointX["pris"] - $this->doc->getTextWidth($this->doc->get("font_size"), "Pris"), $this->doc->get('y'), $this->doc->get("font_size"), "Pris");
        $this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), "Beløb") -3, $this->doc->get('y'), $this->doc->get("font_size"), "Beløb");

        $this->doc->setY('-'.($this->doc->get("font_spacing") - $this->doc->get("font_size")));

        $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));

        // vareoversigt
        $items = $debtor->getItems();

        $total = 0;
        if (isset($items[0]["vat"])) {
            $vat = $items[0]["vat"];
        } else {
            $vat = 0;
        }
        // $line_padding = 4;
        // $line_height = $this->doc->get("font_size") + $line_padding * 2;
        $bg_color = 0;

        for ($i = 0, $max = count($items); $i <  $max; $i++) {
            $vat = $items[$i]["vat"];

            if ($bg_color == 1) {
                $this->doc->setColor(0.8, 0.8, 0.8);
                $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                $this->doc->setColor(0, 0, 0);
            }

            $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
            $this->doc->addText($apointX["varenr"] - $this->doc->getTextWidth($this->doc->get("font_size"), $items[$i]["number"]), $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["number"]);
            if ($items[$i]["unit"] != "") {
                $this->doc->addText($apointX["antal"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", "."));
                $this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["unit"]);
                $this->doc->addText($apointX["pris"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["price"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["price"], 2, ",", "."));
            }
            $amount =  $items[$i]["quantity"] * $items[$i]["price"];
            $total += $amount;
            $this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($amount, 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($amount, 2, ",", "."));

            $tekst = $items[$i]["name"];
            $first = true;

            while ($tekst != "") {

                if (!$first) {
                    $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
                    if ($bg_color == 1) {
                        $this->doc->setColor(0.8, 0.8, 0.8);
                        $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                        $this->doc->setColor(0, 0, 0);
                    }
                }
                $first = false;

                $tekst = $this->doc->addTextWrap($apointX["tekst"], $this->doc->get('y'), $apointX["tekst_width_small"], $this->doc->get("font_size"), $tekst);
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
                if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                    $this->doc->nextPage(true);
                }
            }

            if ($items[$i]["description"] != "") {

                // Laver lige et mellem rum ned til teksten
                $this->doc->setY('-'.($this->doc->get("font_spacing")/2));
                if ($bg_color == 1) {
                    $this->doc->setColor(0.8, 0.8, 0.8);
                    $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing")/2);
                    $this->doc->setColor(0, 0, 0);
                }

                $desc_line = explode("\r\n", $items[$i]["description"]);
                foreach ($desc_line AS $line) {
                    if ($line == "") {
                        if ($bg_color == 1) {
                            $this->doc->setColor(0.8, 0.8, 0.8);
                            $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                            $this->doc->setColor(0, 0, 0);
                        }
                        $this->doc->setY('-'.$this->doc->get("font_spacing"));
                        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                            $this->doc->nextPage(true);
                        }
                    } else {
                        while ($line != "") {

                            if ($bg_color == 1) {
                                $this->doc->setColor(0.8, 0.8, 0.8);
                                $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                                $this->doc->setColor(0, 0, 0);
                            }

                            $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
                            $line = $this->doc->addTextWrap($apointX["tekst"], $this->doc->get('y') + 1, $apointX["tekst_width"], $this->doc->get("font_size"), $line); // Ups Ups, hvor kommer '+ 1' fra - jo ser du, ellers kappes det nederste af teksten!
                            $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

                            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                                // print("a".$this->doc->get('y'));
                                $this->doc->nextPage(true);
                            }
                        }
                    }
                }

            }

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                // print("b".$this->doc->get('y'));
                $this->doc->nextPage(true);
            }

            // Hvis der har været poster med VAT, og næste post er uden, så tilskriver vi moms.
            // if ($vat == 1 && $items[$i+1]["vat"] == 0) {
            if (($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
                // Hvis der er moms på nuværende produkt, men næste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt

                ($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;

                if ($bg_color == 1) {
                    $this->doc->setColor(0.8, 0.8, 0.8);
                    $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
                    $this->doc->setColor(0, 0, 0);
                }

                $this->doc->setLineStyle(0.5);
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));
                $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
                $this->doc->addText($apointX["tekst"], $this->doc->get('y'), $this->doc->get("font_size"), "<b>25% moms af ".number_format($total, 2, ",", ".")."</b>");
                $this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>"), $this->doc->get('y'), $this->doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>");
                $total = $total * 1.25;
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));
                $this->doc->setLineStyle(1);
                $this->doc->setY('-1');
            }

            ($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;
        }


        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
            // print("c".$this->doc->get('y'));
            $this->doc->nextPage();
            // print($this->doc->get('y'));
        }

        $this->doc->setLineStyle(1);
        $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));

        if ($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) {
            $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
            $this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), "I alt:");
            $this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($total, 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($total, 2, ",", "."));
            $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

            $total_text = "Total afrundet DKK:";
        } else {
            $total_text = "Total DKK:";
        }

        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
            // print("d".$this->doc->get('y'));
            $this->doc->nextPage(true);
        }

        $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
        $this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), "<b>".$total_text."</b>");
        $this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>"), $this->doc->get('y'), $this->doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>");
        $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
        $this->doc->line($apointX["enhed"], $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));

        // paymentcondition
        if ($debtor->get("type") == "invoice" || $debtor->get("type") == "order") {


            $parameter = array(
                "contact" => $debtor->contact,
                "payment_text" => ucfirst($this->translation->get($debtor->get('type')))." ".$debtor->get("number"),
                "amount" => $debtor->get("total"),
                "payment" => $debtor->get('payment_total'),
                "payment_online" => $debtor->get('payment_online'),
                "due_date" => $debtor->get("dk_due_date"),
                "girocode" => $debtor->get("girocode"));

            $this->addPaymentCondition($debtor->get("payment_method"), $parameter, $debtor->getPaymentInformation());

            $this->doc->setY('-'.$this->doc->get("font_spacing"));

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                $this->doc->nextPage(true);
            }

            //$text = explode("\r\n", $debtor->kernel->setting->get('intranet', 'debtor.invoice.text'));
            $text = explode("\r\n", $debtor->getInvoiceText());
            foreach ($text AS $line) {
                if ($line == "") {
                    $this->doc->setY('-'.$this->doc->get("font_spacing"));
                    if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                        $this->doc->nextPage(true);
                    }
                } else {
                    while ($line != "") {
                        $this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
                        $line = $this->doc->addTextWrap($this->doc->get('margin_left'), $this->doc->get('y'), $this->doc->get('content_width'), $this->doc->get("font_size"), $line);
                        $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

                        if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
                            $this->doc->nextPage(true);
                        }
                    }
                }
            }
        }
    }

    /**
     * Output the debtor
     *
     * @param string $type     Output to type (string or file)
     * @param string $filename Filename
     *
     * @return void
     */
    function output($type = 'string', $filename = 'debtor.pdf') {
        switch ($type) {
        case 'string':
            return $this->doc->output();
            break;
        case 'file':
            $data = $this->doc->output();
            return $this->doc->writeDocument($data, $filename);
            break;
        default:
            return $this->doc->stream();
            break;
        }
    }

    /**
     * Adds the sender and receiver
     *
     * @param array  $contact  Information about the contact
     * @param array  $intranet Information about the intranet
     * @param string $title    WHAT IS THIS?
     * @param array  $docinfo  WHAT IS THIS?
     *
     * @return The y-coordinate after payment condition has been added
     */
    function addRecieverAndSender($contact, $intranet = array(), $title = "", $docinfo = array()) {

        // $pointX = $this->doc->get("margin_left");

        if (!is_array($contact)) {
            trigger_error("Første parameter skal være et array med konkaktoplysninger i PdfDebtor->addRecieverAndSender", E_USER_ERROR);
        }

        $box_top = $this->doc->get('y'); // $pointY;
        $box_padding_top = 8; // mellemrum fra top boks til første linie
        $box_padding_bottom = 9;
        $box_width = 275; // ($page_width - $margin_left - 10)/2;
        // $box_height = $this->doc->get("font_spacing") * 10 + $box_padding_top + $box_padding_bottom;
        $box_small_height = $this->doc->get("font_spacing") * 3 + $box_padding_top + $box_padding_bottom + 2;

        // Udskrivning af modtager
        $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $box_padding_top;
        $this->doc->addText($this->doc->get('x') + $box_width - 40, $this->doc->get('y') + 4, $this->doc->get("font_size") - 4, "Modtager");

        $this->doc->setY('-'.$box_padding_top);
        $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "<b>".$contact["name"]."</b>");
        $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");

        if (isset($contact["attention_to"]) && $contact["attention_to"] != "") {
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "Att: ".$contact["attention_to"]);
            $this->doc->setY('-'.$this->doc->get('font_spacing')); // $pointY -= $this->doc->get("font_spacing");
        }

        $line = explode("\r\n", $contact["address"]);
        for ($i = 0; $i < count($line); $i++) {
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $line[$i]);
            $this->doc->setY('-'.$this->doc->get("font_spacing"));

            if ($i == 2) $i = count($line);
        }
        // $pointY -= $this->doc->get("font_spacing");
        $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $contact["postcode"]." ".$contact["city"]);
        $this->doc->setY('-'.($this->doc->get("font_spacing") * 2));

        if ($contact["cvr"] != "") {
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "CVR.:");
            $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $contact["cvr"]);
            $this->doc->setY('-'.$this->doc->get("font_spacing"));
        }
        $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "Kontaktnr.:");
        $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $contact["number"]);
        if ($contact["ean"]) {
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y') - 15, $this->doc->get("font_size"), "EANnr.:");
            $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y') - 15, $this->doc->get("font_size"), $contact["ean"]);
        }

        $box_height = $box_top - $this->doc->get('y') + $box_padding_bottom;

        // Udskrivning af Afsender data
        if (is_array($intranet) && count($intranet) > 0) {
            $this->doc->setX($box_width + 10);
            $this->doc->setValue('y', $box_top); // sætter eksakt position
            $this->doc->setY('-'.$this->doc->get("font_spacing"));
            $this->doc->addText($this->doc->get('right_margin_position') - 40, $this->doc->get('y') + 4, $this->doc->get("font_size") - 4, "Afsender");

            $this->doc->setY('-'.$box_padding_top); // $pointY -= $box_padding_top;
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "<b>".$intranet["name"]."</b>");

            $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");
            $line = explode("\r\n", $intranet["address"]);
            for ($i = 0; $i < count($line); $i++) {
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $line[$i]);
                $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");
                if ($i == 2) $i = count($line);
            }
            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $intranet["postcode"]." ".$intranet["city"]);
            $this->doc->setY('-'.($this->doc->get("font_spacing") * 2)); // $pointY -= $this->doc->get("font_spacing") * 2;

            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "CVR.:");
            $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $intranet["cvr"]);
            $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");

            if (!empty($intranet["contact_person"]) AND $intranet['contact_person'] != $intranet["name"]) {
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "Kontakt:");
                $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $intranet["contact_person"]);
                $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");
            }


            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "Telefon:");
            $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $intranet["phone"]);
            $this->doc->setY('-'.$this->doc->get("font_spacing")); // $pointY -= $this->doc->get("font_spacing");

            $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "E-mail:");
            $this->doc->addText($this->doc->get('x') + 10 + 60, $this->doc->get('y'), $this->doc->get("font_size"), $intranet["email"]);

            if ($box_top - $this->doc->get('y') + $box_padding_bottom > $box_height) {
                $box_height = $box_top - $this->doc->get('y') + $box_padding_bottom;
            }
        }

        $this->doc->setValue('y', $box_top - $box_height); // sætter eksakt position

        // boks omkring afsender.
        $this->doc->roundRectangle($this->doc->get('x'), $this->doc->get('y'), $this->doc->get('right_margin_position') - $this->doc->get('x'), $box_height, 10);

        // boks omkring modtager
        $this->doc->roundRectangle($this->doc->get("margin_left"), $this->doc->get('y'), $box_width, $box_height, 10);

        // Udskrvining af fakturadata

        if (is_array($docinfo) && count($docinfo) > 0) {
            $this->doc->setY('-10'); // $pointY -= 10;
            $box_small_top = $this->doc->get('y');
            $box_small_height = count($docinfo) * $this->doc->get("font_spacing") + $box_padding_top + $box_padding_bottom;
            $this->doc->setY('-'.$box_padding_top); // $pointY -= $box_padding_top;

            for ($i = 0; $i < count($docinfo); $i++) {
                $this->doc->setY('-'.$this->doc->get('font_spacing'));
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $docinfo[$i]["label"]);
                $this->doc->addText($this->doc->get("right_margin_position") - 40 - $this->doc->getTextWidth($this->doc->get("font_size"), $docinfo[$i]["value"]), $this->doc->get('y'), $this->doc->get("font_size"), $docinfo[$i]["value"]);
            }

            $this->doc->setValue('y', $box_small_top - $box_small_height); // Sætter eksakt position
            $this->doc->roundRectangle($this->doc->get('x'), $this->doc->get('y'), $this->doc->get('right_margin_position') - $this->doc->get('x'), $box_small_height, 10);
        } else {
            $this->doc->setY($this->doc->get("font_size") + 12); // $pointY = $this->doc->get("font_size") + 12;
        }

        // Udskriver overskrift

        // $pointX = $this->doc->get("margin_left");
        $this->doc->setX(0);
        $this->doc->addText($this->doc->get('x'), $this->doc->get('y'), $this->doc->get("font_size") + 8, $title);

        return($this->doc->get('y'));
    }

    /**
     * Adds the payment condition to the document
     *
     * @param integer $payment_method The chosen payment method
     * @param array   $parameter      array("contact" => (object), "payment_text" => (string), "amount" => (double), "due_date" => (string), "girocode" => (string));
     * @param array   $payment_info   The payment information
     *
     * @return The y-coordinate after payment condition has been added
     */
    function addPaymentCondition($payment_method, $parameter, $payment_info = array()) {
        if (!is_array($parameter)) {
            trigger_error("den 3. parameter til addPaymentCondition skal være et array!", E_USER_ERROR);
        }

        if (!is_object($parameter['contact']->address)) {
            trigger_error("Arrayet i anden parameter indeholder ikke contact object med Address", E_USER_ERROR);
        }

        // adding payments
        if (isset($parameter['payment']) AND $parameter['payment'] != 0 OR isset($parameter['payment_online']) AND $parameter['payment_online'] != 0) {
            $this->doc->setY('-20');

            if (isset($parameter['payment']) AND $parameter['payment'] != 0) {
                $this->doc->setLineStyle(1.5);
                $this->doc->setColor(0, 0, 0);
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));
                $this->doc->setY('-'.$this->doc->get("font_padding_top"));
                $this->doc->setY('-'.$this->doc->get("font_size"));
                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size"), "Betalt");
                $this->doc->addText($this->doc->get("right_margin_position") - $this->getTextWidth($this->doc->get("font_size"), number_format($parameter['payment'], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($parameter['payment'], 2, ",", "."));
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            }

            if (isset($parameter['payment_online']) AND $parameter['payment_online'] != 0) {
                $this->doc->setLineStyle(1.5);
                $this->doc->setColor(0, 0, 0);
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

                $this->doc->setY('-'.$this->doc->get("font_padding_top"));
                $this->doc->setY('-'.$this->doc->get("font_size"));
                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size"), "Ventende betalinger");
                $this->doc->addText($this->doc->get("right_margin_position") - $this->getTextWidth($this->doc->get("font_size"), number_format($parameter['payment_online'], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($parameter['payment_online'], 2, ",", "."));
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            }

            $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

        }

        if (!isset($parameter['payment_online'])) $parameter['payment_online'] = 0;
        $amount = $parameter["amount"] - $parameter['payment_online'] - $parameter['payment'];

        // Indbetalingsoplysninger
        if ($amount <= 0) {
            $payment_method = 0; // så sætter vi ikke betalingsoplysninger på
        }

        if ($payment_method > 0) {
            $this->doc->setY('-20'); // $pointY -= 20; // Afstand ned til betalingsinfo
            // $pointX = $this->doc->get("margin_left");

            $payment_line = 26;
            $payment_left = 230;
            $payment_right = $this->doc->get("right_margin_position") - $this->doc->get("margin_left") - $payment_left;

            if ($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") + 4 + $payment_line * 3) {
                $this->doc->nextPage(true);
            }

            // Sort bjælke
            $this->doc->setLineStyle(1);
            $this->doc->setColor(0, 0, 0);
            $this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing") - 4, $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $this->doc->get("font_spacing") + 4);
            $this->doc->setColor(1, 1, 1);
            $this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top") + 2)); // $pointY -= $this->doc->get("font_size") + $this->doc->get("font_padding_top") + 2;
            $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") + 2, "Indbetalingsoplysninger");
            $this->doc->setColor(0, 0, 0);
            $this->doc->setY('-'.($this->doc->get("font_padding_bottom") + 2)); // $pointY -= $this->doc->get("font_padding_bottom") + 2;

            $payment_start = $this->doc->get('y');

            if ($payment_method == 1) {

                $this->doc->rectangle($this->doc->get('x'), $this->doc->get('y') - $payment_line * 2, $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $payment_line * 2);
                $this->doc->line($this->doc->get('x') + $payment_left, $this->doc->get('y') - $payment_line * 2, $this->doc->get('x') + $payment_left, $this->doc->get('y'));
                $this->doc->line($this->doc->get('x'), $this->doc->get('y') - $payment_line, $this->doc->get("right_margin_position"), $this->doc->get('y') - $payment_line);
                $this->doc->line($this->doc->get('x') + $payment_left / 2, $this->doc->get('y') - $payment_line * 2, $this->doc->get('x') + $payment_left / 2, $this->doc->get('y') - $payment_line);

                $this->doc->setY('-7'); // $pointY -= 7;
                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Bank:");
                $this->doc->setY('-'.($payment_line - 12)); // $pointY -= $payment_line - 12;
                // $this->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $this->kernel->setting->get("intranet", "bank_name"));
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $payment_info["bank_name"]);

                $this->doc->setValue('y', $payment_start); // $pointY = $payment_start;
                $this->doc->setY('-7'); // $pointY -= 7;

                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Tekst til modtager:");
                $this->doc->setY('-'.($payment_line - 12)); // $pointY -= $payment_line - 12;
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["payment_text"]);

                $this->doc->setValue('y', $payment_start - $payment_line); // Sætter ekstakt position
                $this->doc->setY('-7'); // $pointY -= 7;

                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Beløb DKK:");
                $this->doc->setY('-'.($payment_line - 12)); // $this->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), number_format($amount, 2, ",", "."));

                $this->doc->setValue('y', $payment_start - $payment_line); // Sætter eksakt position
                $this->doc->setY('-7'); // $pointY -= 7;

                $this->doc->addText($this->doc->get('x') + $payment_left / 2 + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Betalingsdato:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left / 2 + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["due_date"]);

                $this->doc->setValue('y', $payment_start - $payment_line); // sætter eksakt position
                $this->doc->setY('-7');


                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Regnr.:            Kontonr.:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get("font_size"), $payment_info["bank_reg_number"]."       ".$payment_info["bank_account_number"]);

            } elseif ($payment_method == 2) {

                $this->doc->rectangle($this->doc->get('x'), $this->doc->get('y') - $payment_line * 3, $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $payment_line * 3);
                $this->doc->line($this->doc->get('x') + $payment_left, $this->doc->get('y') - $payment_line * 3, $this->doc->get('x') + $payment_left, $this->doc->get('y'));
                $this->doc->line($this->doc->get('x') + $payment_left, $this->doc->get('y') - $payment_line, $this->doc->get("right_margin_position"), $this->doc->get('y') - $payment_line);
                $this->doc->line($this->doc->get('x') + $payment_left, $this->doc->get('y') - $payment_line * 2, $this->doc->get("right_margin_position"), $this->doc->get('y') - $payment_line * 2);
                $this->doc->line($this->doc->get('x') + $payment_left + $payment_right / 2, $this->doc->get('y') - $payment_line * 2, $this->doc->get('x') + $payment_left + $payment_right / 2, $this->doc->get('y') - $payment_line);

                $this->doc->setY('-7');
                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Indbetaler:");
                $this->doc->setY('-'.$this->doc->get('font_spacing'));

                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["contact"]->address->get("name"));
                $this->doc->setY('-'.$this->doc->get('font_spacing'));
                $line = explode("\r\n", $parameter["contact"]->address->get("address"));
                for ($i = 0; $i < count($line); $i++) {
                    $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $line[$i]);
                    $this->doc->setY('-'.$this->doc->get('font_spacing'));
                    if ($i == 2) $i = count($line);
                }
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["contact"]->address->get("postcode")." ".$parameter["contact"]->address->get("city"));

                $this->doc->setValue('y', $payment_start); // Sætter eksakt position
                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Tekst til modtager:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["payment_text"]);

                $this->doc->setValue('y', $payment_start - $payment_line); // sætter eksakt position
                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Beløb DKK:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get("font_size"), number_format($amount, 2, ",", "."));

                $this->doc->setValue('y', $payment_start - $payment_line); // Sætter eksakt position
                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + $payment_left + $payment_right / 2 + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Betalingsdato:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left + $payment_right / 2 + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["due_date"]);

                $this->doc->setValue('y', $payment_start - $payment_line * 2); // sætter eksakt position
                $this->doc->setY('-7');


                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
                $this->doc->setY('-'.($payment_line - 12));
                //$this_text = '+01<'.str_repeat(' ', 20).'+'.$payment_info['giro_account_number'].'<';
                // TODO change the - back to <> but it does not work right now
                $this_text = '+01-'.str_repeat(' ', 20).'+'.$payment_info['giro_account_number'].'-';
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get('font_size'), $this_text);
            } elseif ($payment_method == 3) {

                $this->doc->rectangle($this->doc->get('x'), $this->doc->get('y') - $payment_line * 2, $this->doc->get("right_margin_position") - $this->doc->get("margin_left"), $payment_line * 2);
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y') - $payment_line, $this->doc->get("right_margin_position"), $this->doc->get('y') - $payment_line);
                $this->doc->line($this->doc->get('x') + $payment_left, $this->doc->get('y'), $this->doc->get('x') + $payment_left, $this->doc->get('y') - $payment_line);

                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Beløb DKK:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), number_format($amount, 2, ",", "."));

                $this->doc->setValue('y', $payment_start); // Sætter eksakt position
                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + $payment_left + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Betalingsdato:");
                $this->doc->setY('-'.($payment_line - 12));
                $this->doc->addText($this->doc->get('x') + $payment_left + 10, $this->doc->get('y'), $this->doc->get("font_size"), $parameter["due_date"]);

                $this->doc->setValue('y', $payment_start - $payment_line); // sætter eksakt position
                $this->doc->setY('-7');

                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
                $this->doc->setY('-'.($payment_line - 12));
                // TODO change the - back to <> but it does not work

                // $this->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "+71< ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$payment_info["giro_account_number"]."<");
                $this->doc->addText($this->doc->get('x') + 10, $this->doc->get('y'), $this->doc->get("font_size"), "+71- ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$payment_info["giro_account_number"]."-");

            }
        }
        return($this->doc->get('y'));
    }

}
?>