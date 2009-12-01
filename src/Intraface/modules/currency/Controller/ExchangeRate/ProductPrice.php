<?php
class Intraface_modules_currency_Controller_ExchangeRate_ProductPrice extends k_Component
{
    public function getCurrency()
    {
        return $this->context->getCurrency();
    }

    function renderHtml()
    {
        return 'Intentionally left blank';
    }

    function map($name)
    {
        if ($name == 'update') {
            return 'Intraface_modules_currency_Controller_ExchangeRate_Update';
        }
    }

    function getTranslation()
    {
        return $this->getKernel()->getTranslation('currency');
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}