<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Amount.php';

class AmountTest extends PHPUnit_Framework_TestCase
{
    function testSomething()
    {
        $this->assertTrue(true);
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