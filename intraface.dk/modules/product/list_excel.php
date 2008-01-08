<?php
require '../../include_first.php';

$kernel->module('product');
$kernel->useModule('stock');

$product = new Product($kernel);
$product->createDBQuery();
$products = $product->getList();
$translation = $kernel->getTranslation('product');



$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send($kernel->intranet->get('name') . ' - products.xls');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('Products');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();
$format_bold->setSize(8);

$format_italic =& $workbook->addFormat();
$format_italic->setItalic();
$format_italic->setSize(8);

$format =& $workbook->addFormat();
$format->setSize(8);

$worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);
$i += 2;
$worksheet->write($i, 0, 'Products', $format_bold);

$i += 2;
foreach ($products AS $p) {

    $worksheet->write($i, 0, $p['number'], $style);
    $worksheet->write($i, 1, $p['name'], $style);
    $worksheet->write($i, 2, $translation->get($p['unit']['combined']), $style);
    $worksheet->write($i, 3, $p['price'], $style);
    if($p['stock'] != 0) {
        $worksheet->write($i, 3, $p['stock_status']['for_sale'], $style);
    }
    $i++;

}

$worksheet->hideGridLines();
$workbook->close();
exit;
