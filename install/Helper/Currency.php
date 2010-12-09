<?php
class Install_Helper_Currency
{
    private $kernel;
    private $db;

    public function __construct($kernel, $db)
    {
        $this->kernel = $kernel;
        $this->db = $db;
        Intraface_Doctrine_Intranet::singleton(1);
    }

    public function create()
    {
        $currency = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency->setType($type->getByIsoCode('EUR'));
        $currency->save();

        return $currency;
    }

    public function createWithExchangeRates()
    {
        $currency = $this->create();

        $product_price = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $product_price->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $product_price->setCurrency($currency);
        $product_price->save();

        $payment = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        $payment->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $payment->setCurrency($currency);
        $payment->save();

        return $currency;
    }
}