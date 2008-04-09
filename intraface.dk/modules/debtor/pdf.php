<?php
require('../../include_first.php');

$debtor_module = $kernel->module("debtor");
$kernel->useModule("contact");
$kernel->useModule("product");
$translation = $kernel->getTranslation('debtor');

$debtor = Debtor::factory($kernel, intval($_GET["id"]));

if($debtor->get('id') == 0) {
    trigger_error('Cannot create pdf from debtor without valid id', E_USER_ERROR);
}

if (!empty($_GET['format'])) {
	$format = $_GET['format'];
}
else {
	$format = 'pdf';
}

if(($debtor->get("type") == "order" || $debtor->get("type") == "invoice") && $kernel->intranet->hasModuleAccess('onlinepayment')) {
    $kernel->useModule('onlinepayment', true); // true: ignore_user_access
    $onlinepayment = OnlinePayment::factory($kernel);
}
else {
    $onlinepayment = NULL;
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
	case 'pdf':
    default:
		
        require_once 'Intraface/modules/debtor/Visitor/Pdf.php';

        if($kernel->intranet->get("pdf_header_file_id") != 0) {
            $kernel->useShared('filehandler');
            $filehandler = new FileHandler($kernel, $kernel->intranet->get("pdf_header_file_id"));
        }
        else {
            $filehandler = NULL;
        }

        $report = new Debtor_Report_Pdf($translation, $filehandler);
        $report->visit($debtor, $onlinepayment);
        $report->output('stream');
    break;
}
exit;

?>

