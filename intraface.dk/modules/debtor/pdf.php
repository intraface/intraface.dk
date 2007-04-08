<?php
require('../../include_first.php');

$debtor_module = $kernel->module("debtor");
$kernel->useModule("contact");
$kernel->useModule("product");

// $mainPdf = $kernel->useModule("pdf");

$debtor = Debtor::factory($kernel, intval($_GET["id"]));

if (!empty($_GET['format'])) {
	$format = $_GET['format'];
}
else {
	$format = 'pdf';
}

switch ($format) {

	case 'oioxml':
		if ($debtor->get('type') != 'invoice') {
			die('Can only generate oioxml for invoices');
		}
		$debtor_module->includeFile('Visitor/OIOXML.php');
		$report = new Debtor_Report_OIOXML;
		$report->visit($debtor);
		echo $report->display();
	break;
	default:
		$debtor->pdf();
	break;
}
exit;


/*
$page = new Page($kernel);


$doc = new Pdf($kernel);

if($kernel->intranet->get("invoice_pic_id") != 0) {
	$doc->startDocument(UPLOAD_PATH.$kernel->intranet->get("invoice_pic_id")); // "header.jpg"
}
else {
	$doc->startDocument();
}

$pointX = $doc->get("margin_left");
$pointY = $doc->get("page_height");

$pointY -= 10; // øvre mellemrum ned til adressebox

$contact["object"] = $debtor->contact;
if(get_class($debtor->contact_person) == "contactperson") {
	$contact["attention_to"] = $debtor->contact_person->get("name");
}
$intranet["address_id"] = $debtor->get("intranet_address_id");
$intranet["user_id"] = $debtor->get("user_id");

$docinfo[0]["label"] = $debtor->getText("number").":";
$docinfo[0]["value"] = $debtor->get("number");
$docinfo[1]["label"] = "Dato:";
$docinfo[1]["value"] = $debtor->get("dk_this_date");
if($debtor->get("type") != "credit_note" && $debtor->get("due_date") != "0000-00-00") {
	$docinfo[2]["label"] = $debtor->getText("due_date").":";
	$docinfo[2]["value"] = $debtor->get("dk_due_date");
}

$pointY = $doc->addRecieverAndSender($pointY, $contact, $intranet, ucfirst($debtor->getText("title")), $docinfo);

// Overskrifter - Vareudskrivning

$pointY -= 40; // mellemrum til vareoversigt

$apointX["varenr"] = 80;
$apointX["tekst"] = 90;
$apointX["antal"] = $doc->get("page_width") - 150;
$apointX["enhed"] = $doc->get("page_width") - 145;
$apointX["pris"] = $doc->get("page_width") - 60;
$apointX["beloeb"] = $doc->get("page_width");
$apointX["tekst_width"] = $doc->get("page_width") - $doc->get("margin_left") - $apointX["tekst"] - 60;
$apointX["tekst_width_small"] = $apointX["antal"] - $doc->get("margin_left") - $apointX["tekst"];


$doc->addText($apointX["varenr"] - $doc->getTextWidth($doc->get("font_size"), "Varenr."), $pointY, $doc->get("font_size"), "Varenr.");
$doc->addText($apointX["tekst"], $pointY, $doc->get("font_size"), "Tekst");
$doc->addText($apointX["antal"] - $doc->getTextWidth($doc->get("font_size"), "Antal"), $pointY, $doc->get("font_size"), "Antal");
// $doc->addText($apointX["enhed"], $pointY, $doc->get("font_size"), "Enhed");
$doc->addText($apointX["pris"] - $doc->getTextWidth($doc->get("font_size"), "Pris"), $pointY, $doc->get("font_size"), "Pris");
$doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "Beløb") -3, $pointY, $doc->get("font_size"), "Beløb");

$pointY -= $doc->get("font_spacing") - $doc->get("font_size");

$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);

// vareoversigt
$debtor->loadItem();
$items = $debtor->item->getList();

$total = 0;
$vat = $items[0]["vat"];
// $line_padding = 4;
// $line_height = $doc->get("font_size") + $line_padding * 2;
$bg_color = 0;

for($i = 0, $max = count($items); $i <  $max; $i++) {
	$vat = $items[$i]["vat"];

	// starter lige med at skrive teksten

	$afterTekst = $pointY;

	$tekst = $items[$i]["name"];
	// HACK
	if($tekst == "") $tekst = " "; // OK hack, men hvis der ikke er nogen tekst, så lige et mellemrum, så den får lavet den grå boks alligevel

	while($tekst != "") {

		if($bg_color == 1) {
			$doc->setColor(0.8, 0.8, 0.8);
			$doc->filledRectangle($doc->get("margin_left"), $afterTekst - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
			$doc->setColor(0, 0, 0);
		}

		$afterTekst -= $doc->get("font_padding_top");
		$afterTekst -= $doc->get("font_size");
		$tekst = $doc->addTextWrap($apointX["tekst"], $afterTekst, $apointX["tekst_width_small"], $doc->get("font_size"), $tekst); // Ups Ups, hvor kommer '+ 1' fra - jo ser du, ellers kappes det nederste af teksten!
		$afterTekst -= $doc->get("font_padding_bottom");
		if($afterTekst < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
			$afterTekst = $doc->nextPage();
		}
	}

	$pointY -= $doc->get("font_padding_top") + $doc->get("font_size");
	$doc->addText($apointX["varenr"] - $doc->getTextWidth($doc->get("font_size"), $items[$i]["number"]), $pointY, $doc->get("font_size"), $items[$i]["number"]);
	if($items[$i]["unit"] != "") {
		$doc->addText($apointX["antal"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($items[$i]["quantity"], 2, ",", "."));
		$doc->addText($apointX["enhed"], $pointY, $doc->get("font_size"), $items[$i]["unit"]);
		$doc->addText($apointX["pris"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["price"], 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($items[$i]["price"], 2, ",", "."));
	}
	$amount =  $items[$i]["quantity"] * $items[$i]["price"];
	$total += $amount;
	$doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), number_format($amount, 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($amount, 2, ",", "."));

	$pointY = $afterTekst;

	if($items[$i]["description"] != "") {

		// Laver lige et mellem rum ned til teksten
		$pointY -= $doc->get("font_spacing")/2;
		if($bg_color == 1) {
			$doc->setColor(0.8, 0.8, 0.8);
			$doc->filledRectangle($doc->get("margin_left"), $pointY, $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing")/2);
			$doc->setColor(0, 0, 0);
		}

		$desc_line = explode("\r\n", $items[$i]["description"]);
		foreach($desc_line AS $line) {
			if($line == "") {
				if($bg_color == 1) {
					$doc->setColor(0.8, 0.8, 0.8);
					$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
					$doc->setColor(0, 0, 0);
				}
				$pointY -= $doc->get("font_spacing");
				if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
					$pointY = $doc->nextPage();
				}
			}
			else {
				while($line != "") {

					if($bg_color == 1) {
						$doc->setColor(0.8, 0.8, 0.8);
						$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
						$doc->setColor(0, 0, 0);
					}

					$pointY -= $doc->get("font_padding_top") + $doc->get("font_size");
					$line = $doc->addTextWrap($apointX["tekst"], $pointY + 1, $apointX["tekst_width"], $doc->get("font_size"), $line); // Ups Ups, hvor kommer '+ 1' fra - jo ser du, ellers kappes det nederste af teksten!
					$pointY -= $doc->get("font_padding_bottom");
					if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
						$pointY = $doc->nextPage();
					}
				}
			}
		}

	}

	if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
		$pointY = $doc->newPage();
	}

	// Hvis der har været poster med VAT, og næste post er uden, så tilskriver vi moms.
	if($vat == 1 && $items[$i+1]["vat"] == 0) {
		($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;

		if($bg_color == 1) {
			$doc->setColor(0.8, 0.8, 0.8);
			$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
			$doc->setColor(0, 0, 0);
		}

		$doc->setLineStyle(0.5);
		$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);
		$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
		$doc->addText($apointX["tekst"], $pointY, $doc->get("font_size"), "<b>25% moms af ".number_format($total, 2, ",", ".")."</b>");
		$doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>"), $pointY, $doc->get("font_size"), "<b>".number_format($total * 0.25, 2, ",", ".")."</b>");
		$total = $total * 1.25;
		$pointY -= $doc->get("font_padding_bottom");
		$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);
		$doc->setLineStyle(1);
		$pointY -= 1;
	}

	($bg_color == 1) ? $bg_color = 0 : $bg_color = 1;
}

if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
	$pointY = $doc->newPage();
}

$doc->setLineStyle(1);

$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);


if($debtor->get("round_off") == 1 && $debtor->get("type") == "invoice" && $total != $debtor->get("total")) {
	$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
	$doc->addText($apointX["enhed"], $pointY, $doc->get("font_size"), "I alt:");
	$doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), number_format($total, 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($total, 2, ",", "."));
	$pointY -= $doc->get("font_padding_bottom");

	$total_text = "Total afrundet DKK:";
}
else {
	$total_text = "Total DKK:";
}

$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
$doc->addText($apointX["enhed"], $pointY, $doc->get("font_size"), "<b>".$total_text."</b>");
$doc->addText($apointX["beloeb"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>"), $pointY, $doc->get("font_size"), "<b>".number_format($debtor->get("total"), 2, ",", ".")."</b>");
$pointY -= $doc->get("font_padding_bottom");
$doc->line($apointX["enhed"], $pointY, $doc->get("page_width"), $pointY);

// paymentcondition
if($debtor->get("type") == "invoice") {

	$parameter = array(
		"contact" => $debtor->contact,
		"payment_text" => "Faktura ".$debtor->get("number"),
		"amount" => $debtor->get("total"),
		"due_date" => $debtor->get("dk_due_date"),
		"girocode" => $debtor->get("girocode"));

	$pointY = $doc->addPaymentCondition($pointY, $debtor->get("payment_method"), $parameter);
}

$doc->stream();
*/
//$data = $doc->output();
//$filename = "faktura.pdf";
//$doc->writeDocument($data, $filename);
// header("location: files/".$filename);
?>

