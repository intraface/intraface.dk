<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/tools/Amount.php';

class AmountTest extends PHPUnit_Framework_TestCase
{
    function testConstructionFromARegularAmount()
    {
        $amount = new NewAmount('2000.50');
        $this->assertTrue(is_object($amount));
        $this->assertEquals('200050', $amount->getRawAmount());
    }

    function testConstructionFromADanishAmount()
    {
        $amount = new NewAmount('2000,50');
        $this->assertTrue(is_object($amount));
        $this->assertEquals('200050', $amount->getRawAmount());
    }

}
?>