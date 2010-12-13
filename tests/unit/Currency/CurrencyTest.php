<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class CurrencyTest extends PHPUnit_Framework_TestCase
{
    private $object = NULL;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE currency');
        $db->query('TRUNCATE currency_exchangerate');
    }

    ///////////////////////////////////////////////////////

    function testConstruct()
    {
        $object = new Intraface_modules_currency_Currency;
        $this->assertTrue(is_object($object));
    }

    function testSave()
    {
        $object = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $object->setType($type->getByIsoCode('EUR'));
        try {
            $object->save();
        } catch(Exception $e) {
            $this->assertTrue(false, $e->getMessage());
        }
    }

    function testGetType()
    {
        $object = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $object->setType($type->getByIsoCode('EUR'));
        $object->save();

        $object = Doctrine::getTable('Intraface_modules_currency_Currency')->find(1);
        $this->assertEquals('Intraface_modules_currency_Currency_Type_Eur', get_class($object->getType()));

    }

    function testGetProductPriceExchangeRate()
    {
        $currency = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency->setType($type->getByIsoCode('EUR'));
        $currency->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,21', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();

        $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection());
        $currency = $gateway->findById(1);

        $rate = $currency->getProductPriceExchangeRate();
        $this->assertEquals('745,23', $rate->getRate()->getAsLocal('da_dk'));
    }

    function testGetProductPriceExchangeRateReturnsNewestWithTwoCurrencies()
    {
        /**
         * Of some strange reason with the following posts in the database it does not
         * return the latest exchange rate
         */

        # create Euro with exchange rate
        $currency_eur = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency_eur->setType($type->getByIsoCode('EUR'));
        $currency_eur->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('7,21', 'da_dk'));
        $rate->setCurrency($currency_eur);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        $rate->setRate(new Ilib_Variable_Float('7,20', 'da_dk'));
        $rate->setCurrency($currency_eur);
        $rate->save();


        # create dollars with exchange rate
        $currency_usd = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency_usd->setType($type->getByIsoCode('USD'));
        $currency_usd->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('530', 'da_dk'));
        $rate->setCurrency($currency_usd);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        $rate->setRate(new Ilib_Variable_Float('531', 'da_dk'));
        $rate->setCurrency($currency_usd);
        $rate->save();


        # Adds a new euro exchange rate
        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $rate->setCurrency($currency_eur);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        $rate->setRate(new Ilib_Variable_Float('745,24', 'da_dk'));
        $rate->setCurrency($currency_eur);
        $rate->save();

        $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection());
        // $currency = $gateway->findById(1);

        $currencies = $gateway->findAllWithExchangeRate();

        foreach ($currencies AS $currency) {
            if ($currency->getType()->getIsoCode() == 'EUR') {
                $rate = $currency->getProductPriceExchangeRate(0);
                $this->assertEquals('745,23', $rate->getRate()->getAsLocal('da_dk'));
            }
        }


    }

    function testGetProductPriceExchangeRateWithId()
    {
        $currency = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $currency->setType($type->getByIsoCode('EUR'));
        $currency->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,21', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_Payment;
        $rate->setRate(new Ilib_Variable_Float('745,80', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();

        $rate = new Intraface_modules_currency_Currency_ExchangeRate_ProductPrice;
        $rate->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $rate->setCurrency($currency);
        $rate->save();

        $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection());
        $currency = $gateway->findById(1);

        $this->assertEquals(1, $currency->getProductPriceExchangeRate(1)->getId());
        $this->assertEquals(3, $currency->getProductPriceExchangeRate(3)->getId());
        try {
            $currency->getProductPriceExchangeRate(2);
            $this->assertTrue(false);
        }
        catch (Intraface_Gateway_Exception $e) {
            $this->assertTrue(true);
        }
    }
}
