<?php
/**
 * UNDER DEVELOPMENT
 * Usage:
 *
 * $file = new FileHandler($file_id);
 * $report = new Debtor_Report_Pdf($file);
 *
 */

class Debtor_Report_Pdf {

	private $file;
	private $translation;
	private $doc;

	function __construct($translation, $file = null) {
		$this->translation = $translation;
		$this->file = $file;
	}

	function visit(Debtor $debtor) {

		$shared_pdf = $debtor->kernel->useShared('pdf');
		$shared_pdf->includeFile('PdfMakerDebtor.php');

		// todo - mon ikke alt fra pdfmakerdebtor kan flyttes hertil?
		$this->doc = new PdfMakerDebtor($debtor->kernel);
		$this->doc->start();

		if (!empty($this->file) AND $this->file->get('id') > 0) {
			$this->doc->addHeader($this->file->get('file_uri_pdf'));
		}

		$this->doc->setY('-5');
		
		$contact = $debtor->contact->address->get();
		if(strtolower(get_class($debtor->contact_person)) == "contactperson") {
			$contact["attention_to"] = $debtor->contact_person->get("name");
		}
		$contact['number'] = $debtor->contact->get('number');
		
		$intranet_address = new Address($debtor->get("intranet_address_id"));
		$intranet = $intranet_address->get();
		
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

		$this->docinfo[0]["label"] = $this->translation->get($debtor->get('type').' number').":";
		$this->docinfo[0]["value"] = $debtor->get("number");
		$this->docinfo[1]["label"] = "Dato:";
		$this->docinfo[1]["value"] = $debtor->get("dk_this_date");
		if($debtor->get("type") != "credit_note" && $debtor->get("due_date") != "0000-00-00") {
			$this->docinfo[2]["label"] = $this->translation->get($debtor->get('type').' due date').":";
			$this->docinfo[2]["value"] = $debtor->get("dk_due_date");
		}

		$this->doc->addRecieverAndSender($contact, $intranet, $this->translation->get($debtor->get('type').' title'), $this->docinfo);

		$this->doc->setY('-'.$this->doc->get("font_spacing"));
			
		if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
			$this->doc->nextPage(true);
		}
			
		$text = explode("\r\n", $debtor->get('message'));
		foreach($text AS $line) {
			if($line == "") {	
				$this->doc->setY('-'.$this->doc->get("font_spacing"));
				if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
					$this->doc->nextPage(true);
				}
			}
			else {
				while($line != "") {
					$this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
					$line = $this->doc->addTextWrap($this->doc->get('margin_left'), $this->doc->get('y'), $this->doc->get('content_width'), $this->doc->get("font_size"), $line);
					$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
						if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
						$this->doc->nextPage(true);
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
		if(isset($items[0]["vat"])) {
			$vat = $items[0]["vat"];
		}
		else {
			$vat = 0;
		}
		// $line_padding = 4;
		// $line_height = $this->doc->get("font_size") + $line_padding * 2;
		$bg_color = 0;

		for($i = 0, $max = count($items); $i <  $max; $i++) {
			$vat = $items[$i]["vat"];


			if($bg_color == 1) {
				$this->doc->setColor(0.8, 0.8, 0.8);
				$this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
				$this->doc->setColor(0, 0, 0);
			}

			$this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
			$this->doc->addText($apointX["varenr"] - $this->doc->getTextWidth($this->doc->get("font_size"), $items[$i]["number"]), $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["number"]);
			if($items[$i]["unit"] != "") {
				$this->doc->addText($apointX["antal"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", "."));
				$this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), $items[$i]["unit"]);
				$this->doc->addText($apointX["pris"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($items[$i]["price"], 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($items[$i]["price"], 2, ",", "."));
			}
			$amount =  $items[$i]["quantity"] * $items[$i]["price"];
			$total += $amount;
			$this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($amount, 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($amount, 2, ",", "."));

			$tekst = $items[$i]["name"];
			$first = true;

			while($tekst != "") {

				if(!$first) {
					$this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
					if($bg_color == 1) {
						$this->doc->setColor(0.8, 0.8, 0.8);
						$this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
						$this->doc->setColor(0, 0, 0);
					}
				}
				$first = false;

				$tekst = $this->doc->addTextWrap($apointX["tekst"], $this->doc->get('y'), $apointX["tekst_width_small"], $this->doc->get("font_size"), $tekst);
				$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
				if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
					$this->doc->nextPage(true);
				}
			}

			if($items[$i]["description"] != "") {

				// Laver lige et mellem rum ned til teksten
				$this->doc->setY('-'.($this->doc->get("font_spacing")/2));
				if($bg_color == 1) {
					$this->doc->setColor(0.8, 0.8, 0.8);
					$this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing")/2);
					$this->doc->setColor(0, 0, 0);
				}

				$desc_line = explode("\r\n", $items[$i]["description"]);
				foreach($desc_line AS $line) {
					if($line == "") {
						if($bg_color == 1) {
							$this->doc->setColor(0.8, 0.8, 0.8);
							$this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
							$this->doc->setColor(0, 0, 0);
						}
						$this->doc->setY('-'.$this->doc->get("font_spacing"));
						if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
							$this->doc->nextPage(true);
						}
					}
					else {
						while($line != "") {

							if($bg_color == 1) {
								$this->doc->setColor(0.8, 0.8, 0.8);
								$this->doc->filledRectangle($this->doc->get("margin_left"), $this->doc->get('y') - $this->doc->get("font_spacing"), $this->doc->get('right_margin_position') - $this->doc->get("margin_left"), $this->doc->get("font_spacing"));
								$this->doc->setColor(0, 0, 0);
							}

							$this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
							$line = $this->doc->addTextWrap($apointX["tekst"], $this->doc->get('y') + 1, $apointX["tekst_width"], $this->doc->get("font_size"), $line); // Ups Ups, hvor kommer '+ 1' fra - jo ser du, ellers kappes det nederste af teksten!
							$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

							if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
								// print("a".$this->doc->get('y'));
								$this->doc->nextPage(true);
							}
						}
					}
				}

			}

			if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
				// print("b".$this->doc->get('y'));
				$this->doc->nextPage(true);
			}

			// Hvis der har været poster med VAT, og næste post er uden, så tilskriver vi moms.
			// if($vat == 1 && $items[$i+1]["vat"] == 0) {
			if(($vat == 1 && isset($items[$i+1]["vat"]) && $items[$i+1]["vat"] == 0) || ($vat == 1 && $i+1 >= $max)) {
				// Hvis der er moms på nuværende produkt, men næste produkt ikke har moms, eller hvis vi har moms og det er sidste produkt

				($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;

				if($bg_color == 1) {
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


		if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
			// print("c".$this->doc->get('y'));
			$this->doc->nextPage();
			// print($this->doc->get('y'));
		}

		$this->doc->setLineStyle(1);
		$this->doc->line($this->doc->get("margin_left"), $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));

		if($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) {
			$this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
			$this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), "I alt:");
			$this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), number_format($total, 2, ",", ".")), $this->doc->get('y'), $this->doc->get("font_size"), number_format($total, 2, ",", "."));
			$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

			$total_text = "Total afrundet DKK:";
		}
		else {
			$total_text = "Total DKK:";
		}

		if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
			// print("d".$this->doc->get('y'));
			$this->doc->nextPage(true);
		}

		$this->doc->setY('-'.($this->doc->get("font_size") + $this->doc->get("font_padding_top")));
		$this->doc->addText($apointX["enhed"], $this->doc->get('y'), $this->doc->get("font_size"), "<b>".$total_text."</b>");
		$this->doc->addText($apointX["beloeb"] - $this->doc->getTextWidth($this->doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>"), $this->doc->get('y'), $this->doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>");
		$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));
		$this->doc->line($apointX["enhed"], $this->doc->get('y'), $this->doc->get('right_margin_position'), $this->doc->get('y'));

		// paymentcondition
		if($debtor->get("type") == "invoice" || $debtor->get("type") == "order") {

			
			$parameter = array(
				"contact" => $debtor->contact,
				"payment_text" => ucfirst($this->translation->get($debtor->get('type')))." ".$debtor->get("number"),
				"amount" => $debtor->get("total"),
				"payment" => $debtor->get('payment_total'),
				"payment_online" => $debtor->get('payment_online'),
				"due_date" => $debtor->get("dk_due_date"),
				"girocode" => $debtor->get("girocode"));

			$this->doc->addPaymentCondition($debtor->get("payment_method"), $parameter);
			
			$this->doc->setY('-'.$this->doc->get("font_spacing"));
			
			if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
				$this->doc->nextPage(true);
			}
			
			$text = explode("\r\n", $debtor->kernel->setting->get('intranet', 'debtor.invoice.text'));
			foreach($text AS $line) {
				if($line == "") {	
					$this->doc->setY('-'.$this->doc->get("font_spacing"));
					if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
						$this->doc->nextPage(true);
					}
				}
				else {
					while($line != "") {
						$this->doc->setY('-'.($this->doc->get("font_padding_top") + $this->doc->get("font_size")));
						$line = $this->doc->addTextWrap($this->doc->get('margin_left'), $this->doc->get('y'), $this->doc->get('content_width'), $this->doc->get("font_size"), $line);
						$this->doc->setY('-'.$this->doc->get("font_padding_bottom"));

						if($this->doc->get('y') < $this->doc->get("margin_bottom") + $this->doc->get("font_spacing") * 2) {
							$this->doc->nextPage(true);
						}
					}
				}
			}
		}


	}

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

}
?>