<?php
/**
 * Guide to calculate VAT
 *
 * Register VAT should persist values in database.
 * 
 * Should be able to calculate whether there is a difference between what has
 * been stated and what should have been stated to make it less error prone.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
class Intraface_modules_accounting_Controller_Vat_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_accounting_Controller_Vat_Show';
        }
    }

    function renderHtml()
    {
        $year = new Year($this->getKernel());
        $year->checkYear();

        $vat_period = new VatPeriod($year);

        $periods = $vat_period->getList();
        $post = new Post(new Voucher($year));

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/vat/period');
        return $smarty->render($this);
    }

    function getPeriods()
    {
        $vat_period = new VatPeriod($this->getYear());

        return $periods = $vat_period->getList();
    }

    function getAllowedPeriods()
    {
        return $allowed_periods = $this->getKernel()->getModule('accounting')->getSetting('vat_periods');
    }

    function getPost()
    {
        return $post = new Post(new Voucher($this->getYear()));
    }

    function getVatPeriod()
    {
        return new VatPeriod($this->getYear());
    }

    function postForm()
    {
    	if (isset($_POST['vat_period_key'])) {
    		$this->getYear()->setSetting('vat_period', $_POST['vat_period_key']);
    	}
    	$vat_period = new VatPeriod($this->getYear());
    	$vat_period->createPeriods();

    	return new k_SeeOther($this->url(null));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function GET()
    {
        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $vat_period = new VatPeriod($year, $_GET['delete']);
            $vat_period->delete();
        }

        return parent::GET();
    }
}
