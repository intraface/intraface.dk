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

?>

