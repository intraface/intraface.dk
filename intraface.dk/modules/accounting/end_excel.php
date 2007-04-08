<?php
require('../../include_first.php');
require('Spreadsheet/Excel/Writer.php');

$module = $kernel->module('accounting');
$module->includeFile('YearEnd.php');

$year = new Year($kernel);
$year->checkYear();

$year_end = new YearEnd($year);

$workbook = new Spreadsheet_Excel_Writer();

// sending HTTP headers
$workbook->send($kernel->intranet->get('name') . ' - konti ' . $year->get('label') . '.xls');

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
$i += 2;
$worksheet->write($i, 0, 'Resultatopgørelse', $format_bold);


$accounts = $year_end->getStatement('operating');

$i += 2;
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
		if ($account['type'] != 'headline') {
			$worksheet->write($i, 2, abs(round($account['saldo'])), $style);
		}
		$i++;
	}


}



$accounts = $year_end->getStatement('balance');
$i += 2;
$worksheet->write($i, 0, 'Balancen', $format_bold);

$i += 2;
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
		if ($account['type'] != 'headline') {
			$worksheet->write($i, 2, abs(round($account['saldo'])), $style);
		}
		$i++;
	}


}




$worksheet->hideGridLines();

// Let's send the file
$workbook->close();

exit;
?>
