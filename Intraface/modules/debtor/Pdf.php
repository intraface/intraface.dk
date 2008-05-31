<?php
/**
 * Main class for Debtor pdfs. Debtor_Report_Pdf and
 * Reminder_Report_Pdf extends from this
 *
 * @package Intraface_Debtor
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */
class Debtor_Pdf
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
        if (!is_object($translation)) {
            throw new Exception('translation is not an object');
        }

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
        $doc = new PdfMaker();
        return $doc;
    }

    /**
     * Output the debtor
     *
     * @param string $type     Output to type (string or file)
     * @param string $filename Filename
     *
     * @return void
     */
    function output($type = 'string', $filename = 'debtor.pdf')
    {
        switch ($type) {
        case 'string':
            return $this->doc->output();
            break;
        case 'file':
            $data = $this->doc->output();
            return $this->doc->writeDocument($data, $filename);
            break;
        case 'stream':
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
    function addRecieverAndSender($contact, $intranet = array(), $title = "", $docinfo = array())
    {
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
     * @todo Make the use of payment info better so it will not crash the server
     *       Create some checks.
     *
     * @param integer $payment_method The chosen payment method
     * @param array   $parameter      array("contact" => (object), "payment_text" => (string), "amount" => (double), "due_date" => (string), "girocode" => (string));
     * @param array   $payment_info   The payment information
     *
     * @return The y-coordinate after payment condition has been added
     */
    function addPaymentCondition($payment_method, $parameter, $payment_info = array())
    {
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
                $this->doc->addText($this->doc->get("right_margin_position") - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($parameter['payment'], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($parameter['payment'], 2, ",", "."));
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            }

            if (isset($parameter['payment_online']) AND $parameter['payment_online'] != 0) {
                $this->doc->setLineStyle(1.5);
                $this->doc->setColor(0, 0, 0);
                $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

                $this->doc->setY('-'.$this->doc->get("font_padding_top"));
                $this->doc->setY('-'.$this->doc->get("font_size"));
                $this->doc->addText($this->doc->get('x') + 4, $this->doc->get('y'), $this->doc->get("font_size"), "Ventende betalinger");
                $this->doc->addText($this->doc->get("right_margin_position") - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($parameter['payment_online'], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($parameter['payment_online'], 2, ",", "."));
                $this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
            }

            $this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get("right_margin_position"), $this->doc->get('y'));

        }

        if (!isset($parameter['payment_online'])) {
            $parameter['payment_online'] = 0;
        }
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
        return $this->doc->get('y');
    }
}