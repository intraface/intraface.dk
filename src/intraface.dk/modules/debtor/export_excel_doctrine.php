<?php
ini_set('memory_limit', '56M');

require '../../include_first.php';
require 'Spreadsheet/Excel/Writer.php';

$translation = $kernel->getTranslation('debtor');
$debtor_module = $kernel->module('debtor');

if (empty($_GET['id'])) $_GET['id'] = '';
if (empty($_GET['type'])) $_GET['type'] = '';

$debtor = Debtor::factory($kernel, intval($_GET["id"]), $_GET["type"]);
$dbquery = $debtor->getDbQuery();
$type = $debtor->get('type');
unset($debtor);

$dbquery->storeResult("use_stored", $type, "toplevel");
$dbquery->loadStored();

$gateway = new Intraface_modules_debtor_DebtorDoctrineGateway(Doctrine_Manager::connection(), $kernel->user);
// echo number_format(memory_get_usage())." After gateway initializd<br />"; die;
$posts = $gateway->findByDbQuerySearch($dbquery);

/*
echo '<pre>';
var_dump($posts->getFirst()->toArray()); die();
print_r($posts->toArray(true));
echo '</pre>';
echo number_format(memory_get_usage())." After gateway initializd<br />"; die;
*/

// spreadsheet
$workbook = new Spreadsheet_Excel_Writer();

$workbook->send('debtor.xls');

$format_bold = $workbook->addFormat();
$format_bold->setBold();
$format_bold->setSize(8);

$format_italic = $workbook->addFormat();
$format_italic->setItalic();
$format_italic->setSize(8);

$format = $workbook->addFormat();
$format->setSize(8);

// Creating a worksheet
$worksheet = $workbook->addWorksheet(ucfirst(__('title')));

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
$worksheet->write($i, 1, $status_types[$dbquery->getFilter('status')], $format_italic);
$i++;

$worksheet->write($i, 0, 'Søgetekst', $format_italic);
$worksheet->write($i, 1, $dbquery->getFilter('text'), $format_italic);
$i++;

if ($dbquery->checkFilter('product_id')) {
    $product = new Product($kernel, $dbquery->getFilter('product_id'));

    $worksheet->write($i, 0, 'Produkt', $format_italic);
    $worksheet->write($i, 1, $product->get('name'), $format_italic);
    $i++;
}

if ($dbquery->checkFilter('contact_id')) {
    $contact = new Contact($kernel, $dbquery->getFilter('contact_id'));

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
//$worksheet->write($i, 7, __("due_date"), $format_bold);
$c = 8;
if ($type == 'invoice') {
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
    foreach($posts AS $debtor) {

        if (strtotime($debtor->getDueDate()->getAsIso()) < time() && ($debtor->getStatus() == "created" OR $debtor->getStatus() == "sent")) {
            $due_total += $debtor->getTotal()->getAsIso(2);
        }
        if ($debtor->getStatus() == "sent") {
            $sent_total += $debtor->getTotal()->getAsIso(2);
        }
        $total += $debtor->getTotal()->getAsIso(2);

        /**
         * @todo this could be done with Doctrine, but this seems only to have minimal memory usage
         */
        $contact = new Contact($kernel, $debtor->contact_id);
        $worksheet->write($i, 0, $debtor->getNumber());
        $worksheet->write($i, 1, $contact->get('number')); // $posts[$j]['contact']['number']
        $worksheet->write($i, 2, $contact->get('name')); 
        $worksheet->write($i, 3, $debtor->getDescription());
        $worksheet->writeNumber($i, 4, $debtor->getTotal()->getAsIso());
        $worksheet->write($i, 5, $debtor->getDebtorDate()->getAsLocal('da_DK'));

        if ($debtor->getStatus() != "created") {
            $worksheet->write($i, 6, $debtor->getDateSent()->getAsLocal('da_DK'));
        } else {
            $worksheet->write($i, 6, "Nej");
        }

        if ($debtor->getStatus() == "executed" || $debtor->getStatus() == "canceled") {
            $worksheet->write($i, 7, __($debtor->getStatus(), 'debtor'));
        } else {
            $worksheet->write($i, 7, $debtor->getDueDate()->getAsLocal('da_DK'));
        }
        $c = 8;
        if ($type == 'invoice') {
            $worksheet->write($i, $c, '-'); // $posts[$j]['arrears']
            $c++;
        }

        /*
        // not implemented
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
        }*/

        /*
        // not implemented
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
        }*/

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

// $worksheet->write($i, 0, number_format(memory_get_usage()));

$worksheet->hideGridLines();

$workbook->close();

exit;