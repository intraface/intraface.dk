<?php
require('../../include_first.php');
require('Spreadsheet/Excel/Writer.php');

$module = $kernel->module('accounting');

$year = new Year($kernel);
$year->checkYear();

$db = new DB_Sql;
$db->query("SELECT * FROM accounting_voucher WHERE intranet_id = " . $year->kernel->intranet->get('id') . " AND year_id = " . $year->get('id') . " ORDER BY voucher_number ASC");
//$i++;
$posts = array();
while ($db->nextRecord()) {
    $voucher = new Voucher($year, $db->f('id'));
    $posts = array_merge($voucher->getPosts(), $posts);
    //$i++;
}

$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send($kernel->intranet->get('name') . ' - poster ' . $year->get('label'));

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('Konti ' . $year->get('label'));

$format_bold =& $workbook->addFormat();
$format_bold->setBold();
$format_bold->setSize(8);

$format_italic =& $workbook->addFormat();
$format_italic->setItalic();
$format_italic->setSize(8);

$format =& $workbook->addFormat();
$format->setSize(8);

$worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);

$i = 2;
if (count($posts) > 0) {
    foreach ($posts AS $post) {
        $worksheet->write($i, 0, $post['date_dk'], $style);
        $worksheet->write($i, 1, $post['voucher_number'], $style);
        $worksheet->write($i, 2, $post['account_number'], $style);
        $worksheet->write($i, 3, $post['account_name'], $style);
        $worksheet->write($i, 4, round($post['debet'], 2), $style);
        $worksheet->write($i, 5, round($post['credit'], 2), $style);
        $i++;
    }
}
$worksheet->hideGridLines();

// Let's send the file
$workbook->close();

exit;
?>