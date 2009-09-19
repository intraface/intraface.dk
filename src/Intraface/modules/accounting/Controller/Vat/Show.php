<?php
/**
 * Momsafregning
 *
 * Denne side skal være en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anført på momskonti.
 *
 * Når man klikker på angiv moms skal tallene gemmes i en database. *
 * Hvis man vil redigere tallene, klikker man sig hen til vat_edit.php
 *
 * Siden skal regne ud, om der er forskel på de tal, der er blevet
 * opgivet og det der rent faktisk skulle være opgivet, så man kan fange
 * evt. fejl næste gang man skal opgive moms.
 *
 * Primosaldoer skal naturligvis fremgå af momsopgørelsen.
 *
 * Der skal være en liste med momsangivelsesperioder for året,
 * og så skal der ud for hver momssopgivelse være et link enten til
 * den tidligere opgivne moms eller til at oprette en momsangivelse.
 *
 * @todo Der kunne skrives en advarsel, hvis man ikke har sat eu-konti mv.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
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

    function renderHtml()
    {
        $module = $kernel->module('accounting');
        $translation = $kernel->getTranslation('accounting');

        $error = new Intraface_Error;

        $year = new Year($kernel);
        $year->checkYear();

        $voucher = new Voucher($year);

    }

    function postForm()
    {
        /*
        if (!empty($_POST['get_amounts']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        	$vat_period = new VatPeriod($year, $_POST['id']);
        	$vat_period->loadAmounts();
        	$account_vat_in = $vat_period->get('account_vat_in');
        	$account_vat_out = $vat_period->get('account_vat_out');
        	$account_vat_abroad = $vat_period->get('account_vat_abroad');
        	//$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
        	$saldo_total = $vat_period->get('saldo_total');

        	$amount = array(
        		'vat_out' => $account_vat_out->get('saldo'),
        		'vat_abroad' => $account_vat_abroad->get('saldo'),
        		'vat_in' => $account_vat_in->get('saldo')
        	);

        	//$vat_period->saveAmounts($amount);
        	header('Location: vat_view.php?id='.$vat_period->get('id'));
        	exit;
        }
        */
        if (!empty($_POST['state']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        	$vat_period = new VatPeriod($year, $_POST['id']);

        	if (!$vat_period->state($_POST['date'], $_POST['voucher_number'])) {
        		trigger_error('Kunne ikke bogføre beløbene', E_USER_ERROR);
        	}

        	header('Location: vat_view.php?id='.$vat_period->get('id'));
        	exit;
        }
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/vat/show.tpl.php');
        return $smarty->render($this);
    }

    function GET()
    {
        if (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
        	$vat_period = new VatPeriod($year, $_GET['id']);
        	$vat_period->loadAmounts();
        	$account_vat_in = $vat_period->get('account_vat_in');
        	$account_vat_out = $vat_period->get('account_vat_out');
        	$account_vat_abroad = $vat_period->get('account_vat_abroad');
        	$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
        	$saldo_total = $vat_period->get('saldo_total');
        }

        parent::GET();
    }
}