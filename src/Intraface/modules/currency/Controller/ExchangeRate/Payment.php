<?php
class Intraface_modules_currency_Controller_ExchangeRate_Payment extends k_Component
{

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
}
