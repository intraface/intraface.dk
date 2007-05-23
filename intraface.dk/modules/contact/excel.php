<?php
require('../../include_first.php');
require('Spreadsheet/Excel/Writer.php');

$_GET['use_stored'] = 'true';

$module = $kernel->module('contact');
$contact = new Contact($kernel);
$contact->createDBQuery();

$keyword = $contact->getKeywords();
$keywords = $keyword->getAllKeywords();

$contact->dbquery->defineCharacter('character', 'address.name');
$contact->dbquery->storeResult('use_stored', 'contact', 'toplevel');
$contacts = $contact->getList('use_address');

$keyword_ids = $contact->dbquery->getKeyword();

$used_keyword = array();

if(is_array($keyword_ids) && count($keyword_ids) > 0) {

    foreach($keyword_ids AS $kid) {
        foreach($keywords AS $k){
            if($k['id'] == $kid) {
                $used_keyword[] = $k['keyword'];
            }
        }
    }

}

$keywords = 'Nøgleord' . implode(' ', $used_keyword);
$search = 'Søgetekst' . $contact->dbquery->getFilter('search');
$count = 'Kontakter i søgning' . count($contacts);

$i = 1;

// spreadsheet
$workbook = new Spreadsheet_Excel_Writer();

$workbook->send('kontakter.xls');

$format_bold = $workbook->addFormat();
$format_bold->setBold();
$format_bold->setSize(8);

$format_italic = $workbook->addFormat();
$format_italic->setItalic();
$format_italic->setSize(8);

$format = $workbook->addFormat();
$format->setSize(8);

// Creating a worksheet
$worksheet = $workbook->addWorksheet('Kontakter');


$worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);
$i = $i + 1;
$worksheet->write($i, 0, $search, $format_italic);
$i = $i + 1;
$worksheet->write($i, 0, $keywords, $format_italic);
$i = $i + 1;
$worksheet->write($i, 0, $count, $format_italic);

$i = $i+2;
$worksheet->write($i, 0, 'Navn', $format_bold);
$worksheet->write($i, 1, 'Adresse', $format_bold);
$worksheet->write($i, 2, 'Postnummer', $format_bold);
$worksheet->write($i, 3, 'By', $format_bold);
$worksheet->write($i, 4, 'Telefon', $format_bold);
$worksheet->write($i, 5, 'Email', $format_bold);

$i++;

if (count($contacts) > 0) {
    foreach ($contacts AS $contact) {
        $worksheet->write($i, 0, $contact['name']);
        $worksheet->write($i, 1, $contact['address']['address']);
        $worksheet->write($i, 2, $contact['address']['postcode']);
        $worksheet->write($i, 3, $contact['address']['city']);
        $worksheet->write($i, 4, $contact['address']['phone']);
        $worksheet->write($i, 5, $contact['address']['email']);
        $i++;
    }

}
$worksheet->hideGridLines();

// Let's send the file


$workbook->close();

exit;


?>