<?php
require('../../include_first.php');
require('Spreadsheet/Excel/Writer.php');

$translation = $kernel->getTranslation('debtor');
$debtor_module = $kernel->module('debtor');

if (empty($_GET['id'])) $_GET['id'] = '';
if (empty($_GET['type'])) $_GET['type'] = '';

$debtor = Debtor::factory($kernel, intval($_GET["id"]), $_GET["type"]);
$debtor->getDbQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");

//$db = new DB_sql;

$posts = $debtor->getList();

// spreadsheet
$workbook = new Spreadsheet_Excel_Writer();

$workbook->send('debtor.xls');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();
$format_bold->setSize(8);

$format_italic =& $workbook->addFormat();
$format_italic->setItalic();
$format_italic->setSize(8);

$format =& $workbook->addFormat();
$format->setSize(8);

// Creating a worksheet
$worksheet =& $workbook->addWorksheet(ucfirst($translation->get('title')));

$i = 1;
$worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);
$i++;

$status_types = array(
    -3 => 'Afskrevet',
    -2 => 'Åbne',
    -1 => 'Alle',
    0 => 'Oprettet',
    1 => 'Sendt',
    2 => 'Afsluttet',
    3 => 'Annulleret');

$worksheet->write($i, 0, 'Status', $format_italic);
$worksheet->write($i, 1, $status_types[$debtor->getDbQuery()->getFilter('status')], $format_italic);
$i++;

$worksheet->write($i, 0, 'Søgetekst', $format_italic);
$worksheet->write($i, 1, $debtor->getDbQuery()->getFilter('text'), $format_italic);
$i++;

if ($debtor->getDbQuery()->checkFilter('product_id')) {
    $product = new Product($kernel, $debtor->getDbQuery()->getFilter('product_id'));

    $worksheet->write($i, 0, 'Produkt', $format_italic);
    $worksheet->write($i, 1, $product->get('name'), $format_italic);
    $i++;
}

if ($debtor->getDbQuery()->checkFilter('contact_id')) {
    $contact = new Contact($kernel, $debtor->getDbQuery()->getFilter('contact_id'));

    $worksheet->write($i, 0, 'Kontakt', $format_italic);
    $worksheet->write($i, 1, $contact->address->get('name'), $format_italic);
    $i++;
}


$worksheet->write($i, 0, "Antal i søgningen", $format_italic);
$worksheet->write($i, 1, count($posts), $format_italic);
$i++;

$i++;
$worksheet->write($i, 0, 'Nummer', $format_bold);
$worksheet->write($i, 1, 'Kontakt nummer', $format_bold);
$worksheet->write($i, 2, 'Kontakt navn', $format_bold);
$worksheet->write($i, 3, 'Beskrivelse', $format_bold);
$worksheet->write($i, 4, 'Beløb', $format_bold);
$worksheet->write($i, 5, 'Oprettet', $format_bold);
$worksheet->write($i, 6, 'Sendt', $format_bold);
//$worksheet->write($i, 7, $translation->get("due_date"), $format_bold);
$c = 8;
if ($debtor->get('type') == 'invoice') {
    $worksheet->write($i, $c, 'Forfaldsbeløb', $format_bold);
    $c++;
}
$worksheet->write($i, $c, 'Kontaktnøgleord', $format_bold);
$c++;

if (!empty($product) && is_object($product) && get_class($product) == 'product') {
    $worksheet->write($i, $c, 'Antal valgte produkt', $format_bold);
    $c++;
}


$i++;

$due_total = 0;
$sent_total = 0;
$total = 0;

if (count($posts) > 0) {
    for ($j = 0, $max = count($posts); $j < $max; $j++) {

        if ($posts[$j]["due_date"] < date("Y-m-d") && ($posts[$j]["status"] == "created" OR $posts[$j]["status"] == "sent")) {
            $due_total += $posts[$i]["total"];
        }
        if ($posts[$j]["status"] == "sent") {
            $sent_total += $posts[$j]["total"];
        }
        $total += $posts[$j]["total"];

        $worksheet->write($i, 0, $posts[$j]["number"]);
        $worksheet->write($i, 1, $posts[$j]['contact']['number']);
        $worksheet->write($i, 2, $posts[$j]["name"]);
        $worksheet->write($i, 3, $posts[$j]["description"]);
        $worksheet->write($i, 4, number_format($posts[$j]["total"], 2, ",","."));
        $worksheet->write($i, 5, $posts[$j]["dk_this_date"]);

        if ($posts[$j]["status"] != "created") {
            $worksheet->write($i, 6, $posts[$j]["dk_date_sent"]);
        }
        else {
            $worksheet->write($i, 6, "Nej");
        }

        if ($posts[$j]["status"] == "executed" || $posts[$j]["status"] == "canceled") {
            $worksheet->write($i, 7, $translation->get($posts[$j]["status"], 'debtor'));
        }
        else {
            $worksheet->write($i, 7, $posts[$j]["dk_due_date"]);
        }
        $c = 8;
        if ($debtor->get('type') == 'invoice') {
            $worksheet->write($i, $c, $posts[$j]['arrears']);
            $c++;
        }

        $keywords = array();
        $contact = new Contact($kernel, $posts[$j]['contact']['id']);
        $appender = $contact->getKeywordAppender();
        $keyword_ids = $appender->getConnectedKeywords();
        if (count($keyword_ids) > 0) {
            foreach ($keyword_ids AS $keyword_id) {
                $keyword = new Keyword($contact, $keyword_id);
                $keywords[] = $keyword->getKeyword();
            }
            $worksheet->write($i, $c, implode(', ', $keywords));
            $c++;
        }

        if (!empty($product) && is_object($product) && get_class($product) == 'product') {
            $quantity_product = 0;
            if (count($posts[$j]['items']) > 0) {
                foreach ($posts[$j]['items'] AS $item) {
                    if ($item['product_id'] == $product->get('id')) {
                        $quantity_product += $item['quantity'];
                    }
                }
            }
            $worksheet->write($i, $c, $quantity_product);
            $c++;
        }

        $i++;

    }
}


$i++;
$i++;

$worksheet->write($i, 0, 'Forfaldne', $format_italic);
$worksheet->write($i, 1, number_format($due_total, 2, ",","."), $format_italic);
$i++;

$worksheet->write($i, 0, 'Udestående (sendt):', $format_italic);
$worksheet->write($i, 1, number_format($sent_total, 2, ",","."), $format_italic);
$i++;

$worksheet->write($i, 0, 'Total:', $format_italic);
$worksheet->write($i, 1, number_format($total, 2, ",","."), $format_italic);
$i++;


$worksheet->hideGridLines();

$workbook->close();

exit;
?>