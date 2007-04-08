<?php
require('../../include_first.php');

$kernel->module("debtor");
$mainInvoice = $kernel->useModule("invoice");

/*
$mainInvoice->includeFile("Reminder.php");
$mainInvoice->includeFile("ReminderItem.php");

$kernel->useModule("contact");
$kernel->useModule("product");

$kernel->useShared('filehandler');

$mainPdf = $kernel->useModule("pdf");
$mainPdf->includeFile("Pdf.php");

//$kernel->useShared('pdf');
*/
$reminder = new Reminder($kernel, intval($_GET["id"]));

$reminder->pdf();
/*
$doc = new Pdf($kernel);

if($kernel->intranet->get("pdf_header_file_id") != 0) {


	$filehandler = new FileHandler($kernel, $kernel->intranet->get("pdf_header_file_id"));
	$doc->startDocument($filehandler->get('file_uri_pdf')); // "header.jpg"
	// $doc->startDocument(UPLOAD_PATH.$kernel->intranet->get("pdf_header_file_id")); // "header.jpg"
}
else {
	$doc->startDocument();
}

$pointX = $doc->get("margin_left");
$pointY = $doc->get("page_height");

$pointY -= 10; // øvre mellemrum ned til adressebox

$contact["object"] = $reminder->contact;
if(get_class($reminder->contact_person) == "contactperson") {
	$contact["attention_to"] = $reminder->contact_person->get("name");
}

$intranet["address_id"] = $reminder->get("intranet_address_id");
$intranet["user_id"] = $reminder->get("user_id");

$docinfo[0]["label"] = "Dato:";
$docinfo[0]["value"] = $reminder->get("dk_this_date");


$pointY = $doc->addRecieverAndSender($pointY, $contact , $intranet, "Påmindelse om betaling", $docinfo);

$pointY -= 20; // mellemrum til vareoversigt

$text = explode("\r\n", $reminder->get("text"));
foreach($text AS $line) {
	if($line == "") {
		$pointY -= $doc->get("font_spacing");

		if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
			$pointY = $doc->nextPage();
		}
	}
	else {
		while($line != "") {
			$pointY -= $doc->get("font_padding_top") + $doc->get("font_size");
			$line = $doc->addTextWrap($pointX, $pointY, $doc->get("page_width") - $pointX, $doc->get("font_size"), $line); // $doc->get("page_width") - $pointX
			// $doc->line($pointX, $pointY, $pointX + $doc->get("page_width") - $pointX, $pointY);

			$pointY -= $doc->get("font_padding_bottom");

			if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
				$pointY = $doc->nextPage();
			}
		}
	}
}

// Overskrifter - Vareudskrivning

$pointY -= 20; // mellemrum til vareoversigt

if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 3) {
	$pointY = $doc->newPage();
}

$apointX["text"] = $doc->get("margin_left");
$apointX["invoice_date"] = $doc->get("page_width") - 225;
$apointX["due_date"] = $doc->get("page_width") - 150;
$apointX["amount"] = $doc->get("page_width");
$apointX["text_width"] = $doc->get("page_width") - $doc->get("margin_left") - $apointX["tekst"] - 60;


$doc->addText($apointX["text"], $pointY, $doc->get("font_size"), "Beskrivelse");
//$doc->addText($apointX["tekst"], $pointY, $doc->get("font_size"), "Tekst");
$doc->addText($apointX["invoice_date"], $pointY, $doc->get("font_size"), "Dato");
$doc->addText($apointX["due_date"], $pointY, $doc->get("font_size"), "Forfaldsdato");
$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), "Beløb") -3, $pointY, $doc->get("font_size"), "Beløb");

$pointY -= $doc->get("font_spacing") - $doc->get("font_size");

$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);

// vareoversigt

$reminder->loadItem();
$items = $reminder->item->getList("invoice");

$total = 0;
$color = 0;

for($i = 0, $max = count($items); $i < $max; $i++) {

	if($color == 1) {
		$doc->setColor(0.8, 0.8, 0.8);
		$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
		$doc->setColor(0, 0, 0);
		$color = 0;
	}
	else {
		$color = 1;
	}

	$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");

	$doc->addText($apointX["text"], $pointY, $doc->get("font_size"), "Faktura nr. ".$items[$i]["number"]);
	$doc->addText($apointX["invoice_date"], $pointY, $doc->get("font_size"), $items[$i]["dk_this_date"]);
	$doc->addText($apointX["due_date"], $pointY, $doc->get("font_size"), $items[$i]["dk_due_date"]);
	$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["total"], 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($items[$i]["total"], 2, ",", "."));
	$pointY -= $doc->get("font_padding_bottom");
	$total += $items[$i]["total"];

	if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
		$pointY = $doc->newPage();
	}
}

$items = $reminder->item->getList("reminder");

for($i = 0, $max = count($items); $i < $max; $i++) {

	if($color == 1) {
		$doc->setColor(0.8, 0.8, 0.8);
		$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
		$doc->setColor(0, 0, 0);
		$color = 0;
	}
	else {
		$color = 1;
	}

	$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
	$doc->addText($apointX["text"], $pointY, $doc->get("font_size"), "Rykkkergebyr fra tidligere rykker");
	$doc->addText($apointX["invoice_date"], $pointY, $doc->get("font_size"), $items[$i]["dk_this_date"]);
	$doc->addText($apointX["due_date"], $pointY, $doc->get("font_size"), $items[$i]["dk_due_date"]);
	$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($items[$i]["reminder_fee"], 2, ",", "."));
	$pointY -= $doc->get("font_padding_bottom");
	$total += $items[$i]["reminder_fee"];

	if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
		$pointY = $doc->newPage();
	}
}


if($reminder->get("reminder_fee") > 0) {

	if($color == 1) {
		$doc->setColor(0.8, 0.8, 0.8);
		$doc->filledRectangle($doc->get("margin_left"), $pointY - $doc->get("font_spacing"), $doc->get("page_width") - $doc->get("margin_left"), $doc->get("font_spacing"));
		$doc->setColor(0, 0, 0);
		$color = 0;
	}
	else {
		$color = 1;
	}

	$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
	$doc->addText($apointX["text"], $pointY, $doc->get("font_size"), "Rykkergebyr pålagt denne rykker");
	$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), number_format($reminder->get("reminder_fee"), 2, ",", ".")), $pointY, $doc->get("font_size"), number_format($reminder->get("reminder_fee"), 2, ",", "."));
	$pointY -= $doc->get("font_padding_bottom");
	$total += $reminder->get("reminder_fee");

	if($pointY < $doc->get("margin_bottom") + $doc->get("font_spacing") * 2) {
		$pointY = $doc->newPage();
	}
}

$doc->setLineStyle(1);
$doc->line($doc->get("margin_left"), $pointY, $doc->get("page_width"), $pointY);
$pointY -= $doc->get("font_size") + $doc->get("font_padding_top");
$doc->addText($apointX["due_date"], $pointY, $doc->get("font_size"), "<b>Total:</b>");
$doc->addText($apointX["amount"] - $doc->getTextWidth($doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>"), $pointY, $doc->get("font_size"), "<b>".number_format($total, 2, ",", ".")."</b>");
$pointY -= $doc->get("font_padding_bottom");
$doc->line($apointX["due_date"], $pointY, $doc->get("page_width"), $pointY);

$parameter = array(
	"contact" => $reminder->contact,
	"payment_text" => "Rykker kunde#".$reminder->contact->get("number"),
	"amount" => $total,
	"due_date" => $reminder->get("dk_due_date"),
	"girocode" => $reminder->get("girocode"));


$pointY = $doc->addPaymentCondition($pointY, $reminder->get("payment_method_id"), $parameter);

$doc->stream();
*/
//$data = $doc->output();
//$filename = "faktura.pdf";
//$doc->writeDocument($data, $filename);
// header("location: files/".$filename);

?>
