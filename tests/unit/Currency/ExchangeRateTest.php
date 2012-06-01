<?php
Intraface_Doctrine_Intranet::singleton(1);

class ExchangeRateTest extends PHPUnit_Framework_TestCase
{
    private $object = NULL;

    function setUp()
    {
        $db = MDB2::singleton(DB_DSN);
        $db->query('TRUNCATE currency');
        $db->query('TRUNCATE currency_exchangerate');
    }

    function createCurrency()
    {
        $object = new Intraface_modules_currency_Currency;
        $type = new Intraface_modules_currency_Currency_Type;
        $object->setType($type->getByIsoCode('EUR'));
        $object->save();
        return $object;
    }

    ///////////////////////////////////////////////////////

    function testConstruct()
    {
        $object = new Intraface_modules_currency_Currency_ExchangeRate;
        $this->assertTrue(is_object($object));
    }

    function testSave()
    {
        $object = new Intraface_modules_currency_Currency_ExchangeRate;
        $object->used_for_key = 1;
        $object->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $object->setCurrency($this->createCurrency());

        try {
            $object->save();
        }
        catch(Exception $e) {
            // $this->assertTrue(false, $e->getErrorMessage());
        }
    }

    function testGetRate()
    {
        $object = new Intraface_modules_currency_Currency_ExchangeRate;
        $object->used_for_key = 1;
        $object->setRate(new Ilib_Variable_Float('745,23', 'da_dk'));
        $object->setCurrency($this->createCurrency());
        $object->save();

        $object = Doctrine::getTable('Intraface_modules_currency_Currency_ExchangeRate')->find(1);
        $this->assertEquals('745,23', $object->getRate()->getAsLocal('da_dk'));

    }
}
