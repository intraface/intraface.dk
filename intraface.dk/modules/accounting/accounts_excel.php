<?php
require('../../include_first.php');
require('Spreadsheet/Excel/Writer.php');

$module = $kernel->module('accounting');

$year = new Year($kernel);
$year->checkYear();

$account = new Account($year);

$values['from_date'] = $year->get('from_date_dk');
$values['to_date'] = $year->get('to_date_dk');

$accounts = $account->getList('stated', true);

$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send($kernel->intranet->get('name') . ' - konti ' . $year->get('label'));

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

$i = 0;
$worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);

$i = 2;
if (count($accounts) > 0) {
	foreach ($accounts AS $account) {
		$style = '';
		if ($account['type'] == 'headline') {
			$style = $format_bold;
		}
		elseif ($account['type'] == 'sum') {
			$style = $format_italic;
		}
		else {
			$style = $format;
		}

		$worksheet->write($i, 0, $account['number'], $style);
		$worksheet->write($i, 1, $account['name'], $style);
		$worksheet->write($i, 2, $account['type'], $style);
		if ($account['type'] != 'Headline') {
			$worksheet->write($i, 3, abs(round($account['saldo'])), $style);
		}
		$i++;
	}


}
$worksheet->hideGridLines();

// Let's send the file
$workbook->close();

exit;
?>
