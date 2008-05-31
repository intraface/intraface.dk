<?php
require('../../include_first.php');

$module = $kernel->module('contact');
$pdf_shared = $kernel->useShared('pdf');

$contact = new Contact($kernel);
$keyword = $contact->getKeywords();
$keywords = $keyword->getAllKeywords();
$contact->createDBQuery();
$contact->dbquery->defineCharacter('character', 'address.name');
$contact->dbquery->storeResult('use_stored', 'contact', 'toplevel');
$contacts = $contact->getList("use_address");

$doc = new PdfMaker($kernel);

$doc->setValue('font_size', 10);

switch($kernel->setting->get("user", "label")) {
	case 1:
		// 2x8 labels pr. ark
		$doc->setValue('margin_top', 0); // x/2,83 = mm
		$doc->setValue('margin_right', 0);
		$doc->setValue('margin_bottom', 0);
		$doc->setValue('margin_left', 0);
		$doc->load();

		$label_width = ceil(($doc->page_width - $doc->get('margin_right') - $doc->get('margin_left'))/2);
		$label_height = ceil(($doc->page_height - $doc->get('margin_top') - $doc->get('margin_bottom'))/8);

		$label_padding_left = 42;
		$label_padding_top = 28;
		break;
	default:
		// (case: 0)
		// 3x7 labels pr. A4 ark
		$doc->setValue('margin_top', 42); // x/2,83 = mm
		$doc->setValue('margin_right', 19);
		$doc->setValue('margin_bottom', 42);
		$doc->setValue('margin_left', 19);
		$doc->load();


		$label_width = ceil(($doc->page_width - $doc->get('margin_right') - $doc->get('margin_left'))/3);
		$label_height = ceil(($doc->page_height - $doc->get('margin_top') - $doc->get('margin_bottom'))/7);

		$label_padding_left = 14;
		$label_padding_top = 14;
		break;
}


$doc->start();

$validator = new Intraface_Validator(new Intraface_Error);

$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top , $doc->get('font_size'), "<b>Søgning</b>");
$line = 1;
if($contact->dbquery->getFilter('search') != "") {
	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing'), $doc->get('font_size'), "Søgetekst: ".$contact->dbquery->getFilter('search'));
	$line++;
}

$keyword_ids = $contact->dbquery->getKeyword();
if(is_array($keyword_ids) && count($keyword_ids) > 0) {

	$used_keyword = array();

	foreach($keyword_ids AS $kid) {
		foreach($keywords AS $k){
			if($k['id'] == $kid) {
				$used_keyword[] = $k['keyword'];
			}
		}
	}

	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing') * $line, $doc->get('font_size'), "Nøgleord: ".implode(", ", $used_keyword));
	$line++;
}
$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing') * $line, $doc->get('font_size'), "Antal labels i søgning: ".count($contacts));


for($i = 0, $max = count($contacts); $i < $max; $i++) {

	/*
	if($validator->isEmail($subscribers[$i]['contact_email'], "")) {
		// Hvis de har en mail, kører vi videre med næste.
		CONTINUE;
	}
	*/
	// TODO -- hvorfor bruger vi ikke antallet af labels til at vide, hvornår
	// vi skifter linje?
	if($doc->get('x') + $label_width  > $doc->get('right_margin_position')) {
		// For enden af linjen, ny linje
		$doc->setY("-".$label_height);
		$doc->setX(0);

	}
	else {
		// Vi rykker en label til højre
		$doc->setX("+".$label_width);
	}


	if($doc->get('y') - $label_height < $doc->get('margin_bottom')) {
		// Hvis næste labelsrække ikke kan nå at være der tager vi en ny side.
		$doc->newPage();
		$doc->setX(0);
		$doc->setY(0);
	}

	// print("X".$doc->get('x')."Y".$doc->get('y'));


	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top , $doc->get('font_size'), "<b>".$contacts[$i]['number']."</b>");
	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing'), $doc->get('font_size'), "<b>".$contacts[$i]['name']."</b>");
	$line = 2;
	$address_lines = explode("\n", $contacts[$i]['address']['address']);
	foreach($address_lines AS $l) {
		if(trim($l) != "") {
			$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing') * $line, $doc->get('font_size'), $l);
			$line++;
		}
	}
	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing') * $line, $doc->get('font_size'), $contacts[$i]['address']['postcode']." ".$contacts[$i]['address']['city']);
	$line++;
	$doc->addText($doc->get('x') + $label_padding_left, $doc->get('y') - $label_padding_top - $doc->get('font_spacing') * $line, $doc->get('font_size'), $contacts[$i]['address']['country']);


}

$doc->stream();

?>