<?php
class Intraface_modules_accounting_Controller_Year_End extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

	function POST()
	{
        $year = $this->getYear();
        $account = new Account($year);
        $year_end = new YearEnd($year);
        $post = new Post(new Voucher($year));
        $vat_period = new VatPeriod($year);

	    // disse g�r det muligt let at skifte mellem trinene
        if (!empty($_POST['previous'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step'] - 2);

        }
        elseif (!empty($_POST['next'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step']);
        }

        // her reagerer vi p� de forskellige trin
        if (!empty($_POST['step_save_result'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->saveStatement('operating')) {
        		throw new Exception('Kunne ikke gemme resultatopgørelsen');
        	}
        	// her skal den s� gemme resultatopg�relsen.

        	$year_end->setStep($_POST['step']);
        }

        elseif (!empty($_POST['step_save_balance'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->saveStatement('balance')) {
        		throw new Exception('Kunne ikke gemme balancen');
        	}

        	$year_end->setStep($_POST['step']);
        }
        elseif (!empty($_POST['step_transfer_result'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->resetYearResult()) {
        		throw new Exception('Kunne ikke nulstille årets resultat');
        	}

        	$year_end->setStep($_POST['step']);
        }
        elseif (!empty($_POST['step_reverse_result_account_reset'])) {
        	$year_end = new YearEnd($year);
        	if (!$year_end->resetYearResult('reverse')) {
        		echo $year_end->error->view();
        		throw new Exception('Kunne ikke tilbageføre årets resultat');
        	}
        	$year_end->setStep($_POST['step'] - 1);
        }

        // step 1
        elseif (!empty($_POST['step_things_stated'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step']);
        }

        // overf�rsel af �rsopg�relsen
        elseif (!empty($_POST['step_result'])) {
        	$year_end = new YearEnd($year);
        	$account = new Account($year);
        	$year->setSetting('result_account_id', $_POST['result_account_id']);

        	if ($year_end->resetOperatingAccounts()) {
        		$year_end->setStep($_POST['step']);
        	} else {
        		echo $year_end->error->view();
        	}
        } elseif (!empty($_POST['step_lock_year'])) {
        	if (!empty($_POST['lock']) AND $_POST['lock'] == '1') {
        		$year->lock();
        	}
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step']);
        } elseif (!empty($_POST['step_reverse_result_reset'])) {
        	$year_end = new YearEnd($year);
        	$year_end->resetOperatingAccounts('reverse');
        	$year_end->setStep($_POST['step'] - 1);
        }

        return $this->render();
	}

	function renderHtml()
	{
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/end');
        return $smarty->render($this);
	}

    function renderXls()
    {
    	$module = $this->getKernel()->module('accounting');
        $module->includeFile('YearEnd.php');

        $kernel = $this->getKernel();

        $year = $this->getYear();

        $year_end = new YearEnd($year);

        $workbook = new Spreadsheet_Excel_Writer();

        // sending HTTP headers
        $workbook->send($kernel->intranet->get('name') . ' - konti ' . $year->get('label') . '.xls');

        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet('Konti ' . $year->get('label'));
        $worksheet->setInputEncoding('UTF-8');

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

    }

    function getAccount()
    {
        $year = $this->getYear();
        return $account = new Account($year);
    }

	function getPost()
	{
        return $post = new Post(new Voucher($this->getYear()));
	}

	function getVatPeriod()
	{
        return $vat_period = new VatPeriod($this->getYear());
	}

	function getYearEnd()
	{
        return $year_end = new YearEnd($this->getYear());
	}

	function getYear()
	{
	    return $this->context->getYear();
	}

	function getKernel()
	{
	    return $this->context->getKernel();
	}
}