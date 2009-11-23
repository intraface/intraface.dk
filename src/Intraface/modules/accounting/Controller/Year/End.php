<?php
class Intraface_modules_accounting_Controller_Year_End extends k_Component
{
    function getAccount()
    {
        $year = $this->getYear();
        return $account = new Account($year);
    }

    function t($phrase)
    {
        return $phrase;
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
        		trigger_error('Kunne ikke gemme resultatopg�relsen', E_USER_ERROR);
        	}
        	// her skal den s� gemme resultatopg�relsen.

        	$year_end->setStep($_POST['step']);
        }

        elseif (!empty($_POST['step_save_balance'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->saveStatement('balance')) {
        		trigger_error('Kunne ikke gemme balancen', E_USER_ERROR);
        	}

        	$year_end->setStep($_POST['step']);
        }
        elseif (!empty($_POST['step_transfer_result'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->resetYearResult()) {
        		trigger_error('Kunne ikke nulstille �rets resultat', E_USER_ERROR);
        	}

        	$year_end->setStep($_POST['step']);
        }
        elseif (!empty($_POST['step_reverse_result_account_reset'])) {
        	$year_end = new YearEnd($year);
        	if (!$year_end->resetYearResult('reverse')) {
        		echo $year_end->error->view();
        		trigger_error('Kunne ikke tilbagef�re �rets resultat �rets resultat', E_USER_ERROR);
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
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year);
        $year_end = new YearEnd($year);
        $post = new Post(new Voucher($year));
        $vat_period = new VatPeriod($year);

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/end.tpl.php');
        return $smarty->render($this);
	}

	function getPost()
	{
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year);
        $year_end = new YearEnd($year);
        return $post = new Post(new Voucher($year));
	}

	function getVatPeriod()
	{
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year);
        $year_end = new YearEnd($year);
        $post = new Post(new Voucher($year));
        return $vat_period = new VatPeriod($year);
	}

	function getYearEnd()
	{
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year);
        return $year_end = new YearEnd($year);
	}

	function getYear()
	{
	    $year = new Year($this->getKernel());
        $year->checkYear();
	    return $year;
	}

	function getKernel()
	{
	    return $this->context->getKernel();
	}
}