<?php

require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Account.php';

class AccountTest extends PHPUnit_Framework_TestCase {

    function testVatCalculation()
    {
        $this->assertEquals((80 + Account::calculateVat(100, 25)), 100);
        $this->assertEquals((100 + Account::calculateVat(110, 10)), 110);
        $this->assertEquals(round((93.40 + Account::calculateVat(100.41, 7.5)), 2), 100.41);
    }
}
?>