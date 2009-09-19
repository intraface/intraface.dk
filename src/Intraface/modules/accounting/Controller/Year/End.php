class Intraface_modules_accounting_Controller_Account_Show extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

	function POST()
	{
        // disse gør det muligt let at skifte mellem trinene
        if (!empty($_POST['previous'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step'] - 2);

        }
        elseif (!empty($_POST['next'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step']);
        }

        // her reagerer vi på de forskellige trin
        if (!empty($_POST['step_save_result'])) {
        	$year_end = new YearEnd($year);

        	if (!$year_end->saveStatement('operating')) {
        		trigger_error('Kunne ikke gemme resultatopgørelsen', E_USER_ERROR);
        	}
        	// her skal den så gemme resultatopgørelsen.

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
        		trigger_error('Kunne ikke nulstille årets resultat', E_USER_ERROR);
        	}

        	$year_end->setStep($_POST['step']);
        }
        elseif (!empty($_POST['step_reverse_result_account_reset'])) {
        	$year_end = new YearEnd($year);
        	if (!$year_end->resetYearResult('reverse')) {
        		echo $year_end->error->view();
        		trigger_error('Kunne ikke tilbageføre årets resultat årets resultat', E_USER_ERROR);
        	}
        	$year_end->setStep($_POST['step'] - 1);
        }


        // step 1
        elseif (!empty($_POST['step_things_stated'])) {
        	$year_end = new YearEnd($year);
        	$year_end->setStep($_POST['step']);
        }

        // overførsel af årsopgørelsen
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
	}

	function renderHtml()
	{
        $accounting_module = $kernel->module('accounting');
        $accounting_module->includeFile('YearEnd.php');
        $translation = $kernel->getTranslation('accounting');

        $year = new Year($kernel);
        $year->checkYear();


        $account = new Account($year);
        $year_end = new YearEnd($year);
        $post = new Post(new Voucher($year));
        $vat_period = new VatPeriod($year);

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/end.tpl.php');
        return $smarty->render($this);
	}
}