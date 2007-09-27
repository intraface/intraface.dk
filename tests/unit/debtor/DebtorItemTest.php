<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Setting.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/debtor/DebtorItem.php';

class FakeItemDebtor
{

}

class DebtorItemTest extends PHPUnit_Framework_TestCase
{
    function createDebtor()
    {
        $debtor = new FakeItemDebtor;
        return new DebtorItem($debtor);
    }

    function testConstruct()
    {
        $debtor = $this->createDebtor();
        $this->assertTrue(is_object($debtor));
    }
}

?>