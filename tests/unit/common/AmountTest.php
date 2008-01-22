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
    
    function testFormatReturnsAccordingToLocale()
    {
        $default = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, 'C');
        $amount = new NewAmount('2000,50');
        $this->assertTrue(is_object($amount));
        $this->assertEquals('2000.50', $amount->format());

        setlocale(LC_ALL, 'da_DK', 'danish');

        $this->assertEquals('2000,50', $amount->format());
        setlocale(LC_ALL, $default);
    }
    
    function testDatabaseReturnsValidDouble()
    {
        $amount = new NewAmount('2000,50');
        $this->assertTrue(is_object($amount));
        $this->assertEquals('2000.50', $amount->database());
        $this->assertTrue(is_double($amount->database()));
    }
    
}
/*
$number = 2000.50;
setlocale(LC_TIME, "C");
echo number_format($number, 2);
echo strftime("%A");

echo '<br>' . setlocale(LC_ALL, 'danish');
echo '<br>' . number_format($number, 2);
echo strftime("%A");
*/
?>