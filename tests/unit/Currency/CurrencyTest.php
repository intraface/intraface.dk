<?php
require_once dirname(__FILE__) . '/../config.test.php';

Intraface_Doctrine_Intranet::singleton(1);

class CurrencyTest extends PHPUnit_Framework_TestCase
{

    private $object = NULL;
    
    function setUp()
    {
        $db = MDB2::factory(DB_DSN);
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
}
