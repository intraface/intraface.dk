<?php
/**
 * Momsafregning
 *
 * Denne side skal være en guide til at afregne moms.
 * Siden tager automatisk alle de poster, der er anført på momskonti.
 *
 * Når man klikker på angiv moms skal tallene gemmes i en database.
 *
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

        $allowed_periods = $module->getSetting('vat_periods');

        $year = new Year($kernel);
        $year->checkYear();

        $vat_period = new VatPeriod($year);

        $periods = $vat_period->getList();
        $post = new Post(new Voucher($year));

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/vat/period.tpl.php');
        return $smarty->render($this);
    }

    function renderForm()
    {
    	if (isset($_POST['vat_period_key'])) {
    		$year->setSetting('vat_period', $_POST['vat_period_key']);
    	}
    	$vat_period = new VatPeriod($year);
    	$vat_period->createPeriods();
    	header('Location: vat_period.php');
    	exit;
    }

    function GET()
    {
        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $vat_period = new VatPeriod($year, $_GET['delete']);
            $vat_period->delete();
        }

        parent::GET();
    }
}