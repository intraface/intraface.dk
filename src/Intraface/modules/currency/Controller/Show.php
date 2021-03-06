<?php
class Intraface_modules_currency_Controller_Show extends k_Component
{
    protected $doctrine;

    function __construct(Doctrine_Connection_Common $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function map($name)
    {
        if ($name == 'exchangerate') {
            return 'Intraface_modules_currency_Controller_ExchangeRate_Index';
        }
    }

    function renderHtml()
    {
        return 'No content';
    }

    /**
     * Returns translations object
     *
     * @return object Translation
     */
    public function getTranslation()
    {
        return $this->context->getTranslation();
    }

    public function getCurrency()
    {
        $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
        $currency = $gateway->findById($this->name());
        if ($currency === false) {
            throw new Exception('Invalid currency '.$this->name());
        }
        return $currency;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
