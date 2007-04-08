<?php

class PdfMakerDebtor extends PdfMaker {

	function PdfMakerDebtor(&$kernel) {
		this::__construct($kernel);
	}

	function __construct(&$kernel) {
		parent::__construct($kernel);


	}


	function addRecieverAndSender($contact, $intranet = array(), $title = "", $docinfo = array()) {

		// $pointX = $this->get("margin_left");

		if(!is_array($contact)) {
			trigger_error("Anden parameter skal være et array med konkaktoplysninger i PdfDebtor->addRecieverAndSender", E_USER_ERROR);
		}

		if(!is_object($contact["object"])) {
			trigger_error("Der er ikke et contact-object med i arrayet i anden parameter i PdfMakerDebtor->addReciverAndSender", E_USER_ERROR);
		}

		if(!is_object($contact["object"]->address)) {
			trigger_error("contact mangler address i PdfDebtor->addReciverAndSender", E_USER_ERROR);
		}

		if(isset($intranet["address_id"])) {
			$intranet_address = new Address($intranet["address_id"]);
		}
		else {
			$intranet_address = $this->kernel->intranet->address;
		}

		$box_top = $this->get('y'); // $pointY;
		$box_padding_top = 8; // mellemrum fra top boks til første linie
		$box_padding_bottom = 9;
		$box_width = 275; // ($page_width - $margin_left - 10)/2;
		// $box_height = $this->get("font_spacing") * 10 + $box_padding_top + $box_padding_bottom;
		$box_small_height = $this->get("font_spacing") * 3 + $box_padding_top + $box_padding_bottom + 2;

		# Udskrivning af modtager

		$this->setY('-'.$this->get("font_spacing")); // $pointY -= $box_padding_top;
		CorePdf::addText($this->get('x') + $box_width - 40, $this->get('y') + 4, $this->get("font_size") - 4, "Modtager");

		$this->setY('-'.$box_padding_top);
		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "<b>".$contact["object"]->address->get("name")."</b>");
		$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");

		if(isset($contact["attention_to"]) && $contact["attention_to"] != "") {
			CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "Att: ".$contact["attention_to"]);
			$this->setY('-'.$this->get('font_spacing')); // $pointY -= $this->get("font_spacing");
		}

		$line = explode("\r\n", $contact["object"]->address->get("address"));
		for($i = 0; $i < count($line); $i++) {
			CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $line[$i]);
			$this->setY('-'.$this->get("font_spacing"));

			if($i == 2) $i = count($line);
		}
		// $pointY -= $this->get("font_spacing");
		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $contact["object"]->address->get("postcode")." ".$contact["object"]->address->get("city"));
		$this->setY('-'.($this->get("font_spacing") * 2));

		if($contact["object"]->address->get("cvr") != "") {
			CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "CVR.:");
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $contact["object"]->address->get("cvr"));
			$this->setY('-'.$this->get("font_spacing"));
		}
		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "Kontaktnr.:");
		CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $contact["object"]->get("number"));
		if ($contact["object"]->address->get("ean")) {
		 CorePdf::addText($this->get('x') + 10, $this->get('y') - 15, $this->get("font_size"), "EANnr.:");
		 CorePdf::addText($this->get('x') + 10 + 60, $this->get('y') - 15, $this->get("font_size"), $contact["object"]->address->get("ean"));
		}

		$box_height = $box_top - $this->get('y') + $box_padding_bottom;


		# Udskrivning af Afsender data
		$this->setX($box_width + 10);
		$this->setValue('y', $box_top); // sætter eksakt position
		$this->setY('-'.$this->get("font_spacing"));
		CorePdf::addText($this->get('right_margin_position') - 40, $this->get('y') + 4, $this->get("font_size") - 4, "Afsender");

		$this->setY('-'.$box_padding_top); // $pointY -= $box_padding_top;
		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "<b>".$intranet_address->get("name")."</b>");

		$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");
		$line = explode("\r\n", $intranet_address->get("address"));
		for($i = 0; $i < count($line); $i++) {
			CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $line[$i]);
			$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");
			if($i == 2) $i = count($line);
		}
		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $intranet_address->get("postcode")." ".$intranet_address->get("city"));
		$this->setY('-'.($this->get("font_spacing") * 2)); // $pointY -= $this->get("font_spacing") * 2;

		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "CVR.:");
		CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $intranet_address->get("cvr"));
		$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");

		if($intranet["user_id"] != 0) {
			$user = new User($intranet["user_id"]);
			CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "Kontakt:");
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $user->address->get("name"));
			$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");
		}


		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "Telefon:");
		if(isset($user) && strtolower(get_class($user)) == "user" && $user->address->get("phone") != "") {
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $user->address->get("phone"));
		}
		else {
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $intranet_address->get("phone"));
		}
		$this->setY('-'.$this->get("font_spacing")); // $pointY -= $this->get("font_spacing");

		CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "E-mail:");
		if(isset($user) && strtolower(get_class($user)) == "user" && $user->address->get("email") != "") {
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $user->address->get("email"));
		}
		else {
			CorePdf::addText($this->get('x') + 10 + 60, $this->get('y'), $this->get("font_size"), $intranet_address->get("email"));
		}

		if($box_top - $this->get('y') + $box_padding_bottom > $box_height) {
			$box_height = $box_top - $this->get('y') + $box_padding_bottom;
		}

		$this->setValue('y', $box_top - $box_height); // sætter eksakt position

		# boks omkring afsender.
		$this->roundRectangle($this->get('x'), $this->get('y'), $this->get('right_margin_position') - $this->get('x'), $box_height, 10);

		# boks omkring modtager
		$this->roundRectangle($this->get("margin_left"), $this->get('y'), $box_width, $box_height, 10);

		// Udskrvining af fakturadata

		if(is_array($docinfo) && count($docinfo) > 0) {
			$this->setY('-10'); // $pointY -= 10;
			$box_small_top = $this->get('y');
			$box_small_height = count($docinfo) * $this->get("font_spacing") + $box_padding_top + $box_padding_bottom;
 			$this->setY('-'.$box_padding_top); // $pointY -= $box_padding_top;

			for($i = 0; $i < count($docinfo); $i++) {
				$this->setY('-'.$this->get('font_spacing'));
				CorePdf::addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $docinfo[$i]["label"]);
				CorePdf::addText($this->get("right_margin_position") - 40 - $this->getTextWidth($this->get("font_size"), $docinfo[$i]["value"]), $this->get('y'), $this->get("font_size"), $docinfo[$i]["value"]);
			}

			$this->setValue('y', $box_small_top - $box_small_height); // Sætter eksakt position
			$this->roundRectangle($this->get('x'), $this->get('y'), $this->get('right_margin_position') - $this->get('x'), $box_small_height, 10);
		}
		else {
			$this->setY($this->get("font_size") + 12); // $pointY = $this->get("font_size") + 12;
		}

		// Udskriver overskrift

		// $pointX = $this->get("margin_left");
		$this->setX(0);
		CorePdf::addText($this->get('x'), $this->get('y'), $this->get("font_size") + 8, $title);

		return($this->get('y'));
	}

	/**
	 *
	 * @param parameter: array("contact" => (object), "payment_text" => (string), "amount" => (double), "due_date" => (string), "girocode" => (string));
	 */


	function addPaymentCondition($payment_method, $parameter) {
		if(!is_array($parameter)) {
			trigger_error("den 3. parameter til addPaymentCondition skal være et array!", E_USER_ERROR);
		}

		if(!is_object($parameter['contact']->address)) {
			trigger_error("Arrayet i anden parameter indeholder ikke contact object med Address", E_USER_ERROR);
		}


		if($parameter['payment'] != 0 || isset($parameter['payment_online']) AND $parameter['payment_online'] != 0) {
			$this->setY('-20');

			if($parameter['payment'] != 0) {
				$this->setLineStyle(1.5);
				$this->setColor(0, 0, 0);
				$this->line($this->get("margin_left"), $this->get('y'), $this->get("right_margin_position"), $this->get('y'));
				$this->setY('-'.$this->get("font_padding_top"));
				$this->setY('-'.$this->get("font_size"));
				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size"), "Betalt");
				$this->addText($this->get("right_margin_position") - $this->getTextWidth($this->get("font_size"), number_format($parameter['payment'], 2, ",", ".")), $this->get('y'), $this->get("font_size"), number_format($parameter['payment'], 2, ",", "."));
				$this->setY('-'.$this->get("font_padding_bottom"));
			}

			if(isset($parameter['payment_online']) AND $parameter['payment_online'] != 0) {
				$this->setLineStyle(1.5);
				$this->setColor(0, 0, 0);
				$this->line($this->get("margin_left"), $this->get('y'), $this->get("page_width"), $this->get('y'));
				$this->setY('-'.$this->get("font_padding_top"));
				$this->setY('-'.$this->get("font_size"));
				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size"), "Ventende betalinger");
				$this->addText($this->get("right_margin_position") - $this->getTextWidth($this->get("font_size"), number_format($parameter['payment_online'], 2, ",", ".")), $this->get('y'), $this->get("font_size"), number_format($parameter['payment_online'], 2, ",", "."));
				$this->setY('-'.$this->get("font_padding_bottom"));
			}

			$this->line($this->get("margin_left"), $this->get('y'), $this->get("right_margin_position"), $this->get('y'));

		}

		if (!isset($parameter['payment_online'])) $parameter['payment_online'] = 0;
		$amount = $parameter["amount"] - $parameter['payment_online'] - $parameter['payment'];

		if($amount <= 0) {
			$payment_method = 0; // så sætter vi ikke betalingsoplysninger på
		}

		// Indbetalingsoplysninger

		if($payment_method > 0) {
			$this->setY('-20'); // $pointY -= 20; // Afstand ned til betalingsinfo
			// $pointX = $this->get("margin_left");

			$payment_line = 26;
			$payment_left = 230;
			$payment_right = $this->get("right_margin_position") - $this->get("margin_left") - $payment_left;

			if($this->get('y') < $this->get("margin_bottom") + $this->get("font_spacing") + 4 + $payment_line * 3) {
				$this->nextPage(true);
			}

			// Sort bjælke
			$this->setLineStyle(1);
			$this->setColor(0, 0, 0);
			$this->filledRectangle($this->get("margin_left"), $this->get('y') - $this->get("font_spacing") - 4, $this->get("right_margin_position") - $this->get("margin_left"), $this->get("font_spacing") + 4);
			$this->setColor(1, 1, 1);
			$this->setY('-'.($this->get("font_size") + $this->get("font_padding_top") + 2)); // $pointY -= $this->get("font_size") + $this->get("font_padding_top") + 2;
			$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") + 2, "Indbetalingsoplysninger");
			$this->setColor(0, 0, 0);
			$this->setY('-'.($this->get("font_padding_bottom") + 2)); // $pointY -= $this->get("font_padding_bottom") + 2;

			$payment_start = $this->get('y');

			if($payment_method == 1) {

				$this->rectangle($this->get('x'), $this->get('y') - $payment_line * 2, $this->get("right_margin_position") - $this->get("margin_left"), $payment_line * 2);
				$this->line($this->get('x') + $payment_left, $this->get('y') - $payment_line * 2, $this->get('x') + $payment_left, $this->get('y'));
				$this->line($this->get('x'), $this->get('y') - $payment_line, $this->get("right_margin_position"), $this->get('y') - $payment_line);
				$this->line($this->get('x') + $payment_left / 2, $this->get('y') - $payment_line * 2, $this->get('x') + $payment_left / 2, $this->get('y') - $payment_line);

				$this->setY('-7'); // $pointY -= 7;
				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") - 4, "Bank:");
				$this->setY('-'.($payment_line - 12)); // $pointY -= $payment_line - 12;
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $this->kernel->setting->get("intranet", "bank_name"));

				$this->setValue('y', $payment_start); // $pointY = $payment_start;
				$this->setY('-7'); // $pointY -= 7;

				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Tekst til modtager:");
				$this->setY('-'.($payment_line - 12)); // $pointY -= $payment_line - 12;
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), $parameter["payment_text"]);

				$this->setValue('y', $payment_start - $payment_line); // Sætter ekstakt position
				$this->setY('-7'); // $pointY -= 7;

				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") - 4, "Beløb DKK:");
				$this->setY('-'.($payment_line - 12)); // $this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), number_format($amount, 2, ",", "."));

				$this->setValue('y', $payment_start - $payment_line); // Sætter eksakt position
				$this->setY('-7'); // $pointY -= 7;

				$this->addText($this->get('x') + $payment_left / 2 + 4, $this->get('y'), $this->get("font_size") - 4, "Betalingsdato:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left / 2 + 10, $this->get('y'), $this->get("font_size"), $parameter["due_date"]);

				$this->setValue('y', $payment_start - $payment_line); // sætter eksakt position
				$this->setY('-7');


				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Regnr.:            Kontonr.:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), $this->kernel->setting->get("intranet", "bank_reg_number")."       ".$this->kernel->setting->get("intranet", "bank_account_number"));

			}
			elseif($payment_method == 2) {

				$this->rectangle($this->get('x'), $this->get('y') - $payment_line * 3, $this->get("right_margin_position") - $this->get("margin_left"), $payment_line * 3);
				$this->line($this->get('x') + $payment_left, $this->get('y') - $payment_line * 3, $this->get('x') + $payment_left, $this->get('y'));
				$this->line($this->get('x') + $payment_left, $this->get('y') - $payment_line, $this->get("right_margin_position"), $this->get('y') - $payment_line);
				$this->line($this->get('x') + $payment_left, $this->get('y') - $payment_line * 2, $this->get("right_margin_position"), $this->get('y') - $payment_line * 2);
				$this->line($this->get('x') + $payment_left + $payment_right / 2, $this->get('y') - $payment_line * 2, $this->get('x') + $payment_left + $payment_right / 2, $this->get('y') - $payment_line);

				$this->setY('-7');
				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") - 4, "Indbetaler:");
				$this->setY('-'.$this->get('font_spacing'));
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $parameter["contact"]->address->get("name"));
				$this->setY('-'.$this->get('font_spacing'));
				$line = explode("\r\n", $parameter["contact"]->address->get("address"));
				for($i = 0; $i < count($line); $i++) {
					$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $line[$i]);
					$this->setY('-'.$this->get('font_spacing'));
					if($i == 2) $i = count($line);
				}
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), $parameter["contact"]->address->get("postcode")." ".$parameter["contact"]->address->get("city"));

				$this->setValue('y', $payment_start); // Sætter eksakt position
				$this->setY('-7');

				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Tekst til modtager:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), $parameter["payment_text"]);

				$this->setValue('y', $payment_start - $payment_line); // sætter eksakt position
				$this->setY('-7');

				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Beløb DKK:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), number_format($amount, 2, ",", "."));

				$this->setValue('y', $payment_start - $payment_line); // Sætter eksakt position
				$this->setY('-7');

				$this->addText($this->get('x') + $payment_left + $payment_right / 2 + 4, $this->get('y'), $this->get("font_size") - 4, "Betalingsdato:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + $payment_right / 2 + 10, $this->get('y'), $this->get("font_size"), $parameter["due_date"]);

				$this->setValue('y', $payment_start - $payment_line * 2); // sætter eksakt position
				$this->setY('-7');


				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), "+01<".str_repeat(" ", 20)."+".$this->kernel->setting->get("intranet", "giro_account_number")."<");
			}
			elseif($payment_method == 3) {

				$this->rectangle($this->get('x'), $this->get('y') - $payment_line * 2, $this->get("right_margin_position") - $this->get("margin_left"), $payment_line * 2);
				$this->line($this->get("margin_left"), $this->get('y') - $payment_line, $this->get("right_margin_position"), $this->get('y') - $payment_line);
				$this->line($this->get('x') + $payment_left, $this->get('y'), $this->get('x') + $payment_left, $this->get('y') - $payment_line);

				$this->setY('-7');

				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") - 4, "Beløb DKK:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), number_format($amount, 2, ",", "."));

				$this->setValue('y', $payment_start); // Sætter eksakt position
				$this->setY('-7');

				$this->addText($this->get('x') + $payment_left + 4, $this->get('y'), $this->get("font_size") - 4, "Betalingsdato:");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + $payment_left + 10, $this->get('y'), $this->get("font_size"), $parameter["due_date"]);

				$this->setValue('y', $payment_start - $payment_line); // sætter eksakt position
				$this->setY('-7');

				$this->addText($this->get('x') + 4, $this->get('y'), $this->get("font_size") - 4, "Kodelinje: (Ej til maskinel aflæsning)");
				$this->setY('-'.($payment_line - 12));
				$this->addText($this->get('x') + 10, $this->get('y'), $this->get("font_size"), "+71< ".str_repeat("0", 15 - strlen($parameter["girocode"])).$parameter["girocode"]." +".$this->kernel->setting->get("intranet", "giro_account_number")."<");

			}
		}
		return($this->get('y'));
	}
}


?>