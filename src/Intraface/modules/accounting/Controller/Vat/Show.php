<?php
/**
 * Guide to calculate VAT
 *
 * @todo Der kunne skrives en advarsel, hvis man ikke har sat eu-konti mv.
 *
 * @author Lars Olesen <lars@legestue.net>
 *
 */
class Intraface_modules_accounting_Controller_Vat_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getVatPeriod()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function renderHtml()
    {
     	$vat_period = $this->getVatPeriod();
       	$vat_period->loadAmounts();
       	$account_vat_in = $vat_period->get('account_vat_in');
       	$account_vat_out = $vat_period->get('account_vat_out');
       	$account_vat_abroad = $vat_period->get('account_vat_abroad');
       	$saldo_rubrik_a = $vat_period->get('saldo_rubrik_a');
       	$saldo_total = $vat_period->get('saldo_total');

       	$data = array(
       	    'vat_period' => $vat_period,
       	    'account_vat_in' => $account_vat_in,
       	    'account_vat_out' => $account_vat_out,
       	    'account_vat_abroad' => $account_vat_abroad,
       	    'saldo_rubrik_a' => $saldo_rubrik_a,
       	    'saldo_total' => $saldo_total
       	);

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/vat/show');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $vat_period = $this->getVatPeriod();
        if ($vat_period->state($this->body('date'), $this->body('voucher_number'))) {
            return new k_SeeOther($this->url(null, array('flare' => 'VAT period has been stated')));
        }
        throw new Exception('Could not state');
    }

    function getError()
    {
        return $error = new Intraface_Error;
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function getVoucher()
    {
        return new Voucher($this->getYear());
    }

    function getVatPeriod()
    {
        return $vat_period = new VatPeriod($this->getYear(), $this->name());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
